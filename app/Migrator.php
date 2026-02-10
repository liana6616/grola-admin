<?php

namespace app;

use PDO;

class Migrator
{
    public $pdo;
    public $migrationsTable = 'migrations';
    public $migrationsDir = __DIR__ . '/../migrations/';
    public $seedsDir = __DIR__ . '/../migrations/seeds/';
    public $structureFile = __DIR__ . '/../migrations/.database_structure.json';

    public function __construct()
    {
        $config = include __DIR__ . '/../config/db.php';
        $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'] . ';charset=UTF8MB4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        $this->pdo = new PDO($dsn, $config['user'], $config['password'], $options);
        $this->pdo->exec("SET NAMES utf8mb4");

        $this->ensureMigrationsDirectoryExists();
        $this->ensureSeedsDirectoryExists();
        $this->ensureMigrationsTableExists();
    }

    public function ensureMigrationsDirectoryExists()
    {
        if (!file_exists($this->migrationsDir)) {
            mkdir($this->migrationsDir, 0777, true);
        }
    }

    public function ensureSeedsDirectoryExists()
    {
        if (!file_exists($this->seedsDir)) {
            mkdir($this->seedsDir, 0777, true);
        }
    }

    public function ensureMigrationsTableExists()
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration_name VARCHAR(255) NOT NULL UNIQUE,
                applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    /**
     * Автоматически проверяет изменения в структуре БД и генерирует миграции
     * Этот метод сравнивает текущую структуру БД с сохраненной ранее и создает SQL-файлы миграций
     * Имена миграций генерируются автоматически на основе обнаруженных изменений
     * 
     * @return array Список сгенерированных файлов миграций
     */
    public function autoCheckAndGenerateMigrations()
    {
        $generatedMigrations = [];
        
        if (!file_exists($this->structureFile)) {
            echo "Initializing database structure tracking...\n";
            $currentStructure = $this->getDatabaseStructure();
            $this->saveStructure($currentStructure);
            echo "Database structure tracking initialized.\n";
            return $generatedMigrations;
        }
        
        $currentStructure = $this->getDatabaseStructure();
        $previousStructure = $this->loadPreviousStructure();
        
        if (empty($previousStructure)) {
            echo "No previous structure found. Creating initial structure file.\n";
            $this->saveStructure($currentStructure);
            return $generatedMigrations;
        }
        
        if ($currentStructure['checksum'] !== $previousStructure['checksum']) {
            echo "Database changes detected!\n";
            
            $changes = $this->compareStructures($previousStructure, $currentStructure);
            
            if (!empty($changes['sql'])) {
                $name = $this->generateMigrationName($changes);
                $existingMigrations = $this->getAllMigrationNames();
                
                if (in_array($name, $existingMigrations)) {
                    $name = $this->generateUniqueMigrationName($name, $existingMigrations);
                }
                
                $timestamp = date('YmdHis');
                $filename = "{$timestamp}_{$name}.sql";
                $path = $this->migrationsDir . $filename;
                
                $sql = "-- Auto-generated migration based on database changes\n";
                $sql .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n";
                $sql .= "-- Previous structure: " . $previousStructure['version'] . "\n";
                $sql .= "-- Current structure: " . $currentStructure['version'] . "\n\n";
                $sql .= $changes['sql'];
                
                file_put_contents($path, $sql);
                
                // ИСПРАВЛЕНИЕ: Выполняем SQL перед отметкой как применённой
                echo "Executing migration SQL...\n";
                $this->pdo->beginTransaction();
                try {
                    $queries = array_filter(
                        array_map('trim', explode(';', $changes['sql'])),
                        function($query) { return !empty($query); }
                    );
                    
                    foreach ($queries as $index => $query) {
                        if (!empty($query) && strpos(trim($query), '--') !== 0) {
                            echo "  Executing query " . ($index + 1) . "/" . count($queries) . "...\n";
                            $this->pdo->exec($query);
                        }
                    }
                    
                    $this->pdo->commit();
                    echo "SQL executed successfully.\n";
                    
                } catch (\Exception $e) {
                    $this->pdo->rollBack();
                    echo "ERROR: Failed to execute migration SQL: " . $e->getMessage() . "\n";
                    echo "Migration file created but SQL not executed.\n";
                    
                    // Не отмечаем как применённую, если SQL не выполнился
                    $this->saveStructure($currentStructure);
                    $generatedMigrations[] = $filename;
                    return $generatedMigrations;
                }
                
                // Только после успешного выполнения SQL отмечаем как применённую
                $this->recordMigrationAsApplied($filename);
                $this->saveStructure($currentStructure);
                
                $generatedMigrations[] = $filename;
                
                echo "Generated and applied migration: {$filename}\n";
                echo "Changes detected:\n";
                foreach ($changes['description'] as $desc) {
                    echo " - {$desc}\n";
                }
            } else {
                echo "No SQL changes detected (only checksum changed).\n";
                $this->saveStructure($currentStructure);
            }
        } else {
            echo "No database changes detected.\n";
        }
        
        return $generatedMigrations;
    }

    /**
     * Полностью автоматический процесс проверки и применения обновлений БД
     * Выполняет три шага:
     * 1. Проверяет изменения и генерирует миграции
     * 2. Применяет все ожидающие миграции
     * 3. Применяет сиды (тестовые данные)
     * 
     * @return array Результат выполнения с информацией о сгенерированных и примененных миграциях
     */
    public function autoCheckAndApply()
    {
        echo "Starting automatic database check and update...\n\n";
        
        $result = [
            'generated_migrations' => [],
            'applied_migrations' => [],
            'errors' => []
        ];
        
        try {
            echo "Step 1: Checking for database changes...\n";
            $generated = $this->autoCheckAndGenerateMigrations();
            $result['generated_migrations'] = $generated;
            
            if (!empty($generated)) {
                echo "Generated " . count($generated) . " new migrations.\n";
            }
            
            echo "\nStep 2: Applying pending migrations...\n";
            $pending = $this->getPendingMigrations();
            
            if (!empty($pending)) {
                echo "Found " . count($pending) . " pending migrations.\n";
                
                foreach ($pending as $migration) {
                    try {
                        if ($this->applySingleMigration($migration)) {
                            $result['applied_migrations'][] = $migration;
                        }
                    } catch (\Exception $e) {
                        $errorMsg = "Failed to apply migration {$migration}: " . $e->getMessage();
                        echo "ERROR: {$errorMsg}\n";
                        $result['errors'][] = $errorMsg;
                    }
                }
            } else {
                echo "No pending migrations to apply.\n";
            }
            
        } catch (\Exception $e) {
            $errorMsg = "Automatic update failed: " . $e->getMessage();
            echo "ERROR: {$errorMsg}\n";
            $result['errors'][] = $errorMsg;
        }
        
        
        return $result;
    }

    /**
     * Показывает статус миграций (примененные и ожидающие)
     * 
     * @param bool $return Если true, возвращает данные вместо вывода
     * @return array|null Массив с данными или null
     */
    public function showMigrationStatus($return = false)
    {
        $applied = $this->getAppliedMigrations();
        $pending = $this->getPendingMigrations();
        
        if ($return) {
            return [
                'applied' => $applied,
                'pending' => $pending,
                'total_applied' => count($applied),
                'total_pending' => count($pending),
                'total' => count($applied) + count($pending)
            ];
        }
        
        // Старый вывод (закомментирован)
        /*
        echo "Applied migrations: " . count($applied) . "\n";
        foreach ($applied as $migration) {
            echo "  ✓ {$migration}\n";
        }
        
        echo "\nPending migrations: " . count($pending) . "\n";
        if (empty($pending)) {
            echo "  (none)\n";
        } else {
            foreach ($pending as $migration) {
                echo "  ● {$migration}\n";
            }
        }
        
        echo "\nTotal: " . (count($applied) + count($pending)) . " migrations\n";
        */
    }

    /**
     * Применяет все ожидающие миграции вручную
     * 
     * @return void
     */
    public function applyPendingMigrations()
    {
        $pending = $this->getPendingMigrations();
        
        if (empty($pending)) {
            echo "No pending migrations to apply.\n";
            return;
        }
        
        echo "Applying " . count($pending) . " pending migrations:\n";
        
        foreach ($pending as $migration) {
            try {
                $this->applySingleMigration($migration);
            } catch (\Exception $e) {
                echo "ERROR: Failed to apply migration {$migration}: " . $e->getMessage() . "\n";
                break;
            }
        }
    }

    /**
     * Создает сиды (тестовые данные) для всех таблиц базы данных
     * Для каждой таблицы создается отдельный SQL-файл с данными
     * Перед созданием новых сидов старые сиды для тех же таблиц удаляются
     * По умолчанию таблица очищается перед вставкой новых данных (TRUNCATE)
     * 
     * @param array $options Опции генерации сидов:
     *   - tables: массив таблиц для обработки (пустой = все таблицы)
     *   - max_rows_per_table: максимальное количество строк на таблицу (по умолчанию 100)
     *   - skip_tables: таблицы для пропуска (по умолчанию пропускается таблица миграций)
     *   - truncate_before_insert: очищать таблицу перед вставкой (по умолчанию true)
     *   - disable_foreign_keys: отключать проверку внешних ключей (по умолчанию true)
     *   - delete_old_seeds: удалять старые сиды для тех же таблиц (по умолчанию true)
     * 
     * @return array Список созданных файлов сидов
     */
    public function createSeedsFromTables($options = [])
    {
        $defaultOptions = [
            'tables' => [], // Пустой массив = все таблицы
            'max_rows_per_table' => 100,
            'skip_tables' => [$this->migrationsTable],
            'truncate_before_insert' => true,
            'disable_foreign_keys' => true,
            'delete_old_seeds' => true // Новая опция: удалять старые сиды
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        echo "Creating seeds from tables...\n";
        
        $allTables = $this->pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $targetTables = empty($options['tables']) ? $allTables : $options['tables'];
        $targetTables = array_diff($targetTables, $options['skip_tables']);
        
        $createdSeeds = [];
        
        foreach ($targetTables as $table) {
            echo "Processing table: {$table}\n";
            
            // Удаляем старые сиды для этой таблицы, если опция включена
            if ($options['delete_old_seeds']) {
                $deleted = $this->deleteOldSeedsForTable($table);
                if ($deleted > 0) {
                    echo "  Deleted {$deleted} old seed file(s) for table {$table}\n";
                }
            }
            
            $seedFile = $this->createSeedFromTable($table, $options);
            if ($seedFile) {
                $createdSeeds[] = $seedFile;
            }
        }
        
        echo "\nCreated " . count($createdSeeds) . " new seed files.\n";
        return $createdSeeds;
    }

    /**
     * Создает сид для конкретной таблицы
     * Перед созданием нового сида удаляет старые сиды для этой таблицы
     * 
     * @param string $table Название таблицы
     * @param array $options Опции:
     *   - max_rows: максимальное количество строк (по умолчанию 100)
     *   - where: условие WHERE для выборки данных
     *   - order_by: сортировка результатов
     *   - truncate_before_insert: очищать таблицу перед вставкой
     *   - disable_foreign_keys: отключать проверку внешних ключей
     *   - delete_old_seeds: удалять старые сиды для этой таблицы (по умолчанию true)
     * 
     * @return string|null Имя созданного файла сида или null при ошибке
     */
    public function createSeedFromTable($table, $options = [])
    {
        $defaultOptions = [
            'max_rows' => 100,
            'where' => '',
            'order_by' => '',
            'truncate_before_insert' => true,
            'disable_foreign_keys' => true,
            'delete_old_seeds' => true // Новая опция
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // Удаляем старые сиды для этой таблицы, если опция включена
        if ($options['delete_old_seeds']) {
            $deleted = $this->deleteOldSeedsForTable($table);
            if ($deleted > 0) {
                echo "  Deleted {$deleted} old seed file(s) for table {$table}\n";
            }
        }
        
        $columns = $this->getTableColumnsForSeed($table);
        if (empty($columns)) {
            echo "  Skipping (no columns)\n";
            return null;
        }
        
        $data = $this->getTableData($table, $columns, $options);
        
        if (empty($data)) {
            echo "  No data found\n";
            return null;
        }
        
        $seedSQL = $this->generateSeedSQLForTable($table, $columns, $data, $options);
        
        $timestamp = date('YmdHis');
        $seedName = "seed_table_{$table}";
        $filename = "{$timestamp}_{$seedName}.sql";
        $path = $this->seedsDir . $filename;
        
        file_put_contents($path, $seedSQL);
        
        echo "  Created new seed: {$filename} (" . count($data) . " rows)\n";
        return $filename;
    }

    /**
     * Удаляет старые сиды для указанной таблицы
     * Ищет файлы с паттерном "*seed_table_{table}*.sql" и удаляет их
     * 
     * @param string $table Название таблицы
     * @return int Количество удаленных файлов
     */
    public function deleteOldSeedsForTable($table)
    {
        $deletedCount = 0;
        $pattern = "*seed_table_{$table}*.sql";
        
        $files = glob($this->seedsDir . $pattern);
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if (unlink($file)) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount;
    }

    /**
     * Удаляет все сиды для указанных таблиц
     * 
     * @param array $tables Массив названий таблиц
     * @return int Общее количество удаленных файлов
     */
    public function deleteSeedsForTables($tables)
    {
        $totalDeleted = 0;
        
        foreach ($tables as $table) {
            $deleted = $this->deleteOldSeedsForTable($table);
            $totalDeleted += $deleted;
            
            if ($deleted > 0) {
                echo "Deleted {$deleted} seed file(s) for table: {$table}\n";
            }
        }
        
        return $totalDeleted;
    }

    /**
     * Показывает список всех сидов сгруппированных по таблицам
     * 
     * @return void
     */
    public function listSeedsByTable()
    {
        $seeds = $this->getSeedFiles();
        
        if (empty($seeds)) {
            echo "No seed files found.\n";
            return;
        }
        
        $seedsByTable = [];
        
        foreach ($seeds as $seed) {
            // Пытаемся определить таблицу из имени файла
            if (preg_match('/seed_table_([a-zA-Z0-9_]+)/', $seed, $matches)) {
                $table = $matches[1];
            } else {
                $table = 'unknown';
            }
            
            if (!isset($seedsByTable[$table])) {
                $seedsByTable[$table] = [];
            }
            
            $seedsByTable[$table][] = $seed;
        }
        
        ksort($seedsByTable);
        
        echo "Seeds grouped by table:\n";
        echo "=======================\n\n";
        
        foreach ($seedsByTable as $table => $tableSeeds) {
            echo "Table: {$table}\n";
            echo str_repeat('-', strlen($table) + 7) . "\n";
            
            sort($tableSeeds);
            foreach ($tableSeeds as $seed) {
                $filePath = $this->seedsDir . $seed;
                $fileSize = filesize($filePath);
                $fileDate = date('Y-m-d H:i:s', filemtime($filePath));
                
                echo "  ● {$seed}\n";
                echo "    Size: " . $this->formatBytes($fileSize) . ", Created: {$fileDate}\n";
            }
            echo "\n";
        }
        
        echo "Total: " . count($seeds) . " seed file(s) for " . count($seedsByTable) . " table(s)\n";
    }

    /**
     * Форматирует размер файла в читаемый вид
     */
    public function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Получает текущую структуру базы данных
     */
    public function getDatabaseStructure()
    {
        $structure = [
            'tables' => [],
            'version' => date('Y-m-d H:i:s'),
            'checksum' => ''
        ];
        
        $tables = $this->pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            if ($table === $this->migrationsTable) {
                continue;
            }
            
            $createTable = $this->pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
            $structure['tables'][$table] = [
                'create_sql' => $createTable['Create Table'],
                'columns' => $this->getTableColumns($table)
            ];
        }
        
        $structure['checksum'] = md5(serialize($structure['tables']));
        
        return $structure;
    }

    /**
     * Получает информацию о колонках таблицы
     */
    public function getTableColumns($table)
    {
        $columns = [];
        $result = $this->pdo->query("DESCRIBE `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($result as $row) {
            $columns[$row['Field']] = [
                'type' => $row['Type'],
                'null' => $row['Null'],
                'key' => $row['Key'],
                'default' => $row['Default'],
                'extra' => $row['Extra']
            ];
        }
        
        return $columns;
    }

    /**
     * Загружает предыдущую структуру БД из файла
     */
    public function loadPreviousStructure()
    {
        if (!file_exists($this->structureFile)) {
            return null;
        }
        
        $content = file_get_contents($this->structureFile);
        return json_decode($content, true);
    }

    /**
     * Сохраняет структуру БД в файл
     */
    public function saveStructure($structure)
    {
        file_put_contents($this->structureFile, json_encode($structure, JSON_PRETTY_PRINT));
    }

    /**
     * Сравнивает две структуры БД и возвращает SQL для изменений
     */
    public function compareStructures($old, $new)
    {
        $sql = '';
        $description = [];
        
        $oldTables = array_keys($old['tables'] ?? []);
        $newTables = array_keys($new['tables'] ?? []);
        
        // Находим новые таблицы
        $addedTables = array_diff($newTables, $oldTables);
        foreach ($addedTables as $table) {
            $sql .= $new['tables'][$table]['create_sql'] . ";\n\n";
            $description[] = "Added table: {$table}";
        }
        
        // Находим удаленные таблицы
        $removedTables = array_diff($oldTables, $newTables);
        foreach ($removedTables as $table) {
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n\n";
            $description[] = "Removed table: {$table}";
        }
        
        // Проверяем изменения в существующих таблицах
        $commonTables = array_intersect($oldTables, $newTables);
        foreach ($commonTables as $table) {
            $tableChanges = $this->compareTables(
                $old['tables'][$table],
                $new['tables'][$table],
                $table
            );
            
            if (!empty($tableChanges['sql'])) {
                $sql .= $tableChanges['sql'] . "\n";
                $description = array_merge($description, $tableChanges['description']);
            }
        }
        
        return [
            'sql' => $sql,
            'description' => $description
        ];
    }

    /**
     * Сравнивает две версии одной таблицы
     */
    public function compareTables($oldTable, $newTable, $tableName)
    {
        $sql = '';
        $description = [];
        
        $oldColumns = array_keys($oldTable['columns'] ?? []);
        $newColumns = array_keys($newTable['columns'] ?? []);
        
        // Добавленные колонки
        $addedColumns = array_diff($newColumns, $oldColumns);
        foreach ($addedColumns as $column) {
            $columnInfo = $newTable['columns'][$column];
            $sql .= "ALTER TABLE `{$tableName}` ADD COLUMN `{$column}` {$columnInfo['type']}";
            if ($columnInfo['null'] === 'NO') {
                $sql .= " NOT NULL";
            }
            if ($columnInfo['default'] !== null) {
                $default = $columnInfo['default'];
                $sql .= " DEFAULT " . ($default === 'CURRENT_TIMESTAMP' ? $default : "'{$default}'");
            }
            if (!empty($columnInfo['extra'])) {
                $sql .= " " . $columnInfo['extra'];
            }
            $sql .= ";\n";
            $description[] = "Added column: {$tableName}.{$column}";
        }
        
        // Удаленные колонки
        $removedColumns = array_diff($oldColumns, $newColumns);
        foreach ($removedColumns as $column) {
            $sql .= "ALTER TABLE `{$tableName}` DROP COLUMN `{$column}`;\n";
            $description[] = "Removed column: {$tableName}.{$column}";
        }
        
        // Измененные колонки
        $commonColumns = array_intersect($oldColumns, $newColumns);
        foreach ($commonColumns as $column) {
            if ($this->isColumnChanged($oldTable['columns'][$column], $newTable['columns'][$column])) {
                $columnInfo = $newTable['columns'][$column];
                $sql .= "ALTER TABLE `{$tableName}` MODIFY COLUMN `{$column}` {$columnInfo['type']}";
                if ($columnInfo['null'] === 'NO') {
                    $sql .= " NOT NULL";
                }
                if ($columnInfo['default'] !== null) {
                    $default = $columnInfo['default'];
                    $sql .= " DEFAULT " . ($default === 'CURRENT_TIMESTAMP' ? $default : "'{$default}'");
                }
                if (!empty($columnInfo['extra'])) {
                    $sql .= " " . $columnInfo['extra'];
                }
                $sql .= ";\n";
                $description[] = "Modified column: {$tableName}.{$column}";
            }
        }
        
        return [
            'sql' => $sql,
            'description' => $description
        ];
    }

    /**
     * Проверяет, изменилась ли колонка
     */
    public function isColumnChanged($oldColumn, $newColumn)
    {
        return $oldColumn['type'] !== $newColumn['type'] ||
               $oldColumn['null'] !== $newColumn['null'] ||
               $oldColumn['default'] !== $newColumn['default'] ||
               $oldColumn['extra'] !== $newColumn['extra'];
    }

    /**
     * Генерирует имя миграции на основе изменений
     */
    public function generateMigrationName($changes)
    {
        $descriptions = $changes['description'];
        $name = '';
        
        if (count($descriptions) === 1) {
            $desc = strtolower($descriptions[0]);
            $desc = preg_replace('/[^a-z0-9_]+/', '_', $desc);
            $desc = trim($desc, '_');
            $name = $desc;
        } else {
            $keywords = [];
            foreach ($descriptions as $desc) {
                if (strpos($desc, 'Added table') !== false) {
                    $keywords[] = 'add_table';
                } elseif (strpos($desc, 'Removed table') !== false) {
                    $keywords[] = 'drop_table';
                } elseif (strpos($desc, 'Added column') !== false) {
                    $keywords[] = 'add_column';
                } elseif (strpos($desc, 'Removed column') !== false) {
                    $keywords[] = 'drop_column';
                } elseif (strpos($desc, 'Modified column') !== false) {
                    $keywords[] = 'modify_column';
                }
            }
            
            $keywords = array_unique($keywords);
            $name = implode('_', $keywords);
            if (count($keywords) > 3) {
                $name = 'multiple_changes';
            }
        }
        
        if (strlen($name) < 5) {
            $name = 'change_' . $name;
        }
        
        return $name;
    }

    /**
     * Генерирует уникальное имя миграции
     */
    public function generateUniqueMigrationName($baseName, $existingMigrations)
    {
        $counter = 1;
        $newName = $baseName;
        
        while (in_array($newName, $existingMigrations)) {
            $newName = $baseName . '_' . $counter;
            $counter++;
        }
        
        return $newName;
    }

    /**
     * Получает все имена миграций
     */
    public function getAllMigrationNames()
    {
        $allFiles = scandir($this->migrationsDir);
        $names = [];
        
        foreach ($allFiles as $file) {
            if (preg_match('/^\d+_(.+?)\.sql$/', $file, $matches)) {
                $names[] = $matches[1];
            }
        }
        
        return $names;
    }

    /**
     * Применяет одну миграцию
     */
    public function applySingleMigration($migration)
    {
        try {
            if ($this->isMigrationApplied($migration)) {
                echo "Migration {$migration} is already applied. Skipping.\n";
                return false;
            }
            
            $path = $this->migrationsDir . $migration;
            if (!file_exists($path)) {
                throw new \RuntimeException("Migration file not found: {$path}");
            }
            
            $sql = file_get_contents($path);
            
            echo "Applying migration: {$migration}\n";
            echo "SQL to execute:\n";
            echo "---\n" . $sql . "\n---\n";
            
            $this->pdo->beginTransaction();
            
            try {
                // Разбиваем SQL на отдельные запросы
                $queries = $this->splitSQL($sql);
                
                echo "Found " . count($queries) . " queries to execute.\n";
                
                foreach ($queries as $index => $query) {
                    $query = trim($query);
                    if (!empty($query) && strpos($query, '--') !== 0) {
                        echo "  [" . ($index + 1) . "/" . count($queries) . "] Executing: ";
                        
                        // Определяем тип запроса для лучшего логирования
                        if (preg_match('/^(CREATE|ALTER|DROP|INSERT|UPDATE|DELETE|TRUNCATE|RENAME)/i', $query, $matches)) {
                            echo $matches[1] . " ...";
                        } else {
                            echo "SQL query ...";
                        }
                        
                        echo "\n";
                        
                        // Выполняем запрос
                        $result = $this->pdo->exec($query);
                        
                        if ($result === false) {
                            $errorInfo = $this->pdo->errorInfo();
                            throw new \RuntimeException("Query failed: " . ($errorInfo[2] ?? 'Unknown error'));
                        }
                        
                        echo "    ✓ Query executed successfully\n";
                    }
                }
                
                // Отмечаем как применённую
                $this->recordMigrationAsApplied($migration);
                $this->pdo->commit();
                
                echo "✓ Migration {$migration} applied successfully\n";
                return true;
                
            } catch (\Exception $e) {
                $this->pdo->rollBack();
                throw new \RuntimeException(
                    "Failed to apply migration {$migration} (transaction rolled back): " . $e->getMessage()
                );
            }
            
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Failed to apply migration {$migration}: " . $e->getMessage()
            );
        }
    }

    /**
     * Разбивает SQL на отдельные запросы с учётом строковых литералов
     */
    private function splitSQL($sql)
    {
        $queries = [];
        $currentQuery = '';
        $inString = false;
        $stringChar = '';
        $escaped = false;
        
        $sql = str_replace(["\r\n", "\r"], "\n", $sql);
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            $nextChar = $i < strlen($sql) - 1 ? $sql[$i + 1] : '';
            
            if (!$inString && $char === '-' && $nextChar === '-') {
                // Пропускаем однострочный комментарий
                while ($i < strlen($sql) && $sql[$i] !== "\n") {
                    $i++;
                }
                continue;
            }
            
            if (!$inString && $char === '/' && $nextChar === '*') {
                // Пропускаем многострочный комментарий
                $i += 2;
                while ($i < strlen($sql) - 1 && !($sql[$i] === '*' && $sql[$i + 1] === '/')) {
                    $i++;
                }
                $i++; // Пропускаем последний символ
                continue;
            }
            
            if (!$escaped && ($char === "'" || $char === '"' || $char === '`')) {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char === $stringChar) {
                    $inString = false;
                }
            }
            
            if ($char === '\\' && !$escaped) {
                $escaped = true;
            } else {
                $escaped = false;
            }
            
            if (!$inString && $char === ';') {
                $query = trim($currentQuery);
                if (!empty($query)) {
                    $queries[] = $query;
                }
                $currentQuery = '';
                continue;
            }
            
            $currentQuery .= $char;
        }
        
        // Последний запрос
        $lastQuery = trim($currentQuery);
        if (!empty($lastQuery)) {
            $queries[] = $lastQuery;
        }
        
        return $queries;
    }

    /**
     * Проверяет, применена ли миграция
     */
    public function isMigrationApplied($migration)
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) as count FROM {$this->migrationsTable} WHERE migration_name = ?"
            );
            $stmt->execute([$migration]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return ($result['count'] > 0);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Записывает миграцию как примененную
     */
    public function recordMigrationAsApplied($migration)
    {
        try {
            // Проверяем, не применена ли уже миграция
            if ($this->isMigrationApplied($migration)) {
                // Если уже применена, просто возвращаем true
                return true;
            }
            
            $stmt = $this->pdo->prepare(
                "INSERT INTO {$this->migrationsTable} (migration_name) VALUES (?)"
            );
            $stmt->execute([$migration]);
            
            if ($stmt->rowCount() === 0) {
                throw new \RuntimeException("Failed to record migration {$migration} as applied");
            }
            
            return true;
        } catch (\PDOException $e) {
            // Если это дубликат (уже применена), игнорируем ошибку
            if ($e->errorInfo[1] == 1062) {
                return true;
            }
            throw $e;
        }
    }

    /**
     * Получает список всех примененных миграций
     */
    public function getAppliedMigrations()
    {
        try {
            $stmt = $this->pdo->query("SELECT migration_name FROM {$this->migrationsTable} ORDER BY migration_name");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Получает список всех файлов миграций, которые еще не были применены
     */
    public function getPendingMigrations()
    {
        $applied = $this->getAppliedMigrations();
        $allFiles = scandir($this->migrationsDir);
        
        $pending = array_filter($allFiles, function($file) use ($applied) {
            $isMigrationFile = preg_match('/^\d+_.+\.sql$/', $file);
            return $isMigrationFile && !in_array($file, $applied);
        });
        
        return array_values($pending);
    }

    /**
     * Получает информацию о колонках таблицы для генерации сидов
     */
    public function getTableColumnsForSeed($table)
    {
        try {
            $columns = [];
            $result = $this->pdo->query("DESCRIBE `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($result as $row) {
                $column = [
                    'name' => $row['Field'],
                    'type' => $row['Type'],
                    'null' => $row['Null'] === 'YES',
                    'key' => $row['Key'],
                    'default' => $row['Default'],
                    'extra' => $row['Extra'],
                    'auto_increment' => strpos($row['Extra'], 'auto_increment') !== false
                ];
                
                $columns[] = $column;
            }
            
            return $columns;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Получает данные из таблицы
     */
    public function getTableData($table, $columns, $options)
    {
        $data = [];
        
        try {
            $query = "SELECT * FROM `{$table}`";
            
            if (!empty($options['where'])) {
                $query .= " WHERE " . $options['where'];
            }
            
            if (!empty($options['order_by'])) {
                $query .= " ORDER BY " . $options['order_by'];
            }
            
            $limit = $options['max_rows'] ?? 100;
            $query .= " LIMIT {$limit}";
            
            $stmt = $this->pdo->query($query);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
            
        } catch (\Exception $e) {
            echo "  Error reading data from table {$table}: " . $e->getMessage() . "\n";
            return [];
        }
        
        return $data;
    }

    /**
     * Генерирует SQL для сида таблицы
     */
    public function generateSeedSQLForTable($table, $columns, $data, $options)
    {
        $sql = "-- Seed data for table: {$table}\n";
        $sql .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Rows: " . count($data) . "\n\n";
        
        if ($options['disable_foreign_keys']) {
            $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        }
        
        if ($options['truncate_before_insert']) {
            $sql .= "TRUNCATE TABLE `{$table}`;\n\n";
        }
        
        $columnsToUse = [];
        foreach ($columns as $column) {
            $colName = $column['name'];
            $hasNonNullValue = false;
            
            foreach ($data as $row) {
                if (isset($row[$colName]) && $row[$colName] !== null) {
                    $hasNonNullValue = true;
                    break;
                }
            }
            
            if ($hasNonNullValue) {
                $columnsToUse[] = $colName;
            }
        }
        
        $insertStatements = [];
        foreach ($data as $row) {
            $values = [];
            
            foreach ($columnsToUse as $column) {
                $value = isset($row[$column]) ? $row[$column] : null;
                
                if ($value === null) {
                    $values[] = 'NULL';
                } elseif (is_numeric($value) && !is_string($value)) {
                    $values[] = $value;
                } else {
                    $escapedValue = str_replace(
                        ["\\", "'", "\0", "\n", "\r", "\x1a"],
                        ["\\\\", "''", "\\0", "\\n", "\\r", "\\Z"],
                        $value
                    );
                    $values[] = "'{$escapedValue}'";
                }
            }
            
            $columnNames = array_map(function($col) {
                return "`{$col}`";
            }, $columnsToUse);
            
            $insertStatements[] = "INSERT INTO `{$table}` (" . implode(', ', $columnNames) . ") VALUES (" . implode(', ', $values) . ");";
        }
        
        if (count($insertStatements) <= 50) {
            $firstInsert = $insertStatements[0];
            if (preg_match('/^INSERT\s+INTO\s+`[^`]+`\s*\([^)]+\)\s+VALUES/i', $firstInsert)) {
                $prefix = substr($firstInsert, 0, strrpos($firstInsert, 'VALUES') + 6);
                
                $allValues = [];
                foreach ($insertStatements as $insert) {
                    $valuesPart = substr($insert, strrpos($insert, 'VALUES') + 7, -1);
                    $allValues[] = $valuesPart;
                }
                
                $sql .= $prefix . "\n  " . implode(",\n  ", $allValues) . ";\n";
            } else {
                $sql .= implode("\n", $insertStatements) . "\n";
            }
        } else {
            $sql .= implode("\n", $insertStatements) . "\n";
        }
        
        if ($options['disable_foreign_keys']) {
            $sql .= "\nSET FOREIGN_KEY_CHECKS = 1;\n";
        }
        
        return $sql;
    }

    /**
     * Применяет все доступные сиды (тестовые данные)
     * Перед вставкой данных таблицы очищаются (TRUNCATE)
     * Можно фильтровать сиды по имени файла
     * 
     * @param string|null $filter Фильтр по имени файла сида (часть имени)
     *                           Например: 'users' - применит все сиды, содержащие 'users' в имени
     */
    public function applySeeds($filter = null)
    {
        $this->ensureSeedsDirectoryExists();
        $seeds = $this->getSeedFiles();
        
        if ($filter) {
            $seeds = array_filter($seeds, function($seed) use ($filter) {
                return stripos($seed, $filter) !== false;
            });
        }
        
        sort($seeds);
        
        if (empty($seeds)) {
            echo "No seed files found.\n";
            return;
        }
        
        echo "Applying " . count($seeds) . " seed file(s):\n";
        
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        $appliedCount = 0;
        
        foreach ($seeds as $seed) {
            try {
                $result = $this->applySingleSeed($seed);
                $appliedCount += $result['applied'];
                echo "  Applied seed: {$seed} (" . $result['applied'] . " rows)\n";
            } catch (\Exception $e) {
                echo "  ERROR: Failed to apply seed {$seed}: " . $e->getMessage() . "\n";
            }
        }
        
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        echo "\nApplied {$appliedCount} total rows from seeds.\n";
    }

    /**
     * Получает список файлов сидов
     */
    public function getSeedFiles()
    {
        $allFiles = scandir($this->seedsDir);
        return array_filter($allFiles, fn($f) => preg_match('/^\d+_.+\.sql$/', $f));
    }

    /**
     * Применяет один сид (упрощенная версия без processInsertQuery)
     */
    public function applySingleSeed($seed)
    {
        $path = $this->seedsDir . $seed;
        $sql = file_get_contents($path);
        
        if (trim($sql) === '') {
            throw new \RuntimeException("Seed file {$seed} is empty");
        }
        
        $result = ['applied' => 0];
        
        // Удаляем комментарии
        $sql = preg_replace('/--.*?$|#.*?$|\/\*.*?\*\//ms', '', $sql);
        
        // Разбиваем на запросы
        $queries = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        $escaped = false;
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            
            if (!$inString && $char === ';') {
                $query = trim($current);
                if (!empty($query)) {
                    $queries[] = $query;
                }
                $current = '';
                continue;
            }
            
            if (!$escaped && ($char === "'" || $char === '"' || $char === '`')) {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } else if ($char === $stringChar) {
                    $inString = false;
                }
            }
            
            if ($char === '\\' && !$escaped) {
                $escaped = true;
            } else {
                $escaped = false;
            }
            
            $current .= $char;
        }
        
        // Последний запрос
        $lastQuery = trim($current);
        if (!empty($lastQuery)) {
            $queries[] = $lastQuery;
        }
        
        // Выполняем запросы
        foreach ($queries as $query) {
            if (empty(trim($query)) || strpos(trim($query), '--') === 0) {
                continue;
            }
            
            try {
                $this->pdo->exec($query);
                if (preg_match('/^INSERT/i', trim($query))) {
                    $result['applied']++;
                }
            } catch (\PDOException $e) {
                // Если ошибка дубликата, пропускаем
                if ($e->errorInfo[1] == 1062) {
                    continue;
                }
                throw $e;
            }
        }
        
        return $result;
    }

    /**
     * Получить список таблиц базы данных
     */
    public function getDatabaseTables(): array
    {
        try {
            $result = $this->pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            return array_values($result);
        } catch (\Exception $e) {
            error_log("Error getting database tables: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Получить подробную информацию о таблицах (количество записей, размер и т.д.)
     */
    public function getTablesInfo(): array
    {
        $tables = $this->getDatabaseTables();
        $info = [];
        
        foreach ($tables as $table) {
            try {
                // Количество записей
                $countResult = $this->pdo->query("SELECT COUNT(*) as count FROM `{$table}`")->fetch(PDO::FETCH_ASSOC);
                $rowCount = $countResult['count'] ?? 0;
                
                // Информация о таблице
                $tableInfo = $this->pdo->query("SHOW TABLE STATUS LIKE '{$table}'")->fetch(PDO::FETCH_ASSOC);
                
                $info[$table] = [
                    'name' => $table,
                    'rows' => (int)$rowCount,
                    'engine' => $tableInfo['Engine'] ?? '',
                    'collation' => $tableInfo['Collation'] ?? '',
                    'data_size' => $this->formatBytes($tableInfo['Data_length'] ?? 0),
                    'index_size' => $this->formatBytes($tableInfo['Index_length'] ?? 0),
                    'total_size' => $this->formatBytes(($tableInfo['Data_length'] ?? 0) + ($tableInfo['Index_length'] ?? 0)),
                    'created' => $tableInfo['Create_time'] ?? '',
                    'updated' => $tableInfo['Update_time'] ?? '',
                    'comment' => $tableInfo['Comment'] ?? ''
                ];
            } catch (\Exception $e) {
                $info[$table] = [
                    'name' => $table,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $info;
    }

    /**
     * Получить структуру конкретной таблицы
     */
    public function getTableStructure(string $tableName): array
    {
        try {
            $columns = $this->pdo->query("DESCRIBE `{$tableName}`")->fetchAll(PDO::FETCH_ASSOC);
            
            // Получаем индексы
            $indexes = $this->pdo->query("SHOW INDEX FROM `{$tableName}`")->fetchAll(PDO::FETCH_ASSOC);
            
            // Получаем внешние ключи
            $foreignKeys = $this->getForeignKeys($tableName);
            
            return [
                'columns' => $columns,
                'indexes' => $indexes,
                'foreign_keys' => $foreignKeys,
                'create_sql' => $this->getCreateTableSql($tableName)
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Получить внешние ключи таблицы
     */
    public function getForeignKeys(string $tableName): array
    {
        try {
            $sql = "
                SELECT 
                    CONSTRAINT_NAME, 
                    COLUMN_NAME, 
                    REFERENCED_TABLE_NAME, 
                    REFERENCED_COLUMN_NAME,
                    UPDATE_RULE,
                    DELETE_RULE
                FROM 
                    INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE 
                    TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = :table_name 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':table_name' => $tableName]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Получить SQL для создания таблицы
     */
    public function getCreateTableSql(string $tableName): string
    {
        try {
            $result = $this->pdo->query("SHOW CREATE TABLE `{$tableName}`")->fetch(PDO::FETCH_ASSOC);
            return $result['Create Table'] ?? '';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Проверить, существует ли таблица
     */
    public function tableExists(string $tableName): bool
    {
        try {
            $sql = "
                SELECT COUNT(*) as count 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = :table_name
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':table_name' => $tableName]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return ($result['count'] ?? 0) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Экспортировать структуру БД в SQL файл
     */
    public function exportDatabaseStructure(?string $filename = null): string
    {
        if ($filename === null) {
            $filename = $this->migrationsDir . 'database_structure_' . date('Y-m-d_H-i-s') . '.sql';
        }
        
        $tables = $this->getDatabaseTables();
        $sql = "-- Database Structure Export\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: " . $this->pdo->query("SELECT DATABASE()")->fetchColumn() . "\n\n";
        
        foreach ($tables as $table) {
            $sql .= "-- Table: {$table}\n";
            $sql .= $this->getCreateTableSql($table) . ";\n\n";
        }
        
        file_put_contents($filename, $sql);
        
        return $filename;
    }

    /**
     * Получить статистику по миграциям
     */
    public function getMigrationStats(): array
    {
        $allMigrations = $this->getAllMigrationNames();
        $appliedMigrations = $this->getAppliedMigrations();
        $pendingMigrations = $this->getPendingMigrations();
        
        return [
            'total_migrations' => count($allMigrations),
            'applied_migrations' => count($appliedMigrations),
            'pending_migrations' => count($pendingMigrations),
            'last_applied' => !empty($appliedMigrations) ? end($appliedMigrations) : null,
            'first_pending' => !empty($pendingMigrations) ? reset($pendingMigrations) : null
        ];
    }

    /**
     * Откатить последнюю применённую миграцию
     * 
     * @return bool Успешность операции
     */
    public function rollbackLastMigration()
    {
        try {
            // Получаем последнюю применённую миграцию
            $stmt = $this->pdo->query(
                "SELECT migration_name FROM {$this->migrationsTable} ORDER BY applied_at DESC, id DESC LIMIT 1"
            );
            $lastMigration = $stmt->fetch(PDO::FETCH_COLUMN);
            
            if (!$lastMigration) {
                echo "Нет применённых миграций для отката.\n";
                return false;
            }
            
            echo "Откат миграции: {$lastMigration}\n";
            
            // Получаем SQL из файла миграции
            $path = $this->migrationsDir . $lastMigration;
            if (!file_exists($path)) {
                echo "Файл миграции не найден: {$lastMigration}\n";
                return false;
            }
            
            $migrationSql = file_get_contents($path);
            
            // Генерируем SQL для отката (инвертируем операции)
            $rollbackSql = $this->generateRollbackSQL($migrationSql, $lastMigration);
            
            if (empty($rollbackSql)) {
                echo "Не удалось сгенерировать SQL для отката.\n";
                echo "Удаляю только запись о миграции из таблицы...\n";
                
                // Удаляем запись о миграции
                $stmt = $this->pdo->prepare(
                    "DELETE FROM {$this->migrationsTable} WHERE migration_name = ?"
                );
                $stmt->execute([$lastMigration]);
                
                echo "Миграция {$lastMigration} отмечена как не применённая.\n";
                echo "ВНИМАНИЕ: SQL изменения НЕ отменены автоматически!\n";
                echo "Для отмены изменений в БД необходимо:\n";
                echo "1. Вручную отменить изменения в БД\n";
                echo "2. Создать новую миграцию для отката\n";
                echo "3. Удалить файл миграции {$lastMigration} если он больше не нужен\n";
                
                return true;
            }
            
            echo "Выполняю SQL отката:\n";
            echo "---\n" . $rollbackSql . "\n---\n";
            
            $this->pdo->beginTransaction();
            try {
                // Выполняем SQL отката
                $queries = $this->splitSQL($rollbackSql);
                
                echo "Found " . count($queries) . " rollback queries to execute.\n";
                
                foreach ($queries as $index => $query) {
                    $query = trim($query);
                    if (!empty($query) && strpos($query, '--') !== 0) {
                        echo "  [" . ($index + 1) . "/" . count($queries) . "] Executing: ";
                        
                        // Определяем тип запроса для лучшего логирования
                        if (preg_match('/^(CREATE|ALTER|DROP|INSERT|UPDATE|DELETE|TRUNCATE|RENAME)/i', $query, $matches)) {
                            echo $matches[1] . " ...";
                        } else {
                            echo "SQL query ...";
                        }
                        
                        echo "\n";
                        
                        // Выполняем запрос
                        $result = $this->pdo->exec($query);
                        
                        if ($result === false) {
                            $errorInfo = $this->pdo->errorInfo();
                            throw new \RuntimeException("Rollback query failed: " . ($errorInfo[2] ?? 'Unknown error'));
                        }
                        
                        echo "    ✓ Query executed successfully\n";
                    }
                }
                
                // Удаляем запись о миграции
                $stmt = $this->pdo->prepare(
                    "DELETE FROM {$this->migrationsTable} WHERE migration_name = ?"
                );
                $stmt->execute([$lastMigration]);
                
                $this->pdo->commit();
                
                echo "✓ Миграция {$lastMigration} успешно откачена.\n";
                return true;
                
            } catch (\Exception $e) {
                $this->pdo->rollBack();
                echo "✗ Ошибка при откате миграции (транзакция отменена): " . $e->getMessage() . "\n";
                
                // Не удаляем запись о миграции, так как откат не удался
                echo "Запись о миграции сохранена, так как откат не удался.\n";
                
                return false;
            }
            
        } catch (\Exception $e) {
            echo "✗ Ошибка при откате миграции: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Генерирует SQL для отката на основе SQL миграции
     */
    public function generateRollbackSQL($migrationSql, $migrationName)
    {
        // Разбиваем SQL на отдельные запросы
        $queries = $this->splitSQL($migrationSql);
        $rollbackQueries = [];
        
        // Анализируем запросы в обратном порядке
        $queries = array_reverse($queries);
        
        foreach ($queries as $query) {
            $trimmedQuery = trim($query);
            
            // Пропускаем комментарии
            if (strpos($trimmedQuery, '--') === 0 || empty($trimmedQuery)) {
                continue;
            }
            
            // Инвертируем операции
            $upperQuery = strtoupper($trimmedQuery);
            
            // CREATE TABLE -> DROP TABLE
            if (preg_match('/^CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?\s+`?([a-zA-Z0-9_]+)`?/i', $trimmedQuery, $matches)) {
                $tableName = $matches[1];
                $rollbackQueries[] = "DROP TABLE IF EXISTS `{$tableName}`;";
            }
            
            // DROP TABLE -> CREATE TABLE (не можем восстановить без структуры)
            elseif (preg_match('/^DROP\s+TABLE(?:\s+IF\s+EXISTS)?\s+`?([a-zA-Z0-9_]+)`?/i', $trimmedQuery, $matches)) {
                $tableName = $matches[1];
                $rollbackQueries[] = "-- WARNING: Cannot automatically recreate dropped table `{$tableName}`";
                $rollbackQueries[] = "-- Original query: " . $trimmedQuery;
            }
            
            // ALTER TABLE ADD COLUMN -> DROP COLUMN
            elseif (preg_match('/ALTER\s+TABLE\s+`?([a-zA-Z0-9_]+)`?\s+ADD\s+(?:COLUMN\s+)?`?([a-zA-Z0-9_]+)`?/i', $trimmedQuery, $matches)) {
                $tableName = $matches[1];
                $columnName = $matches[2];
                $rollbackQueries[] = "ALTER TABLE `{$tableName}` DROP COLUMN `{$columnName}`;";
            }
            
            // ALTER TABLE DROP COLUMN -> не можем восстановить без определения колонки
            elseif (preg_match('/ALTER\s+TABLE\s+`?([a-zA-Z0-9_]+)`?\s+DROP\s+(?:COLUMN\s+)?`?([a-zA-Z0-9_]+)`?/i', $trimmedQuery, $matches)) {
                $tableName = $matches[1];
                $columnName = $matches[2];
                $rollbackQueries[] = "-- WARNING: Cannot automatically recreate dropped column `{$tableName}`.`{$columnName}`";
                $rollbackQueries[] = "-- Original query: " . $trimmedQuery;
            }
            
            // ALTER TABLE MODIFY COLUMN -> не можем восстановить предыдущее состояние
            elseif (preg_match('/ALTER\s+TABLE\s+`?([a-zA-Z0-9_]+)`?\s+MODIFY\s+(?:COLUMN\s+)?`?([a-zA-Z0-9_]+)`?/i', $trimmedQuery, $matches)) {
                $tableName = $matches[1];
                $columnName = $matches[2];
                $rollbackQueries[] = "-- WARNING: Cannot automatically reverse MODIFY COLUMN for `{$tableName}`.`{$columnName}`";
                $rollbackQueries[] = "-- Original query: " . $trimmedQuery;
            }
            
            // ALTER TABLE CHANGE COLUMN -> не можем восстановить
            elseif (preg_match('/ALTER\s+TABLE\s+`?([a-zA-Z0-9_]+)`?\s+CHANGE\s+(?:COLUMN\s+)?`?([a-zA-Z0-9_]+)`?\s+`?([a-zA-Z0-9_]+)`?/i', $trimmedQuery, $matches)) {
                $tableName = $matches[1];
                $oldColumnName = $matches[2];
                $newColumnName = $matches[3];
                $rollbackQueries[] = "-- WARNING: Cannot automatically reverse CHANGE COLUMN for `{$tableName}`.`{$oldColumnName}` -> `{$newColumnName}`";
                $rollbackQueries[] = "-- Original query: " . $trimmedQuery;
            }
            
            // CREATE INDEX -> DROP INDEX
            elseif (preg_match('/CREATE\s+(?:UNIQUE\s+)?INDEX\s+`?([a-zA-Z0-9_]+)`?\s+ON\s+`?([a-zA-Z0-9_]+)`?/i', $trimmedQuery, $matches)) {
                $indexName = $matches[1];
                $tableName = $matches[2];
                $rollbackQueries[] = "DROP INDEX `{$indexName}` ON `{$tableName}`;";
            }
            
            // DROP INDEX -> не можем восстановить
            elseif (preg_match('/DROP\s+INDEX\s+`?([a-zA-Z0-9_]+)`?\s+ON\s+`?([a-zA-Z0-9_]+)`?/i', $trimmedQuery, $matches)) {
                $indexName = $matches[1];
                $tableName = $matches[2];
                $rollbackQueries[] = "-- WARNING: Cannot automatically recreate dropped index `{$indexName}` on `{$tableName}`";
                $rollbackQueries[] = "-- Original query: " . $trimmedQuery;
            }
            
            // INSERT INTO -> DELETE
            elseif (preg_match('/INSERT\s+INTO\s+`?([a-zA-Z0-9_]+)`?(?:\s*\([^)]+\))?\s+VALUES/i', $trimmedQuery, $matches)) {
                $tableName = $matches[1];
                $rollbackQueries[] = "-- WARNING: Cannot automatically reverse INSERT INTO `{$tableName}`";
                $rollbackQueries[] = "-- Original query: " . $trimmedQuery;
            }
            
            // DELETE FROM -> не можем восстановить
            elseif (preg_match('/DELETE\s+FROM\s+`?([a-zA-Z0-9_]+)`?/i', $trimmedQuery, $matches)) {
                $tableName = $matches[1];
                $rollbackQueries[] = "-- WARNING: Cannot automatically reverse DELETE FROM `{$tableName}`";
                $rollbackQueries[] = "-- Original query: " . $trimmedQuery;
            }
            
            // UPDATE -> не можем восстановить
            elseif (preg_match('/UPDATE\s+`?([a-zA-Z0-9_]+)`?\s+SET/i', $trimmedQuery, $matches)) {
                $tableName = $matches[1];
                $rollbackQueries[] = "-- WARNING: Cannot automatically reverse UPDATE `{$tableName}`";
                $rollbackQueries[] = "-- Original query: " . $trimmedQuery;
            }
            
            // TRUNCATE TABLE -> не можем восстановить
            elseif (preg_match('/TRUNCATE\s+TABLE\s+`?([a-zA-Z0-9_]+)`?/i', $trimmedQuery, $matches)) {
                $tableName = $matches[1];
                $rollbackQueries[] = "-- WARNING: Cannot automatically reverse TRUNCATE TABLE `{$tableName}`";
                $rollbackQueries[] = "-- Original query: " . $trimmedQuery;
            }
            
            // Неизвестный/неподдерживаемый запрос
            else {
                $rollbackQueries[] = "-- WARNING: Cannot automatically reverse this query type";
                $rollbackQueries[] = "-- Original query: " . $trimmedQuery;
            }
        }
        
        if (empty($rollbackQueries)) {
            return "";
        }
        
        $rollbackSql = "-- Rollback SQL for migration: {$migrationName}\n";
        $rollbackSql .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n";
        $rollbackSql .= "-- WARNING: Some operations cannot be automatically reversed\n";
        $rollbackSql .= "-- Manual intervention may be required\n\n";
        
        // Удаляем дублирующиеся запросы (например, если DROP TABLE IF EXISTS появляется несколько раз)
        $rollbackQueries = array_unique($rollbackQueries);
        
        $rollbackSql .= implode("\n", $rollbackQueries);
        
        return $rollbackSql;
    }

    /**
     * Применить конкретную миграцию (с проверкой)
     * 
     * @param string $migrationName Имя файла миграции
     * @return bool Успешность операции
     */
    public function applyMigration($migrationName)
    {
        return $this->applySingleMigration($migrationName);
    }

    /**
     * Проверить SQL миграции перед выполнением
     * 
     * @param string $migrationName Имя файла миграции
     * @return array Результат проверки
     */
    public function validateMigration($migrationName)
    {
        $path = $this->migrationsDir . $migrationName;
        
        if (!file_exists($path)) {
            return [
                'valid' => false,
                'error' => "Файл миграции не найден: {$migrationName}"
            ];
        }
        
        $sql = file_get_contents($path);
        
        // Простая проверка SQL
        $queries = array_filter(
            array_map('trim', explode(';', $sql)),
            function($query) { 
                return !empty($query) && strpos(trim($query), '--') !== 0; 
            }
        );
        
        $validation = [
            'valid' => true,
            'file_exists' => true,
            'queries_count' => count($queries),
            'queries' => [],
            'warnings' => []
        ];
        
        foreach ($queries as $index => $query) {
            $queryInfo = [
                'index' => $index + 1,
                'query' => $query,
                'type' => 'UNKNOWN'
            ];
            
            // Определяем тип запроса
            $upperQuery = strtoupper(trim($query));
            if (strpos($upperQuery, 'CREATE TABLE') === 0) {
                $queryInfo['type'] = 'CREATE_TABLE';
            } elseif (strpos($upperQuery, 'ALTER TABLE') === 0) {
                $queryInfo['type'] = 'ALTER_TABLE';
                if (strpos($upperQuery, 'ADD COLUMN') !== false) {
                    $queryInfo['subtype'] = 'ADD_COLUMN';
                } elseif (strpos($upperQuery, 'DROP COLUMN') !== false) {
                    $queryInfo['subtype'] = 'DROP_COLUMN';
                    $validation['warnings'][] = "Запрос #" . ($index + 1) . ": DROP COLUMN - необратимая операция";
                } elseif (strpos($upperQuery, 'MODIFY COLUMN') !== false) {
                    $queryInfo['subtype'] = 'MODIFY_COLUMN';
                }
            } elseif (strpos($upperQuery, 'DROP TABLE') === 0) {
                $queryInfo['type'] = 'DROP_TABLE';
                $validation['warnings'][] = "Запрос #" . ($index + 1) . ": DROP TABLE - необратимая операция";
            } elseif (strpos($upperQuery, 'INSERT INTO') === 0) {
                $queryInfo['type'] = 'INSERT';
            } elseif (strpos($upperQuery, 'UPDATE') === 0) {
                $queryInfo['type'] = 'UPDATE';
            } elseif (strpos($upperQuery, 'DELETE FROM') === 0) {
                $queryInfo['type'] = 'DELETE';
            }
            
            $validation['queries'][] = $queryInfo;
        }
        
        return $validation;
    }

    /**
     * Проверить расхождения между структурой БД и миграциями
     * 
     * @return array Результат проверки
     */
    public function checkDatabaseConsistency()
    {
        $appliedMigrations = $this->getAppliedMigrations();
        $allMigrationFiles = $this->getAllMigrationFiles();
        
        $issues = [];
        
        // Проверяем, все ли примененные миграции существуют как файлы
        foreach ($appliedMigrations as $migration) {
            if (!in_array($migration, $allMigrationFiles)) {
                $issues[] = [
                    'type' => 'warning',
                    'message' => "Миграция отмечена как примененная, но файл не найден: {$migration}"
                ];
            }
        }
        
        // Проверяем, все ли файлы миграций применены
        foreach ($allMigrationFiles as $migrationFile) {
            if (!in_array($migrationFile, $appliedMigrations)) {
                $issues[] = [
                    'type' => 'pending',
                    'message' => "Файл миграции существует, но не применен: {$migrationFile}"
                ];
            }
        }
        
        // Проверяем целостность таблицы миграций
        try {
            $stmt = $this->pdo->query("CHECK TABLE {$this->migrationsTable}");
            $checkResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($checkResult as $row) {
                if ($row['Msg_type'] === 'error') {
                    $issues[] = [
                        'type' => 'error',
                        'message' => "Проблема с таблицей миграций: " . $row['Msg_text']
                    ];
                }
            }
        } catch (\Exception $e) {
            $issues[] = [
                'type' => 'error',
                'message' => "Не удалось проверить таблицу миграций: " . $e->getMessage()
            ];
        }
        
        return [
            'applied_migrations' => count($appliedMigrations),
            'total_migration_files' => count($allMigrationFiles),
            'issues' => $issues,
            'is_consistent' => empty($issues)
        ];
    }

    /**
     * Получить все файлы миграций
     */
    public function getAllMigrationFiles()
    {
        $allFiles = scandir($this->migrationsDir);
        return array_filter($allFiles, function($file) {
            return preg_match('/^\d+_.+\.sql$/', $file);
        });
    }

    /**
     * Принудительно применить миграцию (даже если она уже отмечена как применённая)
     * 
     * @param string $migrationName Имя файла миграции
     * @return bool Успешность операции
     */
    public function forceApplyMigration($migrationName)
    {
        try {
            $path = $this->migrationsDir . $migrationName;
            if (!file_exists($path)) {
                throw new \RuntimeException("Migration file not found: {$path}");
            }
            
            $sql = file_get_contents($path);
            
            echo "Force applying migration: {$migrationName}\n";
            echo "SQL to execute:\n";
            echo "---\n" . $sql . "\n---\n";
            
            $this->pdo->beginTransaction();
            
            try {
                // Разбиваем SQL на отдельные запросы
                $queries = $this->splitSQL($sql);
                
                echo "Found " . count($queries) . " queries to execute.\n";
                
                foreach ($queries as $index => $query) {
                    $query = trim($query);
                    if (!empty($query) && strpos($query, '--') !== 0) {
                        echo "  [" . ($index + 1) . "/" . count($queries) . "] Executing: ";
                        
                        // Определяем тип запроса для лучшего логирования
                        if (preg_match('/^(CREATE|ALTER|DROP|INSERT|UPDATE|DELETE|TRUNCATE|RENAME)/i', $query, $matches)) {
                            echo $matches[1] . " ...";
                        } else {
                            echo "SQL query ...";
                        }
                        
                        echo "\n";
                        
                        // Проверяем, нужно ли выполнять запрос (пропускаем CREATE TABLE IF NOT EXISTS и т.д.)
                        $shouldExecute = true;
                        
                        // Для ALTER TABLE ADD COLUMN - проверяем, существует ли уже колонка
                        if (preg_match('/ALTER\s+TABLE\s+`?([a-zA-Z0-9_]+)`?\s+ADD\s+(?:COLUMN\s+)?`?([a-zA-Z0-9_]+)`?/i', $query, $matches)) {
                            $tableName = $matches[1];
                            $columnName = $matches[2];
                            
                            if ($this->columnExists($tableName, $columnName)) {
                                echo "    ⚠ Column `{$tableName}`.`{$columnName}` already exists. Skipping.\n";
                                $shouldExecute = false;
                            }
                        }
                        
                        // Для CREATE TABLE IF NOT EXISTS - проверяем, существует ли таблица
                        elseif (preg_match('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+`?([a-zA-Z0-9_]+)`?/i', $query, $matches)) {
                            $tableName = $matches[1];
                            
                            if ($this->tableExists($tableName)) {
                                echo "    ⚠ Table `{$tableName}` already exists. Skipping.\n";
                                $shouldExecute = false;
                            }
                        }
                        
                        if ($shouldExecute) {
                            // Выполняем запрос
                            $result = $this->pdo->exec($query);
                            
                            if ($result === false) {
                                $errorInfo = $this->pdo->errorInfo();
                                
                                // Если ошибка "duplicate column" или "duplicate table", пропускаем
                                if (strpos($errorInfo[2] ?? '', 'duplicate') !== false) {
                                    echo "    ⚠ " . ($errorInfo[2] ?? 'Duplicate error') . ". Skipping.\n";
                                } else {
                                    throw new \RuntimeException("Query failed: " . ($errorInfo[2] ?? 'Unknown error'));
                                }
                            } else {
                                echo "    ✓ Query executed successfully\n";
                            }
                        }
                    }
                }
                
                // Принудительно отмечаем как применённую (обновляем или вставляем)
                $this->forceRecordMigrationAsApplied($migrationName);
                $this->pdo->commit();
                
                echo "✓ Migration {$migrationName} force-applied successfully\n";
                return true;
                
            } catch (\Exception $e) {
                $this->pdo->rollBack();
                throw new \RuntimeException(
                    "Failed to force-apply migration {$migrationName} (transaction rolled back): " . $e->getMessage()
                );
            }
            
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Failed to force-apply migration {$migrationName}: " . $e->getMessage()
            );
        }
    }

    /**
     * Принудительно отмечает миграцию как применённую (обновляет или вставляет)
     */
    public function forceRecordMigrationAsApplied($migration)
    {
        try {
            // Сначала пытаемся удалить старую запись (если есть)
            $stmt = $this->pdo->prepare(
                "DELETE FROM {$this->migrationsTable} WHERE migration_name = ?"
            );
            $stmt->execute([$migration]);
            
            // Затем вставляем новую запись
            $stmt = $this->pdo->prepare(
                "INSERT INTO {$this->migrationsTable} (migration_name) VALUES (?)"
            );
            $stmt->execute([$migration]);
            
            if ($stmt->rowCount() === 0) {
                throw new \RuntimeException("Failed to force-record migration {$migration} as applied");
            }
            
            return true;
        } catch (\PDOException $e) {
            // Игнорируем ошибку дубликата (на всякий случай)
            if ($e->errorInfo[1] == 1062) {
                return true;
            }
            throw $e;
        }
    }

    /**
     * Очистить "зависшие" миграции (отмечены как применённые, но изменения не в БД)
     * 
     * @return array Список очищенных миграций
     */
    public function cleanupStuckMigrations()
    {
        $appliedMigrations = $this->getAppliedMigrations();
        $cleaned = [];
        
        echo "Checking for stuck migrations...\n";
        
        foreach ($appliedMigrations as $migration) {
            $path = $this->migrationsDir . $migration;
            
            if (!file_exists($path)) {
                echo "  ⚠ Migration file not found: {$migration}\n";
                continue;
            }
            
            $sql = file_get_contents($path);
            $analysis = $this->analyzeSQL($sql);
            
            $hasIssues = false;
            
            // Проверяем CREATE TABLE
            foreach ($analysis['tables_created'] as $table) {
                if (!$this->tableExists($table)) {
                    echo "  ⚠ Migration {$migration} created table `{$table}` but it doesn't exist\n";
                    $hasIssues = true;
                }
            }
            
            // Проверяем ADD COLUMN
            foreach ($analysis['columns_added'] as $columnInfo) {
                if (!$this->columnExists($columnInfo['table'], $columnInfo['column'])) {
                    echo "  ⚠ Migration {$migration} added column `{$columnInfo['table']}`.`{$columnInfo['column']}` but it doesn't exist\n";
                    $hasIssues = true;
                }
            }
            
            if ($hasIssues) {
                echo "  Removing stuck migration record: {$migration}\n";
                
                $stmt = $this->pdo->prepare(
                    "DELETE FROM {$this->migrationsTable} WHERE migration_name = ?"
                );
                $stmt->execute([$migration]);
                
                $cleaned[] = $migration;
            }
        }
        
        echo "\nCleaned " . count($cleaned) . " stuck migrations.\n";
        return $cleaned;
    }
}

// CLI обработчик с улучшенными командами
if (php_sapi_name() === 'cli') {
    $migrator = new Migrator();
    $command = $argv[1] ?? null;
    
    switch ($command) {
        case 'check':
        case 'auto-check':
            // Проверить изменения БД и сгенерировать миграции
            $migrator->autoCheckAndGenerateMigrations();
            break;
            
        case 'update':
        case 'auto-update':
            // Полностью автоматический процесс обновления БД
            $migrator->autoCheckAndApply();
            break;
            
        case 'migrate':
            // Применить ожидающие миграции вручную
            $migrator->applyPendingMigrations();
            break;
            
        case 'status':
            // Показать статус миграций
            echo "=== Статус миграций ===\n";
            $applied = $migrator->getAppliedMigrations();
            $pending = $migrator->getPendingMigrations();
            
            echo "Применено: " . count($applied) . " миграций\n";
            echo "Ожидает: " . count($pending) . " миграций\n";
            
            if (!empty($pending)) {
                echo "\nОжидающие миграции:\n";
                foreach ($pending as $migration) {
                    echo "  ● {$migration}\n";
                }
            }
            break;
            
        case 'validate':
            // Проверить миграцию
            $migrationName = $argv[2] ?? null;
            if ($migrationName) {
                $result = $migrator->validateMigration($migrationName);
                print_r($result);
            } else {
                echo "Укажите имя миграции: php Migrator.php validate 20240101000000_add_table_users.sql\n";
            }
            break;
            
        case 'apply':
            // Применить конкретную миграцию
            $migrationName = $argv[2] ?? null;
            if ($migrationName) {
                $result = $migrator->applyMigration($migrationName);
                echo $result ? "Миграция применена успешно\n" : "Ошибка при применении миграции\n";
            } else {
                echo "Укажите имя миграции: php Migrator.php apply 20240101000000_add_table_users.sql\n";
            }
            break;
            
        case 'rollback':
            // Откатить последнюю миграцию
            $migrator->rollbackLastMigration();
            break;
            
        case 'rollback-to':
            // Откатить до определенной миграции
            $migrationName = $argv[2] ?? null;
            if ($migrationName) {
                echo "Функция rollback-to пока не реализована\n";
            } else {
                echo "Укажите имя миграции: php Migrator.php rollback-to 20240101000000_add_table_users.sql\n";
            }
            break;
            
        case 'consistency':
            // Проверить целостность
            $result = $migrator->checkDatabaseConsistency();
            print_r($result);
            break;
            
        case 'create-seeds':
            // Создать сиды из данных таблиц (старые сиды удаляются)
            $migrator->createSeedsFromTables();
            break;
            
        case 'seed-single':
            // Создать сид для конкретной таблицы (старые сиды удаляются)
            $table = $argv[2] ?? null;
            if ($table) {
                $migrator->createSeedFromTable($table);
            } else {
                echo "Please specify table name: php Migrator.php seed-single users\n";
            }
            break;
            
        case 'apply-seeds':
            // Применить сиды (очистить таблицы и вставить данные)
            $filter = $argv[2] ?? null;
            $migrator->applySeeds($filter);
            break;
            
        case 'list-seeds':
            // Показать список сидов сгруппированных по таблицам
            $migrator->listSeedsByTable();
            break;
            
        case 'delete-seeds':
            // Удалить сиды для указанных таблиц
            $tables = array_slice($argv, 2);
            if (!empty($tables)) {
                $deleted = $migrator->deleteSeedsForTables($tables);
                echo "Deleted {$deleted} seed file(s) total.\n";
            } else {
                echo "Please specify table names: php Migrator.php delete-seeds users posts\n";
            }
            break;
            
        case 'cleanup-seeds':
            // Удалить ВСЕ сиды
            $allSeeds = $migrator->getSeedFiles();
            $seedsDir = __DIR__ . '/../migrations/seeds/';
            $deleted = 0;
            
            foreach ($allSeeds as $seed) {
                if (unlink($seedsDir . $seed)) {
                    $deleted++;
                }
            }
            
            echo "Deleted {$deleted} seed file(s).\n";
            break;

        case 'force-apply':
            // Принудительно применить миграцию
            $migrationName = $argv[2] ?? null;
            if ($migrationName) {
                $result = $migrator->forceApplyMigration($migrationName);
                echo $result ? "Migration force-applied successfully\n" : "Error force-applying migration\n";
            } else {
                echo "Укажите имя миграции: php Migrator.php force-apply 20240101000000_add_table_users.sql\n";
            }
            break;

        case 'cleanup-stuck':
            // Очистить зависшие миграции
            $result = $migrator->cleanupStuckMigrations();
            if (!empty($result)) {
                echo "Cleaned migrations:\n";
                foreach ($result as $migration) {
                    echo "  - {$migration}\n";
                }
            }
            break;

        case 'reset-migration':
            // Полностью сбросить миграцию (удалить запись и попытаться откатить)
            $migrationName = $argv[2] ?? null;
            if ($migrationName) {
                echo "Resetting migration: {$migrationName}\n";
                
                // 1. Пытаемся откатить
                $path = $migrator->migrationsDir . $migrationName;
                if (file_exists($path)) {
                    $sql = file_get_contents($path);
                    $rollbackSql = $migrator->generateRollbackSQL($sql, $migrationName);
                    
                    if (!empty($rollbackSql)) {
                        echo "Attempting to rollback...\n";
                        $migrator->pdo->beginTransaction();
                        try {
                            $queries = $migrator->splitSQL($rollbackSql);
                            foreach ($queries as $query) {
                                $query = trim($query);
                                if (!empty($query) && strpos($query, '--') !== 0) {
                                    echo "  Executing: " . substr($query, 0, 60) . "...\n";
                                    $migrator->pdo->exec($query);
                                }
                            }
                            $migrator->pdo->commit();
                            echo "Rollback executed (if any valid SQL was found).\n";
                        } catch (\Exception $e) {
                            $migrator->pdo->rollBack();
                            echo "Rollback failed: " . $e->getMessage() . "\n";
                        }
                    }
                }
                
                // 2. Удаляем запись о миграции
                $stmt = $migrator->pdo->prepare(
                    "DELETE FROM {$migrator->migrationsTable} WHERE migration_name = ?"
                );
                $stmt->execute([$migrationName]);
                
                echo "Migration record removed from database.\n";
                echo "You can now re-apply the migration if needed.\n";
                
            } else {
                echo "Укажите имя миграции: php Migrator.php reset-migration 20240101000000_add_table_users.sql\n";
            }
            break;
            
        case 'help':
        default:
            echo "Database Migrator - Available commands:\n\n";
            
            echo "MIGRATION COMMANDS:\n";
            echo "  php Migrator.php check                 - Check for DB changes and generate migrations\n";
            echo "  php Migrator.php update                - Auto-check and apply all updates\n";
            echo "  php Migrator.php migrate               - Apply pending migrations\n";
            echo "  php Migrator.php status                - Show migration status\n";
            echo "  php Migrator.php validate [file]       - Validate migration file\n";
            echo "  php Migrator.php apply [file]          - Apply specific migration\n";
            echo "  php Migrator.php rollback              - Rollback last applied migration\n";
            echo "  php Migrator.php rollback-to [file]    - Rollback to specific migration\n";
            echo "  php Migrator.php consistency           - Check database-migration consistency\n\n";
            
            echo "SEED COMMANDS (with auto-cleanup):\n";
            echo "  php Migrator.php create-seeds          - Create seeds from all tables (delete old ones)\n";
            echo "  php Migrator.php seed-single [table]   - Create seed for table (delete old ones)\n";
            echo "  php Migrator.php apply-seeds [filter]  - Apply seeds (with optional filter)\n";
            echo "  php Migrator.php list-seeds            - List seeds grouped by table\n";
            echo "  php Migrator.php delete-seeds [tables] - Delete seeds for specific tables\n";
            echo "  php Migrator.php cleanup-seeds         - Delete ALL seed files\n\n";
            
            echo "EXAMPLES:\n";
            echo "  php Migrator.php update                       - Full automatic DB update\n";
            echo "  php Migrator.php create-seeds                 - Backup all tables (old seeds auto-deleted)\n";
            echo "  php Migrator.php seed-single users            - Backup users table (old seeds auto-deleted)\n";
            echo "  php Migrator.php apply-seeds users            - Apply only seeds with 'users' in filename\n";
            echo "  php Migrator.php delete-seeds users posts     - Delete seeds for users and posts tables\n";
            echo "  php Migrator.php rollback                     - Rollback last applied migration\n";
            echo "  php Migrator.php validate migration_file.sql  - Validate migration SQL\n";
            exit(1);
    }
}