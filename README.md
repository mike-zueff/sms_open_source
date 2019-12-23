# sms_open_source (Social Media Sieve)
Big data analytics for social media

## Tools and technologies
- PHP
- Composer
- SQLite
- VK API

## Social media

### Currently supported social networks
- VK

### Roadmap
- Facebook
- LiveJournal

## Usage

### Initial steps
```
git clone https://github.com/mike-zueff/sms_open_source.git
cd sms_open_source
cat data/init.sql | sqlite3 data/sms_db.sqlite
composer require vkcom/vk-php-sdk
touch data/sms_db.sqlite_backup_{1,2,3}
mkdir private
echo "TOKEN" > private/vk_api_token.txt
touch private/complaints.txt

echo "-GROUP_1" > private/owner_id_common.txt
echo "-GROUP_2" >> private/owner_id_common.txt
echo "-GROUP_3" >> private/owner_id_common.txt

echo "USER_1" >> private/owner_id_common.txt
echo "USER_2" >> private/owner_id_common.txt
echo "USER_3" >> private/owner_id_common.txt

echo "-GROUP_1" > private/owner_id_enforced.txt
echo "-GROUP_2" >> private/owner_id_enforced.txt
echo "-GROUP_3" >> private/owner_id_enforced.txt

echo "USER_1" >> private/owner_id_enforced.txt
echo "USER_2" >> private/owner_id_enforced.txt
echo "USER_3" >> private/owner_id_enforced.txt

echo "USER_1" > private/default_settlement_enforced.txt
echo "USER_2" >> private/default_settlement_enforced.txt
echo "USER_3" >> private/default_settlement_enforced.txt

echo "/PATTERN_1/iu" > private/patterns_common.txt
echo "/PATTERN_2/iu" >> private/patterns_common.txt
echo "/PATTERN_3/iu" >> private/patterns_common.txt

echo "/PATTERN_1/iu" > private/patterns_enforced.txt
echo "/PATTERN_2/iu" >> private/patterns_enforced.txt
echo "/PATTERN_3/iu" >> private/patterns_enforced.txt
```

### Data masking example
```
D=$(date "+%y_%V|")

echo "${D}post|OWNER_ID_1|POST_ID_1" > private/ignored_items.txt
echo "${D}post|OWNER_ID_2|POST_ID_2" >> private/ignored_items.txt
echo "${D}post|OWNER_ID_3|POST_ID_3" >> private/ignored_items.txt

echo "${D}comment|OWNER_ID_1|POST_ID_1|COMMENT_ID_1" >> private/ignored_items.txt
echo "${D}comment|OWNER_ID_2|POST_ID_2|COMMENT_ID_2" >> private/ignored_items.txt
echo "${D}comment|OWNER_ID_3|POST_ID_3|COMMENT_ID_3" >> private/ignored_items.txt

echo "${D}nested_comment|OWNER_ID_1|POST_ID_1|THREAD_ID_1|COMMENT_ID_1" >> private/ignored_items.txt
echo "${D}nested_comment|OWNER_ID_2|POST_ID_2|THREAD_ID_2|COMMENT_ID_2" >> private/ignored_items.txt
echo "${D}nested_comment|OWNER_ID_3|POST_ID_3|THREAD_ID_3|COMMENT_ID_3" >> private/ignored_items.txt

echo "${D}all_comments_from_under|FROM_ID_1|OWNER_ID_1|POST_ID_1" >> private/ignored_items.txt
echo "${D}all_comments_from_under|FROM_ID_2|OWNER_ID_2|POST_ID_2" >> private/ignored_items.txt
echo "${D}all_comments_from_under|FROM_ID_3|OWNER_ID_3|POST_ID_3" >> private/ignored_items.txt

echo "${D}all_comments_under|OWNER_ID_1|POST_ID_1" >> private/ignored_items.txt
echo "${D}all_comments_under|OWNER_ID_2|POST_ID_2" >> private/ignored_items.txt
echo "${D}all_comments_under|OWNER_ID_3|POST_ID_3" >> private/ignored_items.txt

echo "${D}all_from_with_fragment|FROM_ID_1|FRAGMENT_1" >> private/ignored_items.txt
echo "${D}all_from_with_fragment|FROM_ID_2|FRAGMENT_2" >> private/ignored_items.txt
echo "${D}all_from_with_fragment|FROM_ID_3|FRAGMENT_3" >> private/ignored_items.txt

echo "${D}from_id|FROM_ID_1" >> private/ignored_items.txt
echo "${D}from_id|FROM_ID_2" >> private/ignored_items.txt
echo "${D}from_id|FROM_ID_3" >> private/ignored_items.txt

echo "${D}owner_id|OWNER_ID_1" >> private/ignored_items.txt
echo "${D}owner_id|OWNER_ID_2" >> private/ignored_items.txt
echo "${D}owner_id|OWNER_ID_3" >> private/ignored_items.txt

echo "${D}photo_comment|PHOTO_OWNER_ID_1|PHOTO_ID_1|PHOTO_COMMENT_ID_1" >> private/ignored_items.txt
echo "${D}photo_comment|PHOTO_OWNER_ID_2|PHOTO_ID_2|PHOTO_COMMENT_ID_2" >> private/ignored_items.txt
echo "${D}photo_comment|PHOTO_OWNER_ID_3|PHOTO_ID_3|PHOTO_COMMENT_ID_3" >> private/ignored_items.txt

echo "${D}video_comment|VIDEO_OWNER_ID_1|VIDEO_ID_1|VIDEO_COMMENT_ID_1" >> private/ignored_items.txt
echo "${D}video_comment|VIDEO_OWNER_ID_2|VIDEO_ID_2|VIDEO_COMMENT_ID_2" >> private/ignored_items.txt
echo "${D}video_comment|VIDEO_OWNER_ID_3|VIDEO_ID_3|VIDEO_COMMENT_ID_3" >> private/ignored_items.txt

echo "${D}enforced_post|OWNER_ID_1|POST_ID_1" >> private/ignored_items.txt
echo "${D}enforced_post|OWNER_ID_2|POST_ID_2" >> private/ignored_items.txt
echo "${D}enforced_post|OWNER_ID_3|POST_ID_3" >> private/ignored_items.txt
```

### Weekly steps
```
./sms.php -f
clear; ./sms.php
vim -O private/{owner_id_common,patterns_common,ignored_items,complaints}.txt
clear; ./sms.php
vim -O private/{owner_id_common,patterns_common,ignored_items,complaints}.txt
clear; ./sms.php
vim -O private/{owner_id_common,patterns_common,ignored_items,complaints}.txt
clear; ./sms.php
```

### Corrupted database repair
```
./sms.php -r
```

### Job continuation after power-off
```
./sms.php -c
```

## Credits
Author: Mike Zueff

Email: mike.zueff [at] gmail [dot] com
