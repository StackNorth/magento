jQuery(function ($) {
	D1.ua.str = navigator.userAgent;
	var ua = D1.ua.str;
	if (ua.match(/iPhone/i) || ua.match(/iPad/i) || ua.match(/iPod/i)) {
		D1.iosCheck = true;
	}
	if(D1.iosCheck){
		$('.login-reg a').on('touchend',function(){
			var ahref=$(this).attr('href');			
			location.href=ahref;
		})
	}
})