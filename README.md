# Server component of ShoppingList-App
This is kind of an example implementation.

It uses PHP/SQLite. User and Password are hashed and used as SQLite file name.

## add new user
```bash
# php -f cli.php
Usage: cli.php <user> <pass>
# php -f cli.php youruser yoursecret
```

## mod_rewrite
Multiple paths must be mapped to index.php. I used following mod_rewrite in Apache:
```
RewriteEngine on
ewritecond %{REQUEST_FILENAME} !/index.php
RewriteRule ^(.*)$ /index.php?path=$1 [NC,L,QSA]
```
