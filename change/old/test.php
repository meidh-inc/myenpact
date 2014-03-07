<?php require_once("includes/session.php"); ?>
<?php require_once("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php include_once("includes/form_functions.php"); ?>
<?php

//get petition URL from page form
//
//
//
//




/*
//Get the id number for this petition
$API_KEY = '30908a47b6ef1a46cdefc078591d5ff9a2cef7b1a4a24d3e6b5d64de6600575a';
$REQUEST_URL = 'https://api.change.org/v1/petitions/get_id';
$PETITION_URL = 'http://www.change.org/petitions/kcci-and-hearst-corporation-save-the-weather-beacon';

$parameters = array(
  'api_key' => $API_KEY,
  'petition_url' => $PETITION_URL
);

$query_string = http_build_query($parameters);
$final_request_url = "$REQUEST_URL?$query_string";
$response = file_get_contents($final_request_url);

$json_response = json_decode($response, true);

print_r($json_response);

$petition_id = $json_response['petition_id'];


//Get signature information for this petition
$SIG_REQUEST_URL = 'https://api.change.org/v1/petitions/'.$petition_id.'/signatures';
$SIG_parameters = array(
  'api_key' => $API_KEY,
);
$SIG_query_string = http_build_query($SIG_parameters);
$SIG_final_request_url = "$SIG_REQUEST_URL?$SIG_query_string";
$SIG_response = file_get_contents($SIG_final_request_url);

$SIG_json_response = json_decode($SIG_response, true);
*/


//print_r($SIG_json_response['signatures']['0']['name']);
//echo $SIG_json_response['signatures']['1']['name'];


//echo $petition_id;

?>
<?php  //TEST CODE




?>
<!DOCTYPE html>
<html> 
<head> 
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" /> 
  <title>Change Map by MeidhTech</title>
  <style type="text/css">
      html { height: 100% }
      body { height: 100%; margin: 0; padding: 0 }
      #map_canvas { height: 100% }
  </style>
  <script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
</head> 
<body>
<div style="width:100%;height:100%;position:relative;">
    <div style="width:600px;height:150px;background-image:url('images/menu.png');text-align:center;position:absolute;left:30%;z-index:10;">
      <div style="opacity=1.0;z-index:20;">
      See where your Change.org petition is popular:<br>
      <form action="index.php" method="post">
        <input type="text" name="petition_url" style="width:400px;margin-top:10px;" />
        <input type="submit" name="submit" value="Enter" style="margin-top:10px;" />
      </form>
      </div>
    </div>
  <div id="map" style="width:100%;height:100%;position:absolute;"></div>
</div>
  <script type="text/javascript">
    var locations = [
        <?php
            $petition_id = "11111";
            $get_locations = mysql_query("SELECT * FROM signers WHERE petition_id = '{$petition_id}' ");
            $order = 1;
            while($array_locations = mysql_fetch_array($get_locations)){
                $name = "<div style=\"width:190px;height:200px;overflow-y:scroll;\" >This is a really long sentence, repeated.This is a really long sentence, repeated.</div>";
                $lat = $array_locations['lat'];
                $long = $array_locations['long'];
                echo "['".$name."', ".$lat.", ".$long.", ".$order."],";
                $order++;
            }
        ?>
    ];

    var map = new google.maps.Map(document.getElementById('map'), {
      zoom: 10,
      center: new google.maps.LatLng(41.672687, -93.572173),
      mapTypeId: google.maps.MapTypeId.HYBRID
    });

    var infowindow = new google.maps.InfoWindow({
        maxWidth: 200
        });

    var marker, i;

    for (i = 0; i < locations.length; i++) {  
      marker = new google.maps.Marker({
        position: new google.maps.LatLng(locations[i][1], locations[i][2]),
        map: map
      });

      google.maps.event.addListener(marker, 'click', (function(marker, i) {
        return function() {
          infowindow.setContent(locations[i][0]);
          infowindow.open(map, marker);
        }
      })(marker, i));
    }
  </script>
</body>
</html>