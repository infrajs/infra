# infra
* Модель выполнения php файлов в пространстве infra. (infra/index.php?*path/to/file.php)
* Конфиг - Файлы .infra.json infra_config()
* Работа с путям - config.dirs, ~ data, * search, | cahce. infra_theme infra_srcinfo
* Тесты - *infra/tests.php (папка tests в расширении)
* Система прав - админ, разработчик, тестер infra_test() infra_debug() infra_admin()
* Автоустановка install.php
* Автоподключение config.plugin.require
* infra_once()

После установки через composer функционал доступен через файл ```vendor/infrajs/infra/index.php```. 

Чтобы выполнить тесты нужно открыть в браузере ```vendor/infrajs/infra/index.php?*infra/tests.php```
