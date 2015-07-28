infra.debug=function(){
	return infra.loadJSON('*infra/get.php?config')['debug'];
}
infra.test=function(){
	var is=infra.loadJSON('*infra/get.php?config')['test'];
	if(is){
		infra.require('*infra/ext/test.js');
		return infra.test.apply(this,arguments);
	} else {
		return false;
	}
}