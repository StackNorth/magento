D1.Carousel = function (container, args) {
    this.content = container;
    this.items = container.children();
    this.params = {
      type: "h",
      unit: 1,
      speed: "slow",
      position: 0,
      autoPlay: false,
      intervalTime: 8000,
      offset: 0
    };
    if (args) {
      $.extend(this.params, args);
    }
    this.ini();
    this.content.data('carousel', this);
  };

D1.Carousel.prototype = {
  move: function() {
    var that = this, css = {};
    css[this.params.cssName] = '-' + this.params.position + 'px';
    this.content.stop().animate(css, this.params.speed, function() {
      that.complete();
    });
  },
  stepToggle: function (idx) {
    this.steps.removeClass('current').eq(idx).addClass('current');
    this.currentStep = idx;
  },
  refresh: function (args) {
    var conWidth,
      conHeight;
    jQuery.extend(this.params, args);
    this.items = this.content.children();
    if (this.params.wh) {
      this.wrap.css({
        width: this.params.wh.w,
        height: this.params.wh.h
      });
      this.wrap.find('.carousel-content').css({
        width: this.params.wh.w,
        height: this.params.wh.h
      });
    } else {
      if (this.params.type === "h") {
        this.wrap.css({
          height: this.content.height()
        });
        this.wrap.find('.carousel-content').css({
          height: this.content.height()
        });
      } else {
        this.wrap.css({
          width: this.content.width()
        });
        this.wrap.find('.carousel-content').css({
          width: this.content.width()
        });
      }
    }
    if (this.params.start) {
      this.currentStep = 0;
    }
    if (this.params.type === "h") {
      conWidth = this.items.outerWidth(true) * this.items.length + "px";
      conHeight = "100%";
    } else {
      conWidth = "100%";
      conHeight = this.items.outerHeight(true) * this.items.length + "px";
    }
    this.content.css({
      width: conWidth,
      height: conHeight
    });
    
    var steps = '',
      i = 0,
      items = this.items,
      l = this.params.unit > 0 ? Math.ceil(this.params.scope / this.params.moveUnit + 1) : Math.ceil(this.items.outerWidth(true) * this.items.length / this.canvas.width());
    //console.log(l);
    steps += '<span class="step-box">';
    for (i; i < l; i++) {
      steps += '<span data-step="' + i + '" class="step">' + (i + 1) + '</span>';
    }
    steps += '</span>';
    this.wrap.find('.step-box').remove();
    this.controlPrev.after(steps);
    this.steps = this.wrap.find(".carousel-control .step");
    this.config();
    this.reset();
  },
  moveTo: function (idx) {
    if (idx < 0) {return}
    idx = parseInt(idx, 10);
    var target = idx * this.params.moveUnit - this.params.offset;
    this.params.position = target < this.params.scope ? target : this.params.scope;
    //this.params.position = target > 0 ? target : 0;
    var that = this, css = {};
    css[this.params.cssName] = (-1 * this.params.position) + 'px';
    this.content.stop().animate(css, this.params.speed, function() {
      that.complete();
    });
    this.stepToggle(idx);
    this._autoPlay();
  },
  complete: function() {
    if (this.params.position === this.params.scope) {
      this.controlNext.addClass("not");
    } else {
      this.controlNext.removeClass("not");
    }
    if (this.params.position === -1 * this.params.offset) {
      this.controlPrev.addClass("not");
    } else {
      this.controlPrev.removeClass("not");
    }
    if (this.items.outerWidth(true) * this.items.length < this.canvas.width()) {
      //this.controlNext.addClass("not");
      //this.controlPrev.addClass("not");
    }
    this.status = false;
    if (this.params.onScrollEnd) {
      this.params.onScrollEnd.call(this);
    }
  },
  next: function() {
    if (this.status || this.params.position === this.params.scope) {return;}
    this.status = true;
    /*var target = this.params.position + this.params.moveUnit;
    this.params.position = target < this.params.scope ? target : this.params.scope;
    this.move();
    this.stepToggle(this.currentStep + 1);*/
    this.moveTo(this.currentStep + 1);
    this._autoPlay();
  },
  prev: function() {
    if (this.status || this.params.position === this.offset) {return; }
    this.status = true;
    /*var target = (this.currentStep - 1) * this.params.moveUnit;
    this.params.position = target > 0 ? target : 0;
    this.move();
    this.stepToggle(this.currentStep - 1);*/
    this.moveTo(this.currentStep - 1);
    this._autoPlay();
  },
  reset: function() {
    /*this.content.stop().css({
      top: this.params.type === "v" ? this.params.offset : 0,
      left: this.params.type === "h" ? this.params.offset : 0,
    });*/
    this.complete();
    this.moveTo(this.currentStep);
  },
  bind: function() {
    var that = this;
    this.controlNext.click(function() {
      if ($(this).hasClass('not')) {return}
      that.next();
    });
    this.controlPrev.click(function() {
      if ($(this).hasClass('not')) {return}
      that.prev();
    });
    this.wrap.on('click', '.step', function () {
      var s = jQuery(this),
        idx = s.data('step');
      that.moveTo(idx);
    });
  },
  create: function() {
    var conWidth,
      conHeight,
      html = [
        "<div class='carousel-wrap' style='width:" + this.content.width() + "px;height:" + this.content.height() + "px;'>",
          "<div class='carousel-content' style='position:relative;width:" + this.content.width() + "px;height:" + this.content.height() + "px;'></div>",
          "<div class='carousel-control'>",
            "<span class='prev not'><span class='prev-s'></span></span>",
            "<span class='next'><span class='next-s'></span></span>",
          "</div>",
        "</div>"
      ].join('');
    
    if (this.params.type === "h") {
      conWidth = this.items.outerWidth(true) * this.items.length + "px";
      conHeight = "100%";
    } else {
      conWidth = "100%";
      conHeight = this.items.outerHeight(true) * this.items.length + "px";
    }
	if(this.content.parents(".schedule-popup").find('.carousel-wrap').length<1){		
		this.content.wrap(html);
	}
    this.content.css({
      position: "absolute",
      top: this.params.type === "v" ? this.params.offset : 0,
      left: this.params.type === "h" ? this.params.offset : 0,
      width: conWidth,
      height: conHeight,
      overflow: "visible"
    });
    this.wrap = this.content.parents(".carousel-wrap");
    this.canvas = this.content.parent();
    this.controlNext = this.wrap.find(".carousel-control .next");
    this.controlPrev = this.wrap.find(".carousel-control .prev");
    this.config();
    var steps = '',
      i = 0,
      items = this.items,
      l = this.params.unit > 0 ? Math.ceil(this.params.scope / this.params.moveUnit + 1) : Math.ceil(this.items.outerWidth(true) * this.items.length / this.canvas.width());
    steps += '<span class="step-box">';
    for (i; i < l; i++) {
      steps += '<span data-step="' + i + '" class="step">' + (i + 1) + '</span>';
    }
    steps += '</span>';
    this.controlPrev.after(steps);
    this.steps = this.wrap.find(".carousel-control .step");
    this.steps.eq(0).addClass('current');
    this.currentStep = 0;
  },
  config: function() {
    if (this.params.type === "h") {
      this.params.cssName = "left";
      this.params.scope = this.params.offset + this.content.width() - this.canvas.width();
      this.params.moveUnit = this.params.unit > 0 ? this.items.outerWidth(true) * this.params.unit : this.canvas.width();
    } else {
      this.params.cssName = "top";
      this.params.scope = this.params.offset + this.content.height() - this.canvas.height();
      this.params.moveUnit = this.params.unit > 0 ? this.items.outerHeight() * this.params.unit : this.canvas.height();
    }
  },
  _autoPlay: function () {
    if (this.params.autoPlay) {
      if (this.timer) {clearTimeout(this.timer)}
      var that = this;
      this.timer = setTimeout(function () {
        that.moveTo(that.currentStep + 1 >= that.steps.length ? 0 : (that.currentStep + 1));
        that._autoPlay();
      }, this.params.intervalTime);
    }
  },
  ini: function() {
    /*if (this.params.type === "h") {
      if (this.content.width() >= this.items.outerWidth(true) * this.items.length) {return; }
    } else {
      if (this.content.height() >= this.items.outerHeight() * this.items.length) {return; }
    }*/
    this.create();
    this.bind();
    this.complete();
    this._autoPlay();
  }
};

/**
* D1.UnlimitScroller
*/
D1.UnlimitScroller = function (obj, args) {
  this.box = $(obj);
  this.items = obj.children();
  this.handleCfg(args || {});
  this.ini();
  this.box.data('carousel', this);
};

D1.UnlimitScroller.prototype = {
  handleCfg: function (args) {
    this.cfg = {
      offset: 0,
      idx: 0,
      unit: 1
    };
    
    $.extend(this.cfg, args);
  },
  create: function () {
    var html = [
      "<div class='carousel-wrap'>",
        "<div class='carousel-content' style='position:relative;'></div>",
        "<div class='carousel-control'>",
          "<span class='prev'><span class='prev-s'></span></span>",
          "<span class='next'><span class='next-s'></span></span>",
        "</div>",
      "</div>"
    ].join('');
    this.box.wrap(html);
    this.wrap = this.box.parents('.carousel-wrap');
    this.content = this.wrap.find('.carousel-content');
    this.controlNext = this.wrap.find('.next');
    this.controlPrev = this.wrap.find('.prev');
    
    if (this.items.length > 1) {
      this.box.append(this.items.clone());
      this.time = 2;
      if (this.items.length === 2) {
        this.box.append(this.items.clone());
        this.time = 3;
      }
      this.items = this.box.children();
      this.itemWidth = this.items.outerWidth(true);
      this.box.width(this.itemWidth * this.items.length);
      this.boxWidth = this.box.width() / this.time;
      this.box.css({
        left: -1 * this.boxWidth + this.cfg.offset
      });
      this.moveTo(this.cfg.idx);
      this.content.height(this.box.height());
    } else {
      this.wrap.addClass('no-effect');
    }
  },
  moveTo: function (idx) {
    var l = this.items.length;
    if (idx === 0) {
      idx = l / this.time + idx;
      this.box.css({
        left: -1 * (idx + 1) * this.itemWidth + this.cfg.offset
      });
    } else if (idx === l - 1 - (this.cfg.unit - 1)) {
      idx = idx - l / this.time;
      this.box.css({
        left: -1 * (idx - 1) * this.itemWidth + this.cfg.offset
      });
    }
    
    this.box.stop().animate({
      left: -1 * idx * this.itemWidth + this.cfg.offset
    }, 300);
    
    this.currentIdx = idx;
  },
  toNext: function () {
    this.moveTo(this.currentIdx + 1);
  },
  toPrev: function () {
    this.moveTo(this.currentIdx - 1);
  },
  refresh: function (args) {
    this.handleCfg(args || {});
    this.wrap.removeClass('no-effect');
    this.items = this.box.children();
    
    if (this.items.length > 1) {
      this.box.append(this.items.clone());
      this.time = 2;
      if (this.items.length === 2) {
        this.box.append(this.items.clone());
        this.time = 3;
      }
      this.items = this.box.children();
      this.itemWidth = this.items.outerWidth(true);
      this.box.width(this.itemWidth * this.items.length);
      this.boxWidth = this.box.width() / this.time;
      this.box.css({
        left: -1 * this.boxWidth + this.cfg.offset
      });
      this.moveTo(this.cfg.idx);
      this.content.height(this.box.height());
    } else {
      this.wrap.addClass('no-effect');
    }
  },
  bind: function () {
    var that = this;
  
    this.controlNext.on('click', function () {
      that.toNext();
      return false;
    });
    
    this.controlPrev.on('click', function () {
      that.toPrev();
      return false;
    });
  },
  ini: function () {
    this.create();
    this.bind();
  }
};

/**
* show calendar
* use refresh() to change date
* note: config.date should be date object.
*/
D1.Calendar = function (ele, args) {
  this.content = $(ele);
  this.config = this.handleCfg(args);
  this.ini();
};

D1.Calendar.prototype = {
  config: {
    date: new Date(),
    lang: 'en'//zh
  },
  handleCfg: function (args) {
    return args ? $.extend({}, this.config, args) : this.config;
  },
  refresh: function (args) {
    if (args) {
      this.config = this.handleCfg(args);
      this.create();
    }
  },
  create: function () {
    var table = $('<table cellspacing="0" cellpadding="0"></table>');
    
    var header = this._createHeader();
    table.append(header);
    
    var body = this._createBody();
    table.append(body);
    
    this.content.html(table);
    
    if (this.config.whenUpdate) {
      this.config.whenUpdate();
    }
  },
  _createHeader: function () {
    var i = 0,
        l = 7,
        str = this.config.lang === 'en' ? 'SMTWTFS' : '日一二三四五六',
        html = '<tr>';
    for (i; i < l; i++) {
      html += '<th><span>' + str.slice(i, i + 1) + '</span></th>';
    }
    html += '</tr>';
    return html;
  },
  _createBody: function () {
    var d = this.config.date,
        day = d.getDay(),
        date = d.getDate(),
        month = d.getMonth(),
        time = d.getTime(),
        i = 0,
        j = 0,
        weekLength = 5,
        dayLength = 7,
        dayTime = 24 * 60 * 60 * 1000,
        startDay = new Date(time - (date - 1) * dayTime),
        html = '',
        weekStr = '';
        
    //get real start day
    time = startDay.getTime();
    day = startDay.getDay();
    startDay = new Date(time - day * dayTime);
    
    for (i; i < weekLength; i++) {
      weekStr = '<tr>';
      for (j = 0; j < dayLength; j++) {
        weekStr += '<td class="' + (startDay.getMonth() === month ? '' : 'disable') + ' d' + startDay.getDate() + '"><div class="item"><div class="am"></div><div class="pm"></div><div class="d">' + startDay.getDate() + '</div></div></td>';
        startDay = new Date(+startDay + dayTime);
      }
      weekStr += '</tr>';
      html += weekStr;
    }
    return html;
  },
  ini: function () {
    this.create();
  }
};

/**
 * `UI.niceSelect` set a div to simulate select
 * new UI.niceSelect(select);
 * new UI.niceSelect(select, {
 * 		noArr: false,
 *		placeholder: 'please select...',
 *		afterIni: function () {
 *			console.log(this.cfg);
 *		},
 *		afterSelect: function (jEle) {
 *			console.log(jEle.attr('data-value'));
 *		}
 *	});
 * @module UI
 * @type {Class}
 * @namespace UI
 */
D1.niceSelect = function (select, cfg) {
  this.select = jQuery(select);
  this.cfg = {
    noArr: false,
    placeholder: '请选择'
  };
  this.extend(cfg);
  this.ini();
};
D1.niceSelect.prototype = {
  extend: function (cfg) {
    cfg = cfg || {};
    this.cfg = jQuery.extend(this.cfg, cfg);
  },
  createHtml: function () {
    var html = 
        '<div class="select-box">' +
          '<span class="show-text"></span><a class="arr"><b></b></a>' +
        '</div>' +
        '<ul class="option-list"></ul>',
      select = this.select;
    this.wrapper = jQuery('<div class="cmNiceSelect"></div>');
    select.after(this.wrapper);
    select.hide();
    
    this.wrapper.html(html);
    this.selectBox = this.wrapper.find('.select-box');
    this.showText = this.selectBox.find('.show-text');
    this.arr = this.selectBox.find('.arr');
    this.optionList = this.wrapper.find('.option-list');
    
    this.setList();
  },
  setList: function () {
    var select = this.select,
      option = select.find('option'),
      l = option.length,
      i = 0,
      temp = null,
      html = '';
    
    for (i; i < l; i++) {
      temp = option.eq(i);
      html += '<li data-value="' + (temp.attr('value') || '') + '">' + temp.html() + '</li>';
    }
    //D1.log(html);
    this.optionList.html(html);
    this.lists = this.optionList.find('li');
    this._iniValue();
  },
  _iniValue: function () {
    var select = this.select;
    if (select.attr('placeholder') || this.cfg.placeholder) {
      this.showText.addClass('place-holder').html(select.attr('placeholder') || this.cfg.placeholder);
      select.val('').trigger('change');
      return
    }
    if (this.select.val() !== '') {
      this.handleSelectEvent(this.lists.filter('[data-value="' + this.select.val() + '"]'));
    }
  },
  handleCfg: function () {
    var cfg = this.cfg;
    if (cfg.placeholder && this.showText.html() === '') {
      this.showText.addClass('place-holder').html(cfg.placeholder);
    }
    if (cfg.noArr) {
      this.arr.hide();
    }
    if (cfg.afterIni) {
      cfg.afterIni.call(this);
    }
  },
  update: function () {
    this.setList();
  },
  toggleList: function () {
    var optionList = this.optionList;
    if (optionList.hasClass('on')) {
      optionList.removeClass('on');
      this.wrapper.removeClass('on');
    } else {
      //show up or down
      if (this.wrapper.offset().top + this.optionList.height() + this.wrapper.height() >= $(window).scrollTop() + $(window).height()) {
        this.wrapper.addClass('up');
      } else {
        this.wrapper.removeClass('up');
      }
      optionList.addClass('on');
      this.wrapper.addClass('on');
    }
  },
  hideList: function () {
    this.optionList.removeClass('on');
    this.selectBox.removeClass('on');
  },
  handleSelectEvent: function (jEle) {
    this.showText.removeClass('place-holder').html(jEle.html());
    this.select.val(jEle.attr('data-value')).trigger('change');
    this.hideList();
    if (this.cfg.afterSelect) {
      this.cfg.afterSelect.call(this, jEle);
    }
  },
  bind: function () {
    var that = this;
    this.selectBox.on('click', function () {
      that.toggleList();
    });
    this.optionList.on('click', 'li', function () {
      that.handleSelectEvent(jQuery(this));
    });
    jQuery(document).on('click', function (e) {
      if (!jQuery(e.target).parents('.cmNiceSelect')[0]) {
        that.toggleList();
        that.hideList();
      }
    });
  },
  ini: function () {
    this.createHtml();
    this.handleCfg();
    this.bind();
  }
};

/**
* Set star rating as score for something
*
* @namespace D1
* @type {Class}
* @module D1
*/
D1.StarRating = function (jEle) {
	this.ipt = $(jEle);
	if (this.ipt.data('cmStarReady')) {return}
	this._cfg();
	this.ini();
	this.ipt.data('star', this);
};

D1.StarRating.prototype = {
	cfg: {
		readOnly: 0,
		total: 5,
		score: 0
	},
	_cfg: function () {
		var data = {};
		
		data.readOnly = (this.ipt.attr('data-readonly') && this.ipt.attr('data-readonly') === "1") ? 1 : 0;
		
		if (this.ipt.attr('data-total') && this.ipt.attr('data-total') !== "0") {
			data.total = parseInt(this.ipt.attr('data-total'));
		}
		
		if (this.ipt.val() !== "") {
			data.score = parseInt(this.ipt.val(), 10);
		} else {
			data.score = 0;
			this.ipt.val('0');
		}
		
		$.extend(this.cfg, data);
	},
	createHtml: function () {
		var obj = $('<ul class="cmStarReady"></ul>'),
			html = '',
			i = 0,
			l = this.cfg.total,
			score = this.cfg.score;
		
		for (i; i < l; i++) {
			html += '<li data-index="' + i + '" class="cmStar-item ' + (i < score ? 'on' : '') + '">' + i + '</li>';
		}
		obj.html(html);
		
		if (this.cfg.readOnly) {
			obj.addClass('readonly');
		}
		
		this.ipt.hide().data('cmStarReady', 1).after(obj);
		this.list = obj;
	},
	getValue: function () {
		return this.cfg.score;
	},
	setValue: function (num) {
		this.list.children().removeClass('on').filter(':lt(' + num + ')').addClass('on');
		this.cfg.score = num;
		this.ipt.val(num).trigger('change');
	},
	disable: function () {
		this.list.addClass('readonly');
	},
	enable: function () {
		this.list.removeClass('readonly');
	},
	bind: function () {
		var that = this;
		
		this.list.children().hover(function () {
			var s = $(this),
				lt = s.data('index') + 1;
			if (that.list.hasClass('readonly')) {return}
			that.list.toggleClass('hover').children(':lt(' + lt + ')').toggleClass('hover-on');
		});
		
		this.list.on('click', 'li', function () {
			var s = $(this),
				lt = s.data('index') + 1;
			if (that.list.hasClass('readonly')) {return}
			that.setValue(lt);
		});
	},
	ini: function () {
		this.createHtml();
		this.bind();
	}
};


