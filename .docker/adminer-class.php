<?php
if (class_exists('\Adminer\Adminer')) {
    class AdminerSoftware extends \Adminer\Adminer {
        function login($login, $password) {
            $securePassword = getenv('ADMINER_PASSWORD');
            if (empty($securePassword)) {
                return false;
            }
            return ($password === $securePassword);
        }

        function database() {
            // Force Adminer to use the SQLite database file inside the container
            return "/var/www/html/database/database.sqlite";
        }

        function loginForm() {
            // Simplify the login form specifically for SQLite
            ?>
            <table cellspacing="0">
                <tr>
                    <th>System</th>
                    <td>SQLite 3</td>
                </tr>
                <tr>
                    <th>Database</th>
                    <td><input name="auth[db]" value="database/database.sqlite" readonly style="width: 250px; background: #eee; padding: 4px; border: 1px solid #ccc; border-radius: 4px;"></td>
                </tr>
                <tr>
                    <th>Password</th>
                    <td><input type="password" name="auth[password]" style="width: 250px; padding: 4px; border: 1px solid #ccc; border-radius: 4px;"></td>
                </tr>
            </table>
            <p>
                <input type="submit" value="Login to Dashboard DB" style="padding: 6px 12px; cursor: pointer; background: #31708f; color: #fff; border: none; border-radius: 4px; font-weight: bold;">
            </p>
            <div style="display:none;">
                <input type="hidden" name="auth[driver]" value="sqlite">
                <input type="hidden" name="auth[server]" value="localhost">
                <input type="hidden" name="auth[username]" value="admin">
            </div>
            <?php
            return true;
        }
    }
} else {
    class AdminerSoftware extends Adminer {
        function login($login, $password) {
            $securePassword = getenv('ADMINER_PASSWORD');
            if (empty($securePassword)) {
                return false;
            }
            return ($password === $securePassword);
        }

        function database() {
            // Force Adminer to use the SQLite database file inside the container
            return "/var/www/html/database/database.sqlite";
        }

        function loginForm() {
            // Simplify the login form specifically for SQLite
            ?>
            <table cellspacing="0">
                <tr>
                    <th>System</th>
                    <td>SQLite 3</td>
                </tr>
                <tr>
                    <th>Database</th>
                    <td><input name="auth[db]" value="database/database.sqlite" readonly style="width: 250px; background: #eee; padding: 4px; border: 1px solid #ccc; border-radius: 4px;"></td>
                </tr>
                <tr>
                    <th>Password</th>
                    <td><input type="password" name="auth[password]" style="width: 250px; padding: 4px; border: 1px solid #ccc; border-radius: 4px;"></td>
                </tr>
            </table>
            <p>
                <input type="submit" value="Login to Dashboard DB" style="padding: 6px 12px; cursor: pointer; background: #31708f; color: #fff; border: none; border-radius: 4px; font-weight: bold;">
            </p>
            <div style="display:none;">
                <input type="hidden" name="auth[driver]" value="sqlite">
                <input type="hidden" name="auth[server]" value="localhost">
                <input type="hidden" name="auth[username]" value="admin">
            </div>
            <?php
            return true;
        }
    }
}
