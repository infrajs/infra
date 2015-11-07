<?php
chdir('../../../');
require_once('vendor/autoload.php');

require_once('infra.php');
$conf=infra_config();

itlife\infrajs\Infrajs::controller($conf['infra']['controller']);
