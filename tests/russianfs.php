<?php

	$ans['title'] = 'Cyrilic support';

	$src = infra_theme('*infra/tests/resources/Тест русского.языка');
	if (!$src) {
		return infra_err($ans, 'Cyrillic unreadable.');
	}

	return infra_ret($ans, 'Cyrillic alright');
