<?php

$API_KEY = '30908a47b6ef1a46cdefc078591d5ff9a2cef7b1a4a24d3e6b5d64de6600575a';
$REQUEST_URL = 'https://api.change.org/v1/petitions/get_id';
$PETITION_URL = 'http://www.change.org/petitions/dunkin-donuts-stop-using-styrofoam-cups-and-switch-to-a-more-eco-friendly-solution';

$parameters = array(
  'api_key' => $API_KEY,
  'petition_url' => $PETITION_URL
);

$query_string = http_build_query($parameters);
$final_request_url = "$REQUEST_URL?$query_string";
$response = file_get_contents($final_request_url);

$json_response = json_decode($response, true);
$petition_id = $json_response['petition_id'];
echo $petition_id;

?>