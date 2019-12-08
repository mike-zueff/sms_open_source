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
```
git clone https://github.com/mike-zueff/sms_open_source.git
cd sms_open_source
cat data/init.sql | sqlite3 data/sms_db.sqlite
composer require vkcom/vk-php-sdk
mkdir private
echo "TOKEN" > private/vk_api_token.txt
touch private/complaints.txt

echo "-GROUP_1" > private/watched_owners.txt
echo "-GROUP_2" >> private/watched_owners.txt
echo "-GROUP_3" >> private/watched_owners.txt

echo "USER_1" >> private/watched_owners.txt
echo "USER_2" >> private/watched_owners.txt
echo "USER_3" >> private/watched_owners.txt

echo "USER_1" > private/from_id_enforced.txt
echo "USER_2" >> private/from_id_enforced.txt
echo "USER_3" >> private/from_id_enforced.txt

echo "/PATTERN_1/iu" > private/patterns.txt
echo "/PATTERN_2/iu" >> private/patterns.txt
echo "/PATTERN_3/iu" >> private/patterns.txt

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

./sms.php -f
clear; ./sms.php
vim -O private/{patterns,ignored_items,complaints}.txt
clear; ./sms.php
vim -O private/{patterns,ignored_items,complaints}.txt
clear; ./sms.php
vim -O private/{patterns,ignored_items,complaints}.txt
clear; ./sms.php
./sms.php -f

```

## Credits
Author: Mike Zueff

Email: mike.zueff [at] gmail [dot] com
