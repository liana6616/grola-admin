var fixEmptyParagraphs = function(editor) {
    $('p', editor.dom.doc.body).each(function(index, self) {
        self = $(self);
        if (!self[0].innerText && !self.hasClass('empty-paragraph')) {
            self.addClass('empty-paragraph');
        }
    });
};


function initTiny() {
    tinymce.remove();

    var Theme = 'lightgray';
    if($('body').hasClass('TinyDark')) Theme = 'dark';

    tinymce.init({
        selector: '.editor',
        skin: Theme,
        schema: 'html5',
        branding: false,
        height: 260,
        autoresize_bottom_margin: 30,
        autoresize_min_height: 260,
        autoresize_max_height: 260,
        autoresize_on_init: true,
        language: 'ru',
        language_url : '/vendor/tinymce/tinymce/lang/ru.js',
        external_filemanager_path:"/vendor/tinymce/tinymce/filemanager/",
        external_plugins: { "filemanager" : "/vendor/tinymce/tinymce/filemanager/plugin.min.js"},
        filemanager_title: 'Менеджер файлов',
        filemanager_sort_by: 'date',
        filemanager_descending: 0,
        plugins: 'autoresize imagetools advlist anchor autolink code colorpicker fullscreen hr insertdatetime link lists nonbreaking noneditable paste searchreplace table textcolor textpattern visualblocks visualchars wordcount image filemanager',
        image_advtab: true,
        toolbar1: 'bold italic underline strikethrough | bullist numlist table hr | alignleft aligncenter alignright alignjustify | link unlink | code fullscreen',
        toolbar2: 'styleselect | fontsizeselect forecolor backcolor removeformat outdent indent | undo, redo | mybutton image imagetools responsivefilemanager',
        images_upload_base_path: '/public/src/upload',
        relative_urls: false,
        fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt",
        browser_spellcheck: true,
        fix_list_elements: true,
        keep_styles: false,
        link_class_list: [
            {title: 'None', value: ''},
            {title: 'Gallery', value: 'gallery'}
        ],
        paste_enable_default_filters: false,
        paste_filter_drop: false,
        paste_as_text: false,
        convert_fonts_to_spans : true,
        force_hex_style_colors : true,
        force_p_newlines: true,
        forced_root_block : 'p',
        end_container_on_empty_block: true,
        menubar: false,
        textcolor_map: [
            "000000", "Черный",
            "993300", "Утомленный оранжевый",
            "333300", "Темно-оливковый",
            "003300", "Темно-зеленый",
            "003366", "Темно-лазурный",
            "000080", "Темно-синий",
            "333399", "Индиго",
            "333333", "Очень темно-серый",
            "800000", "Бордовый",
            "FF6600", "Оранжевый",
            "808000", "Оливковый",
            "008000", "Зеленый",
            "008080", "Изумрудный",
            "0000FF", "Синий",
            "666699", "Серовато-синий",
            "808080", "Серый",
            "FF0000", "Красный",
            "FF9900", "Янтарный",
            "339966", "Морской зеленый",
            "33CCCC", "Бирюзовый",
            "3366FF", "Королевский синий",
            "800080", "Фиолетовый",
            "999999", "Средне-серый",
            "FF00FF", "Пурпурный",
            "FFCC00", "Золотой",
            "FFFF00", "Желтый",
            "00FF00", "Лайм",
            "00FFFF", "Аква",
            "00CCFF", "Небесно-голубой",
            "993366", "Красно-фиолетовый",
            "FFFFFF", "Белый",
            "FF99CC", "Розовый",
            "FFCC99", "Персиковый",
            "FFFF99", "Светло-желтый",
            "CCFFCC", "Бледно-зеленый",
            "CCFFFF", "Бледно-голубой",
            "99CCFF", "Светло-голубой",
            "CC99FF", "Слива"
        ],
        body_class: 'tiny-content tiny-portfolio',
        content_css: '/private/src/css/tiny.css',
        cache_suffix: '?v=5',
        setup: function(editor) {
            var e = editor;
            e.addButton('mybutton', {
                tooltip: 'Высокая видимость курсора',
                icon: 'preview',
                onclick: function() {
                    var $body = $(e.contentDocument.body);
                    if (!$body.hasClass('high-visibility')) {
                        $body.addClass('high-visibility');

                        this.$el.addClass('mce-active');
                    } else {
                        $body.removeClass('high-visibility');
                        $('.wide-img', $body).removeClass('wide-img');

                        this.$el.removeClass('mce-active');
                    }
                }
            });
        },
        init_instance_callback: function(editor) {
            var $window = $(editor.contentWindow),
                $body = $(editor.contentDocument.body);

            editor.on('change', function() {
                fixEmptyParagraphs(editor);
            });

            $window.on('resize', function() {
                setTimeout(function() {
                    var w = $window.width();

                    var p = Math.floor((w - 1140) / 2);
                    if (p < 15) p = 15;
                    $body.css({
                        paddingLeft: p + 'px',
                        paddingRight: p + 'px'
                    });
                }, 0);
            });
        },
        formats: {
            alignleft: {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', styles: { textAlign: 'left' }},
            aligncenter: {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', styles: { textAlign: 'center' }},
            alignright: {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', styles: { textAlign: 'right' }},
            alignjustify: {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', styles: { textAlign: 'justify' }},
            bold: {
                inline: 'strong'
            },
            italic: {
                inline: 'em',
            },
            strikethrough: {
                inline: 'del'
            },
        },
        insertdatetime_formats: ['%H:%M:%S', '%d.%m.%Y']
    });
}

initTiny();