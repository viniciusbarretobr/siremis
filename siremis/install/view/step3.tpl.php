<div class="container">

    <div class="left left_style_b" >
        <img src="images/icon_step_03.jpg" />
        <h2>Application Configuration</h2>
    </div>

<div class="right right_style_b" style="padding-top:30px;">


<h4>Check of directories with write access:</h4>
<table class="form_table"  cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
<tr>
	<th>Item</th>
	<th>Value</th>
	<th>Status</th>
</tr>
<?php
$status = getApplicationStatus();
$i=0;
$hasError = false;
foreach ($status as $s) {
    if(fmod($i,2)){
        $default_style="even";
    }else{
        $default_style="odd";
    }

    if (strpos($s['status'],'OK') === 0) {
        $flag_icon="flag_y.gif";
    }else{
        $flag_icon="flag_n.gif";
		$hasError = true;
    }
     $i++;
?>
        <tr
            class="<?php echo $default_style;?>"
            onmouseover="if(this.className!='selected')this.className='hover'" 
            onmouseout="if(this.className!='selected')this.className='<?php echo $default_style;?>'" 
        >
            <td><?php echo $s['item'];?></td>
            <td><?php echo $s['value'];?></td>
            <td><img src="../themes/default/images/<?php echo $flag_icon;?>" /></td>
        </tr>
<?php
}
?>
</table>

<h4>Database options in <?php echo APP_HOME.DIRECTORY_SEPARATOR;?>Config.xml</h4>
<?php
$db = getDefaultDB();
$db1 = getSiremisDB();
 ?>
<table class="form_table"  cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
<tr>
	<th>Name</th>
	<th>Driver</th>
	<th>Server</th>
	<th>Port</th>
	<th>DBName</th>
	<th>User</th>
	<th>Password</th>
</tr>
<tr
	class="even"
    onmouseover="if(this.className!='selected')this.className='hover'" 
    onmouseout="if(this.className!='selected')this.className='even'" 
>
    <td><?php echo $db['Name'];?></td>
    <td><?php echo $db['Driver'];?></td>
    <td><?php echo $db['Server'];?></td>
    <td><?php echo $db['Port'];?></td>
    <td><?php echo $db['DBName'];?></td>
    <td><?php echo $db['User'];?></td>
    <td><?php echo $db['Password'];?></td>
</tr>
<tr
	class="even"
    onmouseover="if(this.className!='selected')this.className='hover'" 
    onmouseout="if(this.className!='selected')this.className='even'" 
>
    <td><?php echo $db1['Name'];?></td>
    <td><?php echo $db1['Driver'];?></td>
    <td><?php echo $db1['Server'];?></td>
    <td><?php echo $db1['Port'];?></td>
    <td><?php echo $db1['DBName'];?></td>
    <td><?php echo $db1['User'];?></td>
    <td><?php echo $db1['Password'];?></td>
</tr>
</table>

        <a href="index.php?step=2" class="button">< Back</a>
		<?php 
		if (!$hasError){
		?>
        	<a href="index.php?step=4" class="button_highlight">Next ></a>
 		<?php
		}else{
		?>
	        <a href="index.php?step=3" class="button_m_highlight">Check Again</a>
        <?php
		}
		?>

</div>
<div id="error_message" class="popup_dialog" onclick="this.style.display='none';"></div>
</div>
</div>

