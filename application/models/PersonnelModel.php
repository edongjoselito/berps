<?php
class PersonnelModel extends CI_Model
{
	function displaypersonnelById($id)
	{
		$query = $this->db->query("SELECT * FROM staff WHERE IDNumber = " . $this->db->escape($id));
		return $query->result();
	}


	public function profilepic($username)
	{
		return $this->db
			->where('username', $username)
			->get('users')
			->result();
	}
}
