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
\$env = [];
if (file_exists('.env')) {
    foreach (file('.env') as \$line) {
        \$line = trim(\$line);
        if (\$line && strpos(\$line, '=') !== false && strpos(\$line, '#') !== 0) {
            list(\$k, \$v) = explode('=', \$line, 2);
            \$env[trim(\$k)] = trim(\$v, '\"\'');
        }
    }
}
\$host = \$env['DB_HOST'] ?? getenv('DB_HOST') ?? '127.0.0.1';
\$port = \$env['DB_PORT'] ?? getenv('DB_PORT') ?? '3306';
\$dbname = \$env['DB_DATABASE'] ?? getenv('DB_DATABASE') ?? 'laravel';
\$user = \$env['DB_USERNAME'] ?? getenv('DB_USERNAME') ?? 'root';
\$pass = \$env['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? '';

try {
    \$db = new PDO(\"mysql:host=\$host;port=\$port;dbname=\$dbname\", \$user, \$pass);
    exit(0);
} catch (Exception \$e) {
    echo \"Debug: Trying to connect with host=\$host, port=\$port, dbname=\$dbname, user=\$user, pass_len=\" . strlen(\$pass) . PHP_EOL;
    echo \"Connection error: \" . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"; do
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
\$env = [];
if (file_exists('.env')) {
    foreach (file('.env') as \$line) {
        \$line = trim(\$line);
        if (\$line && strpos(\$line, '=') !== false && strpos(\$line, '#') !== 0) {
            list(\$k, \$v) = explode('=', \$line, 2);
            \$env[trim(\$k)] = trim(\$v, '\"\'');
        }
    }
}
\$host = \$env['DB_HOST'] ?? getenv('DB_HOST') ?? '127.0.0.1';
\$port = \$env['DB_PORT'] ?? getenv('DB_PORT') ?? '3306';
\$dbname = \$env['DB_DATABASE'] ?? getenv('DB_DATABASE') ?? 'laravel';
\$user = \$env['DB_USERNAME'] ?? getenv('DB_USERNAME') ?? 'root';
\$pass = \$env['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? '';

try {
    \$db = new PDO(\"mysql:host=\$host;port=\$port;dbname=\$dbname\", \$user, \$pass);
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
php artisan package:discover --ansi
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link --force

# Start supervisor
echo "Starting Supervisor..."
exec supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
