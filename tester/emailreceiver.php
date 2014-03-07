<?php require_once("includes/session.php"); ?>
<?php require_once("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php include_once("includes/form_functions.php"); ?>
<?php 
// Set Timezone
date_default_timezone_set('America/Chicago');

// Today's date
$today = date("Y-m-d");


if (isset($_GET['q'])) { //coming from question link

// Get info from link
    $result_id = $_GET['id'];	// id in results table
    $question_id = $_GET['q'];  //Question Number
    $answer_id = $_GET['a'];  //Answer Number
    $recipient_id = $_GET['rid'];  //id number of recipient

// Check for validity
    //all numbers should match, must be sent, must not be answered, must only have one result
    $valid_result = mysql_query("SELECT id, answer_date FROM results WHERE id = '{$result_id}'
				AND recipient_id = '{$recipient_id}'
				AND question_id = '{$question_id}'
				AND sent_date > '0000-00-00' ");
    $count_valid_results = mysql_num_rows($valid_result);
    $array_valid_results = mysql_fetch_array($valid_result);
    
// Deliver appropriate message and write to DB if everything passes
    if($array_valid_results['answer_date'] != '0000-00-00' ){  //Fail: an answer date exists, has been answered.
	$message = "It appears you have already answered this question (error 100). Thank you!";
	
    }elseif($count_valid_results > 1){  //Fail: multiple entries in results table match the link criteria
	$message = "There was a problem recording your answer (error 101).";
	
    }elseif($count_valid_results < 1){  //Fail: no entries in results table match the link criteria
	$message = "There was a problem recording your answer (error 102).";
	
    }else{  //Pass: only one result of matching identifiers, has not been answered
	
	$submit_answer = "UPDATE results SET
			    answer_id = '{$answer_id}',
			    answer_date = '{$today}'
			    WHERE id = {$result_id}";
	$result_submit = mysql_query($submit_answer);
	
	$message = "Your answer has successfully been recorded.  Thank you!
		    <br>
		    <br>
		    <br>
		    You may close this tab or window.";
    }
}


if (isset($_GET['fid'])) {  //validating new From email address

$from_email_edit = $_GET['fid'];
$key = $_GET['kid'];
$campaign_id = $_GET['cid'];

//Check verification key
$check_verification = mysql_query("SELECT verified FROM campaigns WHERE id = '{$campaign_id}' ");
$array_verification = mysql_fetch_array($check_verification);

if($array_verification['verified'] == $key){  // account verified!
	    $update_verified = mysql_query("UPDATE campaigns SET
						verified = 'yes',
						from_email = '{$from_email_edit}'
					    WHERE id = '{$campaign_id}' ");
	    $message = "Your new From address has been validated and updated.";
}else{ // verification keys do not match
	    $message = "Verification Failed. Please email info@myenpact.com for assistance.";
}
}


if (isset($_GET['unsubscribe'])) {  //recipient clicked unsubscribe
/*
$from_email_edit = $_GET['fid']
$campaign_id = $_GET['cid']

$query_update_from = "UPDATE campaigns SET
			from_email = '{$from_email_edit}'
		    WHERE id = {$campaign_id}";
$result_from = mysql_query($query_update_from);

$message = "You have been unsubscribed.";
*/
}




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
	    <!--<div class="help"><button type="submit" name="help" value="help" style="cursor:pointer;height:25px;font-size:12px;">Help</button></div>-->
	</td>
	</form>
	</tr></table>
	</td>
    </tr>
    <tr><td colspan='2' style="height:1px;border-bottom:gray thin solid;"></td></tr>
    <tr>
	<td colspan='2' style="height:150px;text-align:center;">
	    <?php echo $message; ?>
	    
	</td>
    </tr>
    <tr>
	<td colspan='2' style="height:50px;text-align:center;">
	    
	    
	</td>
    </tr>
    <tr>
	<td colspan='2' style="height:100px;text-align:center;">
	    Learn more about <a href="http://myenpact.com/">MyENpact</a>
	    
	</td>
    </tr>
    <tr>
	<td colspan='2' style="text-align:center;">
	    
	    
	</td>
    </tr>
</table>
</center>
<?php include("includes/footer.php");?>