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
if ! docker compose version &>/dev/null; then
    echo "[!] Docker Compose v2 command not found. Trying 'docker-compose'..."
    if ! [ -x "$(command -v docker-compose)" ]; then
        echo "[-] Error: Docker Compose is not installed."
        exit 1
    fi
fi

# Check and setup .env file
if [ ! -f .env ]; then
    echo "[*] Creating .env file from .env.example..."
    cp .env.example .env
fi

# Ask for VPS IP / Domain for WebSockets
read -p "Enter your VPS Public IP Address or Domain (e.g. 103.123.45.67): " VPS_IP
if [ -z "$VPS_IP" ]; then
    echo "[!] VPS IP/Domain cannot be empty."
    exit 1
fi

# Generate random secure passwords for DB
DB_PASSWORD=$(openssl rand -hex 16 2>/dev/null || echo "JamkridaSecurePass123")

# Update .env configuration using Python for cross-platform portability
echo "[*] Updating .env configuration..."
python3 -c "
with open('.env', 'r') as f:
    lines = f.readlines()
new_lines = []
for line in lines:
    if line.startswith('DB_HOST='):
        new_lines.append('DB_HOST=db\n')
    elif line.startswith('DB_PASSWORD='):
        new_lines.append('DB_PASSWORD=$DB_PASSWORD\n')
    elif line.startswith('REVERB_HOST='):
        new_lines.append('REVERB_HOST=\"$VPS_IP\"\n')
    elif line.startswith('VITE_REVERB_HOST='):
        new_lines.append('VITE_REVERB_HOST=\"$VPS_IP\"\n')
    elif line.startswith('REVERB_PORT='):
        new_lines.append('REVERB_PORT=8081\n')
    elif line.startswith('VITE_REVERB_PORT='):
        new_lines.append('VITE_REVERB_PORT=8081\n')
    elif line.startswith('MQTT_HOST='):
        new_lines.append('MQTT_HOST=mqtt\n')
    elif line.startswith('MQTT_PORT='):
        new_lines.append('MQTT_PORT=1883\n')
    else:
        new_lines.append(line)
with open('.env', 'w') as f:
    f.writelines(new_lines)
"

echo "[+] Configuration updated successfully."

# Start services
echo "[*] Building and starting docker containers..."
docker compose down || true
docker compose up -d --build

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
echo "Monitor Logs:    docker compose logs -f"
echo "Stop Service:    docker compose down"
echo "========================================================="
