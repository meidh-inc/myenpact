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

// Set Timezone
date_default_timezone_set('America/Chicago');


// Send Count
    $get_send_count = mysql_query("SELECT id FROM results WHERE campaign_id = '{$campaign_id}'
				    AND question_id != '0' AND sent_date > '0000-00-00' ");
    $send_count = mysql_num_rows($get_send_count);
    
// Response Count
    $get_response_count = mysql_query("SELECT id FROM results WHERE campaign_id = '{$campaign_id}'
					AND answer_id != '0' AND answer_date > '0000-00-00' ");
    $response_count = mysql_num_rows($get_response_count);










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

    $( "#navigation" ).buttonset();
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

<center>
<table border='0' style="width:850px;background-image:url('images/texture2.jpg');padding:30px;margin-top:5px;">
    <tr>
	<td colspan='2'>
	<table style="width:100%;"><tr>
	<form action="results.php" method="post">
	<td colspan='1' style="height:60px;">
	    <img src="images/title.png" alt="title" />
	</td>
	<td style="width:70px;height:60px;text-align:right;vertical-align:top;">
	    <div class="help"><button type="submit" name="help" value="help" style="cursor:pointer;height:25px;font-size:12px;">Help</button></div>
	</td><td style="width:85px;height:60px;text-align:right;vertical-align:top;">
	    <div class="logout"><button type="submit" name="logout" value="logout" style="cursor:pointer;height:25px;font-size:12px;">Logout</button></div>
	</td>
	</form>
	</tr></table>
	</td>
    </tr>
    <tr><td colspan='2' style="height:1px;border-bottom:gray thin solid;"></td></tr>
    <tr>
	<td style="height:150px;text-align:left;">
	    <form action="navigation.php" method="post">
	    <div id="navigation">
		<input type="radio" id="mycampaigns" name="nav" value="mycampaigns" onClick="this.form.action='navigation.php';this.form.submit()" /><label for="mycampaigns">My Campaigns</label>
		<input type="radio" id="who" name="nav" value="who" onClick="this.form.action='navigation.php';this.form.submit()" /><label for="who">Who</label>
		<input type="radio" id="what" name="nav" value="what" onClick="this.form.action='navigation.php';this.form.submit()" /><label for="what">What</label>
		<input type="radio" id="when" name="nav" value="when" onClick="this.form.action='navigation.php';this.form.submit()" /><label for="when">When</label>
		<input type="radio" id="results" name="nav" value="results" checked="checked" /><label for="results">Results</label>
	    </div>
	    </form>
	</td>
	<td style="text-align:right;">
	    <img src="images/results.png" alt="results" />
	</td>
    </tr>
    <tr><td colspan='2' style="height:1px;border-bottom:solid gray thin;"></td></tr>
    <tr>
	<td colspan='2' style="height:50px;text-align:center;">
	    <table  style="width:100%;">
		<tr>
		    <td>
			<span style="font-size:40px;"><?php echo $send_count; ?></span>
			<br style="margin:0px;padding:0px;line-height:2px;">
			<span style="font-size:12px;color:gray;">Sends</span>
		    </td>
		    <td>
			<span style="font-size:40px;"><?php echo $response_count; ?></span>
			<br style="line-height:2px;">
			<span style="font-size:12px;color:gray;">Responses</span>
		    </td>
		</tr>
	    </table>
	</td>
    </tr>
    <tr><td colspan='2' style="height:1px;border-bottom:solid gray thin;"></td></tr>
    <tr>
	<td colspan='2' style="height:100px;text-align:center;">
	    
	    
	</td>
    </tr>
    <tr><td colspan='2' style="height:1px;border-bottom:solid gray thin;"></td></tr>
    <tr>
	<td colspan='2' style="text-align:left;">
	    <table border='0' style="width:100%;">
		<tr>
		    <td>
			Question/Answers
		    </td>
		    <td style="text-align:center;width:115px;">
			Answered
		    </td>
		    <td style="text-align:center;width:115px;">
			Links Clicked
		    </td>
		</tr>
		<?php  //All Questions and Answers with counts
		    
		    $get_all_results = mysql_query("SELECT id, question, status FROM questions
						   WHERE campaign_id = '{$campaign_id}' ");
		    
		    if(mysql_num_rows($get_all_results) != 0){
		    
			while($array_all_results = mysql_fetch_array($get_all_results)){
			    
			    if($array_all_results['status'] == 'inactive'){
				$status = "color:gray;";
			    }else{
				$status = "";
			    }
			    
			    //Print the question
			    echo "  <tr>
					<td colspan=\"1\" style=\"border-top:thin solid silver;padding-top:20px;".$status."\">
					    ".$array_all_results['question']."
					</td>
					<td style=\"border-top:thin solid silver;\"></td>
					<td style=\"border-top:thin solid silver;\"></td>
				    </tr>";
			    
			    //get this question's Answers
			    $get_answers_list = mysql_query("SELECT id, answer FROM answers
							    WHERE campaign_id = '{$campaign_id}'
							    AND question_id = '{$array_all_results['id']}' ");
			    
			    //Loop through and print all the answers
			    while($answers_list_array = mysql_fetch_array($get_answers_list)){
				
				//Get response count
				    $get_answer_response = mysql_query("SELECT id FROM results WHERE
								       campaign_id = '{$campaign_id}'
								       AND question_id = '{$array_all_results['id']}'
								       AND answer_id = '{$answers_list_array['id']}' ");
				    $answer_response = mysql_num_rows($get_answer_response);
				    
				//Get link tracker count
				    //question clicks
				    
				    
				    //answer clicks
				    $get_answer_clicks = mysql_query("SELECT id FROM clicks WHERE
								       campaign_id = '{$campaign_id}'
								       AND question_id = '{$array_all_results['id']}'
								       AND answer_id = '{$answers_list_array['id']}' ");
				    $answer_clicks = mysql_num_rows($get_answer_clicks);
				
				echo "	<td style=\"padding-left:30px;".$status."\">".$answers_list_array['answer']."</td>
					<td style=\"text-align:center;".$status."\">".$answer_response."</td>
					<td style=\"text-align:center;".$status."\">".$answer_clicks."</td>
					</tr>";
				
			    }
			}
		    }else{
			echo "no questions found";
		    }
		?>
	    </table>
	</td>
    </tr>
</table>
</center>
<?php include("includes/footer.php");?>