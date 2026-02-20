<?php

namespace app;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use DomDocument;
use app\Models\Pages;
use app\Models\Settings;

class Helpers
{
    private const TRANSLIT_MAP = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k',
        'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ь' => '\'', 'ы' => 'y', 'ъ' => '\'',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
        'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K',
        'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R',
        'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
        'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch', 'Ь' => '\'', 'Ы' => 'Y', 'Ъ' => '\'',
        'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
    ];

    private const SWITCHER_MAP = [
        'en_ru' => [
            'f' => 'а', ',' => 'б', 'd' => 'в', 'u' => 'г', 'l' => 'д', 't' => 'е', '`' => 'ё',
            ';' => 'ж', 'p' => 'з', 'b' => 'и', 'q' => 'й', 'r' => 'к', 'k' => 'л', 'v' => 'м',
            'y' => 'н', 'j' => 'о', 'g' => 'п', 'h' => 'р', 'c' => 'с', 'n' => 'т', 'e' => 'у',
            'a' => 'ф', '[' => 'х', 'w' => 'ц', 'x' => 'ч', 'i' => 'ш', 'o' => 'щ', 'm' => 'ь',
            's' => 'ы', ']' => 'ъ', "'" => "э", '.' => 'ю', 'z' => 'я',
            'F' => 'А', '<' => 'Б', 'D' => 'В', 'U' => 'Г', 'L' => 'Д', 'T' => 'Е', '~' => 'Ё',
            ':' => 'Ж', 'P' => 'З', 'B' => 'И', 'Q' => 'Й', 'R' => 'К', 'K' => 'Л', 'V' => 'М',
            'Y' => 'Н', 'J' => 'О', 'G' => 'П', 'H' => 'Р', 'C' => 'С', 'N' => 'Т', 'E' => 'У',
            'A' => 'Ф', '{' => 'Х', 'W' => 'Ц', 'X' => 'Ч', 'I' => 'Ш', 'O' => 'Щ', 'M' => 'Ь',
            'S' => 'Ы', '}' => 'Ъ', '"' => 'Э', '>' => 'Ю', 'Z' => 'Я',
            '@' => '"', '#' => '№', '$' => ';', '^' => ':', '&' => '?', '/' => '.', '?' => ','
        ],
        'ru_en' => [
            'а' => 'f', 'б' => ',', 'в' => 'd', 'г' => 'u', 'д' => 'l', 'е' => 't', 'ё' => '`',
            'ж' => ';', 'з' => 'p', 'и' => 'b', 'й' => 'q', 'к' => 'r', 'л' => 'k', 'м' => 'v',
            'н' => 'y', 'о' => 'j', 'п' => 'g', 'р' => 'h', 'с' => 'c', 'т' => 'n', 'у' => 'e',
            'ф' => 'a', 'х' => '[', 'ц' => 'w', 'ч' => 'x', 'ш' => 'i', 'щ' => 'o', 'ь' => 'm',
            'ы' => 's', 'ъ' => ']', 'э' => "'", 'ю' => '.', 'я' => 'z',
            'А' => 'F', 'Б' => '<', 'В' => 'D', 'Г' => 'U', 'Д' => 'L', 'Е' => 'T', 'Ё' => '~',
            'Ж' => ':', 'З' => 'P', 'И' => 'B', 'Й' => 'Q', 'К' => 'R', 'Л' => 'K', 'М' => 'V',
            'Н' => 'Y', 'О' => 'J', 'П' => 'G', 'Р' => 'H', 'С' => 'C', 'Т' => 'N', 'У' => 'E',
            'Ф' => 'A', 'Х' => '{', 'Ц' => 'W', 'Ч' => 'X', 'Ш' => 'I', 'Щ' => 'O', 'Ь' => 'M',
            'Ы' => 'S', 'Ъ' => '}', 'Э' => '"', 'Ю' => '>', 'Я' => 'Z',
            '"' => '@', '№' => '#', ';' => '$', ':' => '^', '?' => '&', '.' => '/', ',' => '?'
        ]
    ];

    private const MONTHS_RU = [
        1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
        5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
        9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
    ];

    private const BLACKLIST_EXTENSIONS = [
        "php", "phtml", "php3", "php4", "js", "jsx", "exe", 
        "sql", "bat", "cmd", "pif", "vbs", "jse", "ps1", "scr", "msi"
    ];

    /**
     * Генерация случайного пароля
     */
    public static function random_password(int $count = 16): string
    {
        if ($count <= 0) {
            $count = 16;
        }

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
        $password = '';
        
        for ($i = 0; $i < $count; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $password;
    }

    /**
     * Вывод отладочной информации
     */
    public static function dump($content): void
    {
        echo '<pre>';
        print_r($content);
        echo '</pre>';
    }

    /**
     * Ответ в формате JSON
     */
    public static function response($content, bool $json = true)
    {
        if ($json) {
            header('Content-Type: application/json');
            return json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        
        return print_r($content, true);
    }

    /**
     * Очистка номера телефона
     */
    public static function clearPhone(?string $phone): string
    {
        return !empty($phone) ? preg_replace('/[^0-9\+]/', '', $phone) : '';
    }

    /**
     * Транслитерация русских букв
     */
    public static function rus2translit(string $string): string
    {
        return strtr($string, self::TRANSLIT_MAP);
    }

    /**
     * Преобразование строки в URL-формат
     */
    public static function str2url(?string $str): string
    {
        if (empty($str)) {
            return '';
        }

        $str = self::rus2translit($str);
        $str = mb_strtolower($str, 'UTF-8');
        $str = strip_tags($str);
        $str = str_replace('-', '', $str);
        $str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
        
        return trim($str, "-");
    }

    /**
     * Форматирование цены с пробелами
     */
    public static function priceSpace($price): ?string
    {
        if (empty($price)) {
            return null;
        }

        $parts = explode('.', (string)$price);
        $formatted = strrev(implode(' ', str_split(strrev($parts[0]), 3)));
        
        if (!empty($parts[1])) {
            $formatted .= '.' . $parts[1];
        }

        return $formatted;
    }

    /**
     * Форматирование цены
     */
    public static function priceFormat($price): string
    {
        if (empty($price)) {
            return '0';
        }

        return number_format((float)$price, 0, '', ' ');
    }

    /**
     * Склонение слов по числу
     */
    public static function declOfNum(int $count, array $titles): string
    {
        $cases = [2, 0, 1, 1, 1, 2];
        $index = ($count % 100 > 4 && $count % 100 < 20) ? 2 : $cases[min($count % 10, 5)];
        
        return $titles[$index] ?? $titles[0] ?? '';
    }

    /**
     * Отправка JWT запроса
     */
    public static function jwt_request(string $url, string $token, array $post, int $timeout = 30): ?array
    {
        $ch = curl_init($url);
        
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: Bearer {$token}"
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($post, JSON_UNESCAPED_UNICODE),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FAILONERROR => true
        ]);
        
        $result = curl_exec($ch);
        
        if (curl_errno($ch)) {
            error_log('CURL Error: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }
        
        curl_close($ch);
        
        return json_decode($result, true);
    }

    /**
     * Получение курсов валют ЦБ РФ с кэшированием
     */
    public static function CBR_XML_Daily_Ru(): ?\stdClass
    {
        $cacheFile = __DIR__ . '/daily.json';
        $cacheTime = 3600; // 1 час
        
        if (!file_exists($cacheFile) || filemtime($cacheFile) < time() - $cacheTime) {
            $json = @file_get_contents('https://www.cbr-xml-daily.ru/daily_json.js');
            
            if ($json && json_decode($json)) {
                file_put_contents($cacheFile, $json);
            } elseif (file_exists($cacheFile)) {
                // Если не удалось получить новые данные, используем старые
                return json_decode(file_get_contents($cacheFile));
            } else {
                return null;
            }
        }
        
        return json_decode(file_get_contents($cacheFile));
    }

    /**
     * Расчет процента
     */
    public static function percent(float $number, float $percent): float
    {
        return round($number + ($number / 100 * $percent));
    }

    /**
     * Форматирование даты в текстовый вид
     */
    public static function dateText(int $timestamp): string
    {
        $monthNum = (int)date('n', $timestamp);
        $monthName = self::MONTHS_RU[$monthNum] ?? '';
        
        return date('j', $timestamp) . ' ' . $monthName . ' ' . date('Y', $timestamp);
    }

    /**
     * Отправка email
     */
    public static function mail(string $to, string $subject, string $message, array $files = []): bool
    {
        try {
            // Проверка и формирование отправителя
            $from = 'robot@' . ($_SERVER['SERVER_NAME'] ?? 'localhost');
            if (!self::validateEmail($from)) {
                $from = 'robot@localhost';
            }
            
            if (empty($message)) {
                $message = "<html><body></body></html>";
            }
            
            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($from);
            $mail->isHTML(true);
            $mail->Subject = $subject;

            // Загрузка конфигурации SMTP
            $configPath = ROOT . '/config/smtp.php';
            if (file_exists($configPath)) {
                $smtp = require $configPath;
                
                // Настройки SMTP из конфигурации
                if (is_array($smtp) && !empty($smtp['enabled'])) {
                    $mail->isSMTP();
                    $mail->Host = $smtp['host'] ?? '';
                    $mail->SMTPAuth = true;
                    $mail->Username = $smtp['user'] ?? '';
                    $mail->Password = $smtp['password'] ?? '';
                    $mail->Port = $smtp['port'] ?? 587;
                    
                    // Выбор шифрования на основе порта
                    $mail->SMTPSecure = ($mail->Port == 465) 
                        ? PHPMailer::ENCRYPTION_SMTPS 
                        : PHPMailer::ENCRYPTION_STARTTLS;
                    
                    // Опционально: отладка SMTP
                    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                }
            }
            
            // Добавление получателей
            $recipients = array_filter(array_map('trim', explode(',', $to)));
            if (empty($recipients)) {
                throw new Exception('No recipients specified');
            }
            
            $validRecipients = 0;
            foreach ($recipients as $email) {
                if (self::validateEmail($email)) {
                    $mail->addAddress($email);
                    $validRecipients++;
                }
            }
            
            if ($validRecipients === 0) {
                throw new Exception('No valid email addresses provided');
            }
            
            $mail->Body = $message;
            
            // Добавление вложений
            foreach ($files as $file) {
                $fullPath = null;
                $filename = null;
                
                if (is_object($file) && isset($file->file, $file->filename, $file->ext)) {
                    $fullPath = ROOT . $file->file;
                    $filename = $file->filename . '.' . $file->ext;
                } elseif (is_array($file) && isset($file['file'], $file['filename'], $file['ext'])) {
                    $fullPath = ROOT . $file['file'];
                    $filename = $file['filename'] . '.' . $file['ext'];
                }
                
                if ($fullPath && file_exists($fullPath)) {
                    $mail->addAttachment($fullPath, $filename);
                }
            }
            
            return $mail->send();
            
        } catch (Exception $e) {
            error_log('Mail error: ' . $e->getMessage());
            self::logError('MAIL', $e->getMessage() . ' | To: ' . $to . ' | Subject: ' . $subject);
            return false;
        }
    }

    /**
     * Валидация email
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Преобразование массива в строку
     */
    public static function arr2str(array $arr, int $level = 0): string
    {
        $str = "array(<br>\n";
        $indent = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $level);
        
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $str .= $indent . "'" . htmlspecialchars($key) . "' => " . 
                       self::arr2str($val, $level + 1) . ",<br>\n";
            } else {
                $str .= $indent . "'" . htmlspecialchars($key) . "' => '<b>" . 
                       htmlspecialchars(str_replace("'", "\'", (string)$val)) . "</b>',<br>\n";
            }
        }
        
        return $str . $indent . ")";
    }

    /**
     * Генерация sitemap.xml с кэшированием
     */
    public static function sitemap(bool $force = false): bool
    {
        $sitemapFile = ROOT . '/sitemap.xml';
        $cacheTime = 3600; // 1 час
        
        if (!$force && file_exists($sitemapFile) && 
            filemtime($sitemapFile) > time() - $cacheTime) {
            return true;
        }
        
        try {
            $xml = new DomDocument('1.0', 'utf-8');
            $root = $xml->appendChild($xml->createElement('urlset'));
            
            $root->appendChild($xml->createAttribute('xmlns'))
                 ->appendChild($xml->createTextNode('https://www.sitemaps.org/schemas/sitemap/0.9'));
            
            $root->appendChild($xml->createAttribute('xmlns:xsi'))
                 ->appendChild($xml->createTextNode('https://www.w3.org/2001/XMLSchema-instance'));
            
            $root->appendChild($xml->createAttribute('xsi:schemaLocation'))
                 ->appendChild($xml->createTextNode(
                     'https://www.sitemaps.org/schemas/sitemap/0.9 https://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd'
                 ));
            
            $baseUrl = "https://" . ($_SERVER['SERVER_NAME'] ?? 'localhost');
            
            // Главная страница
            $url = $root->appendChild($xml->createElement('url'));
            $url->appendChild($xml->createElement('loc'))
                ->appendChild($xml->createTextNode($baseUrl));
            
            // Страницы сайта
            $pages = Pages::findWhere('WHERE `show` = 1 AND name <> "" ORDER BY id ASC');
            
            if (!empty($pages)) {
                foreach ($pages as $item) {
                    if ($item->id != 1) {
                        $pageUrl = $baseUrl . Pages::getUrl($item->id);
                        
                        $url = $root->appendChild($xml->createElement('url'));
                        $url->appendChild($xml->createElement('loc'))
                            ->appendChild($xml->createTextNode($pageUrl));
                    }
                }
            }
            
            $xml->formatOutput = true;
            
            // Создаем резервную копию перед записью
            if (file_exists($sitemapFile)) {
                copy($sitemapFile, $sitemapFile . '.backup');
            }
            
            return $xml->save($sitemapFile) !== false;
            
        } catch (\Exception $e) {
            error_log('Sitemap generation error: ' . $e->getMessage());
            self::logError('SITEMAP', $e->getMessage());
            return false;
        }
    }

    /**
     * Форматирование номера телефона
     */
    public static function formatPhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }
        
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($cleaned) !== 11) {
            return $phone;
        }
        
        // Замена 8 на +7
        if (substr($cleaned, 0, 1) === '8') {
            $cleaned = '7' . substr($cleaned, 1);
        }
        
        return preg_replace(
            '/^(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})$/',
            '+$1 ($2) $3-$4-$5',
            $cleaned
        );
    }

    /**
     * Конвертация раскладки клавиатуры
     */
    public static function switcher(string $text, string $direction = 'en_ru'): string
    {
        if (empty($text) || !isset(self::SWITCHER_MAP[$direction])) {
            return $text;
        }
        
        return strtr($text, self::SWITCHER_MAP[$direction]);
    }

    /**
     * Алиас для switcher (для обратной совместимости)
     */
    public static function switcherEn(string $text): string
    {
        return self::switcher($text, 'ru_en');
    }

    /**
     * Преобразование текста: первая буква заглавная, остальные строчные
     */
    public static function capitalizeText(?string $text): ?string
    {
        if (empty($text)) {
            return null;
        }
        
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = self::replace($text);
        $text = trim($text);
        
        if (empty($text)) {
            return '';
        }
        
        // Переводим весь текст в нижний регистр
        $text = mb_strtolower($text, 'UTF-8');
        
        // Находим первую букву
        $firstCharPos = -1;
        $length = mb_strlen($text, 'UTF-8');
        
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            if (preg_match('/[a-zA-Zа-яА-Я]/u', $char)) {
                $firstCharPos = $i;
                break;
            }
        }
        
        // Преобразуем первую букву в заглавную
        if ($firstCharPos >= 0 && $firstCharPos < $length) {
            $firstChar = mb_substr($text, $firstCharPos, 1, 'UTF-8');
            $before = mb_substr($text, 0, $firstCharPos, 'UTF-8');
            $after = mb_substr($text, $firstCharPos + 1, null, 'UTF-8');
            $text = $before . mb_strtoupper($firstChar, 'UTF-8') . $after;
        }
        
        return $text;
    }

    /**
     * Перевод всего текста в нижний регистр
     */
    public static function lowerText(?string $text): ?string
    {
        if (empty($text)) {
            return null;
        }
        
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = self::replace($text);
        $text = trim($text);
        
        return mb_strtolower($text, 'UTF-8');
    }

    /**
     * Генерация canonical URL
     */
    public static function canonical(bool $useHttps = true): string
    {
        $protocol = $useHttps ? 'https://' : 'http://';
        $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $uri = defined('URI') ? URI : ($_SERVER['REQUEST_URI'] ?? '/');
        
        if ($uri === '/') {
            $uri = '';
        }
        
        return $protocol . $host . '/' . ltrim($uri, '/');
    }

    /**
     * Получение IP адреса пользователя
     */
    public static function get_ip(): string
    {
        $ipSources = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP', 
            'REMOTE_ADDR',
            'HTTP_X_REAL_IP'
        ];
        
        foreach ($ipSources as $source) {
            if (!empty($_SERVER[$source])) {
                $ip = trim(strtok($_SERVER[$source], ','));
                if (self::is_valid_ip($ip)) {
                    return $ip;
                }
            }
        }
        
        return '127.0.0.1';
    }

    /**
     * Проверка валидности IP адреса
     */
    private static function is_valid_ip(?string $ip): bool
    {
        if (empty($ip)) {
            return false;
        }
        
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Генерация хеша
     */
    public static function hash(): string
    {
        return md5(uniqid(mt_rand(), true));
    }

    /**
     * Проверка расширения файла на наличие в черном списке
     */
    public static function blacklist(string $ext): bool
    {
        return in_array(strtolower($ext), self::BLACKLIST_EXTENSIONS, true);
    }

    /**
     * Замена двойных кавычек на «ёлочки»
     */
    public static function replace(?string $text): string
    {
        if (empty($text)) {
            return '';
        }
        
        $text = htmlspecialchars_decode($text, ENT_QUOTES);
        $text = preg_replace('/"([^"]+)"/u', '«$1»', $text);
        $text = str_replace('"', '', $text);
        
        return $text;
    }

    /**
     * Обрезка текста с добавлением многоточия
     */
    public static function text(?string $text, ?int $limit = null, bool $stripTags = true): string
    {
        if (empty($text)) {
            return '';
        }
        
        if ($stripTags) {
            $text = preg_replace('/<a[^>]*>.*?<\/a>/', '', $text);
            $text = trim(strip_tags($text));
        }
        
        $text = preg_replace('/\s+/', ' ', $text);
        $text = self::replace($text);
        $text = trim($text);
        
        if ($limit !== null && $limit > 0 && mb_strlen($text, 'UTF-8') > $limit) {
            $text = mb_substr($text, 0, $limit, 'UTF-8') . '...';
        }
        
        return $text;
    }

    /**
     * Логирование ошибок
     */
    private static function logError(string $type, string $message): void
    {
        $logFile = ROOT . '/logs/errors.log';
        $logDir = dirname($logFile);
        
        // Создаем директорию для логов, если её нет
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logMessage = sprintf(
            "[%s] %s: %s\n",
            date('Y-m-d H:i:s'),
            $type,
            $message
        );
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Безопасное получение значения из массива
     */
    public static function arrayGet(array $array, string $key, $default = null)
    {
        return $array[$key] ?? $default;
    }

    /**
     * Проверка является ли строка JSON
     */
    public static function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Генерация CSRF токена
     */
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_tokens'][$token] = time();
        
        // Очистка старых токенов (старше 1 часа)
        foreach ($_SESSION['csrf_tokens'] as $storedToken => $timestamp) {
            if ($timestamp < time() - 3600) {
                unset($_SESSION['csrf_tokens'][$storedToken]);
            }
        }
        
        return $token;
    }

    /**
     * Проверка CSRF токена
     */
    public static function verifyCsrfToken(string $token): bool
    {
        if (empty($_SESSION['csrf_tokens'][$token])) {
            return false;
        }
        
        $timestamp = $_SESSION['csrf_tokens'][$token];
        
        // Токен действителен 1 час
        if ($timestamp < time() - 3600) {
            unset($_SESSION['csrf_tokens'][$token]);
            return false;
        }
        
        unset($_SESSION['csrf_tokens'][$token]);
        return true;
    }

    /**
     * Экранирует HTML-спецсимволы для безопасного вывода
     * 
     * @param string|null $string Входная строка
     * @param int $flags Флаги для htmlspecialchars (по умолчанию ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401)
     * @param string|null $encoding Кодировка (по умолчанию UTF-8)
     * @param bool $doubleEncode Двойное кодирование
     * @return string Экранированная строка
     */
    public static function escape($string, $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, $encoding = 'UTF-8', $doubleEncode = true)
    {
        if ($string === null) {
            return '';
        }
        
        // Если это не строка, преобразуем в строку
        if (!is_string($string)) {
            $string = (string)$string;
        }
        
        return htmlspecialchars($string, $flags, $encoding, $doubleEncode);
    }
}