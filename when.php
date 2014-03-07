<?php require_once("includes/session.php"); ?>
<?php require_once("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php include_once("includes/form_functions.php"); ?>
<?php confirm_logged_in(); ?>
<?php 

// Get info from session
    $userid = $_SESSION['userid'];
    $user_email = $_SESSION['useremail'];
    $campaign_id = $_SESSION['campaignid'];

// Today's date
    $today = date("Y-m-d");

// Set Timezone
    date_default_timezone_set('America/Chicago');


// Process Actions and Page Load
    if (isset($_POST['edit'])) { // The email information update button was clicked
    
    // start array to hold edit window errors
	$errors_edit = array();
    
    // perform validations on the edited data
	$required_fields_edit = array('start_date_edit', 'end_date_edit', 'frequency_edit');
	$errors_edit = array_merge($errors_edit, check_required_fields($required_fields_edit, $_POST));
    
    // Grab values for variables
	$start_date_edit = trim(mysql_prep($_POST['start_date_edit']));
	$end_date_edit = trim(mysql_prep($_POST['end_date_edit']));
	$frequency_edit = trim(mysql_prep($_POST['frequency_edit']));
    
    // Proceed if there are no errors, else print the errors
	if (empty($errors_edit)) {
	    $query_update_edit = "UPDATE campaigns SET
				    start_date = '{$start_date_edit}',
				    end_date = '{$end_date_edit}',
				    frequency = '{$frequency_edit}'
			    WHERE id = {$campaign_id}";
	    $result_edit = mysql_query($query_update_edit);
	    
	    // test to see if the update occurred
		if (mysql_affected_rows() == 1) {
		    // Success!
		    //$message = "<center><p style=\"color:blue;\">The page was successfully updated.</p></center>";
		    redirect_to("when.php");
		} else {
		    if(mysql_error() == NULL){  // there are no affected rows and no errors (triggered when someone hits save without making changes)
			redirect_to("when.php");
		    }else{
		    $message_edit = "The information could not be updated.";
		    $message_edit .= "<br />" . mysql_error() . mysql_affected_rows();
		    //redirect_to("dashboard.php?settingsupdated=3");
		    }
		}
	} else {
	    if (count($errors_edit) == 1) {
		$message_edit = "There was 1 error in the form.";
	    } else {
		$message_edit = "There were " . count($errors_edit) . " errors in the form.";
	    }
	}
    }
    
    
    if (isset($_POST['undo_edit'])) { // reset the edit popup window
	
	// Grab existing info from database
    	    $query_campaign_info = mysql_query("SELECT start_date, end_date, frequency
    	        				   FROM campaigns
    	        				   WHERE id = '{$campaign_id}' ");
    	    $campaign_info_array = mysql_fetch_array($query_campaign_info);
	
	// Assign values to page variables
	    $frequency_edit = $campaign_info_array['frequency'];
    	
    	// Assign values to the Start/End Dates so 0000-00-00 does not display
    	    if($campaign_info_array['start_date'] != '0000-00-00'){
		$start_date_edit = $campaign_info_array['start_date'];
    	    }else{
		$start_date_edit = "";
    	    }
    	    if($campaign_info_array['end_date'] != '0000-00-00'){
		$end_date_edit = $campaign_info_array['end_date'];
    	    }else{
		$end_date_edit = "";
    	    }
    }
        
    // Proceed with loading the other variables on the page regardless whether the upload button was clicked or not
    // Grab existing info from database
        $query_campaign_info = mysql_query("SELECT start_date, end_date, frequency
            				   FROM campaigns
            				   WHERE id = '{$campaign_id}' ");
        $campaign_info_array = mysql_fetch_array($query_campaign_info);
    
    // Assign values to page variables
    	$frequency = $campaign_info_array['frequency'];
	$frequency_edit = $campaign_info_array['frequency'];
    	
    // Assign values to the Start/End Dates so 0000-00-00 does not display
        if($campaign_info_array['start_date'] != '0000-00-00'){
            $start_date = $campaign_info_array['start_date'];
	$start_date_edit = $campaign_info_array['start_date'];
    	}else{
    	    $start_date = "";
	    $start_date_edit = "";
        }
	if($campaign_info_array['end_date'] != '0000-00-00'){
    	    $end_date = $campaign_info_array['end_date'];
	    $end_date_edit = $campaign_info_array['end_date'];
    	}else{
    	    $end_date = "";
	    $end_date_edit = "";
    	}

if (isset($_POST['start'])) {  // Button clicked to start campaign
    $start_campaign = $_POST['start'];
    $query_update_cstatus = "UPDATE campaigns SET
				    status = 'active'
			    WHERE id = {$start_campaign}";
    $result_start = mysql_query($query_update_cstatus);
}

if (isset($_POST['pause'])) {  // Button clicked to pause campaign
    $pause_campaign = $_POST['pause'];
    $query_update_cstatus = "UPDATE campaigns SET
				    status = 'paused'
			    WHERE id = {$pause_campaign}";
    $result_pause = mysql_query($query_update_cstatus);
}

if (isset($_POST['deactivate'])) {  // Button clicked to deactivate campaign
    $deactivate_campaign = $_POST['deactivate'];
    $query_update_cstatus = "UPDATE campaigns SET
				    status = 'inactive'
			    WHERE id = {$deactivate_campaign}";
    $result_deactivate = mysql_query($query_update_cstatus);
}

// Returning from Paypal
if(isset($_GET['pid'])){
    if(($_GET['pid'] == '8472') && ($_GET['authid'] == $_SESSION['auth_key'])){ //user made a payment
	if($_GET['level'] == '1'){ //user paid for 1 month
	    $onemonth = strtotime('+1 month', strtotime($today));
	    $subscription_end = date('Y-m-d', $onemonth);
	    $insert_payment = mysql_query("INSERT INTO payments (
					       user_id, campaign_id, level_purchased, paid_date, expire_date
						)VALUES(
						'{$userid}', '{$campaign_id}', '{$_GET['level']}', '{$today}', '{$subscription_end}'
						)");
	    $update_paid = mysql_query("UPDATE campaigns SET paid = 'yes' WHERE id = '{$campaign_id}' ");
	    $subscription_end_pretty = strtotime($subscription_end);
	    $subscription_end_pretty = date('M j, Y', $subscription_end_pretty);
	    $paypal_message = "You're payment has been received. This campaign currently expires on ".$subscription_end_pretty.".";
	}elseif($_GET['level'] == '2'){ //user paid for 6 months
	    $sixmonths = strtotime('+6 months', strtotime($today));
	    $subscription_end = date('Y-m-d', $sixmonths);
	    $insert_payment = mysql_query("INSERT INTO payments (
					       user_id, campaign_id, level_purchased, paid_date, expire_date
						)VALUES(
						'{$userid}', '{$campaign_id}', '{$_GET['level']}', '{$today}', '{$subscription_end}'
						)");
	    $update_paid = mysql_query("UPDATE campaigns SET paid = 'yes' WHERE id = '{$campaign_id}' ");
	    $subscription_end_pretty = strtotime($subscription_end);
	    $subscription_end_pretty = date('M j, Y', $subscription_end_pretty);
	    $paypal_message = "You're payment has been received. This campaign currently expires on ".$subscription_end_pretty.".";
	}elseif($_GET['level'] == '3'){ //user paid for 12 months
	    $twelvemonths = strtotime('+12 months', strtotime($today));
	    $subscription_end = date('Y-m-d', $twelvemonths);
	    $insert_payment = mysql_query("INSERT INTO payments (
					       user_id, campaign_id, level_purchased, paid_date, expire_date
						)VALUES(
						'{$userid}', '{$campaign_id}', '{$_GET['level']}', '{$today}', '{$subscription_end}'
						)");
	    $update_paid = mysql_query("UPDATE campaigns SET paid = 'yes' WHERE id = '{$campaign_id}' ");
	    $subscription_end_pretty = strtotime($subscription_end);
	    $subscription_end_pretty = date('M j, Y', $subscription_end_pretty);
	    $paypal_message = "You're payment has been received. This campaign currently expires on ".$subscription_end_pretty.".";
	}elseif($_GET['level'] == '4'){ //user paid for 24 months
	    $twentyfourmonths = strtotime('+24 months', strtotime($today));
	    $subscription_end = date('Y-m-d', $twentyfourmonths);
	    $insert_payment = mysql_query("INSERT INTO payments (
					       user_id, campaign_id, level_purchased, paid_date, expire_date
						)VALUES(
						'{$userid}', '{$campaign_id}', '{$_GET['level']}', '{$today}', '{$subscription_end}'
						)");
	    $update_paid = mysql_query("UPDATE campaigns SET paid = 'yes' WHERE id = '{$campaign_id}' ");
	    $subscription_end_pretty = strtotime($subscription_end);
	    $subscription_end_pretty = date('M j, Y', $subscription_end_pretty);
	    $paypal_message = "You're payment has been received. This campaign currently expires on ".$subscription_end_pretty.".";
	}
    }elseif(($_GET['pid'] == '8472') && ($_GET['authid'] != $_SESSION['auth_key'])){
	$paypal_message = "Payment Error Detected.  Please contact info@myenpact.com.";
	
    }elseif($_GET['pid'] == '550'){ //user hit the cancel button
	
	$paypal_message = "Payment Process Cancelled";
    }
}elseif(!isset($_GET['pid'])){
$_SESSION['auth_key'] = md5(uniqid(rand(), TRUE));

// get subscription situation and print the end date or nothing if zero
    $get_expiration = mysql_query("SELECT expire_date FROM payments WHERE campaign_id = '{$campaign_id}'
				  ORDER BY id DESC LIMIT 1");
    $array_expiration = mysql_fetch_array($get_expiration);
    if($array_expiration['expire_date'] > '0000-00-00'){
	$subscription_end_pretty = strtotime($array_expiration['expire_date']);
	$subscription_end_pretty = date('M j, Y', $subscription_end_pretty);
	$paypal_message = "Credit to run this campaign will end on ".$subscription_end_pretty.".";
    }else{
	$paypal_message = "<span style=\"color:#f4911e;\">Please purchase credit to run this campaign.</span>";
    }
}

//Code for Logging Out
if (isset($_POST['logout'])) {redirect_to('logout.php');}

?>
<?php include("includes/header_index.php"); ?>
		
             <!-- ------------------- page layout begins here ------------------- --> 
		

<link type="text/css" href="jquery/css/custom-theme/jquery-ui-1.8.23.custom.css" rel="stylesheet" />
<script type="text/javascript" src="jquery/js/jquery-1.8.2.min.js"></script>
<script type="text/javascript" src="jquery/js/jquery-ui-1.8.24.custom.min.js"></script>
<script type="text/javascript">
$(function() {
    $( "#dialog:ui-dialog" ).dialog( "destroy" );
    $( "#dialog-edit" ).dialog({
	<?php 
	    if ((isset($_POST['edit'])) || (isset($_POST['undo_edit']))) {
		echo "autoOpen: true,";
	    }else{
		echo "autoOpen: false,";
	    }
	?>
	position: ["center",50],
	width: 800,
	resizable: false,
	modal: true,
    });
    $( "#editpop" )
	.button()
	.click(function() {
	    $( "#dialog-edit" ).dialog( "open" );
	});
    $( "#from" ).datepicker({
	defaultDate: "+1w",
	changeMonth: true,
	numberOfMonths: 1,
	dateFormat: "yy-mm-dd",
	onSelect: function( selectedDate ) {
	    $( "#to" ).datepicker( "option", "minDate", selectedDate );
	}
    });
    $( "#to" ).datepicker({
	defaultDate: "+1w",
	changeMonth: true,
	numberOfMonths: 1,
	dateFormat: "yy-mm-dd",
	onSelect: function( selectedDate ) {
	    $( "#from" ).datepicker( "option", "maxDate", selectedDate );
	}
    });
    $( "#navigation" ).buttonset();
});
$(function() {
    $( "button", ".pause" ).button({
        icons: {
            primary: "ui-icon-pause"
        }
    });
    $( "button", ".start" ).button({
        icons: {
            primary: "ui-icon-play"
        }
    });
    $( "button", ".stop" ).button({
        icons: {
            primary: "ui-icon-stop"
        }
    });
    $( "button", ".help" ).button({
        icons: {
            primary: "ui-icon-help"
        }
    });
    $( "button", ".logout" ).button({
        icons: {
            primary: "ui-icon-circle-close"
        }
    });
});
</script>

<!-- BEGIN EDITOR POP UP BOX -->
<div id="dialog-edit" title="Edit Details">
<form id="editform" action="when.php" method="post">
<center>
<table border='0' style="width:750px;">
    <tr>
	<td colspan="5" align="center">
	    <?php if (!empty($message_edit)) {echo "<p style=\"color:#C20000; font-size: 12px;\">" . $message_edit . "</p>";} ?>
	    <?php if (!empty($errors_edit)) { display_errors($errors_edit); } ?>
	</td>
    </tr>
    <tr>
	<td>SETTINGS:
	</td>
	<td>
	    Maximum emails per year:
	    <input type="text" name="frequency_edit" maxlength="100" style="width:40px;"
		value="<?php echo htmlentities($frequency_edit); ?>" />
	    <br>
	    Start on <input type="text" id="from" name="start_date_edit" value="<?php echo $start_date_edit; ?>" placeholder="click for calendar" style="width:105px;">
	    <br>
	    End on <input type="text" id="to" name="end_date_edit" value="<?php echo $end_date_edit; ?>" placeholder="click for calendar" style="width:105px;">
	    <br>
	    
	</td>
    </tr>
    <tr>
        <td colspan='2' style="padding-top:20px;text-align:center;">
	    <!--<input type="submit" alt="undo_edit" name="undo_edit" value="Undo All" class="ui-button ui-widget ui-state-default ui-corner-all" >
	    -->
	    <input type="submit" alt="edit" name="edit" value="Save" class="ui-button ui-widget ui-state-default ui-corner-all" >
	    <br>
	    <font style="color:gray;font-size:10px;">(click x in upper-right corner to cancel)</font>
        </td>
    </tr>
</table>
</form>
</center>
</div>
<!-- END EDITOR POP UP BOX -->

<center>
<table border='0' style="width:850px;background-image:url('images/texture2.jpg');padding:30px;margin-top:5px;">
    <tr>
	<td colspan='3'>
	<table style="width:100%;"><tr>
	<form action="when.php" method="post">
	<td colspan='1' style="height:60px;">
	    <img src="images/title.png" alt="title" />
	</td>
	<td style="width:70px;height:60px;text-align:right;vertical-align:top;">
	    <div class="help"><button type="submit" name="help" value="help" style="cursor:pointer;height:25px;font-size:12px;">Help</button></div>
	</td><td style="width:85px;height:60px;text-align:right;vertical-align:top;">
	    <div class="logout"><button type="submit" name="logout" value="logout" style="cursor:pointer;height:25px;font-size:12px;">Logout</button></div>
	</td>
	</form>
	</tr>
	</table></td>
    </tr>
    <tr><td colspan='3' style="height:1px;border-bottom:gray thin solid;"></td></tr>
    <tr>
	<td colspan='2' style="height:150px;text-align:left;">
	    <form action="navigation.php" method="post">
	    <div id="navigation">
		<input type="radio" id="mycampaigns" name="nav" value="mycampaigns" onClick="this.form.action='navigation.php';this.form.submit()" /><label for="mycampaigns">My Campaigns</label>
		<input type="radio" id="who" name="nav" value="who" onClick="this.form.action='navigation.php';this.form.submit()" /><label for="who">Who</label>
		<input type="radio" id="what" name="nav" value="what" onClick="this.form.action='navigation.php';this.form.submit()" /><label for="what">What</label>
		<input type="radio" id="when" name="nav" value="when" checked="checked"  /><label for="when">When</label>
		<input type="radio" id="results" name="nav" value="results" onClick="this.form.action='navigation.php';this.form.submit()" /><label for="results">Results</label>
	    </div>
	    </form>
	</td>
	<td style="text-align:right;">
	    <img src="images/when.png" alt="when" />
	</td>
    </tr>
    <tr>
	<td colspan='3' style="height:70px;border-bottom:solid gray thin;border-top:solid gray thin;">
	    <table style="width:100%;text-align:center;"><tr>
	    <form action="when.php" method="post">
	    <td style="width:200px;"><?php
		$query_campaign_status = mysql_query("SELECT status FROM campaigns WHERE id = '{$campaign_id}' ");
		$array_campaign_status = mysql_fetch_array($query_campaign_status);
		if($array_campaign_status['status'] == 'active'){
		    $campaign_status = "Campaign Active";
		}elseif($array_campaign_status['status'] == 'inactive'){
		    $campaign_status = "Campaign Deactivated";
		}elseif($array_campaign_status['status'] == 'paused'){
		    $campaign_status = "Campaign Paused";
		}
		echo "<span style=\"color:#f4911e;\">".$campaign_status."</span>";
	    ?></td>
	    <td><div class="start"><button type="submit" name="start" value="<?php echo $campaign_id; ?>" style="cursor:pointer;height:25px;font-size:12px;">Start</button></div></td>
	    <td><div class="pause"><button type="submit" name="pause" value="<?php echo $campaign_id; ?>" style="cursor:pointer;height:25px;font-size:12px;">Pause</button></div></td>
	    <td><div class="stop"><button type="submit" name="deactivate" value="<?php echo $campaign_id; ?>" style="cursor:pointer;height:25px;font-size:12px;">Deactivate</button></div></td>
	    </form>
	    </tr></table>
	</td>
    </tr>
    <tr>
	<td style="height:200px;">
	    SETTINGS:
	</td>
	<td>
	    Maximum emails per year: <?php echo htmlentities($frequency); ?>
	    <br>
	    Start on 	<?php
			$start_date_read = strtotime($start_date);
			$start_date_read = date('M j, Y', $start_date_read);
			echo $start_date_read;
			?>
	    <br>
	    End on 	<?php
			$end_date_read = strtotime($end_date);
			$end_date_read = date('M j, Y', $end_date_read);
			echo $end_date_read;
			?>
	</td>
	<td rowspan='2'>
	    <input type="submit" alt="editpop" name="editpop" value="Edit Settings" id="editpop" >
	</td>
    </tr>
    <!--<tr><td colspan='3' style="height:1px;border-bottom:gray thin solid;"></td></tr>
    <tr>
	<td colspan='3'>
	    <?php echo $paypal_message; ?>
	</td>
    </tr>
	<td>
	    PURCHASE:
	</td>
	<td colspan='2' style="padding-top:20px;">
	    <table border='0' style="width:100%;">
		<tr>
		    <td style="padding-bottom:10px;width:330px;">
		        $19.99 for 1 month
		    </td>
		    <td>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			    <input type="hidden" name="cmd" value="_s-xclick">
			    <input type="hidden" name="return" value="http://myenpact.com/when.php?pid=8472&amp;level=1&amp;authid=<?php echo $_SESSION['auth_key']; ?>">
			    <input type="hidden" name="cancel_return" value="http://myenpact.com/when.php?pid=550&amp;authid=<?php echo $_SESSION['auth_key']; ?>">
			    <input type="hidden" name="hosted_button_id" value="8EYWR772XZX3J">
			    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_paynow_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			    <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		    </td>
		</tr>
		<tr>
		    <td style="padding-bottom:10px;">
			$113.94 for 6 months (5% off)
		    </td>
		    <td>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			    <input type="hidden" name="cmd" value="_s-xclick">
			    <input type="hidden" name="return" value="http://myenpact.com/when.php?pid=8472&amp;level=2&amp;authid=<?php echo $_SESSION['auth_key']; ?>">
			    <input type="hidden" name="cancel_return" value="http://myenpact.com/when.php?pid=550&amp;authid=<?php echo $_SESSION['auth_key']; ?>">
			    <input type="hidden" name="hosted_button_id" value="FKHL8TVF73KCL">
			    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_paynow_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			    <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		    </td>
		</tr>
		<tr>
		    <td style="padding-bottom:10px;">
			$215.89 for 12 months (10% off)<br>
		    </td>
		    <td>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			    <input type="hidden" name="cmd" value="_s-xclick">
			    <input type="hidden" name="return" value="http://myenpact.com/when.php?pid=8472&amp;level=3&amp;authid=<?php echo $_SESSION['auth_key']; ?>">
			    <input type="hidden" name="cancel_return" value="http://myenpact.com/when.php?pid=550&amp;authid=<?php echo $_SESSION['auth_key']; ?>">
			    <input type="hidden" name="hosted_button_id" value="TTT47N8JJQWG2">
			    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_paynow_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			    <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		    </td>
		</tr>
		<tr>
		    <td style="padding-bottom:10px;">
			$407.77 for 24 months (15% off)
		    </td>
		    <td>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			    <input type="hidden" name="cmd" value="_s-xclick">
			    <input type="hidden" name="return" value="http://myenpact.com/when.php?pid=8472&amp;level=4&amp;authid=<?php echo $_SESSION['auth_key']; ?>">
			    <input type="hidden" name="cancel_return" value="http://myenpact.com/when.php?pid=550&amp;authid=<?php echo $_SESSION['auth_key']; ?>">
			    <input type="hidden" name="hosted_button_id" value="PZZYL34AL2YEU">
			    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_paynow_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			    <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
			<?php //echo "<br>".$_SESSION['auth_key']; ?>
		    </td>
		</tr>-->
	    </table>
	</td>
    </tr>
</table>
</center>
<?php include("includes/footer.php");?>