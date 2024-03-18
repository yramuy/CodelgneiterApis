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

	}

	public function getCategories()
	{

		$data = $this->Sample_model->get_all();
		// $this->output->set_content_type('application/json')->set_output(json_encode($data));
		$response = array('status' => '1', 'message' => 'Category Details', 'categories' => $data);
		json_output_andriod($response['status'], $response);
	}

	public function saveCategory()
	{
		// $params = $_POST;
		// $category_name = $params['category_name'];

		// Extract JSON Data
		$json = file_get_contents("php://input");
		// Convert The String Of Data To An Array
		$data = json_decode($json, true);

		$inserting = array(
			"name" => $data["category_name"]
		);
		// $category_name = $data['category_name'];
		$result = $this->Sample_model->savecategory($inserting);

		if ($result) {
			$response = array('status' => '1', 'message' => 'Category Saved successfuly');
		} else {
			$response = array('status' => '0', 'message' => 'Category Save failed');
		}

		json_output_andriod($response['status'], $response);
	}

	/**
	 * Description : Mapping all the APIs
	 * Author : Ajit
	 * @param json data
	 * @return json data
	 */
	public  function index()
	{
		// Extract JSON Data
		$json       = file_get_contents("php://input");
		// Convert The String Of Data To An Array
		$data       = json_decode($json, true);
		//echo "hi";
	}



	public function mailSentApi()
	{
		// echo "hello";die();
		$method = $_SERVER['REQUEST_METHOD'];

		if ($method != 'POST') {

			json_output_andriod('fail', array('status' => 'fail', 'message' => 'Bad request.'));
		} else {



			// $config = Array(
			// 'protocol' => 'smtp',
			// 'smtp_host' => 'mail.prospectatech.com',
			// 'smtp_port' => 465,
			// 'smtp_user' => 'entreplan@prospectatech.com', 
			// 'smtp_pass' => '3ntr3pl@n', 
			// 'mailtype' => 'html',
			// 'charset' => 'iso-8859-1',
			// 'wordwrap' => TRUE
			// );

			$config = array(
				'protocol' => 'smtp',
				'smtp_host' => 'cp-wc02.iad01.ds.network',
				'smtp_port' => 465,
				'smtp_user' => 'entreplan@prospectatech.com',
				'smtp_pass' => 'Welc0me@2022',
				'mailtype' => 'html',
				'charset' => 'iso-8859-1',
				'wordwrap' => TRUE
			);
			$this->load->library('email', $config);
			// $this->email->initialize($config);

			$params = $_POST;
			$mailTo = $params['to_mail'];
			$image = $_FILES['attachment']['name'];
			$tmpimage = $_FILES['attachment']['tmp_name'];
			// echo $image;exit;

			// $this->load->library('email', $config);
			$this->email->set_newline("\r\n");
			$this->email->from('entreplan@prospectatech.com');
			$this->email->to($mailTo);
			$this->email->subject('Cabin Crew FAM and CHK Flight');
			$this->email->message('Please Check Attachment');
			$this->email->attach($tmpimage, $image, $image);

			if ($this->email->send()) {
				$response = array('status' => 'success', 'message' => 'Your Mail Sent Successfully ');
			} else {
				$response = array('status' => 'fail', 'message' => 'Mail Send failed');
			}
		}
		json_output($response['status'], $response);
	}


	public function uploadImage($image, $fieldname, $folder)

	{

		// echo $image.",".$fieldname.",".$folder;exit;

		$config = array(

			'file_name' => $image,

			'upload_path' => "./uploads/" . $folder . "/",

			'allowed_types' => "gif|jpg|png|jpeg|pdf|docx",

			'overwrite' => false,

			// 'max_size' => "102400000",

			'overwrite' => TRUE,

		);

		$this->load->library('upload', $config);

		if ($this->upload->do_upload($fieldname)) {

			$result = $this->upload->data();

			return true;
		} else {

			$data = $this->upload->display_errors();

			return false;
		}
	}

	// public function getCategories()
	// {

	// 	// $method = $_SERVER['REQUEST_METHOD'];

	// 	$data = $this->Sample_model->get_all();
	// 	$response = array('status' => '1', 'message' => 'Category Details', 'categories' => $data);
	//   // $this->output->set_content_type('application/json')->set_output(json_encode($data));

	// 	// print_r($data);

	// 	// $response = array('status' => 'success', 'message' => 'Your Mail Sent Successfully', 'res' => array());




	// 	// if ($method != 'POST') {

	// 	// 	json_output_andriod('fail', array('status' => 'fail', 'message' => 'Bad request.'));
	// 	// } else {

	// 	// 	// Extract JSON Data
	// 	// 	$json = file_get_contents("php://input");
	// 	// 	// Convert The String Of Data To An Array
	// 	// 	$data = json_decode($json, true);

	// 	// 	// $data = $_POST;

	// 	// 	$name = $data['username'];
	// 	// 	$psw = $data['password'];

	// 	// 	$res = array('name' => $name, 'psw' => $psw);


	// 	// 	$response = array('status' => 'success', 'message' => 'Your Mail Sent Successfully', 'res' => $res);
	// 	// }

	// 	json_output_andriod($response['status'], $response);
	// }
}
