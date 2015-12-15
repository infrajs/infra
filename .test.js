infra.wait(infrajs,'onshow',function(){
	var test=infra.test;
	test.tasks.push([
		'tpl-include',
		function(){
			
			test.check();
		},function(){
			var tpl="{:inc.test}{inc::}-infra/tests/resources/inc.tpl";
			var data={};
			var res=infra.template.parse([tpl], data);
			if(res!='Привет!') {
				return test.err(res);
			}
			test.ok();
		}
	]);
	
	test.exec();
});