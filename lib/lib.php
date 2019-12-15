<?php
require __DIR__ . '/../vendor/autoload.php';

const B_DEBUG_ENABLED = true;
const I_DATE_LIMIT_WALL_GET = 60 * 60 * 24 * 9;
const I_DATE_LIMIT_WALL_GETCOMMENTS = 60 * 60 * 24 * 9;
const I_MAX_LINE_SIZE = 80;
const I_NULL_VALUE = -1;
const I_USLEEP_TIME = 340 * 1000;
const I_VK_API_WALL_GETCOMMENTS_COUNT_DEFAULT = 100;
const I_VK_API_WALL_GETCOMMENTS_THREAD_ITEMS_COUNT_DEFAULT = 10;
const I_VK_API_WALL_GET_COUNT_DEFAULT = 100;
const S_DEFAULT_SETTLEMENT_TITLE = 'Возможно, проживает в Воронеже';
const S_TERMINAL_CYAN = "\e[96m";
const S_TERMINAL_GREEN = "\e[92m";
const S_TERMINAL_RED = "\e[91m";
const S_TERMINAL_RESET = "\e[0m";
const S_TERMINAL_YELLOW = "\e[93m";

function sms_data_parse_from_id_enforced() {
  global $a_default_settlement_enforced;
  global $a_watched_owners;

  $a_result = [];

  foreach ($a_watched_owners as $s_wi) {
    if ($s_wi > 0) {
      array_push($a_result, $s_wi);
    }
  }

  foreach ($a_default_settlement_enforced as $s_dsi) {
    if (!in_array($s_dsi, $a_watched_owners)) {
      array_push($a_result, $s_dsi);
    }
  }

  return $a_result;
}

function sms_db_analyze_data_wall_get() {
  global $a_default_settlement_enforced;
  global $a_ignored_items;
  global $a_owner_id_enforced;
  global $a_patterns;
  global $b_need_to_print_first_line;
  global $o_sqlite;
  global $s_date_label;

  $a_db_data_posts = $o_sqlite->query('SELECT * FROM wall_get ORDER BY from_id');
  $i_counter = 1;

  while ($a_pi = $a_db_data_posts->fetchArray()) {
    if (sms_settlement_check($a_pi['settlement_id']) || in_array($a_pi['from_id'], $a_default_settlement_enforced)) {
      if ($a_pi['from_id'] > 0) {
        if (in_array('owner_id|' . $a_pi['owner_id'], $a_ignored_items)) {
          continue;
        }

        if (in_array('from_id|' . $a_pi['from_id'], $a_ignored_items)) {
          continue;
        }

        $b_need_for_continue = false;

        foreach ($a_ignored_items as $a_ii) {
          $a_matches = [];

          if (preg_match('/^all_from_with_fragment\|' . $a_pi['from_id'] . '\|(.+)$/iu', $a_ii, $a_matches)) {
            if (preg_match('/' . $a_matches[1] . '/iu', base64_decode($a_pi['text']))) {
              $b_need_for_continue = true;
              break;
            }

            if (preg_match('/' . $a_matches[1] . '/iu', base64_decode($a_pi['attachments']))) {
              $b_need_for_continue = true;
              break;
            }
          }
        }

        if ($b_need_for_continue) {
          continue;
        }

        if (in_array('post|' . $a_pi['owner_id'] . '|' . $a_pi['post_id'], $a_ignored_items)) {
          continue;
        }

        $a_db_user_data = sms_user_fetch_data($a_pi['from_id']);

        if (!in_array($a_pi['from_id'], $a_default_settlement_enforced)) {
          $a_settlement_data = sms_settlement_fetch_data($a_pi['settlement_id']);
        }

        $b_need_for_log = false;
        $sms_log_buffer = '';
        $sms_log_buffer .= 'Post #' . $i_counter . PHP_EOL;
        $sms_log_buffer .= base64_decode($a_db_user_data['first_name']) . ' ' . base64_decode($a_db_user_data['last_name']) . ', https://vk.com/id' . $a_pi['from_id'] . PHP_EOL;

        if (in_array($a_pi['from_id'], $a_default_settlement_enforced)) {
          $sms_log_buffer .= S_TERMINAL_YELLOW . S_DEFAULT_SETTLEMENT_TITLE . S_TERMINAL_RESET . PHP_EOL;
        } else {
          if ($a_settlement_data['district'] != '' ) {
            $sms_log_buffer .= $a_settlement_data['district'] . ', ' . $a_settlement_data['settlement'] . PHP_EOL;
          } else {
            $sms_log_buffer .= $a_settlement_data['settlement'] . PHP_EOL;
          }
        }

        $sms_log_buffer .= 'https://vk.com/wall' . $a_pi['owner_id'] . '_' . $a_pi['post_id'] . PHP_EOL;
        $sms_log_buffer .= $s_date_label . 'post|' . $a_pi['owner_id'] . '|' . $a_pi['post_id'] . PHP_EOL;
        $sms_log_buffer .= $s_date_label . 'all_from_with_fragment|' . $a_pi['from_id'] . '|...' . PHP_EOL;
        $sms_log_buffer .= $s_date_label . 'from_id|' . $a_pi['from_id'] . PHP_EOL;
        $sms_log_buffer .= $s_date_label . 'owner_id|' . $a_pi['owner_id'] . PHP_EOL;

        if (in_array($a_pi['owner_id'], $a_owner_id_enforced)) {
          $b_need_for_log = true;
          $sms_log_buffer .= '  ' . S_TERMINAL_CYAN . 'ENFORCED (OWNER_ID)' . S_TERMINAL_RESET . PHP_EOL;
        }

        foreach ($a_patterns as $a_pattern) {
          $a_matches = [];
          preg_match_all($a_pattern, base64_decode($a_pi['text']), $a_matches);

          foreach ($a_matches[0] as $a_mi) {
            $b_need_for_log = true;
            $sms_log_buffer .= '  IN TEXT: ' . S_TERMINAL_GREEN . sms_php_mb_trim($a_mi) . S_TERMINAL_RESET . PHP_EOL;
          }
        }

        foreach ($a_patterns as $a_pattern) {
          $a_matches = [];
          preg_match_all($a_pattern, base64_decode($a_pi['attachments']), $a_matches);

          foreach ($a_matches[0] as $a_mi) {
            $b_need_for_log = true;
            $sms_log_buffer .= '  IN ATTACHMENTS: ' . S_TERMINAL_GREEN . sms_php_mb_trim($a_mi) . S_TERMINAL_RESET . PHP_EOL;
          }
        }

        if (base64_decode($a_pi['attachments']) == '') {
          $sms_log_buffer .= '  TEXT WITHOUT ATTACHMENTS:' . PHP_EOL;
          $sms_log_buffer .= S_TERMINAL_GREEN . sms_print_output_multiline(base64_decode($a_pi['text'])) . S_TERMINAL_RESET;
        }

        $sms_log_buffer .= S_TERMINAL_RED .  sms_print_repeat('*', I_MAX_LINE_SIZE) . S_TERMINAL_RESET;

        if ($b_need_for_log) {
          if (!$b_need_to_print_first_line) {
            $b_need_to_print_first_line = true;
            sms_log(S_TERMINAL_RED .  sms_print_repeat('*', I_MAX_LINE_SIZE) . S_TERMINAL_RESET);
          }

          sms_log($sms_log_buffer);
          ++$i_counter;
        }
      }
    }
  }
}

function sms_db_analyze_data_wall_getcomments() {
  global $a_default_settlement_enforced;
  global $a_from_id_enforced;
  global $a_ignored_items;
  global $a_owner_id_enforced;
  global $a_patterns;
  global $b_need_to_print_first_line;
  global $o_sqlite;
  global $s_date_label;

  $a_db_data_comments = $o_sqlite->query('SELECT * FROM wall_getcomments ORDER BY from_id');
  $i_counter = 1;

  while ($a_ci = $a_db_data_comments->fetchArray()) {
    if (sms_settlement_check($a_ci['settlement_id']) || in_array($a_ci['from_id'], $a_default_settlement_enforced)) {
      if ($a_ci['from_id'] > 0) {
        $b_from_id_enforced = false;

        if (in_array($a_ci['from_id'], $a_from_id_enforced)) {
          $b_from_id_enforced = true;
        }

        if (in_array('owner_id|' . $a_ci['owner_id'], $a_ignored_items)) {
          continue;
        }

        if (in_array('from_id|' . $a_ci['from_id'], $a_ignored_items)) {
          continue;
        }

        $b_need_for_continue = false;

        foreach ($a_ignored_items as $a_ii) {
          $a_matches = [];

          if (preg_match('/^all_from_with_fragment\|' . $a_ci['from_id'] . '\|(.+)$/iu', $a_ii, $a_matches)) {
            if (preg_match('/' . $a_matches[1] . '/iu', base64_decode($a_ci['text']))) {
              $b_need_for_continue = true;
              break;
            }

            if (preg_match('/' . $a_matches[1] . '/iu', base64_decode($a_ci['attachments']))) {
              $b_need_for_continue = true;
              break;
            }
          }
        }

        if ($b_need_for_continue) {
          continue;
        }

        if (in_array('all_comments_under|' . $a_ci['owner_id'] . '|' . $a_ci['post_id'], $a_ignored_items)) {
          if (!$b_from_id_enforced) {
            continue;
          }
        }

        if (in_array('all_comments_from_under|' . $a_ci['from_id'] . '|' . $a_ci['owner_id'] . '|' . $a_ci['post_id'], $a_ignored_items)) {
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

        $a_db_user_data = sms_user_fetch_data($a_ci['from_id']);

        if (!in_array($a_ci['from_id'], $a_default_settlement_enforced)) {
          $a_settlement_data = sms_settlement_fetch_data($a_ci['settlement_id']);
        }

        $b_need_for_log = false;
        $sms_log_buffer = '';
        $sms_log_buffer .= 'Comment #' . $i_counter . PHP_EOL;
        $sms_log_buffer .= base64_decode($a_db_user_data['first_name']) . ' ' . base64_decode($a_db_user_data['last_name']) . ', https://vk.com/id' . $a_ci['from_id'] . PHP_EOL;

        if (in_array($a_ci['from_id'], $a_default_settlement_enforced)) {
          $sms_log_buffer .= S_TERMINAL_YELLOW . S_DEFAULT_SETTLEMENT_TITLE . S_TERMINAL_RESET . PHP_EOL;
        } else {
          if ($a_settlement_data['district'] != '' ) {
            $sms_log_buffer .= $a_settlement_data['district'] . ', ' . $a_settlement_data['settlement'] . PHP_EOL;
          } else {
            $sms_log_buffer .= $a_settlement_data['settlement'] . PHP_EOL;
          }
        }

        if ($a_ci['parent_comment_id'] == I_NULL_VALUE) {
          $sms_log_buffer .= 'https://vk.com/wall' . $a_ci['owner_id'] . '_' . $a_ci['post_id'] . '?reply=' . $a_ci['comment_id'] . PHP_EOL;
          $sms_log_buffer .= $s_date_label . 'comment|' . $a_ci['owner_id'] . '|' . $a_ci['post_id'] . '|' . $a_ci['comment_id'] . PHP_EOL;
        } else {
          $sms_log_buffer .= 'https://vk.com/wall' . $a_ci['owner_id'] . '_' . $a_ci['post_id'] . '?reply=' . $a_ci['comment_id'] . '&thread=' . $a_ci['parent_comment_id'] . PHP_EOL;
          $sms_log_buffer .= $s_date_label . 'nested_comment|' . $a_ci['owner_id'] . '|' . $a_ci['post_id'] . '|' . $a_ci['parent_comment_id'] . '|' . $a_ci['comment_id'] . PHP_EOL;
        }

        $sms_log_buffer .= $s_date_label . 'all_comments_from_under|' . $a_ci['from_id'] . '|' . $a_ci['owner_id'] . '|' . $a_ci['post_id'] . PHP_EOL;
        $sms_log_buffer .= $s_date_label . 'all_comments_under|' . $a_ci['owner_id'] . '|' . $a_ci['post_id'] . PHP_EOL;
        $sms_log_buffer .= $s_date_label . 'all_from_with_fragment|' . $a_ci['from_id'] . '|...' . PHP_EOL;
        $sms_log_buffer .= $s_date_label . 'from_id|' . $a_ci['from_id'] . PHP_EOL;
        $sms_log_buffer .= $s_date_label . 'owner_id|' . $a_ci['owner_id'] . PHP_EOL;

        if ($b_from_id_enforced) {
          $b_need_for_log = true;
          $sms_log_buffer .= '  ' . S_TERMINAL_YELLOW . 'ENFORCED (FROM_ID)' . S_TERMINAL_RESET . PHP_EOL;
        }

        if (in_array($a_ci['owner_id'], $a_owner_id_enforced)) {
          $b_need_for_log = true;
          $sms_log_buffer .= '  ' . S_TERMINAL_CYAN . 'ENFORCED (OWNER_ID)' . S_TERMINAL_RESET . PHP_EOL;
        }

        foreach ($a_patterns as $a_pi) {
          $a_matches = [];
          preg_match_all($a_pi, base64_decode($a_ci['text']), $a_matches);

          foreach ($a_matches[0] as $a_mi) {
            $b_need_for_log = true;
            $sms_log_buffer .= '  IN TEXT: ' . S_TERMINAL_GREEN . sms_php_mb_trim($a_mi) . S_TERMINAL_RESET . PHP_EOL;
          }
        }

        foreach ($a_patterns as $a_pi) {
          $a_matches = [];
          preg_match_all($a_pi, base64_decode($a_ci['attachments']), $a_matches);

          foreach ($a_matches[0] as $a_mi) {
            $b_need_for_log = true;
            $sms_log_buffer .= '  IN ATTACHMENTS: ' . S_TERMINAL_GREEN . sms_php_mb_trim($a_mi) . S_TERMINAL_RESET . PHP_EOL;
          }
        }

        if (base64_decode($a_ci['attachments']) == '') {
          $sms_log_buffer .= '  TEXT WITHOUT ATTACHMENTS:' . PHP_EOL;
          $sms_log_buffer .= S_TERMINAL_GREEN . sms_print_output_multiline(base64_decode($a_ci['text'])) . S_TERMINAL_RESET;
        }

        $sms_log_buffer .= S_TERMINAL_RED .  sms_print_repeat('*', I_MAX_LINE_SIZE) . S_TERMINAL_RESET;

        if ($b_need_for_log) {
          if (!$b_need_to_print_first_line) {
            $b_need_to_print_first_line = true;
            sms_log(S_TERMINAL_RED .  sms_print_repeat('*', I_MAX_LINE_SIZE) . S_TERMINAL_RESET);
          }

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

  $i_current_date_limit = $i_timestamp - I_DATE_LIMIT_WALL_GETCOMMENTS;

  $o_sqlite->exec("DELETE FROM wall_getcomments WHERE date < $i_current_date_limit");
}

function sms_db_delete_obsolete_posts() {
  global $i_timestamp;
  global $o_sqlite;

  $i_current_date_limit = $i_timestamp - I_DATE_LIMIT_WALL_GET;

  $o_sqlite->exec("DELETE FROM wall_get WHERE date < $i_current_date_limit");
}

function sms_db_get_rows_count($o_query_result) {
  $i_rows_counter = 0;

  while ($a_qi = $o_query_result->fetchArray()) {
    ++$i_rows_counter;
  }

  return $i_rows_counter;
}

function sms_db_perform_backup() {
  copy('data/sms_db.sqlite_backup_2', 'data/sms_db.sqlite_backup_3');
  copy('data/sms_db.sqlite_backup_1', 'data/sms_db.sqlite_backup_2');
  copy('data/sms_db.sqlite', 'data/sms_db.sqlite_backup_1');
}

function sms_db_posts_fetch_comments() {
  global $a_ignored_items;
  global $i_timestamp;
  global $o_sqlite;

  $i_db_data_posts_counter = 0;
  $o_db_data_rows = $o_sqlite->query('SELECT * FROM wall_get WHERE comments_are_committed = 0');
  $i_db_data_rows_count = sms_db_get_rows_count($o_db_data_rows);
  $o_db_data_posts = $o_sqlite->query('SELECT * FROM wall_get WHERE comments_are_committed = 0');

  while ($a_pi = $o_db_data_posts->fetchArray()) {
    $b_comments_are_committed = true;
    $i_offset = 0;
    $i_pi_date = $a_pi['date'];
    $i_pi_from_id = $a_pi['from_id'];
    $i_pi_owner_id = $a_pi['owner_id'];
    $i_pi_post_id = $a_pi['post_id'];
    $i_pi_settlement_id = $a_pi['settlement_id'];
    $s_pi_attachments = $a_pi['attachments'];
    $s_pi_text = $a_pi['text'];

    ++$i_db_data_posts_counter;
    sms_echo('Processing posts (' . $i_db_data_posts_counter . ' of ' . $i_db_data_rows_count . ')...');

    if (in_array('owner|' . $a_pi['owner_id'], $a_ignored_items)) {
      sms_echo('Skipping...');

      continue;
    }

    do {
      $b_need_for_break = false;
      $o_result = sms_vk_api_wall_getcomments($a_pi['owner_id'], $a_pi['post_id'], $i_offset, I_NULL_VALUE);

      if ($o_result != null) {
        if (empty($o_result['items']) || count($o_result['items']) < I_VK_API_WALL_GETCOMMENTS_COUNT_DEFAULT) {
          $b_need_for_break = true;
        }

        foreach ($o_result['items'] as $o_ri) {
          $i_db_comment_id = $o_ri['id'];
          $i_db_parent_comment_id = I_NULL_VALUE;
          $i_offset_nested = I_VK_API_WALL_GETCOMMENTS_THREAD_ITEMS_COUNT_DEFAULT;

          if (!array_key_exists('deleted', $o_ri)) {
            $i_db_date = $o_ri['date'];
            $i_db_from_id = $o_ri['from_id'];
            $i_db_owner_id = $o_ri['owner_id'];
            $i_db_post_id = $o_ri['post_id'];
            $s_db_text = base64_encode($o_ri['text']);

            if (array_key_exists('attachments', $o_ri)) {
              $s_db_attachments = base64_encode(serialize($o_ri['attachments']));
            } else {
              $s_db_attachments = base64_encode('');
            }

            $a_db_user_data = sms_user_fetch_data($i_db_from_id);
            $i_db_settlement_id = $a_db_user_data['settlement_id'];

            $o_sqlite->exec("REPLACE INTO wall_getcomments(attachments, settlement_id, comment_id, date, from_id, owner_id, parent_comment_id, post_id, text) VALUES('$s_db_attachments', $i_db_settlement_id, $i_db_comment_id, $i_db_date, $i_db_from_id, $i_db_owner_id, $i_db_parent_comment_id, $i_db_post_id, '$s_db_text')");
          }

          if ($o_ri['thread']['count'] > 0) {
            foreach ($o_ri['thread']['items'] as $o_riti) {
              $i_db_comment_id_riti = $o_riti['id'];
              $i_db_date_riti = $o_riti['date'];
              $i_db_from_id_riti = $o_riti['from_id'];
              $i_db_owner_id_riti = $o_riti['owner_id'];
              $i_db_parent_comment_id_riti = $i_db_comment_id;
              $i_db_post_id_riti = $o_riti['post_id'];
              $s_db_text_riti = base64_encode($o_riti['text']);

              if (array_key_exists('attachments', $o_riti)) {
                $s_db_attachments_riti = base64_encode(serialize($o_riti['attachments']));
              } else {
                $s_db_attachments_riti = base64_encode('');
              }

              $a_db_user_data_riti = sms_user_fetch_data($i_db_from_id_riti);
              $i_db_settlement_id_riti = $a_db_user_data_riti['settlement_id'];

              if (sms_settlement_check($i_db_settlement_id_riti)) {
                $o_sqlite->exec("REPLACE INTO wall_getcomments(attachments, settlement_id, comment_id, date, from_id, owner_id, parent_comment_id, post_id, text) VALUES('$s_db_attachments_riti', $i_db_settlement_id_riti, $i_db_comment_id_riti, $i_db_date_riti, $i_db_from_id_riti, $i_db_owner_id_riti, $i_db_parent_comment_id_riti, $i_db_post_id_riti, '$s_db_text_riti')");
              }
            }
          }

          if ($o_ri['thread']['count'] > I_VK_API_WALL_GETCOMMENTS_THREAD_ITEMS_COUNT_DEFAULT) {
            do {
              $b_need_for_break_nested = false;
              $o_result_nested = sms_vk_api_wall_getcomments($a_pi['owner_id'], $a_pi['post_id'], $i_offset_nested, $i_db_comment_id);

              if ($o_result_nested != null) {
                if (empty($o_result_nested['items']) || count($o_result_nested['items']) < I_VK_API_WALL_GETCOMMENTS_COUNT_DEFAULT) {
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
                $b_comments_are_committed = false;
                sms_debug('error, wall.getcomments, nested');

                break;
              }

              $i_offset_nested += I_VK_API_WALL_GETCOMMENTS_COUNT_DEFAULT;
            } while (!$b_need_for_break_nested);
          }
        }
      } else {
        $b_comments_are_committed = false;
        sms_debug('error, wall.getcomments, https://vk.com/wall' . $a_pi['owner_id'] . '_' . $a_pi['post_id']);

        break;
      }

      $i_offset += I_VK_API_WALL_GETCOMMENTS_COUNT_DEFAULT;
    } while (!$b_need_for_break);

    if ($b_comments_are_committed) {
      $o_sqlite->exec("REPLACE INTO wall_get(attachments, comments_are_committed, settlement_id, date, from_id, owner_id, post_id, text) VALUES('$s_pi_attachments', 1, $i_pi_settlement_id, $i_pi_date, $i_pi_from_id, $i_pi_owner_id, $i_pi_post_id, '$s_pi_text')");
    }
  }
}

function sms_db_vacuum() {
  global $o_sqlite;

  $o_sqlite->exec("VACUUM");
}

function sms_debug($s_message) {
  if (B_DEBUG_ENABLED) {
    echo '  ' . $s_message . PHP_EOL;
  }
}

function sms_echo($s_message) {
  echo $s_message . PHP_EOL;
}

function sms_fs_parse_ignored_items() {
  global $s_date_label;

  $a_ignored_items = file('private/ignored_items.txt', FILE_IGNORE_NEW_LINES);
  $a_result = [];

  foreach ($a_ignored_items as $s_ii) {
    array_push($a_result, mb_substr($s_ii, mb_strlen($s_date_label)));
  }

  return $a_result;
}

function sms_log($s_message) {
  global $r_log_file;

  fwrite($r_log_file, $s_message . PHP_EOL);
  sms_echo($s_message);
}

function sms_php_mb_trim($s_string) {
  return preg_replace('/(\n)|(\r)/u', ' ', preg_replace('/(^\s+)|(\s+$)/u', '', $s_string));
}

function sms_print_output_multiline($s_output) {
  $s_result = '';

  for ($i = 0; $i < mb_strlen($s_output); $i += I_MAX_LINE_SIZE - mb_strlen('  ')) {
    $s_result .= '  ' . sms_php_mb_trim(mb_substr($s_output, $i, I_MAX_LINE_SIZE - mb_strlen('  '))) . PHP_EOL;
  }

  return $s_result;
}

function sms_print_repeat($s_fragment, $i_count) {
  $s_result = '';

  for ($i = 0; $i < $i_count ; ++$i) {
    $s_result .= $s_fragment;
  }

  return $s_result;
}

function sms_settlement_check($i_settlement_id) {
  global $a_settlements;

  if($i_settlement_id < 0) {
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
  sms_debug('https://vk.com/id' . $i_user_id);

  try {
    $o_response = $o_vk_api_client->users()->get($s_vk_api_token, [
      'fields' => $s_fields,
      'user_ids' => $i_user_id,
    ]);

    sms_debug('done');

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
  sms_debug('https://vk.com/wall' . $i_owner_id . '?own=1');

  try {
    $o_response = $o_vk_api_client->wall()->get($s_vk_api_token, array(
      'count' => I_VK_API_WALL_GET_COUNT_DEFAULT,
      'offset' => $i_offset,
      'owner_id' => $i_owner_id,
    ));

    sms_debug('done');

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

  if ($i_comment_id == I_NULL_VALUE) {
    sms_debug('https://vk.com/wall' . $i_owner_id . '_' . $i_post_id);
  } else {
    sms_debug('https://vk.com/wall' . $i_owner_id . '_' . $i_post_id . '?reply=' . $i_comment_id);
  }

  $a_getcomments = [
    'count' => I_VK_API_WALL_GETCOMMENTS_COUNT_DEFAULT,
    'offset' => $i_offset,
    'owner_id' => $i_owner_id,
    'post_id' => $i_post_id,
    'preview_length' => 0,
    'sort' => 'asc',
    'thread_items_count' => I_VK_API_WALL_GETCOMMENTS_THREAD_ITEMS_COUNT_DEFAULT,
  ];

  if ($i_comment_id != I_NULL_VALUE) {
    $a_getcomments['comment_id'] = $i_comment_id;
  }

  try {
    $o_response = $o_vk_api_client->wall()->getComments($s_vk_api_token, $a_getcomments);
    sms_debug('done');

    return $o_response;
  } catch (Exception $e) {
    return null;
  }
}

function sms_watched_owners_wall_get() {
  global $a_watched_owners;
  global $i_timestamp;
  global $o_sqlite;

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
          $i_db_date = $o_ri['date'];
          $i_db_from_id = $o_ri['from_id'];
          $i_db_owner_id = $o_ri['owner_id'];
          $i_db_post_id = $o_ri['id'];
          $s_db_text = base64_encode($o_ri['text']);

          if (array_key_exists('attachments', $o_ri)) {
            $s_db_attachments = base64_encode(serialize($o_ri['attachments']));
          } else {
            $s_db_attachments = base64_encode('');
          }

          if (array_key_exists('is_pinned', $o_ri)) {
            if ($i_timestamp > $i_db_date + I_DATE_LIMIT_WALL_GET) {
              continue;
            }
          } else {
            if ($o_sqlite->querySingle("SELECT * FROM wall_get WHERE owner_id = $i_db_owner_id AND post_id = $i_db_post_id", true) != null) {
              continue;
            }

            if ($i_timestamp > $i_db_date + I_DATE_LIMIT_WALL_GET) {
              $b_need_for_break = true;

              break;
            }
          }

          $a_db_user_data = sms_user_fetch_data($i_db_from_id);
          $i_db_settlement_id = $a_db_user_data['settlement_id'];

          $o_sqlite->exec("REPLACE INTO wall_get(attachments, comments_are_committed, settlement_id, date, from_id, owner_id, post_id, text) VALUES('$s_db_attachments', 0, $i_db_settlement_id, $i_db_date, $i_db_from_id, $i_db_owner_id, $i_db_post_id, '$s_db_text')");
        }
      } else {
        sms_debug('error, wall.get, ' . $i_offset . ', https://vk.com/wall' . $s_wo . '?own=1');

        break;
      }

      $i_offset += I_VK_API_WALL_GET_COUNT_DEFAULT;

      if ($b_need_for_break || empty($o_result['items']) || count($o_result['items']) == 1 || $i_timestamp > $o_result['items'][1]['date'] + I_DATE_LIMIT_WALL_GET) {
        break;
      }
    } while (true);
  }
}

$a_default_settlement_enforced = file('private/default_settlement_enforced.txt', FILE_IGNORE_NEW_LINES);
$a_owner_id_enforced = file('private/owner_id_enforced.txt', FILE_IGNORE_NEW_LINES);
$a_patterns = file('private/patterns.txt', FILE_IGNORE_NEW_LINES);
$a_settlements = json_decode(file_get_contents('data/settlements.json'), true);
$a_watched_owners = file('private/watched_owners.txt', FILE_IGNORE_NEW_LINES);
$b_need_to_print_first_line = false;
$o_sqlite = new SQLite3('data/sms_db.sqlite');
$o_vk_api_client = new VK\Client\VKApiClient();
$r_log_file = fopen('data/log.txt', 'w');
$s_vk_api_token = trim(file_get_contents('private/vk_api_token.txt'));

date_default_timezone_set('Europe/Moscow');
$i_timestamp = time();
$s_date_label = date('y_W|');
$a_from_id_enforced = sms_data_parse_from_id_enforced();
$a_ignored_items = sms_fs_parse_ignored_items();
register_shutdown_function('sms_shutdown');
sms_echo('SMS started.');
