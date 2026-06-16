<?php
class User_model extends CI_Model
{

    private $table = 'users';

    public function get_by_settings($settingsID)
    {
        return $this->db->get_where('users', ['settingsID' => $settingsID])->result();
    }

    public function get_by_settings_and_positions($settingsID, array $positions)
    {
        if (empty($positions)) {
            return $this->get_by_settings($settingsID);
        }

        return $this->db
            ->where('settingsID', $settingsID)
            ->where_in('position', $positions)
            ->get($this->table)
            ->result();
    }

    public function get_by_id($user_id)
    {
        return $this->db->get_where($this->table, ['user_id' => $user_id])->row();
    }

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update($user_id, $data)
    {
        return $this->db->where('user_id', $user_id)->update($this->table, $data);
    }

    public function delete($user_id)
    {
        return $this->db->delete($this->table, ['user_id' => $user_id]);
    }

    public function get_user_password($username)
    {
        $this->db->select('password');
        $this->db->from('users'); // adjust if your table name is different
        $this->db->where('username', $username);
        $query = $this->db->get();

        return $query->row() ? $query->row()->password : null;
    }

    public function reset_userpassword($username, $new_password_hash)
    {
        $this->db->where('username', $username);
        return $this->db->update('users', ['password' => $new_password_hash]);
    }
}
