<div class="section-row">
    <div class="catalog">
        <? foreach ($arResult['items'] as $item): ?>
            <div class="catalog__card">
                <a href="<?= $item['link'] ?>" class="catalog__card-image">
                    <img src="<?= $item['img'] ?>">
                    <div class="cursor-custom" style="display: none; left: 191.5px; top: 375.219px;">
                        <span>Перейти</span>
                    </div>
                </a>
                <? if (!empty(($item['list']))) : ?>
                    <div class="catalog__card-menu">
                        <? foreach ($item['list'] as $childItems): ?>
                            <a href="<?= $childItems["link"]; ?>" class="catalog__card-menu-link 1111"><?= $childItems["title"]; ?></a>
                        <? endforeach; ?>
                    </div>
                <? endif; ?>
            </div>
        <? endforeach; ?>
    </div>
</div>