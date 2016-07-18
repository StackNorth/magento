(function () {
  var page = {
    section0: {
      get: function () {
        this.section = $('.home-page');
        this.scroller = this.section.find('.kv-list');
      },
      setCarousel: function () {
        if (D1.framework.platform === 'pc') {
          var h =$(window).height()-$(".header").outerHeight(); //$(window).width() * 711 / 1400; // modify 2014-10-16 by osmond

          this.scroller.find('li').width(this.scroller.width()).height(h);
          this.scroller.height(h);
          this.carousel = new D1.Carousel(this.scroller, {
            intervalTime: 5000,
            autoPlay: true
          });

        } else {
          this.carousel = new D1.Carousel(this.scroller);

          var s = $('.section0').data('scroll');
          s.on('scrollStart', function () {
            if (Math.abs(this.distX) > Math.abs(this.distY)) {
              if (this.distX > 0) {//right
                D1.page.section0.carousel.prev();
              } else {
                D1.page.section0.carousel.next();
              }
            }
          });
        }
      },
      bind: function () {
        var that = this;

        $(window).on('resize', function () {
          if (that.t) {
            clearTimeout(that.t);
          }
          that.t = setTimeout(function () {
            that.scroller.find('li').width($(window).width());
            that.carousel.refresh({
              wh: {
                w: $(window).width(),
                h: that.scroller.find('img').eq(0).height()
              }
            });
          }, 40);
        });
      },
      setTween: function () {
        var controller = $.superscrollorama();
        //controller.addTween('.section1', TweenMax.fromTo( $('.login-reg'), .5, {css:{top: -200, immediateRender:true}, {css:{top: -600}}));
        //controller.addTween('.kv-title', TweenMax.from( $('.kv-title'), .5, {css:{top: '-200px', right:'1000px'}, ease:Elastic.easeOut}));

        var titleTop = D1.framework.platform === 'pc' ? 180 : 145;
        var btnTop = D1.framework.platform === 'pc' ? 245 : 184;
        var learnTop = 60+"%";

        var arr = [
          // modify 2014-10-16 by osmond
          TweenMax.fromTo($('.login-reg'), 0.6,
            {css: {top: btnTop}, immediateRender: true},
            {css: {top: btnTop + 100}}),
          TweenMax.fromTo($('.kv-title'), 1,
            {css: {top: titleTop}, immediateRender: true},
            {css: {top: titleTop + 100}})
        ];

        if (D1.framework.platform === 'pc') {
          arr.push(
            // modify 2014-10-16 by osmond
            TweenMax.fromTo($('.learn-more'), 1.5,
              {css: {top: learnTop}, immediateRender: false},
              {css: {top: learnTop + 100}})
          );
        }

        controller.addTween(
          '.section1',
          (new TimelineLite()).append(arr),
          1000, // scroll duration of tween
          -1 * (this.section.height() - (D1.framework.platform === 'pc' ? 400 : 400)) // offset
        );

      },
      ini: function () {
        this.get();
        this.setCarousel();
        this.setTween();
        this.bind();
      }
    },
    section1: {
      get: function () {
        this.section = $('.section1');
        this.items = this.section.find('li');
      },
      bind: function () {
        var that = this;
        this.items.on(D1.event.end, function () {
          if (D1.framework.platform === 'pc') {
            //open pop
            //D1.framework.popup.open('.the-academy-popup');
            //that.carouselTo($(this).data('idx'));
          } else {
            if (D1.framework.isMove) {
              return
            }
            $(this).toggleClass('on');
            D1.framework.to(D1.framework.currentSection);
          }
        });
      },
      carouselTo: function (idx) {
        if (D1.page.section1Popup.carousel) {
          D1.page.section1Popup.carousel.moveTo(idx);
        }
      },
      ini: function () {
        this.get();
        this.bind();
      }
    },
    section1Popup: {
      get: function () {
        this.section = $('.the-academy-popup');
        this.scroller = this.section.find('.scroller');
      },
      setCarousel: function () {
        var offset = (Math.min($(window).width(), 1400) - 658) / 2;
        this.scroller.width(Math.min($(window).width(), 1400));

        this.carousel = new D1.UnlimitScroller(this.scroller, {
          offset: offset
        });
        this.carousel.controlPrev.width(offset);
        this.carousel.controlNext.width(offset);
      },
      bind: function () {
        var that = this;
        if (D1.framework.platform === 'pc') {

          D1.wheel(this.scroller[0], function (e, delta) {
            var step = 0,
              t = that.scroller.position().top;

            that.scroller.css('height', 'auto');
            if (delta > 0) {
              step = 50;
            } else {
              step = -50;
            }
            if ((delta > 0 && t + step > 0) || (delta < 0 && (t + that.scroller.height() < that.scroller.parent().height()))) {
              return;
            }

            that.scroller.stop().animate({
              top: t + step
            }, 300);
          }, false);
        }
      },
      ini: function () {
        this.get();
        this.setCarousel();
        this.bind();
      }
    },
    section2: {
      get: function () {
        this.section = $('.section2');
        this.items = this.section.find('li');
      },
      bind: function () {
        var that = this;
        this.items.on(D1.event.end, function () {
          var idx = $(this).data('idx');
          if (D1.framework.platform === 'pc') {
            //open pop
            if (typeof idx !== 'undefined' && idx <= 2) {
              D1.framework.popup.open('.cooking-classes-popup');
              that.carouselTo($(this).data('idx'));
            }
          } else {
            if (D1.framework.isMove) {
              return;
            }
            if (typeof idx !== 'undefined') {
              $(this).toggleClass('on');
              D1.framework.to(D1.framework.currentSection);
            }
          }
        });

      },
      carouselTo: function (idx) {
        if (D1.page.section2Popup.carousel) {
          D1.page.section2Popup.carousel.moveTo(idx);
        }
      },
      ini: function () {
        this.get();
        this.bind();
      }
    },
    section2Popup: {
      get: function () {
        this.section = $('.cooking-classes-popup');
        this.scroller = this.section.find('.scroller');
      },
      setCarousel: function () {
        var offset = (Math.min($(window).width(), 1400) - 658) / 2;
        this.scroller.width(Math.min($(window).width(), 1400));
        this.carousel = new D1.UnlimitScroller(this.scroller, {
          offset: offset
        });
        this.carousel.controlPrev.width(offset);
        this.carousel.controlNext.width(offset);
      },
      bind: function () {
        var that = this;
        if (D1.framework.platform === 'pc') {
          D1.wheel(this.scroller[0], function (e, delta) {
            var step = 0,
              t = that.scroller.position().top;
            that.scroller.css('height', 'auto');
            if (delta > 0) {
              step = 50;
            } else {
              step = -50;
            }
            if ((delta > 0 && t + step > 0) || (delta < 0 && (t + that.scroller.height() < that.scroller.parent().height()))) {
              return;
            }

            that.scroller.stop().animate({
              top: t + step
            }, 300);
          }, false);
        }
      },
      ini: function () {
        this.get();
        this.setCarousel();
        this.bind();
      }
    },
    section3: {
      get: function () {
        this.section = $('.section3');
      },
      bind: function () {
        var self = this;
        //日期选择\表格初始化 first
        self.setDateSelect();
        // 表格点击事件 second
        if (D1.framework.platform === 'pc'){
          self.section.on("click", '.calendar td #course', function () {

            var EveryTd=$(this).parents('td');
            var amCon=EveryTd.find(".am").find(".cls");
            var pmCon=EveryTd.find(".pm").find(".cls");
            if(amCon.length > 0 || pmCon.length>0){//判断是否存在内部对象
			  var dayAmOrPm= $(this).attr('class');
              self.getDayClass($.trim($(this).parents('td').attr('class')).slice(1),EveryTd,dayAmOrPm);
            }
            else{
              return;
            }
          });
        }
        else{
          self.section.on(D1.event.end, 'td', function () {
            var EveryTd=$(this);
            var amCon=EveryTd.find(".am").find(".cls");
            var pmCon=EveryTd.find(".pm").find(".cls");
            if(amCon.length > 0 || pmCon.length>0){//判断是否存在内部对象
				var dayAmOrPm= $(this).attr('class');
              self.getDayClass($.trim($(this).attr('class')).slice(1),EveryTd,dayAmOrPm);
            }
            else{
              return;
            }
          });
        }
      },
      fuDateSelect : function(eventType){//日期更新时update ajax
        var self = this,
            active = "",
            actData ="",
            citySel = $(".section3 .city .option-list li:first-child").data("city"),
            nShopSel = $(".section3 .n_shop .option-list li:first-child").data("n_shop"),
            monthSel = $(".section3 .month .option-list li:first-child").data("month"),
            yearSel = $(".section3 .year .option-list li:first-child").data("year"),
            cateSel =$(".section3 .cate .option-list li:first-child").data("cate"),
            link = "";

        this.section.on(eventType,".cmNiceSelect",function(){
            var acListBox=$(this).find(".option-list");

            $(".option-list").hide();
            acListBox.show();

            return false;
        }).on(eventType,".option-list li",function(){//年月城市选择函数
          var parent = $(this).closest(".cmNiceSelect");

          if($(this).attr("data-year")){
            active = "year"
            yearSel = actData = $(this).data(active);
            linkUpdate();
            
          }else if($(this).attr("data-month") ){
            active = "month";
            monthSel = actData = $(this).data(active);
            linkUpdate();

          }else if($(this).attr("data-n_shop") ){
            active = "n_shop";
            nShopSel = actData = $(this).data(active);
            linkUpdate();
            
          }else if($(this).attr("data-city") ){
            active = "city";
            citySel = actData = $(this).data(active);
            linkUpdate();
            updateNShops($(this).attr("data-n_shops"));

          }else if($(this).attr("data-cate") ){
            active="cate";
            cateSel = actData = $(this).data(active);
            linkUpdate();
            
          }
          self.setTableCon(linkUpdate());
          parent.find(".show-text").html($(this).html()+"<a class='arr'><b></b></a>");
          parent.find(".option-list").hide();
          return false;
        });
        $(document).on(eventType,function(){
          $(".option-list").hide();
        });
        function linkUpdate(){//更新链接
            link="/course/index/newcalendar?py="+yearSel+"&pm="+monthSel+"&n_shop="+nShopSel+"&province="+citySel+"&pcat="+cateSel;
            return link;
        }
        function updateNShops(n_shops){
            var arrstr = n_shops.split("|");
            $('.schedule .n_shop .option-list li').hide();
            var firstShop = true;
            for(var i=0; i<arrstr.length;i++){
                var tempid = arrstr[i];
                $('.schedule .n_shop .option-list li').each(function(){
                    if(tempid == $(this).attr('data-n_shop')){
                        $(this).show();
                        if(firstShop){
                            firstShop = false;
                            nShopSel = actData = $(this).attr('data-n_shop');
                            linkUpdate();
                            var parent = $(".schedule .n_shop .cmNiceSelect");
                            parent.find(".show-text").html($(this).html()+"<a class='arr'><b></b></a>");
                            parent.find(".option-list").hide();
                        }
                    }
                });
            }
        }
        updateNShops($('.schedule .city .select-box').attr("data-n_shops"));
        this.setTableCon(linkUpdate());
      },
      setDateSelect: function () {//日期选择ajax
        var self = this;
        var formBox = self.section.find('.form');
        $.ajax({
          dataType: 'html',
          url: 'index.php/course/index/choose'
        }).then(function (data) {
            formBox.html(data);
            if (D1.framework.platform === 'pc'){
               self.fuDateSelect("click");
            }
            else{
              self.fuDateSelect(D1.event.end);
            }
          });
      },
      setTableCon: function (link) {//表格获取ajax
        $.ajax({
          dataType: 'html',
          url: link
        }).then(function (data) {
            $(".section3 .calendar").html(data);
          });
      },
      getDayClass: function (day,EveryTd,dayAmOrPm){//表格TD事件
        var date=EveryTd.data("date"),
            city=EveryTd.data("city"),
            n_shop=EveryTd.data("n_shop"),
			dayamorpm=dayAmOrPm;

        var dateArr=date.split ("-");//获得数字
		
        page.section3Popup.updateClasses(
            {
              fixeddate: date,
              p: 1,
              province: city,
              n_shop: n_shop,
			  dayamorpm:dayamorpm
            }
        );
      },
      ini: function () {
        this.get();
        this.bind();
      }
    },
    section3Popup: {
      get: function () {
        this.section = $('.schedule-popup');
        this.scroller = this.section.find('.scroller');
        this.citySel = this.section.find('.form').find('select.city');
        this.monthSel = this.section.find('.form').find('select.month');
        this.yearSel = this.section.find('.form').find('select.year');
        this.cateSel = this.section.find('.form').find('select.cate');
      },
      updateClasses: function (data) {
        var self = this;
        var link="/course/index/scheduleajax";
        if (D1.framework.platform === 'pc'){  
            slink= "/course/index/scheduleajax";
             $.ajax({
              type: 'POST',
              data: data,
              dataType: 'html',
              url: link
            }).then(function (d) {
                //插入内容到盒子中
               var ul = $('.schedule-popup .list-box').find('ul.scroller');
				
                ul.html(d);
				page.section3Popup.setCarousel();
				page.section3Popup.preparePopup();
				if(data.dayamorpm=='pm'){				
					$('.schedule-popup .carousel-control .next').click();
				}
                $(".schedule-popup .popup-header").addClass("popupSpe");
                ul.find('.cmStar').each(function (i, o) {//星级函数
                  new D1.StarRating(o);
                });
                D1.framework.popup.open('.schedule-popup');				
              });
      } else{
        link= "/course/index/scheduleajax_mobile?fixeddate="+ data.fixeddate+"&n_shop="+ data.n_shop +"&p=1&province="+ data.province;
        window.open(link)
      }
       
      },
      orderAction: function(){//选择人数 下订单
        this.section.on("click",".cmNiceSelect",function(){
            var acListBox=$(this).find(".option-list");

            $(".option-list").hide();
            acListBox.show();

            return false;
        }).on("click",".option-list li",function(){//年月城市选择函数
          var parent = $(this).closest(".cmNiceSelect");
              personNum = $(this).data("person");

          parent.find(".show-text").html($(this).html()+"<a class='arr'><b></b></a>");
          parent.find(".select-box").data("person",personNum);
          parent.find(".option-list").hide();
          return false;
        }).on("click", '.btn-box .button', function () {//预定课程

          var url = $(this).data("url"),
              num = $(this).closest(".btn-box").find(".select-box").data("person");

              window.location.href = url +'&?qty='+num;
        });
        $(document).on("click",function(){
          $(".option-list").hide();
        });
      },
      setCarousel: function () {
        if (D1.framework.platform === 'pc') {
          var offset = (Math.min($(window).width(), 1400) - 850) / 2;
          this.scroller.width(Math.min($(window).width(), 1400));
          this.carousel = new D1.Carousel(this.scroller, {
            offset: offset
          });
          this.carousel.controlPrev.width(offset);
          this.carousel.controlNext.width(offset);		  
        }
      },
	  setHeight: function () {
		if (D1.framework.platform === 'pc') {
		  var liMaxHeight = 0;
		  this.scroller.find('li.item').each(function(){
			var liHeight=$(this).outerHeight(true);		
			if(liHeight>liMaxHeight){
				liMaxHeight=liHeight;
			}			
		  })
		  liMaxHeight=liMaxHeight+10;
		  $('.schedule-popup .carousel-control').find('.prev').css({height:liMaxHeight});
		  $('.schedule-popup .carousel-control').find('.next').css({height:liMaxHeight});
		}
	  },
	  imgLoad: function(url) { // calculate height after img loaded
		var img = new Image();
		img.src = url;
		if(img.complete) { 
			page.section3Popup.setHeight();
			return; 
		}
		img.onload = function () { 
			page.section3Popup.setHeight();
		};
	  },
	  preparePopup: function(url) { //get last img url
		var ilen = $('.schedule-popup .imgs').find('img').length-1;
		var iurl=$('.schedule-popup .imgs >img:eq('+ ilen +')').attr("src");
		page.section3Popup.imgLoad(iurl);
	  },
      setStar: function () {
        this.section.find('.cmStar').each(function (i, o) {
          new D1.StarRating(o);
        });
      },
      bind: function () {
        var that = this;
        if (D1.framework.platform === 'pc') {
          D1.wheel(this.scroller[0], function (e, delta) {
            var step = 0,
              t = that.scroller.position().top;
            that.scroller.css('height', 'auto');
            if (delta > 0) {
              step = 50;
            } else {
              step = -50;
            }
            if ((delta > 0 && t + step > 0) || (delta < 0 && (t + that.scroller.height() < that.scroller.parent().height()))) {
              return;
            }

            that.scroller.stop().animate({
              top: t + step
            }, 300);
          }, false);

          that.orderAction();//选择人数 下订单

        } else {//手机事件
          // if (!this.iscroll) {
          //   this.iscroll = new IScroll(this.section[0], {
          //     bounce: false
          //   });
          // }
          //预定课程
          // this.section.on(D1.event.touch, '.button', function () {
          //   var l = $(this).data("url");
          //   var qty = $(this).siblings('.fake-select').find('select.n').val();
          //   window.location.href = l +'&?qty='+qty;
          // });
          // this.section.find('.form').find('select').on(D1.event.touch, function () {
          //   D1.framework.popup.close();
          // });
        }
      },
      ini: function () {
        this.get();
        // this.setCarousel();
        this.bind();
        this.setStar();
      }
    },
    section4: {
      get: function () {
        this.section = $('.section4');
        this.items = this.section.find('li');
      },
      bind: function () {
        var that = this;
        this.items.on(D1.event.end, function () {
          if (D1.framework.platform === 'pc') {
            //open pop
            //D1.framework.popup.open('.the-academy-popup');
            //that.carouselTo($(this).data('idx'));
          } else {
            if (D1.framework.isMove) {
              return
            }
            $(this).toggleClass('on');
            D1.framework.to(D1.framework.currentSection);
          }
        });
      },
      ini: function () {
        this.get();
        this.bind();
      }
    },
    section4Popup: {
      get: function () {
        this.section = $('.membership-popup');
        this.scroller = this.section.find('.scroller');
      },
      bind: function () {
        var that = this;
        if (D1.framework.platform === 'pc') {
          this.scroller.each(function (i, o) {
            D1.wheel(o, function (e, delta) {
              var s = $(o),
                step = 0,
                t = parseInt(s.css('top'), 10);
              s.css('height', 'auto');
              if (delta > 0) {
                step = 50;
              } else {
                step = -50;
              }
              if ((delta > 0 && t + step > 0) || (delta < 0 && (t + s.height() < s.height()))) {
                return;
              }

              s.stop().animate({
                top: t + step
              }, 300);
            }, false);
          });
        }
      },
      change: function (idx) {
        this.section.find('.idx-item').hide().eq(idx).show();
      },
      ini: function () {
        this.get();
        this.bind();
      }
    },
    section5: {
      get: function () {
        this.section = $('.section5');
        this.jListBox = this.section.find('.list-box');
        this.scroller = this.jListBox.find('.scroller');
      },
      updateCrew: function (arr) {
        var i = 0,
          l = arr.length,
          temp = null,
          that = this,
          html = '';
        var template =
          '<li class="item">' +
            '<h3>{{name}}</h3>' +
            '<div class="dd">' +
            '<div class="desc">' +
            '<p class="title">{{title}}</p>' +
            '<div>{{desc}}</div>' +
            '<div class="img-box">' +
            '<img src="/skin/frontend/rwd/default/images/crew/{{img}}" />' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</li>';
        if (l > 0) {
          for (i; i < l; i++) {
            temp = arr[i];
            (function (d) {
              html += that.getHtml(template, d);
            }(temp));
          }
          this.scroller.html(html);
        } else {
          this.scroller.html('<li class="none">nothing find!</li>');
        }

        if (D1.framework.platform === 'pc') {
          this.scroller.css('top', 0);
          this.carousel.refresh({
            start: 1
          });
        }
      },
      getHtml: function (str, args) {
        if (typeof str !== 'undefined') {
          if (typeof args === 'object') {
            var item = '';
            for (item in args) {
              str = str.replace(new RegExp("\{\{" + item + "}}", 'g'), args[item]);
            }
          }
          return str;
        }
        return '';
      },
      showList: function () {
        this.section.find('.default').hide();
       // this.jListBox.show();
      },
      _test: function () {
        var num = Math.floor(Math.random() * 20),
          i = 0,
          d = [];
        for (i; i < num; i++) {
          d.push({
            name: 'CREW NAME' + i,
            title: 'TITLE',
            desc: 'desc desc',
            img: 'crew.jpg'
          });
        }
        this.showList();
        this.updateCrew(d);
      },
      bind: function () {
        var that = this;

        this.section.find('.form').find('select').on('change', function () {
          that._test();
        });

        if (D1.framework.platform === 'pc') {
          D1.wheel(this.scroller[0], function (e, delta) {
            if (e.stopPropagation) {
              e.stopPropagation();
            }
            if (typeof e.cancelBubble !== 'undefined') {
              e.cancelBubble = true;
            }
            var step = 0,
              t = that.scroller.position().top;
            that.scroller.css('height', 'auto');
            if (delta > 0) {
              step = 50;
            } else {
              step = -50;
            }
            if ((delta > 0 && t + step > 0) || (delta < 0 && (t + that.scroller.height() < that.scroller.parent().height()))) {
              return;
            }
            that.scroller.stop().animate({
              top: t + step
            }, 300);
          }, false);
        } else {
          this.jListBox.on(D1.event.end, 'li.item', function () {
            if (D1.framework.isMove) {
              return
            }
            $(this).toggleClass('on');
            D1.framework.to(D1.framework.currentSection);
          });
        }
      },
      setCarousel: function () {
        if (D1.framework.platform === 'pc') {
          /*this.carousel = new D1.Carousel(this.scroller, {
           unit: 3,
           offset: 470
           });
          var offset = (Math.min($(window).width(), 1400) - 706) / 2;
          this.scroller.width(Math.min($(window).width(), 1400));
          this.carousel = new D1.Carousel(this.scroller, {
            offset: offset,
            unit: 3
          });
          this.carousel.controlPrev.width(offset);
          this.carousel.controlNext.width(offset);
           */
          var offset = (Math.min($(window).width(), 1400) - 706) / 2;
          this.scroller.width(Math.min($(window).width(), 1400));

          this.carousel = new D1.UnlimitScroller(this.scroller, {
            offset: offset,
            unit:3
          });
          this.carousel.controlPrev.width(offset);
          this.carousel.controlNext.width(offset);
        }
      },
      ini: function () {
        this.get();
        this.setCarousel();
        this.bind();
        this.showList();
      }
    },
    section6: {
      get: function () {
        this.section = $('.section6');
        this.items = this.section.find('.footer_sel');
      },
      addSel: function(eventType){
        var self = this;

        this.section.on(eventType,".cmNiceSelect",function(){
            var acListBox=$(this).find(".option-list");

            $(".option-list").hide();
            acListBox.show();
            return false;
        }).on(eventType,".option-list li",function(){//年月城市选择函数
          var parent = $(this).closest(".cmNiceSelect");

          parent.find(".show-text").html($(this).html()+"<a class='arr'><b></b></a>");
          parent.find(".option-list").hide();
          return false;
        }).on(eventType,".option-list li",function(){//地址函数

          var list=self.section.find(".fake-select-list");

          list.removeClass("active").eq($(this).index()).addClass("active");
        });
        $(document).on(eventType,function(){

          $(".option-list").hide();
        });
      },
      eventBind: function(){
        if (D1.framework.platform === 'pc')
        {
            this.addSel("click");
        }
        else
        {
            this.addSel(D1.event.end);
        }
      },
      ini: function(){
        this.get();
        this.eventBind();
      }
    },
    ini: function () {
      this.section0.ini();
      this.section1.ini();
    //  this.section1Popup.ini();
      this.section2.ini();
      this.section2Popup.ini();
      this.section3.ini();
      this.section3Popup.ini();
      this.section4.ini();
      this.section4Popup.ini();
      this.section5.ini();
      this.section6.ini();
    }
  };

  D1.page = page;

  $(function () {
    page.ini();
  });
}());