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

echo "-GROUP_1" > private/watched_owners.txt
echo "-GROUP_2" >> private/watched_owners.txt
echo "-GROUP_3" >> private/watched_owners.txt
echo "USER_1" >> private/watched_owners.txt
echo "USER_2" >> private/watched_owners.txt
echo "USER_3" >> private/watched_owners.txt

echo "post|OWNER_ID|POST_ID" > private/items_ignored.txt
echo "comment|OWNER_ID|POST_ID|COMMENT_ID" >> private/items_ignored.txt
echo "nested_comment|OWNER_ID|POST_ID|THREAD_ID|COMMENT_ID" >> private/items_ignored.txt

touch private/items_archived.txt
touch private/items_on_hold.txt

echo "/PATTERN_1/i" > private/patterns.txt
echo "/PATTERN_2/i" >> private/patterns.txt
echo "/PATTERN_3/i" >> private/patterns.txt

./sms.php
vim -o data/log.txt private/items_{on_hold,archived,ignored}.txt
./sms.php -a
vim -o data/log.txt private/items_{on_hold,archived,ignored}.txt
./sms.php -a
./sms.php
```

## Credits
Author: Mike Zueff

Email: mike.zueff [at] gmail.com
