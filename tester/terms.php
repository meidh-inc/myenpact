<?php 
// Set Timezone
date_default_timezone_set('America/Chicago');

// Today's date
$today = date("Y-m-d");


?>
<?php include("includes/header_index.php"); ?>
		
             <!-- ------------------- page layout begins here ------------------- --> 
		
<link type="text/css" href="jquery/css/custom-theme/jquery-ui-1.8.23.custom.css" rel="stylesheet" />
<script type="text/javascript" src="jquery/js/jquery-1.8.2.min.js"></script>
<script type="text/javascript" src="jquery/js/jquery-ui-1.8.24.custom.min.js"></script>
<script type="text/javascript">
$(function() {
    $( "button", ".help" ).button({
        icons: {
            primary: "ui-icon-help"
        }
    });
});
</script>

<center>
<table border='0' style="width:850px;background-image:url('images/texture2.jpg');padding:30px;margin-top:5px;">
    <tr>
	<td colspan='2'>
	<table style="width:100%;"><tr>
	<form action="emailreceiver.php" method="post">
	<td colspan='1' style="height:60px;">
	    <a href="index.php"><img src="images/title.png" alt="title" /></a>
	</td>
	<td style="width:70px;height:60px;text-align:right;vertical-align:top;">
	    
	</td><td style="width:85px;height:60px;text-align:right;vertical-align:top;">
	<!--
	    <div class="help"><button type="submit" name="help" value="help" style="cursor:pointer;height:25px;font-size:12px;">Help</button></div>
	-->
	</td>
	</form>
	</tr></table>
	</td>
    </tr>
    <tr><td colspan='2' style="height:1px;border-bottom:gray thin solid;"></td></tr>
    <tr>
	<td colspan='2' style="height:50px;text-align:center;">
	    Terms and Conditions
	    
	</td>
    </tr>
    <tr>
	<td colspan='2' style="height:50px;text-align:justify;font-size:14px;padding:40px;padding-top:0px;">
	    By submitting your details to any Site that uses the ENpact analysis tool, whether it be submitted
	    in your name, under any alias(es), or doing so as an agent of another individual, corporation, or
	    any other legally formed organization, or by sending or receiving data to or from any Site that uses
	    the ENpact analysis tool, you and any individual or entity on whose behalf you are contracted are
	    agreeing to being considered an ENpact User.  By becoming an ENpact User, you and any individual or
	    entity on whose behalf you are working agree to the terms and conditions identified herein and accept
	    any penalties identified herein for violating any of the terms and conditions identified.
	    The purpose of the ENpact tool is to earn revenue for Meidh Technologies Inc by sending and receiving
	    data to Users in accordance with its unique communication process.  The unique communication process
	    employed by the ENpact tool is the Intellectual Property of Meidh Technologies Inc.  By agreeing to
	    these terms and conditions, you and any individual, corporation, or any other legally formed organization
	    that you represented or represent while being an ENpact User, agree not to reverse engineer the unique
	    communication process employed by any Site using the ENpact tool or use any design of a similar
	    communication process to provide a revenue producing product for individuals or organizations that could
	    otherwise be Users of a site using the ENpact tool.  You agree that reverse engineering the unique
	    communication process employed by any Site using the ENpact tool constitutes theft of Meidh Technology Inc
	    Intellectual Property.
	    If you or any individual, corporation, or any other legally formed organization that you represented or
	    represent are responsible for theft of Meidh Technologies Inc intellectual property, the responsible
	    party agrees to pay all profits resulting from such theft to Meidh Technologies Inc.
	    
	</td>
    </tr>
    <tr>
	<td colspan='2' style="height:50px;text-align:justify;font-size:14px;padding:40px;padding-top:0px;">
	    Definitions:
	    <br>
	    <i>Site</i> – Any collection of electronic files that are remotely accessible.
	    <br><br>
	    <i>User</i> – Any individual, corporation, or any other legally formed organization
	    that has sent or received data to or from any Site that employs the ENpact analysis tool.
	    
	</td>
    </tr>
    <tr><td colspan='2' style="height:1px;border-bottom:gray thin solid;text-align:center;font-size:11px;color:gray;">
	(close this window or tab to return)
    </td></tr>
</table>
</center>
<?php include("includes/footer.php");?>