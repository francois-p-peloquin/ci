<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ajax extends CI_Controller {

	function __construct() {
		parent::__construct();	
		$this->load->library(array('session'));
		$this->load->helper(array('url','form'));
		$this->load->model(array('page','user','workshops'));
		
		if (!$this->user->check_login()) {
			redirect(base_url().'login');
		}
	}
	
	function get_quick_edit_form($type) {
		$data = array();
		$data['add'] = $type == 'add';
		
		if ($type == 'add') {
			$type = 'quick';
		} else if ($type == 'add_cert') {
			$type = 'edit_cert';
		} else if ($type == 'add_partner') {
			$type = 'edit_partner';
		}
		
		if ($type == 'edit_admin') {
			$type = 'edit_user';
			$data['admin'] = true;
		} else if ($type == 'add_user') {
			$type = 'edit_user';
			$data['add'] = true;
		}
		
		if ($type == 'quick') {
			$this->load->model(array('partners','currency','countries','schools','certifications'));
			$data['extras_check'] = $this->workshops->get_extras();
			$data['currency'] = $this->currency->get_currency();
			$data['countries'] = $this->countries->get_countries();
			$data['states'] = $this->countries->get_states();
			$data['general_cert_info'] = $this->certifications->general_cert_info();
			$data['schools'] = $this->schools->get_schools();
			$data['partners'] = $this->partners->get_partners();
			$data['schedules'] = $this->workshops->get_schedules();
			$data['language'] = $this->workshops->get_language();
			$data['payment_options'] = $this->workshops->get_payment_options();
			$data['live_states'] = $this->workshops->get_live_states();
		} 
		else if ($type == 'edit_cert') {
			$this->load->model(array('certifications','modules','schools'));
			$data['schools'] = $this->schools->get_schools();
			$data['modules'] = $this->modules->mod_titles();
		}
		else if ($type == 'edit_user') {
			$this->load->model(array('user'));
			$data['permissions'] = $this->user->get_permissions();
			$data['user_data'] = $this->session->userdata('logged_in'); //normally in model->page
			$data['user'] = ucfirst($data['user_data']['username']); //normally in model->page
			$data['permission'] = $data['user_data']['permission']; //normally in model->page
		}
		
		if ($_SERVER['REMOTE_ADDR'] == '24.84.28.128' && $type == 'quick') {
			$type = 'quick_2';
		}
		$this->load->view('quick_edit/'.$type,$data);
	}
	

	/* PARTNER */
	function get_partner($id) {
		if ($id == 'add_partner') {
			echo '{}';
			return;
		} 
		$this->load->model(array('partners',));
		echo json_encode($this->partners->get_partners($id));
	}
	
	function update_partner($id) {
		$this->load->model(array('partners'));
		echo $this->partners->update_partner($id,$_POST);
	}
	
	function delete_partner($id) {
		$this->load->model(array('partners'));
		echo $this->partners->delete_partner($id);
	}
	
	function create_partner() {
		$this->load->model(array('partners'));
		$id = $this->partners->create_partner($_POST);
		$data = (object) array('id' => $id);
		echo ($id ? json_encode($data) : false);
	}
	/* END PARTNER */
	
	
	/* WORKSHOP */
	function get_row($id) {	
		$r = $this->workshops->get_workshops('id',$id);
		if (empty($r)) {
			echo '{}';
			return;
		} 
		
		$r = $r[0];
		$check_field = array('extras_check','payment_options','language','dates','time');
		foreach ($check_field as $k => $v) {
			if (isset($r[$v])) {
				$r[$v] = explode('|',$r[$v]);
			}
		}
		
		echo json_encode($r);
	}
	
	function clone_workshop($id) {
		$this->load->model(array('page','sort_by'));
		echo $this->workshops->clone_workshop($id,$_GET['link']);
	}

	function archive_workshop($id) {
		$product = $this->workshops->archive_workshop($id);
		echo $product;
	}
	
	function restore_workshop($id) {
		$product = $this->workshops->archive_workshop($id,'archive');
		echo $product;
	}
	
	function delete_workshop($id) {
		$product = $this->workshops->archive_workshop($id,'delete');
		echo $product;
	}
	
	function add_workshop() {
		$this->load->model(array('sort_by','schools','certifications','currency','countries','partners',));
		$id = $this->workshops->add_workshop($_GET);
		
		$data['currency'] = $this->currency->get_currency();
		$data['partners'] = $this->partners->get_partners();
		$data['schedules'] = $this->workshops->get_schedules();
		$data['extras_check'] = $this->workshops->get_extras();
		$data['live_states'] = $this->workshops->get_live_states();
		$data['language'] = $this->workshops->get_language();
		$data['payment_options'] = $this->workshops->get_payment_options();
		$data['schools'] = $this->schools->get_schools();
		$data['general_cert_info'] = $this->certifications->general_cert_info();

		$ws = $this->workshops->get_workshops('id',$id);
		$ws = $this->workshops->workshop_arr($ws[0]);
		
		echo '{"id": "'.$id.'", "class" : '.json_encode($this->sort_by->single_list($ws,$data)).',"data-search": '.json_encode($this->sort_by->single_search($ws,$data)).',"index": "'.$this->workshops->get_index($id).'"}';
	}
	
	function update_workshop($id) {
		$this->load->model(array('sort_by','schools','certifications','currency','countries','partners',));
		$q = $_GET;
		
		$up = $this->workshops->update_workshop($id,$q);
		if (!$up) {
			echo '0';
			return;
		}
		
		//Get attributes to update
		$data['currency'] = $this->currency->get_currency();
		$data['partners'] = $this->partners->get_partners();
		$data['schedules'] = $this->workshops->get_schedules();
		$data['extras_check'] = $this->workshops->get_extras();
		$data['live_states'] = $this->workshops->get_live_states();
		$data['language'] = $this->workshops->get_language();
		$data['payment_options'] = $this->workshops->get_payment_options();
		$data['schools'] = $this->schools->get_schools();
		$data['general_cert_info'] = $this->certifications->general_cert_info();
		
		$ws = $this->workshops->get_workshops('id',$id);
		$ws = $this->workshops->workshop_arr($ws[0]);
		
		$index = isset($_GET['dates']) ? ',"index": "'.$this->workshops->get_index($id).'"' : '';
		
		echo '{"class" : '.json_encode($this->sort_by->single_list($ws,$data)).',"data-search": '.json_encode($this->sort_by->single_search($ws,$data)).$index.'}';
	}
	
	function get_change_log($id) {
		$this->load->model(array('partners'));
		
		//Log
		$p1 = $this->workshops->get_change_log($id);
		
		//Add Creation Date as Last
		$q2 = 'SELECT creation_date FROM '.WORKSHOP_DB.' WHERE id = '.$id.' LIMIT 1;';
		$p2 = $this->db->query($q2);
		$p2 = $p2->result_array();
		$p2 = array(
			count($p1) => array(
				'data' => array(
					'created' => $p2[0]['creation_date'],
				),
				'time' => $p2[0]['creation_date'],
			)
		);
		
		echo json_encode(array_merge($p1,$p2));
	}
	
	function get_dash_class($id) {
		$this->load->model(array('sort_by','schools','certifications'));
		$data['schools'] = $this->schools->get_schools();
		$data['general_cert_info'] = $this->certifications->general_cert_info();
		$_GET['dates'] = str_replace(',','|',$_GET['dates']);
		echo $this->sort_by->single_list($_GET,$data);
	}
	
	function check_link_availability() {
		echo $this->workshops->check_link_availability($_GET['certification'],$_GET['link']);
	}
	/* END WORKSHOP */
	
	
	/* CERT */
	function get_cert_row($id) {
		$this->load->model(array('certifications'));
		if ($id == 'add_cert') {
			echo '{}';
			return;
		} 
		$r1 = $this->certifications->general_cert_info(false,array('id' => $id));
		echo json_encode($r1[$id]); //NWASAP
		
	}
	
	function update_cert($id) {
		$this->load->model(array('certifications'));
		echo $this->certifications->update_cert($id,$_GET);
	}
	
	function create_cert() {
		$this->load->model(array('certifications'));
		$data = (object) array('id' => $this->certifications->create_cert($_GET));
		echo json_encode($data);
	}
	
	function clone_cert($id) {
		$this->load->model(array('certifications'));
		echo $this->certifications->clone_cert($id);
	}
	
	function delete_cert($id) {
		$this->load->model(array('certifications'));
		echo $this->certifications->delete_cert($id);
	}
	
	function get_all_certifications() {
		echo json_encode($this->workshops->general_cert_info());
	}
	/* END CERT */

	
	/* USER */
	function get_user_row($id) {
		$this->load->model(array('user'));
		if ($id == 'add_user') {
			echo '{}';
			return;
		} 
		$r1 = $this->user->get_users($id);
		echo json_encode($r1[0]); //NWASAP
		
	}

	function create_user() {
		$this->load->model(array('user'));
		$data = (object) array('id' => $this->user->create_user($_POST));
		echo json_encode($data);
	}

	function delete_user($id) {
		$this->load->model(array('user'));
		echo $this->user->delete_user($id);	}
	
	function check_password($id) {
		$this->load->model(array('user'));
		echo ($this->user->check_password($id,$_POST['password']) ? 1 : 0);
	}
	
	function check_user_name($id) {
		$this->load->model(array('user'));
		$id = $id == 'add_user' ? false : $id;
		echo ($this->user->check_user_name($id,$_POST) ? 0 : 1);
	}
	
	function update_user($id) {
		$this->load->model(array('user'));
		echo $this->user->update_user($id,$_POST);
	}
	/* END USER */
	
	
	function get_workshops_prepped($request) {
		$this->load->model(array('certifications','schools'));
		$data = array();
		$data['schools'] = $this->schools->get_schools();
		$data['general_cert_info'] = $this->certifications->general_cert_info();
		$workshops = $this->workshops->get_workshops();
		$data['workshops'] = $this->workshops->order_workshops($workshops);
		echo $this->workshops->workshops_prepped($data,$data['workshops'],$request);
	}
	
	function get_all_partners() {
		$this->load->model(array('partners'));
		echo json_encode($this->partners->get_partners());
	}
	
	function get_logs($req = false) { //store
		$this->load->model(array('store_logs'));
		if ($req) {
			echo file_get_contents('../samples/'.$req);
		} else { //get list of all
			$order = array();
			foreach (scandir('../samples/') as $k => $v) {
				if ($v != '.' && $v != '..') {
					$d = filemtime('../samples/'.$v);
					if (isset($order[$d])) {
						$d++;
					}
					$order[$d] = array(
						'id' => $this->store_logs->get_id($v),
						'admin' => !$this->store_logs->get_ext($v),
						'file' => $v,
						'time' => $d,
					);
				}
				// $arr = $this->store_logs->update_id($id);
			}
			ksort($order);
			$order_2 = array_reverse($order);
			$order_3 = (object) array_slice($order_2,intval($_POST['start']),25,true);
			$end = end($order_3) == end($order_2) ? 1 : 0;
			$result = (object) array('end' => $end,'result' => $order_3);
			echo json_encode($result);
		}
	}
	
	function get_log_pdf($req) {
		$this->load->model(array('store_logs'));
		$this->load->helper(array('dompdf',));
		$file = file_get_contents('../samples/'.$req);
		$file = preg_replace('/width="750"/','width="100%"',$file);
		$file = preg_replace('/width="800"/','width="100%"',$file);
		$file = str_replace('<body>','<body><h3>'.$_GET['title'].'</h3>',$file);

		create_pdf($file,$_GET['title'],true);
	}
	
	function get_order_logs($req = false) {
		$this->load->model(array('store_logs'));
		if ($req) { //single
			$this->load->model(array('countries','schools','certifications','modules','currency','credit_card','books'));
			$fill = $this->store_logs->get_order_logs($req);
			$data['currency'] = $this->currency->get_currency();
			$data['countries'] = $this->countries->get_countries();
			$data['states'] = $this->countries->get_states();
			$data['general_cert_info'] = $this->certifications->general_cert_info();
			$data['schools'] = $this->schools->get_schools();
			$data['certifications'] = $this->certifications->general_cert_info(false,array('school' => $fill['school']));
			$data['modules'] = $this->modules->get_modules($fill['school']);
			$data['books'] = $this->books->get_books();
			$data['form'] = $this->store_logs->get_form();
			$data['prices'] = $this->store_logs->get_prices();
			
			echo $this->store_logs->render_order_log($fill,$data); //create file
		} else { //list
			echo json_encode($this->store_logs->get_order_logs($req,$_GET['start']));
		}
	}
	
	function get_order_pdf($req) {
		$this->load->model(array('store_logs','countries','schools','certifications','modules','currency','credit_card','books'));
		$this->load->helper(array('dompdf',));
		$fill = $this->store_logs->get_order_logs($req);
		$data['currency'] = $this->currency->get_currency();
		$data['countries'] = $this->countries->get_countries();
		$data['states'] = $this->countries->get_states();
		$data['general_cert_info'] = $this->certifications->general_cert_info();
		$data['schools'] = $this->schools->get_schools();
		$data['certifications'] = $this->certifications->general_cert_info(false,array('school' => $fill['school']));
		$data['modules'] = $this->modules->get_modules($fill['school']);
		$data['books'] = $this->books->get_books();
		$data['form'] = $this->store_logs->get_form();
		$data['prices'] = $this->store_logs->get_prices();
		
		$pdf = '
		<html>
			<head><style type="text/css">'.file_get_contents('static/css/orders.css').'</style></head>
			<body><h3>'.str_replace(' - Order','<br />Order',$_GET['title']).'</h3>'.$this->store_logs->render_order_log($fill,$data).'</body>
		</html>';
		create_pdf($pdf,$_GET['title'],true);
	}
	
	function process_order($id) {
		$this->load->model(array('store_logs','credit_card'));
		echo $this->store_logs->process_order($id);
	}
	
	function cancel_order($id) {
		$this->load->model(array('store_logs','credit_card'));
		echo $this->store_logs->cancel_order($id);
	}
	
	function delete_order($id) {
		$this->load->model(array('store_logs'));
		echo $this->store_logs->delete_order($id);
	}

	function update_store_url($id) {
		$this->load->model(array('schools'));
		if ($this->schools->update_store_url($id,$_POST['url'])) {
			echo $_POST['url'];
		}
	}
}