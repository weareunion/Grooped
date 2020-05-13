kill "$(ps -e | pgrep php | awk '{print $1}')"
echo "Reloading..."
sleep 2
php /var/www/html/moycroft/dev/API/CLI/switchboard.php
