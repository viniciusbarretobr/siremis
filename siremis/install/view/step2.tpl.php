<div class="container">

    <div class="left left_style_b">
        <img src="images/icon_step_02.jpg" />
        <h2>Database Configuration</h2>
    </div>

	<div class="right right_style_b" style="padding-top:30px;">
        <p>Please enter your database configuration information below.</p>
		<p>If you select to create Siremis database, then the user set
		 to access it must have privileges to create it. Importing the
		default data inserts the records required by Siremis administration.</p>
		<p>Siremis database is for internal use while SIP database is the
		 one used by Kamailio - it has to be created separately
		 (i.e., kamdbctl create). Attention: Update SIP DB is removing
		and recreating acc and missed_calls tables in Kamailio database.</p>
<form id="setupform" name="setupform" method="post" action="install.php" >
<table class="input_row">
<tr>
	<td><label>Siremis DB Type</label></td>
	<td>
    <SELECT NAME="dbtype">
    <OPTION VALUE="Pdo_Mysql"<?php if($_REQUEST['dbtype']=="Pdo_Mysql") echo " selected='selected'";?>>MySQL
    <OPTION VALUE="Pdo_Pgsql"<?php if($_REQUEST['dbtype']=="Pdo_Pgsql") echo " selected='selected'";?>>PostgreSQL (Incomplete)
    </SELECT>
    </td>
</tr>
<tr>
	<td><label>Siremis DB Host Name</label></td>
	<td><input class="input_text" onfocus="this.className='input_text_focus'" onblur="this.className='input_text'"
    	 type="text" name="dbHostName" value="<?php echo  isset($_REQUEST['dbHostName']) ? $_REQUEST['dbHostName'] : 'localhost'?>" tabindex="1" ></td>
</tr>
<tr>
	<td><label>Siremis DB Port</label></td>
	<td><input class="input_text" onfocus="this.className='input_text_focus'" onblur="this.className='input_text'" 
    		type="text" name="dbHostPort" value="<?php echo  isset($_REQUEST['dbHostPort']) ? $_REQUEST['dbHostPort'] : '3306'?>" tabindex="3"></td>
</tr>
<tr>
	<td><label>Siremis DB Name</label></td>
	<td><input class="input_text" onfocus="this.className='input_text_focus'" onblur="this.className='input_text'" 
    		type="text" name="dbName" value="<?php echo  isset($_REQUEST['dbName']) ? $_REQUEST['dbName'] : 'siremis'?>" tabindex="3"></td>
</tr>
<tr>
	<td ><label>Siremis DB Username</label></td>
	<td><input class="input_text" onfocus="this.className='input_text_focus'" onblur="this.className='input_text'"
    		 type="text" name="dbUserName" value="<?php echo  isset($_REQUEST['dbUserName']) ? $_REQUEST['dbUserName'] : 'siremis'?>" tabindex="4"> <span class="input_desc">&nbsp;</span></td>
</tr>
<tr>
	<td ><label>Siremis DB Password</label></td>
	<td><input class="input_text" onfocus="this.className='input_text_focus'" onblur="this.className='input_text'"
    		type="password" name="dbPassword" value="<?php echo  isset($_REQUEST['dbPassword']) ? $_REQUEST['dbPassword'] : 'siremisrw'?>" tabindex="5" > <span class="input_desc">&nbsp;</span></td>
</tr>
<tr>
	<td> </td>
	<td> </td>
</tr>
<tr>
	<td><label>SIP DB Type</label></td>
	<td>
    <SELECT NAME="db1type">
    <OPTION VALUE="Pdo_Mysql"<?php if($_REQUEST['dbtype']=="Pdo_Mysql") echo " selected='selected'";?>>MySQL
    <OPTION VALUE="Pdo_Pgsql"<?php if($_REQUEST['dbtype']=="Pdo_Pgsql") echo " selected='selected'";?>>PostgreSQL
    <OPTION VALUE="Pdo_OCi"<?php if($_REQUEST['dbtype']=="Pdo_OCi") echo " selected='selected'";?>>Oracle 
    <OPTION VALUE="Pdo_Mssql"<?php if($_REQUEST['dbtype']=="Pdo_Mssql") echo " selected='selected'";?>>SQL Server
    </SELECT>
    </td>
</tr>
<tr>
	<td><label>SIP DB Host Name</label></td>
	<td><input class="input_text" onfocus="this.className='input_text_focus'" onblur="this.className='input_text'"
    	 type="text" name="db1HostName" value="<?php echo  isset($_REQUEST['db1HostName']) ? $_REQUEST['db1HostName'] : 'localhost'?>" ></td>
</tr>
<tr>
	<td><label>SIP DB Port</label></td>
	<td><input class="input_text" onfocus="this.className='input_text_focus'" onblur="this.className='input_text'" 
    		type="text" name="db1HostPort" value="<?php echo  isset($_REQUEST['db1HostPort']) ? $_REQUEST['db1HostPort'] : '3306'?>"></td>
</tr>
<tr>
	<td><label>SIP DB Name</label></td>
	<td><input class="input_text" onfocus="this.className='input_text_focus'" onblur="this.className='input_text'" 
    		type="text" name="db1Name" value="<?php echo  isset($_REQUEST['db1Name']) ? $_REQUEST['db1Name'] : 'kamailio'?>"></td>
</tr>
<tr>
	<td ><label>SIP DB Username</label></td>
	<td><input class="input_text" onfocus="this.className='input_text_focus'" onblur="this.className='input_text'"
    		 type="text" name="db1UserName" value="<?php echo  isset($_REQUEST['db1UserName']) ? $_REQUEST['db1UserName'] : 'kamailio'?>"> <span class="input_desc">&nbsp;</span></td>
</tr>
<tr>
	<td ><label>SIP DB Password</label></td>
	<td><input class="input_text" onfocus="this.className='input_text_focus'" onblur="this.className='input_text'"
    		type="password" name="db1Password" value="<?php echo  isset($_REQUEST['db1Password']) ? $_REQUEST['db1Password'] : 'kamailiorw'?>"> <span class="input_desc">&nbsp;</span></td>
</tr>
<tr>
	<td> </td>
	<td> </td>
</tr>
<tr>
	<td style="text-align:right;vertical-align:middle;padding-right:20px;"><label style="text-align:right;">Create Siremis DB &#8658;</label>
		<input  type="checkbox" name="create_db" id="create_db" />
	</td>
	<td style="text-align:left;vertical-align:middle;padding-right:20px;"><label style="text-align:right;">Import Default Data &#8658;</label>
		<input type="checkbox" name="load_db" id="load_db" />
	</td>
</tr>
<tr>
	<td style="text-align:right;vertical-align:middle;padding-right:20px;"><label style="text-align:right;">Update SIP DB &#8658;</label>
		<input type="checkbox" name="loadsip_db" id="loadsip_db" />
	</td>
	<td style="text-align:left;vertical-align:middle;padding-right:20px;"><label style="text-align:right;">Replace DB Config &#8658;</label>
		<input  type="checkbox" checked="checked" name="replace_db" id="replace_db" />
	</td>
</tr>
<tr>
	<td colspan=2>
		<img id="replacedb_img" src="images/indicator.white.gif" style="display:none"/>
		<span id="replace_db_result"></span>
	</td>
</tr>

</table>

</form>

    <a href="index.php?step=1" class="button">&lt; Back</a>
    <a href="javascript:step2_next()" class="button_highlight">Next &gt;</a>
    
</div>

<script>
function step2_next()
{
	if (!$('load_db').checked
			&& !$('create_db').checked
			&& !$('loadsip_db').checked
			&& !$('replace_db').checked) {
		alert("Please select from the above checkboxes to continue.");
		return
	}
	update_db();
}

function update_db(actions)
{
    $('replace_db_result').innerHTML='';
    new Ajax.Request('index.php?action=update', {
      onLoading: function() {
         Element.show('replacedb_img'); // or $('createdb_img').show();
      },
      onComplete: function() {
         Element.hide('replacedb_img');
      },
      onSuccess: function(transport) {
         var response = transport.responseText || "no response text";
         $('replace_db_result').innerHTML=response;
         if (response.indexOf('SUCCESS')>=0) {
			setTimeout('window.location = "index.php?step=3"', 1000);
         }
      },
      onFailure: function() { alert('Something went wrong...') },
      parameters: $('setupform').serialize()
   })
}

</script>
