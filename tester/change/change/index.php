<?php require_once("includes/session.php"); ?>
<?php require_once("includes/connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php include_once("includes/form_functions.php"); ?>
<?php
//establish our API key
$API_KEY = '30908a47b6ef1a46cdefc078591d5ff9a2cef7b1a4a24d3e6b5d64de6600575a';

//prevent any errors from appearing to the user
error_reporting(0);


//get petition URL from page form
if ((isset($_POST['submit_url'])) && ($_POST['petition_url'] != "")) {
  
  
  //get the submitted petition URL
  $PETITION_URL = trim(mysql_prep($_POST['petition_url']));
  
  //log the ip address of the user
  $ip = $ip=$_SERVER['REMOTE_ADDR'];
  $timestamp = date("Y-m-d h:i:s a");
  $insert_use = mysql_query("INSERT INTO uselog ( ip_address, timestamp, petition_url
                                      )VALUES( '{$ip}', '{$timestamp}', '{$PETITION_URL}' )");

  //First, check to see if it should be added or updated
  $check_petition = mysql_query("SELECT petition_id FROM petitions WHERE petition_url = '{$PETITION_URL}' ");
  $count_petition = mysql_num_rows($check_petition);
  
  
  if($count_petition >= 1){  //Petition exists, UPDATE PETITION
    echo "Petition already in system.";
    
    $get_petition_info = mysql_query("SELECT * FROM petitions WHERE petition_url = '{$PETITION_URL}' ");
    $array_petition_info = mysql_fetch_array($get_petition_info);
    $petition_id = $array_petition_info['petition_id'];
    $petition_title = $array_petition_info['title'];
    $petition_image = $array_petition_info['img_url'];
    $petition_sigcount = $array_petition_info['sig_count'];
    
    
  }else{  //New Petition, ADD NEW PETITION
    
    
    //Get the id number for this petition
    $REQUEST_URL = 'https://api.change.org/v1/petitions/get_id';
    $parameters = array(
      'api_key' => $API_KEY,
      'petition_url' => $PETITION_URL
    );
    $query_string = http_build_query($parameters);
    $final_request_url = "$REQUEST_URL?$query_string";
    $response = file_get_contents($final_request_url);
    $json_response = json_decode($response, true);
    $petition_id = $json_response['petition_id'];
    
    //Get more information about this petition
    $PET_REQUEST_URL = 'https://api.change.org/v1/petitions/'.$petition_id.'';
    $PET_parameters = array(
      'api_key' => $API_KEY
    );
    $PET_query_string = http_build_query($PET_parameters);
    $PET_final_request_url = "$PET_REQUEST_URL?$PET_query_string";
    $PET_response = file_get_contents($PET_final_request_url);
    $PET_json_response = json_decode($PET_response, true);
    $petition_title = $PET_json_response['title'];
    $petition_image = $PET_json_response['image_url'];
    $petition_sigcount = $PET_json_response['signature_count'];
    
    //calculate page counter for signature collection loop
    $page_count = $petition_sigcount/100;
    
    
    $upload_new_petition = mysql_query("INSERT INTO petitions ( petition_id, title, petition_url, sig_count, img_url
                                                      )VALUES( '{$petition_id}', '{$petition_title}', '{$PETITION_URL}', '{$petition_sigcount}', '{$petition_image}' )");
    
    //loop the sig request to get all of the signers across multiple pages
    //WE ONLY ARE ABLE TO COLLECT THOSE WHO HAVE CHOSEN NOT TO MAKE THEIR SIGNATURE PRIVATE
    for($p=1; $p<=$page_count ; $p++){
      $i=0;
      $SIG_REQUEST_URL = 'https://api.change.org/v1/petitions/'.$petition_id.'/signatures';
      $SIG_parameters = array(
        'api_key' => $API_KEY,
        'page_size' => '100',
        'page' => $p
      );
      $SIG_query_string = http_build_query($SIG_parameters);
      $SIG_final_request_url = "$SIG_REQUEST_URL?$SIG_query_string";
      $SIG_response = file_get_contents($SIG_final_request_url);
      $SIG_json_response = json_decode($SIG_response, true);
      //print_r($SIG_json_response);
      $page = $SIG_json_response['page'];
      
      for($i=0; $SIG_json_response['signatures'][$i]['name']!=""; $i++){
        $sig_name = $SIG_json_response['signatures'][$i]['name'];
        $sig_city = $SIG_json_response['signatures'][$i]['city'];
        $sig_state = $SIG_json_response['signatures'][$i]['state_province'];
        
        //look up lat and long
        $get_geocode = mysql_query("SELECT * FROM geocode WHERE city = '{$sig_city}' AND state = '{$sig_state}'
                               ");
        $array_geocode = mysql_fetch_array($get_geocode);
        
        $sig_lat = $array_geocode['lat'];
        $sig_long = $array_geocode['long'];
        //echo "-".$sig_name.": ".$sig_city.", ".$sig_state." - ".$sig_lat.", ".$sig_long."<br>";
        //insert signer into database
        
        //$add_signer = "INSERT INTO signers ( petition_id, name, city, state, lat, long
	//					   )VALUES( '{$petition_id}', '{$sig_name}', '{$sig_city}', '{$sig_state}', '{$sig_lat}', '{$sig_long}' )";
        
        $query_signers = "INSERT INTO signers ( petition_id, name, city, state, latitude, longitude
				) VALUES ( '{$petition_id}', '{$sig_name}', '{$sig_city}', '{$sig_state}', '{$sig_lat}', '{$sig_long}')";
		$result_signers = mysql_query($query_signers, $connection);
        
        
        
      }
      
    }
    
  }
  
}

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
<div style="width:100%;height:100%;position:relative;font-family:sans-serif;">
    <div style="width:600px;height:150px;background-image:url('images/menu.png');text-align:center;position:absolute;left:30%;z-index:10;">
      <div style="opacity=1.0;z-index:20;">
      <h2>See where your Change.org petition is popular:</h2>
      <form action="index.php" method="post">
        <input type="text" name="petition_url" style="width:400px;" />
        <input type="submit" name="submit_url" value="Enter" /><br>
        <span style="font-size:10px;">Enter your petition's URL (example: http://www.change.org/petitions/my-example-petition)</span>
      </form>
      </div>
    </div>
  <div id="map" style="width:100%;height:100%;position:absolute;"></div>
</div>

        
  <script type="text/javascript">
    var locations = [
        <?php
          if(!isset($_POST['submit_url'])){
            //nothing
          }else{
            $get_locations = mysql_query("SELECT *, COUNT(id) as idcnt FROM signers WHERE petition_id = '{$petition_id}' GROUP BY city ORDER BY idcnt DESC LIMIT 800");
            $order = 1;
            while($array_locations = mysql_fetch_array($get_locations)){
                $lat = $array_locations['latitude'];
                $long = $array_locations['longitude'];
                /*
                //get petition link, image
                $get_link_img = mysql_query("SELECT title, petition_url, img_url FROM petitions WHERE petition_id = '{$petition_id}' ");
                $array_link_img = mysql_fetch_array($get_link_img);
                $pet_title= $array_link_img['title'];
                $pet_link = $array_link_img['petition_url'];
                $pet_img = $array_link_img['img_url'];
                */
                //get sig count for this petition in this city
                $get_sigcount = mysql_query("SELECT id FROM signers WHERE city = '{$array_locations['city']}' AND petition_id = '{$petition_id}' ");
                $count_sigcount = mysql_num_rows($get_sigcount);
                
                //get total sigs for this city
                $get_totsigcount = mysql_query("SELECT id FROM signers WHERE city = '{$array_locations['city']}' ");
                $count_totsigcount = mysql_num_rows($get_totsigcount);
                
                //calculate percentage
                $sig_percent = round(($count_sigcount/$count_totsigcount)*100,3);
                $total_percent = round(($count_sigcount/$petition_sigcount)*100,3);
                
                //set the marker
                if($count_sigcount <= 2){
                  $marker = "images/level1_circle.png";
                }elseif($count_sigcount >= 3 && $count_sigcount <= 9){
                  $marker = "images/level2_circle.png";
                }elseif($count_sigcount >= 10 && $count_sigcount <= 29){
                  $marker = "images/level3_circle.png";
                }elseif($count_sigcount >= 30 && $count_sigcount <= 99){
                  $marker = "images/level4_circle.png";
                }elseif($count_sigcount >= 100 && $count_sigcount <= 299){
                  $marker = "images/level5_circle.png";
                }elseif($count_sigcount >= 300 && $count_sigcount <= 999){
                  $marker = "images/level6_circle.png";
                }elseif($count_sigcount >= 1000 && $count_sigcount <= 2999){
                  $marker = "images/level7_circle.png";
                }else{
                  $marker = "images/level8_circle.png";
                }
                //$marker = "images/blue_marker.png";
                
                $name = "<table style=\"font-family:sans-serif;height:150px;\"><tr><td><a href=\"".$PETITION_URL."\" target=\"_blank\">".$petition_title."</a></td><td rowspan=\"4\"><img src=\"".$petition_image."\" alt=\"Petition IMG\" style=\"height:100px;\"></td></tr><tr><td>".$count_sigcount." signatures in ".$array_locations['city']." for this petition</td></tr><tr><td>".$total_percent."&#37; of this petition&#39;s signatures are in ".$array_locations['city']."</td></tr><tr><td>".$sig_percent."&#37; of all signatures in ".$array_locations['city']." are for this petition</td></tr></table>";
                
                echo "['".$name."', ".$lat.", ".$long.", ".$order.", '".$marker."' ],";
                $order++;
            }
          }
        ?>
    ];

    var map = new google.maps.Map(document.getElementById('map'), {
      zoom: 10,
      center: new google.maps.LatLng(41.672687, -93.572173),
      mapTypeId: google.maps.MapTypeId.HYBRID
    });

    var infowindow = new google.maps.InfoWindow({
        maxWidth:600
        });

    var marker, i;

    for (i = 0; i < locations.length; i++) {  
      marker = new google.maps.Marker({
        icon: locations[i][4],
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