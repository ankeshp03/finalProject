<?php

class LoginModel extends CI_Model {

	public function validateStudent() {

		$this->db->where('student_email_id', $this->input->post('email'));
		$this->db->where('student_password', hash ( "sha256", $this->input->post('password')));

		$query = $this->db->get('student');

		if($query->num_rows() > 0) {
			foreach ($query->result() as $row)
			{
				$res['usn'] = $row->usn;
				$res['username'] = $row->student_name;
				if(strcasecmp($this->input->post('password'), $row->usn) == 0) {
					$res['firstTime'] = "true";
				}
				else {
					$res['firstTime'] = "false";	
				}
				return $res;
			}
		}
		else {
			return null;
		}
	}

	public function validateFaculty() {

		$this->db->where('email_id', $this->input->post('email'));
		$this->db->where('password', hash ( "sha256", $this->input->post('password')));

		$query = $this->db->get('faculty');

		if($query->num_rows() > 0) {
			foreach ($query->result() as $row)
			{
				$res['employeeID'] = $row->employee_code;
				$res['level'] = $row->level;
				$res['username'] = $row->faculty_name;
				$res['institute_department'] = $row->institute_department;
			}
			if($res['level'] == "1") {

				$this->db->select('quote');
				$this->db->where('date', date("Y-m-d"));
				$query = $this->db->get('quotes')->row();
				if(!$query) {
					$data = array('date' => "0000-00-00");
					$this->db->where('date !=', date("Y-m-d"));
					$this->db->where('date !=', "0000-00-00");
					$this->db->update('quotes', $data);

					$randId = rand(1, 25);
					$this->db->select('quote');
					$this->db->where('id', $randId);
					$query = $this->db->get('quotes')->row();

					$data = array('date' => date("Y-m-d"));
					$this->db->where('id', $randId);
					$this->db->update('quotes', $data);
				}
				$res['quote'] = $query->quote;
			}
			return $res;
		}
		else {
			return null;
		}
	}
	/*---
	public function getTotal() {

		$this->db->select('email');
		$query = $this->db->get('users');

		return $query->num_rows();
	} ---*/

	public function emailExist() {

		if (1 == preg_match("/^[a-zA-Z]+\.[a-zA-Z]+\.([0-9][1-9]|[1-9][0-9])@acharya\.ac\.in$/", $this->input->post('emailSendKey'))) {
			$this->db->where('student_email_id', $this->input->post('emailSendKey'));
			$query = $this->db->get('student');
		}
		else {
			$this->db->where('email_id', $this->input->post('emailSendKey'));
			$query = $this->db->get('faculty');
		}

		if($query->num_rows() > 0) {
			return true;
		}
		else {
			return false;
		}
	}

	public function keyPresent() {

		$this->db->where('email', $this->input->post('emailSendKey'));
		$query = $this->db->get('forgotpasswordusers');
		if($query->num_rows() > 0) {
			foreach ($query->result() as $row)
			{
				return $row->hashKey;
			}
		}
		else {
			return null;
		}
	}

	public function addHashKey($key) {

		$data = array(
			'email' => $this->input->post('emailSendKey'),
			'hashKey' => $key
			);

		$query = $this->db->insert('forgotpasswordusers', $data);

		if($this->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function validateHashKey($key) {

		$this->db->where('hashKey', $key);
		$query = $this->db->get('forgotpasswordusers');

		if($query->num_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function deleteHashKey($key) {

		$this->db->select('email');
		$this->db->where('hashKey', $key);
		$query = $this->db->get('forgotpasswordusers');
		$email = null;
		foreach ($query->result() as $row)
		{
			$email = $row->email;
		}

		if (1 == preg_match("/^[a-zA-Z]+\.[a-zA-Z]+\.([0-9][1-9]|[1-9][0-9])@acharya\.ac\.in$/", $email)) {
			$data = array('student_password' => hash ( "sha256", $this->input->post('password')));
			$this->db->where('student_email_id', $email);
			$query = $this->db->update('student', $data);
		}
		else {
			$data = array('password' => hash ( "sha256", $this->input->post('password')));
			$this->db->where('email_id', $email);
			$query = $this->db->update('faculty', $data);
		}

		$this->db->where('hashKey', $key);
		$query = $this->db->delete('forgotpasswordusers');

		if($query) {
			return true;
		} else {
			return false;
		}
	}

	public function setFirstPassword($email) {

		if (1 == preg_match("/^[a-zA-Z]+\.[a-zA-Z]+\.([0-9][1-9]|[1-9][0-9])@acharya\.ac\.in$/", $email)) {
			$data = array('student_password' => hash ( "sha256", $this->input->post('password')));
			$this->db->where('student_email_id', $email);
			$query = $this->db->update('student', $data);
		}
		else {
			$data = array('password' => hash ( "sha256", $this->input->post('password')));
			$this->db->where('email_id', $email);
			$query = $this->db->update('faculty', $data);
		}
		if($query) {
			return true;
		}
		else {
			return false;
		}
	}
}
?>