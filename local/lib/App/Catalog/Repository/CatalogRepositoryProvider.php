<?

namespace App\Catalog\Repository;

use Beeralex\Core\DI\AbstractServiceProvider;

class CatalogRepositoryProvider extends AbstractServiceProvider
{
    const PRODUCTS_REPOSITORY = 'catalogRepository';
    const OFFERS_REPOSITORY = 'offersRepository';
    const EMPTY_OFFERS_REPOSITORY = 'emptyOffersRepository';

    protected function registerServices(): void
    {
        $this->bind(static::PRODUCTS_REPOSITORY, ProductsRepository::class);
        $this->bind(static::OFFERS_REPOSITORY, OffersRepository::class);
        $this->bind(static::EMPTY_OFFERS_REPOSITORY, EmptyOffersRepository::class);
    }
}
