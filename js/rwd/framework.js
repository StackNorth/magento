(function () {
  var framework = {
    header: {
      get: function () {
        this.jHead = $('.header');
        this.jMenuBtn = this.jHead.find('.menu');
        this.jStarBtn = this.jHead.find('.star');
        this.jMenus = this.jHead.find('.nav-item');
      },
      set: function (className) {
        var btn = this.jHead.find('.' + className),
          body = $('body');
        if (framework.popup.isShown) {
          framework.popup.close();
        }
        if (body.hasClass(className + '-on')) {
          body.removeClass(className + '-on');
          btn.removeClass('on');
        } else {
          if (framework.platform !== 'pc') {
            if (!body.hasClass('menu-on') && !body.hasClass('star-on')) {
              $('.dropdown-nav-box').css({
                left: className === 'menu' ? 0 : '-100%'
              });
            }
          }

          body.removeClass('menu-on star-on').addClass(className + '-on');
          this.jMenuBtn.removeClass('on');
          this.jStarBtn.removeClass('on');
          btn.addClass('on');

          if (framework.platform !== 'pc') {
            if (body.hasClass('menu-on')) {
              $('.dropdown-nav-box').stop().animate({
                left: 0
              });
            } else if (body.hasClass('star-on')) {
              $('.dropdown-nav-box').stop().animate({
                left: '-100%'
              });
            }
          }
        }


      },
      close: function () {
        $('body').removeClass('star-on menu-on');
        this.jMenuBtn.removeClass('on');
        this.jStarBtn.removeClass('on');
      },
      bind: function () {
        var that = this;
        this.jMenuBtn.on(D1.event.end, function () {
          that.set('menu');
        });
        this.jStarBtn.on(D1.event.end, function () {
          that.set('star');
        });
        this.jMenus.on(D1.event.end, function () {
          if (framework.platform !== 'pc') {
            that.close();
          }
          framework.to($(this).data('section'));
          if (D1.framework.popup.isShown) {
            D1.framework.popup.close();
          }
        });
        $('.logo').on(D1.event.end, function () {
          framework.to(0);
        });
      },
      ini: function () {
        this.get();
        this.bind();
      }
    },
    checkScreen: function () {
      var j = $('html'),
        width = j.width();
      j.removeClass('mobile pc');
      if (width <= 960) {
        j.addClass('mobile');
        this.platform = 'mobile';
      } else {
        j.addClass('pc');
        this.platform = 'pc';
      }
    },
    scrollTo: function (s, fn, offset, idx) {
      var j = $(s);
      if (j.length && this.rootScrollingElem) {
        this.isScrolling = true;
        var top = j.offset().top + (offset || 0),
          maxTop = this.rootScrollingElem.scrollHeight - $(window).height();
        if (top > maxTop) {
          top = maxTop;
        }
        var duration = 900;
        if (this.platform !== 'pc') {
          $('.header').stop().animate({
            //top: top
          }, duration, 'swing');
        }
        $(this.rootScrollingElem).stop().animate({
          scrollTop: top
        }, duration, 'swing', function () {
          if (fn) {
            fn();
          }
          if (framework.platform !== 'pc') {
            var s = framework.sections,
              i = 0,
              l = s.length;
            for (i; i < l; i++) {
              s.eq(i).data('scroll').scrollTo(0, 0, 100);
            }
            ;
          }
          framework.isScrolling = false;

          framework.currentSection = idx;
          location.hash = '#' + idx;

          if (framework.platform !== 'pc') {// mobile refresh iscroll object
            $('.section' + idx).data('scroll').refresh();
          } else {// pc show current status on nav bar
            $('.header').find('.nav-item').removeClass('on');
            $('.header').find('.nav-item[data-section="' + idx + '"]').addClass('on');
          }

        });
      }
    },
    _getRootScrollingElem: function () {
      var rootElem = document.compatMode && document.compatMode === 'CSS1Compat' ? document.documentElement : document.body;
      framework.rootScrollingElem = (/webkit/.test(navigator.userAgent.toLowerCase())) ? document.body : rootElem;
    },
    to: function (idx) {
      idx = idx >= 0 ? idx : 0;
      if ($('.section' + idx)[0]) {
        this.scrollTo('.section' + idx, null, -1 * $('.header').height(), idx);
      }
    },
    _iniUrl: function () {
      this.currentSection = 0;

      //handle section ...
      /**
       * HASH #hash?params=value
       * hash - section
       * params - query params
       */

      var handle = function (hash) {
        if (!hash) {
          return
        }
        var result = {},
          hash = hash.slice(1);
        var section = hash.split('?')[0],
          query = hash.split('?')[1];
        result.section = parseInt(section, 10);
        if (query) {
          var obj = {},
            temp = query.split('&'),
            i = 0,
            l = temp.length,
            params = [];
          for (i; i < l; i++) {
            params = temp[i].split('=');
            obj[params[0]] = params[1];
          }
          result.params = obj;
        }
        return result;
      };

      var hash = location.hash,
        data = handle(hash);

      if (data) {
        if (data.section) {
          this.currentSection = data.section;
          $(function () {
            framework.to(data.section);
          });
        }
      }
    },
    _iniSections: function () {
      var h = $(window).height(),
        hh = 90,
        that = this;

      //change platform
      this.checkScreen();

      if (this.platform !== 'pc') {
        /**
         * touch to next section by IScroll on mobile
         */
        hh = 58;
        if (!this.isScrollIni) {
          this.sections = $('.section');

          this.sections.each(function (i, o) {
            var scroll = new IScroll(o, {
              bounce: false
            });
            scroll.sectionIdx = i;
            scroll.on('scrollStart', function () {
              if (framework.currentSection !== this.sectionIdx) {
                if ((framework.currentSection < this.sectionIdx && Math.abs(this.distX) < Math.abs(this.distY) && this.distY < 0) || (framework.currentSection > this.sectionIdx && Math.abs(this.distX) < Math.abs(this.distY) && this.distY >= 0)) {
                  framework.to(this.sectionIdx);
                }
                return;
              }
              if (this.y === 0 && Math.abs(this.distX) < Math.abs(this.distY) && this.distY >= 0) {
                framework.to(this.sectionIdx - 1);
                return;
              }
              if (this.y === this.maxScrollY && Math.abs(this.distX) < Math.abs(this.distY) && this.distY < 0) {
                framework.to(this.sectionIdx + 1);
                return;
              }
            });
            $(o).data('scroll', scroll);
          });
          this.isScrollIni = true;
        }

        this.sections.css('max-height', (h - 50) + 'px');

        var i = 0,
          l = this.sections.length;
        for (i; i < l; i++) {
          this.sections.eq(i).data('scroll').refresh();
        }
      } else {
        /**
         * wheel to next section on pc
         */
        if (!this.isWheelIni) {
          this.sections = $('.section');

          //define wheel
          function wheel(obj, fn, mWheel) {
            var mousewheel=mWheel? "mousewheel":"DOMMouseScroll";
            if (obj.attachEvent) {//if IE (and Opera depending on user setting)
              obj.attachEvent('on' + mousewheel, handler);
            } else { //WC3 browsers
              obj.addEventListener(mousewheel, handler, false);
            }

            function handler(ev) {
              var delta = 0;
              var oEvent = ev || event;
              var delta = oEvent.detail ? -oEvent.detail / 3 : oEvent.wheelDelta / 120;
              if (oEvent.preventDefault) {
                oEvent.preventDefault();
              }
              oEvent.returnValue = false;  
              return fn.apply(obj, [oEvent, delta]);
            }
          };
          D1.wheel = wheel;

          var fn = function (e, delta, idx) {
            var step = delta > 0 ? -10 : 10,
              st = $(window).scrollTop() + step,
              t = $(e.target || e.srcElement),
              section = t.hasClass('section') ? t : t.parents('.section'),
              win = $(window);

            if (framework.currentSection !== idx) {
              if ((framework.currentSection < idx && delta < 0) || (framework.currentSection > idx && delta > 0)) {
                framework.to(idx);
              }
              return;
            } else if (delta < 0 && (win.scrollTop() >= section.offset().top + section.height() - win.height())) {
              framework.to(idx + 1);
              return;
            } else if (delta > 0 && (win.scrollTop() + hh <= section.offset().top)) {
              framework.to(idx - 1);
              return;
            } else {
              $(window).scrollTop(st);
            }
          };

          this.sections.each(function (i, o) {
            var currentIdx = i;
            var mWheel=true;
            wheel(o, function (e, delta) {

              if (framework.isScrolling) {
                return
              }
              fn(e, delta, currentIdx);

            },mWheel);

            mWheel=!mWheel;
          });

          this.isWheelIni = true;

        }

        //how about IE6?
        //this.sections.css('max-height', h + 'px');
        //so use it like...
        /*this.sections.each(function (i, o) {
         if ($(o).height() > h - hh) {
         $(o).height(h - hh);
         }
         });*/

      }

      //when ini, because rootScrollElement has not get, so it not effect.
      this.to(this.currentSection);
    },
    bind: function () {
      var that = this,
        win = $(window);
      win.on('resize', function () {
        if (that.winResizeTimer) {
          clearTimeout(that.winResizeTimer);
        }
        that.winResizeTimer = setTimeout(function () {
          that._iniSections();
          that.popup.resizeIt();
          /*
          var crewScroller = $('.section5 .crew .scroller'),
            offset = (Math.min($(window).width(), 1400) - (crewScroller.find('.li:eq(0)').outerWidth() * 3)) / 2;
          crewScroller.refresh({
            offset: offset
          });
          */
        }, 500);
      });

      win.on(D1.event.move, function () {
        that.isMove = true;
        if (that.moveTimer) {
          clearTimeout(that.moveTimer);
        }
        that.moveTimer = setTimeout(function () {
          framework.isMove = false;
        }, 300);
      });
    },
    popup: {
      get: function () {
        this.jPoper = $('.popup-section');
        this.jClose = this.jPoper.find('.close');
        $('.popup-item').appendTo(this.jPoper);
      },
      bind: function () {
        var that = this;
        this.jClose.on(D1.event.touch, function () {
          that.close();
        });
      },
      close: function () {
        $('body').removeClass('popup-show');
        this.opened = '';
        this.isShown = false;
      },
      open: function (selector) {
        if (selector && this.jPoper.find(selector)[0]) {
          var j = this.jPoper.find(selector);
          $('body').addClass('popup-show');
          this.jPoper.find('.popup-item').hide();
          j.show();
          this.opened = j;
          this.isShown = true;
          this.resizeIt();
        }
      },
      resizeIt: function () {
        if (this.isShown) {
          var j = this.opened;
          this.jPoper.css({
            //top: D1.framework.platform === 'pc' ? ($(window).scrollTop() + 90) : 58,
            height: $(window).height() - (D1.framework.platform === 'pc' ? 90 : 58)
          });
          if (D1.framework.platform === 'pc') {
            var h = this.jPoper.height() - j.find('.popup-header').height();
            j.find('.scroller').height(h);
            j.find('.scroller').parents('.carousel-content').height(h);
          } else {
            j.height(this.jPoper.height());
          }
          if (j.find('.scroller').data('carousel')) {
            var offset = (Math.min($(window).width(), 1400) - (j.find('.scroller li:eq(0)').outerWidth() + 38)) / 2;
            j.find('.scroller').data('carousel').refresh({
              offset: offset
            });
          }
        }
      },
      ini: function () {
        this.get();
        this.bind();
      }
    },
    _transSelect: function () {
      if (this.platform === 'pc') {
        $('.fake-select').find('select').each(function (i, o) {
          $(o).data('nice-select', new D1.niceSelect(o, {
            placeholder: ''
          }));
        });
      }
    },
    _iniTopbar: function () {
      window.scrollTo(0, 1);
    },
    ini: function () {
      this._getRootScrollingElem();
      this._iniTopbar();
      this.checkScreen();
      this.header.ini();
      this.bind();
      this.popup.ini();
      this._iniUrl();
      this._iniSections();
      this._transSelect();
    }
  };

  D1 = window.D1 || {};
  D1.framework = framework;

  $(function () {
    framework.ini();
  });
}());