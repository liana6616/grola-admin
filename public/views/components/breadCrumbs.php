<? if(!empty($this->breadCrumbs)): ?>

<ul class="breadcrumps__wrapper" itemscope="" itemtype="https://schema.org/BreadcrumbList">
    <li class="breadcrumps">
        <a href="/" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
			<span itemprop="item">
				<span itemprop="name">Главная</span>
				<meta itemprop="position" content="0">
			</span>
		</a>
    </li>

    <? $i = 1; 
	foreach($this->breadCrumbs AS $bc) : ?>

		<? if($bc['url'] != '/'.URI && !empty($bc['url'])): ?>
			<li class="breadcrumps">
				<a href="<?= $bc['url'] ?>" itemscope="" itemprop="itemListElement" itemtype="https://schema.org/ListItem">
					<span itemprop="item">
						<span itemprop="name"><?= strip_tags($bc['name'] ?? '') ?></span>
						<meta itemprop="position" content="<?= $i ?>">
					</span>
				</a>
			</li>
		<? else : ?>
			<li class="breadcrumps">
				<span itemscope="" itemprop="itemListElement" itemtype="https://schema.org/ListItem">
					<span itemprop="item" data-href="<?= $bc['url'] ?>">
						<span itemprop="name"><?= strip_tags($bc['name'] ?? '') ?></span>
						<meta itemprop="position" content="<?= $i ?>">
					</span>
				</span>
			</li>
		<? endif; ?>

	<? $i++; endforeach; ?>
		
</ul>

<? endif; ?>