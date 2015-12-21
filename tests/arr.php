<?php
	namespace infrajs\infra;

	use infrajs\ans\Ans;
	use infrajs\each\Each;
	use infrajs\config\Config;

	if (!is_file('vendor/autoload.php')) {
		chdir('../../../../');
		require_once('vendor/autoload.php');
	}

	$ans=array('title'=>'Тестируем функции isAssoc и isEqual'); //какие есть аргументы, что она возрващает и вообще как она работает

	
	
	//Проверить что функция infra_isAssoc возвращает ассоциативный массив
	$arr=array(1,2,3,4,5,6); // задаем индексный массив
	$arr=Each::isAssoc($arr);
	if($arr!==false)return Ans::err($ans,'Функция infra_isAssoc вернула неверный массив, задан индексный массив, а она выдает что это ассоциативный');
	
	$arr=array('a'=>1,'b'=>2,'c'=>3); // задаем ассоциативный массив
	$arr=Each::isAssoc($arr);
	if($arr!==true)return Ans::err($ans,'Функция infra_isAssoc вернула неверный результат, переданный массив является ассоциативным');
	
	$arr=array(1,2,3,4,5,6); // задаем индексный массив
	$arr[]=9; // добавляем элемент к индексному массиву
	$arr[30]=15; // при добавлении random-ного индекса массив автоматически становится не индексным, хотя в принципе вроде как он индексный.
	$arr=Each::isAssoc($arr);

	//if($arr!=false)return Ans::err($ans,'функция infra_isAssoc вернула неверный результат, переданный массив является индексным');
	
	
	
	//Проверить, являются ли переданые переменные ссылкой друг на друга
	$a=15; // создаем переменную a
	$b=$a; // записываем копию переменной a в переменную b
	$equal=Each::isEqual($a,$b);
	if($equal!==false)return Ans::err($ans,'Функция infra_isEqual работает неверно $b не является ссылкой на $a');
	$b=&$a; // делаем b ссылкой на a
	$equal=Each::isEqual($a,$b);
	if($equal!==true)return Ans::err($ans,'Функция infra_isEqual работает неверно $b является ссылкой на $a');
	
	$a=array(1,2,4,7); // интересно получается, так как раннее мы $b=&$a то уже здесь, даже если присваивать массивы этим переменным, то всё равно при изменении $b всё записывается в $a
	$b=array(1,2,4,11);
	$equal=Each::isEqual($a,$b);
	if($equal!==true)return Ans::err($ans,'Функция infra_isEqual работает неверно $b является ссылкой на $a');
	
	$b=array(1,2,4,15);
	$c=array(1,2,4,11);
	//print_r($a);
	$equal=Each::isEqual($b,$c);
	if($equal!==false)return Ans::err($ans,'Функция infra_isEqual работает не правильно, массив $с не является ссылкой на $b');
	
	return Ans::ret($ans,'Всё ок');