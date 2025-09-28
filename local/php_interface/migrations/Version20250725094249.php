<?php

namespace Sprint\Migration;


class Version20250725094249 extends Version
{
  protected $author = "admin";

  protected $description = "иб контент";

  protected $moduleVersion = "5.0.0";

  /**
   * @throws Exceptions\HelperException
   * @return bool|void
   */
  public function up()
  {
    $helper = $this->getHelperManager();
    $helper->Iblock()->saveIblockType(array(
      'ID' => 'content',
      'SECTIONS' => 'Y',
      'EDIT_FILE_BEFORE' => '',
      'EDIT_FILE_AFTER' => '',
      'IN_RSS' => 'N',
      'SORT' => '500',
      'LANG' =>
      array(
        'ru' =>
        array(
          'NAME' => 'Контент',
          'SECTION_NAME' => '',
          'ELEMENT_NAME' => '',
        ),
        'en' =>
        array(
          'NAME' => 'Content',
          'SECTION_NAME' => '',
          'ELEMENT_NAME' => '',
        ),
      ),
    ));
    $iblockId = $helper->Iblock()->saveIblock(array(
      'IBLOCK_TYPE_ID' => 'content',
      'LID' =>
      array(
        0 => 's1',
      ),
      'CODE' => 'content',
      'API_CODE' => 'ContentApi',
      'REST_ON' => 'N',
      'NAME' => 'Контент',
      'ACTIVE' => 'Y',
      'SORT' => '500',
      'LIST_PAGE_URL' => '',
      'DETAIL_PAGE_URL' => '',
      'SECTION_PAGE_URL' => '',
      'CANONICAL_PAGE_URL' => '',
      'PICTURE' => NULL,
      'DESCRIPTION' => '',
      'DESCRIPTION_TYPE' => 'text',
      'RSS_TTL' => '24',
      'RSS_ACTIVE' => 'Y',
      'RSS_FILE_ACTIVE' => 'N',
      'RSS_FILE_LIMIT' => NULL,
      'RSS_FILE_DAYS' => NULL,
      'RSS_YANDEX_ACTIVE' => 'N',
      'XML_ID' => '',
      'INDEX_ELEMENT' => 'Y',
      'INDEX_SECTION' => 'Y',
      'WORKFLOW' => 'N',
      'BIZPROC' => 'N',
      'SECTION_CHOOSER' => 'L',
      'LIST_MODE' => '',
      'RIGHTS_MODE' => 'S',
      'SECTION_PROPERTY' => 'N',
      'PROPERTY_INDEX' => 'N',
      'VERSION' => '1',
      'LAST_CONV_ELEMENT' => '0',
      'SOCNET_GROUP_ID' => NULL,
      'EDIT_FILE_BEFORE' => '',
      'EDIT_FILE_AFTER' => '',
      'SECTIONS_NAME' => 'Разделы',
      'SECTION_NAME' => 'Раздел',
      'ELEMENTS_NAME' => 'Элементы',
      'ELEMENT_NAME' => 'Элемент',
      'FULLTEXT_INDEX' => 'N',
      'EXTERNAL_ID' => '',
      'LANG_DIR' => '/',
      'IPROPERTY_TEMPLATES' =>
      array(),
      'ELEMENT_ADD' => 'Добавить элемент',
      'ELEMENT_EDIT' => 'Изменить элемент',
      'ELEMENT_DELETE' => 'Удалить элемент',
      'SECTION_ADD' => 'Добавить раздел',
      'SECTION_EDIT' => 'Изменить раздел',
      'SECTION_DELETE' => 'Удалить раздел',
    ));
    $helper->Iblock()->saveIblockFields($iblockId, array(
      'IBLOCK_SECTION' =>
      array(
        'NAME' => 'Привязка к разделам',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' =>
        array(
          'KEEP_IBLOCK_SECTION_ID' => 'N',
        ),
        'VISIBLE' => 'Y',
      ),
      'ACTIVE' =>
      array(
        'NAME' => 'Активность',
        'IS_REQUIRED' => 'Y',
        'DEFAULT_VALUE' => 'Y',
        'VISIBLE' => 'Y',
      ),
      'ACTIVE_FROM' =>
      array(
        'NAME' => 'Начало активности',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' => '',
        'VISIBLE' => 'Y',
      ),
      'ACTIVE_TO' =>
      array(
        'NAME' => 'Окончание активности',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' => '',
        'VISIBLE' => 'Y',
      ),
      'SORT' =>
      array(
        'NAME' => 'Сортировка',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' => '500',
        'VISIBLE' => 'Y',
      ),
      'NAME' =>
      array(
        'NAME' => 'Название',
        'IS_REQUIRED' => 'Y',
        'DEFAULT_VALUE' => '',
        'VISIBLE' => 'Y',
      ),
      'PREVIEW_PICTURE' =>
      array(
        'NAME' => 'Картинка для анонса',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' =>
        array(
          'FROM_DETAIL' => 'N',
          'UPDATE_WITH_DETAIL' => 'N',
          'DELETE_WITH_DETAIL' => 'N',
          'SCALE' => 'N',
          'WIDTH' => '',
          'HEIGHT' => '',
          'IGNORE_ERRORS' => 'N',
          'METHOD' => 'resample',
          'COMPRESSION' => 95,
          'USE_WATERMARK_TEXT' => 'N',
          'WATERMARK_TEXT' => '',
          'WATERMARK_TEXT_FONT' => '',
          'WATERMARK_TEXT_COLOR' => '',
          'WATERMARK_TEXT_SIZE' => '',
          'WATERMARK_TEXT_POSITION' => 'tl',
          'USE_WATERMARK_FILE' => 'N',
          'WATERMARK_FILE' => '',
          'WATERMARK_FILE_ALPHA' => '',
          'WATERMARK_FILE_POSITION' => 'tl',
          'WATERMARK_FILE_ORDER' => '',
        ),
        'VISIBLE' => 'Y',
      ),
      'PREVIEW_TEXT_TYPE' =>
      array(
        'NAME' => 'Тип описания для анонса',
        'IS_REQUIRED' => 'Y',
        'DEFAULT_VALUE' => 'text',
        'VISIBLE' => 'Y',
      ),
      'PREVIEW_TEXT' =>
      array(
        'NAME' => 'Описание для анонса',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' => '',
        'VISIBLE' => 'Y',
      ),
      'DETAIL_PICTURE' =>
      array(
        'NAME' => 'Детальная картинка',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' =>
        array(
          'SCALE' => 'N',
          'WIDTH' => '',
          'HEIGHT' => '',
          'IGNORE_ERRORS' => 'N',
          'METHOD' => 'resample',
          'COMPRESSION' => 95,
          'USE_WATERMARK_TEXT' => 'N',
          'WATERMARK_TEXT' => '',
          'WATERMARK_TEXT_FONT' => '',
          'WATERMARK_TEXT_COLOR' => '',
          'WATERMARK_TEXT_SIZE' => '',
          'WATERMARK_TEXT_POSITION' => 'tl',
          'USE_WATERMARK_FILE' => 'N',
          'WATERMARK_FILE' => '',
          'WATERMARK_FILE_ALPHA' => '',
          'WATERMARK_FILE_POSITION' => 'tl',
          'WATERMARK_FILE_ORDER' => '',
        ),
        'VISIBLE' => 'Y',
      ),
      'DETAIL_TEXT_TYPE' =>
      array(
        'NAME' => 'Тип детального описания',
        'IS_REQUIRED' => 'Y',
        'DEFAULT_VALUE' => 'text',
        'VISIBLE' => 'Y',
      ),
      'DETAIL_TEXT' =>
      array(
        'NAME' => 'Детальное описание',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' => '',
        'VISIBLE' => 'Y',
      ),
      'XML_ID' =>
      array(
        'NAME' => 'Внешний код',
        'IS_REQUIRED' => 'Y',
        'DEFAULT_VALUE' => '',
        'VISIBLE' => 'Y',
      ),
      'CODE' =>
      array(
        'NAME' => 'Символьный код',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' =>
        array(
          'UNIQUE' => 'N',
          'TRANSLITERATION' => 'N',
          'TRANS_LEN' => 100,
          'TRANS_CASE' => 'L',
          'TRANS_SPACE' => '-',
          'TRANS_OTHER' => '-',
          'TRANS_EAT' => 'Y',
          'USE_GOOGLE' => 'N',
        ),
        'VISIBLE' => 'Y',
      ),
      'TAGS' =>
      array(
        'NAME' => 'Теги',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' => '',
        'VISIBLE' => 'Y',
      ),
      'SECTION_NAME' =>
      array(
        'NAME' => 'Название',
        'IS_REQUIRED' => 'Y',
        'DEFAULT_VALUE' => '',
        'VISIBLE' => 'Y',
      ),
      'SECTION_PICTURE' =>
      array(
        'NAME' => 'Картинка для анонса',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' =>
        array(
          'FROM_DETAIL' => 'N',
          'UPDATE_WITH_DETAIL' => 'N',
          'DELETE_WITH_DETAIL' => 'N',
          'SCALE' => 'N',
          'WIDTH' => '',
          'HEIGHT' => '',
          'IGNORE_ERRORS' => 'N',
          'METHOD' => 'resample',
          'COMPRESSION' => 95,
          'USE_WATERMARK_TEXT' => 'N',
          'WATERMARK_TEXT' => '',
          'WATERMARK_TEXT_FONT' => '',
          'WATERMARK_TEXT_COLOR' => '',
          'WATERMARK_TEXT_SIZE' => '',
          'WATERMARK_TEXT_POSITION' => 'tl',
          'USE_WATERMARK_FILE' => 'N',
          'WATERMARK_FILE' => '',
          'WATERMARK_FILE_ALPHA' => '',
          'WATERMARK_FILE_POSITION' => 'tl',
          'WATERMARK_FILE_ORDER' => '',
        ),
        'VISIBLE' => 'Y',
      ),
      'SECTION_DESCRIPTION_TYPE' =>
      array(
        'NAME' => 'Тип описания',
        'IS_REQUIRED' => 'Y',
        'DEFAULT_VALUE' => 'text',
        'VISIBLE' => 'Y',
      ),
      'SECTION_DESCRIPTION' =>
      array(
        'NAME' => 'Описание',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' => '',
        'VISIBLE' => 'Y',
      ),
      'SECTION_DETAIL_PICTURE' =>
      array(
        'NAME' => 'Детальная картинка',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' =>
        array(
          'SCALE' => 'N',
          'WIDTH' => '',
          'HEIGHT' => '',
          'IGNORE_ERRORS' => 'N',
          'METHOD' => 'resample',
          'COMPRESSION' => 95,
          'USE_WATERMARK_TEXT' => 'N',
          'WATERMARK_TEXT' => '',
          'WATERMARK_TEXT_FONT' => '',
          'WATERMARK_TEXT_COLOR' => '',
          'WATERMARK_TEXT_SIZE' => '',
          'WATERMARK_TEXT_POSITION' => 'tl',
          'USE_WATERMARK_FILE' => 'N',
          'WATERMARK_FILE' => '',
          'WATERMARK_FILE_ALPHA' => '',
          'WATERMARK_FILE_POSITION' => 'tl',
          'WATERMARK_FILE_ORDER' => '',
        ),
        'VISIBLE' => 'Y',
      ),
      'SECTION_XML_ID' =>
      array(
        'NAME' => 'Внешний код',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' => '',
        'VISIBLE' => 'Y',
      ),
      'SECTION_CODE' =>
      array(
        'NAME' => 'Символьный код',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' =>
        array(
          'UNIQUE' => 'N',
          'TRANSLITERATION' => 'N',
          'TRANS_LEN' => 100,
          'TRANS_CASE' => 'L',
          'TRANS_SPACE' => '-',
          'TRANS_OTHER' => '-',
          'TRANS_EAT' => 'Y',
          'USE_GOOGLE' => 'N',
        ),
        'VISIBLE' => 'Y',
      ),
      'LOG_SECTION_ADD' =>
      array(
        'NAME' => 'LOG_SECTION_ADD',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' => NULL,
        'VISIBLE' => 'Y',
      ),
      'LOG_SECTION_EDIT' =>
      array(
        'NAME' => 'LOG_SECTION_EDIT',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' => NULL,
        'VISIBLE' => 'Y',
      ),
      'LOG_SECTION_DELETE' =>
      array(
        'NAME' => 'LOG_SECTION_DELETE',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' => NULL,
        'VISIBLE' => 'Y',
      ),
      'LOG_ELEMENT_ADD' =>
      array(
        'NAME' => 'LOG_ELEMENT_ADD',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' => NULL,
        'VISIBLE' => 'Y',
      ),
      'LOG_ELEMENT_EDIT' =>
      array(
        'NAME' => 'LOG_ELEMENT_EDIT',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' => NULL,
        'VISIBLE' => 'Y',
      ),
      'LOG_ELEMENT_DELETE' =>
      array(
        'NAME' => 'LOG_ELEMENT_DELETE',
        'IS_REQUIRED' => 'N',
        'DEFAULT_VALUE' => NULL,
        'VISIBLE' => 'Y',
      ),
    ));
    $helper->Iblock()->saveGroupPermissions($iblockId, array(
      'administrators' => 'X',
      'everyone' => 'R',
    ));
    $helper->Iblock()->saveProperty($iblockId, array(
      'NAME' => 'Заголовок',
      'ACTIVE' => 'Y',
      'SORT' => '500',
      'CODE' => 'PRODUCTS_TITLE',
      'DEFAULT_VALUE' => '',
      'PROPERTY_TYPE' => 'S',
      'ROW_COUNT' => '1',
      'COL_COUNT' => '30',
      'LIST_TYPE' => 'L',
      'MULTIPLE' => 'N',
      'XML_ID' => '',
      'FILE_TYPE' => '',
      'MULTIPLE_CNT' => '5',
      'LINK_IBLOCK_ID' => '0',
      'WITH_DESCRIPTION' => 'N',
      'SEARCHABLE' => 'N',
      'FILTRABLE' => 'N',
      'IS_REQUIRED' => 'N',
      'VERSION' => '1',
      'USER_TYPE' => NULL,
      'USER_TYPE_SETTINGS' => 'a:0:{}',
      'HINT' => '',
      'SMART_FILTER' => NULL,
      'DISPLAY_TYPE' => NULL,
      'DISPLAY_EXPANDED' => NULL,
      'FILTER_HINT' => NULL,
    ));
    $helper->Iblock()->saveProperty($iblockId, array(
      'NAME' => 'Товары',
      'ACTIVE' => 'Y',
      'SORT' => '500',
      'CODE' => 'PRODUCTS_IDS',
      'DEFAULT_VALUE' => '',
      'PROPERTY_TYPE' => 'E',
      'ROW_COUNT' => '1',
      'COL_COUNT' => '30',
      'LIST_TYPE' => 'L',
      'MULTIPLE' => 'Y',
      'XML_ID' => '',
      'FILE_TYPE' => '',
      'MULTIPLE_CNT' => '5',
      'LINK_IBLOCK_ID' => 'catalog:catalog',
      'WITH_DESCRIPTION' => 'N',
      'SEARCHABLE' => 'N',
      'FILTRABLE' => 'N',
      'IS_REQUIRED' => 'N',
      'VERSION' => '1',
      'USER_TYPE' => NULL,
      'USER_TYPE_SETTINGS' => 'a:0:{}',
      'HINT' => '',
      'FEATURES' =>
      array(
        0 =>
        array(
          'MODULE_ID' => 'iblock',
          'FEATURE_ID' => 'DETAIL_PAGE_SHOW',
          'IS_ENABLED' => 'N',
        ),
        1 =>
        array(
          'MODULE_ID' => 'iblock',
          'FEATURE_ID' => 'LIST_PAGE_SHOW',
          'IS_ENABLED' => 'N',
        ),
      ),
    ));
    $helper->Iblock()->saveProperty($iblockId, array(
      'NAME' => 'Выделить товар',
      'ACTIVE' => 'Y',
      'SORT' => '500',
      'CODE' => 'PRODUCT_BIG',
      'DEFAULT_VALUE' => '',
      'PROPERTY_TYPE' => 'E',
      'ROW_COUNT' => '1',
      'COL_COUNT' => '30',
      'LIST_TYPE' => 'L',
      'MULTIPLE' => 'N',
      'XML_ID' => '',
      'FILE_TYPE' => '',
      'MULTIPLE_CNT' => '5',
      'LINK_IBLOCK_ID' => 'catalog:catalog',
      'WITH_DESCRIPTION' => 'N',
      'SEARCHABLE' => 'N',
      'FILTRABLE' => 'N',
      'IS_REQUIRED' => 'N',
      'VERSION' => '1',
      'USER_TYPE' => NULL,
      'USER_TYPE_SETTINGS' => 'a:0:{}',
      'HINT' => '',
      'FEATURES' =>
      array(
        0 =>
        array(
          'MODULE_ID' => 'iblock',
          'FEATURE_ID' => 'DETAIL_PAGE_SHOW',
          'IS_ENABLED' => 'N',
        ),
        1 =>
        array(
          'MODULE_ID' => 'iblock',
          'FEATURE_ID' => 'LIST_PAGE_SHOW',
          'IS_ENABLED' => 'N',
        ),
      ),
    ));
    $helper->Iblock()->saveProperty($iblockId, array(
      'NAME' => 'Тип',
      'ACTIVE' => 'Y',
      'SORT' => '500',
      'CODE' => 'TYPE',
      'DEFAULT_VALUE' => '',
      'PROPERTY_TYPE' => 'L',
      'ROW_COUNT' => '1',
      'COL_COUNT' => '30',
      'LIST_TYPE' => 'L',
      'MULTIPLE' => 'N',
      'XML_ID' => '',
      'FILE_TYPE' => '',
      'MULTIPLE_CNT' => '5',
      'LINK_IBLOCK_ID' => '0',
      'WITH_DESCRIPTION' => 'N',
      'SEARCHABLE' => 'N',
      'FILTRABLE' => 'N',
      'IS_REQUIRED' => 'Y',
      'VERSION' => '1',
      'USER_TYPE' => NULL,
      'USER_TYPE_SETTINGS' => NULL,
      'HINT' => '',
      'VALUES' =>
      array(
        0 =>
        array(
          'VALUE' => 'Блок видео',
          'DEF' => 'N',
          'SORT' => '500',
          'XML_ID' => 'video',
        ),
        1 =>
        array(
          'VALUE' => 'Вконтакте',
          'DEF' => 'N',
          'SORT' => '500',
          'XML_ID' => 'vkontakte',
        ),
        2 =>
        array(
          'VALUE' => 'Две статьи',
          'DEF' => 'N',
          'SORT' => '500',
          'XML_ID' => 'two_articles',
        ),
        3 =>
        array(
          'VALUE' => 'Разделы каталога',
          'DEF' => 'N',
          'SORT' => '500',
          'XML_ID' => 'catalog_razdel',
        ),
        4 =>
        array(
          'VALUE' => 'Слайдер статей',
          'DEF' => 'N',
          'SORT' => '500',
          'XML_ID' => 'slider_articles',
        ),
        5 =>
        array(
          'VALUE' => 'Слайдер товаров',
          'DEF' => 'N',
          'SORT' => '500',
          'XML_ID' => 'slider',
        ),
      ),
      'FEATURES' =>
      array(
        0 =>
        array(
          'MODULE_ID' => 'iblock',
          'FEATURE_ID' => 'DETAIL_PAGE_SHOW',
          'IS_ENABLED' => 'N',
        ),
        1 =>
        array(
          'MODULE_ID' => 'iblock',
          'FEATURE_ID' => 'LIST_PAGE_SHOW',
          'IS_ENABLED' => 'N',
        ),
      ),
    ));
    $helper->Iblock()->saveProperty($iblockId, array(
      'NAME' => 'Ссылка',
      'ACTIVE' => 'Y',
      'SORT' => '500',
      'CODE' => 'LINK',
      'DEFAULT_VALUE' => '',
      'PROPERTY_TYPE' => 'S',
      'ROW_COUNT' => '1',
      'COL_COUNT' => '30',
      'LIST_TYPE' => 'L',
      'MULTIPLE' => 'N',
      'XML_ID' => '',
      'FILE_TYPE' => '',
      'MULTIPLE_CNT' => '5',
      'LINK_IBLOCK_ID' => '0',
      'WITH_DESCRIPTION' => 'N',
      'SEARCHABLE' => 'N',
      'FILTRABLE' => 'N',
      'IS_REQUIRED' => 'N',
      'VERSION' => '1',
      'USER_TYPE' => NULL,
      'USER_TYPE_SETTINGS' => 'a:0:{}',
      'HINT' => '',
    ));
    $helper->Iblock()->saveProperty($iblockId, array(
      'NAME' => 'Превью к видео',
      'ACTIVE' => 'Y',
      'SORT' => '500',
      'CODE' => 'PREVIEW_VIDEO',
      'DEFAULT_VALUE' => '',
      'PROPERTY_TYPE' => 'F',
      'ROW_COUNT' => '1',
      'COL_COUNT' => '30',
      'LIST_TYPE' => 'L',
      'MULTIPLE' => 'N',
      'XML_ID' => '',
      'FILE_TYPE' => 'jpg, gif, bmp, png, jpeg, webp',
      'MULTIPLE_CNT' => '5',
      'LINK_IBLOCK_ID' => '0',
      'WITH_DESCRIPTION' => 'N',
      'SEARCHABLE' => 'N',
      'FILTRABLE' => 'N',
      'IS_REQUIRED' => 'N',
      'VERSION' => '1',
      'USER_TYPE' => NULL,
      'USER_TYPE_SETTINGS' => 'a:0:{}',
      'HINT' => '',
      'FEATURES' =>
      array(
        0 =>
        array(
          'MODULE_ID' => 'iblock',
          'FEATURE_ID' => 'DETAIL_PAGE_SHOW',
          'IS_ENABLED' => 'N',
        ),
        1 =>
        array(
          'MODULE_ID' => 'iblock',
          'FEATURE_ID' => 'LIST_PAGE_SHOW',
          'IS_ENABLED' => 'N',
        ),
      ),
    ));
    $helper->Iblock()->saveProperty($iblockId, array(
      'NAME' => 'Заголовок к видео',
      'ACTIVE' => 'Y',
      'SORT' => '500',
      'CODE' => 'VIDEO_TITLE',
      'DEFAULT_VALUE' => '',
      'PROPERTY_TYPE' => 'S',
      'ROW_COUNT' => '1',
      'COL_COUNT' => '30',
      'LIST_TYPE' => 'L',
      'MULTIPLE' => 'N',
      'XML_ID' => '',
      'FILE_TYPE' => '',
      'MULTIPLE_CNT' => '5',
      'LINK_IBLOCK_ID' => '0',
      'WITH_DESCRIPTION' => 'N',
      'SEARCHABLE' => 'N',
      'FILTRABLE' => 'N',
      'IS_REQUIRED' => 'N',
      'VERSION' => '1',
      'USER_TYPE' => NULL,
      'USER_TYPE_SETTINGS' => 'a:0:{}',
      'HINT' => '',
    ));
    $helper->Iblock()->saveProperty($iblockId, array(
      'NAME' => 'Текст к видео',
      'ACTIVE' => 'Y',
      'SORT' => '500',
      'CODE' => 'VIDEO_TEXT',
      'DEFAULT_VALUE' =>
      array(
        'TEXT' => '',
        'TYPE' => 'HTML',
      ),
      'PROPERTY_TYPE' => 'S',
      'ROW_COUNT' => '1',
      'COL_COUNT' => '30',
      'LIST_TYPE' => 'L',
      'MULTIPLE' => 'N',
      'XML_ID' => '',
      'FILE_TYPE' => '',
      'MULTIPLE_CNT' => '5',
      'LINK_IBLOCK_ID' => '0',
      'WITH_DESCRIPTION' => 'N',
      'SEARCHABLE' => 'N',
      'FILTRABLE' => 'N',
      'IS_REQUIRED' => 'N',
      'VERSION' => '1',
      'USER_TYPE' => 'HTML',
      'USER_TYPE_SETTINGS' =>
      array(
        'height' => 200,
      ),
      'HINT' => '',
    ));
    $helper->Iblock()->saveProperty($iblockId, array(
      'NAME' => 'Ссылка на видео',
      'ACTIVE' => 'Y',
      'SORT' => '500',
      'CODE' => 'VIDEO_LINK',
      'DEFAULT_VALUE' => '',
      'PROPERTY_TYPE' => 'S',
      'ROW_COUNT' => '1',
      'COL_COUNT' => '30',
      'LIST_TYPE' => 'L',
      'MULTIPLE' => 'N',
      'XML_ID' => '',
      'FILE_TYPE' => '',
      'MULTIPLE_CNT' => '5',
      'LINK_IBLOCK_ID' => '0',
      'WITH_DESCRIPTION' => 'N',
      'SEARCHABLE' => 'N',
      'FILTRABLE' => 'N',
      'IS_REQUIRED' => 'N',
      'VERSION' => '1',
      'USER_TYPE' => NULL,
      'USER_TYPE_SETTINGS' => 'a:0:{}',
      'HINT' => '',
    ));
    $helper->Iblock()->saveProperty($iblockId, array(
      'NAME' => 'Cтатьи',
      'ACTIVE' => 'Y',
      'SORT' => '500',
      'CODE' => 'TWO_ARTICLES_IDS',
      'DEFAULT_VALUE' => '',
      'PROPERTY_TYPE' => 'E',
      'ROW_COUNT' => '1',
      'COL_COUNT' => '30',
      'LIST_TYPE' => 'L',
      'MULTIPLE' => 'Y',
      'XML_ID' => '',
      'FILE_TYPE' => '',
      'MULTIPLE_CNT' => '5',
      'LINK_IBLOCK_ID' => 'content:articles',
      'WITH_DESCRIPTION' => 'N',
      'SEARCHABLE' => 'N',
      'FILTRABLE' => 'N',
      'IS_REQUIRED' => 'N',
      'VERSION' => '1',
      'USER_TYPE' => NULL,
      'USER_TYPE_SETTINGS' => NULL,
      'HINT' => '',
      'FEATURES' =>
      array(
        0 =>
        array(
          'MODULE_ID' => 'iblock',
          'FEATURE_ID' => 'DETAIL_PAGE_SHOW',
          'IS_ENABLED' => 'N',
        ),
        1 =>
        array(
          'MODULE_ID' => 'iblock',
          'FEATURE_ID' => 'LIST_PAGE_SHOW',
          'IS_ENABLED' => 'N',
        ),
      ),
    ));
    $helper->Iblock()->saveProperty($iblockId, array(
      'NAME' => 'Статьи',
      'ACTIVE' => 'Y',
      'SORT' => '500',
      'CODE' => 'ARTICLES_IDS',
      'DEFAULT_VALUE' => '',
      'PROPERTY_TYPE' => 'E',
      'ROW_COUNT' => '1',
      'COL_COUNT' => '30',
      'LIST_TYPE' => 'L',
      'MULTIPLE' => 'Y',
      'XML_ID' => '',
      'FILE_TYPE' => '',
      'MULTIPLE_CNT' => '5',
      'LINK_IBLOCK_ID' => 'content:articles',
      'WITH_DESCRIPTION' => 'N',
      'SEARCHABLE' => 'N',
      'FILTRABLE' => 'N',
      'IS_REQUIRED' => 'N',
      'VERSION' => '1',
      'USER_TYPE' => NULL,
      'USER_TYPE_SETTINGS' => NULL,
      'HINT' => '',
      'FEATURES' =>
      array(
        0 =>
        array(
          'MODULE_ID' => 'iblock',
          'FEATURE_ID' => 'DETAIL_PAGE_SHOW',
          'IS_ENABLED' => 'N',
        ),
        1 =>
        array(
          'MODULE_ID' => 'iblock',
          'FEATURE_ID' => 'LIST_PAGE_SHOW',
          'IS_ENABLED' => 'N',
        ),
      ),
    ));
    $helper->Iblock()->saveProperty($iblockId, array(
      'NAME' => 'Заголовок',
      'ACTIVE' => 'Y',
      'SORT' => '500',
      'CODE' => 'ARTICLES_TITLE',
      'DEFAULT_VALUE' => '',
      'PROPERTY_TYPE' => 'S',
      'ROW_COUNT' => '1',
      'COL_COUNT' => '30',
      'LIST_TYPE' => 'L',
      'MULTIPLE' => 'N',
      'XML_ID' => '',
      'FILE_TYPE' => '',
      'MULTIPLE_CNT' => '5',
      'LINK_IBLOCK_ID' => '0',
      'WITH_DESCRIPTION' => 'N',
      'SEARCHABLE' => 'N',
      'FILTRABLE' => 'N',
      'IS_REQUIRED' => 'N',
      'VERSION' => '1',
      'USER_TYPE' => NULL,
      'USER_TYPE_SETTINGS' => NULL,
      'HINT' => '',
    ));
    $helper->Iblock()->saveProperty($iblockId, array(
      'NAME' => 'Тип товаров',
      'ACTIVE' => 'Y',
      'SORT' => '500',
      'CODE' => 'PRODUCTS_TYPE',
      'DEFAULT_VALUE' => '',
      'PROPERTY_TYPE' => 'L',
      'ROW_COUNT' => '1',
      'COL_COUNT' => '30',
      'LIST_TYPE' => 'L',
      'MULTIPLE' => 'N',
      'XML_ID' => '',
      'FILE_TYPE' => '',
      'MULTIPLE_CNT' => '5',
      'LINK_IBLOCK_ID' => '0',
      'WITH_DESCRIPTION' => 'N',
      'SEARCHABLE' => 'N',
      'FILTRABLE' => 'N',
      'IS_REQUIRED' => 'N',
      'VERSION' => '1',
      'USER_TYPE' => NULL,
      'USER_TYPE_SETTINGS' => NULL,
      'HINT' => 'Если выбрано, то id товаров будут добавлены в конец если в результате выборки было получено мало товаров',
      'VALUES' =>
      array(
        0 =>
        array(
          'VALUE' => 'Новинки',
          'DEF' => 'N',
          'SORT' => '500',
          'XML_ID' => 'new',
        ),
        1 =>
        array(
          'VALUE' => 'Популярные',
          'DEF' => 'N',
          'SORT' => '500',
          'XML_ID' => 'popular',
        ),
      ),
      'FEATURES' =>
      array(
        0 =>
        array(
          'MODULE_ID' => 'iblock',
          'FEATURE_ID' => 'DETAIL_PAGE_SHOW',
          'IS_ENABLED' => 'N',
        ),
        1 =>
        array(
          'MODULE_ID' => 'iblock',
          'FEATURE_ID' => 'LIST_PAGE_SHOW',
          'IS_ENABLED' => 'N',
        ),
      ),
    ));
    $helper->Iblock()->saveProperty($iblockId, array(
      'NAME' => 'Разделы каталога',
      'ACTIVE' => 'Y',
      'SORT' => '500',
      'CODE' => 'CATALOG_RAZDEL_IDS',
      'DEFAULT_VALUE' => '',
      'PROPERTY_TYPE' => 'E',
      'ROW_COUNT' => '1',
      'COL_COUNT' => '30',
      'LIST_TYPE' => 'L',
      'MULTIPLE' => 'Y',
      'XML_ID' => '',
      'FILE_TYPE' => '',
      'MULTIPLE_CNT' => '5',
      'LINK_IBLOCK_ID' => 'content:mainSections',
      'WITH_DESCRIPTION' => 'N',
      'SEARCHABLE' => 'N',
      'FILTRABLE' => 'N',
      'IS_REQUIRED' => 'N',
      'VERSION' => '1',
      'USER_TYPE' => NULL,
      'USER_TYPE_SETTINGS' => NULL,
      'HINT' => '',
      'FEATURES' =>
      array(
        0 =>
        array(
          'MODULE_ID' => 'iblock',
          'FEATURE_ID' => 'DETAIL_PAGE_SHOW',
          'IS_ENABLED' => 'N',
        ),
        1 =>
        array(
          'MODULE_ID' => 'iblock',
          'FEATURE_ID' => 'LIST_PAGE_SHOW',
          'IS_ENABLED' => 'N',
        ),
      ),
    ));
    $helper->UserOptions()->saveElementForm($iblockId, array(
      'Параметры|edit1' =>
      array(
        'ID' => 'ID',
        'DATE_CREATE' => 'Создан',
        'TIMESTAMP_X' => 'Изменен',
        'ACTIVE' => 'Активность',
        'ACTIVE_FROM' => 'Начало активности',
        'ACTIVE_TO' => 'Окончание активности',
        'NAME' => 'Название',
        'XML_ID' => 'Внешний код',
        'CODE' => 'Символьный код',
        'SORT' => 'Сортировка',
        'IBLOCK_ELEMENT_PROP_VALUE' => 'Значения свойств',
        'PROPERTY_TYPE' => 'Тип',
        'PROPERTY_LINK' => 'Ссылка',
      ),
      'Товары|cedit1' =>
      array(
        'PROPERTY_PRODUCTS_TITLE' => 'Заголовок',
        'PROPERTY_PRODUCTS_IDS' => 'Товары',
        'PROPERTY_PRODUCT_BIG' => 'Выделить товар',
        'PROPERTY_PRODUCTS_TYPE' => 'Тип товаров',
      ),
      'Видео|cedit2' =>
      array(
        'PROPERTY_VIDEO_TITLE' => 'Заголовок к видео',
        'PROPERTY_VIDEO_TEXT' => 'Текст к видео',
        'PROPERTY_PREVIEW_VIDEO' => 'Превью к видео',
        'PROPERTY_VIDEO_LINK' => 'Ссылка на видео',
        'PROPERTY_64' => 'Видео',
      ),
      'Две статьи|cedit3' =>
      array(
        'PROPERTY_TWO_ARTICLES_IDS' => 'Cтатьи',
      ),
      'Слайдер статей|cedit4' =>
      array(
        'PROPERTY_ARTICLES_TITLE' => 'Заголовок',
        'PROPERTY_ARTICLES_IDS' => 'Статьи',
      ),
      'Разделы каталога|cedit5' =>
      array(
        'PROPERTY_CATALOG_RAZDEL_IDS' => 'Разделы каталога',
      ),
    ));
    $helper->UserOptions()->saveElementGrid($iblockId, array(
      'views' =>
      array(
        'default' =>
        array(
          'columns' =>
          array(
            0 => '',
          ),
          'columns_sizes' =>
          array(
            'expand' => 1,
            'columns' =>
            array(),
          ),
          'sticked_columns' =>
          array(),
          'last_sort_by' => 'sort',
          'last_sort_order' => 'asc',
          'custom_names' =>
          array(),
        ),
      ),
      'filters' =>
      array(),
      'current_view' => 'default',
    ));
  }
}
