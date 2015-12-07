<?php

	$ans=array('title'=>'test function infra_forr, what did have arguments, what she return and how she work'); //какие есть аргументы, что она возрващает и вообще как она работает

	//Проверить что функция существует
	$funtrue='infra_forr';
	$res=function_exists($funtrue);
	if(!$res)return Ans::err($ans,'Error, no function '.$funtrue);
	//return Ans::ret($ans,'Ok, function '.$funtrue.' true');

	
	//Тестируем утверждение что функция infra_forr запускает функцию переданную аргументов столько раз сколько элементов в массиве	
	$ar=array(1,2);
	$count=0;
	Each::forr($ar,function() use(&$count){
		$count++;
	});
	if($count!=2)return Ans::err($ans,'infra_forr return errors'); //infra_forr выполнилась некорректное число раз
	//return Ans::err($ans,'infra_forr return OK');

	//callback не запускается для Null элементов
	$ar=array(1,null,3,null,5);
	$count=0;
	Each::forr($ar,function() use(&$count){
		$count++;
	});
	if($count!=3)return Ans::err($ans,'infra_forr выполнилась некорректное число раз c null '.$count);

	
	//callback и false
	$ar=array(1,3,3,4,null,10,false,null,0);
	$count=0;
	$result=0;
	Each::forr($ar,function($el) use(&$result, &$count){
		if($el===false)$result=true; //функция запускается, если встречается элемент в массиве равный false (true, 0 и т.д.)
		$count++;
	});
	if(!$result)return Ans::err($ans,'infra_forr not executed with false ');

	//Callback не запустится ни разу если будет передан пустой массив
	$ar2=array();
	$count2=0;
	Each::forr($ar2,function() use(&$count2){
		$count2++;
	});
	if($count2!=0)return Ans::err($ans,'infra_forr работает некорректно');

	//Как работет return внутри callback функции, когда return разные значение возвращает
	$ar4=array(1,2,3,4,5,6);
	$count=0;
	$res=Each::forr($ar4,function($el) use(&$count){
		$count++;
		return 'finish arg'; 
	});
	if($count!=1)return Ans::err($ans,'return str не обрывает пробежку');
	if($res!='finish arg')return Ans::err($ans,'значение return не попало в результат infra_forr');

	//Проверить специальный return благодаря которому удаляется элемент из массива return new infra_Fix('del');
	$ar3=array(1,2,3,4,5,6);
	$len=sizeof($ar3);
	$count3=0;
	$res=Each::forr($ar3,function($el,$i) use(&$count3){
		$count3++;
		if($el==4)return new infra_Fix('del'); //пробовал передавать не new infra_Fix, а просто infra_Fix массив удалялся полностью, ну или просто функция неправильно срабатывала.
	});
	if($res!==null)return Ans::err($ans,'Странный результат');
	if($count3!=$len)return Ans::err($ans,'Неожиданное количество выполненией');
	if($ar3[3]==4)return Ans::err($ans, 'Не работает удаление');
	
	//Протестировать back
	$ar=array(1,2,3,4,5,6);
	$count=0;
	$res=Each::forr($ar,function($el) use(&$count){
		return new infra_Fix('del',false);
	},true);
	if($ar[5]==6)return Ans::err($ans, 'Не работает back');
	
	$ar=array(1,2,3,4,5,6);
	$res=Each::forr($ar,function($el){
		return $el;
	},true);
	if($res!=6)return Ans::err($ans, 'Не работает back');
	

	return Ans::ret($ans,'Всё ок');