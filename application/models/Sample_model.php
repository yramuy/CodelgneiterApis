<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sample_model extends CI_Model
{

	public function get_all()
	{
		return $this->db->get('tbl_categories')->result_array();
	}

	public function getCategoryById($id)
	{
		$this->db->where('id', $id);
		$query = $this->db->get('tbl_categories');
		return $query->result_array();
	}

	public function save_category($data)
	{
		$query = $this->db->insert('tbl_categories', $data);

		return $query;
	}

	public function update_category($data, $id)
	{
		$this->db->where('id', $id);
		$query = $this->db->update('tbl_categories', $data);
		return $query;
	}

	public function delete_category($id)
	{
		$this->db->where('id', $id);
		$query = $this->db->delete('tbl_categories');
		return $query;
	}

	public function get_employees()
	{
		$this->db->select('emp_number,concat(emp_firstname," ",emp_lastname) as full_name');
		$this->db->from('hs_hr_employee');
		$this->db->where('termination_id', null);
		$query = $this->db->get();
		$result = $query->result_array();
		return $result;
	}

	public function save_timesheet($data)
	{
		$query = $this->db->insert('erp_timesheet', $data);
		return $query;
	}

	public function check_timesheet($start_date, $end_date, $employeeId)
	{
		$this->db->select('*');
		$this->db->from('erp_timesheet');
		$this->db->where('start_date', $start_date);
		$this->db->where('end_date', $end_date);
		$this->db->where('employee_id', $employeeId);
		$query = $this->db->get();
		$row_count = $query->num_rows();
		return $row_count;
	}

	function get_projects()
	{
		$this->db->select('project_id,customer_id,name');
		$this->db->from('erp_project');
		// $this->db->where('is_active', 0);
		$this->db->where('is_deleted', 0);
		$query = $this->db->get();
		$result = $query->result_array();

		return $result;
	}

	function get_activities($id)
	{
		$this->db->select('activity_id,project_id,name');
		$this->db->from('erp_project_activity');
		$this->db->where('project_id', $id);
		$this->db->where('is_deleted', 0);
		$query = $this->db->get();
		$result = $query->result_array();

		return $result;
	}

	function getNotSubmittedTimesheets($employeeId, $fromDate, $toDate, $state)
	{
		$this->db->select('*');
		$this->db->from('erp_timesheet');
		$this->db->where('employee_id', $employeeId);
		$this->db->where('state', $state);
		$this->db->where('start_date >=', $fromDate);
		$this->db->where('end_date <=', $toDate);
		$query = $this->db->get();
		$result = $query->result_array();

		return $result;
	}

	function getUserDetails($employeeId)
	{
		$this->db->select('u.id as myUserId, (SELECT us.id FROM hs_hr_emp_reportto r LEFT JOIN erp_user us ON us.emp_number = r.erep_sup_emp_number 
		WHERE r.erep_sub_emp_number=u.emp_number AND r.erep_reporting_mode = 1 LIMIT 1) as supUserId');
		$this->db->from('erp_user as u');
		$this->db->where('u.emp_number', $employeeId);
		$this->db->where('u.deleted', 0);
		$query = $this->db->get();
		$result = $query->result_array();
		return $result;
	}
	function getLeaveData($employeeId, $timesheetDate)
	{
		$this->db->select('*');
		$this->db->from('erp_leave');
		$this->db->where('emp_number', $employeeId);
		$this->db->where('date', $timesheetDate);
		$query = $this->db->get();
		$result = $query->result_array();
		return $result;
	}
	function getHolidyData($timesheetDate)
	{
		$this->db->select('*');
		$this->db->from('erp_holiday');
		$this->db->where('date', $timesheetDate);
		$query = $this->db->get();
		$result = $query->result_array();
		return $result;
	}
	function checkTimesheetItem($employeeId, $timesheetDate)
	{
		$this->db->select('*');
		$this->db->from('erp_timesheet_item');
		$this->db->where('date', $timesheetDate);
		$this->db->where('employee_id', $employeeId);
		$query = $this->db->get();
		$result = $query->result_array();
		return $result;
	}

	function getStandards()
	{
		$this->db->select('*');
		$this->db->from('tbl_standards');
		$query = $this->db->get();
		$result = $query->result_array();

		return $result;
	}

	function getClauses($id)
	{
		$this->db->select('*');
		$this->db->from('tbl_checklist');
		$this->db->where('standard_id', $id);
		$this->db->where('is_active', 1);
		$query = $this->db->get();
		$result = $query->result_array();

		return $result;
	}

	function save_checkList($checkListData)
	{
		$query = $this->db->insert('tbl_checklist', $checkListData);

		return $query;
	}

	function getChecklist($level, $standard, $clause, $parentId)
	{
		$query = $this->LevelDataQuery($level, $standard, $clause, $parentId);
		$result = $query->result_array();
		$rowCnt = $query->num_rows();
		$output1 = array();

		for ($i = 0; $i < $rowCnt; $i++) {
			$output = $this->getCheckListData($result[$i]);
			$output['level2CheckList'] = $this->getLevel2Data($result[$i]['standard_id'], $result[$i]['clause_id'], 2, $result[$i]['id']);
			$output1[] = $output;
		}

		return $output1;
	}

	function getLevel2Data($standardId, $clauseId, $level, $id)
	{
		$query = $this->LevelDataQuery($level, $standardId, $clauseId, $id);
		$result = $query->result_array();
		$rowCnt = $query->num_rows();

		$output1 = array();

		for ($i = 0; $i < $rowCnt; $i++) {
			$output = $this->getCheckListData($result[$i]);
			$output['level3CheckList'] = $this->getLevel3Data($result[$i]['standard_id'], $result[$i]['clause_id'], 3, $result[$i]['id']);

			$output1[] = $output;
		}

		return $output1;
	}
	function getLevel3Data($standardId, $clauseId, $level, $id)
	{
		$query = $this->LevelDataQuery($level, $standardId, $clauseId, $id);
		$result = $query->result_array();
		$rowCnt = $query->num_rows();

		$output1 = array();

		for ($i = 0; $i < $rowCnt; $i++) {
			$output = $this->getCheckListData($result[$i]);
			$output1[] = $output;
		}

		return $output1;
	}

	function LevelDataQuery($level, $standard, $clause, $parentId)
	{
		$this->db->select('s.name as standard,cl.name as clause,ch.*');
		$this->db->from('tbl_checklist as ch');
		$this->db->join('tbl_standards as s', 's.id = ch.standard_id', 'left');
		$this->db->join('tbl_clauses as cl', 'cl.id = ch.clause_id', 'left');
		$this->db->where('ch.level', $level);
		$this->db->where('ch.parent_id', $parentId);

		if ($level != 1) {
			$this->db->where('ch.standard_id', $standard);
			// $this->db->where('ch.clause_id', $clause);
		}

		$query = $this->db->get();

		return $query;
	}

	function getCheckListData($row)
	{
		$output['id'] = $row['id'];
		$output['standard'] = $row['standard'];
		$output['clause'] = $row['clause'];
		$output['clause_no'] = $row['clause_no'];
		$output['clause_id'] = $row['clause_id'];
		$output['parent_id'] = $row['parent_id'];
		$output['clause_main_text'] = $row['clause_main_text'];
		$output['input_field_required'] = $row['input_field_required'];
		$output['level'] = $row['level'];
		if ($row['input_field_required'] == 1) {
			$output['input_field_required'] = $row['input_field_required'];
			$output['input_field_type'] = $row['input_field_type'];
			$output['data_type'] = $row['data_type'];
			$output['master_data'] = $row['master_data'];
			$output['is_comment_require'] = $row['is_comment_require'];
			$output['is_upload_file_require'] = $row['is_upload_file_require'];
			$output['non_conformative'] = $row['non_conformative'];
			$output['dropdownData'] = $this->getDropdownData($row['master_data']);
		}

		return $output;
	}

	function getDropdownData($id)
	{
		$this->db->select('*');
		$this->db->from('tbl_master_data');
		$this->db->where('rating_id', $id);
		$query = $this->db->get();
		$result = $query->result_array();

		return $result;
	}
}
