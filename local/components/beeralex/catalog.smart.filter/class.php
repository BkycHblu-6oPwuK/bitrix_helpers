<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Beeralex\Catalog\Service\CatalogService;
use Beeralex\Catalog\Service\SearchService;
use Beeralex\Core\Service\SortingService;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Uri;

CBitrixComponent::includeComponentClass("bitrix:catalog.smart.filter");

class BeeralexCatalogSmartFilter extends CBitrixCatalogSmartFilter
{
	protected CatalogService $catalogService;
	protected SortingService $sortingService;

	public function onPrepareComponentParams($arParams)
	{
		$this->processServices($arParams);

		return parent::onPrepareComponentParams($arParams);
	}

	public function makeSmartUrl($url, $apply, $checkedControlId = false)
	{
		$res = parent::makeSmartUrl($url, $apply, $checkedControlId);
		return $this->makeUrl($res);
	}

	public function makeUrl(string $url): string
	{
		if ($this->catalogService !== null) {
			return $this->catalogService->makeUrl($url);
		}
		Loader::requireModule('beeralex.catalog');
		$requestedSortId = $this->sortingService->getRequestedSortIdOrDefault();
		$query = Context::getCurrent()->getRequest()->get(SearchService::REQUEST_PARAM);

		$uri = new Uri($url);

		if ($requestedSortId != $this->sortingService->getDefaultSortId()) {
			$uri->addParams([SortingService::REQUEST_PARAM => $requestedSortId]);
		}
		if ($query) {
			$uri->addParams([SearchService::REQUEST_PARAM => $query]);
		}

		return $uri->getUri();
	}

	protected function processServices(array &$arParams): void
	{
		$catalogService = $arParams['CATALOG_SERVICE'] ?? null;
		$sortingService = $arParams['SORTING_SERVICE'] ?? null;
		if ($catalogService === null || !($catalogService instanceof CatalogService)) {
			Loader::requireModule('beeralex.catalog');
			$catalogService = service(CatalogService::class);
		}

		if ($sortingService === null || !($sortingService instanceof SortingService)) {
			throw new \InvalidArgumentException('SORTING_SERVICE param is required');
		}
		$this->catalogService = $catalogService;
		$this->sortingService = $sortingService;
		unset($arParams['CATALOG_SERVICE'], $arParams['SORTING_SERVICE']);
	}

	public function getCatalogService(): CatalogService
	{
		return $this->catalogService;
	}

	public function getSortingService(): SortingService
	{
		return $this->sortingService;
	}
}
