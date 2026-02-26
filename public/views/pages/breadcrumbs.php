<?php
// ========== ФУНКЦИЯ ДЛЯ КРОШЕК ТОВАРА ==========
function getProductBreadcrumbs($product, $category = null, $subcategory = null) {
    $breadcrumbs = [];
    
    // 1. Главная
    $breadcrumbs[] = [
        'name' => 'Главная',
        'url' => '/'
    ];
    
    // 2. Каталог (всегда)
    $breadcrumbs[] = [
        'name' => 'Каталог',
        'url' => '/catalog.php'
    ];
    
    // 3. Если есть категория - добавляем её
    if (!empty($category)) {
        $breadcrumbs[] = [
            'name' => $category['name'],
            'url' => $category['url']
        ];
    }
    
    // 4. Если есть подкатегория - добавляем её
    if (!empty($subcategory)) {
        $breadcrumbs[] = [
            'name' => $subcategory['name'],
            'url' => $subcategory['url']
        ];
    }
    
    // 5. Текущий товар (последний - без ссылки)
    $breadcrumbs[] = [
        'name' => $product->name ?? 'Товар',
        'url' => null
    ];
    
    return $breadcrumbs;
}

// ========== ФУНКЦИЯ ДЛЯ ВЫВОДА КРОШЕК ==========
function showBreadcrumbs($breadcrumbs) {
    if (empty($breadcrumbs)) return '';
    ?>
    <ul class="breadcrumps__wrapper" itemscope itemtype="https://schema.org/BreadcrumbList">
        <?php foreach ($breadcrumbs as $index => $crumb): 
            $position = $index + 1;
        ?>
            <li class="breadcrumps" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <?php if (!empty($crumb['url'])): ?>
                    <a href="<?= htmlspecialchars($crumb['url']) ?>" itemprop="item">
                        <span itemprop="name"><?= htmlspecialchars($crumb['name']) ?></span>
                    </a>
                <?php else: ?>
                    <span itemprop="item">
                        <span itemprop="name"><?= htmlspecialchars($crumb['name']) ?></span>
                    </span>
                <?php endif; ?>
                <meta itemprop="position" content="<?= $position ?>">
            </li>
            
            <?php if ($index < count($breadcrumbs) - 1): ?>
                <li class="breadcrumps__separator">/</li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
    <?php
}

// ========== ПРОСТОЙ ВАРИАНТ (ЕСЛИ НЕТ КАТЕГОРИЙ) ==========
function showSimpleBreadcrumbs($productName) {
    ?>
    <ul class="breadcrumps__wrapper">
        <li class="breadcrumps"><a href="/">Главная</a></li>
        <li class="breadcrumps"><a href="/catalog.php">Каталог</a></li>
        <li class="breadcrumps"><span><?= htmlspecialchars($productName) ?></span></li>
    </ul>
    <?php
}
?>