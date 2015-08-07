<?php

    $db = infra_db(true);
    $ans = array(
        'title' => 'Проверка соединения с базой данных',
    );
    $conf=infra_config();
    if (!$conf['infra']['mysql']) {
    	$ans['class'] = 'bg-warning';
    	return infra_ret($ans, 'База данных не используется infra.mysql:false');
    }
    if (!$db) {
        return infra_err($ans, 'Нет соединения с базой данных');
    }

    return infra_ret($ans, 'Есть соединение с базой данных');
