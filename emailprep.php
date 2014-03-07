<?php require_once("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php include_once("includes/form_functions.php"); ?>
<?php
// code run by server cron to prepare the email que

// Set Timezone
date_default_timezone_set('America/Chicago');

// Today's date
$today = date("Y-m-d");


//mark campaigns to not run if they don't meet the criteria
$update_bad_campaigns = mysql_query("UPDATE campaigns SET run = 'no' ");


//Determine List of campaigns to run through

//Criteria for campaigns to run
// - must be paid
// - must be active
// - must have verified from_email (WHERE verified = 'yes' OR verified = '')
// - must be between start and end dates
// - must meet frequency calculations
$get_campaigns = mysql_query("SELECT id, name, frequency FROM campaigns WHERE
				paid = 'yes'
				AND status = 'active'
				AND (verified = 'yes'
				OR verified = '')
				AND start_date <= '{$today}'
				AND end_date >= '{$today}'
				");
while($array_campaigns = mysql_fetch_array($get_campaigns)){
    
    //calculate frequency
    // Get the last time a campaign was sent out
    $get_last_run = mysql_query("SELECT sent_date FROM results
                                WHERE campaign_id = '{$array_campaigns['id']}'
                                ORDER BY sent_date DESC LIMIT 1 ");
    $array_last_run = mysql_fetch_array($get_last_run);
    
    // calculate the time span
    $desired_frequency = $array_campaigns['frequency']/365;
    
    
    if($desired_frequency <= 1){  //multiple days between emails
	//send emails every $email_interval days
	$email_interval = round(365/$array_campaigns['frequency']);
	
	//it's been $last_interval days
	$last_interval = round((abs(strtotime($today) - strtotime($array_last_run['sent_date'])))/(60*60*24));
	
	//determine run status
	if($email_interval <= $last_interval){  //enough time has passed, set this campaign to run
	    $update_this_campaign = mysql_query("UPDATE campaigns SET run = 'yess' WHERE id = '{$array_campaigns['id']}' ");
	} //not enough time has passed, leave it as do not run.
	
	
    }elseif($desired_frequency > 1){  //multiple emails per day
	//send $emails_per_day emails per day
	$emails_per_day = round($desired_frequency);
	
	//count the number of emails send with this campaign id today
	$get_count_sends = mysql_query("SELECT id FROM results WHERE
					campaign_id  ='{$array_campaigns['id']}'
					AND sent_date = '{$today}' ");
	$count_sends = mysql_num_rows($get_count_sends);
	
	//determine run status
	if($count_sends < $emails_per_day){  //daily email limit has not been met, set this campaign to run
	    $update_this_campaign = mysql_query("UPDATE campaigns SET run = 'yesm' WHERE id = '{$array_campaigns['id']}' ");
	}  //daily email limit has been met, leave it as do not run
	
    }
    
} //END: Determine List of campaigns to run through


//Determine list of recipients

//Criteria for recipients to be put into queue
// - must be subscribed
// - must be valid
// - must have passing campaign id
$grab_recipients = mysql_query("SELECT id, campaign_id, email FROM recipients WHERE subscribed = 'yes' ");

// go through all passing recipients
while($array_grab_recipients = mysql_fetch_array($grab_recipients)){ 
    
    //only want recipients of passing campaigns
    $find_campaign = mysql_query("SELECT run, frequency FROM campaigns WHERE id = '{$array_grab_recipients['campaign_id']}' ");
    $campaign_run = mysql_fetch_array($find_campaign);
    if(($campaign_run['run'] == 'yesm') || ($campaign_run['run'] == 'yess') ){  //run status confirmed
	
	//determine whether the recipient runs
	
	// determine the daily run limit
	if($campaign_run['run'] == 'yesm'){ //allow for multiple occurance
	    
	    $run_count = round($campaign_run['frequency']/365);
	    
	}elseif($campaign_run['run'] == 'yess'){ //allow for single occurance
	    
	    $run_count = 1;
	}
	
	//count the number of times the recipient email is already in the queue for today
	$check_queue = mysql_query("SELECT id FROM results WHERE
				   recipient_email = '{$array_grab_recipients['email']}'
				   AND campaign_id = '{$array_grab_recipients['campaign_id']}'
				   AND queue_date = '{$today}'
				   ");
	$count_queue = mysql_num_rows($check_queue);
	
	if($count_queue < $run_count){ //proceed with adding a queue for this recipient
	    
	    //generate a unique sender_id
	    if($count_queue >= 1){ //check to make sure new send_id is unique
		$i = 1;
		do{
		    $send_id = rand(1,5);
		    $check_send_id = mysql_query("SELECT send_id FROM results WHERE
						    recipient_email = '{$array_grab_recipients['email']}'
						    AND queue_date = '{$today}'
						    AND send_id = '{$send_id}'
						 ");
		    $count_send_id = mysql_num_rows($check_send_id);
		    $i++;
		    if($i >= 6){
			break;
		    }
		}while($count_send_id >= 1);
		
	    }else{ //just generate a send_id
		$send_id = rand(1,5);
	    }
	    
	    //enter email queue into results table
	    $create_new_queue = mysql_query("INSERT INTO results (
					    campaign_id, recipient_id, recipient_email, send_id, queue_date
					    )VALUES(
					    '{$array_grab_recipients['campaign_id']}',
					    '{$array_grab_recipients['id']}',
					    '{$array_grab_recipients['email']}',
					    '{$send_id}', '{$today}'
					    )");
	    
	} //daily email limit for campaign has been reached
	
    }  //this campaign should not run, check next recipient
    
} // END: Determine list of recipients

?>