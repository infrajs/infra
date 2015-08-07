<?php

if (isset($_GET['config'])) {
	$ans['test'] = infra_test();
	$ans['debug'] = infra_debug();
	$ans['admin'] = infra_admin();

	return infra_ret($ans);
}
