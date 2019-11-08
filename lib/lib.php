<?php
require __DIR__ . '/../vendor/autoload.php';

const B_DEBUG_ENABLED = true;

/* Materials older than 6 years are obsolete. */
/* TODO const I_MATERIAL_DATE_LIMIT = 60 * 60 * 24 * 366 * 6; */
const I_MATERIAL_DATE_LIMIT = 60 * 60 * 24 * 1;

const I_VK_API_WALL_GET_COUNT_DEFAULT = 100;

function sms_debug($s_message) {
  if (B_DEBUG_ENABLED) {
    echo '  ' . $s_message . PHP_EOL;
  }
}

function sms_echo($s_message) {
  echo $s_message . PHP_EOL;
}

function sms_groups_watched_fetch() {
  global $i_timestamp;
  global $o_sqlite;

  $a_groups_watched = file('private/groups_watched.txt', FILE_IGNORE_NEW_LINES);

  foreach ($a_groups_watched as $s_gw) {
    $i_offset = 0;

    do {
      $b_need_to_stop = false;
      $o_result = sms_vk_api_wall_get($s_gw, $i_offset);

      if ($o_result != null) {
        foreach ($o_result['items'] as $o_ri) {
          $i_db_post_id = $o_ri['id'];

          if ($o_sqlite->querySingle("SELECT * FROM wall_get WHERE post_id = $i_db_post_id") != null) {
            $b_need_to_stop = true;
            break;
          } else {
            $i_db_date = $o_ri['date'];
            $i_db_from_id = $o_ri['from_id'];
            $i_db_owner_id = $o_ri['owner_id'];
            $s_db_text = $o_ri['text'];

            $o_sqlite->exec("INSERT INTO wall_get(date, from_id, owner_id, post_id, text) VALUES($i_db_date, $i_db_from_id, $i_db_owner_id, $i_db_post_id, '$s_db_text')");
          }
        }
      } else {
        sms_log('error, wall.get, https://vk.com/wall-' . $s_gw . '?own=1&offset=' . $i_offset);
        break;
      }

      $i_offset += I_VK_API_WALL_GET_COUNT_DEFAULT;
    } while (!$b_need_to_stop && $i_timestamp <= $o_result['items'][1]['date'] + I_MATERIAL_DATE_LIMIT);
  }
}

function sms_log($s_message) {
  global $r_log_file;

  fwrite($r_log_file, $s_message . PHP_EOL);
  sms_echo($s_message);
}

function sms_shutdown() {
  global $r_log_file;

  fclose($r_log_file);
  sms_echo('SMS stopped.');
}

function sms_vk_api_wall_get($i_owner_id, $i_offset) {
  global $o_vk_api_client;
  global $s_vk_api_token;

  sms_debug('wall.get, ' . $i_owner_id . ', ' . $i_offset);

  try {
    $o_response = $o_vk_api_client->wall()->get($s_vk_api_token, array(
      'count' => I_VK_API_WALL_GET_COUNT_DEFAULT,
      'offset' => $i_offset,
      'owner_id' => $i_owner_id,
    ));

    return $o_response;
  } catch (Exception $e) {
    return null;
  }
}

$o_sqlite = new SQLite3('data/sms_db.sqlite');
$o_vk_api_client = new VK\Client\VKApiClient();
$r_log_file = fopen('data/log.txt', 'w');
$s_vk_api_token = trim(file_get_contents('private/vk_api_token.txt'));

date_default_timezone_set('Europe/Moscow');
$i_timestamp = time();
register_shutdown_function('sms_shutdown');
sms_echo('SMS started.');
//TODO $o_settlements = json_decode(file_get_contents('data/vor_obl_settlements.json'));
