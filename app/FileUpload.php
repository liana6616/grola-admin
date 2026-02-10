<?php

namespace app;

use app\Models\Gallery;
use app\Models\Files;

/**
 * Класс для обработки ошибок загрузки
 */
class UploadException extends \Exception
{
    public function __construct($code)
    {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'Размер файла превысил значение upload_max_filesize в конфигурации PHP.',
            UPLOAD_ERR_FORM_SIZE => 'Размер загружаемого файла превысил значение MAX_FILE_SIZE в HTML-форме.',
            UPLOAD_ERR_PARTIAL => 'Загружаемый файл был получен только частично.',
            UPLOAD_ERR_NO_FILE => 'Файл не был загружен.',
            UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная папка.',
            UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск.',
            UPLOAD_ERR_EXTENSION => 'PHP-расширение остановило загрузку файла.',
        ];
        
        $unknownMessage = 'При загрузке файла произошла неизвестная ошибка.';
        
        $message = $errorMessages[$code] ?? $unknownMessage;
        parent::__construct($message, $code);
    }
}

class FileUpload
{
    const MAX_FILE_SIZE_MB = 100;
    const DEFAULT_UPLOAD_PATH = '/public/src/upload/';
    const DEFAULT_QUALITY = 90;
    const HIGH_QUALITY = 100;
    
    // Массив с названиями ошибок
    private static $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'Размер файла превысил значение upload_max_filesize в конфигурации PHP.',
        UPLOAD_ERR_FORM_SIZE => 'Размер загружаемого файла превысил значение MAX_FILE_SIZE в HTML-форме.',
        UPLOAD_ERR_PARTIAL => 'Загружаемый файл был получен только частично.',
        UPLOAD_ERR_NO_FILE => 'Файл не был загружен.',
        UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная папка.',
        UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск.',
        UPLOAD_ERR_EXTENSION => 'PHP-расширение остановило загрузку файла.',
    ];
    
    private static $unknownMessage = 'При загрузке файла произошла неизвестная ошибка.';
    
    // Типы файлов для валидации (добавлен SVG)
    private static $imageMimeTypes = [
        'image/jpeg', 
        'image/png', 
        'image/gif', 
        'image/webp', 
        'image/bmp',
        'image/svg+xml',
        'image/svg'
    ];
    
    // Расширения для SVG
    private static $svgExtensions = ['svg'];
    private static $svgMimeTypes = ['image/svg+xml', 'image/svg', 'text/html', 'text/plain', 'application/xml'];
    
    /**
     * Проверяет, был ли загружен файл
     */
    private static function isFileUploaded($inputName)
    {
        return isset($_FILES[$inputName]) && 
               $_FILES[$inputName]['error'] !== UPLOAD_ERR_NO_FILE &&
               !empty($_FILES[$inputName]['tmp_name']) &&
               is_uploaded_file($_FILES[$inputName]['tmp_name']);
    }
    
    /**
     * Проверяет, были ли загружены файлы в множественном поле
     */
    private static function areFilesUploaded($inputName)
    {
        if (!isset($_FILES[$inputName]) || empty($_FILES[$inputName]['tmp_name'])) {
            return false;
        }
        
        // Проверяем хотя бы один файл в множественной загрузке
        $tmpNames = $_FILES[$inputName]['tmp_name'];
        foreach ($tmpNames as $tmpName) {
            if (!empty($tmpName) && is_uploaded_file($tmpName)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Загружает изображение и создает миниатюру
     */
    public static function uploadImage($inputName, $class, $field, $id, $width, $height, $path = '', $fitMode = 0, $width2 = null, $height2 = null, $field2 = null)
    {
        $path = empty($path) ? self::DEFAULT_UPLOAD_PATH : $path;
        self::ensureDirectory($path);
        
        // Проверяем, был ли файл действительно загружен
        if (!self::isFileUploaded($inputName)) {
            // Файл не был загружен - это нормальная ситуация, просто выходим
            return;
        }
        
        try {
            $fileInfo = self::validateAndGetFileInfo($inputName, true);
            
            $extension = self::getExtension($fileInfo['name']);
            $format = self::normalizeExtension($extension);
            
            $name = uniqid();
            $name2 = $name . '_copy';
            
            $mainPath = ROOT . $path . $name . $format;
            $smallPath = !empty($width2) && !empty($height2) ? ROOT . $path . $name2 . $format : null;
            
            self::moveUploadedFile($fileInfo['tmp_path'], $mainPath);
            
            // Проверяем, является ли файл SVG
            $isSvg = self::isSvgFile($mainPath);
            
            // Для SVG не выполняем ресайз и обработку, просто копируем
            if ($isSvg) {
                // Для SVG файлов просто сохраняем как есть
                if ($smallPath && !empty($width2) && !empty($height2)) {
                    copy($mainPath, $smallPath);
                    // Для SVG можно добавить атрибуты width/height в сам файл если нужно
                    self::processSvgDimensions($smallPath, $width2, $height2);
                }
            } else {
                // Обработка растровых изображений
                if (!empty($width) && !empty($height)) {
                    self::processImage($mainPath, $mainPath, $width, $height, $fitMode == 1, self::HIGH_QUALITY);
                } elseif (!empty($width)) {
                    self::resizeImageByWidth($mainPath, $mainPath, $width, self::HIGH_QUALITY);
                }
                
                // Обработка миниатюры
                if ($smallPath && !empty($width2) && !empty($height2)) {
                    copy($mainPath, $smallPath);
                    self::processImage($smallPath, $smallPath, $width2, $height2, $fitMode == 1, self::DEFAULT_QUALITY);
                }
            }
            
            self::saveToDatabase($id, $class, $field, $path, $name, $format, $field2, $smallPath ? $name2 : null);
            
        } catch (\Exception $e) {
            self::logError($e->getMessage(), ['input' => $inputName, 'id' => $id]);
            throw $e;
        }
    }
    
    /**
     * Загружает обычный файл
     */
    public static function uploadFile($inputName, $class, $field, $id, $path = '', $customName = '')
    {
        $path = empty($path) ? self::DEFAULT_UPLOAD_PATH : $path;
        self::ensureDirectory($path);
        
        // Проверяем, был ли файл действительно загружен
        if (!self::isFileUploaded($inputName)) {
            // Файл не был загружен - это нормальная ситуация, просто выходим
            return;
        }
        
        try {
            $fileInfo = self::validateAndGetFileInfo($inputName, false);
            
            $extension = self::getExtension($fileInfo['name']);
            $format = self::normalizeExtension($extension);
            
            $name = !empty($customName) ? $customName : uniqid();
            $serverPath = ROOT . $path . $name . $format;
            
            self::moveUploadedFile($fileInfo['tmp_path'], $serverPath);
            self::saveToDatabase($id, $class, $field, $path, $name, $format);
            
        } catch (\Exception $e) {
            self::logError($e->getMessage(), ['input' => $inputName, 'id' => $id]);
            throw $e;
        }
    }
    
    /**
     * Загружает несколько изображений в галерею
     */
    public static function uploadGallery($inputName, $type, $id, $width1, $height1, $path = '', $width2 = null, $height2 = null, $fitMode = 0)
    {
        error_log("uploadGallery called: inputName=$inputName, id=$id");
        error_log("FILES array exists: " . (isset($_FILES[$inputName]) ? 'yes' : 'no'));
        
        if (isset($_FILES[$inputName])) {
            error_log("FILES content: " . print_r($_FILES[$inputName], true));
        }
        
        $path = empty($path) ? self::DEFAULT_UPLOAD_PATH : $path;
        self::ensureDirectory($path);
        
        // Проверяем, были ли файлы действительно загружены
        if (!isset($_FILES[$inputName]) || empty($_FILES[$inputName]['tmp_name'])) {
            // Файлы не были загружены - это нормальная ситуация, просто выходим
            return;
        }
        
        // Проверяем, есть ли хотя бы один загруженный файл
        $hasFiles = false;
        $tmpNames = $_FILES[$inputName]['tmp_name'];
        if (is_array($tmpNames)) {
            foreach ($tmpNames as $tmpName) {
                if (!empty($tmpName) && is_uploaded_file($tmpName)) {
                    $hasFiles = true;
                    break;
                }
            }
        }
        
        if (!$hasFiles) {
            return;
        }
        
        $count = count($_FILES[$inputName]['tmp_name']);
        
        for ($i = 0; $i < $count; $i++) {
            try {
                // Пропускаем пустые файлы
                if (empty($_FILES[$inputName]['tmp_name'][$i]) || 
                    $_FILES[$inputName]['error'][$i] === UPLOAD_ERR_NO_FILE ||
                    !is_uploaded_file($_FILES[$inputName]['tmp_name'][$i])) {
                    continue;
                }
                
                $fileInfo = [
                    'tmp_path' => $_FILES[$inputName]['tmp_name'][$i],
                    'name' => $_FILES[$inputName]['name'][$i],
                    'error' => $_FILES[$inputName]['error'][$i]
                ];
                
                self::validateFile($fileInfo['tmp_path'], $fileInfo['error'], ['image']);
                
                $extension = self::getExtension($fileInfo['name']);
                $format = self::normalizeExtension($extension);
                
                $name = uniqid();
                $serverPath = ROOT . rtrim($path, '/') . '/' . $name . $format;
                $serverPathBig = ROOT . rtrim($path, '/') . '/' . $name . '_big' . $format;
                $serverPathSmall = ROOT . rtrim($path, '/') . '/' . $name . '_small' . $format;
                
                self::moveUploadedFile($fileInfo['tmp_path'], $serverPath);
                
                $bigFile = $name . '_big' . $format;
                $smallFile = $name . '_small' . $format;
                
                // Проверяем, является ли файл SVG
                $isSvg = self::isSvgFile($serverPath);
                
                // Обработка большого изображения
                if (!empty($width1) && !empty($height1)) {
                    if (!$isSvg) {
                        // Для растровых изображений
                        copy($serverPath, $serverPathBig);
                        self::processImage($serverPathBig, $serverPathBig, $width1, $height1, $fitMode == 1, self::HIGH_QUALITY);
                    } else {
                        // Для SVG просто копируем и обрабатываем размеры
                        copy($serverPath, $serverPathBig);
                        self::processSvgDimensions($serverPathBig, $width1, $height1);
                    }
                }
                
                // Обработка маленького изображения
                if (!empty($width2) && !empty($height2)) {
                    if (!$isSvg) {
                        // Для растровых изображений
                        copy($serverPath, $serverPathSmall);
                        self::processImage($serverPathSmall, $serverPathSmall, $width2, $height2, $fitMode == 1, self::DEFAULT_QUALITY);
                    } else {
                        // Для SVG просто копируем и обрабатываем размеры
                        copy($serverPath, $serverPathSmall);
                        self::processSvgDimensions($serverPathSmall, $width2, $height2);
                    }
                } else {
                    $serverPathSmall = $serverPathBig;
                    $smallFile = $bigFile;
                }
                
                self::addToGallery($id, $type, $path, $name . $format, $smallFile, $bigFile);
                
            } catch (\Exception $e) {
                self::logError($e->getMessage(), ['input' => $inputName, 'index' => $i, 'id' => $id]);
                continue;
            }
        }
    }
    
    /**
     * Загружает несколько файлов
     */
    public static function uploadFiles($inputName, $type, $id, $path = '')
    {
        $path = empty($path) ? self::DEFAULT_UPLOAD_PATH : $path;
        self::ensureDirectory($path);
        
        // Проверяем, были ли файлы действительно загружены
        if (!self::areFilesUploaded($inputName)) {
            // Файлы не были загружены - это нормальная ситуация, просто выходим
            return;
        }
        
        $count = count($_FILES[$inputName]['tmp_name']);
        
        for ($i = 0; $i < $count; $i++) {
            try {
                // Пропускаем пустые файлы
                if (empty($_FILES[$inputName]['tmp_name'][$i]) || 
                    $_FILES[$inputName]['error'][$i] === UPLOAD_ERR_NO_FILE ||
                    !is_uploaded_file($_FILES[$inputName]['tmp_name'][$i])) {
                    continue;
                }
                
                $fileInfo = [
                    'tmp_path' => $_FILES[$inputName]['tmp_name'][$i],
                    'name' => $_FILES[$inputName]['name'][$i],
                    'error' => $_FILES[$inputName]['error'][$i]
                ];
                
                self::validateFile($fileInfo['tmp_path'], $fileInfo['error'], ['image', 'application', 'text']);
                
                $extension = self::getExtension($fileInfo['name']);
                $format = self::normalizeExtension($extension);
                
                $name = uniqid();
                $serverPath = ROOT . $path . $name . $format;
                
                self::moveUploadedFile($fileInfo['tmp_path'], $serverPath);
                self::addToFiles($id, $type, $path, $name, $format, $fileInfo['name']);
                
            } catch (\Exception $e) {
                self::logError($e->getMessage(), ['input' => $inputName, 'index' => $i, 'id' => $id]);
                continue;
            }
        }
    }
    
    /**
     * Удаляет изображение из объекта
     */
    public static function deleteImageFile()
    {    
        if(isset($_POST['image_preview_del']))
        {
            foreach($_POST['image_preview_del'] AS $k=>$nm)
            {
                if(!empty($nm))
                {
                    $i = $_POST['image_preview_id'][$k];
                    $c = explode('\\',$_POST['image_preview_class'][$k]);
                    $c[2] = ucfirst($c[2]);
                    $c = implode('\\',$c);
                    $item = $c::findById($i);
                    unlink(ROOT.$item->$nm);
                    $item->$nm = '';
                    $item->save();
                }
            }
        }
    }
    
    /**
     * Обновляет рейтинг и удаляет изображения из галереи
     */
    public static function updateGallery()
    {
        // Проверяем, существуют ли ключи
        if (!isset($_POST['gallery_id']) || !isset($_POST['gallery_rate'])) {
            return; // Просто выходим, если данных нет
        }
        
        $gal_id = $_POST['gallery_id'];
        $gal_rate = $_POST['gallery_rate'];
        $gal_alt = $_POST['gallery_alt'] ?? [];
        $gal_show = $_POST['gallery_show'] ?? [];
        
        if(!empty($gal_id)) {
            foreach($gal_id AS $i=>$val) {
                $item = Gallery::findById($val);
                $item->rate = (int)($gal_rate[$i] ?? 0);
                $item->show = (int)($gal_show[$i] ?? 1);
                $item->alt = (string)($gal_alt[$i] ?? '');
                $item->save();
            }
        }

        if(isset($_POST['image_gallery_del']) && !empty($_POST['image_gallery_del']))
        {
            foreach($_POST['image_gallery_del'] AS $gal)
            {
                if(!empty($gal)) Gallery::del($gal);
            }
        }
    }
    
    /**
     * Обновляет рейтинг и удаляет изображения из галереи
     */
    public static function updateFiles()
    {
        if(!empty($_POST['files_id'])) {
            $files_id = $_POST['files_id'];
            $files_rate = $_POST['files_rate'];
            $files_show = $_POST['files_show'];
            $files_name = $_POST['files_name'];
            if(!empty($files_id)) {
                foreach($files_id AS $i=>$val) {
                    $item = Files::findById($val);
                    $item->rate = (int)$files_rate[$i];
                    $item->show = (int)$files_show[$i];
                    $item->filename = (string)$files_name[$i];
                    $item->save();
                }
            }
        }

        if(isset($_POST['files_del']) && !empty($_POST['files_del']))
        {
            foreach($_POST['files_del'] AS $file)
            {
                if(!empty($file)) Files::del($file);
            }
        }
    }
    
    /**
     * Вспомогательные методы
     */
    
    private static function validateAndGetFileInfo($inputName, $isImage = true)
    {
        if (!isset($_FILES[$inputName]) || !is_uploaded_file($_FILES[$inputName]['tmp_name'])) {
            throw new \Exception('Файл не был загружен');
        }
        
        $fileInfo = [
            'tmp_path' => $_FILES[$inputName]['tmp_name'],
            'name' => $_FILES[$inputName]['name'],
            'error' => $_FILES[$inputName]['error']
        ];
        
        self::validateFile($fileInfo['tmp_path'], $fileInfo['error'], $isImage ? ['image'] : ['image', 'application', 'text']);
        
        return $fileInfo;
    }
    
    private static function validateFile($filePath, $errorCode, $allowedTypes = [])
    {
        if ($errorCode !== UPLOAD_ERR_OK || !is_uploaded_file($filePath)) {
            throw new UploadException($errorCode);
        }
        
        $limitBytes = 1024 * 1024 * self::MAX_FILE_SIZE_MB;
        if (filesize($filePath) > $limitBytes) {
            throw new \Exception('Размер файла не должен превышать ' . self::MAX_FILE_SIZE_MB . ' Мбайт.');
        }
        
        if (in_array('image', $allowedTypes)) {
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            $mime = (string) finfo_file($fi, $filePath);
            
            // Специальная проверка для SVG
            if (self::isSvgMimeType($mime) || self::isSvgFile($filePath)) {
                // Дополнительная проверка безопасности SVG
                self::validateSvgFile($filePath);
                return;
            }
            
            if (strpos($mime, 'image') !== 0) {
                throw new \Exception('Можно загружать только изображения.');
            }
            
            // Дополнительная проверка MIME-типа
            if (!in_array($mime, self::$imageMimeTypes)) {
                throw new \Exception('Недопустимый тип изображения: ' . $mime);
            }
        }
    }
    
    /**
     * Проверяет, является ли файл SVG по MIME-типу
     */
    private static function isSvgMimeType($mime)
    {
        return in_array($mime, self::$svgMimeTypes);
    }
    
    /**
     * Проверяет, является ли файл SVG по расширению и содержимому
     */
    private static function isSvgFile($filePath)
    {
        $extension = self::getExtension($filePath);
        
        // Проверка по расширению
        if (in_array(strtolower($extension), self::$svgExtensions)) {
            return true;
        }
        
        // Проверка по содержимому
        $content = file_get_contents($filePath, false, null, 0, 100);
        if ($content === false) {
            return false;
        }
        
        // Ищем SVG теги в начале файла
        return stripos($content, '<svg') !== false || stripos($content, '<?xml') !== false;
    }
    
    /**
     * Проверяет SVG файл на безопасность
     */
    private static function validateSvgFile($filePath)
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \Exception('Не удалось прочитать SVG файл');
        }
        
        // Проверка на потенциально опасные теги и атрибуты
        $dangerousPatterns = [
            '/<script/i',
            '/onload=/i',
            '/onerror=/i',
            '/onclick=/i',
            '/javascript:/i',
            '/data:/i',
            '/base64/i',
            '/<!ENTITY/i',
            '/<!DOCTYPE/i',
            '/xlink:href\s*=\s*["\']?\s*javascript:/i',
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                throw new \Exception('SVG файл содержит потенциально опасный код');
            }
        }
        
        // Проверяем, что это валидный XML/SVG
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        
        // Пытаемся загрузить как XML
        if (!$dom->loadXML($content, LIBXML_NOENT | LIBXML_NOCDATA)) {
            // Если не удалось как XML, пытаемся как HTML
            if (!$dom->loadHTML($content, LIBXML_NOENT | LIBXML_NOCDATA)) {
                throw new \Exception('SVG файл содержит невалидный XML/HTML');
            }
        }
        
        // Проверяем наличие SVG тега
        $svgElements = $dom->getElementsByTagName('svg');
        if ($svgElements->length === 0) {
            throw new \Exception('Файл не содержит SVG тегов');
        }
    }
    
    /**
     * Обрабатывает размеры SVG файла (добавляет атрибуты width/height)
     */
    private static function processSvgDimensions($filePath, $width, $height)
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return;
        }
        
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        
        // Пытаемся загрузить
        if ($dom->loadXML($content)) {
            $svgElements = $dom->getElementsByTagName('svg');
            if ($svgElements->length > 0) {
                $svg = $svgElements->item(0);
                
                // Устанавливаем ширину и высоту
                $svg->setAttribute('width', $width);
                $svg->setAttribute('height', $height);
                
                // Также устанавливаем viewBox если его нет
                if (!$svg->hasAttribute('viewBox')) {
                    $svg->setAttribute('viewBox', "0 0 $width $height");
                }
                
                // Сохраняем обратно
                $dom->formatOutput = true;
                $newContent = $dom->saveXML();
                
                if ($newContent !== false) {
                    file_put_contents($filePath, $newContent);
                }
            }
        }
    }
    
    private static function ensureDirectory($path)
    {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $path;
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
    }
    
    private static function getExtension($fileName)
    {
        $parts = explode('.', $fileName);
        return strtolower(end($parts));
    }
    
    private static function normalizeExtension($extension)
    {
        $extension = strtolower($extension);
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }
        return '.' . $extension;
    }
    
    private static function moveUploadedFile($source, $destination)
    {
        if (!move_uploaded_file($source, $destination)) {
            throw new \Exception('При записи файла на диск произошла ошибка.');
        }
    }
    
    private static function processImage($sourcePath, $targetPath, $targetWidth, $targetHeight, $fitMode, $quality)
    {
        if (!file_exists($sourcePath)) {
            throw new \Exception('Исходный файл не существует.');
        }
        
        // Проверяем, не SVG ли это
        if (self::isSvgFile($sourcePath)) {
            // Для SVG просто копируем и обрабатываем размеры
            copy($sourcePath, $targetPath);
            self::processSvgDimensions($targetPath, $targetWidth, $targetHeight);
            return;
        }
        
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            throw new \Exception('Невозможно прочитать изображение.');
        }
        
        list($srcWidth, $srcHeight) = $imageInfo;
        
        if ($fitMode) {
            // Режим вписывания с сохранением пропорций
            $dimensions = self::calculateFitDimensions($srcWidth, $srcHeight, $targetWidth, $targetHeight);
        } else {
            // Режим обрезки
            $dimensions = self::calculateCropDimensions($srcWidth, $srcHeight, $targetWidth, $targetHeight);
            
            // Сначала изменяем размер
            $tempPath = $targetPath . '.tmp';
            copy($sourcePath, $tempPath);
            self::resizeImage($tempPath, $tempPath, $dimensions['resize_width'], $dimensions['resize_height'], $quality);
            
            // Затем обрезаем
            self::cropImage($tempPath, $targetPath, $targetWidth, $targetHeight, $quality);
            unlink($tempPath);
            return;
        }
        
        if ($dimensions['width'] > 0 && $dimensions['height'] > 0) {
            self::resizeImage($sourcePath, $targetPath, $dimensions['width'], $dimensions['height'], $quality);
        }
    }
    
    private static function calculateFitDimensions($srcWidth, $srcHeight, $targetWidth, $targetHeight)
    {
        $width = $height = 0;
        
        if ($srcHeight >= $targetHeight) {
            $height = $targetHeight;
            $width = ($height / $srcHeight) * $srcWidth;
            
            if ($width > $targetWidth) {
                $width = $targetWidth;
                $height = ($width / $srcWidth) * $srcHeight;
            }
        } elseif ($srcWidth >= $targetWidth) {
            $width = $targetWidth;
            $height = ($width / $srcWidth) * $srcHeight;
            
            if ($height > $targetHeight) {
                $height = $targetHeight;
                $width = ($height / $srcHeight) * $srcWidth;
            }
        }
        
        return [
            'width' => round($width),
            'height' => round($height)
        ];
    }
    
    private static function calculateCropDimensions($srcWidth, $srcHeight, $targetWidth, $targetHeight)
    {
        $height = $targetHeight;
        $width = ($height / $srcHeight) * $srcWidth;
        
        if ($width < $targetWidth) {
            $width = $targetWidth;
            $height = ($width / $srcWidth) * $srcHeight;
        } elseif ($height < $targetHeight) {
            $height = $targetHeight;
            $width = ($height / $srcHeight) * $srcWidth;
        }
        
        return [
            'resize_width' => round($width),
            'resize_height' => round($height),
            'crop_width' => $targetWidth,
            'crop_height' => $targetHeight
        ];
    }
    
    private static function resizeImage($sourcePath, $targetPath, $width, $height, $quality)
    {
        // Проверяем, не SVG ли это
        if (self::isSvgFile($sourcePath)) {
            // Для SVG просто копируем и обрабатываем размеры
            copy($sourcePath, $targetPath);
            self::processSvgDimensions($targetPath, $width, $height);
            return;
        }
        
        // Используем Imagick если доступен
        if (extension_loaded('imagick')) {
            $imagick = new \Imagick($sourcePath);
            $imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);
            $imagick->setImageCompressionQuality($quality);
            $imagick->writeImage($targetPath);
            $imagick->destroy();
        } elseif (function_exists('gd_info')) {
            // Fallback на GD
            self::resizeImageGD($sourcePath, $targetPath, $width, $height, $quality);
        } else {
            // Если нет ни GD ни Imagick, используем exec как fallback
            exec("convert -resize {$width}x{$height} -quality {$quality} \"{$sourcePath}\" \"{$targetPath}\"");
        }
    }
    
    private static function resizeImageGD($sourcePath, $targetPath, $width, $height, $quality)
    {
        // Проверяем, не SVG ли это
        if (self::isSvgFile($sourcePath)) {
            copy($sourcePath, $targetPath);
            return;
        }
        
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return;
        }
        
        $mime = $imageInfo['mime'];
        
        switch ($mime) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return;
        }
        
        if (!$sourceImage) {
            return;
        }
        
        $destinationImage = imagecreatetruecolor($width, $height);
        
        // Сохраняем прозрачность для PNG и GIF
        if ($mime === 'image/png' || $mime === 'image/gif') {
            imagecolortransparent($destinationImage, imagecolorallocatealpha($destinationImage, 0, 0, 0, 127));
            imagealphablending($destinationImage, false);
            imagesavealpha($destinationImage, true);
        }
        
        imagecopyresampled($destinationImage, $sourceImage, 0, 0, 0, 0, $width, $height, imagesx($sourceImage), imagesy($sourceImage));
        
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($destinationImage, $targetPath, $quality);
                break;
            case 'image/png':
                imagepng($destinationImage, $targetPath);
                break;
            case 'image/gif':
                imagegif($destinationImage, $targetPath);
                break;
            case 'image/webp':
                imagewebp($destinationImage, $targetPath, $quality);
                break;
        }
        
        imagedestroy($sourceImage);
        imagedestroy($destinationImage);
    }
    
    private static function resizeImageByWidth($sourcePath, $targetPath, $width, $quality)
    {
        // Проверяем, не SVG ли это
        if (self::isSvgFile($sourcePath)) {
            copy($sourcePath, $targetPath);
            self::processSvgDimensions($targetPath, $width, '100%'); // Высота автоматическая
            return;
        }
        
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return;
        }
        
        $srcWidth = $imageInfo[0];
        $srcHeight = $imageInfo[1];
        $height = ($width / $srcWidth) * $srcHeight;
        
        self::resizeImage($sourcePath, $targetPath, $width, $height, $quality);
    }
    
    private static function cropImage($sourcePath, $targetPath, $width, $height, $quality)
    {
        // Для SVG кроп не применяется
        if (self::isSvgFile($sourcePath)) {
            copy($sourcePath, $targetPath);
            self::processSvgDimensions($targetPath, $width, $height);
            return;
        }
        
        if (extension_loaded('imagick')) {
            $imagick = new \Imagick($sourcePath);
            $imagick->cropImage($width, $height, 0, 0);
            $imagick->setImageCompressionQuality($quality);
            $imagick->writeImage($targetPath);
            $imagick->destroy();
        } elseif (function_exists('gd_info')) {
            self::cropImageGD($sourcePath, $targetPath, $width, $height, $quality);
        } else {
            exec("convert -gravity Center -crop {$width}x{$height}+0+0 +repage -quality {$quality} \"{$sourcePath}\" \"{$targetPath}\"");
        }
    }
    
    private static function cropImageGD($sourcePath, $targetPath, $width, $height, $quality)
    {
        // Проверяем, не SVG ли это
        if (self::isSvgFile($sourcePath)) {
            copy($sourcePath, $targetPath);
            return;
        }
        
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return;
        }
        
        $mime = $imageInfo['mime'];
        
        switch ($mime) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return;
        }
        
        if (!$sourceImage) {
            return;
        }
        
        $srcWidth = imagesx($sourceImage);
        $srcHeight = imagesy($sourceImage);
        
        $srcX = max(0, ($srcWidth - $width) / 2);
        $srcY = max(0, ($srcHeight - $height) / 2);
        
        $destinationImage = imagecreatetruecolor($width, $height);
        
        if ($mime === 'image/png' || $mime === 'image/gif') {
            imagecolortransparent($destinationImage, imagecolorallocatealpha($destinationImage, 0, 0, 0, 127));
            imagealphablending($destinationImage, false);
            imagesavealpha($destinationImage, true);
        }
        
        imagecopy($destinationImage, $sourceImage, 0, 0, $srcX, $srcY, $width, $height);
        
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($destinationImage, $targetPath, $quality);
                break;
            case 'image/png':
                imagepng($destinationImage, $targetPath);
                break;
            case 'image/gif':
                imagegif($destinationImage, $targetPath);
                break;
            case 'image/webp':
                imagewebp($destinationImage, $targetPath, $quality);
                break;
        }
        
        imagedestroy($sourceImage);
        imagedestroy($destinationImage);
    }
    
    private static function saveToDatabase($id, $class, $field, $path, $fileName, $extension, $field2 = null, $fileName2 = null)
    {
        $object = $class::findById($id);
        if (!$object) {
            throw new \Exception('Объект не найден');
        }
        
        // Удаляем старые файлы
        if (!empty($object->$field)) {
            $oldPath = ROOT . $object->$field;
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }
        
        $object->$field = $path . $fileName . $extension;
        
        if (!empty($field2) && !empty($fileName2)) {
            if (!empty($object->$field2)) {
                $oldPath2 = ROOT . $object->$field2;
                if (file_exists($oldPath2)) {
                    @unlink($oldPath2);
                }
            }
            $object->$field2 = $path . $fileName2 . $extension;
        }
        
        $object->save();
    }
    
    protected static function addToGallery($id, $type, $path, $file, $fileSmall, $fileBig)
    {
        $big = !empty($fileBig) ? $path . $fileBig : '';
        $small = !empty($fileSmall) ? $path . $fileSmall : '';
        
        $gallery = new Gallery();
        $gallery->type = $type;
        $gallery->ids = $id;
        $gallery->image = $big;
        $gallery->image_small = $small;
        $gallery->image_origin = $path . $file;
        $gallery->alt = '';
        $gallery->rate = '0';
        $gallery->show = '1';
        
        $result = $gallery->save();
                
        return $result;
    }
    
    protected static function addToFiles($id, $type, $path, $file, $extension, $originalName)
    {
        $files = new Files();
        $files->type = $type;
        $files->ids = $id;
        $files->file = $path . $file . $extension;
        $files->filename = $originalName;
        $files->extension = trim($extension, '.');
        $files->rate = '0';
        $files->show = '1';
        $files->save();
    }
    
    private static function logError($message, $context = [])
    {
        // Можно использовать Monolog или другую библиотеку логирования
        error_log('FileUpload Error: ' . $message . ' Context: ' . json_encode($context));
    }
}