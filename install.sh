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
        sudo apt-get update
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

# Check if docker compose (v2) is installed
DOCKER_COMPOSE="docker compose"
if ! docker compose version &>/dev/null; then
    echo "[!] Docker Compose v2 command not found. Trying 'docker-compose'..."
    if ! [ -x "$(command -v docker-compose)" ]; then
        echo "[*] Attempting to install Docker Compose..."
        if [ -f /etc/debian_version ]; then
            sudo apt-get update
            sudo apt-get install -y docker-compose || sudo apt-get install -y docker-compose-v2 || true
            if ! [ -x "$(command -v docker-compose)" ] && ! docker compose version &>/dev/null; then
                echo "[-] Error: Failed to install Docker Compose. Please install it manually."
                exit 1
            fi
            if docker compose version &>/dev/null; then
                DOCKER_COMPOSE="docker compose"
            else
                DOCKER_COMPOSE="docker-compose"
            fi
        else
            echo "[-] Error: Docker Compose is not installed."
            exit 1
        fi
    else
        DOCKER_COMPOSE="docker-compose"
    fi
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

# Generate random secure passwords for DB
DB_PASSWORD=$(openssl rand -hex 16 2>/dev/null || echo "JamkridaSecurePass123")

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

update_env_val "DB_CONNECTION" "sqlite"
update_env_val "DB_DATABASE" "/var/www/database/database.sqlite"
comment_env_val "DB_HOST"
comment_env_val "DB_PORT"
comment_env_val "DB_USERNAME"
comment_env_val "DB_PASSWORD"

update_env_val "REVERB_HOST" "\"$VPS_IP\""
update_env_val "VITE_REVERB_HOST" "\"$VPS_IP\""
update_env_val "REVERB_PORT" "8081"
update_env_val "VITE_REVERB_PORT" "8081"
update_env_val "MQTT_HOST" "mqtt"
update_env_val "MQTT_PORT" "1883"

echo "[+] Configuration updated successfully."

# Setup SQLite database file on host
echo "[*] Setting up SQLite database on host..."
mkdir -p database
touch database/database.sqlite
chmod -R 777 database

# Check if PHP is installed and version is at least 8.4
PHP_VERSION=$(php -r 'echo PHP_VERSION;' 2>/dev/null || echo "0")
if [ "$(printf '%s\n' "8.4" "$PHP_VERSION" | sort -V | head -n1)" != "8.4" ]; then
    echo "[*] PHP version is lower than 8.4 (detected: $PHP_VERSION). Installing/Upgrading to PHP 8.4..."
    if [ -f /etc/debian_version ]; then
        sudo apt-get update
        sudo apt-get install -y software-properties-common
        sudo add-apt-repository -y ppa:ondrej/php
        sudo apt-get update
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
echo "[*] Building and starting docker containers..."
$DOCKER_COMPOSE down || true
$DOCKER_COMPOSE up -d --build

echo ""
echo "========================================================="
echo "   DEPLOYMENT SUCCESSFUL!"
echo "========================================================="
echo "Your IoT Dashboard has been successfully deployed."
echo ""
echo "Web URL:         http://$VPS_IP"
echo "Database GUI:    http://$VPS_IP:8082"
echo "MQTT Broker:     mqtt://$VPS_IP:1883"
echo "WebSocket Port:  8081"
echo ""
echo "Default Credentials:"
echo "Email:           admin@admin.com"
echo "Password:        password"
echo ""
echo "Monitor Logs:    $DOCKER_COMPOSE logs -f"
echo "Stop Service:    $DOCKER_COMPOSE down"
echo "========================================================="
