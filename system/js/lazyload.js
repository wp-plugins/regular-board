jQuery(document).ready(function($){
	$("img").wrap(function(){
		if($(this).hasClass("skipLazy")){
		}else{
			$(this).wrap(function(){
				var newimg = '<img src="" data-original="' + $(this).attr('src') + '" width="' + $(this).attr('width') + '" height="' + $(this).attr('height') + '" class="lazy ' + $(this).attr('class') + '">';
				return newimg;
			});
			return '<noscript>';
		}
		});
	$("img.lazy").lazyload(
		{
		data_attribute: "original",
		failure_limit: 9999
	});
});