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
    $valid_result = mysql_query("SELECT id, answer_date 
        FROM results 
        WHERE id = '{$result_id}'
        AND recipient_id = '{$recipient_id}'
        AND question_id = '{$question_id}'
        AND sent_date > '0000-00-00' ");
    $count_valid_results = mysql_num_rows($valid_result);
    $array_valid_results = mysql_fetch_array($valid_result);
    
// Deliver appropriate message and write to DB if everything passes
    if($array_valid_results['answer_date'] != '0000-00-00' ){  //Fail: an answer date exists, has been answered.
	$message = "It appears you have already answered this question (error 100). Thank you!";
	
    } elseif($count_valid_results > 1) {  //Fail: multiple entries in results table match the link criteria
	$message = "There was a problem recording your answer (error 101).";
	
    } elseif($count_valid_results < 1) {  //Fail: no entries in results table match the link criteria
	$message = "There was a problem recording your answer (error 102).";
	
    } else {  //Pass: only one result of matching identifiers, has not been answered
	
	$submit_answer = "UPDATE results SET
			    answer_id = '{$answer_id}',
			    answer_date = '{$today}'
			    WHERE id = {$result_id}";
	$result_submit = mysql_query($submit_answer);
        
	$message = "Your answer has successfully been recorded.  Thank you!
		    <br>
		    <br>
		    You may close this tab or window.";
    }
    
    $chart_array = array();
        
    $sql_t = "SELECT id, campaign_id, answer_id 
        FROM results 
        WHERE question_id = " . $question_id;
    $answer_chart = mysql_query($sql_t);
    while ($answers_array = mysql_fetch_array($answer_chart)) {
        if($answers_array['answer_id'] != 0) {
            $sql_c = "SELECT * 
                FROM results 
                WHERE answer_id = " . $answers_array['answer_id'];
            $answers_chart = mysql_query($sql_c);
            $answers_num = mysql_num_rows($answers_chart);
            $chart_array[$answers_array['answer_id']] = $answers_num;
        }
        $sql_logo = "SELECT id, logo 
            FROM campaigns 
            WHERE id = " . $answers_array['campaign_id'];
        $logo_query = mysql_query($sql_logo);
        $logo_array = mysql_fetch_array($logo_query);
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

if (isset($_GET['uid'])) {  //recipient clicked unsubscribe

$unsubscribe_id = $_GET['uid'];
$delete_id = $_GET['did'];

$query_update_from = "UPDATE recipients SET
			subscribed = 'no'
		    WHERE id = " . $unsubscribe_id;
$result_from = mysql_query($query_update_from);
$query_delete_result = "DELETE FROM results
                           WHERE id = " . $delete_id;
$result_delete = mysql_query($query_delete_result);

$message = "You have been unsubscribed.";

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
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

    // Load the Visualization API and the barchart package.
    google.load('visualization', '1.0', {'packages':['corechart']});
    
    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawChart);
    
    // Callback that creates and populates a data table,
    // instantiates the bar chart, passes in the data and
    // draws it.
    function drawChart() {
        <?php
        $js_array = "[['Question', 'Number of Answers'],";
        foreach($chart_array as $key => $value) {
            $sql_chart = "SELECT answer 
                FROM answers 
                WHERE id = " . $key;
            $chart_question = mysql_query($sql_chart);
            $chart_array = mysql_fetch_array($chart_question, MYSQL_ASSOC);
            
            $js_array .= "['" . $chart_array['answer'] . "', " . $value . "],";
        }
        $js_array .= "]";
        echo "var javascript_array = " . $js_array . ";\n";
        ?>
        // Create the data table.
        var data = new google.visualization.arrayToDataTable(javascript_array, false);
        
        // Set chart options
        var options = {'title':'What have other people answered:', 
                    'width':600, 
                    'height':300};
        
        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);
    }
        
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
	<td colspan='2' style="height:100px;text-align:center;">
	    <?php echo $message; ?>
	</td>
    </tr>
    <tr>
	<td colspan='2' style="text-align:center;">
            <!--Div that will hold the column chart-->
            <?php if($logo_array['logo']) { ?>
            <img src="images/logos/<?php echo $logo_array['logo']; ?>" name="logo" height="100"><br />
            <?php } ?>
            <?php if($logo_array['id'] != 31) { ?>
            <div id="chart_div" style="width:600; height:300; display:inline-block;"></div>
            <?php } ?>
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