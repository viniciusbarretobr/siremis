<div class="container">

    <div class="left left_style_a">
        <img src="images/icon_step_00.jpg" />
    </div>

<div class="right right_style_a" style="padding-top:25px;">
	<h2>Installation Completed</h2>
    <p>
    Congratulations for completing Siremis Setup Wizard. <br />
	For security reasons, we strongly recommend to delete or properly restrict
	the access to the install folder now (path: siremis/install).<br />
    And also please change default login info before use.
    </p>
    <h4>Default Login Info</h4>
    <p>
     Username : <strong>admin</strong><br />
     Password : <strong>admin</strong><br />
    </p>
    <h4>User Reference Doucments</h4>

    <ul class="list">
    <li><a href="http://siremis.asipto.com">Siremis Web Page</a></li>
    <li><a href="http://www.asipto.com">Asipto Web Page</a></li>
    </ul>

    <a href="../index.php/user/login" class='button_w_highlight'>Launch Siremis</a>

</div>

</div>
<script>setTimeout("location.href='../index.php/user/login'",10000)</script>
<?php
touch (dirname(dirname(dirname(__FILE__))).'/install.lock');
?>
