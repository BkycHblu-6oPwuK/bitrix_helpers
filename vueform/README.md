# Шаблон form.result.new на vue

Сбор данных для vue делает класс ``` Itb\Form\FormNewBuilder ```. В component_epilog проверка на ajax запрос и id формы для возврата ответа json.

шаблон div с классом и json передаются в data атрибуте.

``` local/js/vite/src/app/formNew/index.js ``` - создает vue приложения для всех блоков с классом vue-form из template.php и данными из data атрибута

``` local/js/vite/src/store/form/formNew.js ``` - функция createAppStore создает объект хранилища под каждое vue приложение.

пример вывода формы и отправки реализован в ``` local/js/vite/src/app/formNew/App.vue ```

Флаг ``` successAdded ``` в DTO уведомляет об успешном добавлении.

При вызове компонента битрикс параметр ``` USE_EXTENDED_ERRORS ``` лучше ставить на Y, чтобы сообщения об ошибках возвращались для каждого поля.