(function () {
  var account = {
    lvIni: function () {
      var block = $('.lv-block'),
          bar = block.find('.p-bar'),
          tip = block.find('.tip'),
          process = block.find('.process'),
          t = bar.data('total'),
          c = bar.data('current'),
          val = c / t * 100;
      tip.css('left', val + '%');
      process.css('width', val + '%');
	  $('.accountlevel > span').each(function(){
		var leveldata=$(this).data('level'),
		l = leveldata / t * 100;		
		if(l==100){
			$(this).css('right', 0);
		}else{
			$(this).css('left', l + '%');
		}
	  })
    },
    starIni: function () {
      var star = $('.cmStar');
      star.each(function (i, o) {
        new D1.StarRating($(o));
      });
    },
    ini: function () {
      this.lvIni();
      this.starIni();
    }
  };
  
  D1.account = account;

  $(function () {
    account.ini();
  });
}());