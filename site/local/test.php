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

use Beeralex\Api\UrlHelper;
use Beeralex\Catalog\Service\CatalogService;
use Beeralex\Core\Helpers\FilesHelper;
use Beeralex\Core\Helpers\IblockHelper;
use Beeralex\Core\Helpers\QueryHelper as HelpersQueryHelper;
use Beeralex\Core\UserType\IblockLinkType;
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
// FilesHelper::includeFile('menu');
dd(service(CatalogService::class));

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");