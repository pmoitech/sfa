<!DOCTYPE html>
<!--[if IE 8]>         <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->

<head>


  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width" />
  <title><?= $title.' : '.$this->config->item('app_name') ?></title>
  
  <link rel="shortcut icon" href="<?=base_url()?>assets/images/favicon.ico" type="image/x-icon">

  <link rel="stylesheet" href="<?=base_url()?>assets/foundation/css/normalize.css" />
  <link rel="stylesheet" href="<?=base_url()?>assets/css/app.css" />
  <link rel="stylesheet" href="<?=base_url()?>assets/foundation/css/foundation.min.css" />

  <script src="<?=base_url()?>assets/foundation/js/vendor/custom.modernizr.js"></script>

</head>
<body>

  <!-- body content here -->
<div>
		<img id="background" src="<?=base_url()?>assets/images/fondo.jpg" alt="" title="" />
</div>
<div id="scroller">
	<div id="cabecera">
    	<div class="large-8 large-centered columns">
	  	
	  	<!-- 	<h3 class="centered-text"><?=$this->config->item('app_name');?></h3>-->
<div>
	  
		</div>
	</div>

 <div id="contenido-login">
	<? if ($error): ?>
	<div class="row">
	  <div class="large-12 large-centered columns">
	  	<div data-alert class="alert-box alert">
  			<?=$error?>
  			<a href="#" class="close">&times;</a>
		</div>
	  </div>
	</div>
	<? endif; ?>

	<div class="row">
		<div class="large-12 large-centered columns">
			<?=form_open('login/do_login', array('id' => 'login-form'));?>
			  	<fieldset>
			    	<legend><h5><?= isset($enterprise->name) ? $enterprise->name : ''?><?=lang('login.userlogin')?></h5></legend>
				    <div class="row">
				      <div class="large-12 columns">
				      	<label><b><?=lang('login.usercode')?></b></label>
				        <input type="text" id="username" name="username" placeholder="<?=lang('login.usercode')?>" />
				      </div>
				    </div>
		    		<div class="row">
				      <div class="large-12 columns">
				      	<label><b><?=lang('login.password')?></b></label>
				        <input type="password" id="password" name="password" placeholder="<?=lang('login.password')?>" />
				      </div>
				    </div>
	
				    <div class="row">
				      <div class="large-12 columns">
				      	<a href="javascript:void(0)" id="send-form" class="button"><?=lang('login.start')?></a>
				      </div>
				    </div>
			    
			    </fieldset>
			    <input type="hidden" name="action" value="do_login" />             
			</form>  
			<div class="centre"> 
			Descarga aquí:
				<a href="<?=base_url()?>/assets/download/workforce-solutions-agent.apk"><img src="<?=base_url()?>assets/images/android_bus.png" width="60" height="60" title="Aplicación para trabajadores" /> </a>
			</div>

		</div>

	</div>

</div>


	
  <!-- END body content here -->


  <script>
  document.write('<script src=' +
  ('__proto__' in {} ? '<?=base_url()?>assets/foundation/js/vendor/zepto' : 'js/vendor/jquery') +
  '.js><\/script>')
  </script>
  <script src="<?=base_url()?>assets/foundation/js/foundation/foundation.js"></script>
  <script src="<?=base_url()?>assets/foundation/js/foundation/foundation.alerts.js"></script>
  <script src="<?=base_url()?>assets/foundation/js/foundation/foundation.clearing.js"></script>
  <script src="<?=base_url()?>assets/foundation/js/foundation/foundation.cookie.js"></script>
  <script src="<?=base_url()?>assets/foundation/js/foundation/foundation.dropdown.js"></script>
  <script src="<?=base_url()?>assets/foundation/js/foundation/foundation.forms.js"></script>
  <script src="<?=base_url()?>assets/foundation/js/foundation/foundation.joyride.js"></script>
  <script src="<?=base_url()?>assets/foundation/js/foundation/foundation.magellan.js"></script>
  <script src="<?=base_url()?>assets/foundation/js/foundation/foundation.orbit.js"></script>
  <script src="<?=base_url()?>assets/foundation/js/foundation/foundation.placeholder.js"></script>
  <script src="<?=base_url()?>assets/foundation/js/foundation/foundation.reveal.js"></script>
  <script src="<?=base_url()?>assets/foundation/js/foundation/foundation.section.js"></script>
  <script src="<?=base_url()?>assets/foundation/js/foundation/foundation.tooltips.js"></script>
  <script src="<?=base_url()?>assets/foundation/js/foundation/foundation.topbar.js"></script>
  
  <script>
  $(document).foundation();
  
  $(document).ready(function(){  
  	$('#send-form').click(function(e){
  		e.preventDefault();
  		$('#login-form').submit();
  	})
  });
  </script>
  
</div>
</body>
</html>