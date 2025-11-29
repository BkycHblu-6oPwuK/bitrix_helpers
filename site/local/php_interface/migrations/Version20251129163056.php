<?php

namespace Sprint\Migration;


class Version20251129163056 extends Version
{
    protected $author = "admin";

    protected $description = "удаление инфоблоков";

    protected $moduleVersion = "5.4.1";

    public function up()
    {
        $helper = $this->getHelperManager();

        $helper->Iblock()->deleteIblockIfExists('menu', 'content');
        $helper->Iblock()->deleteIblockIfExists('footer', 'content');
        $helper->Iblock()->deleteIblockIfExists('header', 'content');
        $helper->Iblock()->deleteIblockIfExists('pages', 'content');
    }
}
