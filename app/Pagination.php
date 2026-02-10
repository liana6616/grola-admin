<?php

namespace app;

class Pagination
{
    private string $modelClass;
    private int $currentPage;
    private int $limit;
    private int $total;
    private int $countPages;
    private string $uri;
    private array $queryParams = [];
    private int $nums = 3; // Кол-во страниц слева/справа

    public function __construct(int $currentPage, int $limit, int $total, string $modelClass)
    {
        $this->modelClass = $modelClass;
        $this->limit = $limit;
        $this->total = $total;
        $this->countPages = $this->calculateCountPages();
        $this->currentPage = $this->normalizeCurrentPage($currentPage);
        $this->queryParams = $this->extractQueryParams();
        $this->uri = $this->getCleanUri();
    }

    /**
     * Статический метод для быстрого создания пагинации
     * 
     * @param string $modelClass Класс модели
     * @param string $where Условия WHERE (без WHERE 1=1)
     * @param string $order Порядок сортировки
     * @param int $defaultItemsPerPage Количество элементов по умолчанию
     * @param array $additionalParams Дополнительные параметры для добавления в URL
     * @return array Массив с объектами и пагинацией
     */
    public static function create(
        string $modelClass,
        string $where = '',
        string $order = 'ORDER BY id DESC',
        int $defaultItemsPerPage = 20,
        array $additionalParams = []
    ): array {
        
        // Определяем количество элементов на странице
        $itemsPerPage = $defaultItemsPerPage;
        
        // Получаем общее количество записей
        $totalCount = $modelClass::count($where);
       
        // Определяем текущую страницу
        $page = max(1, (int)($_GET['p'] ?? 1));
        
        // Создаем пагинацию, если нужно
        $pagination = new self($page, $itemsPerPage, $totalCount, $modelClass);
        
        // Добавляем дополнительные параметры в queryParams
        if (!empty($additionalParams)) {
            foreach ($additionalParams as $key => $value) {
                if ($value !== '') {
                    $pagination->queryParams[$key] = $value;
                }
            }
        }
            
        // Проверка на несуществующую страницу
        $countPages = ceil($totalCount / $itemsPerPage);
        if ($page > $countPages && $countPages > 0) {
            self::redirectToFirstPage();
        }
        
        // Получаем записи для текущей страницы
        $limit = '';
        if ($totalCount > $itemsPerPage) {
            $start = $pagination ? $pagination->getStart() : 0;
            $limit = " LIMIT {$start}, {$itemsPerPage}";
        }
        
        $findWhere = " {$where} {$order}" . $limit;
        $items = $modelClass::findWhere($findWhere);

        return [
            'items' => $items,
            'pagination' => $pagination,
            'totalCount' => $totalCount
        ];
    }

    public function getShortModelName(): string
    {
        return htmlspecialchars(strtolower(basename(str_replace('\\', '/', $this->modelClass))));
    }
    
    /**
     * Редирект на первую страницу с сохранением GET-параметров
     */
    private static function redirectToFirstPage(): void
    {
        $urlParts = parse_url($_SERVER['REQUEST_URI']);
        $path = $urlParts['path'] ?? '/';
        $query = [];
        
        // Сохраняем все GET-параметры кроме 'p'
        if (isset($urlParts['query'])) {
            parse_str($urlParts['query'], $query);
            unset($query['p']);
            if($query['parent'] == 0) unset($query['parent']);
        }
        
        // Редирект на первую страницу с сохранением других параметров
        $redirectUrl = $path;
        if (!empty($query)) {
            $redirectUrl .= '?' . http_build_query($query);
        }
        
        header("Location: $redirectUrl");
        exit;
    }

    public function __toString(): string
    {
        return $this->getHtml();
    }

    public function getStart(): int
    {
        return ($this->currentPage - 1) * $this->limit;
    }

    public function getCountPages(): int
    {
        return $this->countPages;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    private function getHtml(): string
    {
        $next = $this->getNextLink();
        $prev = $this->getPrevLink();
        $start = $this->getStartLink();
        $end = $this->getEndLink();
        $plist = $this->getPageList();
        $total = $this->total;

        $txt = Helpers::declOfNum($total,['элемент','элемента','элементов']);

        $html = '<div class="pagination">
            <div class="pagination_info">'.$total.' '.$txt.'</div>
            <div class="pagination_controls">';

                if ($total > $this->limit) {
                    $html .= '<div class="pagination_title">Страницы:</div>
                    <div class="page_numbers">
                        '.$prev.$start.$plist.$end.$next.'
                    </div>';
                }

            $k = $this->getShortModelName();
            $sessionValue = $_SESSION[$k]['per_page'] ?? '20'; // Значение по умолчанию

            $html .= '</div>
                <div class="items_per_page">
                    <span>На странице</span>
                    <select class="per_page_select" data-class="'.htmlspecialchars($k).'">
                        <option value="1"'.($sessionValue == 1 ? ' selected' : '').'>1</option>
                        <option value="20"'.($sessionValue == 20 ? ' selected' : '').'>20</option>
                        <option value="50"'.($sessionValue == 50 ? ' selected' : '').'>50</option>
                        <option value="100"'.($sessionValue == 100 ? ' selected' : '').'>100</option>
                        <option value="9999999999"'.($sessionValue == '9999999999' ? ' selected' : '').'>Все</option>
                    </select>
                </div>
            </div>';

        return $html;
    }

    private function getNextLink(): string
    {
        if ($this->currentPage >= $this->countPages) {
            return '';
        }
        
        $nextPage = $this->currentPage + 1;
        $url = $this->buildPageUrl($nextPage);
        return "<a class='page_number arrow arrow_right' href='$url'></a>";
    }

    private function getPrevLink(): string
    {
        if ($this->currentPage <= 1) {
            return '';
        }

        $prevPage = $this->currentPage - 1;
        $url = $this->buildPageUrl($prevPage);
        return "<a class='page_number arrow arrow_left' href='$url'></a>";
    }

    private function getStartLink(): string
    {
        if ($this->currentPage <= ($this->nums + 1)) {
            return '';
        }
        
        $url = $this->buildPageUrl(1);

        $start = "<a class='page_number' href='$url'>1</a>";
        if (($this->currentPage - $this->nums - 1) != 1) $start .= "<span class='page_number'>...</span>";

        return $start;
    }

    private function getEndLink(): string
    {
        if ($this->currentPage >= ($this->countPages - $this->nums)) {
            return '';
        }

        $total = $this->total;
        
        $url = $this->buildPageUrl($this->countPages);

        $end = "";
        if (($this->currentPage + $this->nums + 1) != $total) $end = "<span class='page_number'>...</span>";
        $end .= "<a class='page_number' href='$url'>".$total."</a>";

        return $end;
    }

    private function getPageList(): string
    {
        $start = max(1, $this->currentPage - $this->nums);
        $end = min($this->countPages, $this->currentPage + $this->nums);
        
        $pages = [];
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $this->currentPage) {
                $pages[] = "<span class='page_number active'>{$i}</span>";
            } else {
                $url = $this->buildPageUrl($i);
                $pages[] = "<a class='page_number' href='$url'>{$i}</a>";
            }
        }
        
        return implode(' ', $pages);
    }

    private function buildPageUrl(int $page): string
    {
        $uri = html_entity_decode($this->uri);
        
        // Создаем параметры для URL
        $queryParams = $this->queryParams;
        if(!empty($queryParams['parent']) && $queryParams['parent'] == 0) unset($queryParams['parent']);
        
        // Если это первая страница - не добавляем параметр пагинации
        if ($page > 1) {
            $queryParams['p'] = $page;
        } else {
            // Для первой страницы удаляем параметр пагинации, если он есть
            unset($queryParams['p']);
        }
        
        // Собираем URL с параметрами
        $queryString = http_build_query($queryParams);
        $url = $uri . ($queryString ? '?' . $queryString : '');
        
        return $url ?: '/';
    }

    private function calculateCountPages(): int
    {
        if ($this->total <= 0 || $this->limit <= 0) {
            return 1;
        }
        
        return (int) ceil($this->total / $this->limit);
    }

    private function normalizeCurrentPage(int $page): int
    {
        if ($page < 1) {
            return 1;
        }
        
        if ($page > $this->countPages) {
            return $this->countPages;
        }
        
        return $page;
    }

    private function getCleanUri(): string
    {
        $url = $_SERVER['REQUEST_URI'];
        $parts = parse_url($url);
        
        return $parts['path'] ?? '/';
    }

    private function extractQueryParams(): array
    {
        $params = [];
        
        // Получаем все GET параметры кроме параметра пагинации
        if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $queryParams);
            
            // Удаляем параметр пагинации из массива (если есть)
            unset($queryParams['p']);
            
            // Фильтруем пустые значения, но сохраняем нулевые и false если нужно
            foreach ($queryParams as $key => $value) {
                if ($value !== '') {
                    $params[$key] = $value;
                }
            }
        }
        
        return $params;
    }
}