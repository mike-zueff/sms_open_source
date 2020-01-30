#!/usr/bin/env php
<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/lib/lib.php';

$a_getopt_a = getopt('a');
$a_getopt_c = getopt('c');
$a_getopt_d = getopt('d:');
$a_getopt_f = getopt('f');
$a_getopt_r = getopt('r');
$s_opt_mode = '';

if (array_key_exists('a', $a_getopt_a)) {
  $s_opt_mode = 'a';
}

if (array_key_exists('c', $a_getopt_c)) {
  $s_opt_mode = 'c';
}

if (array_key_exists('d', $a_getopt_d)) {
  $s_opt_mode = 'd';
}

if (array_key_exists('f', $a_getopt_f)) {
  $s_opt_mode = 'f';
}

if (array_key_exists('r', $a_getopt_r)) {
  $s_opt_mode = 'r';
}

switch ($s_opt_mode) {
case 'a':
  sms_db_prepare_unused_owners_analysis();
  sms_db_analyze_data_wall_get();
  sms_db_analyze_data_wall_getcomments();
  sms_db_analyze_data_wall_get_photos_comments();
  sms_db_analyze_data_wall_get_videos_comments();
  sms_db_perform_unused_owners_analysis();

  break;
case 'c':
  sms_db_vacuum();
  sms_db_posts_fetch_comments();
  sms_db_vacuum();
  sms_db_perform_backup();

  break;
case 'd':
  sms_db_remove_user($a_getopt_d['d']);

  break;
case 'f':
  sms_db_vacuum();
  sms_db_delete_obsolete_posts();
  sms_db_delete_obsolete_comments();
  sms_db_delete_obsolete_photos_comments();
  sms_db_delete_obsolete_videos_comments();
  sms_watched_owners_wall_get();
  sms_enforced_users_newsfeed_getmentions();
  sms_db_posts_fetch_comments();
  sms_db_vacuum();
  sms_db_perform_backup();

  break;
case 'r':
  sms_db_vacuum();

  break;
default:
  sms_db_analyze_data_wall_get();
  sms_db_analyze_data_wall_getcomments();
  sms_db_analyze_data_wall_get_photos_comments();
  sms_db_analyze_data_wall_get_videos_comments();

  break;
}
