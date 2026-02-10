<?php

$migrator = new \app\Migrator();

// Проверяем результат предыдущей операции
$operationResult = $_SESSION['operation_result'] ?? null;
if ($operationResult) {
    unset($_SESSION['operation_result']);
}

// Получаем данные для отображения
$appliedMigrations = $migrator->getAppliedMigrations();
$pendingMigrations = $migrator->getPendingMigrations();
$seeds = $migrator->getSeedFiles();
$tables = $migrator->pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$tables = array_diff($tables, ['migrations']);

// Группируем сиды по таблицам
$seedsByTable = [];
foreach ($seeds as $seed) {
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

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мигратор БД - Веб интерфейс</title>
    
    <!-- Bootstrap CSS -->
    <? /* ?><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <? */ ?>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="private/src/css/migrator.css?v=<?= rand() ?>">
    
</head>
<body>
    <div class="container container-fluid py-4">
        <header class="mb-5 position-relative">
            <button class="refresh-btn" onclick="location.reload()" title="Обновить страницу">
                <i class="fas fa-sync-alt"></i>
            </button>
            <h1 class="display-4"><i class="fas fa-database"></i> Мигратор БД</h1>
            <p class="lead">Веб-интерфейс для управления миграциями и сидами базы данных</p>
            
            <div class="row mt-4">
                <div class="col-md-3 mb-3">
                    <div class="stat-box">
                        <span class="number"><?php echo count($appliedMigrations); ?></span>
                        <span class="label">Применено миграций</span>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-box">
                        <span class="number"><?php echo count($pendingMigrations); ?></span>
                        <span class="label">Ожидает миграций</span>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-box">
                        <span class="number"><?php echo count($tables); ?></span>
                        <span class="label">Таблиц в БД</span>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-box">
                        <span class="number"><?php echo count($seeds); ?></span>
                        <span class="label">Файлов сидов</span>
                    </div>
                </div>
            </div>
        </header>
        
        <div class="content">
            <?php if ($operationResult): ?>
                <div class="section">
                    <div class="alert <?php echo $operationResult['success'] ? 'alert-success' : 'alert-error'; ?>">
                        <h4 class="alert-heading">
                            <i class="fas <?php echo $operationResult['success'] ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i> 
                            <?php echo htmlspecialchars($operationResult['message']); ?>
                        </h4>
                        
                        <?php if (isset($operationResult['output']) && !empty($operationResult['output'])): ?>
                            <div class="output-panel mt-3">
                                <?php echo htmlspecialchars($operationResult['output']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($operationResult['data']['generated_migrations']) && is_array($operationResult['data']['generated_migrations']) && !empty($operationResult['data']['generated_migrations'])): ?>
                            <hr>
                            <p class="mb-2"><strong>Создано файлов:</strong> <?php echo count($operationResult['data']['generated_migrations']); ?></p>
                            <ul class="mb-0 pl-3">
                                <?php foreach ($operationResult['data']['generated_migrations'] as $file): ?>
                                    <?php if(!empty($file)): ?>
                                        <li><code><?php echo htmlspecialchars($file); ?></code></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php elseif (isset($operationResult['data']['created_seeds']) && is_array($operationResult['data']['created_seeds']) && !empty($operationResult['data']['created_seeds'])): ?>
                            <hr>
                            <p class="mb-2"><strong>Создано файлов сидов:</strong> <?php echo count($operationResult['data']['created_seeds']); ?></p>
                            <ul class="mb-0 pl-3">
                                <?php foreach ($operationResult['data']['created_seeds'] as $file): ?>
                                    <?php if(!empty($file)): ?>
                                        <li><code><?php echo htmlspecialchars($file); ?></code></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        
                        <?php if (isset($operationResult['error'])): ?>
                            <hr>
                            <div class="alert alert-danger mt-2 mb-0">
                                <strong>Ошибка:</strong> <?php echo htmlspecialchars($operationResult['error']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="section">
                <h2><i class="fas fa-sync-alt"></i> Основные действия</h2>
                
                <div class="actions-grid">
                    <div class="action-card">
                        <h3><i class="fas fa-search"></i> Проверить изменения</h3>
                        <p>Проверить изменения в структуре БД и автоматически сгенерировать миграции</p>
                        <form method="POST" onsubmit="return confirmAction('Вы уверены, что хотите проверить изменения?')">
                            <input type="hidden" name="action" value="check">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-play"></i> Выполнить
                            </button>
                        </form>
                    </div>
                    
                    <div class="action-card">
                        <h3><i class="fas fa-arrow-up"></i> Применить миграции</h3>
                        <p>Применить все ожидающие миграции</p>
                        <form method="POST" onsubmit="return confirmAction('Вы уверены, что хотите применить миграции?')">
                            <input type="hidden" name="action" value="migrate">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-play"></i> Выполнить
                            </button>
                        </form>
                    </div>

                    <div class="action-card">
                        <h3><i class="fas fa-bolt"></i> Полное обновление</h3>
                        <p>Полностью автоматический процесс: генерация + применение миграций</p>
                        <form method="POST" onsubmit="return confirmAction('Вы уверены, что хотите выполнить полное обновление?')">
                            <input type="hidden" name="action" value="update">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-rocket"></i> Запустить
                            </button>
                        </form>
                    </div>
                    
                    <div class="action-card">
                        <h3><i class="fas fa-file-export"></i> Создать сиды</h3>
                        <p>Создать сиды из текущих данных в БД</p>
                        <button class="btn btn-info" onclick="showModal('createSeedsModal')">
                            <i class="fas fa-cog"></i> Настроить
                        </button>
                    </div>
                    
                    <div class="action-card">
                        <h3><i class="fas fa-file-import"></i> Применить сиды</h3>
                        <p>Очистить таблицы и загрузить сиды</p>
                        <button class="btn btn-success" onclick="showModal('applySeedsModal')">
                            <i class="fas fa-play"></i> Выполнить
                        </button>
                    </div>
                    
                    <div class="action-card">
                        <h3><i class="fas fa-trash-alt"></i> Удалить сиды</h3>
                        <p>Удалить старые сиды</p>
                        <div class="btn-group">
                            <button class="btn btn-danger" onclick="showModal('deleteSeedsModal')">
                                <i class="fas fa-trash"></i> Удалить
                            </button>
                            <form method="POST" onsubmit="return confirmAction('Вы уверены, что хотите удалить ВСЕ сиды?')" style="display: inline;">
                                <input type="hidden" name="action" value="cleanup_seeds">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-broom"></i> Очистить все
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="action-card">
                        <h3><i class="fas fa-undo"></i> Откатить миграцию</h3>
                        <p>Откатить последнюю применённую миграцию</p>
                        <form method="POST" onsubmit="return confirmAction('ВНИМАНИЕ: Откат удалит запись о миграции, но может не отменить SQL изменения. Продолжить?')">
                            <input type="hidden" name="action" value="rollback">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-undo"></i> Откатить
                            </button>
                        </form>
                    </div>
                    <? /* ?>
                    <div class="action-card">
                        <h3><i class="fas fa-redo"></i> Принудительное применение</h3>
                        <p>Применить миграцию даже если она уже отмечена как применённая</p>
                        <form method="POST" data-migration-selector="force-apply-migration">
                            <input type="hidden" name="action" value="force-apply-migration">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-redo"></i> Применить
                            </button>
                        </form>
                    </div>
                    
                    <div class="action-card">
                        <h3><i class="fas fa-search"></i> Проверить миграцию</h3>
                        <p>Проверить состояние конкретной миграции</p>
                        <form method="POST" data-migration-selector="verify-migration">
                            <input type="hidden" name="action" value="verify-migration">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-search"></i> Проверить
                            </button>
                        </form>
                    </div>
                    
                    <div class="action-card">
                        <h3><i class="fas fa-play"></i> Применить миграцию</h3>
                        <p>Применить конкретную миграцию</p>
                        <form method="POST" data-migration-selector="apply-migration">
                            <input type="hidden" name="action" value="apply-migration">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-play"></i> Применить
                            </button>
                        </form>
                    </div>
                    
                    <div class="action-card">
                        <h3><i class="fas fa-broom"></i> Очистка зависших</h3>
                        <p>Удалить записи о миграциях, которые не применились</p>
                        <form method="POST" onsubmit="return confirmAction('Очистить записи о зависших миграциях?')">
                            <input type="hidden" name="action" value="cleanup-stuck-migrations">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-broom"></i> Очистить
                            </button>
                        </form>
                    </div>
                    
                    <div class="action-card">
                        <h3><i class="fas fa-search"></i> Проверить целостность</h3>
                        <p>Проверить согласованность БД и миграций</p>
                        <form method="POST" onsubmit="return confirmAction('Выполнить проверку целостности?')">
                            <input type="hidden" name="action" value="check-consistency">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-search"></i> Проверить
                            </button>
                        </form>
                    </div>

                    <? */ ?>

                </div>
            </div>
            
            <div class="section">
                <h2><i class="fas fa-list-alt"></i> Статус миграций</h2>
                
                <div class="migration-status">
                    <div class="status-column">
                        <h3><i class="fas fa-check-circle text-success"></i> Примененные миграции (<?php echo count($appliedMigrations); ?>)</h3>
                        <div class="list">
                            <?php if (empty($appliedMigrations)): ?>
                                <div class="list-item">Нет примененных миграций</div>
                            <?php else: ?>
                                <?php foreach ($appliedMigrations as $migration): ?>
                                    <div class="list-item">
                                        <i class="fas fa-check text-success"></i>
                                        <?php echo htmlspecialchars($migration); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="status-column">
                        <h3><i class="fas fa-clock text-warning"></i> Ожидающие миграции (<?php echo count($pendingMigrations); ?>)</h3>
                        <div class="list">
                            <?php if (empty($pendingMigrations)): ?>
                                <div class="list-item">Нет ожидающих миграций</div>
                            <?php else: ?>
                                <?php foreach ($pendingMigrations as $migration): ?>
                                    <div class="list-item">
                                        <i class="fas fa-clock text-warning"></i>
                                        <?php echo htmlspecialchars($migration); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2><i class="fas fa-table"></i> Сиды по таблицам (<?php echo count($seeds); ?> файлов)</h2>
                
                <div id="seedsContainer" class="mt-4">
                    <?php if (empty($seedsByTable)): ?>
                        <p class="text-muted">Нет файлов сидов</p>
                    <?php else: ?>
                        <?php foreach ($seedsByTable as $table => $tableSeeds): ?>
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="fas fa-table text-secondary"></i> Таблица: <?php echo htmlspecialchars($table); ?>
                                        <span class="badge badge-primary float-right"><?php echo count($tableSeeds); ?> файлов</span>
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list">
                                        <?php foreach ($tableSeeds as $seed): ?>
                                            <div class="list-item border-0 border-bottom">
                                                <i class="fas fa-file-alt text-info"></i>
                                                <?php echo htmlspecialchars($seed); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <footer class="mt-5 pt-4 border-top">
            <img src='/private/src/images/logo.svg' alt='Visualteam' class="mb-3">
        </footer>
    </div>
    
    <!-- Модальные окна -->
    <div id="createSeedsModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-export"></i> Создание сидов</h5>
                    <button type="button" class="close" data-dismiss="modal" onclick="closeModal('createSeedsModal')">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" onsubmit="return confirmAction('Вы уверены, что хотите создать сиды?')">
                        <input type="hidden" name="action" value="create_seeds">
                        
                        <div class="form-group">
                            <label><i class="fas fa-filter"></i> Выберите таблицы:</label>
                            <div class="btn-group mb-3">
                                <button type="button" class="btn btn-warning btn-sm" onclick="selectAllTables('createSeedsModal')">
                                    <i class="fas fa-check-double"></i> Выделить все
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="deselectAllTables('createSeedsModal')">
                                    <i class="fas fa-times"></i> Снять все
                                </button>
                            </div>
                            <div class="checkbox-group" id="createSeedsTables">
                                <?php foreach ($tables as $table): ?>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="tables[]" value="<?php echo htmlspecialchars($table); ?>" checked>
                                        <?php echo htmlspecialchars($table); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-hashtag"></i> Максимум строк на таблицу:</label>
                            <input type="number" name="max_rows" class="form-control" value="100" min="1" max="10000">
                        </div>
                        
                        <div class="form-group none">
                            <label class="checkbox-item">
                                <input type="checkbox" name="delete_old" checked>
                                Удалить старые сиды для этих таблиц
                            </label>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-play"></i> Создать сиды
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeModal('createSeedsModal')">
                                <i class="fas fa-times"></i> Отмена
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div id="applySeedsModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-import"></i> Применение сидов</h5>
                    <button type="button" class="close" data-dismiss="modal" onclick="closeModal('applySeedsModal')">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" onsubmit="return confirmAction('ВНИМАНИЕ: Таблицы будут очищены перед вставкой данных! Продолжить?')">
                        <input type="hidden" name="action" value="apply_seeds">
                        
                        <div class="form-group">
                            <label><i class="fas fa-filter"></i> Фильтр по имени файла:</label>
                            <input type="text" name="filter" class="form-control" placeholder="Например: users (оставьте пустым для всех)">
                            <small class="form-text text-muted">Применятся только сиды, содержащие эту строку в имени</small>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle"></i> 
                            <strong>Внимание!</strong> При применении сидов таблицы будут очищены (TRUNCATE) перед вставкой новых данных!
                        </div>
                        
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-play"></i> Применить сиды
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeModal('applySeedsModal')">
                                <i class="fas fa-times"></i> Отмена
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div id="deleteSeedsModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-trash-alt"></i> Удаление сидов</h5>
                    <button type="button" class="close" data-dismiss="modal" onclick="closeModal('deleteSeedsModal')">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" onsubmit="return confirmAction('Вы уверены, что хотите удалить выбранные сиды?')">
                        <input type="hidden" name="action" value="delete_seeds">
                        
                        <div class="form-group">
                            <label><i class="fas fa-filter"></i> Выберите таблицы для удаления сидов:</label>
                            <div class="btn-group mb-3">
                                <button type="button" class="btn btn-warning btn-sm" onclick="selectAllTables('deleteSeedsModal')">
                                    <i class="fas fa-check-double"></i> Выделить все
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="deselectAllTables('deleteSeedsModal')">
                                    <i class="fas fa-times"></i> Снять все
                                </button>
                            </div>
                            <div class="checkbox-group" id="deleteSeedsTables">
                                <?php foreach ($tables as $table): ?>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="tables[]" value="<?php echo htmlspecialchars($table); ?>">
                                        <?php echo htmlspecialchars($table); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Удалить выбранные
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeModal('deleteSeedsModal')">
                                <i class="fas fa-times"></i> Отмена
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Передаем данные из PHP в JavaScript
        window.pendingMigrations = <?php echo json_encode($pendingMigrations); ?>;
        window.appliedMigrations = <?php echo json_encode($appliedMigrations); ?>;
        window.migrationStats = {
            applied: <?php echo count($appliedMigrations); ?>,
            pending: <?php echo count($pendingMigrations); ?>,
            total: <?php echo count($appliedMigrations) + count($pendingMigrations); ?>
        };
    </script>
    
    <script src='/private/src/js/migrator.js?v=<?= rand() ?>'></script>
    
    <script>
        // Простые функции для модальных окон
        function showModal(modalId) {
            $('#' + modalId).modal('show');
        }
        
        function closeModal(modalId) {
            $('#' + modalId).modal('hide');
        }
        
        function selectAllTables(modalId) {
            $('#' + modalId + ' .checkbox-item input[type="checkbox"]').prop('checked', true);
        }
        
        function deselectAllTables(modalId) {
            $('#' + modalId + ' .checkbox-item input[type="checkbox"]').prop('checked', false);
        }
        
        function confirmAction(message) {
            return confirm(message);
        }
        
        // Инициализация Bootstrap компонентов
        $(document).ready(function() {
            // Инициализация всех модальных окон
            $('.modal').modal({ 
                show: false,
                backdrop: 'static',
                keyboard: false
            });
            
            // Убедимся что модальные окна центрируются
            $('.modal').on('shown.bs.modal', function() {
                $(this).css('display', 'flex');
            });
        });
    </script>
</body>
</html>