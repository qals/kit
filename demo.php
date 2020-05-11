<?php

include "vendor/autoload.php";
include "lib/GoogleAuth.php";

use Als\GoogleAuth;

$client_id = '';
$client_sec = '';

$auth = new GoogleAuth($client_id, $client_sec);

/*
$code = 'AH-1Ng3GefaZbKj_hvN0cfQrvTn0sl04DcMS-ksth-8vxbBDN1u43-Tqjdv2lhYgJLrxV_dcTNB_4l23dxMtcYYO8s620ZV99A';
$data = $auth->getDevice("https://www.googleapis.com/auth/drive.file");
$data = $auth->authDevice($code);
var_dump($data);
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
