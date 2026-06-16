<?php
class PosProduct_model extends CI_Model
{
    protected $table = 'POS_products';

    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    public function get_all($settingsID = null)
    {
        $this->apply_settings_scope($settingsID);
        return $this->db->order_by('id', 'DESC')->get($this->table)->result();
    }

    public function get_stock_levels($settingsID = null)
    {
        $this->apply_settings_scope($settingsID);
        return $this->db
            ->order_by('category', 'ASC')
            ->order_by('name', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_low_stock($threshold = 5, $settingsID = null)
    {
        $this->apply_settings_scope($settingsID);

        $this->db->where('status', 'active');

        if ($threshold === null) {
            $this->db->where('stock_qty <= reorder_level', null, false);
        } else {
            $this->db->where('stock_qty <=', (int) $threshold);
        }

        return $this->db
            ->order_by('stock_qty', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_all_sorted_by_sku($settingsID = null)
    {
        $this->apply_settings_scope($settingsID);

        return $this->db
            ->order_by('sku', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_active_nonexpired($settingsID = null)
    {
        $today = date('Y-m-d');

        $dateExpr = "(CASE WHEN expiry_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expiry_date, '%Y-%m-%d') ELSE NULL END)";

        $this->apply_settings_scope($settingsID);

        return $this->db
            ->where('status', 'active')
            ->group_start()
                // No expiry or invalid date -> treat as active
                ->where("$dateExpr IS NULL", null, false)
                // Valid expiry date in the future or today
                ->or_where("$dateExpr >=", $today)
            ->group_end()
            ->order_by('id', 'DESC')
            ->get($this->table)
            ->result();
    }

    public function get_expiring_soon($days = 30, $settingsID = null)
    {
        $today = date('Y-m-d');
        $limit = date('Y-m-d', strtotime('+' . (int)$days . ' days'));

        // Parse only valid date strings; invalid/blank values become NULL so they are skipped safely.
        $dateExpr = "(CASE WHEN expiry_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expiry_date, '%Y-%m-%d') ELSE NULL END)";

        $this->apply_settings_scope($settingsID);

        return $this->db
            ->where('status', 'active')
            ->where('expiry_date IS NOT NULL', null, false)
            ->where("$dateExpr IS NOT NULL", null, false)
            ->where("$dateExpr >=", $today)
            ->where("$dateExpr <=", $limit)
            ->order_by($dateExpr, 'ASC', false)
            ->get($this->table)
            ->result();
    }

    public function get_expired($settingsID = null)
    {
        $today = date('Y-m-d');

        // Parse only valid date strings; invalid/blank values become NULL so they are skipped safely.
        $dateExpr = "(CASE WHEN expiry_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expiry_date, '%Y-%m-%d') ELSE NULL END)";

        $this->apply_settings_scope($settingsID);

        return $this->db
            ->where('status', 'active')
            ->where('expiry_date IS NOT NULL', null, false)
            ->where("$dateExpr IS NOT NULL", null, false)
            ->where("$dateExpr <", $today)
            ->order_by($dateExpr, 'ASC', false)
            ->get($this->table)
            ->result();
    }

    public function insert(array $data)
    {
        if (!isset($data['unit']) || trim((string) $data['unit']) === '') {
            $data['unit'] = 'pcs';
        }

        if (!isset($data['reorder_level'])) {
            $data['reorder_level'] = 5;
        }

        if (!isset($data['tax_type']) || trim((string) $data['tax_type']) === '') {
            $data['tax_type'] = 'vatable';
        }

        if (!isset($data['discount_eligible'])) {
            $data['discount_eligible'] = 1;
        }

        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');
        $data['updated_at'] = $data['updated_at'] ?? date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }

    public function find($id, $settingsID = null)
    {
        $this->apply_settings_scope($settingsID);
        return $this->db->get_where($this->table, ['id' => (int)$id])->row();
    }

    public function update($id, array $data, $settingsID = null)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->apply_settings_scope($settingsID);
        return $this->db->where('id', (int)$id)->update($this->table, $data);
    }

    public function delete($id, $settingsID = null)
    {
        $this->apply_settings_scope($settingsID);
        return $this->db->where('id', (int)$id)->delete($this->table);
    }

    public function generate_next_sku($prefix = 'POS-', $settingsID = null)
    {
        $this->db->select('sku');
        $this->db->like('sku', $prefix, 'after');
        $this->apply_settings_scope($settingsID);
        $query = $this->db->get($this->table);

        $numbers = [];
        foreach ($query->result() as $row) {
            if (!isset($row->sku)) {
                continue;
            }
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\\d+)$/', (string)$row->sku, $m)) {
                $numbers[] = (int)$m[1];
            }
        }

        sort($numbers, SORT_NUMERIC);

        // pick the smallest available positive number (fills gaps after deletions)
        $next = 1;
        foreach ($numbers as $num) {
            if ($num === $next) {
                $next++;
                continue;
            }
            if ($num > $next) {
                break;
            }
        }

        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Apply settings scope if provided.
     */
    private function apply_settings_scope($settingsID)
    {
        $id = (int)$settingsID;
        if ($id > 0) {
            $this->db->where('settingsID', $id);
        }
    }

    private function ensureTable()
    {
        if (!$this->db->table_exists($this->table)) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `{$this->table}` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `settingsID` int unsigned NOT NULL DEFAULT 0,
                    `sku` varchar(50) NOT NULL,
                    `barcode` varchar(60) DEFAULT NULL,
                    `name` varchar(150) NOT NULL,
                    `category` varchar(80) DEFAULT NULL,
                    `unit` varchar(20) DEFAULT 'pcs',
                    `unit_cost` double NOT NULL DEFAULT 0,
                    `unit_price` double NOT NULL DEFAULT 0,
                    `stock_qty` int NOT NULL DEFAULT 0,
                    `reorder_level` int NOT NULL DEFAULT 5,
                    `tax_type` varchar(20) NOT NULL DEFAULT 'vatable',
                    `discount_eligible` tinyint(1) NOT NULL DEFAULT 1,
                    `expiry_date` date DEFAULT NULL,
                    `status` varchar(20) NOT NULL DEFAULT 'active',
                    `business_type` varchar(30) DEFAULT 'general',
                    `brand` varchar(120) DEFAULT NULL,
                    `description` text DEFAULT NULL,
                    `specifications` text DEFAULT NULL,
                    `usage_instructions` text DEFAULT NULL,
                    `safety_info` text DEFAULT NULL,

                    `generic_name` varchar(150) DEFAULT NULL,
                    `dosage_form` varchar(80) DEFAULT NULL,
                    `strength` varchar(80) DEFAULT NULL,
                    `prescription_required` tinyint(1) NOT NULL DEFAULT 0,
                    `fda_registration` varchar(120) DEFAULT NULL,
                    `drug_classification` varchar(80) DEFAULT NULL,
                    `storage_requirements` varchar(80) DEFAULT NULL,
                    `expiry_tracking` tinyint(1) NOT NULL DEFAULT 0,

                    `product_type` varchar(80) DEFAULT NULL,
                    `organic_certified` varchar(40) DEFAULT NULL,
                    `allergens` text DEFAULT NULL,
                    `nutritional_info` varchar(40) DEFAULT NULL,
                    `storage_instructions` varchar(80) DEFAULT NULL,
                    `shelf_life` int DEFAULT NULL,
                    `country_of_origin` varchar(80) DEFAULT NULL,

                    `menu_category` varchar(80) DEFAULT NULL,
                    `preparation_time` int DEFAULT NULL,
                    `temperature_requirement` varchar(40) DEFAULT NULL,
                    `dietary_restrictions` text DEFAULT NULL,
                    `allergen_warnings` text DEFAULT NULL,
                    `cooking_method` varchar(40) DEFAULT NULL,
                    `spice_level` varchar(40) DEFAULT NULL,

                    `electronics_category` varchar(80) DEFAULT NULL,
                    `model_number` varchar(120) DEFAULT NULL,
                    `warranty_period` int DEFAULT NULL,
                    `power_requirements` varchar(120) DEFAULT NULL,
                    `technical_specs` text DEFAULT NULL,
                    `compatibility` varchar(255) DEFAULT NULL,
                    `color_options` varchar(255) DEFAULT NULL,
                    `serial_tracking` tinyint(1) NOT NULL DEFAULT 0,

                    `clothing_category` varchar(80) DEFAULT NULL,
                    `material` varchar(120) DEFAULT NULL,
                    `sizes` text DEFAULT NULL,
                    `colors` varchar(255) DEFAULT NULL,
                    `season` varchar(40) DEFAULT NULL,
                    `fit_type` varchar(40) DEFAULT NULL,
                    `care_instructions` varchar(80) DEFAULT NULL,
                    `created_at` datetime DEFAULT NULL,
                    `updated_at` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_pos_products_settings` (`settingsID`),
                    KEY `idx_pos_products_sku` (`sku`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
            ");
            return;
        }

        $fieldMap = [
            'barcode' => "ALTER TABLE `{$this->table}` ADD COLUMN `barcode` varchar(60) DEFAULT NULL AFTER `sku`",
            'unit' => "ALTER TABLE `{$this->table}` ADD COLUMN `unit` varchar(20) DEFAULT 'pcs' AFTER `category`",
            'reorder_level' => "ALTER TABLE `{$this->table}` ADD COLUMN `reorder_level` int NOT NULL DEFAULT 5 AFTER `stock_qty`",
            'tax_type' => "ALTER TABLE `{$this->table}` ADD COLUMN `tax_type` varchar(20) NOT NULL DEFAULT 'vatable' AFTER `reorder_level`",
            'discount_eligible' => "ALTER TABLE `{$this->table}` ADD COLUMN `discount_eligible` tinyint(1) NOT NULL DEFAULT 1 AFTER `tax_type`",
            'business_type' => "ALTER TABLE `{$this->table}` ADD COLUMN `business_type` varchar(30) DEFAULT 'general' AFTER `status`",
            'brand' => "ALTER TABLE `{$this->table}` ADD COLUMN `brand` varchar(120) DEFAULT NULL AFTER `business_type`",
            'description' => "ALTER TABLE `{$this->table}` ADD COLUMN `description` text DEFAULT NULL AFTER `brand`",
            'specifications' => "ALTER TABLE `{$this->table}` ADD COLUMN `specifications` text DEFAULT NULL AFTER `description`",
            'usage_instructions' => "ALTER TABLE `{$this->table}` ADD COLUMN `usage_instructions` text DEFAULT NULL AFTER `specifications`",
            'safety_info' => "ALTER TABLE `{$this->table}` ADD COLUMN `safety_info` text DEFAULT NULL AFTER `usage_instructions`",
            'generic_name' => "ALTER TABLE `{$this->table}` ADD COLUMN `generic_name` varchar(150) DEFAULT NULL AFTER `safety_info`",
            'dosage_form' => "ALTER TABLE `{$this->table}` ADD COLUMN `dosage_form` varchar(80) DEFAULT NULL AFTER `generic_name`",
            'strength' => "ALTER TABLE `{$this->table}` ADD COLUMN `strength` varchar(80) DEFAULT NULL AFTER `dosage_form`",
            'prescription_required' => "ALTER TABLE `{$this->table}` ADD COLUMN `prescription_required` tinyint(1) NOT NULL DEFAULT 0 AFTER `strength`",
            'fda_registration' => "ALTER TABLE `{$this->table}` ADD COLUMN `fda_registration` varchar(120) DEFAULT NULL AFTER `prescription_required`",
            'drug_classification' => "ALTER TABLE `{$this->table}` ADD COLUMN `drug_classification` varchar(80) DEFAULT NULL AFTER `fda_registration`",
            'storage_requirements' => "ALTER TABLE `{$this->table}` ADD COLUMN `storage_requirements` varchar(80) DEFAULT NULL AFTER `drug_classification`",
            'expiry_tracking' => "ALTER TABLE `{$this->table}` ADD COLUMN `expiry_tracking` tinyint(1) NOT NULL DEFAULT 0 AFTER `storage_requirements`",
            'product_type' => "ALTER TABLE `{$this->table}` ADD COLUMN `product_type` varchar(80) DEFAULT NULL AFTER `expiry_tracking`",
            'organic_certified' => "ALTER TABLE `{$this->table}` ADD COLUMN `organic_certified` varchar(40) DEFAULT NULL AFTER `product_type`",
            'allergens' => "ALTER TABLE `{$this->table}` ADD COLUMN `allergens` text DEFAULT NULL AFTER `organic_certified`",
            'nutritional_info' => "ALTER TABLE `{$this->table}` ADD COLUMN `nutritional_info` varchar(40) DEFAULT NULL AFTER `allergens`",
            'storage_instructions' => "ALTER TABLE `{$this->table}` ADD COLUMN `storage_instructions` varchar(80) DEFAULT NULL AFTER `nutritional_info`",
            'shelf_life' => "ALTER TABLE `{$this->table}` ADD COLUMN `shelf_life` int DEFAULT NULL AFTER `storage_instructions`",
            'country_of_origin' => "ALTER TABLE `{$this->table}` ADD COLUMN `country_of_origin` varchar(80) DEFAULT NULL AFTER `shelf_life`",
            'menu_category' => "ALTER TABLE `{$this->table}` ADD COLUMN `menu_category` varchar(80) DEFAULT NULL AFTER `country_of_origin`",
            'preparation_time' => "ALTER TABLE `{$this->table}` ADD COLUMN `preparation_time` int DEFAULT NULL AFTER `menu_category`",
            'temperature_requirement' => "ALTER TABLE `{$this->table}` ADD COLUMN `temperature_requirement` varchar(40) DEFAULT NULL AFTER `preparation_time`",
            'dietary_restrictions' => "ALTER TABLE `{$this->table}` ADD COLUMN `dietary_restrictions` text DEFAULT NULL AFTER `temperature_requirement`",
            'allergen_warnings' => "ALTER TABLE `{$this->table}` ADD COLUMN `allergen_warnings` text DEFAULT NULL AFTER `dietary_restrictions`",
            'cooking_method' => "ALTER TABLE `{$this->table}` ADD COLUMN `cooking_method` varchar(40) DEFAULT NULL AFTER `allergen_warnings`",
            'spice_level' => "ALTER TABLE `{$this->table}` ADD COLUMN `spice_level` varchar(40) DEFAULT NULL AFTER `cooking_method`",
            'electronics_category' => "ALTER TABLE `{$this->table}` ADD COLUMN `electronics_category` varchar(80) DEFAULT NULL AFTER `spice_level`",
            'model_number' => "ALTER TABLE `{$this->table}` ADD COLUMN `model_number` varchar(120) DEFAULT NULL AFTER `electronics_category`",
            'warranty_period' => "ALTER TABLE `{$this->table}` ADD COLUMN `warranty_period` int DEFAULT NULL AFTER `model_number`",
            'power_requirements' => "ALTER TABLE `{$this->table}` ADD COLUMN `power_requirements` varchar(120) DEFAULT NULL AFTER `warranty_period`",
            'technical_specs' => "ALTER TABLE `{$this->table}` ADD COLUMN `technical_specs` text DEFAULT NULL AFTER `power_requirements`",
            'compatibility' => "ALTER TABLE `{$this->table}` ADD COLUMN `compatibility` varchar(255) DEFAULT NULL AFTER `technical_specs`",
            'color_options' => "ALTER TABLE `{$this->table}` ADD COLUMN `color_options` varchar(255) DEFAULT NULL AFTER `compatibility`",
            'serial_tracking' => "ALTER TABLE `{$this->table}` ADD COLUMN `serial_tracking` tinyint(1) NOT NULL DEFAULT 0 AFTER `color_options`",
            'clothing_category' => "ALTER TABLE `{$this->table}` ADD COLUMN `clothing_category` varchar(80) DEFAULT NULL AFTER `serial_tracking`",
            'material' => "ALTER TABLE `{$this->table}` ADD COLUMN `material` varchar(120) DEFAULT NULL AFTER `clothing_category`",
            'sizes' => "ALTER TABLE `{$this->table}` ADD COLUMN `sizes` text DEFAULT NULL AFTER `material`",
            'colors' => "ALTER TABLE `{$this->table}` ADD COLUMN `colors` varchar(255) DEFAULT NULL AFTER `sizes`",
            'season' => "ALTER TABLE `{$this->table}` ADD COLUMN `season` varchar(40) DEFAULT NULL AFTER `colors`",
            'fit_type' => "ALTER TABLE `{$this->table}` ADD COLUMN `fit_type` varchar(40) DEFAULT NULL AFTER `season`",
            'care_instructions' => "ALTER TABLE `{$this->table}` ADD COLUMN `care_instructions` varchar(80) DEFAULT NULL AFTER `fit_type`",
            'created_at' => "ALTER TABLE `{$this->table}` ADD COLUMN `created_at` datetime DEFAULT NULL AFTER `status`",
            'updated_at' => "ALTER TABLE `{$this->table}` ADD COLUMN `updated_at` datetime DEFAULT NULL AFTER `created_at`",
        ];

        foreach ($fieldMap as $field => $sql) {
            if (!$this->db->field_exists($field, $this->table)) {
                $this->db->query($sql);
            }
        }
    }
}
