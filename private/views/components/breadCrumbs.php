<div itemscope="" itemtype="http://schema.org/BreadcrumbList" class="breadcrumbs adminBreadCrumbs">
	<a href="<?= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>" class="path" itemscope="" itemprop="itemListElement" itemtype="http://schema.org/ListItem">
		<span itemprop="item"><span itemprop="name">Главная</span></span></a>
	<? 
		$i = 2; 
		foreach($bread AS $bc) : ?>
			<? if($i != 1) : ?>
				<span class="path_arr">/</span> 
			<? endif; $i++; ?>

			<? if($bc['id'] != $parent || $f == 1): ?>
				<a href="<?= $bc['url'] ?>" class="path" itemscope="" itemprop="itemListElement" itemtype="http://schema.org/ListItem">
					<span itemprop="item"><span itemprop="name"><?= strip_tags($bc['name']) ?></span></span></a>
			<? else : ?>
				<span itemscope="" itemprop="itemListElement" itemtype="http://schema.org/ListItem">
					<span itemprop="item" class="path" data-href="<?= $bc['url'] ?>"><span itemprop="name"><?= strip_tags($bc['name']) ?></span></span></span>
			<? endif; ?>

	<? endforeach; ?>
</div>