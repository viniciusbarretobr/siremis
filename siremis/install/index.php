<?php ob_start(); ?>

<?php
require_once('../bin/app.inc');
require_once('util.php');

require_once('siremisutil.php');

$isInstalled = false;
if(is_file(dirname(dirname(__FILE__)).'/install.lock')){
	$isInstalled = true;
}

// response ajax call
if($isInstalled==false){
	if (isset($_REQUEST['action']) && !$isInstalled)
	{
		if ($_REQUEST['action']!='update')
		{
			echo "ERROR: invalid action";
			exit;
		}
	   if (isset($_REQUEST['create_db']))
	   {
		   if(!createDB())
		   {
			  echo 'ERROR: Database '.$_REQUEST['dbName'].' was not created';
				exit;
			}
	   }
	   if (isset($_REQUEST['load_db']))
	   {
		   if(!fillDB())
		   {
				echo 'ERROR: Data was not populated into database.';
				exit;
			}
	   }
	   if (isset($_REQUEST['loadsip_db']))
	   {
		   if(!siremisFillDB())
		   {
				echo 'ERROR: Data was not populated into SIP database.';
				exit;
		   }
	   }
	   if (isset($_REQUEST['replace_db']))
	   {
	      if(!replaceDbConfig())
		   {
				echo 'ERROR: Database config was not updated.';
				exit;
		   }
	      if(!siremisReplaceDbConfig())
		   {
				echo 'ERROR: Database config was not replaced.';
				exit;
		   }
	   }
		echo 'SUCCESS - actions completed, wait one second ...';
	    exit;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Siremis Installation</title>
<meta http-equiv="x-ua-compatible" content="ie=7" />
<link rel="stylesheet" href="install.css" type="text/css" /> 
<link rel="stylesheet" href="../themes/default/css/openbiz.css" type="text/css" /> 
<script language="javascript" src="../js/prototype.js"></script>
</head>
<body>
<div id="body_warp" align="center">
	<!-- header start -->
    <div class="header_warp">
		<div class="header">
			<div class="header_icon">
			</div>
            <div class="header_text">                
                Powered by <a href="http://www.asipto.com/">Asipto.com</a>
            </div>
		</div>
	</div>
    <!-- header end -->
    
    

<?php
$stepArr = array("",
				 "System Check",
				 "Database Configuration",
				 "Application Configuration",
				 "Finish"
				 );
$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : '0';

if($isInstalled){
	$step=count($stepArr)-1;
}

if((int)$step>0 && (int)$step<count($stepArr)-1){
	echo "<ul class=\"progress_bar\">";
	for($i=0;$i<count($stepArr);$i++){
		if($stepArr[$i]){
			$text = $i.". ".$stepArr[$i];
			if($i>$step){
				$text = "<a>$text</a>";				
				$style="normal";
			}elseif($i==$step){
				$text = "<a href=\"?step=$i\">$text</a>";
				$style= "current";
			}else{
				$text = "<a href=\"?step=$i\">$text</a>";
				$style= "past";
			}
			echo "<li id=\"step_$i\" class=\"$style\">$text</li>";		
		}
	}
	echo "</ul>";	
}
?>


<?php
include('view/step'.$step.'.tpl.php'); 
?>
</div>
</body>
</html>
