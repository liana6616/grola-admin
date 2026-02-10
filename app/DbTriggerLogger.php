<?php

namespace app;

/**
 * Класс для автоматического создания триггеров логирования изменений
 * С отдельными записями для каждого измененного поля
 */
class DbTriggerLogger
{
    private $pdo;
    private $dbname;
    private $logTable = 'changes_log';
    
    // Настройки исключения полей из логирования
    private $excludedFields = [
        'date_visit', 'hash', 'edit_date', 'edit_admin_id', 'original_id'
    ];
    
    // Настройки исключения для конкретных таблиц
    private $tableExcludedFields = [
        'admins' => ['date_visit', 'hash', 'hash_forgot'],
        'forms' => [],
        'telegram_message' => [],
        'telegram_errors' => [],
        'subscribe' => [],
        'news' => [],
        'articles' => [],
        'reviews' => [],
        'settings' => [],
    ];

    /**
     * Конструктор класса
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->dbname = $this->getDatabaseName();
        $this->createLogTable();
    }

    /**
     * Получает имя текущей базы данных
     */
    private function getDatabaseName()
    {
        $stmt = $this->pdo->query("SELECT DATABASE() as dbname");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['dbname'];
    }

    /**
     * Создает таблицу для логирования изменений
     */
    private function createLogTable()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS `{$this->logTable}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `table_name` varchar(255) NOT NULL COMMENT 'Название таблицы',
            `record_id` int(11) DEFAULT NULL COMMENT 'ID измененной записи',
            `field_name` varchar(255) DEFAULT NULL COMMENT 'Название измененного поля',
            `action` enum('INSERT','UPDATE','DELETE','PUBLICATION') NOT NULL COMMENT 'Тип действия',
            `old_value` longtext COMMENT 'Старое значение',
            `new_value` longtext COMMENT 'Новое значение',
            `admin_id` int(11) DEFAULT NULL COMMENT 'ID администратора (если есть)',
            `admin_name` varchar(255) DEFAULT NULL COMMENT 'Имя администратора',
            `admin_ip` varchar(45) DEFAULT NULL COMMENT 'IP адрес администратора',
            `comment` text COMMENT 'Комментарий к изменению',
            `change_type` varchar(50) DEFAULT 'field_change' COMMENT 'Тип изменения (field_change, publication)',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время изменения',
            PRIMARY KEY (`id`),
            KEY `idx_table_record` (`table_name`,`record_id`),
            KEY `idx_field` (`field_name`),
            KEY `idx_action` (`action`),
            KEY `idx_created_at` (`created_at`),
            KEY `idx_admin` (`admin_id`),
            KEY `idx_change_type` (`change_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Лог изменений в базе данных';
        ";

        try {
            $this->pdo->exec($sql);
            echo "Таблица логирования создана или уже существует<br>";
        } catch (Exception $e) {
            echo "Ошибка при создании таблицы логирования: " . $e->getMessage() . "<br>";
        }
    }

    /**
     * Получает список всех таблиц в базе данных
     */
    private function getAllTables()
    {
        $stmt = $this->pdo->query("SHOW TABLES");
        $tables = [];
        
        while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        return $tables;
    }

    /**
     * Получает структуру таблицы
     */
    private function getTableStructure($tableName)
    {
        $stmt = $this->pdo->query("DESCRIBE `{$tableName}`");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Проверяет существование триггера
     */
    private function triggerExists($triggerName)
    {
        $sql = "SELECT 1 FROM information_schema.triggers 
                WHERE trigger_schema = :dbname 
                AND trigger_name = :trigger_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':dbname' => $this->dbname,
            ':trigger_name' => $triggerName
        ]);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Удаляет существующие триггеры для таблицы
     */
    private function removeExistingTriggers($tableName)
    {
        $triggers = ['insert', 'update', 'delete'];
        
        foreach ($triggers as $action) {
            $triggerName = "log_{$tableName}_{$action}";
            if ($this->triggerExists($triggerName)) {
                try {
                    $this->pdo->exec("DROP TRIGGER IF EXISTS `{$triggerName}`");
                    echo "Удален триггер: {$triggerName}<br>";
                } catch (Exception $e) {
                    echo "Ошибка при удалении триггера {$triggerName}: " . $e->getMessage() . "<br>";
                }
            }
        }
    }

    /**
     * Получает список полей для исключения
     */
    private function getExcludedFieldsForTable($tableName)
    {
        $excluded = $this->excludedFields;
        
        if (isset($this->tableExcludedFields[$tableName])) {
            $excluded = array_merge($excluded, $this->tableExcludedFields[$tableName]);
        }
        
        return array_unique($excluded);
    }

    /**
     * Проверяет, нужно ли исключать поле
     */
    private function isFieldExcluded($tableName, $fieldName)
    {
        $excludedFields = $this->getExcludedFieldsForTable($tableName);
        return in_array($fieldName, $excludedFields);
    }

    /**
     * Создает триггер для INSERT операций
     * Для INSERT создает записи для всех неисключенных полей
     */
    private function createInsertTrigger($tableName)
    {
        $triggerName = "log_{$tableName}_insert";
        
        $structure = $this->getTableStructure($tableName);
        $insertStatements = [];
        
        foreach ($structure as $column) {
            $columnName = $column['Field'];
            
            // Пропускаем исключенные поля
            if ($this->isFieldExcluded($tableName, $columnName)) {
                continue;
            }
            
            // Пропускаем поле id
            if ($columnName === 'id') {
                continue;
            }
            
            $insertStatements[] = "
                INSERT INTO `{$this->logTable}` 
                (`table_name`, `record_id`, `field_name`, `action`, `old_value`, `new_value`, `admin_id`, `admin_ip`, `change_type`)
                VALUES (
                    '{$tableName}',
                    NEW.id,
                    '{$columnName}',
                    'INSERT',
                    NULL,
                    NEW.`{$columnName}`,
                    IFNULL(@current_admin_id, 0),
                    IFNULL(@current_admin_ip, '127.0.0.1'),
                    'field_change'
                );";
        }
        
        if (empty($insertStatements)) {
            echo "Нет полей для логирования в таблице {$tableName}, триггер INSERT не создан<br>";
            return;
        }
        
        $sql = "CREATE TRIGGER `{$triggerName}` AFTER INSERT ON `{$tableName}`
                FOR EACH ROW
                BEGIN
                    " . implode("\n                    ", $insertStatements) . "
                END";
        
        try {
            $this->pdo->exec($sql);
            echo "Создан триггер INSERT для таблицы {$tableName} (отдельные записи для каждого поля)<br>";
        } catch (Exception $e) {
            throw new Exception("Ошибка при создании триггера INSERT для {$tableName}: " . $e->getMessage());
        }
    }

    /**
     * Создает триггер для UPDATE операций
     * Для каждого измененного поля создает отдельную запись
     */
    private function createUpdateTrigger($tableName)
    {
        $triggerName = "log_{$tableName}_update";
        
        $structure = $this->getTableStructure($tableName);
        $updateConditions = [];
        
        foreach ($structure as $column) {
            $columnName = $column['Field'];
            
            // Пропускаем исключенные поля
            if ($this->isFieldExcluded($tableName, $columnName)) {
                continue;
            }
            
            // Пропускаем поле id
            if ($columnName === 'id') {
                continue;
            }
            
            // Используем безопасное сравнение для NULL
            $updateConditions[] = "
                IF NOT (OLD.`{$columnName}` <=> NEW.`{$columnName}`) THEN
                    INSERT INTO `{$this->logTable}` 
                    (`table_name`, `record_id`, `field_name`, `action`, `old_value`, `new_value`, `admin_id`, `admin_ip`, `change_type`)
                    VALUES (
                        '{$tableName}',
                        NEW.id,
                        '{$columnName}',
                        'UPDATE',
                        OLD.`{$columnName}`,
                        NEW.`{$columnName}`,
                        IFNULL(@current_admin_id, 0),
                        IFNULL(@current_admin_ip, '127.0.0.1'),
                        'field_change'
                    );
                END IF;";
        }
        
        if (empty($updateConditions)) {
            echo "Нет полей для логирования в таблице {$tableName}, триггер UPDATE не создан<br>";
            return;
        }
        
        $sql = "CREATE TRIGGER `{$triggerName}` AFTER UPDATE ON `{$tableName}`
                FOR EACH ROW
                BEGIN
                    " . implode("\n                    ", $updateConditions) . "
                END";
        
        try {
            $this->pdo->exec($sql);
            echo "Создан триггер UPDATE для таблицы {$tableName} (отдельные записи для каждого измененного поля)<br>";
        } catch (Exception $e) {
            throw new Exception("Ошибка при создании триггера UPDATE для {$tableName}: " . $e->getMessage());
        }
    }

    /**
     * Создает триггер для DELETE операций
     * Для DELETE создает записи для всех неисключенных полей
     */
    private function createDeleteTrigger($tableName)
    {
        $triggerName = "log_{$tableName}_delete";
        
        $structure = $this->getTableStructure($tableName);
        $deleteStatements = [];
        
        foreach ($structure as $column) {
            $columnName = $column['Field'];
            
            // Пропускаем исключенные поля
            if ($this->isFieldExcluded($tableName, $columnName)) {
                continue;
            }
            
            // Пропускаем поле id
            if ($columnName === 'id') {
                continue;
            }
            
            $deleteStatements[] = "
                INSERT INTO `{$this->logTable}` 
                (`table_name`, `record_id`, `field_name`, `action`, `old_value`, `new_value`, `admin_id`, `admin_ip`, `change_type`)
                VALUES (
                    '{$tableName}',
                    OLD.id,
                    '{$columnName}',
                    'DELETE',
                    OLD.`{$columnName}`,
                    NULL,
                    IFNULL(@current_admin_id, 0),
                    IFNULL(@current_admin_ip, '127.0.0.1'),
                    'field_change'
                );";
        }
        
        if (empty($deleteStatements)) {
            echo "Нет полей для логирования в таблице {$tableName}, триггер DELETE не создан<br>";
            return;
        }
        
        $sql = "CREATE TRIGGER `{$triggerName}` BEFORE DELETE ON `{$tableName}`
                FOR EACH ROW
                BEGIN
                    " . implode("\n                    ", $deleteStatements) . "
                END";
        
        try {
            $this->pdo->exec($sql);
            echo "Создан триггер DELETE для таблицы {$tableName} (отдельные записи для каждого поля)<br>";
        } catch (Exception $e) {
            throw new Exception("Ошибка при создании триггера DELETE для {$tableName}: " . $e->getMessage());
        }
    }

    /**
     * Добавляет поле в исключение для всех таблиц
     */
    public function addGlobalExcludedField($fieldName)
    {
        if (!in_array($fieldName, $this->excludedFields)) {
            $this->excludedFields[] = $fieldName;
        }
    }

    /**
     * Добавляет исключение для конкретной таблицы
     */
    public function addTableExcludedField($tableName, $fields)
    {
        if (!isset($this->tableExcludedFields[$tableName])) {
            $this->tableExcludedFields[$tableName] = [];
        }
        
        if (is_array($fields)) {
            $this->tableExcludedFields[$tableName] = array_merge(
                $this->tableExcludedFields[$tableName],
                $fields
            );
        } else {
            $this->tableExcludedFields[$tableName][] = $fields;
        }
        
        $this->tableExcludedFields[$tableName] = array_unique($this->tableExcludedFields[$tableName]);
    }

    /**
     * Получает список исключенных полей для таблицы
     */
    public function getExcludedFields($tableName)
    {
        return $this->getExcludedFieldsForTable($tableName);
    }

    /**
     * Создает триггеры для конкретной таблицы
     */
    public function createTriggersForTable($tableName, $force = false)
    {
        try {
            // Пропускаем таблицу логирования
            if ($tableName === $this->logTable) {
                echo "Пропускаем таблицу логирования: {$tableName}<br>";
                return true;
            }
            
            // Проверяем, есть ли столбец id в таблице
            $structure = $this->getTableStructure($tableName);
            $hasId = false;
            
            foreach ($structure as $column) {
                if ($column['Field'] === 'id' && strpos($column['Type'], 'int') !== false) {
                    $hasId = true;
                    break;
                }
            }
            
            if (!$hasId) {
                echo "Таблица {$tableName} не имеет столбца id типа INT, пропускаем<br>";
                return false;
            }
            
            // Проверяем, есть ли поля для логирования после исключений
            $hasLoggableFields = false;
            foreach ($structure as $column) {
                $fieldName = $column['Field'];
                if ($fieldName !== 'id' && !$this->isFieldExcluded($tableName, $fieldName)) {
                    $hasLoggableFields = true;
                    break;
                }
            }
            
            if (!$hasLoggableFields) {
                echo "Таблица {$tableName} не имеет полей для логирования (все поля исключены), пропускаем<br>";
                return false;
            }
            
            if ($force) {
                $this->removeExistingTriggers($tableName);
            }
            
            // Проверяем, существуют ли уже триггеры
            if (!$force) {
                $triggersExist = true;
                foreach (['insert', 'update', 'delete'] as $action) {
                    $triggerName = "log_{$tableName}_{$action}";
                    if (!$this->triggerExists($triggerName)) {
                        $triggersExist = false;
                        break;
                    }
                }
                
                if ($triggersExist) {
                    echo "Триггеры для таблицы {$tableName} уже существуют, пропускаем<br>";
                    return true;
                }
            }
            
            // Создаем триггеры
            $this->createInsertTrigger($tableName);
            $this->createUpdateTrigger($tableName);
            $this->createDeleteTrigger($tableName);
            
            echo "Триггеры успешно созданы для таблицы: {$tableName}<br>";
            return true;
            
        } catch (Exception $e) {
            echo "Ошибка при создании триггеров для таблицы {$tableName}: " . $e->getMessage() . "<br>";
            return false;
        }
    }

    /**
     * Создает триггеры для всех таблиц
     */
    public function createTriggersForAllTables($excludeTables = [], $force = false)
    {
        $tables = $this->getAllTables();
        $results = [
            'success' => [],
            'failed' => [],
            'skipped' => []
        ];
        
        echo "Всего таблиц в базе данных: " . count($tables) . "<br>";
        
        foreach ($tables as $table) {
            echo "<br>Обработка таблицы: {$table}<br>";
            
            if (in_array($table, $excludeTables) || $table === $this->logTable) {
                $results['skipped'][] = $table;
                echo "Пропущена (в списке исключений)<br>";
                continue;
            }
            
            if ($this->createTriggersForTable($table, $force)) {
                $results['success'][] = $table;
            } else {
                $results['failed'][] = $table;
            }
        }
        
        return $results;
    }

    /**
     * Устанавливает контекст пользователя
     */
    public function setAdminContext($adminId = null, $adminIp = null, $adminName = null)
    {
        if ($adminId === null) {
            $adminId = $this->getCurrentAdminId();
        }
        
        if ($adminIp === null) {
            $adminIp = $this->getClientIp();
        }
        
        if ($adminName === null) {
            $adminName = $this->getCurrentAdminName();
        }
        
        $adminNameEscaped = $this->pdo->quote($adminName ?? 'Система');
        $adminIpEscaped = $this->pdo->quote($adminIp ?? '127.0.0.1');
        
        $this->pdo->exec("SET @current_admin_id = " . ($adminId ?? 'NULL'));
        $this->pdo->exec("SET @current_admin_ip = {$adminIpEscaped}");
        $this->pdo->exec("SET @current_admin_name = {$adminNameEscaped}");
    }

    /**
     * Получает ID пользователя из сессии
     */
    private function getCurrentAdminId()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['admin']['id']) ? (int)$_SESSION['admin']['id'] : null;
    }

    /**
     * Получает имя пользователя из сессии
     */
    private function getCurrentAdminName()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['admin']['name'])) {
            return $_SESSION['admin']['name'];
        } elseif (isset($_SESSION['admin']['login'])) {
            return $_SESSION['admin']['login'];
        }
        
        return null;
    }

    /**
     * Получает IP адрес
     */
    private function getClientIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        
        return $ip;
    }

    /**
     * Записывает событие публикации в лог
     */
    public function logPublication($tableName, $recordId, $adminId, $adminName, $type = 'publish', $comment = null)
    {
        $sql = "INSERT INTO `{$this->logTable}` 
                (`table_name`, `record_id`, `field_name`, `action`, `old_value`, `new_value`, `admin_id`, `admin_name`, `admin_ip`, `comment`, `change_type`)
                VALUES (:table_name, :record_id, 'publication', 'PUBLICATION', '', :new_value, :admin_id, :admin_name, :admin_ip, :comment, 'publication')";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            ':table_name' => $tableName,
            ':record_id' => $recordId,
            ':new_value' => $type,
            ':admin_id' => $adminId,
            ':admin_name' => $adminName,
            ':admin_ip' => $this->getClientIp(),
            ':comment' => $comment
        ]);
    }

    /**
     * Получает лог изменений
     */
    public function getChangeLog($filters = [], $limit = 100, $offset = 0)
    {
        $where = [];
        $params = [];
        
        if (!empty($filters['table_name'])) {
            $where[] = "table_name = :table_name";
            $params[':table_name'] = $filters['table_name'];
        }
        
        if (!empty($filters['record_id'])) {
            $where[] = "record_id = :record_id";
            $params[':record_id'] = $filters['record_id'];
        }
        
        if (!empty($filters['field_name'])) {
            $where[] = "field_name = :field_name";
            $params[':field_name'] = $filters['field_name'];
        }
        
        if (!empty($filters['action'])) {
            $where[] = "action = :action";
            $params[':action'] = $filters['action'];
        }
        
        if (!empty($filters['change_type'])) {
            $where[] = "change_type = :change_type";
            $params[':change_type'] = $filters['change_type'];
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "SELECT * FROM `{$this->logTable}` 
                {$whereClause}
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Получает агрегированный лог (группировка по изменениям)
     */
    public function getAggregatedLog($filters = [], $limit = 100, $offset = 0)
    {
        $where = [];
        $params = [];
        
        if (!empty($filters['table_name'])) {
            $where[] = "table_name = :table_name";
            $params[':table_name'] = $filters['table_name'];
        }
        
        if (!empty($filters['record_id'])) {
            $where[] = "record_id = :record_id";
            $params[':record_id'] = $filters['record_id'];
        }
        
        if (!empty($filters['action'])) {
            $where[] = "action = :action";
            $params[':action'] = $filters['action'];
        }
        
        $dateFrom = !empty($filters['date_from']) ? $filters['date_from'] : date('Y-m-d 00:00:00', strtotime('-7 days'));
        $dateTo = !empty($filters['date_to']) ? $filters['date_to'] : date('Y-m-d 23:59:59');
        
        $where[] = "created_at BETWEEN :date_from AND :date_to";
        $params[':date_from'] = $dateFrom;
        $params[':date_to'] = $dateTo;
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "SELECT 
                    table_name,
                    record_id,
                    action,
                    change_type,
                    COUNT(*) as changes_count,
                    GROUP_CONCAT(DISTINCT field_name ORDER BY field_name SEPARATOR ', ') as changed_fields,
                    MAX(created_at) as last_change,
                    MIN(created_at) as first_change,
                    GROUP_CONCAT(DISTINCT admin_id) as admin_ids,
                    GROUP_CONCAT(DISTINCT admin_name) as admin_names
                FROM `{$this->logTable}` 
                {$whereClause}
                GROUP BY table_name, record_id, action, change_type
                ORDER BY last_change DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Получает детали изменений для конкретной записи
     */
    public function getChangeDetails($tableName, $recordId, $dateFrom = null)
    {
        $params = [
            ':table_name' => $tableName,
            ':record_id' => $recordId
        ];
        
        $where = "table_name = :table_name AND record_id = :record_id";
        
        if ($dateFrom) {
            $where .= " AND created_at >= :date_from";
            $params[':date_from'] = $dateFrom;
        }
        
        $sql = "SELECT * FROM `{$this->logTable}` 
                WHERE {$where}
                ORDER BY created_at DESC, field_name";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Очищает старые записи лога
     */
    public function cleanupOldLogs($olderThan)
    {
        $sql = "DELETE FROM `{$this->logTable}` WHERE created_at < :older_than";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':older_than' => $olderThan]);
        
        return $stmt->rowCount();
    }

    /**
     * Получает статистику логов
     */
    public function getLogStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_logs,
                    COUNT(DISTINCT CONCAT(table_name, '_', record_id)) as total_records,
                    COUNT(DISTINCT table_name) as tables_affected,
                    COUNT(CASE WHEN action = 'INSERT' THEN 1 END) as inserts,
                    COUNT(CASE WHEN action = 'UPDATE' THEN 1 END) as updates,
                    COUNT(CASE WHEN action = 'DELETE' THEN 1 END) as deletes,
                    COUNT(CASE WHEN action = 'PUBLICATION' THEN 1 END) as publications,
                    COUNT(CASE WHEN change_type = 'field_change' THEN 1 END) as field_changes,
                    COUNT(CASE WHEN change_type = 'publication' THEN 1 END) as publication_events,
                    MIN(created_at) as first_log,
                    MAX(created_at) as last_log
                FROM `{$this->logTable}`";
        
        return $this->pdo->query($sql)->fetch(\PDO::FETCH_ASSOC);
    }
}
?>