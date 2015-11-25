# infra
* Модель выполнения php файлов в пространстве infra. (infra/index.php?*path/to/file.php)
* Конфиг - Файлы .infra.json infra_config()
* Работа с путям - config.dirs, ~ data, * search, | cahce. infra_theme() infra_srcinfo() infra_dirs()
* Работа с json ответы сервера. infra_ans(), infra_ret(), infra_err()
* Тесты - *infra/tests.php (папка tests в расширении)
* Система прав - разработчик, тестер infra_test() infra_debug() 
* Авторизация админа infra_admin()
* Автоустановка install.php
* Автоподключение config.plugin.require
* infra_once()
* Шаблонизатор infra_template()
* События infra_fire() infra_listen()
* Кэш по дате изменения файла infra_cache
* Подготовка html выдачи infra_html()
* Работа с последовательностями в строке. Разделитель любой символ. infra_seq_right() infra_seq_short()
* PDO Соединение с базой данных infra_db();
* Отправка писем infra_mail_toAdmin() infra_mail_fromAdmin()
* Работа с кэшем в файловой системе или в memcached

После установки через composer функционал доступен через файл ```vendor/infrajs/infra/index.php```. 

Чтобы выполнить тесты нужно открыть в браузере ```vendor/infrajs/infra/index.php?*infra/tests.php```
