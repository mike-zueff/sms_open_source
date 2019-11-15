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
touch private/items_{archived,on_hold}.txt

echo "-GROUP_1" > private/watched_owners.txt
echo "-GROUP_2" >> private/watched_owners.txt
echo "-GROUP_3" >> private/watched_owners.txt

echo "USER_1" >> private/watched_owners.txt
echo "USER_2" >> private/watched_owners.txt
echo "USER_3" >> private/watched_owners.txt

echo "/PATTERN_1/i" > private/patterns.txt
echo "/PATTERN_2/i" >> private/patterns.txt
echo "/PATTERN_3/i" >> private/patterns.txt

echo "post|OWNER_ID_1|POST_ID_1" > private/items_ignored.txt
echo "post|OWNER_ID_2|POST_ID_2" >> private/items_ignored.txt
echo "post|OWNER_ID_3|POST_ID_3" >> private/items_ignored.txt

echo "comment|OWNER_ID_1|POST_ID_1|COMMENT_ID_1" >> private/items_ignored.txt
echo "comment|OWNER_ID_2|POST_ID_2|COMMENT_ID_2" >> private/items_ignored.txt
echo "comment|OWNER_ID_3|POST_ID_3|COMMENT_ID_3" >> private/items_ignored.txt

echo "nested_comment|OWNER_ID_1|POST_ID_1|THREAD_ID_1|COMMENT_ID_1" >> private/items_ignored.txt
echo "nested_comment|OWNER_ID_2|POST_ID_2|THREAD_ID_2|COMMENT_ID_2" >> private/items_ignored.txt
echo "nested_comment|OWNER_ID_3|POST_ID_3|THREAD_ID_3|COMMENT_ID_3" >> private/items_ignored.txt

echo "all_comments_under|OWNER_ID_1|POST_ID_1" >> private/items_ignored.txt
echo "all_comments_under|OWNER_ID_2|POST_ID_2" >> private/items_ignored.txt
echo "all_comments_under|OWNER_ID_3|POST_ID_3" >> private/items_ignored.txt

./sms.php
vim -o private/items_{ignored,on_hold,archived}.txt
./sms.php -a                  
vim -o private/items_{ignored,on_hold,archived}.txt
./sms.php -a
cp data/sms_db.sqlite{,_backup_REVISION}
./sms.php
```

## Credits
Author: Mike Zueff

Email: mike.zueff [at] gmail.com
