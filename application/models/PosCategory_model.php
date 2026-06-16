<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PosCategory_model extends CI_Model
{
    private $table = 'pos_categories';

    public function get_all($settingsID = null)
    {
        if ($settingsID !== null) {
            $this->db->where('settingsID', (int) $settingsID);
        }

        return $this->db
            ->order_by('name', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function create($name, $settingsID = null)
    {
        $name = trim((string) $name);
        if ($name === '') {
            return false;
        }

        $data = [
            'name' => $name,
            'settingsID' => (int) ($settingsID ?? 0),
        ];

        return $this->db->insert($this->table, $data);
    }

    public function update($id, $name, $settingsID = null)
    {
        $id = (int) $id;
        $name = trim((string) $name);
        if ($id <= 0 || $name === '') {
            return false;
        }

        if ($settingsID !== null) {
            $this->db->where('settingsID', (int) $settingsID);
        }

        $this->db->where('id', $id);
        return $this->db->update($this->table, ['name' => $name]);
    }

    public function delete($id, $settingsID = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return false;
        }

        if ($settingsID !== null) {
            $this->db->where('settingsID', (int) $settingsID);
        }

        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
}
