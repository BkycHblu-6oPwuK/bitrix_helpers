<?php

namespace Sprint\Migration;

class Version20251111174954 extends Version
{
    protected $author = "admin";

    protected $description   = "элементы инфоблока меню";

    protected $moduleVersion = "5.4.1";

    /**
     * @throws Exceptions\MigrationException
     * @throws Exceptions\RestartException
     * @return bool|void
     */
    public function up()
    {
        $this->getExchangeManager()
             ->IblockElementsImport()
             ->setLimit(20)
             ->execute(function ($item) {
                 $this->getHelperManager()
                      ->Iblock()
                      ->saveElementByCode(
                          $item['iblock_id'],
                          $item['fields'],
                          $item['properties']
                      );
             });
    }

    /**
     * @throws Exceptions\MigrationException
     * @throws Exceptions\RestartException
     * @return bool|void
     */
    public function down()
    {
        $this->getExchangeManager()
             ->IblockElementsImport()
             ->setLimit(10)
             ->execute(function ($item) {
                 $this->getHelperManager()
                      ->Iblock()
                      ->deleteElementByCode(
                          $item['iblock_id'],
                          $item['fields']['CODE']
                 );
             });
    }
}
