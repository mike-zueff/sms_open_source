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
touch private/email.txt

echo "-GROUP_1" > private/watched_owners.txt
echo "-GROUP_2" >> private/watched_owners.txt
echo "-GROUP_3" >> private/watched_owners.txt

echo "USER_1" >> private/watched_owners.txt
echo "USER_2" >> private/watched_owners.txt
echo "USER_3" >> private/watched_owners.txt

echo "/PATTERN_1/iu" > private/patterns.txt
echo "/PATTERN_2/iu" >> private/patterns.txt
echo "/PATTERN_3/iu" >> private/patterns.txt

D="@YYYY_MM_DD"

echo "${D}|post|OWNER_ID_1|POST_ID_1" > private/ignored_items.txt
echo "${D}|post|OWNER_ID_2|POST_ID_2" >> private/ignored_items.txt
echo "${D}|post|OWNER_ID_3|POST_ID_3" >> private/ignored_items.txt

echo "${D}|comment|OWNER_ID_1|POST_ID_1|COMMENT_ID_1" >> private/ignored_items.txt
echo "${D}|comment|OWNER_ID_2|POST_ID_2|COMMENT_ID_2" >> private/ignored_items.txt
echo "${D}|comment|OWNER_ID_3|POST_ID_3|COMMENT_ID_3" >> private/ignored_items.txt

echo "${D}|nested_comment|OWNER_ID_1|POST_ID_1|THREAD_ID_1|COMMENT_ID_1" >> private/ignored_items.txt
echo "${D}|nested_comment|OWNER_ID_2|POST_ID_2|THREAD_ID_2|COMMENT_ID_2" >> private/ignored_items.txt
echo "${D}|nested_comment|OWNER_ID_3|POST_ID_3|THREAD_ID_3|COMMENT_ID_3" >> private/ignored_items.txt

echo "${D}|all_comments_under|OWNER_ID_1|POST_ID_1" >> private/ignored_items.txt
echo "${D}|all_comments_under|OWNER_ID_2|POST_ID_2" >> private/ignored_items.txt
echo "${D}|all_comments_under|OWNER_ID_3|POST_ID_3" >> private/ignored_items.txt

echo "${D}|owner|OWNER_ID_1" >> private/ignored_items.txt
echo "${D}|owner|OWNER_ID_2" >> private/ignored_items.txt
echo "${D}|owner|OWNER_ID_3" >> private/ignored_items.txt

./sms.php
cp data/sms_db.sqlite_backup_{1,2}
cp data/sms_db.sqlite{,_backup_1}
vim -O private/{patterns,ignored_items,email}.txt
./sms.php -a
vim -O private/{patterns,ignored_items,email}.txt
./sms.php -a
vim -O private/{patterns,ignored_items,email}.txt
./sms.php -a
./sms.php
```

## Credits
Author: Mike Zueff

Email: mike.zueff [at] gmail [dot] com
