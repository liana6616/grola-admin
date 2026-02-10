<?php

namespace app;

class Db
{
    private static $instance = null;
    protected $dbh;
    
    // Данные текущего пользователя для аудита
    private $auditAdminId = 0;
    private $auditAdminIp = '';
    private $auditAdminAgent = '';

    public function __construct()
    {
        $config = (include ROOT . '/config/db.php');
        $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'] . ';charset=utf8mb4';

        // Подготовка опций с учетом версии PHP
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];

        // Для PHP 8.5+ используем новую константу, для старых версий - старую
        if (PHP_VERSION_ID >= 80500) {
            // PHP 8.5+: используем новое пространство имен или просто DSN
            if (class_exists('Pdo\Mysql') && defined('Pdo\Mysql::ATTR_INIT_COMMAND')) {
                $options[\Pdo\Mysql::ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
            }
            // Если новая константа не найдена, charset уже указан в DSN
        } else {
            // PHP < 8.5: используем старую константу
            $options[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
        }

        $this->dbh = new \PDO(
            $dsn,
            $config['user'],
            $config['password'],
            $options
        );
        
        // Инициализируем переменные сессии для аудита
        $this->initAuditSession();
    }

    public function __clone() {}
    public function __wakeup() {}

    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Инициализация сессии для аудита
     */
    private function initAuditSession()
    {
        // Устанавливаем IP-адрес по умолчанию
        $this->auditAdminIp = $this->getClientIp();
        
        // Устанавливаем user agent
        $this->auditAdminAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Инициализируем сессию, если нужно получить ID пользователя
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        
        // Получаем ID пользователя из сессии, если есть
        if (isset($_SESSION['admin']['id'])) {
            $this->auditAdminId = (int)$_SESSION['admin']['id'];
        }
        
        // Устанавливаем переменные сессии для триггеров
        $this->setSessionVars();
    }

    /**
     * Получает IP-адрес клиента
     */
    private function getClientIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1';
    }

    /**
     * Устанавливает переменные сессии для триггеров
     */
    private function setSessionVars()
    {
        try {
            // Устанавливаем переменные сессии MySQL для триггеров логирования
            $this->dbh->exec("SET @current_admin_id = " . ($this->auditAdminId ?: 'NULL'));
            $this->dbh->exec("SET @current_admin_ip = '" . $this->escapeSqlValue($this->auditAdminIp) . "'");
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем выполнение
            error_log("Ошибка установки переменных аудита: " . $e->getMessage());
        }
    }

    /**
     * Экранирование значения для SQL
     */
    private function escapeSqlValue($value)
    {
        return str_replace(
            ["\\", "'", "\"", "\x00", "\n", "\r", "\x1a"],
            ["\\\\", "\\'", "\\\"", "\\0", "\\n", "\\r", "\\Z"],
            $value
        );
    }

    /**
     * Устанавливает пользовательский контекст для аудита
     */
    public function setAuditContext($adminId = null, $adminIp = null, $adminAgent = null)
    {
        if ($adminId !== null) {
            $this->auditAdminId = (int)$adminId;
        }
        
        if ($adminIp !== null) {
            $this->auditAdminIp = $adminIp;
        }
        
        if ($adminAgent !== null) {
            $this->auditAdminAgent = $adminAgent;
        }
        
        // Обновляем переменные сессии
        $this->setSessionVars();
        
        return $this;
    }

    /**
     * Получает текущий контекст аудита
     */
    public function getAuditContext()
    {
        return [
            'admin_id' => $this->auditAdminId,
            'admin_ip' => $this->auditAdminIp,
            'admin_agent' => $this->auditAdminAgent
        ];
    }

    /**
     * Обновляет контекст аудита из сессии
     */
    public function refreshAuditContext()
    {
        // Обновляем ID пользователя из сессии
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        
        if (isset($_SESSION['admin']['id'])) {
            $this->auditAdminId = (int)$_SESSION['admin']['id'];
        } else {
            $this->auditAdminId = 0;
        }
        
        // Обновляем переменные сессии
        $this->setSessionVars();
        
        return $this;
    }

    public function query($sql, $data = [], $class = null)
    {
        $sth = $this->dbh->prepare($sql);
        
        if (!$sth) {
            throw new \RuntimeException(
                "Ошибка подготовки запроса: " . 
                implode(', ', $this->dbh->errorInfo())
            );
        }
        
        $res = $sth->execute($data);
        
        if (!$res) {
            throw new \RuntimeException(
                "Ошибка выполнения запроса: " . 
                implode(', ', $sth->errorInfo())
            );
        }

        if (empty($class)) {
            return $sth->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $sth->fetchAll(\PDO::FETCH_CLASS, $class);
    }

    public function execute($sql, $data = [])
    {    
        $sth = $this->dbh->prepare($sql);
        
        if (!$sth) {
            throw new \RuntimeException(
                "Ошибка подготовки запроса: " . 
                implode(', ', $this->dbh->errorInfo())
            );
        }
        
        $result = $sth->execute($data);
        
        if (!$result) {
            throw new \RuntimeException(
                "Ошибка выполнения запроса: " . 
                implode(', ', $sth->errorInfo())
            );
        }
        
        return $sth;
    }

    public function getLastId()
    {
        return $this->dbh->lastInsertId();
    }

    public function changeDB($db)
    {
        $config = (include ROOT . '/config/db.php');
        $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $db . ';charset=utf8mb4';

        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_WARNING,
        ];

        // Аналогично конструктору, но с ERRMODE_WARNING
        if (PHP_VERSION_ID >= 80500) {
            if (class_exists('Pdo\Mysql') && defined('Pdo\Mysql::ATTR_INIT_COMMAND')) {
                $options[\Pdo\Mysql::ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
            }
        } else {
            $options[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
        }

        $this->dbh = new \PDO(
            $dsn,
            $config['user'],
            $config['password'],
            $options
        );
        
        // Обновляем переменные сессии для новой базы данных
        $this->setSessionVars();
    }

    public static function PDO()
    {
        $config = (include ROOT . '/config/db.php');
        $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'] . ';charset=utf8mb4';

        // Подготовка опций с учетом версии PHP
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];

        // Для PHP 8.5+ используем новую константу, для старых версий - старую
        if (PHP_VERSION_ID >= 80500) {
            // PHP 8.5+: используем новое пространство имен или просто DSN
            if (class_exists('Pdo\Mysql') && defined('Pdo\Mysql::ATTR_INIT_COMMAND')) {
                $options[\Pdo\Mysql::ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
            }
            // Если новая константа не найдена, charset уже указан в DSN
        } else {
            // PHP < 8.5: используем старую константу
            $options[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
        }

        return new \PDO(
            $dsn,
            $config['user'],
            $config['password'],
            $options
        );
    }

    /**
     * Обертка для execute с установкой контекста аудита
     */
    public function executeWithAudit($sql, $data = [])
    {
        // Обновляем контекст перед выполнением
        $this->refreshAuditContext();
        
        return $this->execute($sql, $data);
    }

    /**
     * Обертка для query с установкой контекста аудита
     */
    public function queryWithAudit($sql, $data = [], $class = null)
    {
        // Обновляем контекст перед выполнением
        $this->refreshAuditContext();
        
        return $this->query($sql, $data, $class);
    }

    /**
     * Начать транзакцию
     */
    public function beginTransaction(): bool
    {
        return $this->dbh->beginTransaction();
    }

    /**
     * Подтвердить транзакцию
     */
    public function commit(): bool
    {
        return $this->dbh->commit();
    }

    /**
     * Откатить транзакцию
     */
    public function rollBack(): bool
    {
        return $this->dbh->rollBack();
    }

    /**
     * Проверить, находится ли соединение в транзакции
     */
    public function inTransaction(): bool
    {
        return $this->dbh->inTransaction();
    }
}