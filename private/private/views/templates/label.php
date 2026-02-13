<div class='label_block'>
      <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?><?= !empty($title)?': ':'' ?><?= !empty($value)?'<span>'.htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8').'</span>':'' ?>
</div>