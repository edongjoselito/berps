<?php
class Knowledge_base_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_all($settingsID, $type = null, $status = 'published', $category = null)
    {
        $this->db->where('settingsID', $settingsID);
        if ($status !== null && $status !== '') {
            $this->db->where('status', $status);
        }

        if ($type !== null && $type !== '') {
            $this->db->where('type', $type);
        }

        if ($category !== null && $category !== '') {
            $this->db->where('category', $category);
        }

        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('knowledge_base')->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('knowledge_base', ['id' => $id])->row();
    }

    public function get_by_user($settingsID, $userID, $type = null, $category = null)
    {
        $this->db->where('settingsID', $settingsID);
        $this->db->where('created_by', $userID);

        if ($type !== null && $type !== '') {
            $this->db->where('type', $type);
        }

        if ($category !== null && $category !== '') {
            $this->db->where('category', $category);
        }

        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('knowledge_base')->result();
    }

    public function get_categories($settingsID, $status = null, $type = null)
    {
        // Check if categories table exists
        $tableExists = $this->db->table_exists('knowledge_base_categories');
        
        if ($tableExists) {
            // Get categories from the categories table
            $this->db->where('settingsID', $settingsID);
            $this->db->order_by('name', 'ASC');
            $categories = $this->db->get('knowledge_base_categories')->result();

            // Get article counts for each category
            foreach ($categories as $cat) {
                $this->db->where('settingsID', $settingsID);
                $this->db->where('category', $cat->name);
                $this->db->where('title NOT LIKE', 'Category:%');
                if ($status !== null && $status !== '') {
                    $this->db->where('status', $status);
                }
                if ($type !== null && $type !== '') {
                    $this->db->where('type', $type);
                }
                $cat->count = $this->db->count_all_results('knowledge_base');
            }

            return $categories;
        } else {
            // Fallback: get categories from articles (excluding placeholders)
            $this->db->select('category as name, COUNT(*) as count');
            $this->db->where('settingsID', $settingsID);
            $this->db->where('category IS NOT NULL');
            $this->db->where('category !=', '');
            $this->db->where('title NOT LIKE', 'Category:%');
            if ($status !== null && $status !== '') {
                $this->db->where('status', $status);
            }
            if ($type !== null && $type !== '') {
                $this->db->where('type', $type);
            }
            $this->db->group_by('category');
            $this->db->order_by('category', 'ASC');
            return $this->db->get('knowledge_base')->result();
        }
    }

    public function get_category($settingsID, $categoryName)
    {
        $tableExists = $this->db->table_exists('knowledge_base_categories');
        
        if ($tableExists) {
            return $this->db->get_where('knowledge_base_categories', [
                'settingsID' => $settingsID,
                'name' => $categoryName
            ])->row();
        } else {
            return $this->db->get_where('knowledge_base', [
                'settingsID' => $settingsID,
                'category' => $categoryName
            ])->row();
        }
    }

    public function insert_category($data)
    {
        // Create table if it doesn't exist
        if (!$this->db->table_exists('knowledge_base_categories')) {
            $this->db->query("
                CREATE TABLE `knowledge_base_categories` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `settingsID` int(11) NOT NULL,
                    `name` varchar(255) NOT NULL,
                    `created_at` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `settingsID` (`settingsID`),
                    KEY `name` (`name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }
        
        $this->db->insert('knowledge_base_categories', $data);
        return $this->db->insert_id();
    }

    public function update_category($settingsID, $oldName, $newName)
    {
        $tableExists = $this->db->table_exists('knowledge_base_categories');
        
        if ($tableExists) {
            $this->db->where('settingsID', $settingsID);
            $this->db->where('name', $oldName);
            return $this->db->update('knowledge_base_categories', ['name' => $newName]);
        }
        
        // Fallback: update articles directly
        $this->db->where('settingsID', $settingsID);
        $this->db->where('category', $oldName);
        return $this->db->update('knowledge_base', ['category' => $newName]);
    }

    public function delete_category($settingsID, $categoryName)
    {
        $tableExists = $this->db->table_exists('knowledge_base_categories');
        
        if ($tableExists) {
            $this->db->where('settingsID', $settingsID);
            $this->db->where('name', $categoryName);
            return $this->db->delete('knowledge_base_categories');
        }
        
        // Fallback: no-op for article-based categories
        return true;
    }

    public function insert($data)
    {
        $this->db->insert('knowledge_base', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('knowledge_base', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('knowledge_base');
    }

    public function increment_view_count($id)
    {
        $this->db->set('view_count', 'view_count + 1', FALSE);
        $this->db->where('id', $id);
        $this->db->update('knowledge_base');
    }

    public function search($settingsID, $query, $type = null)
    {
        $this->db->where('settingsID', $settingsID);
        $this->db->where('status', 'published');
        $this->db->group_start();
        $this->db->like('title', $query);
        $this->db->or_like('content', $query);
        $this->db->group_end();

        if ($type !== null) {
            $this->db->where('type', $type);
        }

        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('knowledge_base')->result();
    }
}
