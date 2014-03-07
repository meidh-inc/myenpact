<?php require_once("includes/session.php"); ?>
<?php require_once("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php include_once("includes/form_functions.php"); ?>
<?php confirm_logged_in(); ?>
<?php
// Kick out if not logged in
  /*  if (!logged_in()) {
	redirect_to("index.php");
    }
  */
// Get user info from session
    $userid = $_SESSION['userid'];
    $user_email = $_SESSION['useremail'];

// Set Timezone
    date_default_timezone_set('America/Chicago');

// Count number of campaigns
    $count_campaigns = mysql_query("SELECT id FROM campaigns WHERE user_id = '{$userid}' ");
    $campaign_count = mysql_num_rows($count_campaigns);
    
// Count campaigns in progress
    $today = date("Y-m-d");
    $count_started_campaigns = mysql_query("SELECT id FROM campaigns WHERE user_id = '{$userid}' AND status = 'active' AND start_date <= '{$today}' ");
    $started_count = mysql_num_rows($count_started_campaigns);

// NEW CAMPAIGN BUTTON CLICKED
    if (isset($_POST['new_campaign'])) { // Start a new campaign
	// get date one year from today
	    $oneyear = strtotime('+1 year', strtotime($today));
	    $oneyear = date('Y-m-d', $oneyear);
	
	// define default values for new campaign
	    $default_user_id = $userid;
	    $default_campaign_name = "New Campaign (".date("M j, Y").")";
	    $default_status = "active";
	    $default_from_email = $user_email;
	    $default_subject = "A question from MyENpact";
	    $default_intro = "Please take a moment to answer the following brief question. Your answer will provide us with much needed feedback about our service and/or product. Thank you for your time. Please feel free to contact me directly with any questions or comments.";
	    $date_created = date("Y-m-d h:i:s a");
	    $default_start_date = date("Y-m-d");
	    $default_end_date = $oneyear;
	    $default_frequency = 50;
	
	// insert the new campaign into the database
	    $create_new_campaign = mysql_query("INSERT INTO campaigns (
						   user_id, name, status, from_email, subject, intro, date_created,
						   start_date, end_date, frequency
						   ) VALUES (
						   '{$default_user_id}', '{$default_campaign_name}',
						   '{$default_status}',
						   '{$default_from_email}', '{$default_subject}', '{$default_intro}',
						   '{$date_created}', '{$default_start_date}',
						   '{$default_end_date}', '{$default_frequency}'
						   )");
	
	// get the new campaign's id
	    $get_campaign_id = mysql_query("SELECT id FROM campaigns
					       WHERE user_id = '{$default_user_id}'
					       AND name = '{$default_campaign_name}'
					       AND date_created = '{$date_created}' ");
	    $campaign_id_array = mysql_fetch_array($get_campaign_id);
	
	// set session id for campaign
	    $_SESSION['campaignid'] = $campaign_id_array['id'];
	
	// send user to destination
	    redirect_to('who.php');
    }


// DETAILS BUTTON CLICKED
    if (isset($_POST['settings'])) { // go to campaign.php for selected campaign
        // set session id for campaign
	    $_SESSION['campaignid'] = $_POST['settings'];
        
	// send user to destination
	    redirect_to('who.php');
    }

// RESULTS BUTTON CLICKED
    if (isset($_POST['results'])) { // go to campaign.php for selected campaign
        // set session id for campaign
	    $_SESSION['campaignid'] = $_POST['results'];
        
	// send user to destination
	    redirect_to('results.php');
    }

// STOP BUTTON CLICKED
    if (isset($_POST['stop'])) { // go to campaign.php for selected campaign
        // set session id for campaign
	    $_SESSION['campaignid'] = $_POST['stop'];
        
	// send user to destination
	    //redirect_to('campaign.php');
    }

// START BUTTON CLICKED
    if (isset($_POST['start'])) { // go to campaign.php for selected campaign
        // set session id for campaign
	    $_SESSION['campaignid'] = $_POST['start'];
        
	// send user to destination
	    //redirect_to('campaign.php');
    }

// DELETE BUTTON CLICKED
    if (isset($_POST['delete'])) { // go to campaign.php for selected campaign
        // set session id for campaign
	    $_SESSION['campaignid'] = $_POST['delete'];
        
	// send user to destination
	    //redirect_to('campaign.php');
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
    $( "input:submit", ".demo" ).button();
    $( "a", ".demo" ).click(function() { return false; });
});
$(function() {
    $( "button", ".gear" ).button({
        icons: {
            primary: "ui-icon-gear"
        }
    });
    $( "button", ".results" ).button({
        icons: {
            primary: "ui-icon-clipboard"
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
<center>
<table border='0' style="width:850px;background-image:url('images/texture2.jpg');padding:30px;margin-top:5px;">
    <tr>
	<td colspan='2'>
	<table style="width:100%;"><tr>
	<form action="mycampaigns.php" method="post">
	<td style="height:60px;">
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
    <tr><td colspan='2' style="height:1px;border-bottom:gray thin solid;"></td></tr>
    <tr>
	<td style="height:70px;text-align:center;vertical-align:middle;">
	    <span style="font-size:30px;padding-right:100px;">My Campaigns</span>
	</td>
	<td style="padding-top:10px;">
	    <img src="images/who.png" alt="who" style="height:45px;" />
	    &nbsp;&nbsp;&nbsp;
	    <img src="images/what.png" alt="what" style="height:45px;" />
	    &nbsp;&nbsp;&nbsp;
	    <img src="images/when.png" alt="when" style="height:45px;" />
	    &nbsp;&nbsp;&nbsp;
	    <img src="images/results.png" alt="results" style="height:45px;" />
	</td>
    </tr>
    <tr>
	<td colspan='2' style="height:50px;text-align:center;">
	    <table  style="width:100%;margin-bottom:40px;border-bottom:gray thin solid;border-top:gray thin solid;">
		<tr>
		    <td>
			<span style="font-size:40px;"><?php echo $campaign_count; ?></span>
			<br style="margin:0px;padding:0px;line-height:2px;">
			<span style="font-size:12px;color:gray;">Total Campaigns</span>
		    </td>
		    <td>
			<span style="font-size:40px;"><?php echo $started_count; ?></span>
			<br style="line-height:2px;">
			<span style="font-size:12px;color:gray;">Campaigns in Progress</span>
		    </td>
		    <td>
			<form action="mycampaigns.php" method="post">
			<div class="demo"><input type="submit" name="new_campaign" value="Start New Campaign"></div>
			</form>
		    </td>
		</tr>
	    </table>
	</td>
    </tr>
    <tr>
	<td colspan='2' style="text-align:left;">
	    <table border='0' style="width:100%;">
		<form action="mycampaigns.php" method="post">
	    <?php // Get list of campaigns
	    // grab campaign list
		$get_campaigns = mysql_query("SELECT id, name, status FROM campaigns WHERE user_id = '{$userid}' ");
		
	    // count the number of rows, to determine what to list for campaigns
	        if(mysql_num_rows($get_campaigns) != 0){  // campaigns were found, list them
		
		    while($campaign_array = mysql_fetch_array($get_campaigns)){
			
			$get_send_count = mysql_query("SELECT id FROM results WHERE campaign_id = '{$campaign_array['id']}'
						      AND sent_date != '{0000-00-00}' ");
			$send_count = mysql_num_rows($get_send_count);
			
			$get_response_count = mysql_query("SELECT id FROM results WHERE campaign_id = '{$campaign_array['id']}'
						      AND answer_date != '{0000-00-00}' ");
			$response_count = mysql_num_rows($get_response_count);
			
			if($campaign_array['status'] != 'active'){
			    $campaign_color = "color:gray;";
			}else{
			    $campaign_color = "";
			}
			
			echo "<tr><td style=\"padding-bottom:20px;".$campaign_color."\">".$campaign_array['name']."</td>
			    <td style=\"padding-bottom:20px;".$campaign_color."\">(".$send_count." sends/".$response_count." engagements)</td>
			    <td style=\"padding-bottom:20px;\"><div class=\"gear\"><button type=\"submit\" name=\"settings\" value=\"".$campaign_array['id']."\" style=\"cursor:pointer;height:25px;font-weight:bold;font-size:12px;\">Settings</button></div></td>
			    <td style=\"padding-bottom:20px;\"><div class=\"results\"><button type=\"submit\" name=\"results\" value=\"".$campaign_array['id']."\" style=\"cursor:pointer;height:25px;font-weight:bold;font-size:12px;\">Results</button></div></td>
			    </tr>";
		    }
		    
		}else{  // nothing found
			echo "no campaigns found";
		}
	    ?>
		</form>
	    </table>
	</td>
    </tr>
</table>
</center>

<?php include("includes/footer.php");?>