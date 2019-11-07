<?php
require __DIR__ . '/../vendor/autoload.php';

const B_DEBUG_ENABLED = true;
const I_E_WALL_GET_CONTENT_NOT_AVAILABLE = 19;
const I_E_WALL_GET_PAGE_REMOVED_OR_BLOCKED = 18;
const I_E_WALL_GET_THIS_PROFILE_IS_PRIVATE = 30;

/* Materials older than 6 years are obsolete. */
/* TODO */
/* const I_MATERIAL_DATE_LIMIT = 60 * 60 * 24 * 366 * 6; */
const I_MATERIAL_DATE_LIMIT = 60 * 60 * 24 * 7;

const I_VK_API_DEFAULT_WALL_GET_COUNT = 100;
const S_DB_PATH = 'data/sms_db.sqlite';

function sms_debug($s_message) {
  if (B_DEBUG_ENABLED) {
    echo '  ' . $s_message . PHP_EOL;
  }
}

function sms_groups_watched_get_data() {
  global $a_groups_watched;
  global $i_timestamp;
  global $o_vk_api_client;
  global $s_vk_api_token;

  foreach ($a_groups_watched as $s_gw) {
    $i_offset = 0;

    do {
      sms_debug('wall.get, ' . $s_gw . ', ' . $i_offset);

      try {
        $o_response = $o_vk_api_client->wall()->get($s_vk_api_token, array(
          'count' => I_VK_API_DEFAULT_WALL_GET_COUNT,
          'offset' => $i_offset,
          'owner_id' => $s_gw,
        ));

      } catch (Exception $e) {
        switch ($e->getCode()) {
        case I_E_WALL_GET_CONTENT_NOT_AVAILABLE:
          break;
        case I_E_WALL_GET_PAGE_REMOVED_OR_BLOCKED:
          break;
        case I_E_WALL_GET_THIS_PROFILE_IS_PRIVATE:
          break;
        default:
          break;
        }
      }


      $i_offset += I_VK_API_DEFAULT_WALL_GET_COUNT;
    } while ($o_response['items'][1]['date'] >= $i_timestamp - I_MATERIAL_DATE_LIMIT);
  }
}

function sms_log($s_message) {
  echo $s_message . PHP_EOL;
}

function sms_shutdown() {
  sms_log('SMS stopped.');
}

$a_groups_watched = file('private/groups_watched', FILE_IGNORE_NEW_LINES);
$o_settlements = json_decode(file_get_contents('data/vor_obl_settlements.json'));
$o_sqlite = null;
$o_vk_api_client = new VK\Client\VKApiClient();
$s_vk_api_token = trim(file_get_contents('private/vk_api_token'));

date_default_timezone_set('Europe/Moscow');
$i_timestamp = time();

register_shutdown_function('sms_shutdown');
sms_log('SMS started.');

$o_sqlite = new SQLite3(S_DB_PATH);
$o_result = $o_sqlite->query('SELECT * FROM wall_get');
var_dump($o_result);
