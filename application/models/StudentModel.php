<?php
class StudentModel extends CI_Model
{
	public function staffProfile($username)
	{
		return $this->db
			->where('username', $username)
			->get('users')
			->row();   // single row object
	}


	public function getStaffByLogin($login)
	{
		return $this->db
			->where('username', $login)
			->get('users')
			->row();
	}
}
