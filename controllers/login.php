<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {

	function __construct() {
		parent::__construct();	
		$this->load->library(array('session'));
		$this->load->helper(array('url','form'));
		$this->load->model(array('page','user','schools'));
	}

	function index() {
		$data['title'] = 'Login';
		$data = $this->page->page_info($data); //Page
		//$data['session'] = $this->session->all_userdata();
		$url = $this->session->userdata('link') ? $this->session->userdata('link') : base_url();
		
		$data['check_session'] = $this->user->check_login();
		if ($data['check_session']) { //send home if already logged in
			$this->session->unset_userdata('link');
			redirect($url);
		}
		
		//Form
		$validated = true;
		if (isset($_POST['username'])) {
			$username = trim(filter_var($_POST['username'], FILTER_SANITIZE_STRING));
			$data['username'] = $username;
			if (empty($username)) {
				$data['username_error'] = true;
				$validated = false;
			}
			else {
				$data['username_error'] = false;
			}			
		}
		else {
			$validated = false;
		}
		
		if (isset($_POST['password'])) {
			$password = trim(filter_var($_POST['password'], FILTER_SANITIZE_STRING));
			if (empty($password)) {
				$data['password_error'] = true;
				$validated = false;
			}
			else {
				$data['password_error'] = false;
			}			
		}
		else {
			$validated = false;
		}
		
		//Check against server
		if ($validated) {
			$result = $this->user->login($username,$password);
			if ($result) { //Success
				$this->user->session_login($result);
				$this->session->unset_userdata('link');
				redirect($url); //send me home
			}
			else {
				$data['password_error'] = true;
				$data['username_error'] = true;
			}			
		}

		$data['content'] = $this->load->view('login',$data,true);
		$this->load->view('page',$data);
	}
} ?>