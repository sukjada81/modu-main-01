
var recentKeywordTotal	= 0;
var mobile_option_open	= 0;
var tmp_ck_url			= "";
var tmp_iframe_url		= "";

window.addEventListener("hashchange", function(e) {	
	var ck_url = window.location.href.split("#");
	
	if(ck_url[1]) tmp_ck_url = ck_url[1]; 	
	else {		
		var back = 0;
		if(tmp_ck_url == 'topSearch') {
			if(parseInt($("#topSearch").css('right')) < -100) back = 1;
		}
		else if(tmp_ck_url == 'topMenu') {
			if(parseInt($("#topMenu").css('top')) > 100) back = 1;
		}	
		else if(tmp_ck_url == 'recentGoods') {
			if(parseInt($("#recentGoods").css('top')) > 100) back = 1;						
		}	
		else if(tmp_ck_url == 'review' || tmp_ck_url == 'frame') {			
			if(iframeViewStatus == 1) back = 1;
			else alertify.iframeDialog().destroy();
		}
		else if(tmp_ck_url == 'genericIframe1') {
			if(genericIframe1ViewStatus == 1) back = 1;
			else alertify.closeAll('genericDialog');
		}
		else if(tmp_ck_url == 'genericIframe2') {
			if(genericIframe2ViewStatus == 1) back = 1;
			else alertify.closeAll('genericDialog2');
		}
		else if(tmp_ck_url == 'btnFixOrder') {
			if(mobile_option_open == 0) back = 1;						
		}	

		if(back == 1) {
			window.history.back();	
			return;
		}
		
	}

	if(parseInt($("#topMenu").css('top')) == 0) {
		$('.topMenuClose').trigger('click');
	}

	if(parseInt($("#recentGoods").css('top')) == 0) {
		$('.recentGoodsClose').trigger('click');
	}

	if(parseInt($("#topSearch").css('right')) == 0) {
		$('.topSearchClose').trigger('click');
	}

	if(mobile_option_open == 1) {
		$('.btnFixClose').trigger('click');	
	}
	
})  

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

var iframeWidth				= '100%';
var iframeHeight			= '.8';
var genericIframe1ViewStatus = 1;

function genericDialogView(obj, title) {

	alertify.genericDialog || alertify.dialog('genericDialog',function(){
		return {
			main:function(content, title){
				this.setHeader(title);
				content.style.display = 'block';
				this.setContent(content);
				genericIframe1ViewStatus = 0;
			},

			build:function(){           
				var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
				this.elements.body.style.minHeight = parseInt(h) * iframeHeight + 'px';
				this.elements.dialog.style.width = "100%"
			},
			hooks: {
			  onclose: function() {				  
				return setTimeout((function() {
					genericIframe1ViewStatus = 1;
				}), 400);
			  }
			}
		};
	});
	
	alertify.genericDialog (obj, title);

}

var genericIframe2ViewStatus = 1;
function genericDialogView2(obj, title) {

	alertify.genericDialog2 || alertify.dialog('genericDialog2',function(){
		return {
			main:function(content, title){
				this.setHeader(title);
				content.style.display = 'block';
				this.setContent(content);
				genericIframe2ViewStatus = 0;
			},

			build:function(){           
				var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
				this.elements.body.style.minHeight = parseInt(h) * iframeHeight + 'px';
				this.elements.dialog.style.width = "100%"
			},
			hooks: {
			  onclose: function() {				  
				return setTimeout((function() {
					genericIframe2ViewStatus = 1;
				}), 400);
			  }
			}			
		};
	});
	
	alertify.genericDialog2 (obj, title);

}

var iframeViewStatus = 0;

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
						iframeViewStatus = 0;	 
						iframe.src = newValue;
					break;   
				}
			},
			hooks: {
			  onclose: function() {				  
				return setTimeout((function() {
					iframeViewStatus = 1;
					return alertify.iframeDialog().destroy();
				}), 400);
			  }
			}
		};
	});
	
	alertify.iframeDialog(url, title).set({frameless:false});	
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

function snsShare(snsName, link, title, img) {

	if (title === null) return false;

	var snsPopUp;

	switch(snsName){
		case 'facebook':
			snsPopUp = window.open("http://www.facebook.com/sharer/sharer.php?u=" + link);
		break;

		case 'twitter' :
			snsPopUp = window.open("http://twitter.com/intent/tweet?url=" + link + "&text=" + title);
		break;

		case 'kakaos' :
			snsPopUp = window.open("https://story.kakao.com/share?url=" + link);
		break;

		case 'kakao' :

			Kakao.init('카카오에서 발급받은 API키');

			Kakao.Link.createDefaultButton({
				container: '.link-icon.kakao', // 카카오공유버튼ID
				objectType: 'feed',
				content: {
				  title: title, // 보여질 제목
				  description: title, // 보여질 설명
				  imageUrl: img, // 콘텐츠 URL
				  link: {
					 mobileWebUrl: link,
					 webUrl: link
				  }
				}
			});

			$(".link-icon.kakao").trigger("click");

		break;		
	}
	
	$(".fixShare").slideUp("fast");
}

$(function() {
	
	if($('#contents').find('h2.contentTitle').length) {
		$("#topUtil .arrow").show();
		$("#topUtil .logo").html($('#contents').find('h2.contentTitle').html());		
	}

	$('#topUtil, #topContent, #contents').click(function(e){
		if(parseInt($('#topSearch').css('right')) == 0) $('.topSearchClose').trigger("click");
		if(typeof(ckCateOpen) != 'undefined') {			
			if(ckCateOpen > 0) {
				$(".cateSubMenu" + ckCateOpen).parent().trigger('click');
			}
		}
	});
	
	$('.btnTop').click(function(e){
		$("html, body").animate({ scrollTop: 0 }, 100);
	});

	$('.btnBottom').click(function(e){
		var bottom = $(document).height() - $(window).height();
		$("html, body").animate({ scrollTop: bottom }, 100);
	});

	$('.btnHome').click(function(e){
		window.location = "index.php";
	});

	$('.btnMypage').click(function(e){
		window.location = "index.php?channel=mypage";
	});

	$("#topUtil .arrow").click(function(e){
		window.history.back();
	});

	$('.btnShineBox .btnPrev').click(function(e){
		window.history.back();
	});

	$('.btnTopSearch').click(function(e){		
		$('#topSearch').css('right', '-' + parseInt($('#topSearch').css('width')) + 'px');
		$('#topSearch').show().stop().animate({right:'+=' + parseInt($('#topSearch').css('width')) + 'px'},'fast', (function(){ $("form[name=TsearchForm] input[name='keyword']").focus(); }));	
		$('.topKeyword').css('width', parseInt($("#searchKeyword").css("width")) - 60);
		$('#topSearch .autocompleteBox').css('width', $("#searchKeyword").css("width"));
	});

	$('.topSearchClose').click(function(e){
		$('#topSearch').stop().animate({right:'-=' + parseInt($('#topSearch').css('width')) + '40' + 'px'},'fast');		
	});

	$('.btnTopMenu').click(function(e){		
		$('#topMenu').css('top', parseInt($('#topMenu').css('height')) + 'px');
		$('#topMenu').show().stop().animate({top:'-=' + parseInt($('#topMenu').css('height')) + 'px'}, 'fast', (function(){ $("#topMenu").stop().css('top', '0px'); }));	
	});

	$('.topMenuClose').click(function(e){		
		$('#topMenu').stop().animate({top:'+=' + parseInt($('#topMenu').css('height')) + '40' + 'px'}, 'fast', (function(){ $("#topMenu").hide(); }));	
	});

	$('.btnRecent').click(function(e){		
		$('#recentGoods').css('top', parseInt($('#recentGoods').css('height')) + 'px');
		$('#recentGoods').show().stop().animate({top:'-=' + parseInt($('#recentGoods').css('height')) + 'px'}, 'fast', (function(){ $("#recentGoods").stop().css('top', '0px'); }));	
	});

	$('.recentGoodsClose').click(function(e){		
		$('#recentGoods').stop().animate({top:'+=' + parseInt($('#recentGoods').css('height')) + '40' + 'px'}, 'fast', (function(){ $("#recentGoods").hide(); }));	
	});

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

var lastScrollTop	= 0;
var topFixIng		= 0;
var bottomFixIng	= 0;
var bottomQuickIng	= 0;

$(window).scroll(function () {
	
	scrollTop = $(this).scrollTop();
    
	if(scrollTop < lastScrollTop) {
		if(topFixIng == 1) {
			$("#topContent").stop().animate({top : '40px'}, '500');				
			topFixIng = 0;
		}
		if(bottomFixIng == 1) {
			$("#fixMenu").stop().animate({bottom : '0px'}, '500');
			if(typeof(fixedBottom) != 'undefined') $("#fixQuick").stop().css({opacity : 1}).animate({bottom : '70px'}, '500');
			else $("#fixQuick").stop().animate({bottom : '50px'}, '500');
			bottomFixIng = 0;			
		}
	}
    else {		
		if(topFixIng == 0) {			
			$("#topContent").stop().animate({top : '0px'}, '500');
			topFixIng = 1;
		}
		if(bottomFixIng == 0) {
			$("#fixMenu").stop().animate({bottom : '-40px'}, '500');
			if(typeof(fixedBottom) != 'undefined') $("#fixQuick").stop().css({opacity : 1}).animate({bottom : '70px'}, '500');
			else $("#fixQuick").stop().animate({bottom : '20px'}, '500');
			bottomFixIng = 1;
		}
		if(bottomQuickIng == 0) {
			if(typeof(fixedBottom) != 'undefined') $("#fixQuick").show().stop().css({opacity : 1}).animate({bottom : '70px'});
			else $("#fixQuick").show().stop().animate({bottom : '20px'});
			bottomQuickIng = 1;
		}
    }

	if(scrollTop == 0) {
		$("#fixQuick").stop().animate({bottom : '-50px', opacity : '-0'}, '100', (function(){ $("#fixQuick").css({opacity : 1}); }));
		bottomQuickIng = 0;
	}

	if($(".goods_explain .contentMenu").length) {
		if(typeof(menuPositionTop) == 'undefined') return;		
		
		if(scrollTop >  menuPositionTop) {	
			if(!$('.goods_explain .contentMenu').hasClass("contentMenuHoldFix")) {
				$('.goods_explain .contentMenu').addClass("contentMenuHoldFix");
				$('.contentEmpty').show();
			}
		}
		else {
			$('.goods_explain .contentMenu').removeClass("contentMenuHoldFix");
			$('.contentEmpty').hide();
		}
	}
    
	lastScrollTop = scrollTop;	

});