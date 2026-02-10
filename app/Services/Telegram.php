<?php

namespace app;

use app\Models\TelegramSettings;
use app\Models\TelegramErrors;
use GuzzleHttp\Client;
use Exception;
use GuzzleHttp\Exception\RequestException;

/**
 * Класс Telegram - полная обертка для Telegram Bot API
 * 
 * @package app
 * @version 2.0
 * @author [Ваше имя]
 * 
 * Основные возможности:
 * - Отправка сообщений, изображений, файлов, видео, аудио
 * - Управление чатами и пользователями
 * - Работа с вебхуками
 * - Модерация и администрирование
 * - Логирование ошибок в БД
 */
class Telegram
{
    /** @var Client HTTP клиент для запросов к API Telegram */
    private $client;
    
    /** @var string Токен бота Telegram */
    private $token;
    
    /** @var string Базовый URL API Telegram */
    private $apiUrl = 'https://api.telegram.org/bot';
    
    /** @var string Путь к файлу логов (не используется, ведется запись в БД) */
    private $logFile = ROOT.'/logs/telegram.log';
    
    /** @const string Режим разметки текста по умолчанию */
    private const DEFAULT_PARSE_MODE = 'MarkdownV2';
    
    /** @const string Тип клавиатуры по умолчанию */
    private const DEFAULT_KEYBOARD_TYPE = 'inline_keyboard';
    
    /** @const int Максимальное количество медиа в одной группе (ограничение Telegram) */
    private const MAX_MEDIA_PER_GROUP = 10;
    
    /** @const array Символы MarkdownV2, требующие экранирования */
    private const MARKDOWN_ESCAPE_CHARS = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
    
    /** @const array Доступные действия для sendChatAction */
    private const CHAT_ACTIONS = [
        'typing',              // печатает текст
        'upload_photo',        // загружает фото
        'record_video',        // записывает видео
        'upload_video',        // загружает видео
        'record_voice',        // записывает голосовое
        'upload_voice',        // загружает голосовое
        'upload_document',     // загружает документ
        'choose_sticker',      // выбирает стикер
        'find_location',       // ищет локацию
        'record_video_note',   // записывает видео-кружок
        'upload_video_note'    // загружает видео-кружок
    ];
    
    /**
     * Конструктор класса Telegram
     * Инициализирует токен бота, HTTP клиент и проверяет директорию для логов
     */
    public function __construct() {
        // Получаем токен бота из базы данных
        $tg = TelegramSettings::findById(1);
        $this->token = $tg->token;
        
        // Формируем полный URL API
        $this->apiUrl .= $this->token.'/';
        
        // Инициализируем HTTP клиент с настройками
        $this->client = new Client([
            'timeout' => 10,           // Таймаут запроса 10 секунд
            'http_errors' => false,    // Не выбрасывать исключения при HTTP ошибках
            'verify' => false,         // Отключить проверку SSL для локального тестирования
        ]);
        
        // Создаем директорию для логов, если она не существует
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }
    
    /**
     * Получает токен бота с ленивой загрузкой
     * @return string Токен бота
     */
    private function getToken(): string {
        if ($this->token === null) {
            $tg = TelegramSettings::findById(1);
            $this->token = $tg->token;
            $this->apiUrl = 'https://api.telegram.org/bot' . $this->token . '/';
        }
        return $this->token;
    }
    
    // ============================================
    // ОСНОВНЫЕ МЕТОДЫ ДЛЯ РАБОТЫ С СООБЩЕНИЯМИ
    // ============================================
    
    /**
     * Отправляет текстовое сообщение в чат
     * 
     * @param int|string $chat_id ID чата
     * @param string $message Текст сообщения
     * @param array $keyboard Массив кнопок клавиатуры
     * @param string|null $keyboard_type Тип клавиатуры (inline_keyboard или reply_keyboard)
     * @param string|null $parse_mode Режим разметки текста (MarkdownV2, HTML и т.д.)
     * @param bool $disable_notification Отключить уведомление
     * @param bool $protect_content Защитить контент от пересылки
     * @return string JSON ответ от Telegram API
     */
    public function sendMessage($chat_id, string $message, array $keyboard = [], ?string $keyboard_type = null, ?string $parse_mode = null, bool $disable_notification = false, bool $protect_content = false): string {
        try {
            // Устанавливаем значения по умолчанию
            $parse_mode = $parse_mode ?? self::DEFAULT_PARSE_MODE;
            $keyboard_type = $keyboard_type ?? self::DEFAULT_KEYBOARD_TYPE;
            
            // Подготавливаем клавиатуру
            $reply_markup = $this->prepareKeyboard($keyboard, $keyboard_type);
            
            // Формируем данные для запроса
            $data = [
                'chat_id' => $chat_id,
                'text' => $this->escapeMarkdown($message),
                'parse_mode' => $parse_mode,
                'reply_markup' => json_encode($reply_markup),
                'disable_notification' => $disable_notification,
                'protect_content' => $protect_content
            ];
            
            // Отправляем запрос
            return $this->makeRequest('sendMessage', $data, false);
        }
        catch(Exception $e) {
            // Логируем ошибку и возвращаем информацию об ошибке
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Редактирует существующее текстовое сообщение
     * 
     * @param int|string $chat_id ID чата
     * @param int $messId ID сообщения для редактирования
     * @param string $message Новый текст сообщения
     * @param array $keyboard Массив кнопок клавиатуры
     * @param string|null $keyboard_type Тип клавиатуры
     * @param string|null $parse_mode Режим разметки текста
     * @return string JSON ответ от Telegram API
     */
    public function editMessage($chat_id, int $messId, string $message, array $keyboard = [], ?string $keyboard_type = null, ?string $parse_mode = null): string {
        try {
            // Устанавливаем значения по умолчанию
            $parse_mode = $parse_mode ?? self::DEFAULT_PARSE_MODE;
            $keyboard_type = $keyboard_type ?? self::DEFAULT_KEYBOARD_TYPE;
            
            // Подготавливаем клавиатуру
            $reply_markup = $this->prepareKeyboard($keyboard, $keyboard_type);
            
            // Формируем данные для запроса
            $data = [
                'chat_id' => $chat_id,
                'message_id' => $messId,
                'text' => $this->escapeMarkdown($message),
                'parse_mode' => $parse_mode,
                'reply_markup' => json_encode($reply_markup),
            ];
            
            // Отправляем запрос на редактирование
            return $this->makeRequest('editMessageText', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Удаляет сообщение из чата
     * 
     * @param int|string $chat_id ID чата
     * @param int $messId ID сообщения для удаления
     * @return string JSON ответ от Telegram API
     */
    public function deleteMessage($chat_id, int $messId): string {
        try {
            $data = [
                'chat_id' => $chat_id,
                'message_id' => $messId
            ];
            
            return $this->makeRequest('deleteMessage', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    // ============================================
    // МЕТОДЫ ДЛЯ ОТПРАВКИ МЕДИА
    // ============================================
    
    /**
     * Отправляет изображение в чат
     * 
     * @param int|string $chat_id ID чата
     * @param string $image Путь к изображению относительно корня проекта
     * @param string $message Подпись к изображению
     * @param array $keyboard Массив кнопок клавиатуры
     * @param string|null $keyboard_type Тип клавиатуры
     * @param string|null $parse_mode Режим разметки текста
     * @param bool $disable_notification Отключить уведомление
     * @return string JSON ответ от Telegram API
     */
    public function sendImage($chat_id, string $image, string $message = '', array $keyboard = [], ?string $keyboard_type = null, ?string $parse_mode = null, bool $disable_notification = false): string {
        return $this->sendMedia($chat_id, $image, $message, $keyboard, $keyboard_type, $parse_mode, 'photo', 'sendPhoto', $disable_notification);
    }
    
    /**
     * Отправляет видео в чат
     * 
     * @param int|string $chat_id ID чата
     * @param string $video Путь к видео файлу
     * @param string $message Подпись к видео
     * @param array $keyboard Массив кнопок клавиатуры
     * @param string|null $keyboard_type Тип клавиатуры
     * @param string|null $parse_mode Режим разметки текста
     * @param bool $disable_notification Отключить уведомление
     * @param int|null $duration Длительность видео в секундах
     * @param int|null $width Ширина видео
     * @param int|null $height Высота видео
     * @return string JSON ответ от Telegram API
     */
    public function sendVideo($chat_id, string $video, string $message = '', array $keyboard = [], ?string $keyboard_type = null, ?string $parse_mode = null, bool $disable_notification = false, ?int $duration = null, ?int $width = null, ?int $height = null): string {
        $params = [];
        if ($duration !== null) $params['duration'] = $duration;
        if ($width !== null) $params['width'] = $width;
        if ($height !== null) $params['height'] = $height;
        
        return $this->sendMedia($chat_id, $video, $message, $keyboard, $keyboard_type, $parse_mode, 'video', 'sendVideo', $disable_notification, $params);
    }
    
    /**
     * Отправляет аудиофайл в чат
     * 
     * @param int|string $chat_id ID чата
     * @param string $audio Путь к аудио файлу
     * @param string $message Подпись к аудио
     * @param array $keyboard Массив кнопок клавиатуры
     * @param string|null $keyboard_type Тип клавиатуры
     * @param string|null $parse_mode Режим разметки текста
     * @param bool $disable_notification Отключить уведомление
     * @param int|null $duration Длительность аудио в секундах
     * @param string|null $performer Исполнитель
     * @param string|null $title Название трека
     * @return string JSON ответ от Telegram API
     */
    public function sendAudio($chat_id, string $audio, string $message = '', array $keyboard = [], ?string $keyboard_type = null, ?string $parse_mode = null, bool $disable_notification = false, ?int $duration = null, ?string $performer = null, ?string $title = null): string {
        $params = [];
        if ($duration !== null) $params['duration'] = $duration;
        if ($performer !== null) $params['performer'] = $performer;
        if ($title !== null) $params['title'] = $title;
        
        return $this->sendMedia($chat_id, $audio, $message, $keyboard, $keyboard_type, $parse_mode, 'audio', 'sendAudio', $disable_notification, $params);
    }
    
    /**
     * Отправляет голосовое сообщение в чат
     * 
     * @param int|string $chat_id ID чата
     * @param string $voice Путь к голосовому файлу (OGG)
     * @param array $keyboard Массив кнопок клавиатуры
     * @param string|null $keyboard_type Тип клавиатуры
     * @param bool $disable_notification Отключить уведомление
     * @param int|null $duration Длительность аудио в секундах
     * @return string JSON ответ от Telegram API
     */
    public function sendVoice($chat_id, string $voice, array $keyboard = [], ?string $keyboard_type = null, bool $disable_notification = false, ?int $duration = null): string {
        $params = [];
        if ($duration !== null) $params['duration'] = $duration;
        
        return $this->sendMedia($chat_id, $voice, '', $keyboard, $keyboard_type, null, 'voice', 'sendVoice', $disable_notification, $params);
    }
    
    /**
     * Отправляет документ (файл) в чат
     * 
     * @param int|string $chat_id ID чата
     * @param string $file Путь к файлу относительно корня проекта
     * @param string $message Подпись к файлу
     * @param array $keyboard Массив кнопок клавиатуры
     * @param string|null $keyboard_type Тип клавиатуры
     * @param string|null $parse_mode Режим разметки текста
     * @param bool $disable_notification Отключить уведомление
     * @param string|null $thumbnail Путь к thumbnail изображению
     * @return string JSON ответ от Telegram API
     */
    public function sendFile($chat_id, string $file, string $message = '', array $keyboard = [], ?string $keyboard_type = null, ?string $parse_mode = null, bool $disable_notification = false, ?string $thumbnail = null): string {
        $params = [];
        if ($thumbnail !== null) {
            $this->validateFile($thumbnail);
            $params['thumbnail'] = fopen(ROOT . $thumbnail, 'r');
        }
        
        return $this->sendMedia($chat_id, $file, $message, $keyboard, $keyboard_type, $parse_mode, 'document', 'sendDocument', $disable_notification, $params);
    }
    
    /**
     * Отправляет анимацию (GIF) в чат
     * 
     * @param int|string $chat_id ID чата
     * @param string $animation Путь к файлу анимации
     * @param string $message Подпись к анимации
     * @param array $keyboard Массив кнопок клавиатуры
     * @param string|null $keyboard_type Тип клавиатуры
     * @param string|null $parse_mode Режим разметки текста
     * @param bool $disable_notification Отключить уведомление
     * @param int|null $duration Длительность анимации
     * @param int|null $width Ширина
     * @param int|null $height Высота
     * @return string JSON ответ от Telegram API
     */
    public function sendAnimation($chat_id, string $animation, string $message = '', array $keyboard = [], ?string $keyboard_type = null, ?string $parse_mode = null, bool $disable_notification = false, ?int $duration = null, ?int $width = null, ?int $height = null): string {
        $params = [];
        if ($duration !== null) $params['duration'] = $duration;
        if ($width !== null) $params['width'] = $width;
        if ($height !== null) $params['height'] = $height;
        
        return $this->sendMedia($chat_id, $animation, $message, $keyboard, $keyboard_type, $parse_mode, 'animation', 'sendAnimation', $disable_notification, $params);
    }
    
    /**
     * Отправляет стикер в чат
     * 
     * @param int|string $chat_id ID чата
     * @param string $sticker Путь к файлу стикера или ID существующего стикера
     * @param array $keyboard Массив кнопок клавиатуры
     * @param string|null $keyboard_type Тип клавиатуры
     * @param bool $disable_notification Отключить уведомление
     * @return string JSON ответ от Telegram API
     */
    public function sendSticker($chat_id, string $sticker, array $keyboard = [], ?string $keyboard_type = null, bool $disable_notification = false): string {
        try {
            // Проверяем, это файл или ID стикера
            if (file_exists(ROOT . $sticker)) {
                $this->validateFile($sticker);
                $data = [
                    'chat_id' => $chat_id,
                    'sticker' => fopen(ROOT . $sticker, 'r'),
                    'disable_notification' => $disable_notification
                ];
                
                // Добавляем клавиатуру если есть
                if (!empty($keyboard)) {
                    $keyboard_type = $keyboard_type ?? self::DEFAULT_KEYBOARD_TYPE;
                    $reply_markup = $this->prepareKeyboard($keyboard, $keyboard_type);
                    $data['reply_markup'] = json_encode($reply_markup);
                }
                
                return $this->makeRequest('sendSticker', $data, true);
            } else {
                // Отправляем по ID стикера
                $data = [
                    'chat_id' => $chat_id,
                    'sticker' => $sticker,
                    'disable_notification' => $disable_notification
                ];
                
                // Добавляем клавиатуру если есть
                if (!empty($keyboard)) {
                    $keyboard_type = $keyboard_type ?? self::DEFAULT_KEYBOARD_TYPE;
                    $reply_markup = $this->prepareKeyboard($keyboard, $keyboard_type);
                    $data['reply_markup'] = json_encode($reply_markup);
                }
                
                return $this->makeRequest('sendSticker', $data, false);
            }
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Общий метод для отправки медиафайлов (изображений, видео, аудио, файлов)
     * 
     * @param int|string $chat_id ID чата
     * @param string $filePath Путь к файлу
     * @param string $message Подпись
     * @param array $keyboard Массив кнопок клавиатуры
     * @param string|null $keyboard_type Тип клавиатуры
     * @param string|null $parse_mode Режим разметки текста
     * @param string $mediaType Тип медиа (photo/document/video/audio/voice/animation)
     * @param string $methodName Название метода API
     * @param bool $disable_notification Отключить уведомление
     * @param array $additionalParams Дополнительные параметры
     * @return string JSON ответ от Telegram API
     */
    private function sendMedia($chat_id, string $filePath, string $message, array $keyboard, ?string $keyboard_type, ?string $parse_mode, string $mediaType, string $methodName, bool $disable_notification = false, array $additionalParams = []): string {
        try {
            // Проверяем существование файла
            $this->validateFile($filePath);
            
            // Устанавливаем значения по умолчанию
            $parse_mode = $parse_mode ?? self::DEFAULT_PARSE_MODE;
            $keyboard_type = $keyboard_type ?? self::DEFAULT_KEYBOARD_TYPE;
            
            // Подготавливаем клавиатуру
            $reply_markup = $this->prepareKeyboard($keyboard, $keyboard_type);
            
            // Формируем базовые данные для запроса
            $data = [
                'chat_id' => $chat_id,
                'parse_mode' => $parse_mode,
                'reply_markup' => json_encode($reply_markup),
                'disable_notification' => $disable_notification,
                $mediaType => fopen(ROOT . $filePath, 'r')
            ];
            
            // Добавляем подпись если есть
            if (!empty($message)) {
                $data['caption'] = $this->escapeMarkdown($message);
            }
            
            // Добавляем дополнительные параметры
            $data = array_merge($data, $additionalParams);
            
            return $this->makeRequest($methodName, $data, true);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    // ============================================
    // ГРУППОВАЯ ОТПРАВКА МЕДИА
    // ============================================
    
    /**
     * Отправляет группу изображений в чат (до 10 изображений в одном сообщении)
     * 
     * @param int|string $chat_id ID чата
     * @param array $images Массив изображений [['image' => 'path.jpg', 'text' => 'caption'], ...]
     * @param bool $disable_notification Отключить уведомление
     * @return array|string Массив результатов или сообщение об ошибке
     */
    public function sendImages($chat_id, array $images, bool $disable_notification = false) {
        return $this->sendMediaGroup($chat_id, $images, 'image', 'photo', $disable_notification);
    }
    
    /**
     * Отправляет группу файлов в чат (до 10 файлов в одном сообщении)
     * 
     * @param int|string $chat_id ID чата
     * @param array $files Массив файлов [['file' => 'path.pdf', 'text' => 'caption'], ...]
     * @param bool $disable_notification Отключить уведомление
     * @return array|string Массив результатов или сообщение об ошибке
     */
    public function sendFiles($chat_id, array $files, bool $disable_notification = false) {
        return $this->sendMediaGroup($chat_id, $files, 'file', 'document', $disable_notification);
    }
    
    /**
     * Общий метод для отправки групп медиафайлов
     * 
     * @param int|string $chat_id ID чата
     * @param array $items Массив медиафайлов
     * @param string $fieldName Имя поля в массиве (image/file)
     * @param string $mediaType Тип медиа для Telegram API (photo/document)
     * @param bool $disable_notification Отключить уведомление
     * @return array|string Массив результатов или сообщение об ошибке
     */
    private function sendMediaGroup($chat_id, array $items, string $fieldName, string $mediaType, bool $disable_notification = false) {
        try {
            if(empty($items)) {
                throw new Exception("No media items provided for sending");
            }
            
            $mediaGroups = [];
            $groupIndex = 0;
            $itemIndex = 0;
            
            // Разбиваем медиафайлы на группы по MAX_MEDIA_PER_GROUP
            foreach($items as $item) {
                // Проверяем существование файла
                $this->validateFile($item[$fieldName]);
                
                // Извлекаем расширение файла для формирования уникального имени
                $pathInfo = pathinfo($item[$fieldName]);
                $fileName = $mediaType . $itemIndex . '.' . ($pathInfo['extension'] ?? 'jpg');
                
                // Создаем новую группу, если нужно
                if($itemIndex % self::MAX_MEDIA_PER_GROUP == 0) {
                    $mediaGroups[] = [
                        'media' => [],
                        'files' => []
                    ];
                    if($itemIndex > 0) {
                        $groupIndex++;
                    }
                }
                
                // Добавляем медиа в текущую группу
                $mediaItem = [
                    'type' => $mediaType,
                    'media' => 'attach://' . $fileName,
                ];
                
                // Добавляем подпись если есть
                if (!empty($item['text'])) {
                    $mediaItem['caption'] = $item['text'];
                }
                
                $mediaGroups[$groupIndex]['media'][] = $mediaItem;
                
                // Сохраняем путь к файлу
                $mediaGroups[$groupIndex]['files'][$fileName] = $item[$fieldName];
                
                $itemIndex++;
            }
            
            // Отправляем каждую группу отдельно
            $results = [];
            foreach($mediaGroups as $group) {
                try {
                    $data = [
                        'chat_id' => $chat_id,
                        'media' => json_encode($group['media']),
                        'disable_notification' => $disable_notification
                    ];
                    
                    // Добавляем файлы в multipart запрос
                    foreach($group['files'] as $fileName => $filePath) {
                        $data[$fileName] = fopen(ROOT . $filePath, 'r');
                    }
                    
                    $results[] = $this->makeRequest('sendMediaGroup', $data, true);
                }
                catch(Exception $e) {
                    $this->logMessage($e->getMessage(), $chat_id);
                    $results[] = json_encode([
                        'ok' => false,
                        'error' => $e->getMessage(),
                        'code' => $e->getCode()
                    ]);
                }
            }
            
            return $results;
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    // ============================================
    // РЕДАКТИРОВАНИЕ МЕДИА
    // ============================================
    
    /**
     * Редактирует подпись у сообщения с изображением или файлом
     * 
     * @param int|string $chat_id ID чата
     * @param int $messId ID сообщения для редактирования
     * @param string $message Новая подпись
     * @param array $keyboard Массив кнопок клавиатуры
     * @param string|null $keyboard_type Тип клавиатуры
     * @param string|null $parse_mode Режим разметки текста
     * @return string JSON ответ от Telegram API
     */
    public function editCaption($chat_id, int $messId, string $message, array $keyboard = [], ?string $keyboard_type = null, ?string $parse_mode = null): string {
        try {
            // Устанавливаем значения по умолчанию
            $parse_mode = $parse_mode ?? self::DEFAULT_PARSE_MODE;
            $keyboard_type = $keyboard_type ?? self::DEFAULT_KEYBOARD_TYPE;
            
            // Подготавливаем клавиатуру
            $reply_markup = $this->prepareKeyboard($keyboard, $keyboard_type);
            
            // Формируем данные для запроса
            $data = [
                'chat_id' => $chat_id,
                'message_id' => $messId,
                'caption' => $this->escapeMarkdown($message),
                'parse_mode' => $parse_mode,
                'reply_markup' => json_encode($reply_markup)
            ];
            
            return $this->makeRequest('editMessageCaption', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Редактирует клавиатуру сообщения
     * 
     * @param int|string $chat_id ID чата
     * @param int $message_id ID сообщения
     * @param array $keyboard Новая клавиатура
     * @param string|null $keyboard_type Тип клавиатуры
     * @return string JSON ответ от Telegram API
     */
    public function editMessageReplyMarkup($chat_id, int $message_id, array $keyboard = [], ?string $keyboard_type = null): string {
        try {
            $keyboard_type = $keyboard_type ?? self::DEFAULT_KEYBOARD_TYPE;
            $reply_markup = $this->prepareKeyboard($keyboard, $keyboard_type);
            
            $data = [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'reply_markup' => json_encode($reply_markup)
            ];
            
            return $this->makeRequest('editMessageReplyMarkup', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    // ============================================
    // РАБОТА С ЧАТАМИ И ПОЛЬЗОВАТЕЛЯМИ
    // ============================================
    
    /**
     * Получает информацию о чате
     * 
     * @param int|string $chat_id ID чата
     * @return string JSON ответ от Telegram API
     */
    public function getChat($chat_id): string {
        try {
            $data = ['chat_id' => $chat_id];
            return $this->makeRequest('getChat', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Получает список администраторов чата
     * 
     * @param int|string $chat_id ID чата
     * @return string JSON ответ от Telegram API
     */
    public function getChatAdministrators($chat_id): string {
        try {
            $data = ['chat_id' => $chat_id];
            return $this->makeRequest('getChatAdministrators', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Получает количество участников чата
     * 
     * @param int|string $chat_id ID чата
     * @return string JSON ответ от Telegram API
     */
    public function getChatMemberCount($chat_id): string {
        try {
            $data = ['chat_id' => $chat_id];
            return $this->makeRequest('getChatMemberCount', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Получает информацию об участнике чата
     * 
     * @param int|string $chat_id ID чата
     * @param int $user_id ID пользователя
     * @return string JSON ответ от Telegram API
     */
    public function getChatMember($chat_id, int $user_id): string {
        try {
            $data = [
                'chat_id' => $chat_id,
                'user_id' => $user_id
            ];
            return $this->makeRequest('getChatMember', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Банит пользователя в чате
     * 
     * @param int|string $chat_id ID чата
     * @param int $user_id ID пользователя
     * @param int|null $until_date Дата разбана (timestamp)
     * @param bool $revoke_messages Удалить сообщения пользователя
     * @return string JSON ответ от Telegram API
     */
    public function banChatMember($chat_id, int $user_id, ?int $until_date = null, bool $revoke_messages = false): string {
        try {
            $data = [
                'chat_id' => $chat_id,
                'user_id' => $user_id,
                'revoke_messages' => $revoke_messages
            ];
            
            if ($until_date !== null) {
                $data['until_date'] = $until_date;
            }
            
            return $this->makeRequest('banChatMember', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Разбанивает пользователя в чате
     * 
     * @param int|string $chat_id ID чата
     * @param int $user_id ID пользователя
     * @param bool $only_if_banned Разбанить только если забанен
     * @return string JSON ответ от Telegram API
     */
    public function unbanChatMember($chat_id, int $user_id, bool $only_if_banned = false): string {
        try {
            $data = [
                'chat_id' => $chat_id,
                'user_id' => $user_id,
                'only_if_banned' => $only_if_banned
            ];
            
            return $this->makeRequest('unbanChatMember', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Ограничивает пользователя в чате
     * 
     * @param int|string $chat_id ID чата
     * @param int $user_id ID пользователя
     * @param array $permissions Разрешения
     * @param int|null $until_date До (timestamp)
     * @return string JSON ответ от Telegram API
     */
    public function restrictChatMember($chat_id, int $user_id, array $permissions, ?int $until_date = null): string {
        try {
            $data = [
                'chat_id' => $chat_id,
                'user_id' => $user_id,
                'permissions' => json_encode($permissions)
            ];
            
            if ($until_date !== null) {
                $data['until_date'] = $until_date;
            }
            
            return $this->makeRequest('restrictChatMember', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Назначает права администратора пользователю
     * 
     * @param int|string $chat_id ID чата
     * @param int $user_id ID пользователя
     * @param bool $is_anonymous Анонимный администратор
     * @param bool $can_manage_chat Может управлять чатом
     * @param bool $can_delete_messages Может удалять сообщения
     * @param bool $can_manage_video_chats Может управлять видеочатами
     * @param bool $can_restrict_members Может ограничивать участников
     * @param bool $can_promote_members Может назначать администраторов
     * @param bool $can_change_info Может менять информацию чата
     * @param bool $can_invite_users Может приглашать пользователей
     * @param bool $can_post_messages Может публиковать сообщения (только для каналов)
     * @param bool $can_edit_messages Может редактировать сообщения (только для каналов)
     * @param bool $can_pin_messages Может закреплять сообщения
     * @return string JSON ответ от Telegram API
     */
    public function promoteChatMember($chat_id, int $user_id, bool $is_anonymous = false, bool $can_manage_chat = false, bool $can_delete_messages = false, bool $can_manage_video_chats = false, bool $can_restrict_members = false, bool $can_promote_members = false, bool $can_change_info = false, bool $can_invite_users = false, bool $can_post_messages = false, bool $can_edit_messages = false, bool $can_pin_messages = false): string {
        try {
            $data = [
                'chat_id' => $chat_id,
                'user_id' => $user_id,
                'is_anonymous' => $is_anonymous,
                'can_manage_chat' => $can_manage_chat,
                'can_delete_messages' => $can_delete_messages,
                'can_manage_video_chats' => $can_manage_video_chats,
                'can_restrict_members' => $can_restrict_members,
                'can_promote_members' => $can_promote_members,
                'can_change_info' => $can_change_info,
                'can_invite_users' => $can_invite_users,
                'can_post_messages' => $can_post_messages,
                'can_edit_messages' => $can_edit_messages,
                'can_pin_messages' => $can_pin_messages
            ];
            
            return $this->makeRequest('promoteChatMember', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Устанавливает кастомный заголовок для администратора
     * 
     * @param int|string $chat_id ID чата
     * @param int $user_id ID пользователя
     * @param string $custom_title Кастомный заголовок
     * @return string JSON ответ от Telegram API
     */
    public function setChatAdministratorCustomTitle($chat_id, int $user_id, string $custom_title): string {
        try {
            $data = [
                'chat_id' => $chat_id,
                'user_id' => $user_id,
                'custom_title' => $custom_title
            ];
            
            return $this->makeRequest('setChatAdministratorCustomTitle', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Покидает чат
     * 
     * @param int|string $chat_id ID чата
     * @return string JSON ответ от Telegram API
     */
    public function leaveChat($chat_id): string {
        try {
            $data = ['chat_id' => $chat_id];
            return $this->makeRequest('leaveChat', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    // ============================================
    // УПРАВЛЕНИЕ ЧАТОМ
    // ============================================
    
    /**
     * Устанавливает фото чата
     * 
     * @param int|string $chat_id ID чата
     * @param string $photo Путь к фото
     * @return string JSON ответ от Telegram API
     */
    public function setChatPhoto($chat_id, string $photo): string {
        try {
            $this->validateFile($photo);
            $data = [
                'chat_id' => $chat_id,
                'photo' => fopen(ROOT . $photo, 'r')
            ];
            
            return $this->makeRequest('setChatPhoto', $data, true);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Удаляет фото чата
     * 
     * @param int|string $chat_id ID чата
     * @return string JSON ответ от Telegram API
     */
    public function deleteChatPhoto($chat_id): string {
        try {
            $data = ['chat_id' => $chat_id];
            return $this->makeRequest('deleteChatPhoto', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Устанавливает название чата
     * 
     * @param int|string $chat_id ID чата
     * @param string $title Новое название
     * @return string JSON ответ от Telegram API
     */
    public function setChatTitle($chat_id, string $title): string {
        try {
            $data = [
                'chat_id' => $chat_id,
                'title' => $title
            ];
            
            return $this->makeRequest('setChatTitle', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Устанавливает описание чата
     * 
     * @param int|string $chat_id ID чата
     * @param string $description Описание
     * @return string JSON ответ от Telegram API
     */
    public function setChatDescription($chat_id, string $description): string {
        try {
            $data = [
                'chat_id' => $chat_id,
                'description' => $description
            ];
            
            return $this->makeRequest('setChatDescription', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    // ============================================
    // РАБОТА С СООБЩЕНИЯМИ (ДОПОЛНИТЕЛЬНО)
    // ============================================
    
    /**
     * Пересылает сообщение
     * 
     * @param int|string $chat_id ID целевого чата
     * @param int|string $from_chat_id ID исходного чата
     * @param int $message_id ID сообщения
     * @param bool $disable_notification Отключить уведомление
     * @param bool $protect_content Защитить контент
     * @return string JSON ответ от Telegram API
     */
    public function forwardMessage($chat_id, $from_chat_id, int $message_id, bool $disable_notification = false, bool $protect_content = false): string {
        try {
            $data = [
                'chat_id' => $chat_id,
                'from_chat_id' => $from_chat_id,
                'message_id' => $message_id,
                'disable_notification' => $disable_notification,
                'protect_content' => $protect_content
            ];
            
            return $this->makeRequest('forwardMessage', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Копирует сообщение
     * 
     * @param int|string $chat_id ID целевого чата
     * @param int|string $from_chat_id ID исходного чата
     * @param int $message_id ID сообщения
     * @param string|null $caption Новая подпись
     * @param string|null $parse_mode Режим разметки
     * @param array $keyboard Клавиатура
     * @param bool $disable_notification Отключить уведомление
     * @param bool $protect_content Защитить контент
     * @return string JSON ответ от Telegram API
     */
    public function copyMessage($chat_id, $from_chat_id, int $message_id, ?string $caption = null, ?string $parse_mode = null, array $keyboard = [], bool $disable_notification = false, bool $protect_content = false): string {
        try {
            $parse_mode = $parse_mode ?? self::DEFAULT_PARSE_MODE;
            $keyboard_type = self::DEFAULT_KEYBOARD_TYPE;
            $reply_markup = $this->prepareKeyboard($keyboard, $keyboard_type);
            
            $data = [
                'chat_id' => $chat_id,
                'from_chat_id' => $from_chat_id,
                'message_id' => $message_id,
                'parse_mode' => $parse_mode,
                'reply_markup' => json_encode($reply_markup),
                'disable_notification' => $disable_notification,
                'protect_content' => $protect_content
            ];
            
            if ($caption !== null) {
                $data['caption'] = $this->escapeMarkdown($caption);
            }
            
            return $this->makeRequest('copyMessage', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    // ============================================
    // РАБОТА С WEBHOOK
    // ============================================
    
    /**
     * Получает информацию о текущем webhook
     * 
     * @return string JSON ответ от Telegram API
     */
    public function getWebhookInfo(): string {
        try {
            return $this->makeRequest('getWebhookInfo', [], false);
        } 
        catch(Exception $e) {
            $this->logMessage($e->getMessage());
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Устанавливает новый webhook
     * 
     * @param string $url URL для webhook
     * @param string|null $certificate Путь к сертификату
     * @param string $ip_address IP адрес для отправки
     * @param int $max_connections Максимальное количество соединений
     * @param array $allowed_updates Типы обновлений
     * @param bool $drop_pending_updates Удалить pending updates
     * @param string|null $secret_token Секретный токен
     * @return string JSON ответ от Telegram API
     */
    public function setWebhook(string $url, ?string $certificate = null, string $ip_address = '', int $max_connections = 40, array $allowed_updates = [], bool $drop_pending_updates = false, ?string $secret_token = null): string {
        try {
            $data = ['url' => $url];
            
            if ($certificate !== null) {
                $this->validateFile($certificate);
                $data['certificate'] = fopen(ROOT . $certificate, 'r');
            }
            
            if (!empty($ip_address)) {
                $data['ip_address'] = $ip_address;
            }
            
            if ($max_connections !== 40) {
                $data['max_connections'] = $max_connections;
            }
            
            if (!empty($allowed_updates)) {
                $data['allowed_updates'] = json_encode($allowed_updates);
            }
            
            if ($drop_pending_updates) {
                $data['drop_pending_updates'] = true;
            }
            
            if ($secret_token !== null) {
                $data['secret_token'] = $secret_token;
            }
            
            $isMultipart = ($certificate !== null);
            return $this->makeRequest('setWebhook', $data, $isMultipart);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage());
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Удаляет текущий webhook
     * 
     * @param bool $drop_pending_updates Удалить pending updates
     * @return string JSON ответ от Telegram API
     */
    public function deleteWebhook(bool $drop_pending_updates = false): string {
        try {
            $data = [];
            if ($drop_pending_updates) {
                $data['drop_pending_updates'] = true;
            }
            
            return $this->makeRequest('deleteWebhook', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage());
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    // ============================================
    // ИНФОРМАЦИОННЫЕ МЕТОДЫ
    // ============================================
    
    /**
     * Получает информацию о боте
     * 
     * @return string JSON ответ от Telegram API
     */
    public function getMe(): string {
        try {
            return $this->makeRequest('getMe', [], false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage());
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Получает информацию о файле
     * 
     * @param string $file_id ID файла
     * @return string JSON ответ от Telegram API
     */
    public function getFile(string $file_id): string {
        try {
            $data = ['file_id' => $file_id];
            return $this->makeRequest('getFile', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage());
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Скачивает файл по file_path
     * 
     * @param string $file_path Путь к файлу от Telegram
     * @return string Содержимое файла
     */
    public function downloadFile(string $file_path): string {
        try {
            $url = 'https://api.telegram.org/file/bot' . $this->getToken() . '/' . $file_path;
            return $this->client->get($url)->getBody()->getContents();
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage());
            return '';
        }
    }
    
    // ============================================
    // ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ
    // ============================================
    
    /**
     * Отправляет действие "печатает..." или другое действие
     * 
     * @param int|string $chat_id ID чата
     * @param string $action Действие (typing, upload_photo, record_video, etc.)
     * @param int|null $message_thread_id ID темы (для форумов)
     * @return string JSON ответ от Telegram API
     */
    public function sendChatAction($chat_id, string $action = 'typing', ?int $message_thread_id = null): string {
        try {
            if (!in_array($action, self::CHAT_ACTIONS)) {
                throw new Exception("Invalid chat action: {$action}");
            }
            
            $data = [
                'chat_id' => $chat_id,
                'action' => $action
            ];
            
            if ($message_thread_id !== null) {
                $data['message_thread_id'] = $message_thread_id;
            }
            
            return $this->makeRequest('sendChatAction', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Закрепляет сообщение в чате
     * 
     * @param int|string $chat_id ID чата
     * @param int $message_id ID сообщения
     * @param bool $disable_notification Отключить уведомление
     * @return string JSON ответ от Telegram API
     */
    public function pinChatMessage($chat_id, int $message_id, bool $disable_notification = false): string {
        try {
            $data = [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'disable_notification' => $disable_notification
            ];
            
            return $this->makeRequest('pinChatMessage', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Открепляет сообщение в чате
     * 
     * @param int|string $chat_id ID чата
     * @param int|null $message_id ID сообщения (если null - открепляет последнее)
     * @return string JSON ответ от Telegram API
     */
    public function unpinChatMessage($chat_id, ?int $message_id = null): string {
        try {
            $data = ['chat_id' => $chat_id];
            
            if ($message_id !== null) {
                $data['message_id'] = $message_id;
            }
            
            return $this->makeRequest('unpinChatMessage', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Открепляет все сообщения в чате
     * 
     * @param int|string $chat_id ID чата
     * @return string JSON ответ от Telegram API
     */
    public function unpinAllChatMessages($chat_id): string {
        try {
            $data = ['chat_id' => $chat_id];
            return $this->makeRequest('unpinAllChatMessages', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage(), $chat_id);
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    // ============================================
    // РАБОТА С КОМАНДАМИ БОТА
    // ============================================
    
    /**
     * Устанавливает команды бота
     * 
     * @param array $commands Массив команд [['command' => 'start', 'description' => 'Start bot']]
     * @param array $scope Область видимости команд
     * @param string|null $language_code Код языка
     * @return string JSON ответ от Telegram API
     */
    public function setMyCommands(array $commands, array $scope = [], ?string $language_code = null): string {
        try {
            $data = [
                'commands' => json_encode($commands)
            ];
            
            if (!empty($scope)) {
                $data['scope'] = json_encode($scope);
            }
            
            if ($language_code !== null) {
                $data['language_code'] = $language_code;
            }
            
            return $this->makeRequest('setMyCommands', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage());
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Получает команды бота
     * 
     * @param array $scope Область видимости
     * @param string|null $language_code Код языка
     * @return string JSON ответ от Telegram API
     */
    public function getMyCommands(array $scope = [], ?string $language_code = null): string {
        try {
            $data = [];
            
            if (!empty($scope)) {
                $data['scope'] = json_encode($scope);
            }
            
            if ($language_code !== null) {
                $data['language_code'] = $language_code;
            }
            
            return $this->makeRequest('getMyCommands', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage());
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Удаляет команды бота
     * 
     * @param array $scope Область видимости
     * @param string|null $language_code Код языка
     * @return string JSON ответ от Telegram API
     */
    public function deleteMyCommands(array $scope = [], ?string $language_code = null): string {
        try {
            $data = [];
            
            if (!empty($scope)) {
                $data['scope'] = json_encode($scope);
            }
            
            if ($language_code !== null) {
                $data['language_code'] = $language_code;
            }
            
            return $this->makeRequest('deleteMyCommands', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage());
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    // ============================================
    // ПРИВАТНЫЕ ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ
    // ============================================
    
    /**
     * Подготавливает данные для multipart/form-data запроса
     * 
     * @param array $data Массив данных
     * @return array Массив в формате multipart
     */
    private function prepareMultipartData(array $data): array {
        $multipart = [];
        foreach ($data as $name => $contents) {
            // Если содержимое - ресурс (открытый файл), используем его как есть
            if (is_resource($contents)) {
                $multipart[] = [
                    'name' => $name,
                    'contents' => $contents
                ];
            } 
            // Если содержимое - массив (для JSON), кодируем его
            elseif (is_array($contents)) {
                $multipart[] = [
                    'name' => $name,
                    'contents' => json_encode($contents)
                ];
            }
            else {
                // Иначе преобразуем в строку
                $multipart[] = [
                    'name' => $name,
                    'contents' => (string)$contents
                ];
            }
        }
        return $multipart;
    }
    
    /**
     * Создает разметку клавиатуры для Telegram API
     * 
     * @param array $keyboard Массив кнопок клавиатуры
     * @param string $type Тип клавиатуры (inline_keyboard/reply_keyboard)
     * @return array Массив с разметкой клавиатуры
     */
    private function prepareKeyboard(array $keyboard, string $type): array {
        if (empty($keyboard)) {
            return ['remove_keyboard' => true];
        }
        
        return [
            $type => $keyboard,
            'resize_keyboard' => true,    // Автоматически подгонять размер клавиатуры
            'one_time_keyboard' => false, // Не скрывать клавиатуру после использования
            'selective' => false          // Показывать всем пользователям
        ];
    }
    
    /**
     * Выполняет запрос к Telegram API
     * 
     * @param string $method Название метода API
     * @param array $data Данные для отправки
     * @param bool $isMultipart Использовать multipart/form-data (true) или form_params (false)
     * @return string Ответ от API
     */
    private function makeRequest(string $method, array $data, bool $isMultipart = false): string {
        try {
            // Выбираем формат данных в зависимости от типа запроса
            if($isMultipart) {
                $options = [
                    'multipart' => $this->prepareMultipartData($data),
                    'force_ip_resolve' => 'v4' // Использовать IPv4
                ];
            } else {
                $options = [
                    'form_params' => $data,
                    'force_ip_resolve' => 'v4'
                ];
            }
            
            // Выполняем POST запрос
            $response = $this->client->post($this->apiUrl . $method, $options);
            
            // Возвращаем тело ответа
            return $response->getBody()->getContents();
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage());
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
    
    /**
     * Экранирует специальные символы MarkdownV2
     * 
     * @param string $text Исходный текст
     * @return string Экранированный текст
     */
    private function escapeMarkdown(string $text): string {
        foreach (self::MARKDOWN_ESCAPE_CHARS as $char) {
            $text = str_replace($char, '\\' . $char, $text);
        }
        return $text;
    }
    
    /**
     * Проверяет существование и доступность файла
     * 
     * @param string $path Путь к файлу относительно корня проекта
     * @return bool true если файл существует и доступен для чтения
     * @throws Exception Если файл не существует или недоступен
     */
    private function validateFile(string $path): bool {
        $fullPath = ROOT . $path;
        
        if (!file_exists($fullPath)) {
            throw new Exception("File not found: {$path}");
        }
        
        if (!is_readable($fullPath)) {
            throw new Exception("File not readable: {$path}");
        }
        
        // Проверяем размер файла (Telegram ограничивает 50MB)
        $fileSize = filesize($fullPath);
        if ($fileSize > 50 * 1024 * 1024) { // 50MB
            throw new Exception("File too large: {$path} ({$fileSize} bytes). Telegram limit is 50MB");
        }
        
        return true;
    }
    
    /**
     * Логирует ошибки в базу данных
     * 
     * @param string $text Текст ошибки
     * @param string|null $chat_id ID чата (если связан с конкретным чатом)
     * @return void
     */
    public function logMessage(string $text, ?string $chat_id = null): void {
        try {
            $tm = new TelegramErrors();
            $tm->chatId = $chat_id;
            $tm->date = time();
            $tm->ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $tm->error = $text;
            $tm->save();
            
            // Дополнительно пишем в PHP error log для критических ошибок
            if (strpos($text, 'critical') !== false || strpos($text, 'fatal') !== false) {
                error_log("Telegram API Critical Error: " . $text);
            }
        }
        catch(Exception $e) {
            // Если не удалось записать в БД, пишем в файл и error log
            $logMessage = date('Y-m-d H:i:s') . " - Telegram error logging failed: " . $e->getMessage() . PHP_EOL;
            $logMessage .= "Original error: " . $text . PHP_EOL;
            
            file_put_contents($this->logFile, $logMessage, FILE_APPEND);
            error_log($logMessage);
        }
    }

    /**
     * Ответ на callback query (убирает "часики" у кнопки)
     * 
     * @param string $callback_query_id ID callback query
     * @param string|null $text Текст для показа пользователю
     * @param bool $show_alert Показать alert
     * @param string|null $url URL для открытия
     * @param int $cache_time Время кэширования ответа
     * @return string JSON ответ от Telegram API
     */
    public function answerCallbackQuery(string $callback_query_id, ?string $text = null, bool $show_alert = false, ?string $url = null, int $cache_time = 0): string {
        try {
            $data = [
                'callback_query_id' => $callback_query_id,
                'show_alert' => $show_alert,
                'cache_time' => $cache_time
            ];
            
            if ($text !== null) {
                $data['text'] = $text;
            }
            
            if ($url !== null) {
                $data['url'] = $url;
            }
            
            return $this->makeRequest('answerCallbackQuery', $data, false);
        }
        catch(Exception $e) {
            $this->logMessage($e->getMessage());
            return json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
}