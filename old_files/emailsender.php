<?php require_once("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php include_once("includes/form_functions.php"); ?>
<?php
// code run by server cron to send emails from campaigns

// Set Timezone
date_default_timezone_set('America/Chicago');

// Today's date
$today = date("Y-m-d");

// Update Run Status
$run_status_update = mysql_query("SELECT id, frequency FROM campaigns WHERE status = 'active' ");

while($run_status_update_array = mysql_fetch_array($run_status_update)){
    // Get the last time a campaign was sent out
    $get_last_run = mysql_query("SELECT sent_date FROM results
                                WHERE campaign_id = '{$run_status_update_array['id']}'
                                ORDER BY sent_date DESC LIMIT 1 ");
    $array_last_run = mysql_fetch_array($get_last_run);
    
    // calculate the time span
    $desired_frequency = round(365/$run_status_update_array['frequency']);
    $last_interval = ((abs(strtotime($today) - strtotime($array_last_run['sent_date'])))/(60*60*24));
    
    if($desired_frequency <= $last_interval){
        $run_status = "yes";
    }else{
        $run_status = "no";
    }
    
    $update_run = "UPDATE campaigns SET
			    run = '{$run_status}'
			    WHERE id = '{$run_status_update_array['id']}' ";
    $result_update_run = mysql_query($update_run);
}


// Get list of campaign_id's that need to run
    
        // Paid (check)
        // start date has passed (check)
        // status is active (double checked)
        // calculate desired time between sends (check)
        // desired time between sends has passed (check)
    
    
    $get_campaigns_run = mysql_query("SELECT id, from_email, subject, organization FROM campaigns
                                     WHERE status = 'active' AND start_date <= '{$today}' AND end_date > '{$today}'
                                     AND run = 'yes' AND paid = 'yes' ");
    
    while($array_campaigns_run = mysql_fetch_array($get_campaigns_run)){
        // Get From and Subject
            $from_email = $array_campaigns_run['from_email'];
            $main_subject = $array_campaigns_run['subject'];
            $running_campaign = $array_campaigns_run['id'];
        
        
        // get recipient list and loop through
            $get_recipients = mysql_query("SELECT id, email FROM recipients WHERE campaign_id = '{$array_campaigns_run['id']}' AND subscribed = 'yes' ");
            
            while($array_recipients = mysql_fetch_array($get_recipients)){  // looping through array of recipients
                
                $recipient_id = $array_recipients['id'];
                $email_to = $array_recipients['email'];
                
                // get question from pool at random
                    $get_send_question = mysql_query("SELECT id, question, subject, url FROM questions
                                                     WHERE status = 'active'
                                                     AND campaign_id = '{$array_campaigns_run['id']}'
                                                     ORDER BY rand() LIMIT 1");
                    $array_send_question = mysql_fetch_array($get_send_question);
                    
                    $send_question_id = $array_send_question['id'];
                    $send_question = $array_send_question['question'];
                    $question_subject = $array_send_question['subject'];
                    $question_link = $array_send_question['url'];
                    
                    $answer_no = array();
                    $answer = array();
                    $get_answers = mysql_query("SELECT id, answer, url FROM answers
                                               WHERE campaign_id = '{$array_campaigns_run['id']}'
                                               AND question_id = '{$array_send_question['id']}'
                                               ORDER BY RAND()");
                    $num_answers = mysql_num_rows($get_answers);
                    while ($row = mysql_fetch_array($get_answers)){
                        $answer_no[] = $row['id'];
                        $answer_link[] = $row['url'];
                        $answer[] = $row['answer'];
                    }
                    
                    // enter email into results table
                        $enter_details = mysql_query("INSERT INTO results (campaign_id, recipient_id, question_id)
    	        					   VALUES ('{$array_campaigns_run['id']}', '{$recipient_id}', '{$array_send_question['id']}' )");
                    
                    // get entry id on results table
                        $get_result_id = mysql_query("SELECT id FROM results WHERE campaign_id = '{$array_campaigns_run['id']}'
                                                     AND recipient_id = '{$recipient_id}' AND question_id = '{$array_send_question['id']}'
                                                     ORDER BY id DESC LIMIT 1");
                        $array_result_id = mysql_fetch_array($get_result_id);
                        $result_id = $array_result_id['id'];
                    
                    
                    //Send the email
                    require_once("phpmailer/class.phpmailer.php");
                    $mail = new PHPMailer();
                    
                    //Get Question more info link
                    if($question_link != ""){
                        $qlink = "<a href=\"http://myenpact.com/linksend.php?q=".$send_question_id."&amp;a=0&amp;rid=".$recipient_id."&amp;cid=".$running_campaign."\">(more info)</a>";
                    }else{
                        $qlink = "";
                    }
                    
                    
                    $body = "<h3>".$send_question." ".$qlink."</h3>
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
                            <br><br>Thank you for participating!
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
                                            sent_date = '{$today}'
                                            WHERE id = {$result_id}";
                    $result_results_sent = mysql_query($update_results_sent);
                    
                    // Echo success
                    echo "Message sent! to: Someone at ".$email_to." \n";
                    }
                    
            }
    }
?>