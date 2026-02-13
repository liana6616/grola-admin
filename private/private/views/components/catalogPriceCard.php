<div class="catalogPriceCard">
    <div class="flex4">
        <?php if ($config['prices']['fields']['weight']['enabled'] ?? false): ?>
            <?= app\Form::input(
                $config['prices']['fields']['weight']['title'] ?? 'Вес (кг)',  
                'prices['.$priceIndex.'][weight]',  
                $price->weight,  
                0,  
                'number',  
                '',  
                '' 
            ) ?>
        <?php endif; ?>
        
        <?php if ($config['prices']['fields']['price']['enabled'] ?? false): ?>
            <?= app\Form::input(
                $config['prices']['fields']['price']['title'] ?? 'Стоимость (руб)',  
                'prices['.$priceIndex.'][price]',  
                $price->price,  
                0,  
                'number',  
                '',  
                '' 
            ) ?>
        <?php endif; ?>
        
        <?php if ($config['prices']['fields']['count']['enabled'] ?? false): ?>
            <?= app\Form::input(
                $config['prices']['fields']['count']['title'] ?? 'Доступное количество',  
                'prices['.$priceIndex.'][count]',  
                $price->count,  
                0,  
                'number',  
                '',  
                '' 
            ) ?>
        <?php endif; ?>
        
        <?php if ($config['prices']['fields']['unit']['enabled'] ?? false): ?>
            <?= app\Form::input(
                $config['prices']['fields']['unit']['title'] ?? 'Единица измерения',  
                'prices['.$priceIndex.'][unit]',  
                $price->unit,  
                0,  
                'text',  
                '',  
                '' 
            ) ?>
        <?php endif; ?>
    </div>
    <div class="btn icon_delete catalogPriceRemove tooltip-trigger" data-tooltip="Удалить"></div>
</div>