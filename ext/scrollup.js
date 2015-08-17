infra.scrollUpt;
infra.scroll_bias=0;
infra.scrollUp=function(){

	var top = Math.max(document.body.scrollTop,document.documentElement.scrollTop);

	var delta=0;
	if(infrajs.scroll){ //depricated
		infra.scroll=infrajs.scroll;
		/*if(typeof(infrajs.scroll)=='number'){
			delta=infrajs.scroll;
		}else if(typeof(infrajs.scroll)=='string'){
			delta=$(infrajs.scroll).offset().top;
		}*/
	}
	if(infra.scroll){

		if(typeof(infra.scroll)=='number'){
			delta=infra.scroll;
		}else if(typeof(infra.scroll)=='string'){

			delta=$(infra.scroll).offset().top;
		}
		if(infra.scroll_bias) {
			if(typeof(infra.scroll_bias)=='number'){
				delta+=infra.scroll_bias;
			}else if(typeof(infra.scroll_bias)=='string'){
				
				delta+=$(infra.scroll_bias).height().top+$(infra.scroll_bias).height();
			}
		}
	}


	top=Math.floor(Math.sqrt(top));
	if(top > 2) {
		//window.scrollBy(0,-100);
		window.scrollTo(0,top);
		infra.scrollUpt = setTimeout(infra.scrollUp,30);
	} else {
		window.scrollTo(0,0);
		clearTimeout(infra.scrollUpt);
	}
}
