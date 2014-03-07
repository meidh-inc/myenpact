<?php require_once("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php include_once("includes/form_functions.php"); ?>
<?php
// code run by server cron to send emails from campaigns

// Set Timezone
date_default_timezone_set('America/Chicago');

// Today's date
$today = date("Y-m-d");

// Define send_id
$send = 1;

// get recipient list from queue and loop through
$get_recipients = mysql_query("SELECT id, campaign_id, recipient_id, recipient_email FROM results WHERE
				    send_id = '{$send}'
				    AND sent_date = '0000-00-00'
				");
    
while($array_recipients = mysql_fetch_array($get_recipients)){  // looping through array of recipients
    
    $result_id = $array_recipients['id'];
    $recipient_id = $array_recipients['recipient_id'];
    $email_to = $array_recipients['recipient_email'];
    $running_campaign = $array_recipients['campaign_id'];
    
    //get FROM EMAIL, main subject
    $get_from = mysql_query("SELECT from_email, subject FROM campaigns WHERE id = '{$array_recipients['campaign_id']}' ");
    $array_from = mysql_fetch_array($get_from);
    $from_email = $array_from['from_email'];
    $main_subject = $array_from['subject'];
    
    
    // get question from pool at random
    $get_send_question = mysql_query("SELECT id, question, subject, url FROM questions
                                        WHERE status = 'active'
                                        AND campaign_id = '{$array_recipients['campaign_id']}'
                                        ORDER BY rand()");
    $count_send_questions = mysql_num_rows($get_send_question);
    $count_send_questions_minus = $count_send_questions - 1;
    if($count_send_questions_minus == 0){
	$count_send_questions_minus = 1;
    }
    
    while($array_send_question = mysql_fetch_array($get_send_question)){
	
	//find question in results within $count_send_questions rows
	$find_question = mysql_query("SELECT * FROM results WHERE
					campaign_id = '{$array_recipients['campaign_id']}'
					AND recipient_id = '{$recipient_id}'
					ORDER BY id DESC LIMIT ".$count_send_questions_minus."
					");
	$find_question_array = mysql_fetch_array($find_question);
	
	if(!in_array($array_send_question['id'],$find_question_array)){
	    $send_question_id = $array_send_question['id'];
	    $send_question = $array_send_question['question'];
	    $question_subject = $array_send_question['subject'];
	    $question_link = $array_send_question['url'];
	    break;
	}
	
    }
    
    $answer_no = array();
    $answer = array();
    $get_answers = mysql_query("SELECT id, answer, url FROM answers
                                    WHERE campaign_id = '{$array_recipients['campaign_id']}'
                                    AND question_id = '{$array_send_question['id']}'
                                    ORDER BY RAND()");
    $num_answers = mysql_num_rows($get_answers);
    
    while ($row = mysql_fetch_array($get_answers)){
        $answer_no[] = $row['id'];
        $answer_link[] = $row['url'];
        $answer[] = $row['answer'];
    }
    
    //get intro paragraph
    $get_intro = mysql_query("SELECT intro FROM campaigns WHERE id = '{$array_recipients['campaign_id']}' ");
    $array_intro = mysql_fetch_array($get_intro);
    $intro = $array_intro['intro'];
    
    //Send the email
    require_once("phpmailer/class.phpmailer.php");
    $mail = new PHPMailer();
    
    //Get Question more info link
    if($question_link != ""){
        $qlink = "<a href=\"http://myenpact.com/linksend.php?q=".$send_question_id."&amp;a=0&amp;rid=".$recipient_id."&amp;cid=".$running_campaign."\">(more info)</a>";
    }else{
        $qlink = "";
    }
    
    
    $body = $intro."<br><h3>".$send_question." ".$qlink."</h3>
            <ul>";
    
    for($loop=0; $loop<$num_answers; $loop++){
	
	// Get Answer more info link
	if($answer_link[$loop] != ""){
	    $alink = "<a href=\"http://myenpact.com/linksend.php?q=".$send_question_id."&amp;a=".$answer_no[$loop]."&amp;rid=".$recipient_id."&amp;cid=".$running_campaign."\">(more info)</a>";
	}else{
	    $alink = "";
	}
	
        $body .= "<li style=\"margin-left:0px;padding-left:0px;\"><a href=\"http://myenpact.com/emailreceiver.php?q=".$send_question_id."&amp;a=".$answer_no[$loop]."&amp;rid=".$recipient_id."&amp;id=".$result_id."\">".$answer[$loop]."</a> ".$alink."</li>";
    }
    
    $body .="</ul>
            <br>Please only use these links once.
            <br><br>Thank you for participating via <a href=\"http://myenpact.com/\">MyENpact</a>.
            <font style=\"font-size:12px;\">
            <!--<br><br>No more emails please: <a href=\"http://myenpact.com/emailunsubscribe.php?id=$recipient_id\">unsubscribe</a>-->
            </font>";
    
    //echo $body;
    $mail->Host       = "dedrelay.secureserver.net";  // this relay server was recommended by godaddy via an email on 9/20/12
    $mail->Port       = 25; // set the SMTP port for the server, godaddy says port 25
    
    $mail->SetFrom($from_email);
    $mail->AddReplyTo("eric.nelson@meidh.com","Eric Nelson");
    $mail->Subject    = $main_subject.": ".$question_subject;
    $mail->AltBody    = "To view the message, please use an HTML compatible email viewer"; // optional, comment out and test
    $mail->MsgHTML($body);
    $mail->AddAddress($email_to);
    if(!$mail->Send()) {  //Email Failed
        echo "Mailer Error: " . $mail->ErrorInfo ."/n";
    } else {  //Email Success
	
        // Update the results table with the sent date
        $update_results_sent = "UPDATE results SET
                                    question_id = '{$send_question_id}',
				    sent_date = '{$today}'
                                    WHERE id = {$result_id}";
        $result_results_sent = mysql_query($update_results_sent);
	
	// Echo success
        echo "Message sent! to: Someone at ".$email_to." \n";
        }
	
    }
    
?>