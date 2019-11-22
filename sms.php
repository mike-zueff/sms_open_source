#!/usr/bin/env php
<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/lib/lib.php';

$a_getopt = getopt('a');

if (!array_key_exists('a', $a_getopt)) {
  sms_db_delete_obsolete_posts();
  sms_db_delete_obsolete_comments();
  sms_watched_owners_wall_get();
  sms_db_posts_fetch_comments();
  sms_db_vacuum();
}

sms_db_analyze_data_wall_get();
sms_db_analyze_data_wall_getcomments();
