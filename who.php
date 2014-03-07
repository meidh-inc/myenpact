<?php require_once("includes/session.php"); ?>
<?php require_once("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/pagination.php"); ?>
<?php include_once("includes/form_functions.php"); ?>
<?php confirm_logged_in(); ?>
<?php 

// Get info from session
    $userid = $_SESSION['userid'];
    $user_email = $_SESSION['useremail'];
    $campaign_id = $_SESSION['campaignid'];

// Set Timezone
    date_default_timezone_set('America/Chicago');
    
    $max_file_size = 1048576;

// Process Actions and Page Load
    if (isset($_POST['edit'])) { // The email information update button was clicked
    
    // start array to hold edit window errors
	$errors_edit = array();
    
    // perform validations on the edited data
	$required_fields_edit = array('from_email_edit');
	$errors_edit = array_merge($errors_edit, check_required_fields($required_fields_edit, $_POST));
	$fields_with_lengths_edit = array('from_email_edit' => 250, 'organization_edit' => 150, 'logo_edit' => 100);
	$errors_edit = array_merge($errors_edit, check_max_field_lengths($fields_with_lengths_edit, $_POST));
    
    // Grab values for variables
	$from_email_edit = trim(mysql_prep($_POST['from_email_edit']));
	$organization_edit = trim(mysql_prep($_POST['organization_edit']));
        $logo_edit = trim(mysql_prep($_FILES['logo_edit']['name']));
    
    // Proceed if there are no errors, else print the errors
	if (empty($errors_edit)) {
            
	    //lookup original, see if new is different.
	    $check_current = mysql_query("SELECT from_email FROM campaigns WHERE id = '{$campaign_id}' ");
	    $array_current = mysql_fetch_array($check_current);
	    
	    //check to see if the new email is the main user's email (they are changing it back)
	    $check_user = mysql_query("SELECT email FROM users WHERE id = '{$userid}' ");
	    $array_user = mysql_fetch_array($check_user);
	    
	    if(($from_email_edit != $array_current['from_email']) && ($from_email_edit != $array_user['email'])){ //$from_email_edit requires validation
                //if $from_email_edit is different than what exists, send email to verify
                //write in new from_email on emailreceiver.php

                //generate verification key
                $key = md5(uniqid(rand(), TRUE));

                /**
                 * put key into db ALSO update organization
                 * currently doesn't have a way to find the key when it pulls the campaigns from emailprep.php
                */
                $update_key = mysql_query("UPDATE campaigns SET
                                          verified = 'yes',
                                          organization = '{$organization_edit}'
                                          WHERE id = '{$campaign_id}' ");

                //Send the email
                require_once("phpmailer/class.phpmailer.php");
                $mail = new PHPMailer();

                $body = "Please follow the link to <a href=\"http://myenpact.com/emailreceiver.php?fid=$from_email_edit&amp;kid=$key&amp;cid=$campaign_id\">VERIFY</a> and update your campaign.
                        <br>
                        <h4>Thank You!</h4>
                        -the MyENpact team
                ";

                $mail->Host       = "dedrelay.secureserver.net";  // this relay server was recommended by godaddy via an email on 9/20/12
                $mail->Port       = 25; // set the SMTP port for the server, godaddy says port 25

                $mail->SetFrom("info@myenpact.com");
                $mail->AddReplyTo("eric.nelson@meidh.com","Eric Nelson");
                $mail->Subject    = "Verify New From Email";
                $mail->AltBody    = "To view the message, please use an HTML compatible email viewer"; // optional, comment out and test
                $mail->MsgHTML($body);
                $mail->AddAddress($from_email_edit);
                if(!$mail->Send()) {  //Email Failed
                    echo "Mailer Error: " . $mail->ErrorInfo ."/n";
                } else {  //Email Success
                    //echo "Message sent! to: Someone at ".$email_to." \n";
                    redirect_to('who.php');
                }
	    } else { //no issues with $from_email_edit
	    
                $query_update_edit = "UPDATE campaigns SET
                                        from_email = '{$from_email_edit}',
                                        organization = '{$organization_edit}',
                                        logo = '{$logo_edit}'
                                WHERE id = {$campaign_id}";
                $result_edit = mysql_query($query_update_edit);
	    
	    // test to see if the update occurred
		if (mysql_affected_rows() == 1) {
                    // start array to hold edit window errors
                    $errors_edit = array();

                    // Grab values for variables
                    $logo_edit = trim($_FILES['logo_edit']['name']);

                    $upload_errors = array(
                        // http://php.net/manual/en/features.file-upload.errors.php
                        UPLOAD_ERR_OK           => "No errors.",
                        UPLOAD_ERR_INI_SIZE     => "Larger than upload_max_filesize.",
                        UPLOAD_ERR_FORM_SIZE    => "Larger than form MAX_FILE_SIZE.",
                        UPLOAD_ERR_PARTIAL      => "Partial upload.",
                        UPLOAD_ERR_NO_FILE      => "No file.",
                        UPLOAD_ERR_NO_TMP_DIR   => "No temporary directory.",
                        UPLOAD_ERR_CANT_WRITE   => "Can't write to disk.",
                        UPLOAD_ERR_EXTENSION    => "File upload stopped by extension.",
                    );

                    if(!$_FILES['logo_edit'] || empty($_FILES['logo_edit']) || !is_array($_FILES['logo_edit'])) {
                        // error: nothing uploaded or wrong argument usage
                        $message_edit = "No file was uploaded.";
                    } elseif ($_FILES['logo_edit']['error'] != 0) {
                        // error: report what PHP says went wrong
                        $message_edit = $upload_errors[$_FILES['logo_edit']['error']];
                    } else {
                        $temp_path    = $_FILES['logo_edit']['tmp_name'];
                        $filename     = basename($_FILES['logo_edit']['name']);
                        $type         = $_FILES['logo_edit']['type'];
                        $size         = $_FILES['logo_edit']['size'];
                    }

                    if(empty($filename) || empty($temp_path)) {
                        $message_edit = "The file location was not available.";
                    } else {
                        $target_path = "images/logos/" . $filename;
                    }

                    if (file_exists($target_path)) {
                        $message_edit = "The file {$filename} already exists.";
                    }

                    if(move_uploaded_file($temp_path, $target_path)) {
                        // Success
                        redirect_to("who.php");
                    } else {
                        // File was not moved.
                        $message_edit = "The file upload failed, possibly due to incorrect permissions on the upload folder.";
                    }
		} else {
		    if(mysql_error() == NULL){  // there are no affected rows and no errors (triggered when someone hits save without making changes)
			redirect_to("who.php");
		    } else {
		    $message_edit = "The information could not be updated.";
		    $message_edit .= "<br />" . mysql_error() . mysql_affected_rows();
		    //redirect_to("dashboard.php?settingsupdated=3");
		    }
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
    	    $query_campaign_info = mysql_query("SELECT from_email, subject, organization, logo
    	        				   FROM campaigns
    	        				   WHERE id = '{$campaign_id}' ");
    	    $campaign_info_array = mysql_fetch_array($query_campaign_info);
	
	// Assign values to page variables
	    $from_email_edit = $campaign_info_array['from_email'];
	    $subject_edit = $campaign_info_array['subject'];
	    $organization_edit = $campaign_info_array['organization'];
            $logo_edit = $campaign_info_array['logo'];
    	
    }
    
    // check to see if the upload button was clicked
    if(isset($_POST['upload_to'])){  // Upload emails was clicked, proceed with upload of new addresses

        // Crunch the input text
        $input = $_POST['email_csv'];
        $data = str_replace(array("\r", "\n"), ',', $input);
        $data = array_filter(explode(',', $data));

    // Upload the addresses to the database
        foreach ($data as $email_address) {
            // validate email addresses
            if(filter_var($email_address, FILTER_VALIDATE_EMAIL) != TRUE){ //format failed
                // upload with ERROR status
                $create_new_campaign = mysql_query("INSERT INTO recipients (campaign_id, email, subscribed)
                                                       VALUES ('{$campaign_id}', '{$email_address}', 'error' )");

            } else { //format passed
                // upload to database with yes status
                $create_new_campaign = mysql_query("INSERT INTO recipients (campaign_id, email, subscribed)
                                                       VALUES ('{$campaign_id}', '{$email_address}', 'yes' )");
            }
        } 
    }

    if(isset($_POST['remove'])){  //remove was clicked

        $remove_id = $_POST['remove'];

        $remove_recipient = mysql_query("DELETE FROM recipients WHERE id = '{$remove_id}' ");


    }
	
    // Proceed with loading the other variables on the page regardless whether the upload button was clicked or not
    // Grab existing info from database
    $query_campaign_info = mysql_query("SELECT name, from_email, subject, organization, logo, start_date, end_date, frequency
                                           FROM campaigns
                                           WHERE id = '{$campaign_id}' ");
    $campaign_info_array = mysql_fetch_array($query_campaign_info);

    // Assign values to page variables
    $from_email = $campaign_info_array['from_email'];
    $from_email_edit = $campaign_info_array['from_email'];
    $subject = $campaign_info_array['subject'];
    $subject_edit = $campaign_info_array['subject'];
    $organization = $campaign_info_array['organization'];
    $organization_edit = $campaign_info_array['organization'];
    $logo = $campaign_info_array['logo'];
    $logo_edit = $campaign_info_array['logo'];


// 1. the current page number ($current_page)
    $page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
// 2. records per page ($per_page)
    $per_page = 30;
// 3. total record count ($total_count)
    $count = mysql_query("SELECT COUNT(*) FROM recipients WHERE campaign_id = $campaign_id");
    $total_count = array_shift(mysql_fetch_array($count));
    
// Find all photos
// use pagination instead
//$photos = Photograph::find_all();
    
    $pagination = new Pagination ($page, $per_page, $total_count);
    
// Need to add ?page=$page to all links we want to
// maintain the current page (or store $page in $session)

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
    $( "#navigation" ).buttonset();
    $( "button", ".help" ).button({
        icons: {
            primary: "ui-icon-help"
        }
    });
    $( "button", ".resubscribe" ).button({
        icons: {
            primary: "ui-icon-mail-open"
        }
    });
    $( "button", ".remove" ).button({
        icons: {
            primary: "ui-icon-trash"
        }
    });
         $( "button", ".newrecipient" ).button({
        icons: {
            primary: "ui-icon-plusthick"
        }
    });
    $( "button", ".logout" ).button({
        icons: {
            primary: "ui-icon-circle-close"
        }
    });
    $( "button", ".next" ).button({
        icons: {
            primary: "ui-icon-arrowthick-1-e"
        }
    });
    $( "button", ".prev" ).button({
        icons: {
            primary: "ui-icon-arrowthick-1-w"
        }
    });
    $( "button", ".last" ).button({
        icons: {
            primary: "ui-icon-arrowthickstop-1-e"
        }
    });
    $( "button", ".first" ).button({
        icons: {
            primary: "ui-icon-arrowthickstop-1-w"
        }
    });
});
</script>

<!-- BEGIN EDITOR POP UP BOX -->
<div id="dialog-edit" title="Edit Details">
<form id="editform" action="who.php" method="post" enctype="multipart/form-data">
<center>
<table border='0' style="width:750px;">
    <tr>
	<td colspan="3" align="center">
	    <?php if (!empty($message_edit)) {echo "<p style=\"color:#C20000; font-size: 12px;\">" . $message_edit . "</p>";} ?>
	    <?php if (!empty($errors_edit)) { display_errors($errors_edit); } ?>
	</td>
    </tr>
    <tr>
	<td>FROM:
	</td>
	<td style="height:50px;">
	    Address:
	    <input type="text" name="from_email_edit" maxlength="100" style="width:250px;"
		value="<?php echo htmlentities($from_email_edit); ?>" />
	    <br>
	    Organization:
	    <input type="text" name="organization_edit" maxlength="100" placeholder="not listed" style="width:250px;"
		value="<?php echo htmlentities($organization_edit); ?>" />
	    <br>
            Logo:
            <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_file_size; ?>" />
            <input type="file" name ="logo_edit" maxlength="100" style="width:250px;" value="" />
	</td>
	<td style="width:250px;font-size:12px;color:gray;">
	    Note: Changing address requires verification.<br>
	    1. type in the new address and hit Save.<br>
	    2. check the new email for a verification link.<br>
	    3. click the link to update your campaign.
	</td>
    </tr>
    <tr>
        <td colspan='3' style="padding-top:20px;text-align:center;">
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
	<table style="width:100%;">
            <tr>
                <form action="who.php" method="post">
                <td colspan='1' style="height:60px;">
                    <img src="images/title.png" alt="title" />
                </td>
                <td style="width:70px;height:60px;text-align:right;vertical-align:top;">
                    <div class="help"><button type="submit" name="help" value="help" style="cursor:pointer;height:25px;font-size:12px;">Help</button></div>
                </td>
                <td style="width:85px;height:60px;text-align:right;vertical-align:top;">
                    <div class="logout"><button type="submit" name="logout" value="logout" style="cursor:pointer;height:25px;font-size:12px;">Logout</button></div>
                </td>
                </form>
            </tr>
	</table>
        </td>
    </tr>
    <tr>
        <td colspan='3' style="height:1px;border-bottom:gray thin solid;"></td>
    </tr>
    <tr>
	<td colspan='2' style="height:150px;text-align:left;">
	    <form action="navigation.php" method="post">
	    <div id="navigation">
		<input type="radio" id="mycampaigns" name="nav" value="mycampaigns" onClick="this.form.action='navigation.php';this.form.submit()" /><label for="mycampaigns">My Campaigns</label>
		<input type="radio" id="who" name="nav" value="who" checked="checked" /><label for="who">Who</label>
		<input type="radio" id="what" name="nav" value="what" onClick="this.form.action='navigation.php';this.form.submit()" /><label for="what">What</label>
		<input type="radio" id="when" name="nav" value="when" onClick="this.form.action='navigation.php';this.form.submit()" /><label for="when">When</label>
		<input type="radio" id="results" name="nav" value="results" onClick="this.form.action='navigation.php';this.form.submit()" /><label for="results">Results</label>
	    </div>
	    </form>
	</td>
	<td style="text-align:right;">
	    <img src="images/who.png" alt="who" style="height:130px;" />
	</td>
    </tr>
    <tr>
        <td colspan='3' style="height:1px;border-top:solid gray thin;padding-bottom:20px;"></td>
    </tr>
    <tr>
	<td>
	    FROM:
	</td>
	<td style="height:50px; width:550px; padding-left:20px;">
	    <table border='0' style="width:100%;">
                <tr>
                    <td>
                        <?php echo htmlentities($from_email); ?><br>
                        <?php if($organization == ""){$organization = "Organization not listed";} echo htmlentities($organization); ?></td>
                    <td>
                        <?php if($logo == "") {$logo = "No image chosen"; } echo "<img src=\"images/logos/" . $logo . "\" name=\"logo\" height=\"100\">"; ?>
                    </td>
                </tr>
            </table>
	</td>
	<td style="">
	    <input type="submit" alt="editpop" name="editpop" value="Edit From" id="editpop" >
	</td>
    </tr>
    <tr>
        <td colspan='3' style="height:1px;border-bottom:solid gray thin;padding-top:20px;"></td>
    </tr>
    <tr>
	<td style="vertical-align:middle;">
	    TO:
	</td>
	<td colspan='2' style="height:100px;text-align:center;">
	    <table border='0' style="width:100%;">
		<tr>
		    <form action="who.php" method="post">
		    <td>
			<center>
			<br>
			
			<!-- Instructions to user: enter the data into Excel, file->save as csv -->
			<font style="font-size:12px;color:gray;">
			Enter email addresses separated by commas
			<br>
			(joe@example.com, jane@example.com, etc.)
			</font>
			<br>
			<textarea rows=4 cols=50 name="email_csv" style="outline: #f4911e thin solid;"></textarea>
			<br><br>
			</center>
		    </td>
		    <td>
			<div class="newrecipient"><button type="submit" alt="upload_to" name="upload_to" id="upload_to" value="Add New Recipient" style="cursor:pointer;height:40px;font-weight:bold;font-size:15px;">Add New Recipient</button></div>
		    </td>
		    </form>
		</tr>
	    </table>
	</td>
    </tr>
    <tr>
        <td colspan='3' style="height:1px;border-top:solid gray thin;"></td>
    </tr>
    <tr>
        <td colspan='3' style="padding:10px; text-align:center;">
    	<table align="center"><form action="who.php" method="get">
            <tr>
            <?php if ($pagination->total_pages() > 1) { ?>
                <td><div class="first"><button type="submit" alt="first" name="page" id="first" value="1" style="cursor:pointer;height:25px; width: 100px; font-weight:bold; font-size:12px;">First</button></div></td>
                <?php if ($pagination->has_previous_page()) { ?>
                <td><div id="prev" class="prev"><button type="submit" alt="prev" name="page" id="prev" value="<?php echo $pagination->previous_page(); ?>" style="cursor:pointer;height:25px; width: 100px; font-weight:bold; font-size:12px;">Previous</button></div></td>
            <?php
                }
            }
            if ($pagination->total_pages() > 1) {
                if ($pagination->has_next_page()) { ?>
                <td><div id="next" class="next"><button type="submit" alt="next" name="page" id="next" value="<?php echo $pagination->next_page(); ?>" style="cursor:pointer;height:25px; width: 100px; font-weight:bold; font-size:12px;">Next</button></div></td>
            <?php
                }
            }
            if ($pagination->total_pages() > 1) { ?>
                <td><div id="last" class="last"><button type="submit" alt="last" name="page" id="last" value="<?php echo $pagination->total_pages(); ?>" style="cursor:pointer;height:25px; width: 100px; font-weight:bold; font-size:12px;">Last</button></div></td>
            </tr>
            <?php } ?>
         </form></table>
         </td>
    </tr>
    <tr>
	<td colspan='3' style="padding:10px;text-align:center;">
	    <form action="who.php" method="post">
		<?php  
                    // Echo the list of contacts in the system
		    // Get list of recipients for this campaign
                        $sql = "SELECT id, email, subscribed FROM recipients ";
                        $sql .= "WHERE campaign_id = {$campaign_id} ";
                        $sql .= "LIMIT {$per_page} ";
                        $sql .= "OFFSET {$pagination->offset()}";
			$get_recipients = mysql_query($sql);
			
			if(mysql_num_rows($get_recipients) != 0){ // recipients were found, list them
			
			    $number = 3;
			    
			    while($recipients_array = mysql_fetch_array($get_recipients)){
				
				if($number % 2 == 0){$bgcolor = "background-color:#d6d1d1;";}else{$bgcolor = "background-color:none;";}
				
				if($recipients_array['subscribed'] == 'no'){
				    $txcolor = "color:gray;";
				    $subscribed = "unsubscribed";
				    $button_active = "<td style=\"text-align:right;\"><div class=\"resubscribe\"><button type=\"submit\" name=\"resubscribe\" value=\"" . $recipients_array['id'] . "\" style=\"cursor:pointer;height:25px;font-weight:bold;font-size:12px;\">Resubscribe</button></div></td>";
				}elseif($recipients_array['subscribed'] == 'error'){
				    $txcolor = "color:gray;";
				    $subscribed = "Format Error!";
				    $button_active = "<td style=\"text-align:right;width:115px;\"></td>";
				}else{
				    $txcolor = "";
				    $subscribed = "subscribed";
				    $button_active = "<td style=\"text-align:right;\"><div class=\"resubscribe\"><button type=\"submit\" name=\"resubscribe\" value=\"" . $recipients_array['id'] . "\" style=\"cursor:pointer;height:25px;font-weight:bold;font-size:12px;\" disabled=\"disabled\">Resubscribe</button></div></td>";
				}
				
				echo "<table style=\"width:100%;text-align:center;" . $bgcolor . "" . $txcolor . "\"><tr>
				    <td style=\"width:350px;text-align:left;\">" . $recipients_array['email'] . "</td>
				    <td style=\"width:150px;text-align:left;\">" . $subscribed . "</td>
				    <td><div class=\"remove\"><button type=\"submit\" name=\"remove\" value=\"" . $recipients_array['id'] . "\" style=\"cursor:pointer;height:25px;font-weight:bold;font-size:12px;\">Remove</button></div></td>
				    " . $button_active . "
				    </tr></table>";
				$number++;
			    }
			    
			}else{  // nothing found
				echo "<tr><td>no recipients found</td></tr>";
			}
		?>
	    </form>
	</td>
    </tr>
</table>
</center>
<?php include("includes/footer.php");?>