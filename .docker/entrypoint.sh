#!/bin/sh
set -e

# Copy env file if not exists
if [ ! -f .env ]; then
    echo "Creating .env from example..."
    cp .env.example .env
fi

# Wait for MySQL database to become ready
echo "Waiting for database connection..."
until php -r "
try {
    \$db = new PDO('mysql:host=' . env('DB_HOST') . ';port=' . env('DB_PORT') . ';dbname=' . env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD'));
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
" 2>/dev/null; do
    echo "Database not ready yet, sleeping 2 seconds..."
    sleep 2
done
echo "Database connection established!"

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
USER_COUNT=$(php -r "
try {
    \$db = new PDO('mysql:host=' . env('DB_HOST') . ';port=' . env('DB_PORT') . ';dbname=' . env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD'));
    \$stmt = \$db->query('SELECT COUNT(*) FROM users');
    echo \$stmt->fetchColumn();
} catch (Exception \$e) {
    echo 0;
}
")

if [ "$USER_COUNT" -eq 0 ]; then
    echo "Database is empty. Seeding database..."
    php artisan db:seed --force
else
    echo "Database already has records. Skipping seeding."
fi

# Cache config/routes
echo "Optimizing application cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link --force

# Start supervisor
echo "Starting Supervisor..."
exec supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
