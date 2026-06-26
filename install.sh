#!/bin/bash

# Exit on error
set -e

echo "========================================================="
echo "   JAMKRIDA ENERGY IOT DASHBOARD - VPS QUICK DEPLOYER"
echo "========================================================="
echo ""

# Check if Docker is installed
if ! [ -x "$(command -v docker)" ]; then
    echo "[-] Error: Docker is not installed."
    echo "[*] Attempting to install Docker..."
    if [ -f /etc/debian_version ]; then
        sudo apt-get update || true
        if ! [ -x "$(command -v curl)" ]; then
            sudo apt-get install -y curl
        fi
        echo "[*] Running official Docker installation script..."
        curl -fsSL https://get.docker.com -o get-docker.sh
        sudo sh get-docker.sh
        rm get-docker.sh
        sudo systemctl start docker
        sudo systemctl enable docker
    else
        echo "[!] Please install Docker manually and try again."
        exit 1
    fi
fi

# Check if docker compose (v2) or docker-compose (v1) is installed
DOCKER_COMPOSE=""
if docker compose version &>/dev/null; then
    DOCKER_COMPOSE="docker compose"
elif command -v docker-compose &>/dev/null; then
    DOCKER_COMPOSE="docker-compose"
fi

if [ -z "$DOCKER_COMPOSE" ]; then
    echo "[!] Docker Compose not found. Attempting to install..."
    if [ -f /etc/debian_version ]; then
        sudo apt-get update || true
        # Try installing docker-compose-plugin (v2) or fallback to docker-compose (v1)
        sudo apt-get install -y docker-compose-plugin || sudo apt-get install -y docker-compose || sudo apt-get install -y docker-compose-v2 || true
        
        # Clear shell path hash to recognize new binaries
        hash -r 2>/dev/null || true
        
        if docker compose version &>/dev/null; then
            DOCKER_COMPOSE="docker compose"
        elif command -v docker-compose &>/dev/null || [ -x /usr/bin/docker-compose ] || [ -x /usr/local/bin/docker-compose ]; then
            DOCKER_COMPOSE="docker-compose"
        fi
    else
        echo "[-] Error: Docker Compose is not installed."
        exit 1
    fi
fi

if [ -z "$DOCKER_COMPOSE" ]; then
    echo "[-] Error: Failed to install Docker Compose. Please install it manually."
    exit 1
fi

# Check and setup .env file
if [ ! -f .env ]; then
    echo "[*] Creating .env file from .env.example..."
    cp .env.example .env
fi

# Auto-detect public IP
echo "[*] Detecting public IP address..."
DEFAULT_IP=$(curl -s --max-time 3 ifconfig.me || curl -s --max-time 3 icanhazip.com || echo "127.0.0.1")

# Ask for VPS IP / Domain for WebSockets
read -p "Enter your VPS Public IP Address or Domain [default: $DEFAULT_IP]: " VPS_IP
VPS_IP=${VPS_IP:-$DEFAULT_IP}

# Read existing credentials if they are set in .env to preserve them
EXISTING_DB_USER=$(grep "^DB_USERNAME=" .env | cut -d'=' -f2- | tr -d '"' | tr -d "'" | tr -d ' ' || echo "")
EXISTING_DB_PASS=$(grep "^DB_PASSWORD=" .env | cut -d'=' -f2- | tr -d '"' | tr -d "'" | tr -d ' ' || echo "")
EXISTING_MQTT_USER=$(grep "^MQTT_USERNAME=" .env | cut -d'=' -f2- | tr -d '"' | tr -d "'" | tr -d ' ' || echo "")
EXISTING_MQTT_PASS=$(grep "^MQTT_PASSWORD=" .env | cut -d'=' -f2- | tr -d '"' | tr -d "'" | tr -d ' ' || echo "")

DB_USERNAME=${EXISTING_DB_USER:-"root"}
DB_PASSWORD=${EXISTING_DB_PASS:-$(openssl rand -hex 16 2>/dev/null || echo "JamkridaSecurePass123")}
MQTT_USER=${EXISTING_MQTT_USER:-"jamkrida_sensor"}
MQTT_PASSWORD=${EXISTING_MQTT_PASS:-$(openssl rand -hex 16 2>/dev/null || echo "MqttSecurePass123")}

# Update .env configuration
echo "[*] Updating .env configuration..."

update_env_val() {
    local key="$1"
    local val="$2"
    if grep -q "^${key}=" .env; then
        if [[ "$OSTYPE" == "darwin"* ]]; then
            sed -i "" "s|^${key}=.*|${key}=${val}|" .env
        else
            sed -i "s|^${key}=.*|${key}=${val}|" .env
        fi
    else
        echo "${key}=${val}" >> .env
    fi
}

comment_env_val() {
    local key="$1"
    if grep -q "^${key}=" .env; then
        if [[ "$OSTYPE" == "darwin"* ]]; then
            sed -i "" "s|^${key}=|# ${key}=|" .env
        else
            sed -i "s|^${key}=|# ${key}=|" .env
        fi
    fi
}

update_env_val "DB_CONNECTION" "mysql"
update_env_val "DB_HOST" "mysql"
update_env_val "DB_PORT" "3306"
update_env_val "DB_DATABASE" "dashboard_iot_baru"
update_env_val "DB_USERNAME" "$DB_USERNAME"
update_env_val "DB_PASSWORD" "$DB_PASSWORD"

update_env_val "REVERB_HOST" "\"$VPS_IP\""
update_env_val "VITE_REVERB_HOST" "\"$VPS_IP\""
update_env_val "REVERB_PORT" "8085"
update_env_val "VITE_REVERB_PORT" "8085"

# Ensure Reverb Keys exist in the .env file
if ! grep -q "^REVERB_APP_ID=" .env; then
    update_env_val "REVERB_APP_ID" "533833"
fi
if ! grep -q "^REVERB_APP_KEY=" .env; then
    update_env_val "REVERB_APP_KEY" "azt0mh1mhiwobwenacjr"
fi
if ! grep -q "^REVERB_APP_SECRET=" .env; then
    update_env_val "REVERB_APP_SECRET" "cr4owmnc0g59xex0yi3j"
fi
if ! grep -q "^REVERB_SCHEME=" .env; then
    update_env_val "REVERB_SCHEME" "http"
fi

# Set VITE keys using literal values to prevent Vite bundler expansion issues
VAL_REVERB_APP_KEY=$(grep "^REVERB_APP_KEY=" .env | cut -d'=' -f2- | tr -d '"' | tr -d "'")
update_env_val "VITE_REVERB_APP_KEY" "$VAL_REVERB_APP_KEY"
update_env_val "VITE_REVERB_SCHEME" "http"

update_env_val "MQTT_HOST" "127.0.0.1"
update_env_val "MQTT_PORT" "1883"
update_env_val "MQTT_USERNAME" "$MQTT_USER"
update_env_val "MQTT_PASSWORD" "$MQTT_PASSWORD"

echo "[+] Configuration updated successfully."


# Check if PHP is installed and version is at least 8.4
PHP_VERSION=$(php -r 'echo PHP_VERSION;' 2>/dev/null || echo "0")
if [ "$(printf '%s\n' "8.4" "$PHP_VERSION" | sort -V | head -n1)" != "8.4" ]; then
    echo "[*] PHP version is lower than 8.4 (detected: $PHP_VERSION). Installing/Upgrading to PHP 8.4..."
    if [ -f /etc/debian_version ]; then
        sudo apt-get update || true
        sudo apt-get install -y software-properties-common
        sudo add-apt-repository -y ppa:ondrej/php
        sudo apt-get update || true
        sudo apt-get install -y php8.4-cli php8.4-xml php8.4-curl php8.4-mbstring php8.4-zip php8.4-gd php8.4-bcmath php8.4-sockets php8.4-mysql unzip
        sudo update-alternatives --set php /usr/bin/php8.4 || true
    else
        echo "[!] This script requires PHP 8.4 or higher. Please upgrade your PHP installation manually."
        exit 1
    fi
fi

# Check and install Composer on host if missing
if ! [ -x "$(command -v composer)" ]; then
    echo "[*] Composer is not installed on the host. Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
fi

# Check and install Node.js & NPM on host if missing
if ! [ -x "$(command -v npm)" ]; then
    echo "[*] Node.js/NPM is not installed on the host. Installing Node.js..."
    if [ -f /etc/debian_version ]; then
        curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
        sudo apt-get install -y nodejs
    else
        echo "[!] Please install Node.js (v20+) and NPM manually."
        exit 1
    fi
fi

# Run Composer Install on Host
echo "[*] Running 'composer install' on host..."
composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Run NPM install and build on Host
echo "[*] Compiling assets with NPM on host..."
npm install
npm run build


# Start services
echo "[*] Generating Mosquitto MQTT credentials file..."
# Generate the passwd file inside the .docker directory using the eclipse-mosquitto image tool
docker run --rm -v "$(pwd)/.docker:/config" eclipse-mosquitto:latest mosquitto_passwd -c -b /config/passwd "$MQTT_USER" "$MQTT_PASSWORD" || {
    echo "[!] Warning: Failed to generate encrypted mosquitto passwd file via Docker. Writing unencrypted fallback."
    echo "$MQTT_USER:$MQTT_PASSWORD" > .docker/passwd
}
chmod 644 .docker/passwd
echo "[*] Building and starting docker containers..."
$DOCKER_COMPOSE down || true
$DOCKER_COMPOSE up -d --build

echo "[*] Waiting for MySQL container to start and initialize..."
for i in {1..30}; do
    if $DOCKER_COMPOSE exec -T app php -r "
        try {
            \$pass = '';
            if (file_exists('/var/www/.env')) {
                \$lines = file('/var/www/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach (\$lines as \$line) {
                    if (strpos(\$line, '=') !== false && strpos(\$line, '#') !== 0) {
                        list(\$key, \$val) = explode('=', \$line, 2);
                        if (trim(\$key) === 'DB_PASSWORD') \$pass = trim(\$val, \"'\\\" \");
                    }
                }
            }
            new PDO('mysql:host=mysql;dbname=dashboard_iot_baru', 'root', \$pass);
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
    " &>/dev/null; then
        echo ""
        echo "[+] MySQL is ready!"
        break
    fi
    echo -n "."
    sleep 1
done

echo "[*] Generating application encryption key inside container..."
$DOCKER_COMPOSE exec -T app php artisan key:generate --force

echo "[*] Running database migrations inside container..."
$DOCKER_COMPOSE exec -T app php artisan migrate --force

echo "[*] Seeding database with default users and operational groups..."
$DOCKER_COMPOSE exec -T app php artisan db:seed --force

echo ""
echo "========================================================="
echo "   DEPLOYMENT SUCCESSFUL!"
echo "========================================================="
echo "Your IoT Dashboard has been successfully deployed."
echo ""
echo "Web URL:         http://$VPS_IP"
echo "Database GUI:    http://$VPS_IP:8082 (phpMyAdmin)"
echo "                 - Username: root"
echo "                 - Password: (sama seperti DB_PASSWORD Anda)"
echo "MQTT Broker:     mqtt://$VPS_IP:1883"
echo "MQTT User:       $MQTT_USER"
echo "MQTT Password:   $MQTT_PASSWORD"
echo "WebSocket Port:  8085"
echo ""
echo "Default Credentials (Web App):"
echo "Email:           admin@admin.com"
echo "Password:        password"
echo ""
echo "Monitor Logs:    $DOCKER_COMPOSE logs -f"
echo "Stop Service:    $DOCKER_COMPOSE down"
echo "========================================================="
