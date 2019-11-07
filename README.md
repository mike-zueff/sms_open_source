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

echo "-GROUP_1" > private/groups_watched
echo "-GROUP_N" > private/groups_watched

echo "TOKEN" > private/vk_api_token
./sms.php
TODO
touch config/private_patterns
```

## Credits
Author: Mike Zueff

Email: mike.zueff [at] gmail.com
