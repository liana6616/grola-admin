<?php

namespace app\Controllers;

use app\Controller;
use app\Migrator;
use app\Models\Admins;

class MigratorController extends Controller
{
    protected function handle(...$parameters)
    {
        try {
            $admin = Admins::checkLogged(true);
            $this->view->admin = $admin;

            // Вход в мигратор только с определенных IP
            if (!Admins::isPermittedIP()) {
                return $this->showErrorPage(403, 'Доступ к мигратору разрешен только с определенных IP-адресов');
            }

            if (!empty($_POST)) {
                // Получаем значение поля 'action' из POST
                if (isset($_POST['action']) && !empty($_POST['action'])) {
                    $action = trim($_POST['action']);
                } else {
                    return $this->showErrorPage(400, "Не указано действие (параметр 'action')");
                }
            
                // Преобразуем действие из kebab-case или snake_case в camelCase
                $action = $this->normalizeActionName($action);
                
                // Проверяем существование метода
                if (!method_exists($this, $action)) {
                    return $this->showErrorPage(404, "Метод '{$action}' не найден");
                }
                
                // Проверяем, что метод защищенный (для безопасности)
                $reflectionMethod = new \ReflectionMethod($this, $action);
                if (!$reflectionMethod->isProtected()) {
                    return $this->showErrorPage(403, "Доступ к методу '{$action}' запрещен");
                }
                
                // Вызываем метод
                $this->$action();
            } else {
                // Показываем список доступных миграций
                $this->showMigratorPage();
            }
            
        } catch (\Exception $e) {
            error_log('Ошибка в MigratorController: ' . $e->getMessage());
            return $this->showErrorPage(500, 'Произошла внутренняя ошибка', $e->getMessage());
        }
    }

    /**
     * Преобразует имя действия из различных форматов в camelCase
     */
    private function normalizeActionName($action)
    {
        // Удаляем все не-буквенно-цифровые символы кроме подчеркивания и тире
        $action = preg_replace('/[^a-zA-Z0-9_-]/', '', $action);
        
        // Преобразуем kebab-case и snake_case в camelCase
        $action = str_replace(['-', '_'], ' ', $action);
        $action = ucwords($action);
        $action = str_replace(' ', '', $action);
        
        // Делаем первую букву строчной для camelCase
        $action = lcfirst($action);
        
        return $action;
    }
    
    protected function access(): bool
    {
        // Проверяем авторизацию администратора
        if (Admins::isGuest()) {
            $this->showErrorPage(401, 'Требуется авторизация администратора');
            return false;
        }
        
        return true;
    }
    
    /**
     * Показать основную страницу мигратора
     */
    private function showMigratorPage()
    {
        try {
            $migrator = new Migrator();
            
            // Получаем информацию о миграциях для отображения
            $this->view->migration_stats = $migrator->getMigrationStats();
            $this->view->pending_migrations = $migrator->getPendingMigrations();
            $this->view->applied_migrations = $migrator->getAppliedMigrations();
            $this->view->seed_files = $migrator->getSeedFiles();
            $this->view->database_tables = $migrator->getDatabaseTables();
            
            // Получаем результат предыдущей операции из сессии
            $this->view->operation_result = isset($_SESSION['operation_result']) 
                ? $_SESSION['operation_result'] 
                : null;
            
            // Очищаем результат после получения
            if (isset($_SESSION['operation_result'])) {
                unset($_SESSION['operation_result']);
            }
            
            $this->view->display(ROOT . '/private/views/migrator.php');
            
        } catch (\Exception $e) {
            error_log('Ошибка при загрузке страницы мигратора: ' . $e->getMessage());
            return $this->showErrorPage(500, 'Ошибка загрузки данных миграций', $e->getMessage());
        }
    }

    protected function check()
    {
        try {
            $migrator = new Migrator();

            ob_start();
            $result = $migrator->autoCheckAndGenerateMigrations();
            $output = ob_get_clean();
            
            $_SESSION['operation_result'] = [
                'success' => true,
                'message' => 'Проверка изменений выполнена',
                'output' => $output,
                'data' => ['generated_migrations' => $result] // Исправлено: правильный ключ
            ];

            $this->redirect($_SERVER['REQUEST_URI']);
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе check: ' . $e->getMessage());
            $_SESSION['operation_result'] = [
                'success' => false,
                'message' => 'Ошибка при проверке изменений',
                'error' => $e->getMessage()
            ];
            $this->redirect($_SERVER['REQUEST_URI']);
        }
    }

    protected function update()
    {
        try {
            $migrator = new Migrator();

            ob_start();
            $result = $migrator->autoCheckAndApply();
            $output = ob_get_clean();
            
            $_SESSION['operation_result'] = [
                'success' => true,
                'message' => 'Полное обновление выполнено',
                'output' => $output,
                'data' => $result
            ];

            $this->redirect($_SERVER['REQUEST_URI']);
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе update: ' . $e->getMessage());
            $_SESSION['operation_result'] = [
                'success' => false,
                'message' => 'Ошибка при полном обновлении',
                'error' => $e->getMessage()
            ];
            $this->redirect($_SERVER['REQUEST_URI']);
        }
    }

    protected function migrate()
    {
        try {
            $migrator = new Migrator();

            ob_start();
            $migrator->applyPendingMigrations();
            $output = ob_get_clean();
            
            $_SESSION['operation_result'] = [
                'success' => true,
                'message' => 'Миграции применены',
                'output' => $output
            ];

            $this->redirect($_SERVER['REQUEST_URI']);
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе migrate: ' . $e->getMessage());
            $_SESSION['operation_result'] = [
                'success' => false,
                'message' => 'Ошибка при применении миграций',
                'error' => $e->getMessage()
            ];
            $this->redirect($_SERVER['REQUEST_URI']);
        }
    }

    protected function create_seeds()
    {
        try {
            $migrator = new Migrator();

            $tables = $_POST['tables'] ?? [];
            $options = [
                'tables' => $tables,
                'max_rows_per_table' => intval($_POST['max_rows'] ?? 100),
                'delete_old_seeds' => boolval($_POST['delete_old'] ?? true)
            ];
            
            // Валидация
            if (empty($tables)) {
                throw new \Exception('Не указаны таблицы для создания сидов');
            }
            
            ob_start();
            $result = $migrator->createSeedsFromTables($options);
            $output = ob_get_clean();
            
            $_SESSION['operation_result'] = [
                'success' => true,
                'message' => 'Сиды успешно созданы',
                'output' => $output,
                'data' => ['created_seeds' => $result] // Добавлен ключ для ясности
            ];

            $this->redirect($_SERVER['REQUEST_URI']);
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе create_seeds: ' . $e->getMessage());
            $_SESSION['operation_result'] = [
                'success' => false,
                'message' => 'Ошибка при создании сидов',
                'error' => $e->getMessage()
            ];
            $this->redirect($_SERVER['REQUEST_URI']);
        }
    }

    protected function apply_seeds()
    {
        try {
            $migrator = new Migrator();

            $filter = $_POST['filter'] ?? null;
            
            ob_start();
            $migrator->applySeeds($filter);
            $output = ob_get_clean();
            
            $_SESSION['operation_result'] = [
                'success' => true,
                'message' => 'Сиды успешно применены',
                'output' => $output
            ];

            $this->redirect($_SERVER['REQUEST_URI']);
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе apply_seeds: ' . $e->getMessage());
            $_SESSION['operation_result'] = [
                'success' => false,
                'message' => 'Ошибка при применении сидов',
                'error' => $e->getMessage()
            ];
            $this->redirect($_SERVER['REQUEST_URI']);
        }
    }

    protected function delete_seeds()
    {
        try {
            $migrator = new Migrator();

            $tables = $_POST['tables'] ?? [];
            
            if (!empty($tables)) {
                ob_start();
                $result = $migrator->deleteSeedsForTables($tables);
                $output = ob_get_clean();
                
                $_SESSION['operation_result'] = [
                    'success' => true,
                    'message' => "Удалено {$result} файлов сидов",
                    'output' => $output,
                    'data' => $result
                ];
            } else {
                throw new \Exception('Не указаны таблицы для удаления сидов');
            }

            $this->redirect($_SERVER['REQUEST_URI']);
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе delete_seeds: ' . $e->getMessage());
            $_SESSION['operation_result'] = [
                'success' => false,
                'message' => 'Ошибка при удалении сидов',
                'error' => $e->getMessage()
            ];
            $this->redirect($_SERVER['REQUEST_URI']);
        }
    }

    protected function cleanup_seeds()
    {
        try {
            $migrator = new Migrator();

            $allSeeds = $migrator->getSeedFiles();
            $seedsDir = ROOT . '/migrations/seeds/';
            $deleted = 0;
            
            // Проверяем существование директории
            if (!is_dir($seedsDir)) {
                throw new \Exception("Директория сидов не найдена: {$seedsDir}");
            }
            
            ob_start();
            foreach ($allSeeds as $seed) {
                $filePath = $seedsDir . $seed;
                if (file_exists($filePath) && unlink($filePath)) {
                    $deleted++;
                    echo "Удален файл: {$seed}\n";
                }
            }
            $output = ob_get_clean();
            
            $_SESSION['operation_result'] = [
                'success' => true,
                'message' => "Удалено {$deleted} файлов сидов",
                'output' => $output,
                'data' => $deleted
            ];

            $this->redirect($_SERVER['REQUEST_URI']);
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе cleanup_seeds: ' . $e->getMessage());
            $_SESSION['operation_result'] = [
                'success' => false,
                'message' => 'Ошибка при очистке сидов',
                'error' => $e->getMessage()
            ];
            $this->redirect($_SERVER['REQUEST_URI']);
        }
    }
    
    /**
     * Показать статус миграций (AJAX метод)
     */
    protected function get_status()
    {
        try {
            if (!$this->isAjax()) {
                return $this->sendJsonError('Только для AJAX запросов', 400);
            }
            
            $migrator = new Migrator();
            $stats = $migrator->getMigrationStats();
            
            $this->sendJson([
                'success' => true,
                'stats' => $stats,
                'pending' => $migrator->getPendingMigrations(),
                'applied' => $migrator->getAppliedMigrations()
            ]);
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе get_status: ' . $e->getMessage());
            $this->sendJsonError('Ошибка получения статуса миграций', 500);
        }
    }
    
    /**
     * Откат последней миграции
     */
    protected function rollback()
    {
        try {
            $migrator = new Migrator();
            
            ob_start();
            $result = $migrator->rollbackLastMigration();
            $output = ob_get_clean();
            
            if ($result) {
                $_SESSION['operation_result'] = [
                    'success' => true,
                    'message' => 'Последняя миграция успешно откачена',
                    'output' => $output
                ];
            } else {
                $_SESSION['operation_result'] = [
                    'success' => false,
                    'message' => 'Не удалось выполнить откат миграции',
                    'output' => $output
                ];
            }

            $this->redirect($_SERVER['REQUEST_URI']);
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе rollback: ' . $e->getMessage());
            $_SESSION['operation_result'] = [
                'success' => false,
                'message' => 'Ошибка при откате миграции',
                'error' => $e->getMessage()
            ];
            $this->redirect($_SERVER['REQUEST_URI']);
        }
    }

    protected function checkConsistency()
    {
        try {
            $migrator = new Migrator();
            
            ob_start();
            $result = $migrator->checkDatabaseConsistency();
            echo "=== Проверка целостности БД и миграций ===\n\n";
            echo "Применено миграций: " . $result['applied_migrations'] . "\n";
            echo "Всего файлов миграций: " . $result['total_migration_files'] . "\n\n";
            
            if (empty($result['issues'])) {
                echo "✓ Все проверки пройдены успешно. База данных и миграции согласованы.\n";
            } else {
                echo "Обнаружены проблемы:\n";
                foreach ($result['issues'] as $issue) {
                    echo "[" . strtoupper($issue['type']) . "] " . $issue['message'] . "\n";
                }
            }
            
            $output = ob_get_clean();
            
            $_SESSION['operation_result'] = [
                'success' => $result['is_consistent'],
                'message' => $result['is_consistent'] ? 'Целостность проверена успешно' : 'Обнаружены проблемы',
                'output' => $output,
                'data' => $result
            ];

            $this->redirect($_SERVER['REQUEST_URI']);
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе checkConsistency: ' . $e->getMessage());
            $_SESSION['operation_result'] = [
                'success' => false,
                'message' => 'Ошибка при проверке целостности',
                'error' => $e->getMessage()
            ];
            $this->redirect($_SERVER['REQUEST_URI']);
        }
    }

    /**
     * Валидация миграции (AJAX метод)
     */
    protected function validateMigration()
    {
        try {
            if (!$this->isAjax()) {
                return $this->sendJsonError('Только для AJAX запросов', 400);
            }
            
            $migrationName = $_POST['migration'] ?? null;
            if (!$migrationName) {
                return $this->sendJsonError('Не указано имя миграции', 400);
            }
            
            $migrator = new Migrator();
            $result = $migrator->validateMigration($migrationName);
            
            $this->sendJson([
                'success' => true,
                'validation' => $result
            ]);
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе validateMigration: ' . $e->getMessage());
            $this->sendJsonError('Ошибка при валидации миграции', 500);
        }
    }

    /**
     * Применить конкретную миграцию
     */
    protected function applyMigration()
    {
        try {
            $migrationName = $_POST['migration'] ?? null;
            if (!$migrationName) {
                throw new \Exception('Не указано имя миграции');
            }
            
            $migrator = new Migrator();
            
            ob_start();
            $result = $migrator->applyMigration($migrationName);
            $output = ob_get_clean();
            
            $_SESSION['operation_result'] = [
                'success' => $result,
                'message' => $result ? "Миграция {$migrationName} применена успешно" : "Ошибка при применении миграции {$migrationName}",
                'output' => $output
            ];

            $this->redirect($_SERVER['REQUEST_URI']);
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе applyMigration: ' . $e->getMessage());
            $_SESSION['operation_result'] = [
                'success' => false,
                'message' => 'Ошибка при применении миграции',
                'error' => $e->getMessage()
            ];
            $this->redirect($_SERVER['REQUEST_URI']);
        }
    }

    protected function forceApplyMigration()
    {
        try {
            $migrationName = $_POST['migration'] ?? null;
            if (!$migrationName) {
                throw new \Exception('Не указано имя миграции');
            }
            
            $migrator = new Migrator();
            
            ob_start();
            $result = $migrator->forceApplyMigration($migrationName);
            $output = ob_get_clean();
            
            $_SESSION['operation_result'] = [
                'success' => $result,
                'message' => $result ? "Миграция {$migrationName} принудительно применена" : "Ошибка при принудительном применении миграции",
                'output' => $output
            ];

            $this->redirect($_SERVER['REQUEST_URI']);
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе forceApplyMigration: ' . $e->getMessage());
            $_SESSION['operation_result'] = [
                'success' => false,
                'message' => 'Ошибка при принудительном применении миграции',
                'error' => $e->getMessage()
            ];
            $this->redirect($_SERVER['REQUEST_URI']);
        }
    }

    protected function cleanupStuckMigrations()
    {
        try {
            $migrator = new Migrator();
            
            ob_start();
            $result = $migrator->cleanupStuckMigrations();
            $output = ob_get_clean();
            
            $_SESSION['operation_result'] = [
                'success' => true,
                'message' => "Очистка зависших миграций выполнена",
                'output' => $output,
                'data' => ['cleaned_migrations' => $result]
            ];

            $this->redirect($_SERVER['REQUEST_URI']);
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе cleanupStuckMigrations: ' . $e->getMessage());
            $_SESSION['operation_result'] = [
                'success' => false,
                'message' => 'Ошибка при очистке зависших миграций',
                'error' => $e->getMessage()
            ];
            $this->redirect($_SERVER['REQUEST_URI']);
        }
    }

    protected function getMigrationList()
    {
        try {
            if (!$this->isAjax()) {
                return $this->sendJsonError('Только для AJAX запросов', 400);
            }
            
            $migrator = new Migrator();
            
            $this->sendJson([
                'success' => true,
                'pending' => $migrator->getPendingMigrations(),
                'applied' => $migrator->getAppliedMigrations(),
                'stats' => $migrator->getMigrationStats()
            ]);
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе getMigrationList: ' . $e->getMessage());
            $this->sendJsonError('Ошибка получения списка миграций', 500);
        }
    }
}