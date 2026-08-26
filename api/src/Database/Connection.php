<?php

class Connection
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance instanceof mysqli) {
            return self::$instance;
        }

        $host = getenv('DB_HOST');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');
        $name = getenv('DB_NAME');

        if (!$host || !$user || !$name) {
            $legacyConfigPath = dirname(__DIR__, 2) . '/data/config.php';
            if (file_exists($legacyConfigPath)) {
                require $legacyConfigPath;
                $host = $host ?: (isset($host_1) ? $host_1 : 'localhost');
                $user = $user ?: (isset($user_1) ? $user_1 : 'root');
                $pass = ($pass !== false && $pass !== null) ? $pass : (isset($password_1) ? $password_1 : '');
                $name = $name ?: (isset($db_1) ? $db_1 : '');
            }
        }

        self::$instance = new mysqli($host, $user, $pass, $name);

        if (self::$instance->connect_error) {
            throw new RuntimeException('Database connection failed: ' . self::$instance->connect_error);
        }

        self::$instance->set_charset('utf8mb4');

        return self::$instance;
    }
}
