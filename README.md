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

# Работа с путями
Работа с путями это основной функционал расширения ```infrajs/infra```. В конфиге указывается по каким папкам искать скрипты - search (\*), где будет папка данных - data (~), где будет папка кэша - cache (|). По умолчанию:
```json
{
  "dirs":{
		"cache" : "cache/",
		"data" : "data/",
		"search" : [
			"data/",
			"./",
			"vendor/infrajs/"
		]
  }
}
``` 
```php
$dirs=infra_dirs();
```

# Если в корне проекта есть index.php с поддержкой infra
Пример кода для index.php, который должен быть в корне проекта
```php
<?php
	require_once('vendor/autoload.php');
	infrajs\infra\Infra::init();
```
В этом случае если строка параметров будет начинаться с символа (\*) (~) (|), то она будет интерпретироваться, как путь до файла. 

В папке data есть файл ```mypic.jpg```. Путь до указанного файла может быть  ```index.php?~mypic.jpg``` ```index.php?*mypic.jpg``` или без указания index.php ```?~mypic.jpg``` ```?*mypic.jpg``` 

Все указанные get параметры в таком пути будут переданны указанному файлами, а сам index.php эти параметры проигнорирует. В infrajs все расширения подджеривают указанные сокращения. ```?*imager/imager.php?src=~mypic.jpg&w=100```
В примере расширение imager принимает путь до картинки и ширину к которой картинку нужно привести.

# infra только что установлен
Для только что установленного infra будет работать путь
* ```vendor/infrajs/infra/index.php?*~mypic.jpg```
* ```vendor/infrajs/infra/?*~mypic.jpg```
