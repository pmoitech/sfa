<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends CI_Controller {


	public function index(){
		// load language file
		$this->lang->load('dashboard');
		$data['id_user'] = "user";
		$this->load->view('public/dashboard',$data);
	}
	
}