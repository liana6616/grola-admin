

<div class="pagePaymentServicesItem">

    <input type="hidden" name="payment_service[<?= $itemIndex ?>][id]" value="<?= $item->id ?>">
    <input type="hidden" name="payment_service[<?= $itemIndex ?>][show]" value="<?= $item->show ?>" class="show">
    <input type="hidden" name="payment_service[<?= $itemIndex ?>][rate]" value="<?= $item->rate ?>" class="rate">

    <div class="btn icon_handler handler"></div>
    <div class="flex2">

        <?= app\Form::image(
            'Логотип',
            'payment_service['.$itemIndex.'][image]',
            $item,
            false,
            false,
            'image'
        ) ?>
        
        <?= app\Form::input(
            'Название (Alt) для SEO',  
            'payment_service['.$itemIndex.'][alt]',
            $item->alt,  
            0,  
            'text',  
            '',  
            '' 
        ) ?>
        
    </div>
    <div class="btn icon_show pagePaymentServiceShow<?= $item->show?' active':'' ?> tooltip-trigger" data-tooltip="Показывать на сайте"></div>
    <div class="btn icon_delete pagePaymentServiceRemove tooltip-trigger" data-tooltip="Удалить"></div>
</div>