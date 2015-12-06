# infra
Включает расширения Config

* Модель выполнения php файлов в пространстве infra. (index.php?*path/to/file.php)
* Конфиг - Файлы .infra.json infra_config()
* Работа с путям - config.dirs, ~ data, * search, | cahce. infra_theme() infra_srcinfo() infra_dirs()
* Работа с json ответы сервера. infra_ans(), infra_ret(), infra_err()
* Тесты - *infra/tests.php (папка tests в расширении)
* Система прав - разработчик, тестер infra_test() infra_debug() 
* Авторизация админа infra_admin()
* Автоустановка install.php
* Автоподключение config.plugin.require
* Шаблонизатор infra_template()
* События infra_fire() infra_listen()
* Управление кэшем браузера infra_cache_no() infra_cache_yes()
* Кэш по дате изменения файла infra_cache
* Подготовка html выдачи infra_html()
* Работа с последовательностями в строке. Разделитель любой символ. infra_seq_right() infra_seq_short()
* Отправка писем infra_mail_toAdmin() infra_mail_fromAdmin()
* Работа с кэшем в файловой системе или в memcached

После установки через composer функционал доступен через файл ```vendor/infrajs/infra/index.php```. 
Чтобы выполнить тесты нужно открыть в браузере ```vendor/infrajs/infra/index.php?*infra/tests.php```



Расширение [infrajs/imager](https://github.com/infrajs/imager) принимает путь до картинки и ширину, к которой картинку нужно привести.
```
?*imager/imager.php?src=~mypic.jpg&w=100
```

В php и javascript скриптах используется единый формат путей - путь относительно корня сайта вне зависимости от расположения php или js файла. Все функции работающие с файловой системой настроены на работу именно с таким форматом адреса. Путь также может содержать указанные выше специальные символы *, ~, |.

Если расширение работает самостоятельно:
```
vendor/infrajs/imager/imager.php?src=images/mypic.jpg&w=100
```
и в пространстве infra.
```
vendor/infrajs/infra/?*imager/imager.php?src=~mypic.jpg&w=100
?*imager/imager.php?src=*mypic.jpg&w=100
```
Пути внутри библиотеки должны приводится к абсолютному виду. Фактически оба варианта работы отличаются текущей рабочей дирректорией в php.
```php
require_once(__DIR__.'/../../../vendor/autoload.php'); //Правильная запись
```
# index.php с поддержкой infra
```php
<?php
	require_once('vendor/autoload.php');
	infrajs\infra\Infra::init();
```
# infra только что установлен
* ```vendor/infrajs/infra/index.php?~mypic.jpg```
* ```vendor/infrajs/infra/?~mypic.jpg```

  