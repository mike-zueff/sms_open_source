#!/usr/bin/env php
<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/lib/lib.php';

$a_getopt_f = getopt('f');
$a_getopt_r = getopt('r');

if (array_key_exists('f', $a_getopt_f)) {
  sms_db_delete_obsolete_posts();
  sms_db_delete_obsolete_comments();
  sms_db_delete_obsolete_photos_comments();
  sms_db_delete_obsolete_videos_comments();
  sms_watched_owners_wall_get();
  sms_db_posts_fetch_comments();
  sms_db_vacuum();
  sms_db_perform_backup();
} else if (array_key_exists('r', $a_getopt_r)) {
  sms_db_vacuum();
} else {
  sms_db_analyze_data_wall_get();
  sms_db_analyze_data_wall_getcomments();
  sms_db_analyze_data_wall_get_photos_comments();
  sms_db_analyze_data_wall_get_videos_comments();
}
