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
		// $this->load->database();
		// $this->load->vars($data);

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

	public function updateCategory() {

		$json = file_get_contents("php://input");
		$data = json_decode($json, true);

		$id = $data['id'];

		$update = array(
			"name" => $data['category_name']
		);

		$updateQuery = $this->Sample_model->update_category($update,$id);

		if ($updateQuery) {
			$response = array("statusCode" =>200, 'message' => "Updated successfully");
		} else {
			$response = array("statusCode" =>400, 'message' => "Update failed");
		}

		json_output($response['statusCode'], $response);
	}

	public function deleteCategory() {
		
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
}
