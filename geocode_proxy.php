<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");


if (!isset($_GET['q']) || trim($_GET['q']) === '') {
    echo json_encode(["error" => "Missing query"]);
    exit;
}

$q = urlencode($_GET['q']);


$viewbox = "120.52,16.20,120.76,16.52"; 
$url = "https://nominatim.openstreetmap.org/search?format=json&limit=5&bounded=1&viewbox=$viewbox&countrycodes=ph&q={$q}&email=baguioalert@app.local";


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_USERAGENT, "ItogonDisasterApp/1.0 (admin@baguioalert.local)");
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(["error" => curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);
echo $response;
