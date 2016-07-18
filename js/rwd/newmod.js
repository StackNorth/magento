;(function(){
  var Omodify = {
    setBoxClose :function(){
      $("#show_bigshow").on("click",".show_bigbox_close",function(){
         $(this).closest(".show_bigshow").remove();
      });
      $(".nav-item").click(function(){
        $(".show_bigbox_close").trigger("click");
      });
    },
    setShowBox : function(){
      $("#carousel_cooker .carousel_list[data-cooker]").on("click",function(){
        var i=$(this).data("cooker");
          console.log(i);
          $.ajax({
            url:"/index.php/chef/index/big/?id="+i,
            success: function(data){
              $(".frame").append(data);
              Omodify.setBoxClose();
            }
          });
      });
    },
    paymentIni: function () {
        var price = $('.price').html();
        price = parseInt(price.replace(/[&\|\\\*^%$#@\-￥]/g,"").replace(/[a-zA-Z]/g,""));
        if( price != "0"){
        var obj = $('.paybox-waya-b'),
            labelList = obj.find('.paybox-way-btn'),
            methodBox = $('.payment_method') ;
            if (D1.framework.platform === 'pc') {
              labelList.on("click", function () {
                labelList.removeClass('current').filter($(this)).addClass("current");
                methodBox= $(this).closest(".paybox").find('.payment_method');
                methodBox.val($(this).data("payment"));
                // console.log(methodBox)
              });
            }
            else{
              labelList.on(D1.event.touch, function () {
                labelList.removeClass('current').filter($(this)).addClass("current");
                methodBox= $(this).closest(".paybox").find('.payment_method');
                methodBox.val($(this).data("payment"));
                 // console.log(methodBox)
              });
            }}else {
            $('label.paybox-way-btn').css({"color":"#6C6C6C","border-color":"#3C3C3C","cursor":"default"});
        }
    },
    buyLesson: function(){
        var lessonBox= $("#buy-lesson .lesson-box");

          lessonBox.each(function(index, el) {
              buyLessonAct($(this));
          });

          function buyLessonAct(lessonBox){
            var selPeople= lessonBox.find('.select-box'),
                selPeopleNum= selPeople.find('.show-text'),
                selPeopleCon= lessonBox.find('.option-list'),
                bookBtn= lessonBox.find('.lesson-book'),
                num= 0;

                selPeople.on("click", function () {   /*select open*/
                  selPeopleCon.show();
                  return false;
                });
                selPeopleCon.find('li').on("click",function(){ /*select*/
                  num= $(this).data("people");
                  selPeople.data("people",num);
                  selPeopleNum.html(num+"人");
                  $(document).trigger('click');
                });
                selPeopleCon.find('li').eq(0).trigger('click'); /* click first li */

                if (D1.framework.platform === 'pc') {
                    bookBtn.on("click",function(){
                      window.open($(this).data("url")+"?qty="+num);
                    });
                  }
                  else{
                    bookBtn.on(D1.event.touch,function(){
                      window.open($(this).data("url")+"?qty="+num);

                    });
                  }
                $(document).on("click",function(){
                  selPeopleCon.hide();
                });
          }
          
    }
}
$(function(){
  Omodify.setShowBox();
  Omodify.paymentIni();
  Omodify.buyLesson();
})
})();