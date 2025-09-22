function snUpImage(files, editor, type) {
	var data = new FormData();
	
	$.each(files, function(key, value) {
		data.append(key, value);
	});

	data.append('type', type);

	$.ajax({
		url: '../common/sn_image_post_json.php?files', 
		type: 'POST',
		data: data, 
		cache: false,
		dataType: 'json',
		processData: false, 
		contentType: false, 
		success: function(data, textStatus, jqXHR) {
			if(typeof(data.error) === 'undefined') {				
			
				$.each(data, function(key, value) {							
					$(editor).summernote('editor.insertImage', data[key].img);
					alertify.success('이미지가 등록 되었습니다');															
				});

			}
			else {
				alertify.error(data.error);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alertify.error(textStatus);
		}	
	 });	
}

function snUpImageBo(files, editor, type, b_id) {
	var data = new FormData();
	
	$.each(files, function(key, value) {
		data.append(key, value);
	});

	data.append('type', type);
	data.append('b_id', b_id);

	$.ajax({
		url: '../../board/sn_image_post_json.php?files', 
		type: 'POST',
		data: data, 
		cache: false,
		dataType: 'json',
		processData: false, 
		contentType: false, 
		success: function(data, textStatus, jqXHR) {
			if(typeof(data.error) === 'undefined') {				
			
				$.each(data, function(key, value) {							
					$(editor).summernote('editor.insertImage', data[key].img);
					alertify.success('이미지가 등록 되었습니다');
				});

			}
			else {
				alertify.error(data.error);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alertify.error(textStatus);
		}	
	 });	
}

function dipslayDarkShow(obj) {
	
	$(obj).attr('readonly', false);
	$(obj).removeClass('darks');

	if($(obj).attr('data-msg')) {
		$(obj).attr('required', true);
		iconType = '';
		if($(obj).hasClass('only_num_format') || $(obj).hasClass('only_num')) {
			iconType = 'Num';
		}
		else {			
			if($(obj).parent().is('div')) iconType = 'Div';
			else if($(obj).parent().is('p')) iconType = 'P';
		}
		$(obj).parent().append('<p class="requireIcon' + iconType + '"><i class="fas fa-pen-square masterTooltip" title="필수 입력사항 입니다."></i></p>');			
	}	
}

function dipslayDarkHide(obj) {
	$(obj).attr('readonly',true);
	$(obj).addClass('darks');

	if($(obj).attr('data-msg')) {
		$(obj).attr('required',false);
		$(obj).parent().find('.requireIconDiv, .requireIconP').remove();	
	}
}

function getCombinations(arr, n) {
	var n = (n || arr.length);
	var ret = [];
	for (var i = 0; i < arr.length; i++) {
		var elem = (n == 1) ? arr[i] : arr.shift();
		for (var j = 0; j < elem.length; j++) {
			if (n == 1) {
				ret.push([elem[j]]);
			} 
			else {
				var childperm = getCombinations(arr.slice(), n - 1);
				for (var k = 0; k < childperm.length; k++) {
					ret.push([elem[j]].concat(childperm[k]));
				}
			}
		}
	}
  return ret;
}

var tmp_obj = null;
function topMenuOver(obj, sub){		
	if(tmp_obj!=obj) {
		$(obj).children("a").children("i").animate({top:'-=10px'}, "fast").animate({top:'+=10px'}, "fast").animate({top:'-=3px'}, "fast").animate({top:'+=3px'}, "fast");
	}
	$(obj).children("center").children("p").stop().animate({width: 'show'});		

	$('.subMenuNavi').stop().slideDown(300,(function(){ $(obj).children("a").removeClass("colorWhite").addClass("colorTopOver"); }));
		
	var ttl = $(obj).attr("data-ttl");
		
	$('.topMenu').find('.subMenu').each(function(i) {
		$t = jQuery(this);

		if($t.attr("data-ttl") == ttl) {
			$t.stop().animate({'background-color':'#f1f0ef'},'100');
			$(obj).stop().animate({'background-color':'#c04706'},'100');
			return false;
		}
	});
	
	if($select_menu != null) {
		if($(obj).children("a").html().indexOf(menu_title) == -1) {;
			$select_menu.css({background:'#f48042'}).children("a").removeClass("colorTopOver").addClass("colorWhite");
			$select_menu.children("center").children("p").stop().animate({width: 'hide'})
		}	
	}
	tmp_obj = obj;
}

function topMenuOut(obj){		
	tmp_obj = obj;
	
	$(obj).children("a").children("i").finish().animate({top:'0px'}, "fast");
	$(obj).children("a").removeClass("colorTopOver").addClass("colorWhite");
	$(obj).stop().animate({'background-color':'#f48042'},'100');
	$(obj).children("center").children("p").stop().animate({width: 'hide'});
	$('.subMenuNavi').stop().slideUp(300,secMenu);

	var ttl = $(obj).attr("data-ttl");

	$('.topMenu').find('.subMenu').each(function(i) {
		$t = jQuery(this);

		if($t.attr("data-ttl") == ttl) {
			$t.stop().animate({'background-color':'#fff'},'100');			
			return false;
		}
	});
}

function rightBoxHoldWidgetShow(){	
	$('#menuTitle').css({opacity:0.1, display:"inline-block", left:'-50px'}).stop().animate({left:'0px', opacity:1.0}, 'fast');
	$('#menuTitleBar').css({"width":0}).animate({width: $('#menuTitle').css('width')});
	$('.menuSubList').css({opacity:0.1, display:"block", left:'-50px'}).stop().animate({left:'0px', opacity:1.0}, 'fast');
	$('#widgetToggle').children('p').html('위젯설정닫기');
	$('#widgetToggle').children('i').removeClass("xi-apps").addClass("xi-close");	
}

function rightBoxHoldWidgetHide(){
	$('#widgetToggle').children('p').html('위젯설정열기');
	$('#widgetToggle').children('i').removeClass("xi-close").addClass("xi-apps");	
}

function rightBoxHoldMenuShow(){	
	$('#menuTitle').css({opacity:0.1, display:"inline-block", left:'-50px'}).stop().animate({left:'0px', opacity:1.0}, 'fast');
	$('#menuTitleBar').css({"width":0}).animate({width: $('#menuTitle').css('width')});
	$('.menuSubList').css({opacity:0.1, display:"block", left:'-50px'}).stop().animate({left:'0px', opacity:1.0}, 'fast');
	$('#menuToggle').children('p').html('메뉴닫기');
	$('#menuToggle').children('i').removeClass("xi-list-dot").addClass("xi-close");	
}

function rightBoxHoldMenuHide(){
	$('#menuToggle').children('p').html('메뉴열기');
	$('#menuToggle').children('i').removeClass("xi-close").addClass("xi-list-dot");	
}


function secMenu() {
	if($select_menu==null) return;
	$select_menu.children("a").removeClass("colorWhite").addClass("colorTopOver");	
	$select_menu.children("center").children("p").animate({width: 'show'});
	$select_menu.animate({'background-color':'#c04706'},'100');
	tmp_obj = null;
}

function resetTooltip() {
	$(".masterTooltip").tooltip({
		show: null,
		position: {
			my: "left-10 top",
			at: "left bottom"
		},
		open: function( event, ui ) {			
			ui.tooltip.finish().css({top:ui.tooltip.position().top + 5}).animate({ top: ui.tooltip.position().top + 10, opacity:'1'}, "fast" );
		}
    });

	$(".masterTooltipR").tooltip({
		show: null,
		position: {
			my: "right+10 top",
			at: "right bottom"
		},
		open: function( event, ui ) {			
			ui.tooltip.finish().css({top:ui.tooltip.position().top + 5}).animate({ top: ui.tooltip.position().top + 10, opacity:'1'}, "fast" );
		}
    });

	$(".masterTooltipM").tooltip({
		show: null,
		position: {
			my: "right+10 top",
			at: "right bottom"
		},
		open: function( event, ui ) {			
			ui.tooltip.finish().css({top:ui.tooltip.position().top + 5}).animate({ top: ui.tooltip.position().top + 10, opacity:'1'}, "fast" );
		}
    });

	$(".masterTooltipWidget").tooltip({
		show: null,
		position: {
			my: "left-5 bottom",
			at: "left top"
		},
		open: function( event, ui ) {			
			ui.tooltip.finish().css({top:ui.tooltip.position().top - 4}).animate({ top: ui.tooltip.position().top - 7, opacity:'1'}, "fast" );
		}
    });		
}

function resetContent() {
	$('.content').find('.contentTitle').each(function(i) {
		var $t = jQuery(this);		
		var position = $t.position();
		var title = $t.text(); 

		contentPosition[i] = position.top + 160;		

	});	

	if($('.btnSubmitBox').length) {
		var ck = 0;
		if($('.btnSubmitBox').hasClass("bottomHoldFix")) {
			$('.btnSubmitBox').removeClass("bottomHoldFix");
			ck = 1;
		}
		btnSubmitBoxPositionTop = $('.btnSubmitBox').offset().top;	
		if(ck == 1) $('.btnSubmitBox').addClass("bottomHoldFix");
	}
}

function ckCookie() {
	if($.cookie('mallUrl')) {			
		$.removeCookie("mallUrl",	{ path: '/' });
		$.removeCookie("mallPage",	{ path: '/' });
		$.removeCookie("mallSort",	{ path: '/' });
		$.removeCookie("mallLimit", { path: '/' });		
	}
}

var contentPosition = new Array();
var contentIcon = new Array("one", "two", "three", "four", "five", "six");
var btnSubmitBoxPositionTop = 0;


function checkAllRe() {
	$("#check_all").unbind('click').bind('click',function(e) {
		var checkeds = $(this).prop("checked");
		$("form[name=listForm]").find("input:checkbox").each(function(i) {
			$t = jQuery(this);
			if(!$t.prop('disabled')) {
				if(checkeds) $t.prop("checked", true);
				else $t.prop("checked", false);
			}
		});
	});		
}

function imageView(img, img_name) {
	var oimg = document.createElement("img");	
	oimg.src = img;
	oimg.style.width = '100%'
		
	alertify.imgDialog || alertify.dialog('imgDialog',function(){
		return {
			main:function(img, img_name, img_width ){
				this.setContent(img);						
				this.setHeader(img_name);		
				if(parseInt(img_width)>1000) img_width = 1000;
				this.elements.dialog.style.width = img_width + 'px';
			},
			setup:function(){
				return {
					options:{							
						maximizable:false,
					}
				};
			},
			build:function(){           					
				this.elements.body.style.minHeight = screen.height * .6 + 'px';								
			}
		};
	});

	alertify.imgDialog(oimg, img_name, oimg.width);
}

var iframeWidth = '1058px';
var iframeHeight = '.9';

function iframeView(url, title) {
	alertify.iframeDialog || alertify.dialog('iframeDialog',function(){
		var iframe;
		return {
			main:function(url, title){							
				this.setHeader(title);
				return this.set({ 
					'url': url
				});
			},
			setup:function(){
				return {
					options:{						
						padding : !1,
						overflow: !1,
					}
				};
			},
			build:function(){           
				// create the iframe element
				iframe = document.createElement('iframe');
				iframe.frameBorder = "no";
				iframe.width = "100%";
				iframe.height = "100%";
				// add it to the dialog
				this.elements.content.appendChild(iframe);								
				this.elements.body.style.minHeight = window.innerHeight * iframeHeight + 'px';
				this.elements.dialog.style.width = iframeWidth;
			},			 
			settings:{
				url:undefined
			},
			settingUpdated:function(key, oldValue, newValue){
				switch(key){
				   case 'url':
						iframe.src = newValue;
					break;   
				}
			},
			hooks: {
			  onclose: function() {
				return setTimeout((function() {
				  return alertify.iframeDialog().destroy();
				}), 400);
			  }
			}
		};
	});
	
	alertify.iframeDialog(url, title).set({frameless:false});	
}

function listFormCheck() {
	var ret = [];
	$("form[name=listForm] input[name='item[]']:checkbox:checked").each(function(i) {
		ret.push($(this).val());
	});

	if(ret.length == 0) {
		alertify.alert("선택된 항목이 없습니다.")
		return false;
	}			
	return ret;
}

function getCateSubInfo(cate, cate_dep, form) {
	if(!cate) return;
	if(!form) form = "searchForm";
	
	var data = new FormData();
	data.append('cate', cate);
	
	$.ajax({
			url: '../goods/cate_sub_info_json.php', 
			type: 'POST',
			data: data, 
			cache: false,
			dataType: 'json',
			processData: false, 
			contentType: false, 
			success: function(data, textStatus, jqXHR) {					
				if(typeof(data.error) === 'undefined') {						
					
					var insertCode = '';
					var num = parseInt(cate_dep) + 1;
					
					$.each(data, function(key, value) {
						newOption = $('<option/>', {  
							value: data[key].id,
							text: data[key].name
						});
						$("form[name=" + form + "] select[name='cate" + num + "']").append(newOption);
					});						
					$("form[name=" + form + "] select[name='cate" + num + "']").heapbox("update");

					if(cate_select[num]) {						
						$("form[name=" + form + "] select[name='cate" + num + "']").val(cate_select[num]).heapbox('update');
						cateChange(cate_select[num], num, form);							
						cate_select[num] = '';
					}	
				}
				else {
					alertify.error(data.error);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alertify.error(textStatus);
			}		
		});
}

function cateChange(value, num, form) {
	if(!form) form = "searchForm";	
	num = parseInt(num);
	for(i = (1 + num); i < 5; i++) {
		$("form[name=" + form + "] select[name='cate" + i + "']").empty();
		$("form[name=" + form + "] select[name='cate" + i + "']").append($('<option/>', { value: '', text: i + '차분류 선택' }));
		$("form[name=" + form + "] select[name='cate" + i + "']").heapbox("update");
	}
	if(value) getCateSubInfo(value, num, form);
	$("form[name=" + form + "] input[name='cate']").val(value);
}

function ckScrollContent() {
	var scrollTop = $(window).scrollTop();
	var scrollBottom = $(document).height() - $(window).scrollTop() - $(window).height();
	var ck_last = 0;
	
	if(scrollBottom<60) {
		for(i = 0, cnt = contentPosition.length; i < cnt; i++) {
			$('#contentTitle' + i).removeClass('hover');
		}
		$('#contentTitle' + (i-1)).addClass('hover');
	}
	else {
		for(i = 0, cnt = contentPosition.length; i < cnt; i++) {
			$('#contentTitle' + i).removeClass('hover');
			if(i==(cnt-1)) ck_last = $('#copyright').position().top;
			else ck_last = contentPosition[i+1];

			if(scrollTop >= contentPosition[i] && scrollTop <  ck_last) {
				$('#contentTitle' + i).addClass('hover');
			}	
		}
	}
}

function priceLimit(price) {

	type1 = goods_price_limit1;
	type2 = goods_price_limit2;
	
	if(type1 == 0 || type2 == 0) return price;
	
	var rtn = 0;		
	price = str_replace(",","", price);

	if(type2==1) {
		rtn = Math.floor(price / (10 * type1));
		rtn = rtn * (10 * type1);
	}
	else if(type2==2) {
		rtn = Math.round(price / (10 * type1));
		rtn = rtn * (10 * type1);
	}
	else if(type2==3) {
		rtn = Math.ceil(price / (10 * type1));
		rtn = rtn * (10 * type1);
	}

	return number_format(rtn);
}

function priceLimit2(price) {

	type1 = goods_price_limit1;
	type2 = goods_price_limit2;

	if(type1 == 0 || type2 == 0) return price;
	
	var rtn = 0;		

	if(type2==1) {
		rtn = Math.floor(price / (10 * type1));
		rtn = rtn * (10 * type1);
	}
	else if(type2==2) {
		rtn = Math.round(price / (10 * type1));
		rtn = rtn * (10 * type1);
	}
	else if(type2==3) {
		rtn = Math.ceil(price / (10 * type1));
		rtn = rtn * (10 * type1);
	}

	return rtn;
}

var ckAnimateLoop1 = 0;
function animateLoop1() {
	if(ckAnimateLoop1 == 0) {
		$('.box_down').animate({top : '30px'}, "fast").animate({top : '25px'}, "fast", animateLoop1);
	}
	else {
		$('.box_down').finish().css({top : '25px'});
	}
}

function putImage(src, num, callBack) {
	
	var image = new Image();
	var pDiv = $("#image" + num).parent();
	
	$("input[name='image" + num + "_del']").val(0);	
	$("#image" + num).attr("src", src).fadeIn(100);
	$("#btn_image" + num).hide();
	$("#btn2_image" + num).fadeIn(100);
	$('.inputMessage, .inputMessage_arrow').fadeOut('200').remove();

	image.src = src;
	image.onload = function(){ 
		var owidth = parseInt(image.width);
		var oheight = parseInt(image.height);

		$("#imgSize" + num).html(owidth + 'px X ' + oheight + 'px');
	
		if(owidth > 1000) {			
			var width = 1000;
			var height = (oheight * width) / owidth;
		}
		else {
			var height = parseInt($(pDiv).css('height'));
			var width = (owidth * height) / oheight;
		}
		
		$(pDiv).css({'width' : width, 'height' : height});
		if(callBack) eval(callBack + "()");
	}		
}

function searchResultLoad() {
	
	var data	= new FormData();
	var keyword = $("form[name=topSearchForm] input[name='keyword']").val();
	data.append('keyword', keyword);
	
	$.ajax({
		url: '../main/search_result.php', 
		type: 'POST',
		data: data, 
		cache: false,
		dataType: 'json',
		processData: false, 
		contentType: false, 
		success: function(data, textStatus, jqXHR) {
			if(typeof(data.error) === 'undefined') {
				$.each(data, function(key, value) {
					$('.searchBox .searchResult').html(data[key].listHtml);
					$(".searchBox .searchResultBox").css("height", window.innerHeight - 240);	
					$(".topResetIconBtn").show();

					$(".searchBox .searchResult").find('.btnCrm').unbind('click').bind('click',function(e) {
						var id = $(this).attr("data-id");
						iframeWidth = '950px';
						iframeHeight = '.8';
						iframeView("../member/popup_member_crm.php?id=" + id, "회원 CRM보기");			
					});					
				});				
			}
			else {
				alertify.error(data.error);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {						
			alertify.error(textStatus);
		}		
	});

}

function searchDefaultLoad() {
	
	$.ajax({
		url: '../main/site_map.php', 
		type: 'POST',		
		cache: false,
		dataType: 'json',
		processData: false, 
		contentType: false, 
		success: function(data, textStatus, jqXHR) {
			if(typeof(data.error) === 'undefined') {
				$.each(data, function(key, value) {
					$('.searchBox .searchResult').html(data[key].listHtml);
					$(".searchBox .searchResultBox").css("height", window.innerHeight - 240);						
					$('.masonry-grid').masonry({ itemSelector: '.masonry-grid-item' });
				});				
			}
			else {
				alertify.error(data.error);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {						
			alertify.error(textStatus);
		}		
	});

}

/** @preserve jQuery animateNumber plugin v0.0.14
 * (c) 2013, Alexandr Borisov.
 * https://github.com/aishek/jquery-animateNumber
 */

// ['...'] notation using to avoid names minification by Google Closure Compiler
(function($) {
  var reverse = function(value) {
    return value.split('').reverse().join('');
  };

  var defaults = {
    numberStep: function(now, tween) {
      var floored_number = Math.floor(now),
          target = $(tween.elem);

      target.text(floored_number);
    }
  };

  var handle = function( tween ) {
    var elem = tween.elem;
    if ( elem.nodeType && elem.parentNode ) {
      var handler = elem._animateNumberSetter;
      if (!handler) {
        handler = defaults.numberStep;
      }
      handler(tween.now, tween);
    }
  };

  if (!$.Tween || !$.Tween.propHooks) {
    $.fx.step.number = handle;
  } else {
    $.Tween.propHooks.number = {
      set: handle
    };
  }

  var extract_number_parts = function(separated_number, group_length) {
    var numbers = separated_number.split('').reverse(),
        number_parts = [],
        current_number_part,
        current_index,
        q;

    for(var i = 0, l = Math.ceil(separated_number.length / group_length); i < l; i++) {
      current_number_part = '';
      for(q = 0; q < group_length; q++) {
        current_index = i * group_length + q;
        if (current_index === separated_number.length) {
          break;
        }

        current_number_part = current_number_part + numbers[current_index];
      }
      number_parts.push(current_number_part);
    }

    return number_parts;
  };

  var remove_precending_zeros = function(number_parts) {
    var last_index = number_parts.length - 1,
        last = reverse(number_parts[last_index]);

    number_parts[last_index] = reverse(parseInt(last, 10).toString());
    return number_parts;
  };

  $.animateNumber = {
    numberStepFactories: {
      /**
       * Creates numberStep handler, which appends string to floored animated number on each step.
       *
       * @example
       * // will animate to 100 with "1 %", "2 %", "3 %", ...
       * $('#someid').animateNumber({
       *   number: 100,
       *   numberStep: $.animateNumber.numberStepFactories.append(' %')
       * });
       *
       * @params {String} suffix string to append to animated number
       * @returns {Function} numberStep-compatible function for use in animateNumber's parameters
       */
      append: function(suffix) {
        return function(now, tween) {
          var floored_number = Math.floor(now),
              target = $(tween.elem);

          target.prop('number', now).text(floored_number + suffix);
        };
      },

      /**
       * Creates numberStep handler, which format floored numbers by separating them to groups.
       *
       * @example
       * // will animate with 1 ... 217,980 ... 95,217,980 ... 7,095,217,980
       * $('#world-population').animateNumber({
       *    number: 7095217980,
       *    numberStep: $.animateNumber.numberStepFactories.separator(',')
       * });
       * @example
       * // will animate with 1% ... 217,980% ... 95,217,980% ... 7,095,217,980%
       * $('#salesIncrease').animateNumber({
       *   number: 7095217980,
       *   numberStep: $.animateNumber.numberStepFactories.separator(',', 3, '%')
       * });
       *
       * @params {String} [separator=' '] string to separate number groups
       * @params {String} [group_length=3] number group length
       * @params {String} [suffix=''] suffix to append to number
       * @returns {Function} numberStep-compatible function for use in animateNumber's parameters
       */
      separator: function(separator, group_length, suffix) {
        separator = separator || ' ';
        group_length = group_length || 3;
        suffix = suffix || '';

        return function(now, tween) {
          var negative = now < 0,
              floored_number = Math.floor((negative ? -1 : 1) * now),
              separated_number = floored_number.toString(),
              target = $(tween.elem);

          if (separated_number.length > group_length) {
            var number_parts = extract_number_parts(separated_number, group_length);

            separated_number = remove_precending_zeros(number_parts).join(separator);
            separated_number = reverse(separated_number);
          }

          target.prop('number', now).text((negative ? '-' : '') + separated_number + suffix);
        };
      }
    }
  };

  $.fn.animateNumber = function() {
    var options = arguments[0],
        settings = $.extend({}, defaults, options),

        target = $(this),
        args = [settings];

    for(var i = 1, l = arguments.length; i < l; i++) {
      args.push(arguments[i]);
    }

    // needs of custom step function usage
    if (options.numberStep) {
      // assigns custom step functions
      var items = this.each(function(){
        this._animateNumberSetter = options.numberStep;
      });

      // cleanup of custom step functions after animation
      var generic_complete = settings.complete;
      settings.complete = function() {
        items.each(function(){
          delete this._animateNumberSetter;
        });

        if ( generic_complete ) {
          generic_complete.apply(this, arguments);
        }
      };
    }

    return target.animate.apply(target, args);
  };

}(jQuery));


$(function() {

	$('.imageBtn').on('change', function(e){
		
		if(!CommonCheckImage($(this))) return;

		if(e.target.files[0]) {			
			var num = $(this).attr("data-num");
			var reader = new FileReader();
			reader.onload = function (e) {
				var image = new Image();
				image.src = e.target.result;

				image.onload = function(){        						
					putImage(this.src, num);
				};                      
			}
			reader.readAsDataURL(e.target.files[0]);
		}
	});

	$('.btnImgEdit').click(function(e){
		var num = $(this).parent().attr("data-num");
		$("input[name='image" + num + "']").trigger('click');
	});

	$('.btnImgDel').click(function(e){

		var num = $(this).parent().attr("data-num");
		var pDiv = $("#image" + num).parent();

		$("input[name='image" + num + "']").val('');
		$("input[name='image" + num + "_del']").val(1);

		$("#image" + num).attr("src", "../common/img/blank.gif").hide();
		$("#btn2_image" + num).hide();
		$("#btn_image" + num).fadeIn(100);
		$("#imgSize" + num).html('');
		$(pDiv).css({'width' : 400});

		if($("input[name='image" + num + "']").attr("iconPos")) {
			$("input[name='image" + num + "']").attr('required',true);	
		}

	});	

});	