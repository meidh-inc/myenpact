<?php require_once("includes/session.php"); ?>
<?php require_once("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php include_once("includes/form_functions.php"); ?>
<?php

//find petition ids in signers table not found in petitions table



//loop through the resulting array


//gather required info via change.org's api


//submit it to the petitions table


//delete signers
$remove_signers = mysql_query("DELETE FROM signers WHERE petition_id = '327329' ");



?>