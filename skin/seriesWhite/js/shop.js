function textMenus(id) {	
    
	this.ids = id;
	
	this.init = function() {		
		var self = (this)? this : '';

		$('#' + this.ids).find('li').each(function(i) {
			var obj = $(this).children();
			obj.attr('cks', i);
			obj.hover(function(e){				
				self.mover($(this).attr('cks'));
			}, 
			function() {
				self.mout($(this).attr('cks'));
			});	
		});
	}

	this.mover = function(secNum) {	
		
		$('#' + this.ids).find('li').each(function(i) {
			var obj = $(this).children();
			if(obj.attr('cks') != secNum) obj.css({opacity:1}).stop().animate({opacity:0.4}, 'fast');
		});

	}   

	this.mout = function(secNum) {		
		
		$('#' + this.ids).find('li').each(function(i) {
			var obj = $(this).children();
			if(obj.attr('cks') != secNum) obj.css({opacity:0.4}).stop().animate({opacity:1}, 'fast');
		});
		
	}   	

	this.init();

}

function bookmarksite(url,title){
	if (navigator.appName=="Netscape") { 
		alert("확인을 클릭 후 , <Ctrl-D>키를 눌러 즐겨찾기에 등록하실 수 있습니다."); 
	}
	else if(window.opera && window.print){ // opera
		var elem = document.createElement('a');
		elem.setAttribute('href',url);
		elem.setAttribute('title',title);
		elem.setAttribute('rel','sidebar');
		elem.click();
	}
	else if(document.all){	// ie
		window.external.AddFavorite(url, title);
	}
	else if (window.sidebar && window.sidebar.addPanel){	// firefox
		window.sidebar.addPanel(sidebartitle, sidebarurl,""); 
	}
}	

function addFavoriteGoods(uid) {
	if(!uid) return;
	
	var data = new FormData();
	data.append('uid', uid);

	$.ajax({
		url: 'php/favorite_goods_json.php', 
		type: 'POST',	
		data: data, 
		cache: false,
		dataType: 'json',
		processData: false, 
		contentType: false, 
		success: function(data, textStatus, jqXHR) {
			if(typeof(data.error) === 'undefined') {
				var cnt = parseInt(str_replace(",", "", $(".favGoodsCnt").html()));
				if(data.success == 1) {					
					$(".favGoodsCnt").html(number_format(cnt + 1));
					$(".btnFavGoods").addClass("colorOrange");
					alertify.success("관심상품에 등록되었습니다.");
				}
				else {
					$(".favGoodsCnt").html(number_format(cnt - 1));
					$(".btnFavGoods").removeClass("colorOrange");
					alertify.success("관심상품에서 삭제되었습니다.");
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

function addFavoriteGoods2(uid) {
	if(!uid) return;
	
	var data = new FormData();
	data.append('uid', uid);

	$.ajax({
		url: 'php/favorite_goods_json.php', 
		type: 'POST',	
		data: data, 
		cache: false,
		dataType: 'json',
		processData: false, 
		contentType: false, 
		success: function(data, textStatus, jqXHR) {
			if(typeof(data.error) === 'undefined') {
				if(data.success == 1) {					
					$(".wish_" + uid).addClass("colorOrange");
					alertify.success("관심상품에 등록되었습니다.");
				}
				else {
					$(".wish_" + uid).removeClass("colorOrange");
					alertify.success("관심상품에서 삭제되었습니다.");
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

function addFavoriteStore(vendor) {
	if(!vendor) return;
	
	var data = new FormData();
	data.append('vendor', vendor);

	$.ajax({
		url: 'php/favorite_store_json.php', 
		type: 'POST',	
		data: data, 
		cache: false,
		dataType: 'json',
		processData: false, 
		contentType: false, 
		success: function(data, textStatus, jqXHR) {
			if(typeof(data.error) === 'undefined') {
				var cnt = parseInt(str_replace(",", "", $(".favStoreCnt").html()));
				if(data.success == 1) {					
					$(".favStoreCnt").html(number_format(cnt + 1));
					$(".btnFavStore").addClass("colorOrange");
					alertify.success("관심스토어에 등록되었습니다.");
				}
				else {
					$(".favStoreCnt").html(number_format(cnt - 1));
					$(".btnFavStore").removeClass("colorOrange");
					alertify.success("관심스토어에서 삭제되었습니다.");
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

function swiperNaviHS(name) {
	
	$(name + " .swiper-button-next").hide();
	$(name + " .swiper-button-prev").hide();
	$(name + " .swiper-container").hover(function(e){
		$(name + " .swiper-button-next").show();
		$(name + " .swiper-button-prev").show();
	}, 
	function() {
		$(name + " .swiper-button-next").hide();
		$(name + " .swiper-button-prev").hide();
	});
}

function recentHide() {
	$('#rightBoxHoldMenu').hide();
	check_recent_view = 0;
}

/*
'ㄱ','ㄲ','ㄴ','ㄷ','ㄸ','ㄹ','ㅁ','ㅂ','ㅃ','ㅅ','ㅆ','ㅇ','ㅈ','ㅉ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ';//초성 19개 
'ㅏ','ㅐ','ㅑ','ㅒ','ㅓ','ㅔ','ㅕ','ㅖ','ㅗ','ㅘ','ㅙ','ㅚ','ㅛ','ㅜ','ㅝ','ㅞ','ㅟ','ㅠ','ㅡ','ㅢ','ㅣ';//중성 21개 
'ㄱ','ㄲ','ㄳ','ㄴ','ㄵ','ㄶ','ㄷ','ㄹ','ㄺ','ㄻ','ㄼ','ㄽ','ㄾ','ㄿ','ㅀ','ㅁ','ㅂ','ㅄ','ㅅ','ㅆ','ㅇ','ㅈ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ');//종성 28개 
*/

function getJamoCodes(t) { 
    var c = t.charCodeAt(0), c = c<0x3130?0:c<0x3164?c-0x3130:c<0xac00?0:c<0xd7a5?c+68:0; 
	var ck_arr = Array('','1','2','1,10','3','3,13','3,19','4','6','6,1','6,7','6,8','6,10','6,17','6,18','6,19','7','8','8,10','10','11','12','13','15','16','17','18','19');
    if (c>51) arr_var = Array((c-c%588)/588-74,((c-c%28)/28)%21+1,c%28); 
    else arr_var =  Array(c<3?c:c<4?0:c<5?c-1:c<7?0:c<10?c-3:c<17?0:c<20?c-10:c<21?0:c<31?c-11:0,c<31?0:c-30,0); 
	
	if(arr_var[0]==0) return '';
	rtn_value = arr_var[0];

	if(arr_var[1]>0) {
		rtn_value = rtn_value +","+(arr_var[1]);	
		if(arr_var[2]) {		
			if(ck_arr[arr_var[2]]) arr_var[2] = ck_arr[arr_var[2]];		
			rtn_value = rtn_value +","+(arr_var[2]);
		}
	}
	return rtn_value;
} 

function genericDialogView(obj, title) {

	alertify.genericDialog || alertify.dialog('genericDialog',function(){
		return {
			main:function(content, title){
				this.setHeader(title);
				content.style.display = 'block';
				this.setContent(content);
			},

			build:function(){           
				this.elements.body.style.minHeight = "500px"
				this.elements.dialog.style.width = "800px"
			},
			
		};
	});
	
	alertify.genericDialog (obj, title);

}

function genericDialogView2(obj, title) {

	alertify.genericDialog2 || alertify.dialog('genericDialog2',function(){
		return {
			main:function(content, title){
				this.setHeader(title);
				content.style.display = 'block';
				this.setContent(content);
			},

			build:function(){           
				this.elements.body.style.minHeight = "500px"
				this.elements.dialog.style.width = "800px"
			},
			
		};
	});
	
	alertify.genericDialog2 (obj, title);

}

/*
var countTime1		= "";
var countTime2		= "";
var countObj		= "";
var countCallBack	= "";
var countStop		= "0";
*/

function countTime(){
	if(!countTime1) return;
	
	countTime2	++;	
	ck =		 1;
	
	lastTime = countTime1 - countTime2;	
	
	if(lastTime >= 0) {
		lastTime2 = lastTime;
		hour = Math.floor(lastTime2/3600);
		lastTime2 -=  hour * 3600;

		min  = Math.floor(lastTime2/60);
		lastTime2 -=  min * 60;

		sec = Math.floor(lastTime2); 

		hour	= hour>9 ?	hour + ''	: '0' + hour;
		min		= min>9 ?	min + ''	: '0' + min;
		sec		= sec>9 ?	sec + ''	: '0' + sec;
	
		$(countObj).html(min + '분 ' + sec + '초');
		
		if(lastTime == 0) {
			$(countObj).html('시간만료');
			if(countCallBack) eval(countCallBack + "()");
			ck = 0;
		}
	}
			
	if(ck == 1 && countStop == 0) timerID  = setTimeout(countTime, 1000);
}	


function snUpImage(files, editor, type, b_id) {
	var data = new FormData();
	
	$.each(files, function(key, value) {
		data.append(key, value);
	});

	data.append('type', type);
	data.append('b_id', b_id);

	$.ajax({
		url: 'board/sn_image_post_json.php?files', 
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
				var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
				this.elements.body.style.minHeight = parseInt(h) * iframeHeight + 'px';
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

function snsShare(snsName, link, title) {

	if (title === null) return false;

	var snsPopUp;
	var _width = '500';
	var _height = '450';
	var _left = Math.ceil(( window.screen.width - _width )/2);
	var _top = Math.ceil(( window.screen.height - _height )/2);

	switch(snsName){
		case 'facebook':
			snsPopUp = window.open("http://www.facebook.com/sharer/sharer.php?u=" + link, '', 'width='+ _width +', height='+ _height +', left=' + _left + ', top='+ _top);
		break;

		case 'twitter' :
			snsPopUp = window.open("http://twitter.com/intent/tweet?url=" + link + "&text=" + title, '', 'width='+ _width +', height='+ _height +', left=' + _left + ', top='+ _top);
		break;

		case 'kakao' :
			snsPopUp = window.open("https://story.kakao.com/share?url=" + link, '', 'width='+ _width +', height='+ _height +', left=' + _left + ', top='+ _top);
		break;		
	}
}


;(function ( $, window, document, undefined ) {

    var pluginName = "autocompleteKeyword",
        defaults = {
			parentDiv: null,
			recentDiv: null,
			btnSearch: null
		};
		

    function Plugin( element, options ) {        
	    /* Settings */
	    this.element	= element;
        this.options	= $.extend( {}, defaults, options );
        this._name		= pluginName;
		this.parentDiv	= $(this.options.parentDiv);
		this.recentDiv	= $(this.options.recentDiv);
		this.btnSearch	= $(this.options.btnSearch);
		this.secNum		= 0;
		this.totalNum	= 0;
		this.changes	= 0;
		this.ckOver		= 0;
		this.tmpRtns	= null;
		this.tmpVls		= null;
	    this.init();
    }

	Plugin.prototype = {

		/*
		 * autocompleteKeyword init
		*/
		init: function() {       

			this.instance = this.createInstance();		
			this.createElements();
			this.bindEvents();
			
		},

		/*
		*  Generate new ID for Table
		*/
		createInstance: function() {

			 return $(this.element).attr('id') || Math.round(Math.random() * 99999999);
		 },
		
		 createElements: function() {
			var self = this;
			
			autocompleteBox = $('<div/>', {  
				id: 'autocompleteBox_'+this.instance.heapId,
				'class': 'autocompleteBox'
			});

			this.parentDiv.append(autocompleteBox);
		},

		bindEvents: function() {
			var self = this;

			$('body').on('click', function(e){ self.blurHandler(self) } );
			$(this.element).on('focus', function(e){ self.focusHandler(self) } );
			$(this.element).on('keyup', function(e){ self.keyupHandler(e, self) } );			
			this.btnSearch.on('click', function(e){ self.brnSearchHandler(self) } );

			this.parentDiv.hover(function(e) {		
				self.ckOver = 1;
			}, function() {
				self.ckOver = 0;
			});			

		},     

		blurHandler: function(obj) {			
			if(obj.ckOver == 0) obj.recentDiv.hide();
			else {
				$(obj.element).focus();
			}
		},

		focusHandler: function(obj) {
			obj.recentDiv.slideDown('fast');					
		},

		brnSearchHandler: function(obj) {
			if($(obj.element).val()) $(obj.element).parents('form').submit();
			else {
				$(obj.element).focus();
			}
		},
		
		keyupHandler : function(e, obj) {
			var val = $(obj.element).val();
			if(!val) {
				obj.defaultSet();
				return;
			}

			val			= val.toLowerCase(); 
			obj.changes = 0;

			switch (eval(e.keyCode)) {
				case 40 : 
					
					if(obj.secNum == 0 || obj.secNum == obj.totalNum) {
						obj.secNum = 1;
					}
					else {						
						obj.secNum ++;
					}
						
					obj.changes = 1;				
				
				break;

				case 38 : 
					
					if(obj.secNum == 0 || obj.secNum == 1) {
						obj.secNum = obj.totalNum;
					}
					else {						
						obj.secNum --;
					}
						
					this.changes = 1;
				
				break;			

			}

			if(obj.changes == 1) {
				$('.autocompleteBoxItem').removeClass('hover');
				$('#autocompleteBox_' + obj.instance.heapId + 'Item' + obj.secNum).addClass('hover');
				$(obj.element).val($('#autocompleteBox_' + obj.instance.heapId + 'Item' + obj.secNum).text());
				return;					
			}
			
			if(this.tmpVls == val || jQuery.trim(val).length == 0) return;
			
			var rtns = '';
			for(i=0, cnt = val.length; i < cnt; i++){
				var ch = val.charAt(i);
				if(tmp = getJamoCodes(ch)) {						
					if(i == 0) rtns = tmp;	
					else rtns += ',' + tmp;			
				} 
				else rtns += ch;
			}	

			var data = new FormData();

			data.append('keyword', rtns);
			if(val == rtns) data.append('ints', 1); 

			$.ajax({
				url: 'php/auto_keyword_result_json.php', 
				type: 'POST',	
				data: data, 
				cache: false,
				dataType: 'json',
				processData: false, 
				contentType: false, 
				success: function(data, textStatus, jqXHR) {
					if(typeof(data.error) === 'undefined') {											
						$('#autocompleteBox_' + obj.instance.heapId).html('');
						obj.totalNum = 0;

						$.each(data, function(key, value) {
							obj.resultKeyword(data[key].keyword);							
						});
						
						if(obj.totalNum == 0) obj.defaultSet();
						
					}
					else {
						alertify.error(data.error);
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alertify.error(textStatus);
				}		
			});
						
			this.tmpVls = val;

		},

		resultKeyword : function(keyword) {
			var self = this;
			var val = $(this.element).val();

			this.totalNum++;
			
			hkeyword = keyword.substring(0, val.length).replace(eval('/(' + val + '){1,1}/gi'), '<font color="colorOrange">$1</font>');
			hkeyword += keyword.substring(val.length, keyword.length);

			keywordItem = $('<div/>', {  
				id: 'autocompleteBox_' + this.instance.heapId + 'Item' + this.totalNum,
				'class': 'autocompleteBoxItem',
			}).html(hkeyword).click(function(e) {
				$(self.element).val($(this).text());
				$(self.element).parents('form').submit();
			});
			
			$('#autocompleteBox_'+this.instance.heapId).append(keywordItem).show();			
		},

		defaultSet : function() {
			$('#autocompleteBox_' + this.instance.heapId).html('').hide();
			this.totalNum	= 0;
			this.secNum		= 0;
			this.tmpVls		= null;
		}
				
	}

	$.fn[pluginName] = function ( options, optional ) {

        return this.each(function () {	
			if (!$.data(this, "plugin_" + pluginName)) {
					$.data(this, "plugin_" + pluginName, new Plugin( this, options ));
			}
			else {				
				autocompleteKeywordInst = $.data(this, "plugin_" + pluginName);		

				switch(options) {
					case "add" :								
						autocompleteKeyword.add(optional);
					break;					
				}				
			}	
        });
    };

})( jQuery, window, document );


var naviTop				= null;
var check_recent_view	= 0;

$(function() {
	
	$(".menuMy").hover(function(e){
		$("#menuMy").removeClass('rotate180r').addClass('rotate180');
		$("#mypageBox").slideDown("fast");
	}, 
	function() {
		$("#menuMy").removeClass('rotate180').addClass('rotate180r');		
		$("#mypageBox").hide();		
	})

	$('.recentKeywordDel').each(function(i) {			
		recentKeywordTotal++;
	});

	$('.recentKeywordDel').click(function(e) {
		var self = this;

		var data = new FormData();
		data.append('uid', $(this).attr('data-uid'));

		$.ajax({
			url: 'php/recent_keyword_del_json.php', 
			type: 'POST',	
			data: data, 
			cache: false,
			dataType: 'json',
			processData: false, 
			contentType: false, 
			success: function(data, textStatus, jqXHR) {
				if(typeof(data.error) === 'undefined') {											
					$(self).parent().remove();
					recentKeywordTotal--;
					if(recentKeywordTotal == 0) {
						$('#recentKeywordEmpty').show();
						$('#recentKeywordReset').hide();
					}
					alertify.success("최근 키워드가 삭제 되었습니다.");
				}
				else {
					alertify.error(data.error);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alertify.error(textStatus);
			}		
		});			
		
	});

	$('#recentKeywordReset').click(function(e) {
		var self = this;

		var data = new FormData();
		data.append('uid', 'all');

		$.ajax({
			url: 'php/recent_keyword_del_json.php', 
			type: 'POST',	
			data: data, 
			cache: false,
			dataType: 'json',
			processData: false, 
			contentType: false, 
			success: function(data, textStatus, jqXHR) {
				if(typeof(data.error) === 'undefined') {											
					$('.recentItem').remove();
					$('#recentKeywordEmpty').show();
					$('#recentKeywordReset').hide();
					recentKeywordTotal = 0;
					alertify.success("최근 키워드가 모두 삭제 되었습니다.");
				}
				else {
					alertify.error(data.error);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alertify.error(textStatus);
			}		
		});

	});

	if(recentKeywordTotal == 0) {
		$('#recentKeywordEmpty').show();
		$('#recentKeywordReset').hide();
	}

	$('.cateAllOpen').click(function(e){
		if($('.cate_bar_2').css('opacity') == 0) {
			$('.cate_bar_1').removeClass('rotate225').addClass('rotate225r').animate({top:'0px'}, "fast");
			$('.cate_bar_3').removeClass('rotate315').addClass('rotate225r').animate({top:'0px'}, "fast");
			$('.cate_bar_2').animate({opacity:1}, "fast");
			$('#cateAllIn').slideUp('fast');
		}
		else {
			$('.cate_bar_1').removeClass('rotate225r').addClass('rotate225').animate({top:'8px'}, "fast");
			$('.cate_bar_3').removeClass('rotate225r').addClass('rotate315').animate({top:'-8px'}, "fast");
			$('.cate_bar_2').animate({opacity:0}, "fast");
			$('#cateAllIn').slideDown('fast');
		}
	});
	
	$('.cateAllClose').click(function(e){
		$('.cateAllOpen').trigger('click');
	});

	$("#topNavis .topNavi").hover(function(e){
		obj = $("#topNaviSub" + $(this).attr("data-num"));
		if(obj.length) obj.stop().slideDown("fast");
		
	}, 
	function() {
		$("#topNavis .naviSubBox").hide();
	});

	$('.btnCircle').hover(function(e){
		$(this).find('.btnCircleTitle').css({opacity:0.1, display:"block"}).stop().animate({opacity:1.0}, 'fast');			
	}, 
	function() {
		$(this).find('.btnCircleTitle').stop().animate({opacity:0}, 'fast');				
	});

	$('.btnCircle').first().click(function(e){
		$("html, body").animate({ scrollTop: 0 }, 100);
	});

	$('.btnCircle').last().click(function(e){
		var bottom = $(document).height() - $(window).height();
		$("html, body").animate({ scrollTop: bottom }, 100);
	});

	$('#btnQuickSearh').click(function(e){
		if($(this).hasClass("xi-search")) {
			$(this).removeClass("xi-search").addClass("xi-close-thin");
			$("#topSearch").addClass('topSearchFixed');
		}
		else {
			$(this).removeClass("xi-close-thin").addClass("xi-search");
			$("#topSearch").removeClass('topSearchFixed');
		}	

		naviTop = $('.naviDefault').position().top;
	});

	$('.btnShineBox .btnPrev').hover(function(e){
		$(this).css({color:'#333'});
		$('.btnPrevTitle').css({opacity:0.1, display:"block"}).stop().animate({opacity:1.0}, 'fast');			
	}, 
	function() {
		$(this).css({color:'#999'});
		$('.btnPrevTitle').stop().animate({opacity:0}, 'fast');				
	});

	$('.btnShineBox .btnPrev').click(function(e){
		window.history.back();
	});

	$('#btnQuickRecent').click(function(){ 
		var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
		$('#rightBoxHoldMenu').stop().show().animate({right:'+=304px'});
		$('#rightBoxHoldMenu .item').css({'height':(parseInt(h) - 51)});
		check_recent_view = 1;
	});

	$('#btnQuickCart').click(function(){ 
		window.location.href = "index.php?channel=cart";
	});

	$('.recentClose').click(function(){ 
		$('#rightBoxHoldMenu').animate({right:'-=304px'},'', recentHide);
	});

	$('.topUtil, .topContent, #contents').click(function() {
		if(check_recent_view == 1) {
			$('#rightBoxHoldMenu').animate({right:'-=304px'},'', recentHide);		
		}
	});

	$('.recentDel').click(function() {		
		var data = new FormData();
		var uid = $(this).attr("data-uid");
		data.append('uid', uid);

		$.ajax({
			url: 'php/goods_recent_view_del_json.php', 
			type: 'POST',	
			data: data, 
			cache: false,
			dataType: 'json',
			processData: false, 
			contentType: false, 
			success: function(data, textStatus, jqXHR) {
				if(typeof(data.error) === 'undefined') {
					alertify.success("최근본 상품에서 삭제되었습니다.");
					$("#recentView_" + uid).remove();
					if($("#recentView2_" + uid).length) $("#recentView2_" + uid).remove();
					$('.recent_view_cnt').html(parseInt($('.recent_view_cnt').html()) - 1);
				}
				else {
					alertify.error(data.error);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alertify.error(textStatus);
			}		
		});
	});
});


$(window).resize(function(){ 
	var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
	$('#rightBoxHoldMenu .item').css({'height':(parseInt(h) - 51)});

});

$(window).scroll(function () {
	if(!naviTop) {
		naviTop = 170;		
		if(topBannerU == 1) naviTop += 50;
	}
	
	var scrollTop = $(window).scrollTop();

	if(scrollTop > naviTop) {		
		if(topBannerU == 1) $('.naviDefault').removeClass("naviDefaultBU");
		$('.naviDefault').removeClass("naviDefault").addClass("naviFixed");
		$('#btnQuickMenu').fadeIn();
		$('#btnTopDown').fadeIn();	
	}
	else {
		$('.naviFixed').removeClass("naviFixed").addClass("naviDefault");
		if(topBannerU == 1) $('.naviDefault').addClass("naviDefaultBU");
		$('#btnQuickMenu').hide();
		$('#btnTopDown').fadeOut();
		if($('#btnQuickSearh').hasClass("xi-close-thin")) $('#btnQuickSearh').trigger('click');		
	}

	if($(".goods_explain .content_list").length) {

		if(typeof(goods_explain_pos) != 'undefined') {
			for(p = 1; p <= goods_explain_pos.length; p ++) {
				if(scrollTop > goods_explain_pos[p] - 82) { 
					$('.contentMenu .contentMenuSub').removeClass("selected");
					$('.contentMenu .goods_explain' + p).addClass("selected");
				}
			}			
		}

		if(typeof(menuPositionTop) != 'undefined') {

			if(scrollTop >  menuPositionTop) {	
				if(!$('.content_list').hasClass("content_listHoldFix")) {
					$('.content_list').addClass("content_listHoldFix");
					
					left = parseInt($('#contents').css('margin-left')) + fixdPositionLeft;
					$('.goods_fixed').addClass("goods_fixedHoldFix");
					$('.goods_fixed').css("left", left);

					$('.contentEmpty').show();
				}
				
				var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
				if(parseInt(scrollTop + parseInt(h)) > fixdPositionbottom) {
					tops =  parseInt($('.goods_fixed').css('height')) + fixdPositionTop;
					$('.goods_fixedHoldFix').css({"position" : "relative", "left" : 0, "top" : fixdPositionbottom - tops});				
				}
				else {
					if($('.goods_fixedHoldFix').css("position") == "relative") {
						left = parseInt($('#contents').css('margin-left')) + fixdPositionLeft;
						$('.goods_fixedHoldFix').css({"position" : "fixed", "left" : left, "top" : 50});
					}
				}
			}
			else {
				$('.content_list').removeClass("content_listHoldFix");
				$('.goods_fixed').removeClass("goods_fixedHoldFix");	
				$('.goods_fixed').css({"position" : "relative", "left" : 0, "top" : 0});
				$('.contentEmpty').hide();
			}
		}
	}

	if($(".cartSum").length) {

		if(typeof(sumPositionTop) != 'undefined') {
			
			if(parseInt($(".cartListIn").css("height")) - 30  > parseInt($('.cartSum').css("height"))) {

				if(scrollTop > sumPositionTop) {
					if(!$('.cartSum').hasClass("cartSumHoldFix")) {
						$('.cartSum').addClass("cartSumHoldFix");		
						left = parseInt($('#contents').css('margin-left')) + sumPositionLeft;
						$('.cartSum').css("left", left);				
					}		
					
					var h1	= parseInt($(".cartListIn").position().top) + parseInt($(".cartListIn").css("height")) - 30 + 222;
					var h2	= parseInt(scrollTop) + parseInt($('.cartSum').css('height')) + 20 + 65;
					var h3	= parseInt($(".cartListIn").css("height")) - 30;
					var h4	= parseInt($('.cartSum').css('height')) + 20;
					
					if(h1 < h2) {						
						$('.cartSum').css({"position" : "relative", "left" : 0, "top" : h3 - h4});				
					}
					else {
						if($('.cartSumHoldFix').css("position") == "relative") {
							left = parseInt($('#contents').css('margin-left')) + sumPositionLeft;
							$('.cartSumHoldFix').css({"position" : "fixed", "left" : left, "top" : 0});
						}
					}
				}
				else {
					$('.cartSum').removeClass("cartSumHoldFix");	
					$('.cartSum').css({"position" : "relative", "left" : 0, "top" : 0});
				}
			}
		}
	}	

	if($('.btnShineBox').length) {
			
		if(typeof(btnShineBoxPositionTop) != 'undefined') {

			var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
			if(btnShineBoxPositionTop +  contentBtnHeight < parseInt(scrollTop + parseInt(h))) {
				$('.btnShineBox').css({opacity : 1});
				$('.btnShineBox').removeClass("bottomHoldFix");	
				return;
			}

			if(scrollTop > naviTop) {		
				if(!$('.btnShineBox').hasClass("bottomHoldFix")) {
					$('.btnShineBox').addClass("bottomHoldFix");	
					$('.btnShineBox').css({opacity:0}).stop().animate({opacity : 0.8}, 'fast');					
				}		
				return;	
			}
			else {
				if($('.btnShineBox').css("opacity") >= 0.8) {
					$('.btnShineBox').stop().animate({opacity : 0}, 'fast',(function(){ $('.btnShineBox').css({opacity : 1}); $('.btnShineBox').removeClass("bottomHoldFix");}));						
				}
				return;		
			}
		}
	}	

});