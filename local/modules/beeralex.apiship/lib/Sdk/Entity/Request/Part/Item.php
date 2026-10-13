<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Sdk\Entity\Request\Part;

use Apiship\Entity\Request\Part\Orders\Item as BaseItem;

/**
 * Товар заказа с полями, которых нет в vendor SDK (asArray() подхватывает protected-свойства потомка).
 */
class Item extends BaseItem
{
    /** @var string|null Код ТН ВЭД */
    protected $tnved;

    /** @var string|null Ссылка на страницу товара */
    protected $url;

    /** @var string[]|null Ссылки на фото товара */
    protected $photoLinks;

    public function setTnved($tnved): self
    {
        $this->tnved = $tnved;
        return $this;
    }

    public function setUrl($url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param string[] $photoLinks
     */
    public function setPhotoLinks($photoLinks): self
    {
        $this->photoLinks = $photoLinks;
        return $this;
    }
}
