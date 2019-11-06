# sms_open_source (Social Media Sieve, open source edition)
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
TODO
cat database/init.sql | sqlite3 database/sms_db_sqlite
cat > config/private_vk_api_current_token.json <<EOF
{
  "access_token": "TOKEN",
  "email": "EMAIL",
  "expires_in": 0,
  "user_id": ID
}
EOF
touch config/private_patterns
cargo run
```

## Credits
Author: Mike Zueff

Email: mike.zueff [at] gmail.com
