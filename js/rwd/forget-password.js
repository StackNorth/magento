;(function () {
  var verification={
    get: function (formBox) {
      //this.form = $('.register').find('form');
      this.form = formBox;
      this.phoneIpt = this.form.find('#phone');
      this.codeBtn = this.form.find('.send-code');
      this.MsgBox = this.form.siblings(".messages");
    },
    bind: function () {
      var self = this;
      var fullMsg = $("<div class='messages'><p class='error-msg'><span>请输入正确手机号码</span></p></div>");
          halfMsg = $("<p class='error-msg'><span>请输入正确手机号码</span></p>");
      this.codeBtn.on('click', function () {
        var activeBtn= $(this).closest("form").siblings(".messages");
        activeBtn.remove();
        if($(this).hasClass('disable')){
          return;
        }
        else if (!$(this).hasClass('disable') && /^[0-9]{11}$/g.test(self.phoneIpt.val()) ) {
          self.sendCode(self.phoneIpt.val(),activeBtn);
        }
        else{
          if(self.MsgBox.length>0){
            self.MsgBox.append(halfMsg);
          }
          else{
            self.form.before(fullMsg);
          }
        }

      });
    },
    sendCode: function (phone,acBox) {
      var self = this,
          fullMsg="";

      acBox.remove();
        var geeC = $('input[name="geetest_challenge"]');
        var geeV = $('input[name="geetest_validate"]');
        var geeS = $('input[name="geetest_seccode"]');
        var geeCval = geeC.length ? geeC.val() : '';
        var geeVval = geeV.length ? geeV.val() : '';
        var geeSval = geeS.length ? geeS.val() : '';
      $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/customer/account/verify',
        data: {
          phone: phone,
          used: 1,
            geetest_challenge : geeCval,
            geetest_validate : geeVval,
            geetest_seccode : geeSval
        },
        success: function (d) {
          self.MsgBox.remove();
          if (d.status) {
            //发送验证码
            self.cd(60);
            fullMsg = $("<div class='messages'><p class='error-msg'><span>手机验证码已发送，请查收!</span></p></div>");
          }
          else{
            fullMsg = $("<div class='messages'><p class='error-msg'><span>"+d.msg+"</span></p></div>");
          }
          self.form.before(fullMsg);
        }
      });
    },
    cd: function (n) {
      var self=this;
      n = n - 1;
      if (n <= 0) {
        self.codeBtn.removeClass('disable').html("获取验证码");
      } else {
        setTimeout(function () {
          self.cd(n);
        }, 1000);
        // modify 2014-10-15 by osmond
        self.codeBtn.addClass('disable').html(n+ "s后再次获取验证");

      }
    },
    ini: function (formBox) {
      this.get(formBox);
      this.bind();
    }
  };
  $(function () {
    verification.ini($('.forget-password').find('form.step1'));
  });
}());