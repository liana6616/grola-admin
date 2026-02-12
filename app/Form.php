<?php

declare(strict_types=1);

namespace app;

class Form
{
    private const TEMPLATES_PATH = '/private/views/templates/';

    /**
     * Рендерит шаблон с переданными данными
     *
     * @param string $template Имя файла шаблона
     * @param array $data Данные для передачи в шаблон
     * @return string HTML-код шаблона
     * @throws \RuntimeException Если файл шаблона не найден
     */
    private static function renderTemplate(string $template, array $data = []): string
    {
        $fullPath = ROOT . self::TEMPLATES_PATH . $template;
        
        if (!file_exists($fullPath)) {
            throw new \RuntimeException("Template not found: {$template}");
        }
        
        extract($data);
        ob_start();
        include $fullPath;
        return ob_get_clean();
    }

    /**
     * Создает текстовое поле ввода
     *
     * @param string $title Заголовок поля
     * @param string $name Имя поля
     * @param string|null $value Значение поля
     * @param bool $required Обязательное поле
     * @param string $type Тип поля (text, number, password и т.д.)
     * @param bool $disabled Отключить поле
     * @param string $class CSS классы
     * @return string HTML-код поля ввода
     */
    public static function input(
        string $title,
        string $name,
        ?string $value = '',
        bool $required = false,
        string $type = 'text',
        bool $disabled = false,
        string $class = ''
    ): string {
        return self::renderTemplate('input.php', [
            'title' => $title,
            'name' => $name,
            'value' => $value ?? '',
            'required' => $required,
            'type' => $type,
            'disabled' => $disabled,
            'class' => $class
        ]);
    }

    /**
     * Создает кнопку отправки формы
     *
     * @param string $id ID элемента (определяет режим: edit/add)
     * @param string $value Значение кнопки
     * @param string $text Текст кнопки
     * @param string $ids Дополнительный идентификатор контекста
     * @return string HTML-код кнопки отправки
     */
    public static function submit(
        string $id,
        string $value,
        string $text = 'Отправить',
        string $ids = ''
    ): string {
        // Определяем тип кнопки на основе переданных параметров
        $buttonType = 'add'; // По умолчанию
        
        $isEdit = !empty($id) && $id !== '0';
        
        // Определяем текущий URL для понимания контекста
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
        
        // Проверяем параметры в URL для определения уровня
        if (isset($_GET['group_id']) && isset($_GET['ids'])) {
            // Уровень 3: Параметры в группе (шаблоны параметров)
            $buttonType = $isEdit ? 'editItem' : 'addItem';
        } elseif (isset($_GET['ids']) && !isset($_GET['group_id'])) {
            // Уровень 2: Группы в шаблоне (шаблоны параметров) ИЛИ значения в справочнике
            // Нужно проверить, что редактируем
            if (isset($_GET['editItem']) || isset($_GET['addItem']) || isset($_GET['copyItem'])) {
                // Это значение справочника
                $buttonType = $isEdit ? 'editItem' : 'addItem';
            } elseif (isset($_GET['editGroup']) || isset($_GET['addGroup']) || isset($_GET['copyGroup'])) {
                // Это группа шаблона параметров
                $buttonType = $isEdit ? 'editGroup' : 'addGroup';
            } else {
                // По умолчанию для директорий
                $buttonType = $isEdit ? 'editItem' : 'addItem';
            }
        } else {
            // Уровень 1: Шаблоны или справочники
            $buttonType = $isEdit ? 'edit' : 'add';
        }
        
        return self::renderTemplate('submit.php', [
            'buttonType' => $buttonType,
            'id' => $id,
            'value' => $value,
            'text' => $text,
            'ids' => $ids
        ]);
    }

    /**
     * Создает чекбокс
     *
     * @param string $name Имя поля
     * @param bool $checked Состояние чекбокса
     * @param string $title Заголовок чекбокса
     * @param string $value Значение поля
     * @param string|null $id ID поля (опционально)
     * @param bool $disabled Отключить поле
     * @return string HTML-код чекбокса
     */
    public static function checkbox(
        string $name,
        bool $checked,
        string $title = 'Показывать на сайте',
        string $value = '1',
        ?string $id = null,
        bool $disabled = false
    ): string {
        return self::renderTemplate('checkbox.php', [
            'name' => $name,
            'checked' => $checked,
            'title' => $title,
            'value' => $value,
            'id' => $id,
            'disabled' => $disabled
        ]);
    }

    /**
     * Создает группу радиокнопок
     *
     * @param string $title Заголовок группы
     * @param string $name Имя поля
     * @param array $items Массив элементов [значение => метка]
     * @param string|null $checked Выбранное значение
     * @param string $class Дополнительные CSS классы
     * @param bool $horizontal Горизонтальное расположение
     * @param string $size Размер (small, normal, large)
     * @param bool $disabled Отключить группу полей
     * @return string HTML-код группы радиокнопок
     */
    public static function radio(
        string $title,
        string $name,
        array $items = [],
        ?string $checked = null,
        string $class = '',
        bool $horizontal = false,
        string $size = 'normal',
        bool $disabled = false
    ): string {
        return self::renderTemplate('radio.php', [
            'title' => $title,
            'name' => $name,
            'items' => $items,
            'checked' => $checked,
            'class' => $class,
            'horizontal' => $horizontal,
            'size' => $size,
            'disabled' => $disabled
        ]);
    }

    /**
     * Создает многострочное текстовое поле
     *
     * @param string $title Заголовок поля
     * @param string $name Имя поля
     * @param string|null $value Значение поля
     * @param int|null $height Высота поля в пикселях
     * @param bool $disabled Отключить поле
     * @param string $class CSS классы
     * @return string HTML-код текстового поля
     */
    public static function textarea(
        string $title,
        string $name,
        ?string $value = '',
        ?int $height = null,
        bool $disabled = false,
        string $class = ''
    ): string {
        return self::renderTemplate('textarea.php', [
            'title' => $title,
            'name' => $name,
            'value' => $value ?? '',
            'height' => $height,
            'disabled' => $disabled,
            'class' => $class
        ]);
    }

    /**
     * Создает поле для текстового редактора (WYSIWYG)
     *
     * @param string $title Заголовок поля
     * @param string $name Имя поля
     * @param string|null $value Значение поля (HTML)
     * @param bool $disabled Отключить поле
     * @param string $class CSS классы
     * @return string HTML-код поля текстового редактора
     */
    public static function textbox(
        string $title,
        string $name,
        ?string $value = '',
        bool $disabled = false,
        string $class = ''
    ): string {
        return self::renderTemplate('textbox.php', [
            'title' => $title,
            'name' => $name,
            'value' => $value ?? '',
            'disabled' => $disabled,
            'class' => $class
        ]);
    }

    /**
     * Создает поле для загрузки изображения
     *
     * @param string $title Заголовок поля
     * @param string $name Имя поля (name атрибут input)
     * @param mixed $object Объект с данными изображения
     * @param string $dbField Название поля в базе данных (если отличается от name)
     * @param bool $required Обязательное поле
     * @param bool $disabled Отключить поле
     * @return string HTML-код поля для изображения
     */
    public static function image(
        string $title,
        string $name,
        $object,
        bool $required = false,
        bool $disabled = false,
        string $dbField = ''
    ): string {
        // Если dbField не указано, используем name
        $field = !empty($dbField) ? $dbField : $name;
        
        return self::renderTemplate('image.php', [
            'title' => $title,
            'name' => $name,
            'dbField' => $field,
            'object' => $object,
            'required' => $required,
            'disabled' => $disabled
        ]);
    }

    /**
     * Создает поле для загрузки файла
     *
     * @param string $title Заголовок поля
     * @param string $name Имя поля (name атрибут input)
     * @param mixed $object Объект с данными файла
     * @param string $dbField Название поля в базе данных (если отличается от name)
     * @param bool $required Обязательное поле
     * @param bool $disabled Отключить поле
     * @return string HTML-код поля для файла
     */
    public static function file(
        string $title,
        string $name,
        $object,
        bool $required = false,
        bool $disabled = false,
        string $dbField = ''
    ): string {
        // Если dbField не указано, используем name
        $field = !empty($dbField) ? $dbField : $name;
        
        return self::renderTemplate('file.php', [
            'title' => $title,
            'name' => $name,
            'dbField' => $field,
            'object' => $object,
            'required' => $required,
            'disabled' => $disabled
        ]);
    }

    /**
     * Создает поле для множественной загрузки файлов
     *
     * @param string $title Заголовок поля
     * @param string $name Имя поля
     * @param mixed $objects Массив объектов с файлами
     * @param bool $required Обязательное поле
     * @param string $accept Разрешенные типы файлов
     * @param bool $disabled Отключить поле
     * @return string HTML-код поля для множественной загрузки файлов
     */
    public static function files(
        string $title,
        string $name,
        $objects,
        bool $required = false,
        string $accept = '',
        bool $disabled = false
    ): string {
        return self::renderTemplate('files.php', [
            'title' => $title,
            'name' => $name,
            'objects' => $objects,
            'required' => $required,
            'accept' => $accept,
            'disabled' => $disabled
        ]);
    }

    /**
     * Создает галерею изображений
     *
     * @param string $title Заголовок галереи
     * @param string $name Имя поля
     * @param mixed $gallerys Массив объектов галереи
     * @param bool $disabled Отключить поле
     * @return string HTML-код галереи
     */
    public static function gallery(
        string $title,
        string $name,
        $gallerys,
        bool $disabled = false
    ): string {
        return self::renderTemplate('gallery.php', [
            'title' => $title,
            'name' => $name,
            'gallerys' => $gallerys,
            'disabled' => $disabled
        ]);
    }

    /**
     * Создает выпадающий список
     *
     * @param string $title Заголовок поля
     * @param string $name Имя поля
     * @param mixed $object Данные для списка
     * @param mixed $selectedId Выбранный ID (может быть int или string для специальных значений)
     * @param bool $null Добавить пустой элемент
     * @param string $nullTitle Заголовок пустого элемента
     * @param string $fieldName Имя поля для отображения
     * @param int $no_obj Тип отображения объектов (0 - по умолчанию, 1 - ФИО, 2 - ассоциативный массив)
     * @param string $class CSS классы
     * @param int $data_id ID данных для data-атрибута
     * @param string $form_id ID формы
     * @param bool $disabled Отключить поле
     * @return string HTML-код выпадающего списка
     */
    public static function select(
        string $title,
        string $name,
        $object,
        $selectedId = null,
        bool $null = true,
        string $nullTitle = 'Не выбрано',
        string $fieldName = 'name',
        int $no_obj = 0,
        string $class = '',
        int $data_id = 0,
        string $form_id = '',
        bool $disabled = false
    ): string {
        return self::renderTemplate('select.php', [
            'title' => $title,
            'name' => $name,
            'object' => $object,
            'selectedId' => $selectedId,
            'null' => $null,
            'nullTitle' => $nullTitle,
            'fieldName' => $fieldName,
            'no_obj' => $no_obj,
            'class' => $class,
            'data_id' => $data_id,
            'form_id' => $form_id,
            'disabled' => $disabled
        ]);
    }

    /**
     * Создает поле множественного выбора чекбоксами
     *
     * @param string $title Заголовок поля
     * @param string $name Имя поля
     * @param mixed $object Данные для выбора
     * @param string $value Строка с выбранными ID через '|'
     * @param string $info Дополнительная информация
     * @param bool $disabled Отключить поле
     * @return string HTML-код поля множественного выбора
     */
    public static function multiple(
        string $title,
        string $name,
        $object,
        string $value = '',
        string $info = '',
        bool $disabled = false
    ): string {
        return self::renderTemplate('selectMultiple.php', [
            'title' => $title,
            'name' => $name,
            'object' => $object,
            'value' => $value,
            'info' => $info,
            'disabled' => $disabled
        ]);
    }

    /**
     * Создает метку (label) с текстом
     *
     * @param string $title Заголовок метки
     * @param mixed $value Значение метки
     * @param string $class CSS классы
     * @return string HTML-код метки
     */
    public static function label(string $title, $value, string $class = ''): string
    {
        return self::renderTemplate('label.php', [
            'title' => $title,
            'value' => $value,
            'class' => $class
        ]);
    }
}