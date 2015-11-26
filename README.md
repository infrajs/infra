# infra
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

# Работа с путями
Работа с путями это основной функционал расширения ```infrajs/infra```. В конфиге указывается по каким папкам искать скрипты - search (\*), где будет папка данных - data (~), где будет папка кэша - cache (|). По умолчанию .infra.json:
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

Если строка параметров начинается с одного из символов (\*) (~) (|), то она будет интерпретироваться, как путь до файла. 

В папке ```data``` есть файл ```mypic.jpg```. Путь будет  ```?~mypic.jpg``` Если файл находится в расширении infrajs/sample в папке vendor, то путь будет ```index.php?*sample/mypic.jpg```. 

Все другие указанные в get параметры будут переданны указанному файлу. В infrajs все расширения подджеривают указанные сокращения.
```php
$src = infra_theme('~mypic.jpg'); //data/mypic.jpg
```

Расширение [infrajs/imager](https://github.com/infrajs/imager) принимает путь до картинки и ширину, к которой картинку нужно привести.
```
?*imager/imager.php?src=~mypic.jpg&w=100
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
