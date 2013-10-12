<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta http-equiv="content-script-type" content="text/javascript">
	<title><?=Kohana::config('lattice.siteTitle');?></title>
 <!--
  <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
  <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
  <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>--> 
  <link rel="stylesheet" href=" <?php echo $css; ?>" />
   
   
	<?php
		foreach($javascript as $js):
	?>
	<script type="text/javascript" src="<?php echo url::base().$js; ?>"></script>
	<?php
		endforeach;
	?>
	
  <script type="text/javascript">
	window.addEvent('domready', function() {
		var mySmartFormObj = new SmartAjaxForm();
	});
  </script>
  
</head>
<body>
<header>
<?php echo Request::Factory('authstatus')->execute()->body() ;?>
</header>
<?php 
	if($notice != "")
	{
		echo $notice;
	}
?>

<form class="ajaxForm" action="<?=url::site('import/initialize')?>" method="GET"> 
	<input name="import_dir" type="text" placeholder="please enter folder name"/>
	<input type="submit" value="import">
</form>


<div id="progressbar">cc</div>
 
 
</body>
</html>


