<?php
// $host = 'localhost';
// $port = 443;
// $errno = 0;
// $errstr = '';
// $ssl = 'ssl://';

// $fp = fsockopen("$ssl$host", $port, $errno, $errstr, 30);

// if (!$fp) {
//     echo "Ошибка подключения: $errstr ($errno)\n";
// } else {
//     var_dump("$ssl$host");
//     var_dump($port);
//     echo "<pre>";
//     var_dump($fp);
//     echo "</pre>";
// }

// require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

// use Bitrix\Main\Loader;
// use Itb\Delivery\Agents\CitiesUpdate;
// Loader::includeModule('itb.delivery');
// CitiesUpdate::exec();
//phpinfo();
//$body = "Test message.\nDelete it.";
//$val = mail("hosting_test@bitrixsoft.com", "Bitrix site checker", $body);
//
//if (!$val) {
//    $error = error_get_last();
//    echo "Error sending mail: " . $error['message'];
//}

// echo '<pre>';
// echo var_dump($_SERVER);
// echo '</pre>';

use Beeralex\Catalog\Basket\BasketFacade;
use Beeralex\Catalog\Enum\DIServiceKey;
use Beeralex\Catalog\Service\CatalogElementService;
use Beeralex\Catalog\Service\CatalogService;
use Beeralex\Content\Repository\HeaderRepository;
use Beeralex\Content\Repository\MenuRepository;
use Beeralex\Core\Config\Config as CoreConfig;
use Beeralex\Core\Model\ElementTableFactory;
use Beeralex\Core\Repository\IblockPropertyRepository;
use Beeralex\Core\Repository\IblockRepository;
use Beeralex\Core\Service\IblockService;
use Beeralex\Core\Service\LanguageService;
use Beeralex\Core\Service\UrlService;
use Beeralex\Core\Service\UserService;
use Beeralex\Core\UserType\IblockLinkType;
use Beeralex\Core\UserType\WebFormLinkType;
use Beeralex\Favorite\FavouriteService;
use Beeralex\Notification\Events\SmsEvent;
use Beeralex\Notification\Tables\NotificationLinkEventTypeTable;
use Beeralex\Notification\Tables\NotificationTemplateLinkTable;
use Beeralex\Oauth2\PrivateKeyChecker;
use Beeralex\User\Auth\Adapters\BitrixSocialService;
use Beeralex\User\Auth\AuthManager;
use Beeralex\User\Auth\Factories\SocialAuthenticatorFactory;
use Beeralex\User\Auth\Social\SocialManager;
use Beeralex\User\Contracts\UserRepositoryContract;
use Beeralex\User\EventHandlers;
use Beeralex\User\Options;
use Beeralex\User\Phone;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\ORM\Query\QueryHelper;
use Bitrix\Sale\Cashbox\Internals\CashboxTable;
use Itb\Catalog\Types\CatalogContext;
use Itb\Catalog\Types\Contracts\CatalogContextContract;
use Itb\Catalog\Types\Enum\TypesCatalog;
use Itb\Core\Assets\Vite;
use Itb\Core\Config;
use Itb\Core\DI\ServiceProviderContract;
use Itb\Core\DI\ServiceProviderRegistry;
use Itb\Core\Helpers\LanguageHelper;
use Itb\Core\Logger\FileLogger;
use Itb\Core\Logger\LoggerServiceProvider;
use Itb\Gigachat\Logger;
use Itb\Ssr\SsrService;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;
// require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';

// use Bitrix\Main\Web\HttpClient;
// use Illuminate\Support\LazyCollection;

// function get()
// {
//     $client = new HttpClient();
//     $pages = 500;
//     $page = 1;

//     // Открываем CSV файл в режиме записи ('w' - write), это очистит файл при каждом запуске
//     $file = fopen('/var/www/data.csv', 'w');

//     $firstEntry = true; // Флаг для записи заголовков

//     while ($page <= $pages) {
//         if ($response = $client->get("https://localhost/test2.php?page=$page")) {
//             $response = json_decode($response, true);

//             foreach ($response as $i) {
//                 if ($firstEntry) {
//                     // Записываем заголовки только один раз
//                     fputcsv($file, array_keys($i)); // Используем ключи массива как заголовки
//                     $firstEntry = false;
//                 }

//                 // Записываем данные в CSV файл
//                 fputcsv($file, $i);
//             }
//         }
//         $page++;
//     }

//     fclose($file); // Закрываем файл после записи

//     // Возвращаем LazyCollection для чтения CSV файла построчно
//     return new LazyCollection(function () {
//         if (file_exists('/var/www/data.csv')) {
//             $handle = fopen('/var/www/data.csv', 'r');
//             if ($handle) {
//                 // Читаем CSV файл построчно
//                 while (($row = fgetcsv($handle)) !== false) {
//                     // Преобразуем строку в ассоциативный массив (используем заголовки из первой строки)
//                     yield $row;
//                 }
//                 fclose($handle);
//             }
//         }
//     });
// }

// $apiData = get();
// $apiData->each(function ($item) {
//     echo '<pre>';
//     var_dump($item);
//     echo '</pre>';
// });
//$data = [];
//foreach(range(1, 2) as $i){
//    if($apiData->has($i)){
//        $data[] = $apiData->get($i);
//    }
//}
//echo '<pre>';
//var_dump($data);
//echo '</pre>';

// Loader::includeModule('itb.reviews');

// var_dump(LanguageHelper::getPlural(1, ['a','b', '3']));


//timeGrpc();
//timeCurl();

// $memcached = new \Memcached();
// $memcached->addServer('memcached', 11211);

// if ($memcached->set('test', 'value', 60)) {
//     echo 'Memcached работает!';
// } else {
//     echo 'Ошибка подключения к Memcached';
// }

//$redis = new \Redis();
//$redis->connect('redis', 6379); // 'redis' — имя контейнера в docker-compose.yml

// Установить значение
//$redis->set('key', 'value');

// Получить значение
//echo $redis->get('key');

// Loader::includeModule('sale');
// Loader::includeModule('api.sbis');

//$check = unserialize(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/2.txt'));
// Loader::includeModule('itb.gigachat');
// dd(Config::getViteBasePath());
// $logger = new Logger;
// $logger->error('1234', ['test' => 'test', 'orderId' => 1]);
//dd($check->getDataForCheck());
// dd(CashboxTable::query()->setSelect(['SETTINGS'])->where('HANDLER', CashBoxSbis::class)->fetch());
// dd((new CheckInfo(new CheckInfoParams([1])))->request()->getResponse()->getResult());

// interface loggerInterface
// {
//     public function log(string $message);
// }

// class MyLogger implements loggerInterface
// {
//     public function log(string $message)
//     {
//         var_dump($message);
//     }
// }

// class Database
// {
//     protected readonly loggerInterface $logger;
//     public function __construct(loggerInterface $logger)
//     {
//         $this->logger = $logger;
//     }
//     public function save()
//     {
//         $this->logger->log('данные сохранены');
//     }
// }

// \Bitrix\Main\DI\ServiceLocator::getInstance()->addInstanceLazy('logger', [
//     'className' => MyLogger::class
// ]);
// \Bitrix\Main\DI\ServiceLocator::getInstance()->addInstanceLazy('database', [
//     'className' => Database::class,
//     'constructorParams' => static function () {
//         return [\Bitrix\Main\DI\ServiceLocator::getInstance()->get('logger')];
//     },
// ]);

// /** @var \loggerInterface $logger */
// $logger = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('logger');
// $logger->log('log');
// /** @var \Database $database */
// $database = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('database');
// $database->save();

// class MyController extends \Bitrix\Main\Engine\Controller
// {
//     public function __construct(?Bitrix\Main\Request $request = null)
//     {
//         parent::__construct($request);
//         Loader::includeModule('sale');
//     }
//     protected function getDefaultPreFilters()
//     {
//         return [];
//     }
//     public function getPrimaryAutoWiredParameter()
//     {
//         return new \Bitrix\Main\Engine\AutoWire\ExactParameter(
//             \Bitrix\Sale\Order::class,
//             'order',
//             function ($className, $id) {
//                 $id = (int)$id;
//                 if ($id > 0) {
//                     $registry = \Bitrix\Sale\Registry::getInstance(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);

//                     /** @var \Bitrix\Sale\Order $className */
//                     $orderClass = $registry->getOrderClassName();

//                     /** @var \Bitrix\Sale\Order $className */
//                     $order = $orderClass::load($id);
//                     if ($order instanceof \Bitrix\Sale\OrderBase) {
//                         return $order;
//                     }
//                 }

//                 $this->addError(new \Bitrix\Main\Error('заказ не найден'));

//                 return null;
//             }
//         );
//     }

//     public function getIdAction(\Bitrix\Sale\Order $order)
//     {
//         return $order->getId();
//     }
// }
// // простая реализация вызова экшена в контроллере, примерно так происходит обработка вашего ajax запроса
// // создаю объект HttpRequest чтобы явно прокинуть параметры
// $request = new Bitrix\Main\HttpRequest( 
//     Bitrix\Main\Context::getCurrent()->getServer(),
//     ['id' => 7], // GET
//     ['id' => 7], // POST
//     [],
//     [],
//     []
// );
// $controller = new \MyController($request);
// $controller->setScope(\Bitrix\Main\Engine\Controller::SCOPE_AJAX);
// // вызов экшена, если заказ с id 7 есть, то вернется 7
// $result = $controller->run('getId', [
//     $request->getPostList(),
//     $request->getQueryList(),
// ]);
// dd($result);


// $catalogSwitcher->set(TypesCatalog::ALL);
// dd($catalogContext->getRootSections());
// $serviceLocator = ServiceLocator::getInstance();
// dd($serviceLocator->get(CatalogSwitcherContract::class));
//$catalogService = new CatalogService($serviceLocator->get(CatalogRepositoryProvider::PRODUCTS_REPOSITORY), $serviceLocator->get(CatalogRepositoryProvider::OFFERS_REPOSITORY));
// dd($serviceLocator->get(CatalogRepositoryProvider::PRODUCTS_REPOSITORY), $serviceLocator->get(CatalogRepositoryProvider::PRODUCTS_REPOSITORY));

// EventManager::getInstance()->unRegisterEventHandler(
//     'main',
//     'OnPageStart',
//     'itb.seo',
//     'Itb\Seo\EventHandlers\Main',
//     'onPageStart'
// );
// EventManager::getInstance()->unRegisterEventHandler(
//     'main',
//     'OnEndBufferContent',
//     'itb.seo',
//     'Itb\Seo\EventHandlers\Main',
//     'onEndBufferContent'
// );
// EventManager::getInstance()->unRegisterEventHandler(
//     'main',
//     'OnBeforeEndBufferContent',
//     'itb.seo',
//     'Itb\Seo\EventHandlers\Main',
//     'onBeforeEndBufferContent'
// );

// ModuleManager::unRegisterModule('itb.seo');

// dd(Event::send([
//     "EVENT_NAME" => "ADD_IDEA",
//     "LID" => "ru",
//     "C_FIELDS" => [
//         "MESSAGE" => "Test message",
//     ],
// ]));
// $sms = new SmsEvent('SMS_EVENT_LOG_NOTIFICATION', [
//     "PHONE_NUMBER" => "+79991234567",
//     "MESSAGE" => "Test SMS message",
//     "NAME" => "123",
//     "ADDITIONAL_TEXT" => "456",
// ]);
// dd($sms->send());
// NotificationTemplateLinkTable::createTable();
// NotificationLinkEventTypeTable::createTable();
//QueryHelper::decompose(NotificationTemplateLinkTable::query());

// $phone = Phone::fromString('89991234567');
// dd($phone->formatInternational());

//dd(service(UserRepositoryContract::class)->getById(2));

// echo service(AuthManager::class)->getAuthorizationUrlOrHtml('telegram')['value'];

// $eventManager = EventManager::getInstance();
// $eventManager->registerEventHandler('iblock', 'OnIBlockPropertyBuildList', 'beeralex.core', IblockLinkType::class, 'getUserTypeDescription');
//$eventManager = EventManager::getInstance();
//$eventManager->registerEventHandler('iblock', 'OnIBlockPropertyBuildList', 'beeralex.core', WebFormLinkType::class, 'GetUserTypeDescription');
// FilesHelper::includeFile('v1.index', [
//     'pathName' => '/',
// ]);
// dd(GlobalResult::$result);
//dd((new HeaderRepository)->getMenu(339));
//dd(service('catalogRepository'));
//dd(service(IblockService::class)->getTableEntityByPropertyId(5));
//
// dd(service(SortingService::class)->getSorting());
// dd((new IblockRepository('catalog'))->getById(16, ['BRAND_REF']));
// dd(service(CatalogService::class)->getProductsWithOffers([16]));
//dd(service(CatalogElementService::class)->getElementData(35));

// dd(service(BasketFacade::class)->getItems());
// $userHelper = service(UserService::class);
// dd($userHelper->generatePassword($userHelper->getDefaultUserGroups()));
// dd(service(DIServiceKey::PRODUCT_REPOSITORY->value)->getProducts([25]));
$ser_content = 'a:2:{s:7:"CONTENT";s:0:"";s:4:"VARS";a:2:{s:8:"arResult";a:2:{s:3:"DTO";a:7:{i:0;O:37:"Beeralex\\Api\\Domain\\Iblock\\SectionDTO":1:{s:9:"'.chr(0).'*'.chr(0).'values";a:5:{s:2:"id";i:1;s:4:"name";s:16:"Мужчинам";s:4:"code";s:5:"shoes";s:3:"url";s:14:"/catalog/shoes";s:10:"pictureSrc";s:55:"/upload/iblock/407/im13un19puvqu6er1jxx7wpxcujfwnc4.jpg";}}i:1;O:37:"Beeralex\\Api\\Domain\\Iblock\\SectionDTO":1:{s:9:"'.chr(0).'*'.chr(0).'values";a:5:{s:2:"id";i:7;s:4:"name";s:12:"Платья";s:4:"code";s:7:"dresses";s:3:"url";s:16:"/catalog/dresses";s:10:"pictureSrc";s:55:"/upload/iblock/eae/vfr76b1d5ifwo7rpk1vr1gyed5qls7tx.jpg";}}i:2;O:37:"Beeralex\\Api\\Domain\\Iblock\\SectionDTO":1:{s:9:"'.chr(0).'*'.chr(0).'values";a:5:{s:2:"id";i:8;s:4:"name";s:16:"Женщинам";s:4:"code";s:5:"pants";s:3:"url";s:14:"/catalog/pants";s:10:"pictureSrc";s:55:"/upload/iblock/960/111nkr6x7e9ehdhlh8bny90vqqrc15un.jpg";}}i:3;O:37:"Beeralex\\Api\\Domain\\Iblock\\SectionDTO":1:{s:9:"'.chr(0).'*'.chr(0).'values";a:5:{s:2:"id";i:9;s:4:"name";s:23:"Нижнее белье";s:4:"code";s:9:"underwear";s:3:"url";s:18:"/catalog/underwear";s:10:"pictureSrc";s:55:"/upload/iblock/150/skfllahhtq8bqehny4m6j0lbraru9tmi.jpg";}}i:4;O:37:"Beeralex\\Api\\Domain\\Iblock\\SectionDTO":1:{s:9:"'.chr(0).'*'.chr(0).'values";a:5:{s:2:"id";i:10;s:4:"name";s:16:"Футболки";s:4:"code";s:8:"t-shirts";s:3:"url";s:17:"/catalog/t-shirts";s:10:"pictureSrc";s:55:"/upload/iblock/b4a/t2nv3fyxrth0wpfs42c22vdkz2o0qc6i.jpg";}}i:5;O:37:"Beeralex\\Api\\Domain\\Iblock\\SectionDTO":1:{s:9:"'.chr(0).'*'.chr(0).'values";a:5:{s:2:"id";i:11;s:4:"name";s:33:"Спортивная Одежда";s:4:"code";s:10:"sportswear";s:3:"url";s:19:"/catalog/sportswear";s:10:"pictureSrc";s:55:"/upload/iblock/909/aon87g5qrf712c0galyn27abc7ei1hjl.jpg";}}i:6;O:37:"Beeralex\\Api\\Domain\\Iblock\\SectionDTO":1:{s:9:"'.chr(0).'*'.chr(0).'values";a:5:{s:2:"id";i:12;s:4:"name";s:20:"Аксессуары";s:4:"code";s:11:"accessories";s:3:"url";s:20:"/catalog/accessories";s:10:"pictureSrc";s:55:"/upload/iblock/72d/5cun4rhu2tcx33g11grbu365049zp9pp.jpg";}}}s:8:"SECTIONS";a:7:{i:0;a:10:{s:2:"ID";s:1:"1";s:4:"NAME";s:16:"Мужчинам";s:4:"CODE";s:5:"shoes";s:7:"PICTURE";s:2:"37";s:16:"SECTION_TEMPLATE";s:46:"#SITE_DIR#/api/v1/catalog/#SECTION_CODE_PATH#/";s:11:"LEFT_MARGIN";s:1:"1";s:12:"RIGHT_MARGIN";s:2:"12";s:11:"DEPTH_LEVEL";s:1:"1";s:11:"PICTURE_SRC";s:55:"/upload/iblock/407/im13un19puvqu6er1jxx7wpxcujfwnc4.jpg";s:3:"URL";s:14:"/catalog/shoes";}i:1;a:10:{s:2:"ID";s:1:"7";s:4:"NAME";s:12:"Платья";s:4:"CODE";s:7:"dresses";s:7:"PICTURE";s:2:"43";s:16:"SECTION_TEMPLATE";s:46:"#SITE_DIR#/api/v1/catalog/#SECTION_CODE_PATH#/";s:11:"LEFT_MARGIN";s:2:"13";s:12:"RIGHT_MARGIN";s:2:"14";s:11:"DEPTH_LEVEL";s:1:"1";s:11:"PICTURE_SRC";s:55:"/upload/iblock/eae/vfr76b1d5ifwo7rpk1vr1gyed5qls7tx.jpg";s:3:"URL";s:16:"/catalog/dresses";}i:2;a:10:{s:2:"ID";s:1:"8";s:4:"NAME";s:16:"Женщинам";s:4:"CODE";s:5:"pants";s:7:"PICTURE";s:2:"44";s:16:"SECTION_TEMPLATE";s:46:"#SITE_DIR#/api/v1/catalog/#SECTION_CODE_PATH#/";s:11:"LEFT_MARGIN";s:2:"15";s:12:"RIGHT_MARGIN";s:2:"16";s:11:"DEPTH_LEVEL";s:1:"1";s:11:"PICTURE_SRC";s:55:"/upload/iblock/960/111nkr6x7e9ehdhlh8bny90vqqrc15un.jpg";s:3:"URL";s:14:"/catalog/pants";}i:3;a:10:{s:2:"ID";s:1:"9";s:4:"NAME";s:23:"Нижнее белье";s:4:"CODE";s:9:"underwear";s:7:"PICTURE";s:2:"45";s:16:"SECTION_TEMPLATE";s:46:"#SITE_DIR#/api/v1/catalog/#SECTION_CODE_PATH#/";s:11:"LEFT_MARGIN";s:2:"17";s:12:"RIGHT_MARGIN";s:2:"18";s:11:"DEPTH_LEVEL";s:1:"1";s:11:"PICTURE_SRC";s:55:"/upload/iblock/150/skfllahhtq8bqehny4m6j0lbraru9tmi.jpg";s:3:"URL";s:18:"/catalog/underwear";}i:4;a:10:{s:2:"ID";s:2:"10";s:4:"NAME";s:16:"Футболки";s:4:"CODE";s:8:"t-shirts";s:7:"PICTURE";s:2:"46";s:16:"SECTION_TEMPLATE";s:46:"#SITE_DIR#/api/v1/catalog/#SECTION_CODE_PATH#/";s:11:"LEFT_MARGIN";s:2:"19";s:12:"RIGHT_MARGIN";s:2:"20";s:11:"DEPTH_LEVEL";s:1:"1";s:11:"PICTURE_SRC";s:55:"/upload/iblock/b4a/t2nv3fyxrth0wpfs42c22vdkz2o0qc6i.jpg";s:3:"URL";s:17:"/catalog/t-shirts";}i:5;a:10:{s:2:"ID";s:2:"11";s:4:"NAME";s:33:"Спортивная Одежда";s:4:"CODE";s:10:"sportswear";s:7:"PICTURE";s:2:"47";s:16:"SECTION_TEMPLATE";s:46:"#SITE_DIR#/api/v1/catalog/#SECTION_CODE_PATH#/";s:11:"LEFT_MARGIN";s:2:"21";s:12:"RIGHT_MARGIN";s:2:"22";s:11:"DEPTH_LEVEL";s:1:"1";s:11:"PICTURE_SRC";s:55:"/upload/iblock/909/aon87g5qrf712c0galyn27abc7ei1hjl.jpg";s:3:"URL";s:19:"/catalog/sportswear";}i:6;a:10:{s:2:"ID";s:2:"12";s:4:"NAME";s:20:"Аксессуары";s:4:"CODE";s:11:"accessories";s:7:"PICTURE";s:2:"48";s:16:"SECTION_TEMPLATE";s:46:"#SITE_DIR#/api/v1/catalog/#SECTION_CODE_PATH#/";s:11:"LEFT_MARGIN";s:2:"23";s:12:"RIGHT_MARGIN";s:2:"30";s:11:"DEPTH_LEVEL";s:1:"1";s:11:"PICTURE_SRC";s:55:"/upload/iblock/72d/5cun4rhu2tcx33g11grbu365049zp9pp.jpg";s:3:"URL";s:20:"/catalog/accessories";}}}s:18:"templateCachedData";a:3:{s:9:"frameMode";N;s:12:"frameModeCtx";s:88:"/local/templates/.default/components/beeralex/catalog.section.list/.default/template.php";s:16:"component_epilog";a:5:{s:10:"epilogFile";s:96:"/local/templates/.default/components/beeralex/catalog.section.list/.default/component_epilog.php";s:12:"templateName";s:8:".default";s:12:"templateFile";s:88:"/local/templates/.default/components/beeralex/catalog.section.list/.default/template.php";s:14:"templateFolder";s:75:"/local/templates/.default/components/beeralex/catalog.section.list/.default";s:12:"templateData";b:0;}}}}';
dd(unserialize($ser_content));
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
