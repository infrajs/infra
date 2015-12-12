<?php
namespace infrajs\infra;
class Fix
{
	public function __construct($opt, $ret = null)
	{
		if (is_string($opt)) {
			if ($opt == 'del') {
				$opt = array(
					'del' => true,
					'ret' => $ret,
				);
			}
		}
		$this->opt = $opt;
	}
}