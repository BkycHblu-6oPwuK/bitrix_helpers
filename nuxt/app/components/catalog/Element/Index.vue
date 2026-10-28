<!--
  Детальная страница товара: галерея, название, цена, предложения, действия, описание.
  Состояние — в provideCatalogProduct (без стора): ни setItem, ни onUnmounted(reset).
-->

<template>
    <div class="product-detail">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <CatalogElementGallery />

            <div class="product-info space-y-6">
                <CatalogElementInfo />
                <CatalogElementOffers />
                <CatalogElementActions />
                <CatalogElementDescription />
            </div>
        </div>
    </div>
</template>