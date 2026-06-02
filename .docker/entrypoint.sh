#!/bin/sh
set -e

# Copy env file if not exists
if [ ! -f .env ]; then
    echo "Creating .env from example..."
    cp .env.example .env
fi

# Ensure SQLite database file exists and is writable
echo "Setting up SQLite database file..."
mkdir -p /var/www/database
touch /var/www/database/database.sqlite
chmod 664 /var/www/database/database.sqlite
chown -R www-data:www-data /var/www/database

# Key generate if missing
if [ -f .env ] && ! grep -q "APP_KEY=base" .env; then
    echo "Generating Application Key..."
    php artisan key:generate --force
fi

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Seed database if users table is empty
echo "Checking if database needs seeding..."
USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null || echo 0)

if [ "$USER_COUNT" -eq 0 ]; then
    echo "Database is empty. Seeding database..."
    php artisan db:seed --force
else
    echo "Database already has records. Skipping seeding."
fi

# Cache config/routes
echo "Optimizing application cache..."
php artisan package:discover --ansi
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link --force

# Start supervisor
echo "Starting Supervisor..."
mkdir -p /var/log/supervisor
exec supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
