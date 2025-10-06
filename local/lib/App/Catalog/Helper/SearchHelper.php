<?php
namespace App\Catalog\Helper;

use Bitrix\Main\Loader;
use CSearch;
use App\Catalog\Helper\CatalogHelper;
use App\Iblock\Model\SectionModel;
use Beeralex\Core\Helpers\IblockHelper;

class SearchHelper
{
    public static function getHints($query)
    {
        $result = [];
        $productsIds = self::getProductsIds($query, 50);

        if (empty($productsIds)) {
            if (static::issetTranslitirate($query)) {
                $query = static::transliterate($query);
                $productsIds = self::getProductsIds($query, 50);
            } else {
                return $result;
            }
        }

        $sections = self::getSections($productsIds);
        $query = strtolower($query);
        $productsIds = collect($productsIds)->splice(0, 7)->toArray();
        $result['products'] = array_values(CatalogSectionHelper::getProductsForCard($productsIds, true));
        $result['hints'] = $sections;
        return $result;
    }

    public static function getProductsIds($query, int $limit)
    {
        Loader::includeModule('search');

        $search = new CSearch;
        $search->SetLimit($limit);
        $search->Search(
            [
                'QUERY' => $query,
                'SITE_ID' => SITE_ID,
                'MODULE_ID' => 'iblock',
                'CHECK_DATES' => 'Y',
                'PARAM2' => IblockHelper::getIblockIdByCode('catalog'),
            ],
            [
                'RANK' => 'DESC',
                'CUSTOM_RANK' => 'DESC',
            ],
            [
                'STEMMING' => false
            ]
        );
        $productsIds = [];
        while ($element = $search->Fetch()) {
            if (mb_substr($element['ITEM_ID'], 0, 1) === 's') {
                // Это раздел, а не элемент
                continue;
            }

            $productsIds[] = $element['ITEM_ID'];
        }

        return $productsIds;
    }

    protected static function getSections($productsIds)
    {
        $catalogId = IblockHelper::getIblockIdByCode('catalog');
        $section = SectionModel::compileEntityByIblock($catalogId);
        $dbResult = IblockHelper::getElementApiTable($catalogId)::query()
            ->setSelect(
                [
                    'IBLOCK_SECTION_ID',
                    'SECTION_CODE' => 'SECTION.CODE',
                    'SECTION_PAGE_URL' => 'IBLOCK.SECTION_PAGE_URL',
                    'SECTION_NAME' => 'SECTION.NAME',
                    'SECTION_CUSTOM_NAME' => 'SECTION.UF_CUSTOM_NAME',
                ]
            )
            ->registerRuntimeField('SECTION', [
                'data_type' => $section,
                'reference' => [
                    '=this.IBLOCK_SECTION_ID' => 'ref.ID',
                ],
                'join_type' => 'INNER'
            ])
            ->whereIn('ID', $productsIds)
            ->where('SECTION.ACTIVE', 'Y')
            ->setCacheTtl(86400)
            ->cacheJoins(true)
            ->exec();

        $sections = [];
        $count = 0;
        while ($count < 5 && $item = $dbResult->Fetch()) {
            $id = (int)$item['IBLOCK_SECTION_ID'];
            if(!$sections[$id]){
                $url = \CIBlock::ReplaceSectionUrl($item['SECTION_PAGE_URL'], $item, false, 'E');
                $item['URL'] = $url;
                $sections[$id] = [
                    'id' => $id,
                    'url' => $item['URL'],
                    'name' => $item['SECTION_CUSTOM_NAME'] ? $item['SECTION_CUSTOM_NAME'] : $item['SECTION_NAME']
                ];
                $count++;
            }
        }
        return array_values($sections);
    }
    public static function transliterate(string $str): string
    {
        $translit = [
            'q' => 'й',
            'w' => 'ц',
            'e' => 'у',
            'r' => 'к',
            't' => 'е',
            'y' => 'н',
            'u' => 'г',
            'i' => 'ш',
            'o' => 'щ',
            'p' => 'з',
            '[' => 'х',
            ']' => 'ъ',
            'a' => 'ф',
            's' => 'ы',
            'd' => 'в',
            'f' => 'а',
            'g' => 'п',
            'h' => 'р',
            'j' => 'о',
            'k' => 'л',
            'l' => 'д',
            ';' => 'ж',
            "'" => 'э',
            'z' => 'я',
            'x' => 'ч',
            'c' => 'с',
            'v' => 'м',
            'b' => 'и',
            'n' => 'т',
            'm' => 'ь',
            ',' => 'б',
            '.' => 'ю',
            '/' => '.',
            ' ' => ' '
        ];

        $transliterated = '';
        $length = strlen($str);
        for ($i = 0; $i < $length; $i++) {
            $char = strtolower($str[$i]);
            if (isset($translit[$char])) {
                if ($str[$i] === strtoupper($str[$i])) {
                    $transliterated .= strtoupper($translit[$char]);
                } else {
                    $transliterated .= $translit[$char];
                }
            } else {
                $transliterated .= $char;
            }
        }

        return $transliterated;
    }
    public static function issetTranslitirate(string $str): bool
    {
        return preg_match('/[a-zA-Z\[\]\'\'\/\s\;]/', $str) === 1;
    }
}
