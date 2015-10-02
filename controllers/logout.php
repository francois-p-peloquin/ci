<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Logout extends CI_Controller {

	function __construct() {
		parent::__construct();	
		$this->load->library(array('session'));
		$this->load->helper(array('url','form'));
		$this->load->model(array('page','user'));
	}

	function index() {
		$this->session->sess_destroy();
		setcookie("workshop_editor_user", '', time() -3600); //unset cookie
		redirect(base_url().'login');
	}
} ?>