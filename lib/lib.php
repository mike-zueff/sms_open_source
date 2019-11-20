<?php
require __DIR__ . '/../vendor/autoload.php';

const B_DEBUG_ENABLED = true;
const I_DATE_LIMIT_WALL_GET = 60 * 60 * 24 * 4;
const I_DATE_LIMIT_WALL_GETCOMMENTS = 60 * 60 * 24 * 4;
const I_NULL_VALUE = -1;
const I_USLEEP_TIME = 340 * 1000;
const I_VK_API_WALL_GETCOMMENTS_COUNT_DEFAULT = 100;
const I_VK_API_WALL_GET_COUNT_DEFAULT = 100;

function sms_db_analyze_data_wall_get() {
  global $a_ignored_items;
  global $a_patterns;
  global $o_sqlite;

  $a_db_data_posts = $o_sqlite->query('SELECT * FROM wall_get');
  $i_counter = 1;

  while ($a_pi = $a_db_data_posts->fetchArray()) {
    if (sms_settlement_check($a_pi['settlement_id'])) {
      $a_db_user_data = sms_user_fetch_data($a_pi['from_id']);

      if ($a_pi['from_id'] > 0) {
        if (in_array('owner|' . $a_pi['owner_id'], $a_ignored_items)) {
          continue;
        }

        if (in_array('post|' . $a_pi['owner_id'] . '|' . $a_pi['post_id'], $a_ignored_items)) {
          continue;
        }

        $a_settlement_data = sms_settlement_fetch_data($a_pi['settlement_id']);
        $need_for_log = false;
        $sms_log_buffer = '';
        $sms_log_buffer .= '********************************************************************************' . PHP_EOL;
        $sms_log_buffer .= '#' . $i_counter . ': ' . base64_decode($a_db_user_data['first_name']) . ' ' . base64_decode($a_db_user_data['last_name']) . PHP_EOL;

        if ($a_settlement_data['district'] != '' ) {
          $sms_log_buffer .= $a_settlement_data['district'] . ', ' . $a_settlement_data['settlement'] . PHP_EOL;
        } else {
          $sms_log_buffer .= $a_settlement_data['settlement'] . PHP_EOL;
        }

        $sms_log_buffer .= 'https://vk.com/wall' . $a_pi['owner_id'] . '_' . $a_pi['post_id'] . PHP_EOL;
        $sms_log_buffer .= 'post|' . $a_pi['owner_id'] . '|' . $a_pi['post_id'] . PHP_EOL;

        foreach ($a_patterns as $a_pattern) {
          $a_matches = [];
          preg_match_all($a_pattern, base64_decode($a_pi['text']), $a_matches);

          foreach ($a_matches[0] as $a_mi) {
            $need_for_log = true;
            $sms_log_buffer .= '  Text: ' . $a_mi . PHP_EOL;
          }
        }

        foreach ($a_patterns as $a_pattern) {
          $a_matches = [];
          preg_match_all($a_pattern, base64_decode($a_pi['attachments']), $a_matches);

          foreach ($a_matches[0] as $a_mi) {
            $need_for_log = true;
            $sms_log_buffer .= '  Attachments: ' . $a_mi . PHP_EOL;
          }
        }

        $sms_log_buffer .= '********************************************************************************';

        if ($need_for_log) {
          sms_log($sms_log_buffer);
          ++$i_counter;
        }
      }
    }
  }
}

function sms_db_analyze_data_wall_getcomments() {
  global $a_ignored_items;
  global $a_patterns;
  global $o_sqlite;

  $a_db_data_comments = $o_sqlite->query('SELECT * FROM wall_getcomments');
  $i_counter = 1;

  while ($a_ci = $a_db_data_comments->fetchArray()) {
    if (sms_settlement_check($a_ci['settlement_id'])) {
      $a_db_user_data = sms_user_fetch_data($a_ci['from_id']);

      if ($a_ci['from_id'] > 0) {
        if (in_array('owner|' . $a_ci['owner_id'], $a_ignored_items)) {
          continue;
        }

        if (in_array('all_comments_under|' . $a_ci['owner_id'] . '|' . $a_ci['post_id'], $a_ignored_items)) {
          continue;
        }

        if ($a_ci['parent_comment_id'] == I_NULL_VALUE) {
          if (in_array('comment|' . $a_ci['owner_id'] . '|' . $a_ci['post_id'] . '|' . $a_ci['comment_id'], $a_ignored_items)) {
            continue;
          }
        } else {
          if (in_array('nested_comment|' . $a_ci['owner_id'] . '|' . $a_ci['post_id'] . '|' . $a_ci['parent_comment_id'] . '|' . $a_ci['comment_id'], $a_ignored_items)) {
            continue;
          }
        }

        $a_settlement_data = sms_settlement_fetch_data($a_ci['settlement_id']);
        $need_for_log = false;
        $sms_log_buffer = '';
        $sms_log_buffer .= '********************************************************************************' . PHP_EOL;
        $sms_log_buffer .= '#' . $i_counter . ': ' . base64_decode($a_db_user_data['first_name']) . ' ' . base64_decode($a_db_user_data['last_name']) . PHP_EOL;

        if ($a_settlement_data['district'] != '' ) {
          $sms_log_buffer .= $a_settlement_data['district'] . ', ' . $a_settlement_data['settlement'] . PHP_EOL;
        } else {
          $sms_log_buffer .= $a_settlement_data['settlement'] . PHP_EOL;
        }

        if ($a_ci['parent_comment_id'] == I_NULL_VALUE) {
          $sms_log_buffer .= 'https://vk.com/wall' . $a_ci['owner_id'] . '_' . $a_ci['post_id'] . '?reply=' . $a_ci['comment_id'] . PHP_EOL;
          $sms_log_buffer .= 'comment|' . $a_ci['owner_id'] . '|' . $a_ci['post_id'] . '|' . $a_ci['comment_id'] . PHP_EOL;
          $sms_log_buffer .= 'all_comments_under|' . $a_ci['owner_id'] . '|' . $a_ci['post_id'] . PHP_EOL;
        } else {
          $sms_log_buffer .= 'https://vk.com/wall' . $a_ci['owner_id'] . '_' . $a_ci['post_id'] . '?reply=' . $a_ci['comment_id'] . '&thread=' . $a_ci['parent_comment_id'] . PHP_EOL;
          $sms_log_buffer .= 'nested_comment|' . $a_ci['owner_id'] . '|' . $a_ci['post_id'] . '|' . $a_ci['parent_comment_id'] . '|' . $a_ci['comment_id'] . PHP_EOL;
          $sms_log_buffer .= 'all_comments_under|' . $a_ci['owner_id'] . '|' . $a_ci['post_id'] . PHP_EOL;
        }

        foreach ($a_patterns as $a_pi) {
          $a_matches = [];
          preg_match_all($a_pi, base64_decode($a_ci['text']), $a_matches);

          foreach ($a_matches[0] as $a_mi) {
            $need_for_log = true;
            $sms_log_buffer .= '  Text: ' . $a_mi . PHP_EOL;
          }
        }

        foreach ($a_patterns as $a_pi) {
          $a_matches = [];
          preg_match_all($a_pi, base64_decode($a_ci['attachments']), $a_matches);

          foreach ($a_matches[0] as $a_mi) {
            $need_for_log = true;
            $sms_log_buffer .= '  Attachments: ' . $a_mi . PHP_EOL;
          }
        }

        $sms_log_buffer .= '********************************************************************************';

        if ($need_for_log) {
          sms_log($sms_log_buffer);
          ++$i_counter;
        }
      }
    }
  }
}

function sms_db_delete_obsolete_comments() {
  global $i_timestamp;
  global $o_sqlite;

  $i_current_date_limit = round($i_timestamp - I_DATE_LIMIT_WALL_GETCOMMENTS * 2);

  $o_sqlite->exec("DELETE FROM wall_getcomments WHERE date < $i_current_date_limit");
}

function sms_db_delete_obsolete_posts() {
  global $i_timestamp;
  global $o_sqlite;

  $i_current_date_limit = round($i_timestamp - I_DATE_LIMIT_WALL_GETCOMMENTS * 2);

  $o_sqlite->exec("DELETE FROM wall_get WHERE date < $i_current_date_limit");
}

function sms_db_posts_fetch_comments() {
  global $a_ignored_items;
  global $i_timestamp;
  global $o_sqlite;

  $i_db_data_posts_counter = 0;
  $o_db_data_posts = $o_sqlite->query('SELECT * FROM wall_get');

  while ($a_pi = $o_db_data_posts->fetchArray()) {
    $i_offset = 0;

    ++$i_db_data_posts_counter;
    sms_echo('Processing posts (' . $i_db_data_posts_counter . ' of ' . $o_db_data_posts->numRows() . ')...');

    if (in_array('owner|' . $a_pi['owner_id'], $a_ignored_items)) {
      continue;
    }

    if ($a_pi['date'] < $i_timestamp - I_DATE_LIMIT_WALL_GETCOMMENTS) {
      continue;
    }

    do {
      $b_need_for_break = false;
      $o_result = sms_vk_api_wall_getcomments($a_pi['owner_id'], $a_pi['post_id'], $i_offset, I_NULL_VALUE);

      if ($o_result != null) {
        if (empty($o_result['items'])) {
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
            $s_db_text = base64_encode($o_ri['text']);

            if (array_key_exists('attachments', $o_ri)) {
              $s_db_attachments = base64_encode(serialize($o_ri['attachments']));
            } else {
              $s_db_attachments = base64_encode('');
            }

            $a_db_user_data = sms_user_fetch_data($i_db_from_id);
            $i_db_settlement_id = $a_db_user_data['settlement_id'];

            $o_sqlite->exec("REPLACE INTO wall_getcomments(attachments, settlement_id, comment_id, date, from_id, owner_id, parent_comment_id, post_id, text) VALUES('$s_db_attachments', $i_db_settlement_id, $i_db_comment_id, $i_db_date, $i_db_from_id, $i_db_owner_id, $i_db_parent_comment_id, $i_db_post_id, '$s_db_text')");

            if ($o_ri['thread']['count'] > 0) {
              do {
                $b_need_for_break_nested = false;
                $o_result_nested = sms_vk_api_wall_getcomments($a_pi['owner_id'], $a_pi['post_id'], $i_offset_nested, $i_db_comment_id);

                if ($o_result_nested != null) {
                  if (empty($o_result_nested['items'])) {
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
                      $s_db_text_nested = base64_encode($o_rin['text']);

                      if (array_key_exists('attachments', $o_rin)) {
                        $s_db_attachments_nested = base64_encode(serialize($o_rin['attachments']));
                      } else {
                        $s_db_attachments_nested = base64_encode('');
                      }

                      $a_db_user_data_nested = sms_user_fetch_data($i_db_from_id_nested);
                      $i_db_settlement_id_nested = $a_db_user_data_nested['settlement_id'];

                      if (sms_settlement_check($i_db_settlement_id_nested)) {
                        $o_sqlite->exec("REPLACE INTO wall_getcomments(attachments, settlement_id, comment_id, date, from_id, owner_id, parent_comment_id, post_id, text) VALUES('$s_db_attachments_nested', $i_db_settlement_id_nested, $i_db_comment_id_nested, $i_db_date_nested, $i_db_from_id_nested, $i_db_owner_id_nested, $i_db_parent_comment_id_nested, $i_db_post_id_nested, '$s_db_text_nested')");
                      }
                    }
                  }
                } else {
                  sms_debug('error, wall.getcomments, nested, https://vk.com/wall' . $i_db_owner_id . '_' . $i_db_post_id . '?reply=' . $i_db_comment_id);
                  break;
                }

                $i_offset_nested += I_VK_API_WALL_GETCOMMENTS_COUNT_DEFAULT;
              } while (!$b_need_for_break_nested);
            }
          }
        }
      } else {
        sms_debug('error, wall.getcomments, https://vk.com/wall' . $a_pi['owner_id'] . '_' . $a_pi['post_id']);
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

function sms_settlement_fetch_data($i_settlement_id) {
  global $a_settlements;

  $a_settlement_data = [];

  foreach ($a_settlements['items'] as $a_si) {
    if ($a_si['id'] == $i_settlement_id) {
      if (array_key_exists('area', $a_si)) {
        $a_settlement_data['district'] = $a_si['area'];
      } else {
        $a_settlement_data['district'] = '';
      }

      $a_settlement_data['settlement'] = $a_si['title'];

      return $a_settlement_data;
    }
  }
}

function sms_shutdown() {
  global $r_log_file;

  fclose($r_log_file);
  sms_echo('SMS stopped.');
}

function sms_user_fetch_data($i_user_id) {
  global $o_sqlite;

  $a_db_data_settlements = $o_sqlite->query("SELECT * FROM users WHERE user_id = $i_user_id");
  $a_db_user_data = [];
  $b_need_for_commit = false;
  $i_settlement_id = I_NULL_VALUE;
  $s_first_name = '';
  $s_last_name = '';

  while ($a_si = $a_db_data_settlements->fetchArray()) {
    if (array_key_exists('settlement_id', $a_si)) {
      $a_db_user_data['first_name'] = $a_si['first_name'];
      $a_db_user_data['last_name'] = $a_si['last_name'];
      $a_db_user_data['settlement_id'] = $a_si['settlement_id'];

      return $a_db_user_data;
    }
  }

  if ($i_user_id > 0) {
    $o_result = sms_vk_api_user_get($i_user_id, 'city');

    if ($o_result != null) {
      $b_need_for_commit = true;
      $s_first_name = base64_encode($o_result[0]['first_name']);
      $s_last_name = base64_encode($o_result[0]['last_name']);

      if (array_key_exists('city', $o_result[0])) {
        $i_settlement_id =  $o_result[0]['city']['id'];
      }
    }
  } else {
    $b_need_for_commit = true;
  }

  if ($b_need_for_commit) {
    $o_sqlite->exec("REPLACE INTO users(first_name, last_name, settlement_id, user_id) VALUES('$s_first_name', '$s_last_name', $i_settlement_id, $i_user_id)");
  }

  $a_db_user_data['first_name'] = $s_first_name;
  $a_db_user_data['last_name'] = $s_last_name;
  $a_db_user_data['settlement_id'] = $i_settlement_id;

  return $a_db_user_data;
}

function sms_vk_api_user_get($i_user_id, $s_fields) {
  global $o_vk_api_client;
  global $s_vk_api_token;

  if ($i_user_id < 0) {
    return null;
  }

  usleep(I_USLEEP_TIME);
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

  usleep(I_USLEEP_TIME);
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

  usleep(I_USLEEP_TIME);
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
  $i_watched_owners_counter = 0;

  foreach ($a_watched_owners as $s_wo) {
    $i_offset = 0;

    ++$i_watched_owners_counter;
    sms_echo('Processing walls (' . $i_watched_owners_counter . ' of ' . count($a_watched_owners) . ')...');

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
            $s_db_text = base64_encode($o_ri['text']);

            if (array_key_exists('attachments', $o_ri)) {
              $s_db_attachments = base64_encode(serialize($o_ri['attachments']));
            } else {
              $s_db_attachments = base64_encode('');
            }

            $a_db_user_data = sms_user_fetch_data($i_db_from_id);
            $i_db_settlement_id = $a_db_user_data['settlement_id'];

            $o_sqlite->exec("REPLACE INTO wall_get(attachments, settlement_id, date, from_id, owner_id, post_id, text) VALUES('$s_db_attachments', $i_db_settlement_id, $i_db_date, $i_db_from_id, $i_db_owner_id, $i_db_post_id, '$s_db_text')");
          }
        }
      } else {
        sms_debug('error, wall.get, https://vk.com/wall' . $s_wo . '?own=1&offset=' . $i_offset);
        break;
      }

      $i_offset += I_VK_API_WALL_GET_COUNT_DEFAULT;

      if ($b_need_for_break || empty($o_result['items']) || count($o_result['items']) == 1 || $i_timestamp > $o_result['items'][1]['date'] + I_DATE_LIMIT_WALL_GET) {
        break;
      }
    } while (true);
  }
}

$a_ignored_items = file('private/ignored_items.txt', FILE_IGNORE_NEW_LINES);
$a_patterns = file('private/patterns.txt', FILE_IGNORE_NEW_LINES);
$a_settlements = json_decode(file_get_contents('data/settlements.json'), true);
$o_sqlite = new SQLite3('data/sms_db.sqlite');
$o_vk_api_client = new VK\Client\VKApiClient();
$r_log_file = fopen('data/log.txt', 'w');
$s_vk_api_token = trim(file_get_contents('private/vk_api_token.txt'));

date_default_timezone_set('Europe/Moscow');
$i_timestamp = time();
register_shutdown_function('sms_shutdown');
sms_echo('SMS started.');
