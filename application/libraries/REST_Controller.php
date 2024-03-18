<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries\REST_Controller.php';

class REST_Controller extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function response($data, $status = 200) {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->_display();

        exit;
    }

    public function index() {
        $this->response(['message' => 'Welcome to RESTful API'], 200);
    }

}
