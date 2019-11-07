<?php
require __DIR__ . '/../vendor/autoload.php';

const B_DEBUG_ENABLED = true;

function sms_debug($s_message) {
  if (B_DEBUG_ENABLED) {
    echo '  ' . $s_message . PHP_EOL;
  }
}

function sms_groups_watched_get_data() {
  global $a_groups_watched;

  foreach($a_groups_watched as $s_gw) {
    sms_debug('Current watched group: ' . $s_gw . '.');
  }
}

function sms_log($s_message) {
  echo $s_message . PHP_EOL;
}

function sms_shutdown() {
  sms_log('SMS stopped.');
}

$a_groups_watched = file('private/groups_watched', FILE_IGNORE_NEW_LINES);
$o_vk_api_client = new VK\Client\VKApiClient();
$s_settlements = file_get_contents('data/vor_obl_settlements.json');
$s_vk_api_token = trim(file_get_contents('private/vk_api_token'));

register_shutdown_function('sms_shutdown');
sms_log('SMS started.');
