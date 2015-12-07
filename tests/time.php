<?php

$ans['title'] = 'Временная зона по умолчанию';
$msg = date_default_timezone_get();

return Ans::ret($ans, $msg);
