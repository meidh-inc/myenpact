<?php //require_once("includes/session.php"); ?>
<?php //require_once("includes/connection.php"); ?>
<?php //require_once("includes/functions.php"); ?>
<?php //include_once("includes/form_functions.php"); ?>
<?php







?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <style type="text/css">
      html { height: 100% }
      body { height: 100%; margin: 0; padding: 0 }
      #map_canvas { height: 100% }
    </style>
    <script type="text/javascript"
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCmMa9Y0p84Cc8Vk0OOGhzX_NFIsnbhoVI&sensor=false">
    </script>
    <script type="text/javascript">
      function initialize() {
        var mapOptions = {
          center: new google.maps.LatLng(41.600397, -93.608982),
          zoom: 12,
          mapTypeId: google.maps.MapTypeId.HYBRID
        };
        var map = new google.maps.Map(document.getElementById("map_canvas"),
            mapOptions);
      }
    </script>
  </head>
  <body onload="initialize()">
    <div style="width:100%;height:80px;background-color:#7B7754;text-align:center;">
      <form action="index.php" method="post">
        <input type="text" name="petition_url" style="width:400px;margin-top:30px;" />
        <input type="submit" name="submit" value="Enter" style="margin-top:30px;" />
      </form>
    </div>
    <div id="map_canvas" style="width:100%; height:100%"></div>
  </body>
</html>