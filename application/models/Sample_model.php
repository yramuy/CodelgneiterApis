<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sample_model extends CI_Model
{

	public function get_all()
	{
		return $this->db->get('tbl_categories')->result_array();
	}

	public function getCategoryById($id) {
		$this->db->where('id', $id);
		$query = $this->db->get('tbl_categories');
		return $query->result_array();
	}

	public function save_category($data)
	{
		$query = $this->db->insert('tbl_categories', $data);

		return $query;
	}

	public function update_category ($data, $id) {
		$this->db->where('id', $id);
		$query = $this->db->update('tbl_categories', $data);
		return $query;

	}

	public function delete_category($id) {
		$this->db->where('id', $id);
		$query = $this->db->delete('tbl_categories');
		return $query;
	}

}
