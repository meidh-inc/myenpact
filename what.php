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


// Reset the question_id variable everytime the page reloads
    $question_id = 0;

// Set Timezone
    date_default_timezone_set('America/Chicago');

function isValidURL($url){
    return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}

// START FORM PROCESSING
if (isset($_POST['subedit'])) { // The campaign subject was edited
    
    // start array to hold edit window errors
	$errors_subedit = array();
    
    // perform validations on the edited data
	$required_fields_subedit = array('campaign_name_edit', 'subject_edit', 'intro_edit');
	$errors_subedit = array_merge($errors_subedit, check_required_fields($required_fields_subedit, $_POST));
	$fields_with_lengths_subedit = array('campaign_name_edit' => 100, 'subject_edit' => 250, 'intro_edit' => 252);
	$errors_subedit = array_merge($errors_subedit, check_max_field_lengths($fields_with_lengths_subedit, $_POST));
    
    // Grab values for variables
	$campaign_name_edit = trim(mysql_prep($_POST['campaign_name_edit']));
	$subject_edit = trim(mysql_prep($_POST['subject_edit']));
	$intro_edit = trim(mysql_prep($_POST['intro_edit']));
    
    // Proceed if there are no errors, else print the errors
	if (empty($errors_subedit)) {
	    $query_update_subedit = "UPDATE campaigns SET
				    name = '{$campaign_name_edit}',
				    subject = '{$subject_edit}',
				    intro = '{$intro_edit}'
			    WHERE id = {$campaign_id}";
	    $result_subedit = mysql_query($query_update_subedit);
	    
	    // test to see if the update occurred
		if (mysql_affected_rows() == 1) {
		    // Success!
		    //$message = "<center><p style=\"color:blue;\">The page was successfully updated.</p></center>";
		    redirect_to("what.php");
		} else {
		    if(mysql_error() == NULL){  // there are no affected rows and no errors (triggered when someone hits save without making changes)
			redirect_to("what.php");
		    }else{
		    $message_subedit = "The information could not be updated.";
		    $message_subedit .= "<br />" . mysql_error() . mysql_affected_rows();
		    //redirect_to("dashboard.php?settingsupdated=3");
		    }
		}
	} else {
	    if (count($errors_subedit) == 1) {
		$message_subedit = "There was 1 error in the form.";
	    } else {
		$message_subedit = "There were " . count($errors_subedit) . " errors in the form.";
	    }
	}
}


if (isset($_POST['save_create'])) { // Save button on Q/A CREATOR window was clicked
    //create array to place any errors
	$errors_create = array();  
    
    // perform validations on the form data, require a question and at least two answers
        $required_fields = array('question_text', 'answer1', 'answer2');
	$errors_create = array_merge($errors_create, check_required_fields($required_fields, $_POST));
	
	
    // no errors detected so far, continue
        if ( empty($errors_create) ) {
	    
	    // submit question
		// gather question text variable
		$question_text = trim(mysql_prep($_POST['question_text']));
		$question_subject = trim(mysql_prep($_POST['question_subject']));
		$question_link = trim(mysql_prep($_POST['question_link']));
		
		// write question text to database
		$create_new_question = mysql_query("INSERT INTO questions ( campaign_id, question, subject, status, url
						   )VALUES( '{$campaign_id}', '{$question_text}', '{$question_subject}', 'active', '{$question_link}' )");
		
		// get that question's id
		$get_question_id = mysql_query("SELECT id FROM questions WHERE question = '{$question_text}'");
		$question_id_array = mysql_fetch_array($get_question_id);
		
	    // submit answers
		// get the number of answers selected
		//$answer_count = trim(mysql_prep($_POST['answer_count']));
		$answer_count = 20;
		// set counter for loop
		$counter = 1;
		
		while($counter <= $answer_count){
		    $answer_text = trim(mysql_prep($_POST['answer'.$counter]));
		    $answer_link = trim(mysql_prep($_POST['answerlink'.$counter]));
		    
		    $create_new_answer = mysql_query("INSERT INTO answers ( campaign_id, question_id, answer, url
						     )VALUES( '{$campaign_id}', '{$question_id_array['id']}', '{$answer_text}', '{$answer_link}' )");
		    
		    $next_count = $counter + 1;
		    
		    if(!isset($_POST['answer'.$next_count])){
			break;
		    }
		    
		    
		    // up the counter and continue
		    $counter++;
		}
		
    // errors detected, relay them to the user
	} else {
	    if (count($errors_create) == 1) {
		$message_create = "There was 1 error in the form.";
	    } else {
		$message_create = "There were " . count($errors_create) . " errors in the form.";
	    }
	}
}


if(isset($_POST['edit'])){
    // grab the posted question id from the button clicked
    $question_id = trim(mysql_prep($_POST['edit']));
    $_SESSION['old_question_id'] = $question_id;
    // get and assign the question text
    $get_question = mysql_query("SELECT question, subject, url FROM questions WHERE id = '{$question_id}'");
    $question_array = mysql_fetch_array($get_question);
    $question_text_edit = $question_array['question'];
    $question_subject_edit = $question_array['subject'];
    $question_url_edit = $question_array['url'];
    
    // get the number of answers already in the system to prep the editor window
    $get_answer_count = mysql_query("SELECT id FROM answers WHERE question_id = '{$question_id}' ");
    $count_answers = mysql_num_rows($get_answer_count) + 1;
	if(empty($errors_edit)){
	    $ecounter = $count_answers;
	}else{
	    $ecounter = $count_answers;
	}
    
}else{
    $ecounter = 2;
}


if (isset($_POST['save_edit'])) { // Save button on Q/A EDITOR window was clicked
    //create array to place any errors
	$errors_edit = array();  
    
    // perform validations on the form data, require a question and at least two answers
        $required_fields = array('question_text_edit', 'eanswer1', 'eanswer2');
	$errors_edit = array_merge($errors_edit, check_required_fields($required_fields, $_POST));
	
    // gather question text variable
	$question_text_edit = trim(mysql_prep($_POST['question_text_edit']));
	$question_subject_edit = trim(mysql_prep($_POST['question_subject_edit']));
	$question_url_edit = trim(mysql_prep($_POST['question_url_edit']));
    
    
    // no errors detected so far, continue
        if ( empty($errors_edit) ) {
	    
	    // submit question
		// gather question text variable
		$question_text_edit = trim(mysql_prep($_POST['question_text_edit']));
		
		// write question text to database
		$create_new_question_edit = mysql_query("INSERT INTO questions ( campaign_id, question, subject, status, url
						   )VALUES( '{$campaign_id}', '{$question_text_edit}', '{$question_subject_edit}', 'active', '{$question_url_edit}' )");
		
		// get that question's id
		$get_question_id_edit = mysql_query("SELECT id FROM questions WHERE question = '{$question_text_edit}'
						    ORDER BY id DESC LIMIT 1");
		$question_id_array_edit = mysql_fetch_array($get_question_id_edit);
		
	    // submit answers
		// get the number of answers selected
		$answer_count_edit = 15;
		// set counter for loop
		$counter_edit = 1;
		
		while($counter_edit <= $answer_count_edit){
		    $answer_text_edit = trim(mysql_prep($_POST['eanswer'.$counter_edit]));
		    $answer_link_edit = trim(mysql_prep($_POST['eanswerlink'.$counter_edit]));
		    
		    $create_new_answer_edit = mysql_query("INSERT INTO answers ( campaign_id, question_id, answer, url
						     )VALUES( '{$campaign_id}', '{$question_id_array_edit['id']}', '{$answer_text_edit}', '{$answer_link_edit}' )");
		    
		    $next_count_edit = $counter_edit + 1;
		    
		    if(!isset($_POST['eanswer'.$next_count_edit])){
			break;
		    }
		    
		    
		    // up the counter and continue
		    $counter_edit++;
		}
	    
	    // Deactivate Old Question
		$query_update_oldq = "UPDATE questions SET
				    status = 'inactive'
				    WHERE id = '{$_SESSION['old_question_id']}' ";
		$result_oldq = mysql_query($query_update_oldq);
		
		redirect_to('what.php');
		
    // errors detected, relay them to the user
	} else {
	    if (count($errors_edit) == 1) {
		$message_edit = "There was 1 error in the form.";
	    } else {
		$message_edit = "There were " . count($errors_edit) . " errors in the form.";
	    }
	    
	}
}

if (isset($_POST['activate_question'])) {  // Button clicked to reactivate question
    $activate_qid = $_POST['activate_question'];
    $query_update_qstatus = "UPDATE questions SET
				    status = 'active'
			    WHERE id = {$activate_qid}";
    $result_subedit = mysql_query($query_update_qstatus);
}

if (isset($_POST['deactivate_question'])) {  // Button clicked to reactivate question
    $deactivate_qid = $_POST['deactivate_question'];
    $query_update_qstatus = "UPDATE questions SET
				    status = 'inactive'
			    WHERE id = {$deactivate_qid}";
    $result_subedit = mysql_query($query_update_qstatus);
}

if(!isset($_POST['show_questions'])){
    $query_questions = mysql_query("SELECT id, question, subject, status, url FROM questions WHERE campaign_id = '{$campaign_id}' ");
    $showing1 = "selected=\"selected\"";
    $showing2 = "";
    $showing3 = "";
}elseif($_POST['show_questions'] == 'all'){
    $query_questions = mysql_query("SELECT id, question, subject, status, url FROM questions WHERE campaign_id = '{$campaign_id}' ");
    $showing1 = "selected=\"selected\"";
    $showing2 = "";
    $showing3 = "";
}elseif($_POST['show_questions'] == 'active'){
    $query_questions = mysql_query("SELECT id, question, subject, status, url FROM questions WHERE campaign_id = '{$campaign_id}' AND status = 'active' ");
    $showing1 = "";
    $showing2 = "selected=\"selected\"";
    $showing3 = "";
}elseif($_POST['show_questions'] == 'inactive'){
    $query_questions = mysql_query("SELECT id, question, subject, status, url FROM questions WHERE campaign_id = '{$campaign_id}' AND status = 'inactive' ");
    $showing1 = "";
    $showing2 = "";
    $showing3 = "selected=\"selected\"";
}





// Proceed with loading the other variables on the page regardless whether the upload button was clicked or not
// Grab existing info from database
    $query_campaign_info = mysql_query("SELECT name, subject, intro
    	        			    FROM campaigns
    	        			    WHERE id = '{$campaign_id}' ");
    $campaign_info_array = mysql_fetch_array($query_campaign_info);


$question_text_create = "";
$campaign_name = $campaign_info_array['name'];
$campaign_name_edit = $campaign_info_array['name'];
$subject = $campaign_info_array['subject'];
$subject_edit = $campaign_info_array['subject'];
$intro = $campaign_info_array['intro'];
$intro_edit = $campaign_info_array['intro'];

//$intro = "Please take a moment to answer the following brief question. Your answer will provide us with much needed information about our service and/or product. Thank you for your time. Please feel free to contact me directly with any questions or comments.";


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
    $( "#dialog-subedit" ).dialog({
	<?php 
	    if (isset($_POST['subedit'])) {
		if(empty($errors_subedit)){
		    echo "autoOpen: false,";
		}else{
		    echo "autoOpen: true,";
		}
	    }else{
		echo "autoOpen: false,";
	    }
	?>
	position: ["center",50],
	width: 800,
	resizable: false,
	modal: true,
    });
    $( "#subeditpop" )
	.button()
	.click(function() {
	    $( "#dialog-subedit" ).dialog( "open" );
	});
    $( "#previewpop" )
	.button()
	.click(function() {
	    $( "#dialog-preview" ).dialog( "open" );
	});
	  $( "#dialog-preview" ).dialog({
	autoOpen: false,
	position: ["center",50],
	width: 800,
	resizable: false,
	modal: true,
    });
    $( "#previewpop" )
	.button()
	.click(function() {
	    $( "#dialog-preview" ).dialog( "open" );
	});
    $( "#dialog-createqa" ).dialog({
	<?php 
	    if (isset($_POST['save_create'])) {
		if(empty($errors_create)){
		    echo "autoOpen: false,";
		}else{
		    echo "autoOpen: true,";
		}
	    }else{
		echo "autoOpen: false,";
	    }
	?>
	position: ["center",50],
	width: 800,
	resizable: false,
	modal: true,
    });
    $( "#createqapop" )
	.button()
	.click(function() {
	    $( "#dialog-createqa" ).dialog( "open" );
	});
	
    $( "#dialog-editqa" ).dialog({
	<?php 
	    if (isset($_POST['edit'])) {
		echo "autoOpen: true,";
	    }elseif(isset($_POST['save_edit'])){
		if(empty($errors_edit)){
		    echo "autoOpen: false,";
		}else{
		    echo "autoOpen: true,";
		}
	    }else{
		echo "autoOpen: false,";
	    }
	?>
	position: ["center",50],
	width: 800,
	resizable: false,
	modal: true,
    });
    $( "#editqapop" )
	.button()
	.click(function() {
	    $( "#dialog-editqa" ).dialog( "open" );
	});
});

// Add/Remove Textboxes - Creator
$(document).ready(function(){
    var counter = 2;
    $("#addButton").click(function () {
	if(counter>20){
            alert("Only 20 textboxes allow");
            return false;
	}   
	var newTextBoxDiv = $(document.createElement('div'))
	     .attr("id", 'TextBoxDiv' + counter);
	newTextBoxDiv.html('<label>Answer '+ counter + ' : </label>' +
	      '<input type="text" name="answer' + counter + 
	      '" id="textbox' + counter + '" value="" >' +
	      '<label> More info link : </label>' +
	      '<input type="text" name="answerlink' + counter + 
	      '" id="textboxlink' + counter + '" value="" >');
	newTextBoxDiv.appendTo("#TextBoxesGroup");
	
	counter++;
     });
     $("#removeButton").click(function () {
	if(counter==1){
          alert("No more textbox to remove");
          return false;
       }   
	counter--;
        $("#TextBoxDiv" + counter).remove();
     });
     $("#getButtonValue").click(function () {
	var msg = '';
	for(i=1; i<counter; i++){
   	  msg += "\n Textbox " + i + " : " + $('#textbox' + i).val();
	}
    	  alert(msg);
     });
});

$(document).ready(function(){
    var ecounter = <?php echo $ecounter; ?>;
    //var ecounter = 3;
    $("#eaddButton").click(function () {
	if(ecounter>20){
            alert("Only 20 textboxes allowed");
            return false;
	}   
	var enewTextBoxDiv = $(document.createElement('div'))
	     .attr("id", 'eTextBoxDiv' + ecounter);
	enewTextBoxDiv.html('<label>Answer '+ ecounter + ' : </label>' +
	      '<input type="text" name="eanswer' + ecounter + 
	      '" id="etextbox' + ecounter + '" value="" >' +
	      '<label> More info link : </label>' +
	      '<input type="text" name="eanswerlink' + ecounter + 
	      '" id="etextboxlink' + ecounter + '" value="" >');
	enewTextBoxDiv.appendTo("#eTextBoxesGroup");
	
	ecounter++;
     });
     $("#eremoveButton").click(function () {
	if(ecounter==1){
          alert("No more textbox to remove");
          return false;
       }   
	ecounter--;
        $("#eTextBoxDiv" + ecounter).remove();
     });
     $("#egetButtonValue").click(function () {
	var emsg = '';
	for(ei=1; ei<ecounter; ei++){
   	  emsg += "\n Textbox " + ei + " : " + $('#etextbox' + ei).val();
	}
    	  alert(emsg);
     });
     $( "#navigation" ).buttonset();
     $( "button", ".newquestion" ).button({
        icons: {
            primary: "ui-icon-plusthick"
        }
    });
    $( "button", ".help" ).button({
        icons: {
            primary: "ui-icon-help"
        }
    });
    $( "button", ".editsubject" ).button({
        icons: {
            primary: "ui-icon-gear"
        }
    });
    $( "button", ".logout" ).button({
        icons: {
            primary: "ui-icon-circle-close"
        }
    });
    $( "button", ".preview" ).button({
        icons: {
            primary: "ui-icon-search"
        }
    });
});

$(document).ready(function(){
var characters_Limit = 250;
$('#myInput').keyup(function() {
    if ($(this).val().length > characters_Limit) {
         $(this).val($(this).val().substr(0, characters_Limit));
    }
     $('#charCount').text(this.value.replace(/{.*}/g, '').length);
});
});
</script>


<!-- BEGIN SUBJECT EDITOR POP UP BOX -->
<div id="dialog-subedit" title="Edit Campaign Subject">
<form id="subeditform" action="what.php" method="post">
<center>
<table border='0' style="width:750px;">
    <tr>
	<td colspan="5" align="center">
	    <?php if (!empty($message_subedit)) {echo "<p style=\"color:#C20000; font-size: 12px;\">" . $message_subedit . "</p>";} ?>
	    <?php if (!empty($errors_subedit)) { display_errors($errors_subedit); } ?>
	</td>
    </tr>
    <tr>
	<td style="">
	    Campaign Name:
	    <input type="text" name="campaign_name_edit" maxlength="100" style="width:450px;margin-bottom:20px;"
		value="<?php echo htmlentities($campaign_name_edit); ?>" />
	    <br>
	    Subject:
	    <input type="text" name="subject_edit" maxlength="100" placeholder="add a subject line" style="width:450px;margin-bottom:20px;"
		value="<?php echo htmlentities($subject_edit); ?>" />
	    <br>
	    Intro Message: <br>
	    <textarea id="myInput" rows=3 cols=70 name="intro_edit" style="margin-left:120px;font-size:14px;"><?php echo htmlentities($intro_edit); ?></textarea>
	    <span id="charCount" style="font-size:12px;color:gray;"></span><span style="font-size:12px;color:gray;">/250 limit</span>
	</td>
    </tr>
    <tr>
        <td colspan='2' style="padding-top:20px;text-align:center;">
	    <input type="submit" alt="subedit" name="subedit" value="Save" class="ui-button ui-widget ui-state-default ui-corner-all" >
	    <br>
	    <font style="color:gray;font-size:10px;">(click x in upper-right corner to cancel)</font>
	</td>
    </tr>
</table>
</form>
</center>
</div>
<!-- END SUBJECT EDITOR POP UP BOX -->
<!-- BEGIN QnA CREATOR POP UP BOX -->
<div id="dialog-createqa" title="Question and Answer Creator">
<form action="what.php" method="post">
<center>
<table style="width:750px;">
    <tr>
	<td colspan="5" align="center">
	    <?php if (!empty($message_create)) {echo "<p style=\"color:#C20000; font-size: 12px;\">" . $message_create . "</p>";} ?>
	    <?php if (!empty($errors_create)) { display_errors($errors_create); } ?>
	</td>
    </tr>
    <tr>
	<td colspan='5'>
	    Question Subject: <input type="text" name="question_subject" value="" />
	    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	    <font style="font-size:12px;color:gray;">All links must start with "http://"</font>
	</td>
    </tr>
    <tr>
	<td colspan='5'>
	    Question: 
	<input type="text" name="question_text" style="width:250px;"
		value="<?php echo htmlentities($question_text_create); ?>" />
	More info link: <input type="text" name="question_link" value="" />
	</td>
    </tr>
    <tr>
	<td colspan='5'>
	    <div id='TextBoxesGroup'>
		<div id="TextBoxDiv1">
			<label>Answer 1 : </label><input type='textbox' id='textbox1' name='answer1' >
			<label> More info link : </label><input type='textboxlink' id='textboxlink1' name='answerlink1' >
		</div>
	    </div>
	    <input type='button' value='Add Answer' id='addButton' class="ui-button ui-widget ui-state-default ui-corner-all" style="font-size:10px;">
	    <input type='button' value='Delete Bottom Answer' id='removeButton' class="ui-button ui-widget ui-state-default ui-corner-all" style="font-size:10px;">
	</td>
    </tr>
    <tr>
        <td colspan='5' style="padding-top:20px;text-align:center;">
	    <input type="submit" alt="save" name="save_create" value="Save" class="ui-button ui-widget ui-state-default ui-corner-all">
        </td>
    </tr>
</table>
</form>
</center>
</div>
<!-- END QnA CREATOR POP UP BOX -->
<!-- BEGIN QnA EDITOR POP UP BOX -->
<div id="dialog-editqa" title="Question and Answer Editor">
<form action="what.php" method="post">
<center>
<table border='0' style="width:750px;">
    <tr>
	<td colspan="5" align="center">
	    <?php if (!empty($message_edit)) {echo "<p style=\"color:#C20000; font-size: 12px;\">" . $message_edit . "</p>";} ?>
	    <?php if (!empty($errors_edit)) { display_errors($errors_edit); } ?>
	</td>
    </tr>
    <tr>
	<td colspan='5'>
	    Question Subject: <input type="text" name="question_subject_edit" value="<?php echo htmlentities($question_subject_edit); ?>" />
	    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	    <font style="font-size:12px;color:gray;">All links must start with "http://"</font>
	</td>
    </tr>
    <tr>
	<td colspan='5'>Question: <input type="text" name="question_text_edit" style="width:250px;"
		value="<?php echo htmlentities($question_text_edit); ?>" />
		More info link: <input type="text" name="question_url_edit" value="<?php echo htmlentities($question_url_edit); ?>" />
	</td>
    </tr>
    <tr>
	<td colspan='5'>
	    <?php  // List answers and boxes
		
		// get the answers for the selected question
		    $get_answers = mysql_query("SELECT id, answer, url FROM answers
						WHERE campaign_id = '{$campaign_id}'
						AND question_id = '{$question_id}' ");
		
		// generate the text boxes
		    echo "<div id=\"eTextBoxesGroup\">";
		    $n = 1;
		    while($answers_array = mysql_fetch_array($get_answers)){
			
			echo "<div id=\"eTextBoxDiv".$n."\">";
			    echo "<label>Answer ".$n." : </label><input type=\"textbox\" id=\"etextbox".$n."\" name=\"eanswer".$n."\" value=\"".$answers_array['answer']."\">";
			    echo "<label> More info link : </label><input type=\"textbox\" id=\"etextboxlink".$n."\" name=\"eanswerlink".$n."\" value=\"".$answers_array['url']."\">";
			echo "</div>";
			
			$n++;
		    }
		    echo "</div>";
		
		// echo the add/remove buttons
		    echo "<input type=\"button\" value=\"Add Answer\" id=\"eaddButton\" class=\"ui-button ui-widget ui-state-default ui-corner-all\" style=\"font-size:10px;\">";
		    echo "<input type=\"button\" value=\"Delete Bottom Answer\" id=\"eremoveButton\" class=\"ui-button ui-widget ui-state-default ui-corner-all\" style=\"font-size:10px;\">";
			
	    ?>
	</td>
    </tr>
    <tr>
        <td colspan='5' style="padding-top:20px;text-align:center;">
	    <input type="submit" alt="save" name="save_edit" value="Save" id="login_button" >
        </td>
    </tr>
</table>
</center>
</form>
</div>
<!-- END QnA EDITOR POP UP BOX -->
<!-- BEGIN Email Preview POP UP BOX -->
<div id="dialog-preview" title="Email Preview">
<form id="preview" action="what.php" method="post">
<center>
    <?php // get email preview data
	    $query_preview = mysql_query("SELECT from_email, subject FROM campaigns WHERE id = '{$campaign_id}' ");
	    $preview_array = mysql_fetch_array($query_preview);
	    
	    $query_preview2 = mysql_query("SELECT email FROM recipients WHERE campaign_id = '{$campaign_id}' ORDER BY id DESC LIMIT 1 ");
	    $preview2_array = mysql_fetch_array($query_preview2);
	    
	    $query_preview3 = mysql_query("SELECT id, question, subject, url FROM questions WHERE campaign_id = '{$campaign_id}' LIMIT 1 ");
	    $preview3_array = mysql_fetch_array($query_preview3);
	    
	    $query_preview4 = mysql_query("SELECT answer, url FROM answers WHERE campaign_id = '{$campaign_id}' AND question_id = '{$preview3_array['id']}' ");
	    
	    
	    
    ?>
<table border='0' style="width:750px;">
    <tr>
	<td style="padding:10px;">
	    To: <?php echo $preview2_array['email']; ?>
	</td>
    </tr>
    <tr>
	<td style="padding:10px;">
	    From: <?php echo $preview_array['from_email']; ?>
	</td>
    </tr>
    <tr>
	<td style="padding:10px;">
	    Subject: <?php echo $preview_array['subject'].": ".$preview3_array['subject']; ?>
	</td>
    </tr>
    <tr>
	<td style="padding:10px;">
	    <table border='1' style="width:100%;">
		<?php
		    echo $intro."<br><br>";
		    echo "Body: ".$preview3_array['question']."<br>";
		    
		    while($preview4_array = mysql_fetch_array($query_preview4)){
			
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &#149; ".$preview4_array['answer']."<br>";
			
		    }
		?>
	    </table>
	</td>
    </tr>
    <tr>
        <td colspan='2' style="padding-top:20px;text-align:center;">
	    <br>
	    <font style="color:gray;font-size:10px;">(click x in upper-right corner to close preview)</font>
        </td>
    </tr>
</table>
</form>
</center>
</div>
<!-- END Email Preview POP UP BOX -->

<center>
<table border='0' style="width:850px;background-image:url('images/texture2.jpg');padding:30px;margin-top:5px;">
    <tr>
	<td colspan='3'>
	<table style="width:100%;"><tr>
	<form action="what.php" method="post">
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
	<td colspan='3' style="height:150px;text-align:left;">
	    <table style="width:100%;"><tr><td>
	    <form action="navigation.php" method="post">
	    <div id="navigation">
		<input type="radio" id="mycampaigns" name="nav" value="mycampaigns" onClick="this.form.action='navigation.php';this.form.submit()" /><label for="mycampaigns">My Campaigns</label>
		<input type="radio" id="who" name="nav" value="who" onClick="this.form.action='navigation.php';this.form.submit()" /><label for="who">Who</label>
		<input type="radio" id="what" name="nav" value="what" checked="checked"  /><label for="what">What</label>
		<input type="radio" id="when" name="nav" value="when" onClick="this.form.action='navigation.php';this.form.submit()" /><label for="when">When</label>
		<input type="radio" id="results" name="nav" value="results" onClick="this.form.action='navigation.php';this.form.submit()" /><label for="results">Results</label>
	    </div>
	    </form>
	</td>
	<td style="text-align:right;">
	    <img src="images/what.png" alt="what" />
	</td>
	    </tr></table>
	</td>
    </tr>
    <tr><td colspan='3' style="height:1px;border-bottom:solid gray thin;"></td></tr>
    <tr>
	<td colspan='3'>
	<table border='0' style="width:100%;">
	    <tr>
		<td style="height:70px;text-align:left;padding-right:50px;">
		    Campaign Name: <span style="font-size:14px;color:gray;"><?php echo htmlentities($campaign_name); ?></span>
		    <br>
		    Campaign Subject: <span style="font-size:14px;color:gray;"><?php echo htmlentities($subject); ?></span>
		    <br>
		    Intro Message: <span style="font-size:14px;color:gray;padding-left:10px;">
		    <?php if($intro == ""){echo "no intro message found";}else{ echo htmlentities($intro);} ?>
		    </span>
		</td>
		<td style="width:135px;">
		    <div class="editsubject"><button type="submit" name="subedit" id="subeditpop" value="subedit" style="cursor:pointer;height:25px;font-size:12px;">Edit Subject</button></div>
		</td>
	    </tr>
	</table>
	</td>
    </tr>
    <tr><td colspan='3' style="height:1px;border-bottom:solid gray thin;"></td></tr>
    <tr>
	<td colspan='3'>
	<table border='0' style="width:100%;"><tr>
	<td colspan='1' style="width:185px;height:70px;text-align:center;">
	    <div class="newquestion"><button type="submit" alt="editqapop" name="createqapop" id="createqapop" value="Add New Question" style="cursor:pointer;height:40px;font-weight:bold;font-size:15px;">Add New Question</button></div>
	</td>
	<td colspan='1' style="width:155px;height:70px;text-align:center;">
	    <div class="preview"><button type="submit" alt="previewpop" name="previewpop" id="previewpop" value="Preview" style="cursor:pointer;height:40px;font-weight:bold;font-size:15px;">Preview Email</button></div>
	</td>
	<td colspan='1' style="height:70px;text-align:center;padding-left:10px;font-size:12px;color:grey;">
	    <form action="what.php" method="post">
	    <select name="show_questions" onChange="this.form.submit();" style="text-align:center;color:#f4911e;font-weight:bold;font-size:15px;width:250px;outline: #f4911e thin solid;">
		<option <?php echo $showing1; ?> value="all">Show All Question</option>
		<option <?php echo $showing2; ?> value="active">Show Only Active</option>
		<option <?php echo $showing3; ?> value="inactive">Show Only Inactive</option>
	    </select>
	    </form>
	    <!--
	    &#149; Questions/Answers cannot be edited, just copied.
	    <br>
	    &#149; Once copied, the original question is automatically deactivated.
	    <br>
	    &#149; This protects the integrity of your data.
	    -->
	</td>
	</tr></table>
	</td>
    </tr>
    <tr><td colspan='3' style="height:1px;border-bottom:solid gray thin;"></td></tr>
    <tr>
	<td colspan='3' style="text-align:left;">
	    <form action="what.php" method="post">
	    <table border='0' style="width:100%;">
	    <?php // Print the Questions and Answers
	    
	    if(mysql_num_rows($query_questions) != 0){
		
		while($question_list = mysql_fetch_array($query_questions)){
		    
		    if($question_list['status'] == 'inactive'){
			$status = "color:gray;";
			$status_button = "<button type=\"submit\" name=\"activate_question\" value=\"".$question_list['id']."\" class=\"ui-button ui-widget ui-state-default ui-corner-all\" style=\"cursor:pointer;font-size:15px;width:70px;height:25px;\" >Activate</button>";
		    }else{
			$status = "";
			$status_button = "<button type=\"submit\" name=\"deactivate_question\" value=\"".$question_list['id']."\" class=\"ui-button ui-widget ui-state-default ui-corner-all\" style=\"cursor:pointer;font-size:15px;width:90px;height:25px;\" >Deactivate</button>";
		    }
		    
		    //echo the campaign:question subject, if it exists
		    if($question_list['subject'] != ""){
			echo "	<tr>
				    <td style=\"padding-top:20px;".$status."\">
					<b>".$subject.": ".$question_list['subject']."</b>
				    </td>
				    <td style=\"width:155px;\">
				    </td>
				</tr>
			    ";
		    }
		    
		    //build the more info url if it exists
		    if($question_list['url'] != ""){
			
			//check url to see if conditioning is needed
			$url = $question_list['url'];
			if(!isValidURL($url)){
			    $info_link = "http://".$url;
			}else{
			    $info_link = $url;
			}
			
			//prep more info echo
			$print_url = "<a href=\"".$info_link."\"><span style=\"font-size:12px;\">(more info)</span></a>";
		    }else{
			$print_url = "";
		    }
		    
		    //echo the question text and buttons
		    echo "  <tr>
				<td style=\"padding-left:10px;".$status."\">
				    ".$question_list['question']." ".$print_url."
				</td>
				<td style=\"width:155px;\">
				    <button type=\"submit\" name=\"edit\" value=\"".$question_list['id']."\" class=\"ui-button ui-widget ui-state-default ui-corner-all\" style=\"cursor:pointer;font-size:15px;width:50px;height:25px;\" >Copy</button>
				    ".$status_button."
				</td>
			    </tr>
			";
		    
		    //echo the answers
		    $get_answers_list = mysql_query("SELECT id, answer, url FROM answers
					       WHERE campaign_id = '{$campaign_id}'
					       AND question_id = '{$question_list['id']}' ");
		    while($answers_list_array = mysql_fetch_array($get_answers_list)){
			//build the more info url if it exists
			if($answers_list_array['url'] != ""){
			
			    //check url to see if conditioning is needed
			    $url = $answers_list_array['url'];
			    if(!isValidURL($url)){
			        $info_link = "http://".$url;
			    }else{
			        $info_link = $url;
			    }
			    
			    //prep more info echo
			    $print_urlans = "<a href=\"".$info_link."\"><span style=\"font-size:12px;\">(more info)</span></a>";
			}else{
			    $print_urlans = "";
			}
			
			
			echo "	<tr>
				    <td style=\"padding-left:30px;".$status."\">
					&#149; ".$answers_list_array['answer']." ".$print_urlans."
				    </td>
				    <td style=\"width:155px;\">
				    </td>
				</tr>
			    ";
			
		    }
		    //echo gray line
		    echo "<tr><td colspan='3' style=\"height:1px;border-bottom:solid gray thin;\"></td></tr>";
		}
	    }else{
		echo "no questions found";
	    }
	    ?>
	    </table>
	    </form>
	</td>
    </tr>
</table>
</center>

<?php include("includes/footer.php");?>