<?php
	
	ini_set('error_reporting',E_ALL & ~E_NOTICE & ~E_STRICT);
	ini_set('display_errors', 1);

	$ans=array('title'=>'Последовательности infra_seq'); //какие есть аргументы, что она возрващает и вообще как она работает

	if(!function_exists('infra_seq_right'))return infra_err($ans,'seq не подключены!');
	
	$obj='hello';
	$objTest=infra_seq_short($obj); //если infra_seq_short передать строку, то она возвращает эту строку
	if($obj!== $objTest) return infra_err($ans, 'функция infra_seq_short отработала неверно со строкой');
	
	$objFirst='some.test.ok..right...help';
	$objFirst=infra_seq_right($objFirst);
	$objFirst=infra_seq_short($objFirst);

	$obj=infra_seq_right($objFirst);
	$obj=infra_seq_short($obj);
	$obj=infra_seq_right($obj);
	$obj=infra_seq_short($obj);
	$obj=infra_seq_right($obj);
	$obj=infra_seq_short($obj);

	if($obj!==$objFirst) return infra_err($ans, 'функции infra_seq_short и функция infra_seq_right работают неправильно, 
												так как они должны конвертировать туда и обратно строку и массив и возвращать то же самое значение');
												
	$obj='....some';
	$objTest=infra_seq_right($obj);
	if($objTest!==array('','','','some')) return infra_err($ans, 'функция infra_seq_right отработала неверно так как она должна при точках в строке (если точки идут первыми)
	возвращать пустые элементы, если сначала не пустые элементы, то если больше одной точки, то должна удалять элементы идущие до точек');
	

	$obj='';
	$right=infra_seq_right($obj);
	if($right!==array()) return infra_err($ans, 'Необработалась пустая строка');
	

	$obj=array();
	$short=infra_seq_short($obj);
	if($short!=='') return infra_err($ans, 'Необработался пустой массив');


	$right1='some.test..ok.help'; 
	$right2='...some'; 
	$obj1=infra_seq_right($right1); //должно остаться ['some','ok','help']
	$obj2=infra_seq_right($right2); //должно остаться ...some ['','','some']
	$objTest=array_merge($obj1,$obj2);
	$objTest=infra_seq_right($objTest); //должно остаться ['some','some']
	$objTest=infra_seq_short($objTest); //должно остаться some.some
	if($objTest!=='some.some') return infra_err($ans, 'функция infra_seq_short выдала неверный результат');
	
	
	$obj='some·test·ok.test.ok';
	$objTest=infra_seq_short($obj); //строка останется точно такой же какой и была
	if($objTest!==$obj) return infra_err($ans, 'функция infra_seq_short не вернула специальный элемент');
	$objTest=infra_seq_right($objTest); //some·test·ok станет одним элементом, равным some.test.ok
	if($objTest!==array('some.test.ok','test','ok')) return infra_err($ans, 'функция infra_seq_array не вернула правильную строку в элементе [0]');
	$objTest=infra_seq_short($objTest); //специальные знаки возвращаются обратно
	if($objTest!==$obj) return infra_err($ans, 'функция infra_seq_short не вернула обратно специальный элемент, когда в элементе массива были точки');
	
	$obj=array('some·test','ok·help');
	$objTest=infra_seq_short($obj); //вернет строку some·test.ok·help
	if($objTest!=='some·test.ok·help') return infra_err($ans, 'функция infra_seq_short не вернула правильную строку');
	$objTest=infra_seq_right($objTest); //вернет обратно, наш первоначальный массив, но уже вместо спец массивов будут точки
	//$objTest=infra_seq_short($objTest);
	if($objTest!==array('some.test','ok.help')) return infra_err($ans,'функция infra_seq_right не отрабатывает');
		
	$o=array(
		'author'=>array(
			"country2"=>array(
				"lang"=>"en", "ang"=>"ru"
			)
		),
		'list'=>array(
			array(
				"name"=>"Сущность важная"
			),
			array(
				"name"=>"Сущность более важная"
			)
		)
	);
	$obj=array(
		'author'=>array(
			"country2"=>array(
				"lang"=>"en", "ang"=>"ru"
			),
			"country3"=>array(
				"lang"=>"ru", "ang"=>"com"
			)
		),
		'list'=>array(
			array(
				"name"=>"Сущность важная"
			),
			array(
				"name"=>"Сущность более важная"
			)
		)
	);

	//Получение значений с разными вариантами пути до одной и той же переменной
	//..lang, authore.....author.country2.lang
	
	
	//set если устанавливается null элемент должен удалиться
	$right=array('author','country3');
	$r=null;
	infra_seq_set($obj,$right,$r);
	if(!is_null($obj['author']['country3'])) return infra_err($ans, 'Удаление элемента не произошло');
	
	//Если устанавливается путь равный пустому массиву, то подменяется весь исходных массив
	$o=$obj;
	$right=array();
	$r=false;
	$o=infra_seq_set($o,$right,$r);
	if($o!==false) return infra_err($ans, 'obj не заменился на пустой массив');
	
	//Проверить что устанавливается значение
	$right=array('author','country2');
	$r=array();
	infra_seq_set($obj,$right,$r);
	if($obj['author']['country2']!==array()) return infra_err($ans, 'при передаче третим параметром пустого массива в объекте не сделался массив пустым');
	
	// Установить значение когда в иерархии проскакивает звено не массив.
	$o=$obj;
	$right=array('list','0','name','test');
	$r=123;
	infra_seq_set($o, $right, $r);
	if($o['list']['0']['name']['test']!==123) return infra_err($ans, 'не удалось установить значение в немассив');

	
	// Проанализировать работу когда индексному массиву присваивается новое свойство
	// infra_seq_set($obj,array('list','asdf'),array('test'));
	$obj1=array('test','help','ok','print');
	$o=array('test','help','ok','print','test'=>array('help'=>array('new','fax')));
	$new=array('new','fax');
	infra_seq_set($obj1,array('test','help'),$new);
	if($obj1!==$o) return infra_err($ans, 'функция infra_seq_set отработала неверно, она должна добавить в массив ассоциативные элементы');

	
	// Сущность важная не должна пропасть она останется
	$right=array('list','0','name','test','ok');
	$res=infra_seq_get($obj, $right);
	if($res!==NULL) return infra_err($ans, 'при передаче несуществующего элемента должно вернуться NULL');
	if(!is_string($obj['list'][0]['name']))return infra_err($ans,'Что-то случилось со строкой после get');


	//Протестировать создание длинной цепочки свойств, когда вся цепочка создаётся
	$obj=array();
	$right=array('qwer','asdf','zxcv','test','some');
	//$obj=array('test',array('author'=>'Dostoevsky','country'=>'Russia','lang'=>'ru'),array('author'=>'Obama','country'=>'USA','lang'=>'en'));
	$val='hi';		
	infra_seq_set($obj,$right,$val);
	if(!$obj['qwer']['asdf']['zxcv']['test']['some'])return infra_err($ans,'Не создалась длинная цепочка свойств');
	return infra_ret($ans,'Всё ок');