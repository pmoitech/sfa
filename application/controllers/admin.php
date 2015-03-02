<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends CI_Controller {
	var $title = 'Login';
	var $error = NULL;
	var $enterprise = NULL;
	var $userconfig = NULL;

	function __construct()
	{
		parent::__construct();
		
		if(!$this->userconfig = $this->session->userdata('userconfig')){
			redirect($user->lang.'login'); 
		}
		
		// load language file
		$this->lang->load('dashboard');
		$this->load->model('usuarios');
		$this->load->database();
		$this->load->helper('url');
		$this->load->library('grocery_CRUD');	
	}
	
	function _admin_output($output = null)
	{
		$this->load->view('private/admin.php',$output);	
	}
	
	
	
	function index()
	{
		if($this->userconfig->perfil=='ADMIN')
			$this->user_management();
		else
			if($this->userconfig->perfil=='CALL')
				//$this->_admin_output((object)array('output' => '' , 'js_files' => array() , 'css_files' => array() , 'op' => '' ));
				$this->user_managervehicle();

			else
				if($this->userconfig->perfil=='CUST')
					$this->_admin_output((object)array('output' => '' , 'js_files' => array() , 'css_files' => array() , 'op' => '' ));
	}	
	
	function encrypt_password_callback($post_array) {

		if(!empty($post_array['clave']))
		{
		    $post_array['clave'] = md5($post_array['clave']);
		}
		else
		{
		    unset($post_array['clave']);
		}
	    return $post_array;

    }  



    function set_password_input_to_empty() {
    	return "<input type='password' name='clave' value='' />";
	}

	function set_user_call() {
    	return "<input type='hidden' name='perfil' value='CALL' />";
	}
	
	function set_user_cust() {
    	return "<input type='hidden' name='perfil' value='CUST' />";
	}

	function set_user_admin() {
    	return "<input type='hidden' name='perfil' value='ADMIN' />";
	}

	function set_user_sucursal() {
    	return "<input type='hidden' name='idsucursal' value='-1' />";
	}
	
	
	function user_management()
	{
		if($this->userconfig->perfil=='ADMIN'){
	
			$crud = new grocery_CRUD();

			$crud->set_theme('datatables');
			$crud->set_table('usuarios');
			$crud->set_subject('Administrador del sistema');
			$crud->columns('nombre','codigo','ciudad','direccion','telefono');
			$crud->fields('nombre','codigo','clave','pais','departamento','ciudad','direccion','telefono');
			$crud->required_fields('nombre','codigo','pais','departamento','ciudad','direccion','telefono');
			$crud->display_as('codigo', 'Login');
			$crud->display_as('departamento', 'Provincia');

			$crud->change_field_type('clave', 'password');
			
        	$crud->callback_edit_field('clave',array($this,'set_password_input_to_empty'));
    		$crud->callback_add_field('clave',array($this,'set_password_input_to_empty'));

    		
    		$crud->callback_before_update(array($this,'encrypt_password_callback'));
    		$crud->callback_before_insert(array($this,'encrypt_password_callback'));

    		$crud->callback_edit_field('perfil',array($this,'set_user_admin'));
    		$crud->callback_add_field('perfil',array($this,'set_user_admin'));
			
			$crud->callback_edit_field('idsucursal',array($this,'set_user_sucursal'));
    		$crud->callback_add_field('idsucursal',array($this,'set_user_sucursal'));
 			 			

			$crud->where('perfil =', 'ADMIN');
			
			$output = $crud->render();
			$output -> op = 'user_management';
						
			$this->_admin_output($output);
		}else{
			$this->close();			
		}
	}

	function office_management()
	{
		if($this->userconfig->perfil=='ADMIN'){
	
			$crud = new grocery_CRUD();

			$this->load->library('googlemaps');

			$crud->set_theme('datatables');
			$crud->set_table('sucursales');
			$crud->set_subject('sucursales');
			$crud->columns('nombre');
			$crud->fields('nombre','latitud','longitud');
			$crud->required_fields('nombre');
			$crud->display_as('nombre', 'Nombre sucursal');
			
			$output = $crud->render();
			$output -> op = 'office_management';

			$state = $crud->getState();
	    	$state_info = $crud->getStateInfo();
	 		$primary_key='-1';
	    	if($state == 'edit')
	    	{
	        	$this->load->model('sqlexteded');
	        	$primary_key = $state_info->primary_key;
	        	$sucursal = $this->sqlexteded->getLatLngOficce($primary_key);
	        	$config['center'] =  $sucursal->latitud.', '.$sucursal->longitud; 
				$config['zoom'] = '14';
				$config['map_width'] = '98%';
				
				//$config['onclick'] = 'alert(\'You just clicked at: \' + event.latLng.lat() + \', \' + event.latLng.lng());';
				$this->googlemaps->initialize($config);
				
				$marker = array();
				$marker['position']		= $config['center'];
				$marker['draggable']	= TRUE;
				$marker['animation']	= 'DROP';
				$marker['icon']		 	=  base_url() . 'assets/images/colegio.png';	
				$marker['ondragend'] 	= "$('#field-latitud').val(event.latLng.lat()); $('#field-longitud').val(event.latLng.lng());";
				$this->googlemaps->add_marker($marker);

				$output->map = $this->googlemaps->create_map();
			
	        	
	    	}
	    	$output->state = $state;
	    	$output->primary_key = $primary_key;

			$this->_admin_output($output);
		
		}else{
			$this->close();
		}
	}

	function user_callcenter()
	{
		if($this->userconfig->perfil=='ADMIN'){
	
			$crud = new grocery_CRUD();

			$crud->set_theme('datatables');
			$crud->set_table('usuarios');
			$crud->set_subject('Coordinadores');
			$crud->columns('nombre','idsucursal','codigo','ciudad');
			$crud->fields('nombre','idsucursal','codigo','clave','pais','departamento','ciudad','direccion','telefono','perfil');
			$crud->required_fields('nombre','idsucursal','codigo','pais','departamento','ciudad','direccion','telefono','perfil');
			$crud->display_as('codigo', 'Login');
			$crud->display_as('departamento', 'Provincia');
			
			$crud->set_relation('idsucursal', 'sucursales', 'nombre');
			$crud->display_as('idsucursal', 'Sucursal');
			
			$crud->change_field_type('clave', 'password');
			$crud->change_field_type('perfil', 'hidden');
			
        	$crud->callback_edit_field('clave',array($this,'set_password_input_to_empty'));
    		$crud->callback_add_field('clave',array($this,'set_password_input_to_empty'));
 			
 			$crud->callback_edit_field('perfil',array($this,'set_user_call'));
    		$crud->callback_add_field('perfil',array($this,'set_user_call'));
 			
    		$crud->callback_before_update(array($this,'encrypt_password_callback'));
    		$crud->callback_before_insert(array($this,'encrypt_password_callback'));

			$crud->where('perfil =','CALL');
			
			$output = $crud->render();
			$output -> op = 'user_management';
			
			
			$this->_admin_output($output);
		}else{
			$this->close();
		}
	}

	function set_code_rutas($post_array,$primary_key)
	{
	    $this->db->update('usuarios',array('codigo' => $primary_key),array('id' => $primary_key));
    	return true;
	}	

	function user_managervehicle()
	{
		if(($this->userconfig->perfil=='ADMIN')or($this->userconfig->perfil=='CALL')){
		
			$crud = new grocery_CRUD();

			$crud->set_theme('datatables');
			$crud->set_table('usuarios');
			$crud->set_subject('Rutas');
			
			//$crud->columns('nombre','idsucursal','codigo','pais','departamento','ciudad','direccion','telefono','perfil');
			$crud->columns('idsucursal','nombre');
			$crud->fields('idsucursal','nombre','codigo','pais','departamento','ciudad','perfil');
			$crud->required_fields('idsucursal','nombre','perfil');
			$crud->display_as('nombre', 'Ruta');
			
			$crud->change_field_type('perfil', 'hidden');
			$crud->change_field_type('codigo', 'hidden');
			$crud->change_field_type('pais', 'hidden');
			$crud->change_field_type('departamento', 'hidden');
			$crud->change_field_type('ciudad', 'hidden');

			if($this->userconfig->perfil=='ADMIN')
				$crud->set_relation('idsucursal', 'sucursales', 'nombre');
			else
				$crud->set_relation('idsucursal', 'sucursales', 'nombre','id IN ("'.$this->userconfig->idsucursal.'")');			
			
			$crud->display_as('idsucursal', 'Sucursal');

	    	$crud->callback_edit_field('perfil',array($this,'set_user_cust'));
			$crud->callback_add_field('perfil',array($this,'set_user_cust'));
				
			$crud->callback_before_update(array($this,'encrypt_password_callback'));
			$crud->callback_before_insert(array($this,'encrypt_password_callback'));

			$crud->callback_after_insert(array($this, 'set_code_rutas'));
   

			$crud->where('perfil =', 'CUST');
			$crud->order_by('idsucursal,nombre');

			if($this->userconfig->perfil<>'ADMIN')
				$crud->where('idsucursal =', $this->userconfig->idsucursal);
			$output = $crud->render();
			$output -> op = 'user_management';
			$this->_admin_output($output);
		}else{
			$this->close();
		}
	}

	function vehicle_management()
	{
		if(($this->userconfig->perfil=='ADMIN')or($this->userconfig->perfil=='CALL')){
			//$crud = new grocery_CRUD();
			$this->load->library('ajax_grocery_CRUD');
            $crud = new ajax_grocery_CRUD();

			$crud->set_theme('datatables');
			$crud->set_table('vehiculos');
			$crud->set_subject('Vehiculos');
			$crud->columns('idsucursal','placa','propietario','modelo','marca');
			$crud->fields('idsucursal','placa','propietario','modelo','marca','puestos','latitud','longitud');
			$crud->display_as('idsucursal', 'Sucursal');
			$crud->display_as('puestos', 'Número de puestos');
			$crud->display_as('propietario', 'Ruta');
			$crud->required_fields('idsucursal','placa','propietario');
			$crud->change_field_type('latitud', 'hidden');
			$crud->change_field_type('longitud', 'hidden');
			
			if($this->userconfig->perfil=='ADMIN'){
				$crud->set_relation('idsucursal', 'sucursales', 'nombre');
				$crud->set_relation('propietario', 'usuarios', 'nombre','perfil IN ("CUST") ');

			}
			else{
				$crud->set_relation('idsucursal', 'sucursales', 'nombre','id IN ("'.$this->userconfig->idsucursal.'")');			
				$crud->set_relation('propietario', 'usuarios', 'nombre','perfil IN ("CUST") and idsucursal IN ("'.$this->userconfig->idsucursal.'")');
			}

			//$crud->set_relation_dependency('propietario','idsucursal','idsucursal');

			$crud->callback_before_insert(array($this,'insert_coordinates'));

			if($this->userconfig->perfil<>'ADMIN')
				$crud->where('vehiculos.idsucursal =', $this->userconfig->idsucursal);
			
			$output = $crud->render();
			$output -> op = 'user_management';
			$this->_admin_output($output);
		}else{
			$this->close();
		}
	}

	function insert_coordinates($post_array)
	{
		$this->load->model('sqlexteded');
		$result =  $this->sqlexteded->getLatLngOficce($post_array['idsucursal']);
		
        $post_array['latitud']  = $result->latitud;
        $post_array['longitud'] = $result->longitud;
    	return $post_array;
	}

	function agent_management()
	{
		if(($this->userconfig->perfil=='ADMIN')or($this->userconfig->perfil=='CALL')){
		
			//$crud = new grocery_CRUD();
			$this->config->set_item('grocery_crud_file_upload_allow_file_types','jpg|png');
			$this->load->library('ajax_grocery_CRUD');
            $crud = new ajax_grocery_CRUD();

			$crud->set_theme('datatables');
			$crud->set_table('agente');
			$crud->set_subject('Vendedores');
			//$crud->columns('nombre','idsucursal','codigo','vehiculo','pais','departamento','ciudad','direccion','telefono','fecha_localizacion');
			$crud->columns('nombre','idsucursal','codigo','vehiculo','telefono','fecha_localizacion');
			$crud->fields('nombre','idsucursal','codigo','clave','vehiculo','pais','departamento','ciudad','direccion','telefono','foto','latitud','longitud');
			$crud->required_fields('nombre','idsucursal','codigo','vehiculo','pais','departamento','ciudad','direccion','telefono');
			$crud->change_field_type('latitud', 'hidden');
			$crud->change_field_type('longitud', 'hidden');
			
			
			$crud->display_as('departamento', 'Provincia');

			$crud->set_relation('idsucursal', 'sucursales', 'nombre');
			$crud->display_as('idsucursal', 'Sucursal');

			$crud->display_as('codigo', 'Cedula');
			$crud->display_as('vehiculo', 'Placa');
			$crud->display_as('fecha_localizacion', 'Fec. Geolocalizacón');
			
			$crud->set_field_upload('foto','assets/images/agents');
			$crud->callback_after_upload(array($this,'image_callback_after_upload'));

			$crud->change_field_type('clave', 'password');
			

			if($this->userconfig->perfil=='ADMIN'){
				$crud->set_relation('idsucursal', 'sucursales', 'nombre');
				$crud->set_relation('vehiculo', 'vehiculos', 'placa');
			}
			else{
				$crud->set_relation('idsucursal', 'sucursales', 'nombre','id IN ("'.$this->userconfig->idsucursal.'")');
				$crud->set_relation('vehiculo', 'vehiculos', 'placa','idsucursal IN ("'.$this->userconfig->idsucursal.'")');
			}
	
			$crud->set_relation_dependency('vehiculo','idsucursal','idsucursal');

        	$crud->callback_edit_field('clave',array($this,'set_password_input_to_empty'));
    		$crud->callback_add_field('clave',array($this,'set_password_input_to_empty'));
 
    		$crud->callback_before_update(array($this,'encrypt_password_callback'));
    		$crud->callback_before_insert(array($this,'encrypt_password_callback'));
    		
			if($this->userconfig->perfil<>'ADMIN')
				$crud->where('agente.idsucursal =', $this->userconfig->idsucursal);

			$crud->order_by('fecha_localizacion','asc');
					
			//$crud->where('codigo =', 1);
			$output = $crud->render();
			$output -> op = 'agent_management';

			$this->_admin_output($output);
		}else{
			$this->close();
		}
	}


	function callService()
	{
		//$this->load->view('private/callcenter.php',array('op' => ''));
		$this->load->view('private/callcenter.php',(object)array('output' => '' , 'js_files' => array() , 'css_files' => array() , 'op' => '' ));
	}

	
	function student_stop_management()
	{
		//$this->load->view('private/student_stop.php',array('op' => '/admin/viewstudent_stop'));
		$this->load->view('private/admin.php',(object)array('op' => 'student_stop_management','url' => '/admin/viewstudent_stop'  , 'js_files' => array() , 'css_files' => array() ));
	}

	function way_stop_management()
	{
		//$this->load->view('private/student_stop.php',array('op' => '/admin/viewstudent_stop'));
		$this->load->view('private/admin.php',(object)array('op' => 'student_stop_management','url' => '/admin/viewway_stop'  , 'js_files' => array() , 'css_files' => array() ));
	}

	function tabletCallAgent()
	{
		$this->load->view('private/admin.php',(object)array('op' => 'tabletCallAgent','url' => '/admin/viewAgent' , 'js_files' => array() , 'css_files' => array() ));
	}

	function showAgentCust()
	{
		$this->load->view('private/admin.php',(object)array('op' => 'showAgentCust','url' => '/admin/viewAgent' , 'js_files' => array() , 'css_files' => array() ));
	}
	
	function tabletAdminAgent()
	{
		$this->load->view('private/admin.php',(object)array('op' => 'tabletAdminAgent','url' => '/admin/viewAgent' , 'js_files' => array() , 'css_files' => array() ));
	}


	function student_management()
	{
		if(($this->userconfig->perfil=='ADMIN')or($this->userconfig->perfil=='CALL')){
			//$crud = new grocery_CRUD();
			$this->config->set_item('grocery_crud_file_upload_allow_file_types','jpg|png');
			$this->load->library('ajax_grocery_CRUD');
            $crud = new ajax_grocery_CRUD();

			$crud->set_theme('datatables');
			$crud->set_table('alumno');
			$crud->set_subject('Cliente');
			$crud->columns('idsucursal','idgrado','nombre','codigo','idparada','idparada_tarde');
			$crud->fields('codigo','clave','idsucursal','idgrado','nombre','foto1','foto2','idparada','idparada_tarde');
			$crud->display_as('idsucursal', 'Institución');
			$crud->display_as('idgrado', 'Cliente');
			
			$crud->display_as('idparada', 'Parada mañana');
			$crud->display_as('idparada_tarde', 'Parada tarde');
			
			$crud->required_fields('codigo','idsucursal','nombre');
			//$crud->set_relation('idparadas', 'paradas', 'direccion');
			$crud->set_field_upload('foto1','assets/images/students');
			$crud->display_as('foto1', 'Foto uno');
			$crud->set_field_upload('foto2','assets/images/students');
			$crud->display_as('foto2', 'Foto dos');
			$crud->callback_after_upload(array($this,'image_callback_after_upload'));
			
			$crud->change_field_type('clave', 'password');
			$crud->callback_edit_field('clave',array($this,'set_password_input_to_empty'));
    		$crud->callback_add_field('clave',array($this,'set_password_input_to_empty'));
     		$crud->callback_before_update(array($this,'encrypt_password_callback'));
    		$crud->callback_before_insert(array($this,'encrypt_password_callback'));

			$state = $crud->getState();
	    	$state_info = $crud->getStateInfo();
	 		$primary_key='-1';
	    	if($state == 'edit')
	    	{
	        	$primary_key = $state_info->primary_key;
	    	}

			if($this->userconfig->perfil=='ADMIN'){
				$crud->set_relation('idsucursal', 'sucursales', 'nombre');
				$crud->set_relation('idparada', 'paradas', 'descripcion', array('idalumno' => $primary_key));
				$crud->set_relation('idparada_tarde', 'paradas', 'descripcion', array('idalumno' => $primary_key));
				$crud->set_relation('idgrado', 'grados', 'descripcion');
			}
			else{
				$crud->set_relation('idsucursal', 'sucursales', 'nombre','id IN ("'.$this->userconfig->idsucursal.'")');			
				$crud->set_relation('idgrado', 'grados', 'descripcion','id IN ("'.$this->userconfig->idsucursal.'")');			
				$crud->set_relation('idparada', 'paradas', 'descripcion', array('idalumno' => $primary_key));
				$crud->set_relation('idparada_tarde', 'paradas', 'descripcion', array('idalumno' => $primary_key));
			}

			$crud->set_relation_dependency('idgrado','idsucursal','idsucursal');


			if($this->userconfig->perfil<>'ADMIN')
				$crud->where('alumno.idsucursal =', $this->userconfig->idsucursal);
			
			$crud->order_by('idsucursal,idgrado,nombre');

			$output = $crud->render();
			$output -> op = 'student_management';
			$this->_admin_output($output);
		}else{
			$this->close();
		}
	}

	function novedades_management()
	{
		if(($this->userconfig->perfil=='ADMIN')or($this->userconfig->perfil=='CALL')){
			$crud = new grocery_CRUD();
			
			$crud->set_theme('datatables');
			$crud->set_table('novedades');
			$crud->set_subject('Novedades');
			$crud->columns('idsucursal','descripcion');
			$crud->fields('idsucursal','descripcion');
			$crud->display_as('idsucursal', 'Sucursal');
			$crud->required_fields('idsucursal','descripcion');

			if($this->userconfig->perfil=='ADMIN'){
				$crud->set_relation('idsucursal', 'sucursales', 'nombre');
			}
			else{
				$crud->set_relation('idsucursal', 'sucursales', 'nombre','id IN ("'.$this->userconfig->idsucursal.'")');			
			}


			if($this->userconfig->perfil<>'ADMIN')
				$crud->where('novedades.idsucursal =', $this->userconfig->idsucursal);
			
			$output = $crud->render();
			$output -> op = 'novedades_management';
			$this->_admin_output($output);
		}else{
			$this->close();
		}
	}


	function grados_management()
	{
		if(($this->userconfig->perfil=='ADMIN')or($this->userconfig->perfil=='CALL')){
			$crud = new grocery_CRUD();

			$crud->set_theme('datatables');
			$crud->set_table('grados');
			$crud->set_subject('Clientes');
			$crud->columns('idsucursal','descripcion');
			$crud->fields('idsucursal','descripcion');
			$crud->display_as('idsucursal', 'Sucursal');
			$crud->required_fields('idsucursal','descripcion');

			if($this->userconfig->perfil=='ADMIN'){
				$crud->set_relation('idsucursal', 'sucursales', 'nombre');
			}
			else{
				$crud->set_relation('idsucursal', 'sucursales', 'nombre','id IN ("'.$this->userconfig->idsucursal.'")');			
			}

			if($this->userconfig->perfil<>'ADMIN')
				$crud->where('grados.idsucursal =', $this->userconfig->idsucursal);
			
			$output = $crud->render();
			$output -> op = 'novedades_management';
			$this->_admin_output($output);
		}else{
			$this->close();
		}
	}

	function paradas($idalumno){
		if(($this->userconfig->perfil=='ADMIN')or($this->userconfig->perfil=='CALL')){
		  $crud = new grocery_CRUD();
		  $crud->set_table('paradas');
		  $crud->where('idalumno', $idalumno);

		  $crud->callback_before_insert(array($this,'before_insert_paradas'));
		  $output = $crud->render();
		  $output -> op = 'viewstudent_management';
		  $this->_admin_output($output);
		  //$this->load->view('example',$output);
		}else{
				$this->close();
		}

	}


	function stops_tracking(){
		$crud = new grocery_CRUD();

		$crud->unset_add();
		$crud->unset_delete();
		$crud->unset_edit();
		
		$crud->set_theme('datatables');
		$crud->set_table('seguimiento');
		$crud->set_subject('Seguimiento de paradas');
		$crud->columns('idsucursal','idruta','idagente','fecha','idalumno','descripcion');
 		//$crud->add_action('Ver Posición', '', '','ui-icon-image',array($this,'get_row_id' ));
 		$crud->add_action('Ver mapa', '', '','ui-icon-image',array($this,'showTracking'));
		$crud->display_as('idsucursal', 'Institución');
		$crud->display_as('idalumno', 'Cliente');
		$crud->display_as('idruta', 'Ruta');
		$crud->display_as('idagente', 'Vendedor');
		
		$crud->set_relation('idsucursal', 'sucursales', 'nombre');			
		$crud->set_relation('idalumno', 'alumno', '{codigo} {nombre}');		
		$crud->set_relation('idruta', 'usuarios', 'nombre','perfil IN ("CUST")');
		$crud->set_relation('idagente', 'agente', 'nombre');			

		$filtro = $this->input->get('fechaini');
		if ($filtro!=''){
			$fi = $this->input->get('fechaini');
			$ff = $this->input->get('fechafin');
		}else{
			$fi = date('Y-m-d 00:00:00');
        	$ff = date('Y-m-d 23:59:59');
		}
		    
		if($this->userconfig->perfil<>'ADMIN')
			$where = array('fecha >= ' => $fi, 'fecha <= ' => $ff,'seguimiento.idsucursal = '  => $this->userconfig->idsucursal);
		else	
			$where = array('fecha >= ' => $fi, 'fecha <= ' => $ff);
		$crud->where($where);  
		  
		$output = $crud->render();
		$output -> fechaini = $fi;
		$output -> fechafin = $ff;
		$output -> op = 'stops_tracking';
		//$output -> url = '/admin/viewstops_tracking';
		$this->_admin_output($output);

	}

	function showTracking($primary_key , $row)
	{
    	//return 'http://maps.googleapis.com/maps/api/staticmap?markers='.$row->latitud.','.$row->longitud.'&zoom=15&size=600x350';
    	return  site_url('admin/viewstops_tracking').'?lat='.$row->latitud.'&lng='.$row->longitud.'&idalumno='.$row->idalumno;
	}

	function viewstops_tracking()
	{
		$lat = $this->input->get('lat');
		$lng = $this->input->get('lng');
		$idalumno = $this->input->get('idalumno');
	
		//$this->load->view('private/admin',array('op' => 'viewstops_tracking','url' => '/admin/viewstops_tracking','idalumno' => $idalumno,'lat' => $lat,'lng' => $lng));
		$this->load->view('private/viewstops_tracking',array('op' => '/admin/viewstops_tracking','idalumno' => $idalumno,'lat' => $lat,'lng' => $lng));
	}
	
 	
	
	function showAgent()
	{
		$this->load->view('private/callcenter.php',array('op' => '/admin/underConstuction'));
	}
	
	
	function viewstudent_stop()
	{
		$this->load->view('private/viewstudent_stop',array('op' => '/admin/viewstudent_stop'));
	}

	function viewway_stop()
	{
		$this->load->view('private/viewway_stop',array('op' => '/admin/viewway_stop'));
	}
	
	function viewAgent()
	{
		$this->load->view('private/viewAgent',array('op' => '/admin/viewAgent'));
	}
	
	
	
	

	public function close()
    {
    	//cerrar sesión
    	$this->session->sess_destroy();
    	redirect($user->lang.'/login'); 

    }


	function image_callback_after_upload($uploader_response,$field_info, $files_to_upload)
	{
	    $this->load->library('image_moo');
	 
	    //Is only one file uploaded so it ok to use it with $uploader_response[0].
	    $file_uploaded = $field_info->upload_path.'/'.$uploader_response[0]->name; 
	 
	    $this->image_moo->load($file_uploaded)->resize(96,96)->save($file_uploaded,true);
	 
	    return true;
	}
	
}