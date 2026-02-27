<?php

function getProductBreadcrumbs($product, $category = null, $subcategory = null) {
    $breadcrumbs = [];
    
    // 1. 
    $breadcrumbs[] = [
        'name' => '�������',
        'url' => '/'
    ];
    
    // 2.
    $breadcrumbs[] = [
        'name' => '�������',
        'url' => '/catalog.php'
    ];
    
    // 3. 
    if (!empty($category)) {
        $breadcrumbs[] = [
            'name' => $category['name'],
            'url' => $category['url']
        ];
    }
    
    // 4. 
    if (!empty($subcategory)) {
        $breadcrumbs[] = [
            'name' => $subcategory['name'],
            'url' => $subcategory['url']
        ];
    }
    
    // 5. 
    $breadcrumbs[] = [
        'name' => $product->name ?? '�����',
        'url' => null
    ];
    
    return $breadcrumbs;
}


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

function showSimpleBreadcrumbs($productName) {
    ?>
    <ul class="breadcrumps__wrapper">
        <li class="breadcrumps"><a href="/">�������</a></li>
        <li class="breadcrumps"><a href="/catalog.php">�������</a></li>
        <li class="breadcrumps"><span><?= htmlspecialchars($productName) ?></span></li>
    </ul>
    <?php
}
?>