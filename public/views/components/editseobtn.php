<? if (!empty($editPage) || !empty($editSeo)): ?>
	<div class='adminBlock'>
		<? if (!empty($editPage)): ?>
			<a href='<?= $editPage ?>' class='button adminEdit' rel='external'>Редактировать</a>
		<? endif; ?>
		<? if (!empty($editSeo)): ?>
			<a href='<?= $editSeo ?>' class='button adminSeo' rel='external'>SEO</a>
		<? endif; ?>
	</div>
<? endif; ?>
