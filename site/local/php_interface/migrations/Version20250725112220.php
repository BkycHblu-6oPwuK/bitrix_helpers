<?php

namespace Sprint\Migration;


class Version20250725112220 extends Version
{
  protected $author = "admin";

  protected $description = "хайлоад смс-билдинг";

  protected $moduleVersion = "5.0.0";

  /**
   * @throws Exceptions\HelperException
   * @return bool|void
   */
  public function up()
  {
    $helper = $this->getHelperManager();
    $hlblockId = $helper->Hlblock()->saveHlblock(array(
      'NAME' => 'SmsBuilding',
      'TABLE_NAME' => 'b_sms_building',
      'LANG' =>
      array(
        'ru' =>
        array(
          'NAME' => 'Смс-билдинг',
        ),
        'en' =>
        array(
          'NAME' => 'Смс-билдинг',
        ),
      ),
    ));
    $helper->Hlblock()->saveField($hlblockId, array(
      'FIELD_NAME' => 'UF_PHONE',
      'USER_TYPE_ID' => 'string',
      'XML_ID' => '',
      'SORT' => '100',
      'MULTIPLE' => 'N',
      'MANDATORY' => 'N',
      'SHOW_FILTER' => 'N',
      'SHOW_IN_LIST' => 'Y',
      'EDIT_IN_LIST' => 'Y',
      'IS_SEARCHABLE' => 'N',
      'SETTINGS' =>
      array(
        'SIZE' => 20,
        'ROWS' => 1,
        'REGEXP' => '',
        'MIN_LENGTH' => 0,
        'MAX_LENGTH' => 0,
        'DEFAULT_VALUE' => '',
      ),
      'EDIT_FORM_LABEL' =>
      array(
        'en' => 'Номер телефона',
        'ru' => 'Номер телефона',
      ),
      'LIST_COLUMN_LABEL' =>
      array(
        'en' => '',
        'ru' => '',
      ),
      'LIST_FILTER_LABEL' =>
      array(
        'en' => '',
        'ru' => '',
      ),
      'ERROR_MESSAGE' =>
      array(
        'en' => '',
        'ru' => '',
      ),
      'HELP_MESSAGE' =>
      array(
        'en' => '',
        'ru' => '',
      ),
    ));
    $helper->Hlblock()->saveField($hlblockId, array(
      'FIELD_NAME' => 'UF_SMS',
      'USER_TYPE_ID' => 'string',
      'XML_ID' => '',
      'SORT' => '100',
      'MULTIPLE' => 'N',
      'MANDATORY' => 'N',
      'SHOW_FILTER' => 'N',
      'SHOW_IN_LIST' => 'Y',
      'EDIT_IN_LIST' => 'Y',
      'IS_SEARCHABLE' => 'N',
      'SETTINGS' =>
      array(
        'SIZE' => 20,
        'ROWS' => 1,
        'REGEXP' => '',
        'MIN_LENGTH' => 0,
        'MAX_LENGTH' => 0,
        'DEFAULT_VALUE' => '',
      ),
      'EDIT_FORM_LABEL' =>
      array(
        'en' => 'смс',
        'ru' => 'смс',
      ),
      'LIST_COLUMN_LABEL' =>
      array(
        'en' => '',
        'ru' => '',
      ),
      'LIST_FILTER_LABEL' =>
      array(
        'en' => '',
        'ru' => '',
      ),
      'ERROR_MESSAGE' =>
      array(
        'en' => '',
        'ru' => '',
      ),
      'HELP_MESSAGE' =>
      array(
        'en' => '',
        'ru' => '',
      ),
    ));
    $helper->Hlblock()->saveField($hlblockId, array(
      'FIELD_NAME' => 'UF_DATE_CREATE',
      'USER_TYPE_ID' => 'datetime',
      'XML_ID' => '',
      'SORT' => '100',
      'MULTIPLE' => 'N',
      'MANDATORY' => 'N',
      'SHOW_FILTER' => 'N',
      'SHOW_IN_LIST' => 'Y',
      'EDIT_IN_LIST' => 'Y',
      'IS_SEARCHABLE' => 'N',
      'SETTINGS' =>
      array(
        'DEFAULT_VALUE' =>
        array(
          'TYPE' => 'NOW',
          'VALUE' => '',
        ),
        'USE_SECOND' => 'Y',
        'USE_TIMEZONE' => 'N',
      ),
      'EDIT_FORM_LABEL' =>
      array(
        'en' => 'Дата создания',
        'ru' => 'Дата создания',
      ),
      'LIST_COLUMN_LABEL' =>
      array(
        'en' => '',
        'ru' => '',
      ),
      'LIST_FILTER_LABEL' =>
      array(
        'en' => '',
        'ru' => '',
      ),
      'ERROR_MESSAGE' =>
      array(
        'en' => '',
        'ru' => '',
      ),
      'HELP_MESSAGE' =>
      array(
        'en' => '',
        'ru' => '',
      ),
    ));
    $helper->Hlblock()->saveField($hlblockId, array(
      'FIELD_NAME' => 'UF_ACTIVE',
      'USER_TYPE_ID' => 'boolean',
      'XML_ID' => '',
      'SORT' => '100',
      'MULTIPLE' => 'N',
      'MANDATORY' => 'N',
      'SHOW_FILTER' => 'N',
      'SHOW_IN_LIST' => 'Y',
      'EDIT_IN_LIST' => 'Y',
      'IS_SEARCHABLE' => 'N',
      'SETTINGS' =>
      array(
        'DEFAULT_VALUE' => 1,
        'DISPLAY' => 'CHECKBOX',
        'LABEL' =>
        array(
          0 => '',
          1 => '',
        ),
        'LABEL_CHECKBOX' => '',
      ),
      'EDIT_FORM_LABEL' =>
      array(
        'en' => 'Активность',
        'ru' => 'Активность',
      ),
      'LIST_COLUMN_LABEL' =>
      array(
        'en' => '',
        'ru' => '',
      ),
      'LIST_FILTER_LABEL' =>
      array(
        'en' => '',
        'ru' => '',
      ),
      'ERROR_MESSAGE' =>
      array(
        'en' => '',
        'ru' => '',
      ),
      'HELP_MESSAGE' =>
      array(
        'en' => '',
        'ru' => '',
      ),
    ));
    $helper->Hlblock()->saveField($hlblockId, array(
      'FIELD_NAME' => 'UF_NUMBER_INPUT',
      'USER_TYPE_ID' => 'integer',
      'XML_ID' => '',
      'SORT' => '100',
      'MULTIPLE' => 'N',
      'MANDATORY' => 'N',
      'SHOW_FILTER' => 'N',
      'SHOW_IN_LIST' => 'Y',
      'EDIT_IN_LIST' => 'Y',
      'IS_SEARCHABLE' => 'N',
      'SETTINGS' =>
      array(
        'SIZE' => 20,
        'MIN_VALUE' => 0,
        'MAX_VALUE' => 0,
        'DEFAULT_VALUE' => 0,
      ),
      'EDIT_FORM_LABEL' =>
      array(
        'en' => 'Количество попыток ввода',
        'ru' => 'Количество попыток ввода',
      ),
      'LIST_COLUMN_LABEL' =>
      array(
        'en' => '',
        'ru' => '',
      ),
      'LIST_FILTER_LABEL' =>
      array(
        'en' => '',
        'ru' => '',
      ),
      'ERROR_MESSAGE' =>
      array(
        'en' => '',
        'ru' => '',
      ),
      'HELP_MESSAGE' =>
      array(
        'en' => '',
        'ru' => '',
      ),
    ));
  }
}
