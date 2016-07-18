(function () {
  var buyCredits = {
    step1: {
      pointIni: function () {
        var obj = $('.point-select'),
            li = obj.find('li:not(".text")'),
            activeLi =isNaN(obj.find('li.current').html()) ? obj.find('li.current').find("input").val() : obj.find('li.current').find("input").html(),//当前选中 数量
            singlePrice = obj.find("ol").data("price"), //单价
            price = obj.find('.price'),//总价
            otherIpt = obj.find('input.other-qty');//其他选中 数量
           // ipt = obj.find('input.current-selected'),//其他数量选中(隐藏)

        li.on("click", function () {//默认数量
          li.removeClass('current');
          var s = $(this),
              num = s.find('input')[0] ? s.find('input').val() : s.text();

          s.addClass('current');
          num = /^[0-9]+$/g.test(num) ? parseInt(num) : 0;
          if(num===0){
            otherIpt.val("");
          }
          else{
            otherIpt.val(num);
          }
          price.html(num * singlePrice + ' RMB');
        });
        otherIpt.on('keyup', function () {//其他数量
          var self = $(this),
              num = parseInt(self.val()),
              p = parseFloat(self.parents('ol').data('price'));
          if(isNaN(num)){
             self.val(0);
             price.html(0+ ' RMB');
          }
          else{
            if(num===0){
              otherIpt.val("");
            }
            else{
              otherIpt.val(num);
            }
            price.html(num * singlePrice + ' RMB');
          }
        });
        //初始化总价
        price.html(0 + ' RMB');
      },
      paymentIni: function () {
        var obj = $('.select-payment'),
            labelList = obj.find('label'),
            methodBox = obj.find('#payment_method') ;
            if (D1.framework.platform === 'pc') {
              labelList.on("click", function () {
                labelList.removeClass('current').filter($(this)).addClass("current");
                methodBox.val($(this).data("payment"));
              });
            }
            else{
              labelList.on(D1.event.touch, function () {
                labelList.removeClass('current').filter($(this)).addClass("current");
                methodBox.val($(this).data("payment"));
              });
            }
      },
      ini: function () {
        if ($('.buy-credits.step1')[0]) {
          this.pointIni();
          this.paymentIni();
        }
      }
    },
    ini: function () {
      this.step1.ini();
    }
  };
  D1.buyCredits = buyCredits;
  $(function () {
    buyCredits.ini();
  });
}());