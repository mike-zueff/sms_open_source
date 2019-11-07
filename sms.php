#!/usr/bin/env php
<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/lib/lib.php';

sms_groups_watched_get_data();
/*
$response = $vk_api_client->users()->get($vk_api_token, array(
  "fields" => array('city', 'photo'),
  'user_ids' => array(1, 210700286),
));
var_dump($response);
 */
