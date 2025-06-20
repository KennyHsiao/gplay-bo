#!/bin/sh
sshpass -p "$1" ssh ubuntu@$2 -t << EOF
cd /home/ubuntu/htdocs/app/gpoints
git pull
php artisan down
composer update
php artisan config:clear
php artisan view:clear
php artisan cache:clear
php artisan up
exit
EOF
