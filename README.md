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

echo "-GROUP_1" > private/watched_owners.txt
echo "-GROUP_2" > private/watched_owners.txt
echo "-GROUP_3" > private/watched_owners.txt
echo "USER_1" > private/watched_owners.txt
echo "USER_2" > private/watched_owners.txt
echo "USER_3" > private/watched_owners.txt

echo "/PATTERN_1/i" > private/patterns.txt
echo "/PATTERN_2/i" > private/patterns.txt
echo "/PATTERN_3/i" > private/patterns.txt

echo "TOKEN" > private/vk_api_token.txt
./sms.php
vim data/log.txt
```

## Credits
Author: Mike Zueff

Email: mike.zueff [at] gmail.com
