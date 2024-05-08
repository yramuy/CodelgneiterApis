<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Class Name : Rsapi Controller
 * Description : Used to handle all APIs
 * @author Ajit
 * @createddate : Jun 10, 2016
 * @modificationlog : Adding comments and cleaning the code
 * @change on Mar 16, 2017
 */


class App_Api extends CI_Controller
{
	/**
	 * Initializing variable
	 */

	protected $token;
	/**
	 * Responsable for auto load the the models
	 * Responsable for auto load helpers
	 * Responsable for auto load libraries
	 * Defining the timezone
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		// Load API Model
		$this->load->database();
		// $this->load->vars($data);
		// Extract JSON Data
		$json = file_get_contents("php://input");
		// Convert The String Of Data To An Array
		$this->data = json_decode($json, true);
	}

	public function getCategories()
	{
		// Extract JSON Data
		$json = file_get_contents("php://input");
		// Convert The String Of Data To An Array
		$data = json_decode($json, true);

		if (isset($data['id'])) {
			$data = $this->Sample_model->getCategoryById($data['id']);
		} else {
			$data = $this->Sample_model->get_all();
		}

		$response = array('statusCode' => '200', 'message' => 'Category Details', 'categories' => $data);
		json_output($response['statusCode'], $response);
	}

	public function saveCategory()
	{
		// Extract JSON Data
		$json = file_get_contents("php://input");
		// Convert The String Of Data To An Array
		$data = json_decode($json, true);

		$inserting = array(
			"name" => $data["category_name"]
		);
		// $category_name = $data['category_name'];
		$result = $this->Sample_model->save_category($inserting);

		if ($result) {
			$response = array('status' => '200', 'message' => 'Category Saved successfuly');
		} else {
			$response = array('status' => '0', 'message' => 'Category Save failed');
		}

		json_output($response['status'], $response);
	}

	public function updateCategory()
	{

		$json = file_get_contents("php://input");
		$data = json_decode($json, true);

		$id = $data['id'];

		$update = array(
			"name" => $data['category_name']
		);

		$updateQuery = $this->Sample_model->update_category($update, $id);

		if ($updateQuery) {
			$response = array("statusCode" => 200, 'message' => "Updated successfully");
		} else {
			$response = array("statusCode" => 400, 'message' => "Update failed");
		}

		json_output($response['statusCode'], $response);
	}

	public function deleteCategory()
	{

		$json = file_get_contents("php://input");
		$data = json_decode($json, true);

		$id = $data['id'];

		$deleteQry = $this->Sample_model->delete_category($id);

		if ($deleteQry) {
			$response = array('statusCode' => 200, 'message' => "Deleted successfully");
		} else {
			$response = array('statusCode' => 200, 'message' => "Delete failed");
		}

		json_output($response['statusCode'], $response);
	}

	public function employees()
	{
		$employees = $this->Sample_model->get_employees();
		$response = array("statusCode" => 200, "message" => "Employee Details", "employees" => $employees);

		json_output($response['statusCode'], $response);
	}

	public function addTimesheet()
	{

		$weekDates = $this->getWeekDates($this->data['from_date'], $this->data['to_date']);
		$datesData = array();
		foreach ($weekDates as $weekRange) {
			array_push($datesData, $weekRange);
		}

		$response = array('statusCode' => 200, 'message' => '');

		if (!empty($datesData)) {
			for ($i = 0; $i < sizeof($datesData); $i++) {

				$start_date = $datesData[$i]['start'];
				$end_date = $datesData[$i]['end'];
				$employeeId = $this->data['emp_number'];

				$checkTimesheetCnt = $this->Sample_model->check_timesheet($start_date, $end_date, $employeeId);

				// echo $checkTimesheet;

				if ($checkTimesheetCnt == 0) {
					$insert = array(
						"state" => "NOT SUBMITTED",
						"start_date" => $start_date,
						"end_date" => $end_date,
						"employee_id" => $employeeId
					);

					$this->db->insert('erp_timesheet', $insert);
					$timesheetLastId = $this->db->insert_id();

					$updateUniqueTimesheetLog = array(
						'last_id' => $timesheetLastId
					);
					$this->db->where('field_name', 'timesheet_id');
					$this->db->update('hs_hr_unique_id', $updateUniqueTimesheetLog);

					$response = array('statusCode' => 200, 'message' => 'Timesheets added successfully!');
				} else {
					$response = array('statusCode' => 200, 'message' => 'This timesheets already existed!');
				}
			}
		} else {
			$response = array('statusCode' => 200, 'message' => 'Dates should not be empty!');
		}

		json_output($response['statusCode'], $response);
	}

	function getWeekDates($fromDate, $toDate)
	{
		$weekDates = array();

		$fromDateObj = new DateTime($fromDate);
		$toDateObj = new DateTime($toDate);

		while ($fromDateObj <= $toDateObj) {
			$weekStart = clone $fromDateObj;
			$weekStart->modify('this week');
			$weekEnd = clone $weekStart;
			$weekEnd->modify('+6 days');

			$weekDates[] = array(
				'start' => $weekStart->format('Y-m-d'),
				'end' => $weekEnd->format('Y-m-d')
			);

			$fromDateObj->modify('+1 day');
		}

		// Remove duplicate dates
		$weekDates = array_unique($weekDates, SORT_REGULAR);

		return $weekDates;
	}

	function projects()
	{
		$projects = $this->Sample_model->get_projects();

		$response = array('statusCode' => 200, 'projectDetails' => $projects);

		json_output($response['statusCode'], $response);
	}

	function activitiesByProjectId()
	{
		$projectId = $this->data['project_id'];

		$activities = $this->Sample_model->get_activities($projectId);

		$response = array('statusCode' => 200, 'activities' => $activities);

		json_output($response['statusCode'], $response);
	}

	function saveTimesheetItem()
	{
		$employeeId = $this->data['employee_id'];
		$fromDate = $this->data['from_date'];
		$toDate = $this->data['to_date'];
		$state = 'NOT SUBMITTED';
		$projectId = $this->data['project_id'];
		$activityId = $this->data['activity_id'];
		$taskId = $this->data['task_id'];

		$timesheets = $this->Sample_model->getNotSubmittedTimesheets($employeeId, $fromDate, $toDate, $state);

		// print_r($timesheets);
		// die;

		if (sizeof($timesheets) > 0) {
			for ($i = 0; $i < sizeof($timesheets); $i++) {
				$user = $this->Sample_model->getUserDetails($employeeId);

				$myUserId = $user[0]['myUserId'];
				$supUserId = $user[0]['supUserId'];

				$date1 = date_create(date('Y-m-d', strtotime($timesheets[$i]['start_date'])));
				$date2 = date_create(date('Y-m-d', strtotime($timesheets[$i]['end_date'])));
				$timesheetId = $timesheets[$i]['timesheet_id'];

				$diff = date_diff($date1, $date2);

				$cntdays = $diff->format("%a");

				$dates = array();

				for ($j = 0; $j <= $cntdays - 2; $j++) {
					$timesheetDate = date('Y-m-d', strtotime($timesheets[$i]['start_date'] . ' +' . $j . 'days'));
					array_push($dates, $timesheetDate);

					// Leave Query
					$leave = $this->Sample_model->getLeaveData($employeeId, $timesheetDate);

					// Holiday Query
					$holiday = $this->Sample_model->getHolidyData($timesheetDate);

					// Check Timesheet
					$timesheetItemCheck = $this->Sample_model->checkTimesheetItem($employeeId, $timesheetDate);

					if (empty($timesheetItemCheck)) {
						if (!empty($leave)) {
							if ($leave[0]["length_hours"] < 8.0) {
								$leaveData = array(
									"timesheet_id" => $timesheetId,
									"date" => $timesheetDate,
									"duration" => '14400',
									"project_id" => '12',
									"employee_id" => $employeeId,
									"activity_id" => '181',
									"task_id" => $taskId
								);

								$this->db->insert('erp_timesheet_item', $leaveData);

								$halfWorkingDayData = array(
									"timesheet_id" => $timesheetId,
									"date" => $timesheetDate,
									"duration" => '14400',
									"project_id" => $projectId,
									"employee_id" => $employeeId,
									"activity_id" => $activityId,
									"task_id" => $taskId
								);

								$this->db->insert('erp_timesheet_item', $halfWorkingDayData);

								$timesheetItemlastId = $this->db->insert_id();
							} else {
								$fullHoliDayData = array(
									"timesheet_id" => $timesheetId,
									"date" => $timesheetDate,
									"duration" => '28800',
									"project_id" => '12',
									"employee_id" => $employeeId,
									"activity_id" => '181',
									"task_id" => $taskId
								);

								$this->db->insert('erp_timesheet_item', $fullHoliDayData);

								$timesheetItemlastId = $this->db->insert_id();
							}

							$response = array('statusCode' => 200, 'message' => 'Leave Data saves successsfully');
						} else if (!empty($holiday)) {
							$holiDayData = array(
								"timesheet_id" => $timesheetId,
								"date" => $timesheetDate,
								"duration" => '28800',
								"project_id" => '12',
								"employee_id" => $employeeId,
								"activity_id" => '192',
								"task_id" => $taskId
							);

							$this->db->insert('erp_timesheet_item', $holiDayData);

							$timesheetItemlastId = $this->db->insert_id();

							$response = array('statusCode' => 200, 'message' => 'Holiday Data saves successsfully');
						} else {
							$fullWorkingDayData = array(
								"timesheet_id" => $timesheetId,
								"date" => $timesheetDate,
								"duration" => '28800',
								"project_id" => $projectId,
								"employee_id" => $employeeId,
								"activity_id" => $activityId,
								"task_id" => $taskId
							);

							$this->db->insert('erp_timesheet_item', $fullWorkingDayData);

							$timesheetItemlastId = $this->db->insert_id();

							$response = array('statusCode' => 200, 'message' => 'Timesheet Data saved successsfully');
						}
					} else {
						$response = array('statusCode' => 200, 'message' => 'No Timesheet Items found');
					}
				}

				if (!empty($timesheetId)) {

					$timesheetLogInsert = array(
						"action" => 'SUBMITTED',
						"date_time" => $timesheetDate,
						"performed_by" => $myUserId,
						"timesheet_id" => $timesheetId
					);

					$this->db->insert('erp_timesheet_action_log', $timesheetLogInsert);

					$updateTimesheet = array(
						'state' => 'APPROVED'
					);
					$this->db->where('timesheet_id', $timesheetId);
					$this->db->update('erp_timesheet', $updateTimesheet);

					$timesheetLogApproveInsert = array(
						"action" => 'APPROVED',
						"date_time" => $timesheetDate,
						"performed_by" => '1',
						"timesheet_id" => $timesheetId
					);

					$this->db->insert('erp_timesheet_action_log', $timesheetLogApproveInsert);

					$timesheetItemLoglastId = $this->db->insert_id();

					$updateUniqueTimesheetItem = array(
						'last_id' => $timesheetItemlastId
					);
					$this->db->where('field_name', 'timesheet_item_id');
					$this->db->update('hs_hr_unique_id', $updateUniqueTimesheetItem);

					$updateUniqueTimesheetLog = array(
						'last_id' => $timesheetItemLoglastId
					);
					$this->db->where('field_name', 'timesheet_action_log_id');
					$this->db->update('hs_hr_unique_id', $updateUniqueTimesheetLog);

					$response = array('statusCode' => 200, 'message' => 'Timesheet Item and Log saved successsfully');
				}
			}
		} else {
			$response = array('statusCode' => 200, 'message' => 'No Timesheets found');
		}

		json_output($response['statusCode'], $response);
	}

	function standards()
	{
		$output = array();

		$standards = $this->Sample_model->getStandards();

		$response = array('statusCode' => 200, 'standards' => $standards);

		json_output($response['statusCode'], $response);
	}

	function clauses()
	{

		$id = $this->data['id'];

		$clauses = $this->Sample_model->getClauses($id);

		$response = array('statusCode' => 200, 'clauses' => $clauses);

		json_output($response['statusCode'], $response);
	}

	function SaveCheckList()
	{
		$checkListData = array(
			"standard_id" => $this->data['standardId'],
			"clause_id" => $this->data['clauseId'],
			"parent_id" => $this->data['clauseId'],
			"level" => $this->data['level'],
			"clause_no" => $this->data['clauseNo'],
			"display_order" => $this->data['displayOrder'],
			"clause_main_text" => $this->data['mainText'],
			"sub_text" => $this->data['comment'],
			"input_field_required" => $this->data['inputField'],
			"input_field_type" => $this->data['inputFieldType'],
			"data_type" => $this->data['dataType'],
			"master_data" => $this->data['masterData'],
			"is_comment_require" => $this->data['commentField'],
			"is_upload_file_require" => $this->data['fileUploadField'],
			"non_conformative" => $this->data['nonConformance'],
			"is_active" => $this->data['isActive']
		);

		$result = $this->Sample_model->save_checkList($checkListData);

		if ($result) {
			$response = array('status' => '200', 'message' => 'CheckList Saved successfuly');
		} else {
			$response = array('status' => '0', 'message' => 'CheckList Save failed');
		}

		json_output($response['status'], $response);
	}

	function checklist()
	{
		$level = $this->data['level'];
		$standard = $this->data['standard'];
		$clause = $this->data['clause'];
		$parentId = 0;

		$checklist = $this->Sample_model->getChecklist($level,$standard,$clause,$parentId);

		$response = array('statusCode' => 200, 'level1CheckList' => $checklist);

		json_output($response['statusCode'], $response);
	}
}
