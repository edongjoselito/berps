<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_industry_fields_to_pos_products extends CI_Migration {

    public function up() {
        // Add basic business type field
        $this->dbforge->add_column('POS_products', array(
            'business_type' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE,
                'default' => 'general',
                'after' => 'status'
            ),
            'brand' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE,
                'after' => 'business_type'
            )
        ));

        // Pharmacy-specific fields
        $this->dbforge->add_column('POS_products', array(
            'generic_name' => array(
                'type' => 'VARCHAR',
                'constraint' => '200',
                'null' => TRUE,
                'after' => 'brand'
            ),
            'dosage_form' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE,
                'after' => 'generic_name'
            ),
            'strength' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE,
                'after' => 'dosage_form'
            ),
            'prescription_required' => array(
                'type' => 'TINYINT',
                'constraint' => '1',
                'null' => TRUE,
                'default' => '0',
                'after' => 'strength'
            ),
            'fda_registration' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE,
                'after' => 'prescription_required'
            ),
            'drug_classification' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE,
                'after' => 'fda_registration'
            ),
            'storage_requirements' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE,
                'after' => 'drug_classification'
            ),
            'expiry_tracking' => array(
                'type' => 'TINYINT',
                'constraint' => '1',
                'null' => TRUE,
                'default' => '0',
                'after' => 'storage_requirements'
            )
        ));

        // Grocery-specific fields
        $this->dbforge->add_column('POS_products', array(
            'product_type' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE,
                'after' => 'expiry_tracking'
            ),
            'organic_certified' => array(
                'type' => 'TINYINT',
                'constraint' => '1',
                'null' => TRUE,
                'default' => '0',
                'after' => 'product_type'
            ),
            'allergens' => array(
                'type' => 'TEXT',
                'null' => TRUE,
                'after' => 'organic_certified'
            ),
            'nutritional_info' => array(
                'type' => 'TINYINT',
                'constraint' => '1',
                'null' => TRUE,
                'default' => '0',
                'after' => 'allergens'
            ),
            'storage_instructions' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE,
                'after' => 'nutritional_info'
            ),
            'shelf_life' => array(
                'type' => 'INT',
                'constraint' => '11',
                'null' => TRUE,
                'after' => 'storage_instructions'
            ),
            'country_of_origin' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE,
                'after' => 'shelf_life'
            )
        ));

        // Restaurant-specific fields
        $this->dbforge->add_column('POS_products', array(
            'menu_category' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE,
                'after' => 'country_of_origin'
            ),
            'preparation_time' => array(
                'type' => 'INT',
                'constraint' => '11',
                'null' => TRUE,
                'after' => 'menu_category'
            ),
            'temperature_requirement' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE,
                'after' => 'preparation_time'
            ),
            'dietary_restrictions' => array(
                'type' => 'TEXT',
                'null' => TRUE,
                'after' => 'temperature_requirement'
            ),
            'allergen_warnings' => array(
                'type' => 'TEXT',
                'null' => TRUE,
                'after' => 'dietary_restrictions'
            ),
            'cooking_method' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE,
                'after' => 'allergen_warnings'
            ),
            'spice_level' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE,
                'after' => 'cooking_method'
            )
        ));

        // Electronics-specific fields
        $this->dbforge->add_column('POS_products', array(
            'electronics_category' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE,
                'after' => 'spice_level'
            ),
            'model_number' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE,
                'after' => 'electronics_category'
            ),
            'warranty_period' => array(
                'type' => 'INT',
                'constraint' => '11',
                'null' => TRUE,
                'after' => 'model_number'
            ),
            'power_requirements' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE,
                'after' => 'warranty_period'
            ),
            'technical_specs' => array(
                'type' => 'TEXT',
                'null' => TRUE,
                'after' => 'power_requirements'
            ),
            'compatibility' => array(
                'type' => 'VARCHAR',
                'constraint' => '200',
                'null' => TRUE,
                'after' => 'technical_specs'
            ),
            'color_options' => array(
                'type' => 'VARCHAR',
                'constraint' => '200',
                'null' => TRUE,
                'after' => 'compatibility'
            ),
            'serial_tracking' => array(
                'type' => 'TINYINT',
                'constraint' => '1',
                'null' => TRUE,
                'default' => '0',
                'after' => 'color_options'
            )
        ));

        // Clothing-specific fields
        $this->dbforge->add_column('POS_products', array(
            'clothing_category' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE,
                'after' => 'serial_tracking'
            ),
            'material' => array(
                'type' => 'VARCHAR',
                'constraint' => '200',
                'null' => TRUE,
                'after' => 'clothing_category'
            ),
            'sizes' => array(
                'type' => 'TEXT',
                'null' => TRUE,
                'after' => 'material'
            ),
            'colors' => array(
                'type' => 'VARCHAR',
                'constraint' => '200',
                'null' => TRUE,
                'after' => 'sizes'
            ),
            'season' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE,
                'after' => 'colors'
            ),
            'fit_type' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE,
                'after' => 'season'
            ),
            'care_instructions' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => TRUE,
                'after' => 'fit_type'
            )
        ));

        // General fields
        $this->dbforge->add_column('POS_products', array(
            'description' => array(
                'type' => 'TEXT',
                'null' => TRUE,
                'after' => 'care_instructions'
            ),
            'specifications' => array(
                'type' => 'TEXT',
                'null' => TRUE,
                'after' => 'description'
            ),
            'usage_instructions' => array(
                'type' => 'TEXT',
                'null' => TRUE,
                'after' => 'specifications'
            ),
            'safety_info' => array(
                'type' => 'TEXT',
                'null' => TRUE,
                'after' => 'usage_instructions'
            )
        ));

        // Add indexes for better performance
        $this->db->query('ALTER TABLE `POS_products` ADD INDEX `idx_business_type` (`business_type`)');
        $this->db->query('ALTER TABLE `POS_products` ADD INDEX `idx_brand` (`brand`)');
        $this->db->query('ALTER TABLE `POS_products` ADD INDEX `idx_generic_name` (`generic_name`)');
        $this->db->query('ALTER TABLE `POS_products` ADD INDEX `idx_model_number` (`model_number`)');
    }

    public function down() {
        // Remove all added columns in reverse order
        $fields_to_remove = [
            'safety_info',
            'usage_instructions', 
            'specifications',
            'description',
            'care_instructions',
            'fit_type',
            'season',
            'colors',
            'sizes',
            'material',
            'clothing_category',
            'serial_tracking',
            'color_options',
            'compatibility',
            'technical_specs',
            'power_requirements',
            'warranty_period',
            'model_number',
            'electronics_category',
            'spice_level',
            'cooking_method',
            'allergen_warnings',
            'dietary_restrictions',
            'temperature_requirement',
            'preparation_time',
            'menu_category',
            'country_of_origin',
            'shelf_life',
            'storage_instructions',
            'nutritional_info',
            'allergens',
            'organic_certified',
            'product_type',
            'expiry_tracking',
            'storage_requirements',
            'drug_classification',
            'fda_registration',
            'prescription_required',
            'strength',
            'dosage_form',
            'generic_name',
            'brand',
            'business_type'
        ];

        foreach ($fields_to_remove as $field) {
            if ($this->db->field_exists($field, 'POS_products')) {
                $this->dbforge->drop_column('POS_products', $field);
            }
        }
    }
}
