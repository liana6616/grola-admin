<?php

namespace app;

use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

abstract class Model
{
    public int $id = 0;
    
    // Константы
    protected const EXCLUDED_PROPERTIES = ['attributes'];
    protected const DEFAULT_ORDER = 'id ASC';
    
    // Кэш для метаданных
    protected static array $tableColumns = [];
    
    // Хранилище для динамических свойств
    protected array $attributes = [];
    
    /**
     * Магические методы для работы с динамическими свойствами
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }
    
    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }
    
    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }
    
    public function __unset(string $name): void
    {
        unset($this->attributes[$name]);
    }
    
    /**
     * Получить опубликованную версию записи
     */
    public static function getPublished(int $draftId): ?static
    {
        $draft = self::findById($draftId);
        
        if (!$draft || !$draft->original_id()) {
            return null;
        }
        
        return $draft->original_id ? self::findById($draft->original_id) : null;
    }

    /**
     * Проверить, поддерживает ли таблица черновики
     */
    public static function supportsDrafts(): bool
    {
        return static::fieldExists('is_draft') && static::fieldExists('original_id');
    }
    
    /**
     * Получить все записи с сортировкой
     */
    public static function findAll(?string $order = null): array
    {
        $order = $order ?? self::DEFAULT_ORDER;
        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . static::TABLE . ' ORDER BY ' . $order;
        
        return $db->query($sql, [], static::class) ?: [];
    }
    
    /**
     * Получить записи с фильтром по show
     */
    public static function findAllShow(?string $order = null, bool $show = true): array
    {
        $order = $order ?? '`rate` DESC, id ASC';
        $db = Db::getInstance();
        
        $params = [];
        $where = '';
        
        if ($show) {
            $where = 'WHERE `show` = :show';
            $params[':show'] = 1;
        }
        
        $sql = 'SELECT * FROM ' . static::TABLE . ' ' . $where . ' ORDER BY ' . $order;
        
        return $db->query($sql, $params, static::class) ?: [];
    }
    
    /**
     * Получить записи как массив [id => object]
     */
    public static function findArray(?string $order = null, bool $show = true): array
    {
        $items = self::findAllShow($order, $show);
        $result = [];
        
        foreach ($items as $item) {
            $result[$item->id] = $item;
        }
        
        return $result;
    }
    
    /**
     * Получить массив ID
     */
    public static function findArrayIds(string $order = "id ASC"): array
    {
        $items = self::findAllShow($order, false);
        $result = [];
        
        foreach ($items as $item) {
            $result[] = $item->id;
        }
        
        return $result;
    }
    
    /**
     * Найти по URL
     */
    public static function findByUrl(string $url, bool $show = false): ?static
    {
        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . static::TABLE . ' WHERE `url` = :url';
        
        if ($show) {
            $sql .= ' AND `show` = 1';
        }
        
        $sql .= ' LIMIT 1';
        
        $data = $db->query($sql, [':url' => $url], static::class);
        
        return $data ? $data[0] : null;
    }
    
    /**
     * Найти по ID
     */
    public static function findById(int $id): ?static
    {
        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . static::TABLE . ' WHERE id = :id';
        
        $data = $db->query($sql, [':id' => $id], static::class);
        
        return $data ? $data[0] : null;
    }
    
    /**
     * Найти по хэшу
     */
    public static function findByHash(string $hash): ?static
    {
        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . static::TABLE . ' WHERE hash = :hash';
        
        $data = $db->query($sql, [':hash' => $hash], static::class);
        
        return $data ? $data[0] : null;
    }
    
    /**
     * Найти по значению поля ids
     */
    public static function findByIds(string $value, ?string $order = null): array
    {
        $order = $order ?? self::DEFAULT_ORDER;
        $db = Db::getInstance();
        
        $sql = 'SELECT * FROM ' . static::TABLE . ' WHERE `ids` = :value ORDER BY ' . $order;
        
        return $db->query($sql, [':value' => $value], static::class) ?: [];
    }
    
    /**
     * Найти по произвольному условию WHERE
     */
    public static function findWhere(string $where, array $params = []): array
    {
        $db = Db::getInstance();
        $sql = 'SELECT * FROM `' . static::TABLE . '` ' . $where;
        
        return $db->query($sql, $params, static::class) ?: [];
    }
    
    /**
     * Найти по условию WHERE и преобразовать в массив
     */
    public static function findWhereArray(string $where, array $params = [], string $field = 'id'): array
    {
        $items = self::findWhere($where, $params);
        $result = [];
        
        foreach ($items as $item) {
            $result[$item->$field] = $item;
        }
        
        return $result;
    }
    
    /**
     * Универсальный метод для запросов с WHERE
     */
    public static function where(?string $where = null, array $params = [], bool $single = false)
    {
        if (empty($where)) {
            return $single ? null : [];
        }
        
        $db = Db::getInstance();
        $sql = 'SELECT * FROM `' . static::TABLE . '` ' . $where;

        $data = $db->query($sql, $params, static::class);
        
        if ($single) {
            return $data ? $data[0] : null;
        }
        
        return $data ?: [];
    }
    
    /**
     * Универсальный метод для SQL запросов
     */
    public static function query(?string $sql = null, array $params = [], bool $single = false)
    {
        if (empty($sql)) {
            return $single ? null : [];
        }
        
        $db = Db::getInstance();
        $data = $db->query($sql, $params, static::class);
        
        if ($single) {
            return $data ? $data[0] : null;
        }
        
        return $data ?: [];
    }
    
    /**
     * Поиск по имени (LIKE)
     */
    public static function findByName(string $name): array
    {
        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . static::TABLE . ' WHERE `name` LIKE :name';
        
        return $db->query($sql, [':name' => "%{$name}%"], static::class) ?: [];
    }
    
    /**
     * Поиск по произвольному полю
     */
    public static function findByField(string $field, $value = '', ?string $order = null): array
    {
        $order = $order ?? self::DEFAULT_ORDER;
        $db = Db::getInstance();
        
        $sql = 'SELECT * FROM ' . static::TABLE . ' WHERE `' . $field . '` = :value ORDER BY ' . $order;
        
        return $db->query($sql, [':value' => $value], static::class) ?: [];
    }
    
    /**
     * Получить меню
     */
    public static function menuShow(): array
    {
        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . static::TABLE . ' WHERE `show` = 1 AND `menu` = 1 ORDER BY `rate` DESC, id ASC';
        
        return $db->query($sql, [], static::class) ?: [];
    }
    
    /**
     * Получить поля для базы данных
     */
    protected function getDbFields(): array
    {
        $fields = [];
        
        // Получаем публичные свойства через рефлексию
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        
        foreach ($properties as $property) {
            $name = $property->getName();
            if (!in_array($name, self::EXCLUDED_PROPERTIES) && $name !== 'id') {
                $value = $property->getValue($this);
                if ($value !== null) {
                    // Проверяем, существует ли поле в таблице
                    if (static::fieldExists($name)) {
                        $fields[$name] = $value;
                    }
                }
            }
        }
        
        // Добавляем динамические свойства, если они существуют в таблице
        foreach ($this->attributes as $name => $value) {
            if ($name !== 'id' && $value !== null && static::fieldExists($name)) {
                $fields[$name] = $value;
            }
        }
        
        return $fields;
    }
    
    /**
     * Вставить новую запись
     */
    public function insert(): int
    {
        $fields = $this->getDbFields();
        
        if (empty($fields)) {
            throw new RuntimeException('Нет полей для вставки');
        }
        
        $cols = [];
        $params = [];
        
        foreach ($fields as $name => $value) {
            $cols[] = '`' . $name . '`';
            $params[':' . $name] = $value;
        }
        
        $sql = 'INSERT INTO ' . static::TABLE . ' 
                (' . implode(', ', $cols) . ') 
                VALUES (' . implode(', ', array_keys($params)) . ')';
        
        $db = Db::getInstance();
        $result = $db->execute($sql, $params);
        
        $this->id = (int)$db->getLastId();
        return $this->id;
    }
    
    /**
     * Обновить запись
     */
    public function update(): void
    {
        if ($this->id === 0) {
            throw new RuntimeException('Нельзя обновить запись без ID');
        }
        
        $fields = $this->getDbFields();
        
        if (empty($fields)) {
            return;
        }
        
        $setParts = [];
        $params = [];
        
        foreach ($fields as $name => $value) {
            $setParts[] = '`' . $name . '` = :' . $name;
            $params[':' . $name] = $value;
        }
        
        $params[':id'] = $this->id;
        
        $sql = 'UPDATE ' . static::TABLE . ' 
                SET ' . implode(', ', $setParts) . ' 
                WHERE id = :id';
        
        $db = Db::getInstance();
        $db->execute($sql, $params);
    }
    
    /**
     * Сохранить запись (вставить или обновить)
     */
    public function save(): int
    {
        if ($this->id === 0) {
            return $this->insert();
        }
        
        $this->update();
        return $this->id;
    }
    
    /**
     * Удалить запись
     */
    public function delete(): void
    {
        if ($this->id === 0) {
            throw new RuntimeException('Нельзя удалить запись без ID');
        }
        
        $sql = 'DELETE FROM ' . static::TABLE . ' WHERE id = :id';
        $db = Db::getInstance();
        $db->execute($sql, [':id' => $this->id]);
    }

    /**
     * Удалить записи по условию WHERE
     */
    public static function deleteWhere(string $where, array $params = []): int
    {
        $db = Db::getInstance();
        $sql = 'DELETE FROM ' . static::TABLE . ' ' . $where;
        
        try {
            $stmt = $db->execute($sql, $params);
            return $stmt->rowCount();
        } catch (\Exception $e) {
            error_log("Ошибка в deleteWhere(): " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Изменить статус show
     */
    public function show(int $show): void
    {
        if ($this->id === 0) {
            throw new RuntimeException('Нельзя изменить статус записи без ID');
        }
        
        $sql = 'UPDATE ' . static::TABLE . ' SET `show` = :show WHERE id = :id';
        $db = Db::getInstance();
        $db->execute($sql, [
            ':show' => $show,
            ':id' => $this->id
        ]);
    }
    
    /**
     * Генерация хлебных крошек для админки
     */
    public static function adminBread($bread, $f): string
    {
        $parent = $_GET['parent'] ?? 0;
        
        $breadcrumbFile = ROOT . '/private/views/components/breadCrumbs.php';
        
        if (is_file($breadcrumbFile)) {
            ob_start();
            include $breadcrumbFile;
            $content = ob_get_clean();
            return (string)$content;
        }
        
        return '';
    }
    
    /**
     * Получить колонки таблицы из базы данных (с кэшированием)
     */
    protected static function getTableColumns(): array
    {
        $className = static::class;
        
        if (!isset(self::$tableColumns[$className])) {
            $db = Db::getInstance();
            $tableName = static::TABLE;
            
            // Запрашиваем информацию о колонках
            $sql = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = :table_name";
            
            $result = $db->query($sql, [':table_name' => $tableName]);
            
            $columns = [];
            foreach ($result as $row) {
                $columns[$row['COLUMN_NAME']] = [
                    'type' => $row['DATA_TYPE'],
                    'nullable' => $row['IS_NULLABLE'] === 'YES',
                    'default' => $row['COLUMN_DEFAULT']
                ];
            }
            
            self::$tableColumns[$className] = $columns;
        }
        
        return self::$tableColumns[$className];
    }

    /**
     * Публичный метод для проверки существования поля
     */
    public static function hasField(string $field): bool
    {
        return static::fieldExists($field);
    }
    
    /**
     * Проверить, существует ли поле в таблице
     */
    protected static function fieldExists(string $field): bool
    {
        $columns = self::getTableColumns();
        return isset($columns[$field]);
    }
    
    /**
     * Массовое присвоение свойств из массива
     */
    public function fill(array $data): static
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            } else {
                $this->$key = $value; // Попадет в attributes через __set
            }
        }
        
        return $this;
    }
    
    /**
     * Получить модель как массив
     */
    public function toArray(): array
    {
        $array = get_object_vars($this);
        
        // Удаляем служебные свойства
        foreach (self::EXCLUDED_PROPERTIES as $property) {
            unset($array[$property]);
        }
        
        // Добавляем динамические свойства
        $array = array_merge($array, $this->attributes);
        
        return $array;
    }

    /**
     * Реализация подсчёта записей
     */
    public static function count($where = '') {
        try {
            $db = Db::getInstance();
            
            // Получаем имя таблицы
            $table = static::TABLE;
                        
            $sql = "SELECT COUNT(*) as cnt FROM `{$table}` {$where}";
            $result = $db->query($sql, []); // Без класса!

            return !empty($result) ? (int)$result[0]['cnt'] : 0;
        } catch (\Exception $e) {
            error_log("Error in Model::count() for table '{$table}': " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Получить имена полей таблицы (кроме служебных)
     */
    public static function getTableFields(bool $includeId = false): array
    {
        $tableName = static::TABLE;
        $className = static::class;
        
        if (!isset(self::$tableColumns[$className])) {
            self::getTableColumns(); // Инициализируем кэш
        }
        
        $columns = self::$tableColumns[$className] ?? [];
        $fields = array_keys($columns);
        
        // Исключаем служебные поля
        $excludedFields = ['id', 'is_draft', 'draft_id', 'edit_date', 'edit_admin_id', 'published_at', 'created_at'];
        
        if (!$includeId) {
            $excludedFields[] = 'id';
        }
        
        $fields = array_filter($fields, function($field) use ($excludedFields) {
            return !in_array($field, $excludedFields);
        });
        
        return array_values($fields);
    }

    /**
     * Копировать данные из одной записи в другую (исключая служебные поля)
     */
    public static function copyData(self $source, self $destination, array $excludeFields = []): self
    {
        $fields = static::getTableFields(false);
        
        // Добавляем стандартные исключения
        $defaultExclude = ['id', 'is_draft', 'original_id', 'created_at'];
        $excludeFields = array_merge($defaultExclude, $excludeFields);
        
        foreach ($fields as $field) {
            if (!in_array($field, $excludeFields)) {
                $destination->$field = $source->$field;
            }
        }

        return $destination;
    }

    /**
     * Записать версию публикации
     */
    public function savePublicationVersion(array $extraData = [], string $comment = ''): bool
    {
        try {
            $db = Db::getInstance();
            
            // Получаем контекст аудита для логирования
            $auditContext = $db->getAuditContext();
            
            // Подготавливаем данные для сохранения
            $versionData = $this->toArray();
            
            // Исключаем служебные поля
            $excludedFields = ['id', 'is_draft', 'original_id', 'edit_date', 'edit_admin_id'];
            foreach ($excludedFields as $field) {
                unset($versionData[$field]);
            }
            
            // Подготавливаем данные для JSON
            $versionDataJson = json_encode($versionData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            // Подготавливаем метаданные
            $metadata = array_merge([
                'model_class' => static::class,
                'table_name' => static::TABLE,
                'audit_context' => $auditContext
            ], $extraData);
            
            $metadataJson = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            // SQL запрос для вставки версии
            $sql = "INSERT INTO publication_versions 
                    (table_name, record_id, version_data, admin_id, admin_ip, comment, metadata) 
                    VALUES (:table_name, :record_id, :version_data, :admin_id, :admin_ip, :comment, :metadata)";
            
            $params = [
                ':table_name' => static::TABLE,
                ':record_id' => $this->id,
                ':version_data' => $versionDataJson,
                ':admin_id' => $auditContext['admin_id'] ?? null,
                ':admin_ip' => $auditContext['admin_ip'] ?? null,
                ':comment' => $comment,
                ':metadata' => $metadataJson
            ];
            
            $db->execute($sql, $params);
            
            return true;
        } catch (\Exception $e) {
            error_log("Ошибка сохранения версии публикации: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Получить историю версий публикаций
     */
    public static function getPublicationVersions(int $recordId, int $limit = 10): array
    {
        try {
            $db = Db::getInstance();
            
            $sql = "SELECT * FROM publication_versions 
                    WHERE table_name = :table_name AND record_id = :record_id 
                    ORDER BY created_at DESC 
                    LIMIT :limit";
            
            $params = [
                ':table_name' => static::TABLE,
                ':record_id' => $recordId,
                ':limit' => $limit
            ];
            
            return $db->query($sql, $params);
        } catch (\Exception $e) {
            error_log("Ошибка получения версий публикации: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Восстановить данные из версии
     */
    public static function restoreFromVersion(int $versionId): ?static
    {
        try {
            $db = Db::getInstance();
            
            // Получаем данные версии
            $sql = "SELECT * FROM publication_versions WHERE id = :version_id";
            $version = $db->query($sql, [':version_id' => $versionId], true);
            
            if (!$version) {
                throw new \Exception("Версия не найдена");
            }
            
            // Проверяем, что версия принадлежит правильной таблице
            if ($version[0]['table_name'] !== static::TABLE) {
                throw new \Exception("Версия принадлежит другой таблице");
            }
            
            // Декодируем данные версии
            $versionData = json_decode($version[0]['version_data'], true);
            if (!$versionData) {
                throw new \Exception("Ошибка декодирования данных версии");
            }
            
            // Получаем или создаем запись
            $recordId = $version[0]['record_id'];
            $model = static::findById($recordId);
            
            if (!$model) {
                $model = new static();
            }
            
            // Восстанавливаем данные
            foreach ($versionData as $field => $value) {
                if (property_exists($model, $field) || static::fieldExists($field)) {
                    $model->$field = $value;
                }
            }
            
            // Обновляем дату редактирования
            if (static::fieldExists('edit_date')) {
                $model->edit_date = date('Y-m-d H:i:s');
            }
            
            if (static::fieldExists('edit_admin_id')) {
                $model->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
            }
            
            // Сохраняем восстановленную запись
            $model->save();
            
            // Логируем восстановление
            if (class_exists('app\Models\ChangesLog')) {
                ChangesLog::logRestore(
                    static::TABLE,
                    $model->id,
                    $_SESSION['admin']['id'] ?? 0,
                    $versionId,
                    $version[0]['comment'] ?? ''
                );
            }
            
            return $model;
        } catch (\Exception $e) {
            error_log("Ошибка восстановления из версии: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Получить последнюю опубликованную версию
     */
    public function getLatestPublishedVersion(): ?array
    {
        $versions = static::getPublicationVersions($this->id, 1);
        return !empty($versions) ? $versions[0] : null;
    }

    /**
     * Проверить, есть ли опубликованные версии
     */
    public function hasPublishedVersions(): bool
    {
        $versions = static::getPublicationVersions($this->id, 1);
        return !empty($versions);
    }
}