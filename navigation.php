<?php require_once("includes/functions.php"); ?>
<?php include_once("includes/form_functions.php"); ?>
<?php

//Navigation
if (isset($_POST['nav'])) {

    if($_POST['nav'] == 'mycampaigns'){
    redirect_to('mycampaigns.php');
    }
    if($_POST['nav'] == 'who'){
    redirect_to('who.php');
    }
    if($_POST['nav'] == 'what'){
    redirect_to('what.php');
    }
    if($_POST['nav'] == 'when'){
    redirect_to('when.php');
    }
    if($_POST['nav'] == 'results'){
    redirect_to('results.php');
    }
}



?>