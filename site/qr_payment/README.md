Компонент генерирует qr код для пк версии и список банков с ссылками в приложение для телефонов.

Параметр 'ORDER_ID' обязательный.

Так же передаются параметры необходимые для проверки оплаты. "CHECK_PAYMENT" => 'Y', "REDIRECT_URL" => '/account/orders/#ORDER_ID#'

Работает с юкассой

Так же можно генерировать qr для sberPay и настроить push уведомления, для этого нужно создать две платежной системы SberPay QR и SberPay по смс. Заказ создавать с этими двумя платежками. SberPay qr возвращает ссылку вида - sberpay://invoicing/v2?bankInvoiceId=6f3aa955c90946d9a899d75c1867d5d3&operationType=Web2App

Для генерации qr используется библиотека - Bacon/BaconQrCode Так же необходима библиотека - DASPRiD/Enum

Для установки через composer: composer require bacon/bacon-qr-code; composer require dasprid/enum

В шаблоне используется vue.js. Тестировал на версии 2.6.4.

todo - Освежить бы код, шаблон вынести в директорию сборщика и т.д. порефакторить