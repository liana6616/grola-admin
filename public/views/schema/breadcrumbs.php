<? if (!empty($this->breadcrumbs)) : ?>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BreadcrumbList",
      "itemListElement": [
          <? $i = 0; foreach($this->breadcrumbs AS $val): ?><?= !empty($i) ? ',' : '' ?>{
            "@type": "ListItem",
            "position": <?= ($i + 1) ?>,
            "name": "<?= app\Helpers::text($val[0]) ?>"
            <? if (!empty($val[1])) : ?>
                , "item": "<?= $val[1] ?>"
            <? endif; ?>
          }<? $i++; endforeach; ?>
      ]
    }
    </script>
<? endif; ?>
