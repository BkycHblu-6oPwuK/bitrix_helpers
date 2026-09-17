<?php

namespace Sprint\Migration;


class Version20260723112335 extends Version
{
    protected $author = "admin";

    protected $description = "удаление инфоблока новости";

    protected $moduleVersion = "5.6.2";

    public function up()
    {
        $helper = $this->getHelperManager();

        $helper->Iblock()->deleteIblockIfExists('news', 'news');
    }
}
