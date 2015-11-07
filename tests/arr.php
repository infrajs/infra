<?php
	
	
	$ans=array('title'=>'Тестируем функции infra_isAssoc и infra_isEqual'); //какие есть аргументы, что она возрващает и вообще как она работает

	//Проверить что функция существует
	$funtrue='infra_isAssoc';
	$res=function_exists($funtrue);
	if(!$res)return infra_err($ans,'Error, no function '.$funtrue);
	//return infra_ret($ans,'Ok, function '.$funtrue.' true');
	
	//Проверить что функция infra_isAssoc возвращает ассоциативный массив
	$arr=array(1,2,3,4,5,6); // задаем индексный массив
	$arr=infra_isAssoc($arr);
	if($arr!==false)return infra_err($ans,'Функция infra_isAssoc вернула неверный массив, задан индексный массив, а она выдает что это ассоциативный');
	
	$arr=array('a'=>1,'b'=>2,'c'=>3); // задаем ассоциативный массив
	$arr=infra_isAssoc($arr);
	if($arr!==true)return infra_err($ans,'Функция infra_isAssoc вернула неверный результат, переданный массив является ассоциативным');
	
	$arr=array(1,2,3,4,5,6); // задаем индексный массив
	$arr[]=9; // добавляем элемент к индексному массиву
	$arr[30]=15; // при добавлении random-ного индекса массив автоматически становится не индексным, хотя в принципе вроде как он индексный.
	$arr=infra_isAssoc($arr);

	//if($arr!=false)return infra_err($ans,'функция infra_isAssoc вернула неверный результат, переданный массив является индексным');
	
	//Проверить что функция существует
	$funtrue='infra_isEqual';
	$res=function_exists($funtrue);
	if(!$res)return infra_err($ans,'Error, no function '.$funtrue);
	//return infra_ret($ans,'Ok, function '.$funtrue.' true');
	
	//Проверить, являются ли переданые переменные ссылкой друг на друга
	$a=15; // создаем переменную a
	$b=$a; // записываем копию переменной a в переменную b
	$equal=infra_isEqual($a,$b);
	if($equal!==false)return infra_err($ans,'Функция infra_isEqual работает неверно $b не является ссылкой на $a');
	$b=&$a; // делаем b ссылкой на a
	$equal=infra_isEqual($a,$b);
	if($equal!==true)return infra_err($ans,'Функция infra_isEqual работает неверно $b является ссылкой на $a');
	
	$a=array(1,2,4,7); // интересно получается, так как раннее мы $b=&$a то уже здесь, даже если присваивать массивы этим переменным, то всё равно при изменении $b всё записывается в $a
	$b=array(1,2,4,11);
	$equal=infra_isEqual($a,$b);
	if($equal!==true)return infra_err($ans,'Функция infra_isEqual работает неверно $b является ссылкой на $a');
	
	$b=array(1,2,4,15);
	$c=array(1,2,4,11);
	//print_r($a);
	$equal=infra_isEqual($b,$c);
	if($equal!==false)return infra_err($ans,'Функция infra_isEqual работает не правильно, массив $с не является ссылкой на $b');
	
	return infra_ret($ans,'Всё ок');