<?php require_once("includes/session.php"); ?>
<?php require_once("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php include_once("includes/form_functions.php"); ?>
<?php 
// Set Timezone
date_default_timezone_set('America/Chicago');

// Today's date
$today = date("Y-m-d");

// Get info from link
    $question_id = $_GET['q'];  //Question Number
    $answer_id = $_GET['a'];  //Answer Number
    $recipient_id = $_GET['rid'];  //id number of recipient
    $campaign_id = $_GET['cid'];  //id number of recipient

// recover $campaign_id if missing
if($campaign_id == ""){
    $get_cid = mysql_query("SELECT campaign_id FROM results WHERE
			   recipient_id = '{$recipient_id}'
			   AND question_id = '{$question_id}'
			   ORDER BY id DESC LIMIT 1");
    $array_cid = mysql_fetch_array($get_cid);
    $campaign_id = $array_cid['campaign_id'];
}


// Get the link
    if($answer_id == 0){
	$get_link = mysql_query("SELECT url FROM questions WHERE id = '{$question_id}' AND campaign_id = '{$campaign_id}' ");
	$link_array = mysql_fetch_array($get_link);
    }else{
	$get_link = mysql_query("SELECT url FROM answers WHERE id = '{$answer_id}' AND campaign_id = '{$campaign_id}' ");
	$link_array = mysql_fetch_array($get_link);
    }
    

// Validate the link and forward
    if($link_array['url'] != ""){
        $url = $link_array['url'];
        function isValidURL($url){
            return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
        }
        if(!isValidURL($url)){
            $forward_to = "http://".$url;
        }else{
            $forward_to = $url;
        }
	    // Update Database
		$update_clicks = mysql_query("INSERT INTO clicks ( campaign_id, recipient_id, question_id, answer_id, click_date
						   )VALUES( '{$campaign_id}', '{$recipient_id}', '{$question_id}', '{$answer_id}', '{$today}' )");
		
            //echo $forward_to;
            redirect_to($forward_to);
    }else{
        echo "no link available";
    }

?>