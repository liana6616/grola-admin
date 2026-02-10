<?php

namespace app\Models;

use app\Db;
use app\Model;
use app\Models\Admins;
use app\Models\Gallery;
use app\Models\Files;

class ChangesLog extends Model
{
    public const TABLE = 'changes_log';
    
    // Типы изменений
    public const CHANGE_MAIN = 'main';
    public const CHANGE_GALLERY = 'gallery';
    public const CHANGE_FILES = 'files';
    public const CHANGE_IMAGE = 'image';
    public const CHANGE_PREVIEW = 'preview';
    public const CHANGE_SEO = 'seo';
    public const CHANGE_PUBLICATION = 'publication';
    public const CHANGE_SORT = 'sort';
    
    // Подтипы изменений для галереи
    public const GALLERY_ADD = 'gallery_add';
    public const GALLERY_DELETE = 'gallery_delete';
    public const GALLERY_UPDATE = 'gallery_update';
    public const GALLERY_SORT = 'gallery_sort';
    
    // Подтипы изменений для файлов
    public const FILES_ADD = 'files_add';
    public const FILES_DELETE = 'files_delete';
    public const FILES_UPDATE = 'files_update';
    public const FILES_SORT = 'files_sort';
    
    // Типы изображений
    public const IMAGE_MAIN = 'image_main';
    public const IMAGE_PREVIEW = 'image_preview';
    
    // Действия
    public const ACTION_INSERT = 'INSERT';
    public const ACTION_UPDATE = 'UPDATE';
    public const ACTION_DELETE = 'DELETE';
    public const ACTION_PUBLISH = 'PUBLISH';
    public const ACTION_SORT = 'SORT';

    /**
     * Отобразить карточку изменения
     */
    public static function change_card($classPath, $log, $index, $fieldLabel) {
        
        $className = strtolower(basename(str_replace('\\', '/', $classPath)));

        // Определяем тип действия
        $actionClass = 'action_' . strtolower($log->action);
        $actionLabels = [
            self::ACTION_INSERT => 'Создание',
            self::ACTION_UPDATE => 'Изменение',
            self::ACTION_DELETE => 'Удаление',
            self::ACTION_PUBLISH => 'Публикация',
            self::ACTION_SORT => 'Изменение порядка'
        ];
        
        // Форматирование значений с учетом типа изменений
        $formatValue = function($value, $field, $changeSubtype) use ($className) {
            if (empty($value) && $value !== '0' && $value !== 0) {
                return '<span class="value_empty">пусто</span>';
            }

            if ($field === 'show') {
                return $value == 1 ? 'Да' : 'Нет';
            }

            // Обработка JSON данных
            if (is_string($value) && ($value[0] === '{' || $value[0] === '[')) {
                $data = json_decode($value, true);
                if ($data && is_array($data)) {
                    $result = [];
                    
                    // Форматирование в зависимости от типа данных
                    if (isset($data['filename'])) {
                        $result[] = 'Файл: ' . htmlspecialchars($data['filename']);
                    }
                    if (isset($data['count'])) {
                        $result[] = 'Количество: ' . $data['count'];
                    }
                    if (isset($data['ids'])) {
                        $result[] = 'ID: ' . (is_array($data['ids']) ? implode(', ', $data['ids']) : $data['ids']);
                    }
                    if (isset($data['action'])) {
                        $result[] = 'Действие: ' . $data['action'];
                    }
                    if (isset($data['order_changed']) && $data['order_changed']) {
                        $result[] = 'Изменён порядок элементов';
                    }
                    
                    return !empty($result) ? implode('<br>', $result) : $value;
                }
            }

            // Обрезка длинного текста
            $displayValue = htmlspecialchars_decode($value);
            if (mb_strlen($displayValue) > 512) {
                $displayValue = mb_substr($displayValue, 0, 512) . '...';
            }

            return $displayValue;
        };

        $oldValue = $formatValue($log->old_value, $log->field_name, $log->change_subtype);
        $newValue = $formatValue($log->new_value, $log->field_name, $log->change_subtype);

        // Определяем отображаемое имя пользователя
        if(!empty($log->admin_id)) {
            $admin = Admins::findById($log->admin_id);
            if ($admin) {
                $adminDisplay = $admin->name.' ('.$admin->login.')';
                $adminImage = $admin->image ?? '/private/src/images/admin_settings.png';
            } else {
                $adminDisplay = $log->admin_name ?? 'Система';
                $adminImage = '/private/src/images/admin_settings.png';
            }
        }
        else {
            $adminDisplay = $log->admin_name ?? 'Система';
            $adminImage = '/private/src/images/admin_settings.png';
        }
        
        // Подключаем шаблон
        include ROOT . '/private/views/components/change_card.php';
    }

    /**
     * Проверить и залогировать изменения в записи
     */
    public static function checkAndLogChanges(
        string $tableName,
        string $moduleType,
        int $recordId,
        int $adminId,
        array $currentData = [],
        ?array $oldData = null,
        array $options = []
    ): array {
        $changes = [];
        
        // Настройки по умолчанию
        $defaultOptions = [
            'fields_to_check' => ['name', 'text', 'textshort', 'url', 'show', 'rate', 'date'],
            'image_fields' => ['image', 'image_preview'],
            'check_gallery' => true,
            'check_files' => true,
            'check_images' => true,
            'check_main_fields' => true
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // Логируем создание записи
        if (empty($oldData)) {
            $changes[] = self::logInsert($tableName, $recordId, $adminId, $currentData);
        } else {
            // Проверяем изменения в основных полях
            if ($options['check_main_fields']) {
                $fieldChanges = self::checkFieldChanges($currentData, $oldData, $options['fields_to_check']);
                
                foreach ($fieldChanges as $field => $change) {
                    $changes[] = self::logFieldChange(
                        $tableName,
                        $recordId,
                        $field,
                        $change['old'],
                        $change['new'],
                        $adminId
                    );
                }
            }
            
            // Проверяем изменения изображений
            if ($options['check_images']) {
                $imageChanges = self::checkImageChanges(
                    $tableName,
                    $recordId,
                    $adminId,
                    $currentData,
                    $oldData,
                    $options['image_fields']
                );
                $changes = array_merge($changes, $imageChanges);
            }
        }
        
        // Проверяем изменения в галерее (отдельно, т.к. нужны POST данные)
        if ($options['check_gallery']) {
            $galleryChanges = self::checkGalleryChanges($tableName, $moduleType, $recordId, $adminId);
            $changes = array_merge($changes, $galleryChanges);
        }
        
        // Проверяем изменения в файлах
        if ($options['check_files']) {
            $filesChanges = self::checkFilesChanges($tableName, $moduleType, $recordId, $adminId);
            $changes = array_merge($changes, $filesChanges);
        }
        
        return $changes;
    }
    
    /**
     * Проверить изменения в полях
     */
    private static function checkFieldChanges(array $newData, array $oldData, array $fieldsToCheck): array
    {
        $changes = [];
        
        foreach ($fieldsToCheck as $field) {
            $newValue = $newData[$field] ?? null;
            $oldValue = $oldData[$field] ?? null;
            
            // Нормализуем значения для сравнения
            $newValue = self::normalizeValue($newValue);
            $oldValue = self::normalizeValue($oldValue);
            
            // Для дат преобразуем формат
            if (in_array($field, ['date', 'edit_date', 'created_at', 'published_at'])) {
                $newValue = $newValue ? date('Y-m-d H:i:s', strtotime($newValue)) : '';
                $oldValue = $oldValue ? date('Y-m-d H:i:s', strtotime($oldValue)) : '';
            }
            
            if ($newValue !== $oldValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }
        
        return $changes;
    }
    
    /**
     * Нормализовать значение для сравнения
     */
    private static function normalizeValue($value)
    {
        if (is_null($value)) {
            return '';
        }
        
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        
        if (is_numeric($value)) {
            return (string)$value;
        }
        
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        }
        
        return trim((string)$value);
    }
    
    /**
     * Проверить изменения в галерее
     */
    public static function checkGalleryChanges(
        string $tableName,
        string $moduleType,
        int $recordId, 
        int $adminId
    ): array {
        $changes = [];
        
        // Проверяем, были ли отправлены данные галереи
        $hasGalleryData = isset($_POST['gallery_id']) || 
                         (isset($_FILES['gallery']['tmp_name']) && !empty($_FILES['gallery']['tmp_name'][0]));
        
        if (!$hasGalleryData) {
            return $changes;
        }
        
        $currentGallery = Gallery::where("WHERE type = ? AND ids = ? ORDER BY rate", [$moduleType, $recordId]);
        $currentGalleryMap = [];
        foreach ($currentGallery as $item) {
            $currentGalleryMap[$item->id] = $item;
        }
        
        // Получаем отправленные ID, если они есть
        $postedIds = [];
        if (isset($_POST['gallery_id']) && is_array($_POST['gallery_id'])) {
            $postedIds = array_map('intval', $_POST['gallery_id']);
            // Фильтруем нулевые значения (новые файлы)
            $postedIds = array_filter($postedIds, function($id) {
                return $id > 0;
            });
        }
        
        $addedItems = [];
        $deletedItems = [];
        $updatedItems = [];
        $orderChanged = false;
        
        // Проверяем изменения в существующих элементах
        if (!empty($postedIds)) {
            // Проверяем изменение порядка среди существующих элементов
            $currentIds = array_keys($currentGalleryMap);

            $orderChanged = false;
            if (count($currentIds) === count($postedIds)) {
                foreach ($currentIds as $position => $id) {
                    if ($id !== $postedIds[$position]) {
                        $orderChanged = true;
                        break;
                    }
                }
            } else {
                // Если количество элементов изменилось, это не сортировка
                $orderChanged = false;
            }
            
            // Дополнительно: если были добавлены или удалены элементы, 
            // но порядок оставшихся не изменился - это не сортировка
            if (!empty($addedItems) || !empty($deletedItems)) {
                $orderChanged = false;
            }
            
            foreach ($postedIds as $index => $id) {
                if (isset($currentGalleryMap[$id])) {
                    $item = $currentGalleryMap[$id];
                    $newAlt = $_POST['gallery_alt'][$index] ?? '';
                    $newRate = $_POST['gallery_rate'][$index] ?? $index;
                    $newShow = $_POST['gallery_show'][$index] ?? 0;
                    
                    if ($item->alt !== $newAlt || 
                        (int)$item->rate !== (int)$newRate || 
                        (int)$item->show !== (int)$newShow) {
                        
                        $updatedItems[] = [
                            'id' => $id,
                            'old' => [
                                'alt' => $item->alt,
                                'rate' => $item->rate,
                                'show' => $item->show
                            ],
                            'new' => [
                                'alt' => $newAlt,
                                'rate' => $newRate,
                                'show' => $newShow
                            ]
                        ];
                    }
                }
            }
        }
        
        // Проверяем удалённые элементы
        foreach ($currentGalleryMap as $id => $item) {
            if (!in_array($id, $postedIds)) {
                $deletedItems[] = [
                    'id' => $id,
                    'filename' => self::getSafeFilename($item->image_origin ?? $item->image ?? '')
                ];
            }
        }
        
        // Проверяем новые загруженные файлы
        if (isset($_FILES['gallery']['tmp_name'])) {
            foreach ($_FILES['gallery']['tmp_name'] as $index => $tmpName) {
                if (!empty($tmpName) && is_uploaded_file($tmpName)) {
                    $filename = $_FILES['gallery']['name'][$index] ?? 'Новый файл';
                    $addedItems[] = [
                        'filename' => $filename,
                        'index' => $index
                    ];
                }
            }
        }
        
        // Логируем изменения только если они есть
        if (!empty($addedItems)) {
            $changes[] = self::logGalleryChange(
                $tableName,
                $recordId,
                $moduleType,
                null,
                self::GALLERY_ADD,
                $adminId,
                [
                    'items' => $addedItems,
                    'count' => count($addedItems)
                ]
            );
        }
        
        if (!empty($deletedItems)) {
            $changes[] = self::logGalleryChange(
                $tableName,
                $recordId,
                $moduleType,
                null,
                self::GALLERY_DELETE,
                $adminId,
                [
                    'items' => $deletedItems,
                    'count' => count($deletedItems)
                ]
            );
        }
        
        if (!empty($updatedItems)) {
            // Группируем обновления в одну запись
            $changes[] = self::logGalleryChange(
                $tableName,
                $recordId,
                $moduleType,
                null,
                self::GALLERY_UPDATE,
                $adminId,
                [
                    'items' => $updatedItems,
                    'count' => count($updatedItems)
                ]
            );
        }
        
        // ВНИМАНИЕ: Убрано создание записи о сортировке при других изменениях
        // Логируем изменение порядка ТОЛЬКО если есть изменение порядка и нет других изменений
        if ($orderChanged && empty($addedItems) && empty($deletedItems) && empty($updatedItems)) {
            $changes[] = self::logSortChange(
                $tableName,
                $recordId,
                'gallery',
                $adminId,
                [
                    'type' => 'gallery',
                    'old_order' => array_keys($currentGalleryMap),
                    'new_order' => $postedIds,
                    'count' => count($postedIds)
                ]
            );
        }
        
        return $changes;
    }
    
    /**
     * Проверить изменения в файлах
     */
    public static function checkFilesChanges(
        string $tableName,
        string $moduleType,
        int $recordId, 
        int $adminId
    ): array {
        $changes = [];
        
        // Проверяем, были ли отправлены данные файлов
        $hasFilesData = isset($_POST['files_id']) || 
                       (isset($_FILES['files']['tmp_name']) && !empty($_FILES['files']['tmp_name'][0]));
        
        if (!$hasFilesData) {
            return $changes;
        }
        
        $currentFiles = Files::where("WHERE type = ? AND ids = ? ORDER BY rate", [$moduleType, $recordId]);
        $currentFilesMap = [];
        foreach ($currentFiles as $item) {
            $currentFilesMap[$item->id] = $item;
        }
        
        // Получаем отправленные ID, если они есть
        $postedIds = [];
        if (isset($_POST['files_id']) && is_array($_POST['files_id'])) {
            $postedIds = array_map('intval', $_POST['files_id']);
            // Фильтруем нулевые значения (новые файлы)
            $postedIds = array_filter($postedIds, function($id) {
                return $id > 0;
            });
        }
        
        $addedItems = [];
        $deletedItems = [];
        $updatedItems = [];
        $orderChanged = false;
        
        // Проверяем изменения в существующих элементах
        if (!empty($postedIds)) {
            // Проверяем изменение порядка среди существующих элементов
            $currentIds = array_keys($currentFilesMap);
            
            $orderChanged = false;
            if (count($currentIds) === count($postedIds)) {
                foreach ($currentIds as $position => $id) {
                    if ($id !== $postedIds[$position]) {
                        $orderChanged = true;
                        break;
                    }
                }
            } else {
                // Если количество элементов изменилось, это не сортировка
                $orderChanged = false;
            }
            
            // Дополнительно: если были добавлены или удалены элементы, 
            // но порядок оставшихся не изменился - это не сортировка
            if (!empty($addedItems) || !empty($deletedItems)) {
                $orderChanged = false;
            }
            
            foreach ($postedIds as $index => $id) {
                if (isset($currentFilesMap[$id])) {
                    $item = $currentFilesMap[$id];
                    $newFilename = $_POST['files_name'][$index] ?? $item->filename;
                    $newRate = $_POST['files_rate'][$index] ?? $index;
                    $newShow = $_POST['files_show'][$index] ?? $item->show;
                    
                    if ($item->filename !== $newFilename || 
                        (int)$item->rate !== (int)$newRate || 
                        (int)$item->show !== (int)$newShow) {
                        
                        $updatedItems[] = [
                            'id' => $id,
                            'old' => [
                                'filename' => $item->filename,
                                'rate' => $item->rate,
                                'show' => $item->show
                            ],
                            'new' => [
                                'filename' => $newFilename,
                                'rate' => $newRate,
                                'show' => $newShow
                            ]
                        ];
                    }
                }
            }
        }
        
        // Проверяем удалённые элементы
        foreach ($currentFilesMap as $id => $item) {
            if (!in_array($id, $postedIds)) {
                $deletedItems[] = [
                    'id' => $id,
                    'filename' => $item->filename
                ];
            }
        }
        
        // Проверяем новые загруженные файлы
        if (isset($_FILES['files']['tmp_name'])) {
            foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {
                if (!empty($tmpName) && is_uploaded_file($tmpName)) {
                    $filename = $_FILES['files']['name'][$index] ?? 'Новый файл';
                    $addedItems[] = [
                        'filename' => $filename,
                        'index' => $index
                    ];
                }
            }
        }
        
        // Логируем изменения только если они есть
        if (!empty($addedItems)) {
            $changes[] = self::logFilesChange(
                $tableName,
                $recordId,
                $moduleType,
                null,
                self::FILES_ADD,
                $adminId,
                [
                    'items' => $addedItems,
                    'count' => count($addedItems)
                ]
            );
        }
        
        if (!empty($deletedItems)) {
            $changes[] = self::logFilesChange(
                $tableName,
                $recordId,
                $moduleType,
                null,
                self::FILES_DELETE,
                $adminId,
                [
                    'items' => $deletedItems,
                    'count' => count($deletedItems)
                ]
            );
        }
        
        if (!empty($updatedItems)) {
            // Группируем обновления в одну запись
            $changes[] = self::logFilesChange(
                $tableName,
                $recordId,
                $moduleType,
                null,
                self::FILES_UPDATE,
                $adminId,
                [
                    'items' => $updatedItems,
                    'count' => count($updatedItems)
                ]
            );
        }
        
        // ВНИМАНИЕ: Убрано создание записи о сортировке при других изменениях
        // Логируем изменение порядка ТОЛЬКО если есть изменение порядка и нет других изменений
        if ($orderChanged && empty($addedItems) && empty($deletedItems) && empty($updatedItems)) {
            $changes[] = self::logSortChange(
                $tableName,
                $recordId,
                'files',
                $adminId,
                [
                    'type' => 'files',
                    'old_order' => array_keys($currentFilesMap),
                    'new_order' => $postedIds,
                    'count' => count($postedIds)
                ]
            );
        }
        
        return $changes;
    }
    
    /**
     * Проверить изменения изображений
     */
    public static function checkImageChanges(
        string $tableName,
        int $recordId,
        int $adminId,
        array $currentData,
        ?array $oldData = null,
        array $imageFields = ['image', 'image_preview']
    ): array {
        $changes = [];
        
        if (!$oldData) {
            return $changes;
        }
        
        foreach ($imageFields as $field) {
            $currentValue = $currentData[$field] ?? '';
            $oldValue = $oldData[$field] ?? '';
            
            // Проверяем загрузку изображения через файл
            $isUploaded = isset($_FILES[$field]['tmp_name']) && is_uploaded_file($_FILES[$field]['tmp_name']);
            
            // Проверяем удаление изображения через чекбокс
            $isDeleted = isset($_POST[$field . '_del']) && $_POST[$field . '_del'] == '1';
            
            // Определяем тип изображения
            $imageType = $field === 'image_preview' ? self::IMAGE_PREVIEW : self::IMAGE_MAIN;
            
            if ($isUploaded) {
                // Изображение загружено
                $changes[] = self::logImageChange(
                    $tableName,
                    $recordId,
                    $imageType,
                    $adminId,
                    [
                        'action' => 'upload',
                        'old_image' => $oldValue ? self::getSafeFilename($oldValue) : '(нет изображения)',
                        'new_image' => $_FILES[$field]['name'] ?? 'новое изображение',
                        'old_value' => $oldValue,
                        'new_value' => 'uploaded_' . ($_FILES[$field]['name'] ?? ''),
                        'field_name' => $field
                    ]
                );
            } elseif ($isDeleted) {
                // Изображение удалено через чекбокс
                $changes[] = self::logImageChange(
                    $tableName,
                    $recordId,
                    $imageType,
                    $adminId,
                    [
                        'action' => 'delete',
                        'old_image' => $oldValue ? self::getSafeFilename($oldValue) : '(нет изображения)',
                        'new_image' => '(удалено)',
                        'old_value' => $oldValue,
                        'new_value' => '',
                        'field_name' => $field
                    ]
                );
            } elseif (!empty($oldValue) && empty($currentValue)) {
                // Изображение было, стало пусто (удалено через что-то другое)
                $changes[] = self::logImageChange(
                    $tableName,
                    $recordId,
                    $imageType,
                    $adminId,
                    [
                        'action' => 'delete',
                        'old_image' => self::getSafeFilename($oldValue),
                        'new_image' => '(удалено)',
                        'old_value' => $oldValue,
                        'new_value' => '',
                        'field_name' => $field
                    ]
                );
            } elseif (empty($oldValue) && !empty($currentValue)) {
                // Изображения не было, появилось (но не через загрузку файла)
                $changes[] = self::logImageChange(
                    $tableName,
                    $recordId,
                    $imageType,
                    $adminId,
                    [
                        'action' => 'upload',
                        'old_image' => '(нет изображения)',
                        'new_image' => self::getSafeFilename($currentValue),
                        'old_value' => '',
                        'new_value' => $currentValue,
                        'field_name' => $field
                    ]
                );
            } elseif (!empty($oldValue) && !empty($currentValue) && $oldValue !== $currentValue) {
                // Изображение изменилось (другой файл)
                $changes[] = self::logImageChange(
                    $tableName,
                    $recordId,
                    $imageType,
                    $adminId,
                    [
                        'action' => 'change',
                        'old_image' => self::getSafeFilename($oldValue),
                        'new_image' => self::getSafeFilename($currentValue),
                        'old_value' => $oldValue,
                        'new_value' => $currentValue,
                        'field_name' => $field
                    ]
                );
            }
        }
        
        return $changes;
    }
    
    /**
     * Записать изменение поля
     */
    public static function logFieldChange(
        string $tableName, 
        int $recordId, 
        string $field, 
        $oldValue, 
        $newValue, 
        int $adminId
    ): self {
        $log = new self();
        $log->table_name = $tableName;
        $log->record_id = $recordId;
        $log->field_name = $field;
        $log->action = self::ACTION_UPDATE;
        $log->old_value = $oldValue;
        $log->new_value = $newValue;
        $log->change_type = self::CHANGE_MAIN;
        $log->admin_id = $adminId;
        $log->admin_name = $_SESSION['admin']['name'] ?? 'Система';
        $log->admin_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $log->comment = self::getFieldComment($field);
        $log->created_at = date('Y-m-d H:i:s');
        
        // Записываем только изменённые данные
        $log->change_details = json_encode([
            'field' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue
        ], JSON_UNESCAPED_UNICODE);
        
        $log->save();
        return $log;
    }
    
    /**
     * Записать изменение галереи
     */
    public static function logGalleryChange(
        string $tableName, 
        int $recordId,
        string $moduleType,
        ?int $galleryId, 
        string $subtype, 
        int $adminId, 
        array $details = []
    ): self {
        $log = new self();
        $log->table_name = $tableName;
        $log->record_id = $recordId;
        $log->related_table = Gallery::TABLE;
        $log->related_id = $galleryId;
        $log->field_name = 'gallery';
        $log->action = self::ACTION_UPDATE;
        $log->change_type = self::CHANGE_GALLERY;
        $log->change_subtype = $subtype;
        $log->admin_id = $adminId;
        $log->admin_name = $_SESSION['admin']['name'] ?? 'Система';
        $log->admin_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $log->comment = self::getGalleryActionText($subtype, $details['count'] ?? 0);
        $log->created_at = date('Y-m-d H:i:s');
        
        // Записываем только детали изменений
        $log->change_details = json_encode($details, JSON_UNESCAPED_UNICODE);
        
        // Для старых/новых значений сохраняем только количество
        $log->old_value = json_encode(['count' => $details['old_count'] ?? 0]);
        $log->new_value = json_encode(['count' => $details['new_count'] ?? $details['count'] ?? 0]);
        
        $log->save();
        return $log;
    }
    
    /**
     * Записать изменение файлов
     */
    public static function logFilesChange(
        string $tableName, 
        int $recordId,
        string $moduleType,
        ?int $fileId, 
        string $subtype, 
        int $adminId, 
        array $details = []
    ): self {
        $log = new self();
        $log->table_name = $tableName;
        $log->record_id = $recordId;
        $log->related_table = Files::TABLE;
        $log->related_id = $fileId;
        $log->field_name = 'files';
        $log->action = self::ACTION_UPDATE;
        $log->change_type = self::CHANGE_FILES;
        $log->change_subtype = $subtype;
        $log->admin_id = $adminId;
        $log->admin_name = $_SESSION['admin']['name'] ?? 'Система';
        $log->admin_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $log->comment = self::getFilesActionText($subtype, $details['count'] ?? 0);
        $log->created_at = date('Y-m-d H:i:s');
        
        // Записываем только детали изменений
        $log->change_details = json_encode($details, JSON_UNESCAPED_UNICODE);
        
        // Для старых/новых значений сохраняем только количество
        $log->old_value = json_encode(['count' => $details['old_count'] ?? 0]);
        $log->new_value = json_encode(['count' => $details['new_count'] ?? $details['count'] ?? 0]);
        
        $log->save();
        return $log;
    }
    
    /**
     * Записать изменение изображения
     */
    public static function logImageChange(
        string $tableName, 
        int $recordId, 
        string $imageType, 
        int $adminId, 
        array $details = []
    ): self {
        $log = new self();
        $log->table_name = $tableName;
        $log->record_id = $recordId;
        $log->field_name = $details['field_name'] ?? ($imageType === self::IMAGE_PREVIEW ? 'image_preview' : 'image');
        $log->action = self::ACTION_UPDATE;
        $log->old_value = self::getSafeFilename($details['old_value'] ?? $details['old_image'] ?? '');
        $log->new_value = self::getSafeFilename($details['new_value'] ?? $details['new_image'] ?? '');
        $log->change_type = $imageType === self::IMAGE_MAIN ? self::CHANGE_IMAGE : self::CHANGE_PREVIEW;
        $log->change_subtype = $details['action'] ?? 'change';
        $log->admin_id = $adminId;
        $log->admin_name = $_SESSION['admin']['name'] ?? 'Система';
        $log->admin_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $log->comment = self::getImageActionText($imageType, $details['action'] ?? 'change');
        $log->created_at = date('Y-m-d H:i:s');
        
        // Записываем только детали изменений
        $log->change_details = json_encode($details, JSON_UNESCAPED_UNICODE);
        
        $log->save();
        return $log;
    }
    
    /**
     * Записать изменение порядка сортировки
     */
    public static function logSortChange(
        string $tableName, 
        int $recordId,
        string $type, // gallery или files
        int $adminId, 
        array $details = []
    ): self {
        $log = new self();
        $log->table_name = $tableName;
        $log->record_id = $recordId;
        $log->field_name = $type;
        $log->action = self::ACTION_SORT;
        $log->change_type = $type === 'gallery' ? self::CHANGE_GALLERY : self::CHANGE_FILES;
        $log->change_subtype = $type === 'gallery' ? self::GALLERY_SORT : self::FILES_SORT;
        $log->admin_id = $adminId;
        $log->admin_name = $_SESSION['admin']['name'] ?? 'Система';
        $log->admin_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $log->comment = "Изменение порядка " . ($type === 'gallery' ? 'изображений в галерее' : 'файлов');
        $log->created_at = date('Y-m-d H:i:s');
        
        // Записываем только детали изменений
        $log->change_details = json_encode($details, JSON_UNESCAPED_UNICODE);
        
        // Для сортировки старые/новые значения - это порядок ID
        $log->old_value = json_encode(['order' => $details['old_order'] ?? []]);
        $log->new_value = json_encode(['order' => $details['new_order'] ?? []]);
        
        $log->save();
        return $log;
    }
    
    /**
     * Записать создание записи
     */
    public static function logInsert(
        string $tableName, 
        int $recordId, 
        int $adminId, 
        array $data = [],
        string $comment = ''
    ): self {
        $log = new self();
        $log->table_name = $tableName;
        $log->record_id = $recordId;
        $log->field_name = '';
        $log->action = self::ACTION_INSERT;
        $log->change_type = self::CHANGE_MAIN;
        $log->admin_id = $adminId;
        $log->admin_name = $_SESSION['admin']['name'] ?? 'Система';
        $log->admin_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $log->comment = $comment ?: 'Создание записи';
        $log->created_at = date('Y-m-d H:i:s');
        
        // Записываем только основные данные, не весь объект
        $essentialData = [];
        if (isset($data['name'])) $essentialData['name'] = $data['name'];
        if (isset($data['url'])) $essentialData['url'] = $data['url'];
        if (isset($data['date'])) $essentialData['date'] = $data['date'];
        
        $log->change_details = json_encode([
            'type' => 'create',
            'data' => $essentialData
        ], JSON_UNESCAPED_UNICODE);
        
        $log->save();
        return $log;
    }
    
    /**
     * Записать удаление записи
     */
    public static function logDelete(
        string $tableName, 
        int $recordId, 
        int $adminId, 
        array $data = [],
        string $comment = ''
    ): self {
        $log = new self();
        $log->table_name = $tableName;
        $log->record_id = $recordId;
        $log->field_name = '';
        $log->action = self::ACTION_DELETE;
        $log->change_type = self::CHANGE_MAIN;
        $log->admin_id = $adminId;
        $log->admin_name = $_SESSION['admin']['name'] ?? 'Система';
        $log->admin_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $log->comment = $comment ?: 'Удаление записи';
        $log->created_at = date('Y-m-d H:i:s');
        
        // Записываем только основные данные
        $essentialData = [];
        if (isset($data['name'])) $essentialData['name'] = $data['name'];
        if (isset($data['url'])) $essentialData['url'] = $data['url'];
        
        $log->change_details = json_encode([
            'type' => 'delete',
            'data' => $essentialData
        ], JSON_UNESCAPED_UNICODE);
        
        $log->save();
        return $log;
    }
    
    /**
     * Записать событие публикации
     */
    public static function logPublication(
        string $tableName, 
        int $draftId, 
        int $publishedId, 
        int $adminId, 
        string $type = 'publish', 
        ?string $comment = null, 
        array $details = []
    ): self {
        $log = new self();
        $log->table_name = $tableName;
        $log->record_id = $publishedId; // Записываем для опубликованной версии
        $log->related_id = $draftId; // Связываем с черновиком
        $log->field_name = '';
        $log->action = self::ACTION_PUBLISH;
        $log->change_type = self::CHANGE_PUBLICATION;
        $log->admin_id = $adminId;
        $log->admin_name = $_SESSION['admin']['name'] ?? 'Система';
        $log->admin_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $log->comment = $comment ?? 'Публикация изменений';
        $log->created_at = date('Y-m-d H:i:s');
        
        // Записываем детали публикации
        $log->change_details = json_encode(array_merge([
            'draft_id' => $draftId,
            'published_id' => $publishedId,
            'type' => $type,
            'date' => date('Y-m-d H:i:s')
        ], $details), JSON_UNESCAPED_UNICODE);
        
        $log->save();
        return $log;
    }
    
    /**
     * Получить текст действия для поля
     */
    private static function getFieldComment(string $field): string
    {
        $comments = [
            'name' => 'Изменение названия',
            'url' => 'Изменение ссылки',
            'text' => 'Изменение текста',
            'text2' => 'Изменение дополнительного текста',
            'textshort' => 'Изменение краткого описания',
            'show' => 'Изменение видимости',
            'date' => 'Изменение даты',
            'section_id' => 'Изменение раздела',
            'rate' => 'Изменение рейтинга сортировки',
            'title' => 'Изменение SEO заголовка',
            'keywords' => 'Изменение SEO ключевых слов',
            'description' => 'Изменение SEO описания',
            'image' => 'Изменение изображения',
            'image_preview' => 'Изменение превью изображения'
        ];
        
        return $comments[$field] ?? "Изменение поля {$field}";
    }
    
    /**
     * Получить текст действия для галереи
     */
    private static function getGalleryActionText(string $subtype, int $count = 0): string
    {
        $texts = [
            self::GALLERY_ADD => "Добавление {$count} изображений в галерею",
            self::GALLERY_DELETE => "Удаление {$count} изображений из галереи",
            self::GALLERY_UPDATE => "Изменение {$count} изображений в галерее",
            self::GALLERY_SORT => "Изменение порядка изображений в галерее"
        ];
        
        return $texts[$subtype] ?? 'Изменение фотогалереи';
    }
    
    /**
     * Получить текст действия для файлов
     */
    private static function getFilesActionText(string $subtype, int $count = 0): string
    {
        $texts = [
            self::FILES_ADD => "Добавление {$count} файлов",
            self::FILES_DELETE => "Удаление {$count} файлов",
            self::FILES_UPDATE => "Изменение {$count} файлов",
            self::FILES_SORT => "Изменение порядка файлов"
        ];
        
        return $texts[$subtype] ?? 'Изменение файлов';
    }
    
    /**
     * Получить текст действия для изображений
     */
    private static function getImageActionText(string $imageType, string $action): string
    {
        $imageName = $imageType === self::IMAGE_MAIN ? 'главного изображения' : 'изображения превью';
        
        $actions = [
            'upload' => "Загрузка {$imageName}",
            'delete' => "Удаление {$imageName}",
            'change' => "Изменение {$imageName}"
        ];
        
        return $actions[$action] ?? "Изменение {$imageName}";
    }
    
    /**
     * Получить безопасное имя файла из пути
     */
    private static function getSafeFilename(string $path): string
    {
        if (empty($path)) {
            return '';
        }
        
        $filename = basename($path);
        
        // Удаляем временные префиксы
        $filename = str_replace('draft-', '', $filename);
        
        // Обрезаем слишком длинные имена
        if (strlen($filename) > 50) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $filename = substr($name, 0, 40) . '...' . ($ext ? '.' . $ext : '');
        }
        
        return htmlspecialchars($filename, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Получить полную историю изменений для записи
     */
    public static function getFullHistory(string $tableName, int $recordId): array
    {
        return self::where(
            "WHERE table_name = ? AND record_id = ? 
            ORDER BY created_at DESC",
            [$tableName, $recordId]
        );
    }
    
    /**
     * Получить историю изменений для черновика
     */
    public static function getDraftHistory(string $tableName, int $draftId): array
    {
        return self::where(
            "WHERE table_name = ? AND record_id = ? 
            ORDER BY created_at DESC",
            [$tableName, $draftId]
        );
    }
    
    /**
     * Получить статистику изменений по записи
     */
    public static function getChangesStats(string $tableName, int $recordId): array
    {
        $db = Db::getInstance();
        
        $sql = "SELECT 
                    COUNT(*) as total_changes,
                    SUM(CASE WHEN change_type = ? THEN 1 ELSE 0 END) as main_changes,
                    SUM(CASE WHEN change_type = ? THEN 1 ELSE 0 END) as gallery_changes,
                    SUM(CASE WHEN change_type = ? THEN 1 ELSE 0 END) as files_changes,
                    SUM(CASE WHEN change_type = ? THEN 1 ELSE 0 END) as image_changes,
                    SUM(CASE WHEN change_type = ? THEN 1 ELSE 0 END) as preview_changes,
                    SUM(CASE WHEN change_type = ? THEN 1 ELSE 0 END) as publications,
                    SUM(CASE WHEN action = ? THEN 1 ELSE 0 END) as insertions,
                    SUM(CASE WHEN action = ? THEN 1 ELSE 0 END) as deletions,
                    SUM(CASE WHEN action = ? THEN 1 ELSE 0 END) as sorts,
                    MIN(created_at) as first_change,
                    MAX(created_at) as last_change
                FROM changes_log
                WHERE table_name = ? AND record_id = ?";
        
        $params = [
            self::CHANGE_MAIN,
            self::CHANGE_GALLERY,
            self::CHANGE_FILES,
            self::CHANGE_IMAGE,
            self::CHANGE_PREVIEW,
            self::CHANGE_PUBLICATION,
            self::ACTION_INSERT,
            self::ACTION_DELETE,
            self::ACTION_SORT,
            $tableName,
            $recordId
        ];
        
        $result = $db->query($sql, $params);
        
        return $result[0] ?? [];
    }
    
    /**
     * Получить историю публикаций для записи
     */
    public static function getPublicationHistory(string $tableName, int $publishedId): array
    {
        return self::where(
            "WHERE table_name = ? AND record_id = ? AND change_type = ? 
            ORDER BY created_at DESC",
            [$tableName, $publishedId, self::CHANGE_PUBLICATION]
        );
    }
    
    /**
     * Получить изменения за период
     */
    public static function getChangesByPeriod(string $startDate, string $endDate, ?string $tableName = null): array
    {
        $where = "WHERE created_at >= ? AND created_at <= ?";
        $params = [$startDate, $endDate];
        
        if ($tableName !== null) {
            $where .= " AND table_name = ?";
            $params[] = $tableName;
        }
        
        return self::where($where . " ORDER BY created_at DESC", $params);
    }
    
    /**
     * Получить изменения по типу
     */
    public static function getChangesByType(string $tableName, string $changeType, int $recordId = 0): array
    {
        $where = "WHERE table_name = ? AND change_type = ?";
        $params = [$tableName, $changeType];
        
        if ($recordId > 0) {
            $where .= " AND record_id = ?";
            $params[] = $recordId;
        }
        
        return self::where($where . " ORDER BY created_at DESC", $params);
    }
    
    /**
     * Получить изменения по действию
     */
    public static function getChangesByAction(string $tableName, string $action, int $recordId = 0): array
    {
        $where = "WHERE table_name = ? AND action = ?";
        $params = [$tableName, $action];
        
        if ($recordId > 0) {
            $where .= " AND record_id = ?";
            $params[] = $recordId;
        }
        
        return self::where($where . " ORDER BY created_at DESC", $params);
    }
    
    /**
     * Получить последние изменения по администратору
     */
    public static function getChangesByAdmin(int $adminId, int $limit = 50): array
    {
        return self::where("WHERE admin_id = ? ORDER BY created_at DESC LIMIT ?", [$adminId, $limit]);
    }
    
    /**
     * Получить суммарную статистику по таблице
     */
    public static function getTableStats(string $tableName): array
    {
        $db = Db::getInstance();
        
        $sql = "SELECT 
                    COUNT(*) as total_changes,
                    COUNT(DISTINCT record_id) as unique_records,
                    COUNT(DISTINCT admin_id) as unique_admins,
                    MIN(created_at) as first_change,
                    MAX(created_at) as last_change
                FROM changes_log
                WHERE table_name = ?";
        
        $result = $db->query($sql, [$tableName]);
        
        return $result[0] ?? [];
    }
    
    /**
     * Очистить старые записи лога
     */
    public static function cleanupOldRecords(int $daysToKeep = 365): int
    {
        $db = Db::getInstance();
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));
        
        $sql = "DELETE FROM changes_log WHERE created_at < ?";
        $result = $db->execute($sql, [$cutoffDate]);
        
        return $result->rowCount();
    }
}