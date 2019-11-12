<?php
require __DIR__ . '/../vendor/autoload.php';

const B_DEBUG_ENABLED = true;
/* TODO Fix all limits. */
const I_DATE_LIMIT_WALL_GETCOMMENTS = 60 * 60 * 4 * 1;
const I_DATE_LIMIT_WALL_GET = 60 * 60 * 4 * 1;
const I_NULL_VALUE = -1;
const I_VK_API_WALL_GETCOMMENTS_COUNT_DEFAULT = 5; //100 TODO
const I_VK_API_WALL_GET_COUNT_DEFAULT = 5; //100 TODO

function sms_db_analyze_data() {
}

function sms_db_posts_fetch_comments() {
  global $i_timestamp;
  global $o_sqlite;

  $o_db_data_posts = $o_sqlite->query('SELECT * FROM wall_get');

  while ($a_i = $o_db_data_posts->fetchArray()) {
    $i_offset = 0;

    if ($a_i['date'] < $i_timestamp - I_DATE_LIMIT_WALL_GETCOMMENTS) {
      continue;
    }

    do {
      $b_need_for_break = false;
      $o_result = sms_vk_api_wall_getcomments($a_i['owner_id'], $a_i['post_id'], $i_offset, I_NULL_VALUE);

      if ($o_result != null) {
        if (count($o_result['items']) != 0) {
          $b_need_for_break = true;
        }

        foreach ($o_result['items'] as $o_ri) {
          if (!array_key_exists('deleted', $o_ri)) {
            $i_db_comment_id = $o_ri['id'];
            $i_db_date = $o_ri['date'];
            $i_db_from_id = $o_ri['from_id'];
            $i_db_owner_id = $o_ri['owner_id'];
            $i_db_parent_comment_id = I_NULL_VALUE;
            $i_db_post_id = $o_ri['post_id'];
            $i_offset_nested = 0;
            $s_db_text = $o_ri['text'];

            if (array_key_exists('attachments', $o_ri)) {
              $s_db_attachments = serialize($o_ri['attachments']);
            } else {
              $s_db_attachments = '';
            }

            $i_db_settlement_id = sms_user_fetch_settlement_id($i_db_from_id);

            $o_sqlite->exec("INSERT INTO wall_getcomments(attachments, settlement_id, comment_id, date, from_id, owner_id, parent_comment_id, post_id, text) VALUES('$s_db_attachments', $i_db_settlement_id, $i_db_comment_id, $i_db_date, $i_db_from_id, $i_db_owner_id, $i_db_parent_comment_id, $i_db_post_id, '$s_db_text')");

            if ($o_ri['thread']['count'] > 0) {
              do {
                $b_need_for_break_nested = false;
                $o_result_nested = sms_vk_api_wall_getcomments($a_i['owner_id'], $a_i['post_id'], $i_offset_nested, $i_db_comment_id);

                if ($o_result_nested != null) {
                  if (count($o_result_nested['items']) != 0) {
                    $b_need_for_break_nested = true;
                  }

                  foreach ($o_result_nested['items'] as $o_rin) {
                    if (!array_key_exists('deleted', $o_rin)) {
                      $i_db_comment_id_nested = $o_rin['id'];
                      $i_db_date_nested = $o_rin['date'];
                      $i_db_from_id_nested = $o_rin['from_id'];
                      $i_db_owner_id_nested = $o_rin['owner_id'];
                      $i_db_parent_comment_id_nested = $i_db_comment_id;
                      $i_db_post_id_nested = $o_rin['post_id'];
                      $s_db_text_nested = $o_rin['text'];

                      if (array_key_exists('attachments', $o_rin)) {
                        $s_db_attachments_nested = serialize($o_rin['attachments']);
                      } else {
                        $s_db_attachments_nested = '';
                      }

                      $i_db_settlement_id_nested = sms_user_fetch_settlement_id($i_db_from_id_nested);

                      if (sms_settlement_check($i_db_settlement_id_nested)) {
                        $o_sqlite->exec("INSERT INTO wall_getcomments(attachments, settlement_id, comment_id, date, from_id, owner_id, parent_comment_id, post_id, text) VALUES('$s_db_attachments_nested', $i_db_settlement_id_nested, $i_db_comment_id_nested, $i_db_date_nested, $i_db_from_id_nested, $i_db_owner_id_nested, $i_db_parent_comment_id_nested, $i_db_post_id_nested, '$s_db_text_nested')");
                      }
                    }
                  }
                } else {
                  sms_echo('error, wall.getcomments, nested, https://vk.com/wall' . $i_db_owner_id . '_' . $i_db_post_id . '?reply=' . $i_db_comment_id);
                  break;
                }

                $i_offset_nested += I_VK_API_WALL_GETCOMMENTS_COUNT_DEFAULT;
              } while (!$b_need_for_break_nested);
            }
          }
        }
      } else {
        sms_echo('error, wall.getcomments, https://vk.com/wall' . $a_i['owner_id'] . '_' . $a_i['post_id']);
        break;
      }

      $i_offset += I_VK_API_WALL_GETCOMMENTS_COUNT_DEFAULT;
    } while (!$b_need_for_break);
  }
}

function sms_debug($s_message) {
  if (B_DEBUG_ENABLED) {
    echo '  ' . $s_message . PHP_EOL;
  }
}

function sms_echo($s_message) {
  echo $s_message . PHP_EOL;
}

function sms_log($s_message) {
  global $r_log_file;

  fwrite($r_log_file, $s_message . PHP_EOL);
  sms_echo($s_message);
}

function sms_settlement_check($i_settlement_id) {
  global $a_settlements;

  if($i_settlement_id == I_NULL_VALUE) {
    return false;
  }

  foreach ($a_settlements['items'] as $a_si) {
    if ($a_si['id'] == $i_settlement_id) {
      return true;
    }
  }

  return false;
}

function sms_shutdown() {
  global $r_log_file;

  fclose($r_log_file);
  sms_echo('SMS stopped.');
}

function sms_user_fetch_settlement_id($i_user_id) {
  $o_result = sms_vk_api_user_get($i_user_id, 'city');

  if ($o_result != null && array_key_exists('city', $o_result[0])) {
    return $o_result[0]['city']['id'];
  } else {
    return I_NULL_VALUE;
  }
}

function sms_vk_api_user_get($i_user_id, $s_fields) {
  global $o_vk_api_client;
  global $s_vk_api_token;

  if ($i_user_id < 0) {
    return null;
  }

  sms_debug('user.get | ' . $i_user_id . ' | ' . $s_fields);

  try {
    $o_response = $o_vk_api_client->users()->get($s_vk_api_token, [
      'fields' => $s_fields,
      'user_ids' => $i_user_id,
    ]);

    return $o_response;
  } catch (Exception $e) {
    return null;
  }
}

function sms_vk_api_wall_get($i_owner_id, $i_offset) {
  global $o_vk_api_client;
  global $s_vk_api_token;

  sms_debug('wall.get | ' . $i_owner_id . ' | ' . $i_offset);

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

function sms_vk_api_wall_getcomments($i_owner_id, $i_post_id, $i_offset, $i_comment_id) {
  global $o_vk_api_client;
  global $s_vk_api_token;

  sms_debug('wall.getcomments | ' . $i_owner_id . ' | ' . $i_post_id . ' | ' . $i_offset . ' | ' . $i_comment_id);

  $a_getcomments = [
    'count' => I_VK_API_WALL_GETCOMMENTS_COUNT_DEFAULT,
    'offset' => $i_offset,
    'owner_id' => $i_owner_id,
    'post_id' => $i_post_id,
    'preview_length' => 0,
    'sort' => 'asc',
  ];

  if ($i_comment_id != I_NULL_VALUE) {
    $a_getcomments['comment_id'] = $i_comment_id;
  }

  try {
    $o_response = $o_vk_api_client->wall()->getComments($s_vk_api_token, $a_getcomments);

    return $o_response;
  } catch (Exception $e) {
    return null;
  }
}

function sms_watched_owners_wall_get() {
  global $i_timestamp;
  global $o_sqlite;

  $a_watched_owners = file('private/watched_owners.txt', FILE_IGNORE_NEW_LINES);

  foreach ($a_watched_owners as $s_wo) {
    $i_offset = 0;

    do {
      $b_need_for_break = false;
      $o_result = sms_vk_api_wall_get($s_wo, $i_offset);

      if ($o_result != null) {
        foreach ($o_result['items'] as $o_ri) {
          $i_db_owner_id = $o_ri['owner_id'];
          $i_db_post_id = $o_ri['id'];

          if ($o_sqlite->querySingle("SELECT * FROM wall_get WHERE owner_id = $i_db_owner_id AND post_id = $i_db_post_id") != null) {
            if (!array_key_exists('is_pinned', $o_ri)) {
              $b_need_for_break = true;

              break;
            } else {
              continue;
            }
          } else {
            $i_db_date = $o_ri['date'];
            $i_db_from_id = $o_ri['from_id'];
            $s_db_text = $o_ri['text'];

            if (array_key_exists('attachments', $o_ri)) {
              $s_db_attachments = serialize($o_ri['attachments']);
            } else {
              $s_db_attachments = '';
            }

            $i_db_settlement_id = sms_user_fetch_settlement_id($i_db_from_id);

            $o_sqlite->exec("INSERT INTO wall_get(attachments, settlement_id, date, from_id, owner_id, post_id, text) VALUES('$s_db_attachments', $i_db_settlement_id, $i_db_date, $i_db_from_id, $i_db_owner_id, $i_db_post_id, '$s_db_text')");
          }
        }
      } else {
        sms_echo('error, wall.get, https://vk.com/wall' . $s_wo . '?own=1&offset=' . $i_offset);
        break;
      }

      $i_offset += I_VK_API_WALL_GET_COUNT_DEFAULT;

      if ($b_need_for_break || count($o_result['items']) == 1 || $i_timestamp > $o_result['items'][1]['date'] + I_DATE_LIMIT_WALL_GET) {
        break;
      }
    } while (true);
  }
}

$a_settlements = json_decode(file_get_contents('data/vor_obl_settlements.json'), true);
$o_sqlite = new SQLite3('data/sms_db.sqlite');
$o_vk_api_client = new VK\Client\VKApiClient();
$r_log_file = fopen('data/log.txt', 'w');
$s_vk_api_token = trim(file_get_contents('private/vk_api_token.txt'));

date_default_timezone_set('Europe/Moscow');
$i_timestamp = time();
register_shutdown_function('sms_shutdown');
sms_echo('SMS started.');
