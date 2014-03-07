<?php require_once("includes/session.php"); ?>
<?php require_once("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php include_once("includes/form_functions.php"); ?>
<?php
$get_signers = mysql_query("SELECT * FROM signers");

while($array_signers = mysql_fetch_array($get_signers)){
    $id = $array_signers['id'];
    $city = $array_signers['city'];
    $state = $array_signers['state'];
    
    $get_geocode = mysql_query("SELECT * FROM geocode WHERE city = '{$city}' AND state = '{$state}'
                               ORDER BY id LIMIT 1");
    $array_geocode = mysql_fetch_array($get_geocode);
    
    $lat = $array_geocode['lat'];
    $long = $array_geocode['long'];
    
    $query_update_signers = "UPDATE signers SET
				    lat = '{$lat}',
				    long = '{$long}'
			    WHERE id = {$id}";
    $result_signers = mysql_query($query_update_signers);
    
    
    
    
    
    
    echo $id." - ".$city.", ".$state." - ".$lat.", ".$long."<br>";
    
}





?>