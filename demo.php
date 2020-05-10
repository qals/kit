<?php

include "vendor/autoload.php";
include "lib/GoogleAuth.php";

use Als\GoogleAuth;

$client_id = '';
$client_sv = '';

$auth = new GoogleAuth($client_id, $client_sv);

/*
$data = $auth->getDevice("https://www.googleapis.com/auth/drive.file");
var_dump($data);
$code = ' device_code ';
$data = $auth->authDevice($code);
// */

/*
$url = $auth->getUrl("https://www.googleapis.com/auth/drive.file");
echo $url . "\n\n";
$code = ' access_code ';
$data = $auth->doAuth($code);
var_dump($data);
// */

/*
$refresh_token = ' refresh_token ';
$data = $auth->doRefresh($refresh_token);
var_dump($data);
// */
