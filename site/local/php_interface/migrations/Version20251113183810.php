<?php

namespace Sprint\Migration;


class Version20251113183810 extends Version
{
    protected $author = "admin";

    protected $description = "веб форма на главной";

    protected $moduleVersion = "5.4.1";

    /**
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
        $helper = $this->getHelperManager();
        $formId = $helper->Form()->saveForm(array (
  'NAME' => 'Тест',
  'SID' => 'SIMPLE_FORM_1',
  'MAIL_EVENT_TYPE' => 'FORM_FILLING_SIMPLE_FORM_1',
  'arSITE' => 
  array (
    0 => 's1',
  ),
  'arMENU' => 
  array (
    'ru' => 'Ссылка',
    'en' => 'Ссылка',
  ),
  'arGROUP' => 
  array (
  ),
  'arMAIL_TEMPLATE' => 
  array (
  ),
));
        $helper->Form()->saveStatuses($formId, array (
  0 => 
  array (
    'CSS' => 'statusgreen',
    'TITLE' => 'DEFAULT',
    'arPERMISSION_VIEW' => 
    array (
      0 => '0',
    ),
    'arPERMISSION_MOVE' => 
    array (
      0 => '0',
    ),
    'arPERMISSION_EDIT' => 
    array (
      0 => '0',
    ),
    'arPERMISSION_DELETE' => 
    array (
      0 => '0',
    ),
  ),
));
        $helper->Form()->saveFields($formId, array (
  0 => 
  array (
    'TITLE' => 'Имя',
    'TITLE_TYPE' => 'text',
    'SID' => 'SIMPLE_QUESTION_703',
    'REQUIRED' => 'Y',
    'FILTER_TITLE' => 'Имя',
    'RESULTS_TABLE_TITLE' => 'Имя',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'text',
      ),
    ),
    'VALIDATORS' => 
    array (
      0 => 
      array (
        'PARAMS' => 
        array (
          'LENGTH_FROM' => 1,
          'LENGTH_TO' => 100,
        ),
        'NAME' => 'text_len',
      ),
    ),
  ),
  1 => 
  array (
    'TITLE' => 'Фамилия',
    'TITLE_TYPE' => 'text',
    'SID' => 'SIMPLE_QUESTION_309',
    'C_SORT' => '200',
    'REQUIRED' => 'Y',
    'FILTER_TITLE' => 'Фамилия',
    'RESULTS_TABLE_TITLE' => 'Фамилия',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'text',
      ),
    ),
    'VALIDATORS' => 
    array (
      0 => 
      array (
        'PARAMS' => 
        array (
          'LENGTH_FROM' => 1,
          'LENGTH_TO' => 100,
        ),
        'NAME' => 'text_len',
      ),
    ),
  ),
  2 => 
  array (
    'TITLE' => '123?',
    'TITLE_TYPE' => 'text',
    'SID' => 'SIMPLE_QUESTION_913',
    'C_SORT' => '300',
    'REQUIRED' => 'Y',
    'FILTER_TITLE' => '123?',
    'RESULTS_TABLE_TITLE' => '123?',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'MESSAGE' => '111',
        'FIELD_TYPE' => 'checkbox',
        'FIELD_PARAM' => 'checked',
      ),
      1 => 
      array (
        'MESSAGE' => '2222',
        'FIELD_TYPE' => 'checkbox',
        'C_SORT' => '10',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  3 => 
  array (
    'TITLE' => 'radio',
    'TITLE_TYPE' => 'text',
    'SID' => 'SIMPLE_QUESTION_396',
    'C_SORT' => '400',
    'FILTER_TITLE' => 'radio',
    'RESULTS_TABLE_TITLE' => 'radio',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'MESSAGE' => '111111',
        'FIELD_TYPE' => 'radio',
      ),
      1 => 
      array (
        'MESSAGE' => '22222',
        'FIELD_TYPE' => 'radio',
        'C_SORT' => '10',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  4 => 
  array (
    'TITLE' => 'тексти',
    'TITLE_TYPE' => 'text',
    'SID' => 'SIMPLE_QUESTION_504',
    'C_SORT' => '500',
    'FILTER_TITLE' => 'тексти',
    'RESULTS_TABLE_TITLE' => 'тексти',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'textarea',
        'FIELD_WIDTH' => '20',
        'FIELD_HEIGHT' => '20',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  5 => 
  array (
    'TITLE' => 'select',
    'TITLE_TYPE' => 'text',
    'SID' => 'SIMPLE_QUESTION_582',
    'C_SORT' => '600',
    'FILTER_TITLE' => 'select',
    'RESULTS_TABLE_TITLE' => 'select',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'MESSAGE' => '11111',
        'FIELD_TYPE' => 'dropdown',
      ),
      1 => 
      array (
        'MESSAGE' => '222222',
        'FIELD_TYPE' => 'dropdown',
        'C_SORT' => '10',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  6 => 
  array (
    'TITLE' => 'multiseelct',
    'TITLE_TYPE' => 'text',
    'SID' => 'SIMPLE_QUESTION_967',
    'C_SORT' => '700',
    'FILTER_TITLE' => 'multiseelct',
    'RESULTS_TABLE_TITLE' => 'multiseelct',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'MESSAGE' => '111111',
        'FIELD_TYPE' => 'multiselect',
      ),
      1 => 
      array (
        'MESSAGE' => '222222',
        'FIELD_TYPE' => 'multiselect',
        'C_SORT' => '10',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  7 => 
  array (
    'TITLE' => 'date',
    'TITLE_TYPE' => 'text',
    'SID' => 'SIMPLE_QUESTION_367',
    'C_SORT' => '800',
    'FILTER_TITLE' => 'date',
    'RESULTS_TABLE_TITLE' => 'date',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'date',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  8 => 
  array (
    'TITLE' => 'image',
    'TITLE_TYPE' => 'text',
    'SID' => 'SIMPLE_QUESTION_716',
    'C_SORT' => '900',
    'FILTER_TITLE' => 'image',
    'RESULTS_TABLE_TITLE' => 'image',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'image',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  9 => 
  array (
    'TITLE' => 'file',
    'TITLE_TYPE' => 'text',
    'SID' => 'SIMPLE_QUESTION_561',
    'C_SORT' => '1000',
    'FILTER_TITLE' => 'file',
    'RESULTS_TABLE_TITLE' => 'file',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'file',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  10 => 
  array (
    'TITLE' => 'email',
    'TITLE_TYPE' => 'text',
    'SID' => 'SIMPLE_QUESTION_387',
    'C_SORT' => '1100',
    'FILTER_TITLE' => 'email',
    'RESULTS_TABLE_TITLE' => 'email',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'email',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  11 => 
  array (
    'TITLE' => 'url',
    'TITLE_TYPE' => 'text',
    'SID' => 'SIMPLE_QUESTION_600',
    'C_SORT' => '1200',
    'FILTER_TITLE' => 'url',
    'RESULTS_TABLE_TITLE' => 'url',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'url',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  12 => 
  array (
    'TITLE' => 'password',
    'TITLE_TYPE' => 'text',
    'SID' => 'SIMPLE_QUESTION_950',
    'C_SORT' => '1300',
    'FILTER_TITLE' => 'password',
    'RESULTS_TABLE_TITLE' => 'password',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'FIELD_TYPE' => 'password',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
));
    }
}

