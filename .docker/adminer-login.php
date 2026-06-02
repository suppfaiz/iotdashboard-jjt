<?php
namespace docker {
    function adminer_object() {
        require_once('plugins/plugin.php');
        
        class AdminerSoftware extends \AdminerPlugin {
            function login($login, $password) {
                // Allow login without a password for local SQLite
                return true;
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
                </table>
                <p>
                    <input type="submit" value="Login to Dashboard DB" style="padding: 6px 12px; cursor: pointer; background: #31708f; color: #fff; border: none; border-radius: 4px; font-weight: bold;">
                </p>
                <div style="display:none;">
                    <input type="hidden" name="auth[driver]" value="sqlite">
                    <input type="hidden" name="auth[server]" value="localhost">
                    <input type="hidden" name="auth[username]" value="">
                    <input type="hidden" name="auth[password]" value="">
                </div>
                <?php
                return true;
            }
        }
        return new AdminerSoftware([]);
    }
}

namespace {
    function adminer_object() {
        return docker\adminer_object();
    }
    require('adminer.php');
}
