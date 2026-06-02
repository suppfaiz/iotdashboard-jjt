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
        sudo apt-get install -y docker.io docker-compose-plugin
        sudo systemctl start docker
        sudo systemctl enable docker
    else
        echo "[!] Please install Docker and try again."
        exit 1
    fi
fi

# Check if docker compose (v2) is installed
DOCKER_COMPOSE="docker compose"
if ! docker compose version &>/dev/null; then
    echo "[!] Docker Compose v2 command not found. Trying 'docker-compose'..."
    if ! [ -x "$(command -v docker-compose)" ]; then
        echo "[-] Error: Docker Compose is not installed."
        exit 1
    fi
    DOCKER_COMPOSE="docker-compose"
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

update_env_val "DB_HOST" "db"
update_env_val "DB_PASSWORD" "$DB_PASSWORD"
update_env_val "REVERB_HOST" "\"$VPS_IP\""
update_env_val "VITE_REVERB_HOST" "\"$VPS_IP\""
update_env_val "REVERB_PORT" "8081"
update_env_val "VITE_REVERB_PORT" "8081"
update_env_val "MQTT_HOST" "mqtt"
update_env_val "MQTT_PORT" "1883"

echo "[+] Configuration updated successfully."

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
