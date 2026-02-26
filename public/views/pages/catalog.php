<?= $this->include('layouts/header'); ?>

<?php use app\Helpers; ?> 
<?php use app\Models\Categories; ?>

<?php
    $db = \app\Db::getInstance();
    $prices = $db->query("SELECT MIN(price) as min, MAX(price) as max FROM catalog");
    $min_price = floor($prices[0]['min']);
    $max_price = ceil($prices[0]['max']);

    $current_min = isset($_GET['price_min']) ? (int)$_GET['price_min'] : $min_price;
    $current_max = isset($_GET['price_max']) ? (int)$_GET['price_max'] : $max_price;

    if (isset($_GET['price_min']) && isset($_GET['price_max'])) {
    }
?>

<main>
  <div class="catalog">
    <ul class="breadcrumps__wrapper">
        <li class="breadcrumps">
            <a href="index.php">Главная</a>
        </li>
        <li class="breadcrumps">
            <span><?= $this->page->name ?></span>
        </li>
    </ul>

    <h2 class="catalog__title title">Каталог продукции</h2>

    <? if(!empty($this->childs) && $this->is_mainCategories): ?>
        <ul class="catalog__list">
            <? foreach($this->childs AS $item): ?>
                <li class="catalog__item">
                    <a href="catalog/<?= $item->url ?>">
                        <h3><?= $item->name ?></h3>
                        <span class="catalog__text">
                            <?= number_format((int)($item->products_count ?? 0), 0, '', ' ') ?> 
                            <?= \app\Helpers::declOfNum((int)($item->products_count ?? 0), ['товар', 'товара', 'товаров']) ?>
                        </span>          
                    </a>
                </li>
            <? endforeach; ?>
        </ul>
    <? endif; ?>


        <? if(!empty($this->childs) && !$this->is_mainCategories): ?>
            <div class="catalog__wrapper-category">
                <a href="?subcat=0" class="catalog__button-category filter <?= ($this->selectedSubcat == 0) ? 'active' : '' ?>">Все категории</a>

                <? foreach($this->childs AS $item): ?>
                    <a href="?subcat=<?= $item->id ?>" class="catalog__button-category filter <?= ($this->selectedSubcat == $item->id) ? 'active' : '' ?>">
                        <?= $item->name ?>
                    </a>
                <? endforeach; ?>
            </div>
        <? endif; ?>


    <div class="catalog__wrapper-filter">
        <button class="catalog__button-filter-names" onclick="this.classList.toggle('active'); this.parentElement.classList.toggle('active')">Фильтры</button>

        <div class="catalog__wrapper-filter-sort">
            <button class="catalog__buttons-filter-sort catalog__buttons-filter-sort-tile active filter" type="button">
                <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g opacity="0.6" clip-path="url(#clip0_1557_801)">
                        <path d="M7.125 0H1.78125C0.799009 0 0 0.799009 0 1.78125V7.125C0 8.10724 0.799009 8.90625 1.78125 8.90625H7.125C8.10724 8.90625 8.90625 8.10724 8.90625 7.125V1.78125C8.90625 0.799009 8.10724 0 7.125 0ZM7.71875 7.125C7.71875 7.45232 7.45261 7.71875 7.125 7.71875H1.78125C1.45364 7.71875 1.1875 7.45232 1.1875 7.125V1.78125C1.1875 1.45393 1.45364 1.1875 1.78125 1.1875H7.125C7.45261 1.1875 7.71875 1.45393 7.71875 1.78125V7.125Z" />
                        <path d="M17.2188 0H11.875C10.8928 0 10.0938 0.799009 10.0938 1.78125V7.125C10.0938 8.10724 10.8928 8.90625 11.875 8.90625H17.2188C18.201 8.90625 19 8.10724 19 7.125V1.78125C19 0.799009 18.201 0 17.2188 0ZM17.8125 7.125C17.8125 7.45232 17.5464 7.71875 17.2188 7.71875H11.875C11.5474 7.71875 11.2812 7.45232 11.2812 7.125V1.78125C11.2812 1.45393 11.5474 1.1875 11.875 1.1875H17.2188C17.5464 1.1875 17.8125 1.45393 17.8125 1.78125V7.125Z" />
                        <path d="M17.2188 10.0938H11.875C10.8928 10.0938 10.0938 10.8928 10.0938 11.875V17.2188C10.0938 18.201 10.8928 19 11.875 19H17.2188C18.201 19 19 18.201 19 17.2188V11.875C19 10.8928 18.201 10.0938 17.2188 10.0938ZM17.8125 17.2188C17.8125 17.5461 17.5464 17.8125 17.2188 17.8125H11.875C11.5474 17.8125 11.2812 17.5461 11.2812 17.2188V11.875C11.2812 11.5477 11.5474 11.2812 11.875 11.2812H17.2188C17.5464 11.2812 17.8125 11.5477 17.8125 11.875V17.2188Z" />
                        <path d="M7.125 10.0938H1.78125C0.799009 10.0938 0 10.8928 0 11.875V17.2188C0 18.201 0.799009 19 1.78125 19H7.125C8.10724 19 8.90625 18.201 8.90625 17.2188V11.875C8.90625 10.8928 8.10724 10.0938 7.125 10.0938ZM7.71875 17.2188C7.71875 17.5461 7.45261 17.8125 7.125 17.8125H1.78125C1.45364 17.8125 1.1875 17.5461 1.1875 17.2188V11.875C1.1875 11.5477 1.45364 11.2812 1.78125 11.2812H7.125C7.45261 11.2812 7.71875 11.5477 7.71875 11.875V17.2188Z" />
                    </g>
                    <defs>
                        <clipPath id="clip0_1557_801">
                            <rect width="19" height="19" fill="white" />
                        </clipPath>
                    </defs>
                </svg>
            </button>

            <button class="catalog__buttons-filter-sort catalog__buttons-filter-sort-list filter" type="button">
                <svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g opacity="0.6">
                        <path d="M3 2.50007C3 3.32845 2.32845 4 1.50007 4C0.67155 4 0 3.32845 0 2.50007C0 1.67155 0.67155 1 1.50007 1C2.32845 1 3 1.67155 3 2.50007Z" />
                        <path d="M3 10.4999C3 11.3285 2.32845 12 1.50007 12C0.67155 12 0 11.3285 0 10.4999C0 9.67155 0.67155 9 1.50007 9C2.32845 9 3 9.67155 3 10.4999Z" />
                        <path d="M3 18.5C3 19.3283 2.32845 20 1.50007 20C0.67155 20 0 19.3283 0 18.5C0 17.6717 0.67155 17 1.50007 17C2.32845 17 3 17.6717 3 18.5Z" />
                        <path d="M5.97804 2.93861H18.7721C19.2438 2.93861 19.625 2.6481 19.625 2.28867C19.625 1.92925 19.2438 1.63861 18.7721 1.63861H5.97804C5.50639 1.63861 5.125 1.92925 5.125 2.28867C5.125 2.6481 5.50639 2.93861 5.97804 2.93861Z" />
                        <path d="M18.7721 9.63867H5.97804C5.50639 9.63867 5.125 9.92919 5.125 10.2886C5.125 10.648 5.50639 10.9387 5.97804 10.9387H18.7721C19.2438 10.9387 19.625 10.648 19.625 10.2886C19.625 9.92919 19.2438 9.63867 18.7721 9.63867Z" />
                        <path d="M18.7721 17.6387H5.97804C5.50639 17.6387 5.125 17.9293 5.125 18.2887C5.125 18.6482 5.50639 18.9387 5.97804 18.9387H18.7721C19.2438 18.9387 19.625 18.6482 19.625 18.2887C19.625 17.9293 19.2438 17.6387 18.7721 17.6387Z" />
                    </g>
                </svg>
            </button>
        </div>
        <div class="catalog__filter-hidden">
        <div class="catalog__filter-wrapper-sort">
            <span class="catalog__name-filter">Сортировать по:</span>
            
        <?php
        $is_rate = ($this->current_sort ?? 'rate') == 'rate';
        $is_price = ($this->current_sort ?? '') == 'price';

        $rate_order = $is_rate ? (($this->current_order == 'DESC') ? 'ASC' : 'DESC') : 'DESC';
        $price_order = $is_price ? (($this->current_order == 'DESC') ? 'ASC' : 'DESC') : 'DESC';
        ?>

        <a href="?sort=rate&order=<?= $rate_order ?><?= $this->subcat_param ?>" 
        class="catalog__button-filter catalog__button-filter--sorting filter <?= $is_rate ? 'active' : '' ?>">
        <span>По популярности</span>
                <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g opacity="0.6" clip-path="url(#clip0_1557_801)">
                        <path d="M7.125 0H1.78125C0.799009 0 0 0.799009 0 1.78125V7.125C0 8.10724 0.799009 8.90625 1.78125 8.90625H7.125C8.10724 8.90625 8.90625 8.10724 8.90625 7.125V1.78125C8.90625 0.799009 8.10724 0 7.125 0ZM7.71875 7.125C7.71875 7.45232 7.45261 7.71875 7.125 7.71875H1.78125C1.45364 7.71875 1.1875 7.45232 1.1875 7.125V1.78125C1.1875 1.45393 1.45364 1.1875 1.78125 1.1875H7.125C7.45261 1.1875 7.71875 1.45393 7.71875 1.78125V7.125Z" />
                        <path d="M17.2188 0H11.875C10.8928 0 10.0938 0.799009 10.0938 1.78125V7.125C10.0938 8.10724 10.8928 8.90625 11.875 8.90625H17.2188C18.201 8.90625 19 8.10724 19 7.125V1.78125C19 0.799009 18.201 0 17.2188 0ZM17.8125 7.125C17.8125 7.45232 17.5464 7.71875 17.2188 7.71875H11.875C11.5474 7.71875 11.2812 7.45232 11.2812 7.125V1.78125C11.2812 1.45393 11.5474 1.1875 11.875 1.1875H17.2188C17.5464 1.1875 17.8125 1.45393 17.8125 1.78125V7.125Z" />
                        <path d="M17.2188 10.0938H11.875C10.8928 10.0938 10.0938 10.8928 10.0938 11.875V17.2188C10.0938 18.201 10.8928 19 11.875 19H17.2188C18.201 19 19 18.201 19 17.2188V11.875C19 10.8928 18.201 10.0938 17.2188 10.0938ZM17.8125 17.2188C17.8125 17.5461 17.5464 17.8125 17.2188 17.8125H11.875C11.5474 17.8125 11.2812 17.5461 11.2812 17.2188V11.875C11.2812 11.5477 11.5474 11.2812 11.875 11.2812H17.2188C17.5464 11.2812 17.8125 11.5477 17.8125 11.875V17.2188Z" />
                        <path d="M7.125 10.0938H1.78125C0.799009 10.0938 0 10.8928 0 11.875V17.2188C0 18.201 0.799009 19 1.78125 19H7.125C8.10724 19 8.90625 18.201 8.90625 17.2188V11.875C8.90625 10.8928 8.10724 10.0938 7.125 10.0938ZM7.71875 17.2188C7.71875 17.5461 7.45261 17.8125 7.125 17.8125H1.78125C1.45364 17.8125 1.1875 17.5461 1.1875 17.2188V11.875C1.1875 11.5477 1.45364 11.2812 1.78125 11.2812H7.125C7.45261 11.2812 7.71875 11.5477 7.71875 11.875V17.2188Z" />
                    </g>
                    <defs>
                        <clipPath id="clip0_1557_801">
                            <rect width="19" height="19" fill="white" />
                        </clipPath>
                    </defs>
                </svg>
            </a>
                
            <a href="?sort=price&order=<?= $price_order ?><?= $this->subcat_param ?>" 
            class="catalog__button-filter catalog__button-filter--sum filter <?= $is_price ? 'active' : '' ?>">
            <span>По цене</span>
                    <svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g opacity="0.6">
                            <path d="M3 2.50007C3 3.32845 2.32845 4 1.50007 4C0.67155 4 0 3.32845 0 2.50007C0 1.67155 0.67155 1 1.50007 1C2.32845 1 3 1.67155 3 2.50007Z" />
                            <path d="M3 10.4999C3 11.3285 2.32845 12 1.50007 12C0.67155 12 0 11.3285 0 10.4999C0 9.67155 0.67155 9 1.50007 9C2.32845 9 3 9.67155 3 10.4999Z" />
                            <path d="M3 18.5C3 19.3283 2.32845 20 1.50007 20C0.67155 20 0 19.3283 0 18.5C0 17.6717 0.67155 17 1.50007 17C2.32845 17 3 17.6717 3 18.5Z" />
                            <path d="M5.97804 2.93861H18.7721C19.2438 2.93861 19.625 2.6481 19.625 2.28867C19.625 1.92925 19.2438 1.63861 18.7721 1.63861H5.97804C5.50639 1.63861 5.125 1.92925 5.125 2.28867C5.125 2.6481 5.50639 2.93861 5.97804 2.93861Z" />
                            <path d="M18.7721 9.63867H5.97804C5.50639 9.63867 5.125 9.92919 5.125 10.2886C5.125 10.648 5.50639 10.9387 5.97804 10.9387H18.7721C19.2438 10.9387 19.625 10.648 19.625 10.2886C19.625 9.92919 19.2438 9.63867 18.7721 9.63867Z" />
                            <path d="M18.7721 17.6387H5.97804C5.50639 17.6387 5.125 17.9293 5.125 18.2887C5.125 18.6482 5.50639 18.9387 5.97804 18.9387H18.7721C19.2438 18.9387 19.625 18.6482 19.625 18.2887C19.625 17.9293 19.2438 17.6387 18.7721 17.6387Z" />
                        </g>
                    </svg>
                </a>
            </div>
            <div class="catalog__filter-wrapper-block">
                <div class="catalog__filter-wrapper-sum">
                    <span class="catalog__name-filter">По стоимости:</span>
                    <div class="catalog__filter-polzunok">
                        <div class="catalog__filter-weight" 
                            data-min="<?= $min_price ?>" 
                            data-max="<?= $max_price ?>"
                            data-current-min="<?= $current_min ?>"
                            data-current-max="<?= $current_max ?>">
                        </div>
                    </div>
                </div>
                <button class="catalog__button-filter-add filter" type="button">Применить фильтры</button>

                <button class="catalog__button-filter-delete filter" type="button">Сбросить все</button>

            </div>
        </div>


    </div>
    
    <div class="catalog__list-wrapper">
        <?php
        // ФИЛЬТРАЦИЯ ТОВАРОВ ПО ЦЕНЕ
        $filtered_items = [];

        if(!empty($this->catalog)) {
            foreach($this->catalog as $item) {
                if($item->parent == 0) {
                    $price_ok = true;
                    if(isset($_GET['price_min']) && isset($_GET['price_max'])) {
                        $price_min = (int)$_GET['price_min'];
                        $price_max = (int)$_GET['price_max'];
                        if($item->price < $price_min || $item->price > $price_max) {
                            $price_ok = false;
                        }
                    }
                    
                    if($price_ok) {
                        $filtered_items[] = $item;
                    }
                }
            }
        }
        ?>

        <? if(!empty($filtered_items)): ?>
            <ul class="catalog-card__list">
                <? foreach($filtered_items as $item): ?>
                    <?php 
                        $productCategory = \app\Models\Categories::findById($item->category_id);
                        if ($productCategory) {
                            $categoryPath = \app\Models\Categories::getUrl($productCategory->id);
                            $productUrl = '/catalog' . $categoryPath . '/' . $item->url;
                        } else {
                            $productUrl = '/catalog/' . ($this->childs[0]->url ?? '') . '/' . $item->url;
                        }
                    ?>
                    <li class="catalog-card__item" data-id="<?= $item->id ?>" data-category-id="<?= $item->category_id ?>">
                        <?php
                        $paramValue = \app\Models\CatalogParams::findWhere("WHERE catalog_id = " . $item->id );
                        ?>

                        <a href="<?= $productUrl ?>">
                            <?php 
                            if($item->action == 1): ?>
                                <span class="catalog-card__cta catalog-card__cta-action">Акция</span>
                            <?php elseif($item->popular == 1): ?>
                                <span class="catalog-card__cta catalog-card__cta-hit">Хит</span>
                            <?php endif; ?>  

                            <div class="catalog-card__wrapper-img">
                                <img class="catalog-card__img" src="<?= $item->image_preview ?>" alt="<?= $item->name ?>">
                            </div>

                            <h3 class="catalog-card__title title-small"><?= $item->name ?></h3>
                                <? foreach($paramValue AS $catalogParam): ?>
                                    <?php
                                        $paramName = \app\Models\Params::findById($catalogParam->param_id);
                                        if($paramName->name == 'Артикул:'){
                                            continue;
                                        }
                                    ?>
                                    <div class="catalog-card__wrapper-parameters">
                                        <span class="catalog-card__name"><?= $paramName->name ?></span>
                                        <span class="catalog-card__parameters"><?= $catalogParam->value ?></span>
                                    </div>
                                <? endforeach; ?>
                            <span class="catalog-card__sum">от <?= number_format($item->price, 0, '', ' ') ?> ₽</span>
                        </a>
                    </li>
                <? endforeach; ?>
            </ul>
        <? else: ?>
            <p class="catalog-card__title title-small">Товаров не найдено</p>
        <? endif; ?>
 
        <div class="pagination" id="pagination">
            <span class="pagination__sum"></span>
        </div>
    </div>
  </div>
</main>

<?= $this->include('layouts/footer'); ?>