function str_replace(search, replace, subject) {
	if (subject === null || subject === undefined) return subject;

	if (Array.isArray(subject)) {
		return subject.map(s => str_replace(search, replace, s));
	}

	let result = String(subject);

	if (Array.isArray(search)) {
		for (let i = 0; i < search.length; i++) {
			const s = String(search[i]);
			const r = Array.isArray(replace) ? String(replace[i] ?? '') : String(replace);
			result = result.split(s).join(r);
		}
		return result;
	}

	return result.split(String(search)).join(String(replace));
}

function setListCookie(page) {
	$.cookie("mallListUrl",	defaultUrl);

	$.cookie("mallListPage",		page,																				{ path: '/' });
	$.cookie("mallListSort",		$("form[name=listSearchForm] select[name='sort'] option:selected").attr("value"),	{ path: '/' });
	$.cookie("mallListLimit",		$("form[name=listSearchForm] select[name='limit'] option:selected").attr("value"),	{ path: '/' });
	$.cookie("mallListType",		listType,																			{ path: '/' });
}

function postListVar() {

	var keyword			= $("form[name=listSearchForm] input[name='def_keyword']").val() ? $("form[name=listSearchForm] input[name='def_keyword']").val() : $("form[name=listSearchForm] input[name='keyword']").val();
	var orig_keyword	= $("form[name=listSearchForm] input[name='orig_keyword']").val();
	var cate			= $("form[name=listSearchForm] input[name='cate']").val();
	var sort			= $("form[name=listSearchForm] select[name='sort']").val();
	var limit			= $("form[name=listSearchForm] select[name='limit']").val();
	if(!limit) limit	= $("form[name=listSearchForm] select[name='limit'] option:first").val();

	if(pagingType == 1) $('.paging').timeliny('reset', defaultUrl + '&sort=' + sort + '&limit=' + limit + '&orig_keyword=' + orig_keyword + '&keyword=' + keyword + '&cate=' + cate, 0, 1);
	else getListPage(defaultUrl + '&sort=' + sort + '&limit=' + limit + '&orig_keyword=' + orig_keyword + '&keyword=' + keyword + '&cate=' + cate, 1);	

}

function sendSearch() {

	var keyword			= jQuery.trim($("form[name=listSearchForm] input[name='keyword']").val());
	var orig_keyword	= $("form[name=listSearchForm] input[name='orig_keyword']");

	if(!keyword) return;
	
	if($("form[name=listSearchForm] input[name='def_keyword']").val()) {
		orig_keyword3 = orig_keyword.val() ? str_replace(" ", "|*|", $("form[name=listSearchForm] input[name='def_keyword']").val()) + '|*|' + orig_keyword.val() : str_replace(" ", "|*|", $("form[name=listSearchForm] input[name='def_keyword']").val());
		orig_keyword2 = orig_keyword3.split('|*|');
	}
	else orig_keyword2 = orig_keyword.val().split('|*|');
	
	if(orig_keyword2.length == 4) {
		$("form[name=listSearchForm] input[name='keyword']").val('');
		alertify.alert("검색은 4개까지만 가능 합니다.");
		return;
	}

	for(k = 0; k < orig_keyword2.length; k ++) {
		if(orig_keyword2[k] == keyword) {
			$("form[name=listSearchForm] input[name='keyword']").val('');
			return;	
		}
	}
	
	var tmp_keyword = $("form[name=listSearchForm] input[name='keyword']").val();
	if($("form[name=listSearchForm] input[name='def_keyword']").val()) {
		if(orig_keyword.val()) orig_keyword.val(orig_keyword.val() + '|*|' + keyword);
		else orig_keyword.val(keyword);
		
		$("form[name=listSearchForm] input[name='keyword']").val('');		

		$.cookie("mallListKeyword",	orig_keyword.val(),	{ path: '/' });
	}
	else {
		$.cookie("mallListKeyword",	orig_keyword.val() ? orig_keyword.val() + '|*|' + keyword : keyword,	{ path: '/' });
	}

	postListVar();
	$("form[name=listSearchForm] input[name='keyword']").val(tmp_keyword);

	$("form[name=listSearchForm] input[name='keyword']").focus();
	secKeyword = $.cookie("mallListKeyword");
}	


function sendCateAdd(sCate) {
	var num		= $(".searchCate" + sCate).attr('data-num');
	var cate	= $("form[name=listSearchForm] input[name='cate']");

	if(num == 1) {
		if($(".searchCate" + sCate).parent().hasClass("selected")) return;
	}
	else {		
		if($(".searchCate" + sCate).hasClass("selected")) return;
		
		var cate2 = cate.val().split('|*|');
		var cate3 = new Array();	
		for(k = 0, k2 = 0; k < cate2.length; k ++) {
			if(cate2[k] != sCate.substr(0, 3) + '000000000') {
				cate3[k2] = cate2[k];
				k2 ++;
			}
			else {
				$(".cateDel" + cate2[k]).remove();
			}
		}		
		cate.val(cate3.join("|*|"));

	}
	
	if(cate.val()) cate.val(cate.val() + '|*|' + sCate);
	else cate.val(sCate);

	$(".secCateList").append("<div class='cateDel" + sCate + "'>" + $(".searchCate" + sCate).attr('data-cate-name') + "&nbsp;<i class='xi-close-thin cursorPoint cateDel' data-cate='" + sCate + "' title='삭제'></i></div>");
	cateDelReset();

	postListVar();

	if(num == 1)  $(".searchCate" + sCate).parent().addClass("selected");
	else {
		$(".searchCate" + sCate).addClass("selected");
		$(".searchCate" + sCate).parents('li').addClass("selected");
	}

	$.cookie("mallListCate",	cate.val(),	{ path: '/' });
	secCate =  $.cookie("mallListCate");

}

function cateDelReset() {
	
	$('.cateDel').unbind('click').bind('click',function(e) {
		$.cookie("mallListPage", 1),	{ path: '/' };
		
		var cate			= $("form[name=listSearchForm] input[name='cate']");
		var delCate			= $(this).attr('data-cate');
		var num				= $(".searchCate" + delCate).attr('data-num');
		
		if(cate.val() == delCate) {
			cate.val('');
		}
		else {
			var cate2 = cate.val().split('|*|');
			var cate3 = new Array();	
			for(k = 0, k2 = 0; k < cate2.length; k ++) {
				if(cate2[k] != delCate) {
					cate3[k2] = cate2[k];
					k2 ++;
				}
			}
			cate.val(cate3.join("|*|"));
		}

		$(this).parent().remove();
		if(num == 1)  $(".searchCate" + delCate).parent().removeClass("selected");
		else {
			$(".searchCate" + delCate).removeClass("selected");

			var cate2 = cate.val().split('|*|');
			var cate3 = new Array();	
			for(k = 0, k2 = 0; k < cate2.length; k ++) {
				if(cate2[k].substr(0, 3) == delCate.substr(0, 3)) {
					k2 = 1;
					break;
				}
			}

			if(k2 == 0) $(".searchCate" + delCate).parents('li').removeClass("selected");
		}

		postListVar();

		$.cookie("mallListCate",	cate.val(),	{ path: '/' });
		secCate =  $.cookie("mallListCate");

	});
	
}

function keywordDelReset() {

	$('.keywordDel').unbind('click').bind('click',function(e) {
		$.cookie("mallListPage", 1,	{ path: '/' });
		var orig_keyword	= $("form[name=listSearchForm] input[name='orig_keyword']");
		var del_keyword		= $(this).attr('data-keyword');

		if(orig_keyword.val() == del_keyword) {
			$(".listTopSelect .keywordList").html('').hide();
			$(".listTopSelect .arrow").hide();
			orig_keyword.val('');
		}
		else {
			$(this).parent().remove();
			orig_keyword2 = orig_keyword.val().split('|*|');
			orig_keyword3 = new Array();	
			for(k = 0, k2 = 0; k < orig_keyword2.length; k ++) {
				if(orig_keyword2[k] != del_keyword) {
					orig_keyword3[k2] = orig_keyword2[k];
					k2 ++;
				}
			}
			orig_keyword.val(orig_keyword3.join("|*|"));						
		}

		postListVar();
		
		$.cookie("mallListKeyword",	orig_keyword.val(),	{ path: '/' });
		secKeyword = $.cookie("mallListKeyword");

	});
	
}

function keywordInitAdd(keyword) {
	$(".listTopSelect .keywordList").append("<div>" + keyword + "&nbsp;<i class='xi-close-thin cursorPoint keywordDel' data-keyword='" + keyword + "' title='삭제'></i></div>").show();
	$(".listTopSelect .arrow").show();	
	keywordDelReset();
}

function cateInitAdd(cate) {
	$(".secCateList").append("<div class='cateDel" + cate + "'>" + $(".searchCate" + cate).attr('data-cate-name') + "&nbsp;<i class='xi-close-thin cursorPoint cateDel' data-cate='" + cate + "' title='삭제'></i></div>");
	cateDelReset();
}

function timelinyResetCallBack() {
	$('.icon_th' + listType).removeClass('cursorPoint').trigger('click');
	$("html, body").animate({ scrollTop: $("#listArea").position().top + 100 }, 100);
}

function timelinyResetCallBackSearch(total) {
	if($('#listTotal').length) $('#listTotal').html(total);
	var keyword			= jQuery.trim($("form[name=listSearchForm] input[name='keyword']").val());
	
	if(keyword) {

		if(!$("form[name=listSearchForm] input[name='def_keyword']").val()) {
			var orig_keyword	=  $("form[name=listSearchForm] input[name='orig_keyword']");
			
			if(orig_keyword.val()) orig_keyword.val(orig_keyword.val() + '|*|' + keyword);
			else orig_keyword.val(keyword);
		}

		$(".listTopSelect .keywordList").append("<div>" + keyword + "&nbsp;<i class='xi-close-thin cursorPoint keywordDel' data-keyword='" + keyword + "' title='삭제'></i></div>").show();
		$(".listTopSelect .arrow").show();
		$("form[name=listSearchForm] input[name='keyword']").val('');
		keywordDelReset();
	}

	if(total == '0') {
		$('#listArea').html('<div class="emptyList">등록된 상품이 없거나 선택한 조건에 맞는 상품이 없습니다.</div>');
		$('.paging').hide();
	}
	else {		
		$('#listArea .emptList').remove();
		$('.paging').show();
	}	
}

function pageClickReset() {
	$('.paging a').click(function(e){
		getListPage($(this).attr('href'));
		return false;
	});
}

function getListPage(url, search){
	if(!search) search = 0;

	var data = new FormData();
	data.append('reset', 1);
	if(search == 1) data.append('lastPage', 2);	
	$.ajax({
			url: url,
			type: 'POST',
			data: data, 
			cache: false,
			dataType: 'json',
			processData: false, 
			contentType: false, 
			success: function(data, textStatus, jqXHR) {					
				if(typeof(data.error) === 'undefined') {						
					$.each(data, function(key, value) {
						$('#listArea').html(data[key].listHtml);
						$('.paging').html(data[key].listPaging);															
						setListCookie(data[key].listPage);
						pageClickReset();
						if(search == 1) {
							timelinyResetCallBackSearch(data[key].total);
						}
					});
					if(typeof(timelinyResetCallBack) != 'undefined') timelinyResetCallBack();
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

function changeSL(ck, page) {
	if(!ck)	ck		= 0;
	if(!page) page	= 1;

	var sort			= $("form[name=listSearchForm] select[name='sort']").length ? $("form[name=listSearchForm] select[name='sort']").val() : defaultSort;
	var limit			= $("form[name=listSearchForm] select[name='limit']").length ? $("form[name=listSearchForm] select[name='limit']").val() : defaultLimit;
	var sendUrl			= defaultUrl + '&sort=' + sort + '&limit=' + limit + '&orig_keyword=' + secKeyword;
		
	if(pagingType == 1) $('.paging').timeliny('reset', sendUrl, 0, ck, page);
	else getListPage(sendUrl + '&page=1');		
}

if(typeof(is_mobile) == 'undefined') {
	for(i = 0; i < 9; i ++) {
		$('.icon_th3').append('<div class="icon_box">');
	}

	$('.icon_th3 .icon_box').each(function(i) {
		$(this).css({'top' : Math.floor(i / 3) * 8, 'left' : Math.floor(i % 3) * 8});
	});

	var icon_th3_top = new Array();
	$('.icon_th3').hover(function(e){
		if(!$(this).hasClass('cursorPoint')) return;
		
		$(this).find('div').each(function(i) {
			if(!icon_th3_top[i]) icon_th3_top[i] = $(this).css('top');

			if(icon_th3_top[i] != $(this).css('top')) return;;

			$(this).stop().animate({top : '-=10px'}, 100).animate({top : '+=10px'}, 100).animate({top : '-=3px'}, 100).animate({top : '+=3px'}, 100);				
		});
	}
	, 
	function() {
		if(!$(this).hasClass('cursorPoint')) return;
		$(this).find('div').each(function(i) {					
			$(this).css({top : icon_th3_top[i]});
		});
		
	});

	for(i = 0; i < 16; i ++) {
		$('.icon_th4').append('<div class="icon_box selected">');
	}

	$('.icon_th4 .icon_box').each(function(i) {
		$(this).css({'top' : Math.floor(i / 4) * 6, 'left' : Math.floor(i % 4) * 6});
	});

	var icon_th4_top	= new Array();
	var icon_th4_left	= new Array();
	$('.icon_th4').hover(function(e){
		if(!$(this).hasClass('cursorPoint')) return;
		
		$(this).find('div').each(function(i) {
			if(!icon_th4_top[i])	icon_th4_top[i]		= $(this).css('top');
			if(!icon_th4_left[i])	icon_th4_left[i]	= $(this).css('left');
			
			if(icon_th4_top[i] != $(this).css('top') || icon_th4_left[i] != $(this).css('left')) return;

			if(i == 0 || i == 1 || i == 2 || i == 3) $(this).stop().animate({top : '-=10px'}, 100).animate({top : '+=10px'}, 100);
			if(i == 4 || i == 8 ) $(this).stop().animate({left : '-=10px'}, 100).animate({left : '+=10px'}, 100);
			if(i == 7 || i == 11) $(this).stop().animate({left : '+=10px'}, 100).animate({left : '-=10px'}, 100);
			if(i == 12 || i == 13 || i == 14 | i == 15) $(this).stop().animate({top : '+=10px'}, 100).animate({top : '-=10px'}, 100);
			
		});
	}
	, 
	function() {
		if(!$(this).hasClass('cursorPoint')) return;
		$(this).find('div').each(function(i) {					
			$(this).css({top : icon_th4_top[i], left : icon_th4_left[i]});
		});
		
	});

	for(i = 0; i < 36; i ++) {
		$('.icon_th6').append('<div class="icon_box">');
	}

	$('.icon_th6 .icon_box').each(function(i) {
		$(this).css({'top' : Math.floor(i / 6) * 4, 'left' : Math.floor(i % 6) * 4});
	});

	var icon_th6_top	= new Array();
	var icon_th6_left	= new Array();
	$('.icon_th6').hover(function(e){
		if(!$(this).hasClass('cursorPoint')) return;
		
		$(this).find('div').each(function(i) {
			if(!icon_th6_top[i])	icon_th6_top[i]		= $(this).css('top');
			if(!icon_th6_left[i])	icon_th6_left[i]	= $(this).css('left');
			
			if(icon_th6_top[i] != $(this).css('top') || icon_th6_left[i] != $(this).css('left')) return;
			if(i == 0 || i == 1 || i == 2 || i == 6 || i == 7 || i == 8 || i == 12 || i == 13 || i == 14) $(this).stop().animate({top : '-=10px'}, 100).animate({top : '+=10px'}, 100);
			if(i == 3 || i == 4 || i == 5 || i == 9 || i == 10 || i == 11 || i == 15 || i == 16 || i == 17) $(this).stop().animate({left : '+=10px'}, 100).animate({left : '-=10px'}, 100);
			if(i == 18 || i == 19 || i == 20 || i == 24 || i == 25 || i == 26 || i == 30 || i == 31 || i == 32) $(this).stop().animate({left : '-=10px'}, 100).animate({left : '+=10px'}, 100);
			if(i == 21 || i == 22 || i == 23 || i == 27 || i == 28 || i == 29 || i == 33 || i == 34 || i == 35) $(this).stop().animate({top : '+=10px'}, 100).animate({top : '-=10px'}, 100);
		});
	}
	, 
	function() {
		if(!$(this).hasClass('cursorPoint')) return;
		$(this).find('div').each(function(i) {					
			$(this).css({top : icon_th6_top[i], left : icon_th6_left[i]});
		});
		
	});

	$('.icon_th3, .icon_th4, .icon_th6').click(function(e){
		var cnt = $(this).attr('data-cnt');		
		$("#listArea .goodsItemBox .goodsItem").removeClass("goodsItem4 goodsItem3 goodsItem6").addClass("goodsItem" + cnt);
		$('.icon_th3, .icon_th4, .icon_th6').find('div').removeClass('selected');
		$(this).find('div').addClass('selected');
		$('.icon_th3, .icon_th4, .icon_th6').addClass("cursorPoint");
		$(this).removeClass('cursorPoint');
		
		if(listType == cnt) return;

		listType = cnt;
		setListCookie(page);
	});
}

var secPage	= secSort = secLimit = secListType = secKeyword	= secCate = "";

if($.cookie('mallListUrl')) {
	if($.cookie('mallListUrl') == defaultUrl) {
		secPage		= $.cookie('mallListPage');
		secKeyword	= $.cookie("mallListKeyword")	? $.cookie("mallListKeyword")	: '';
		secCate		= $.cookie("mallListCate")		? $.cookie("mallListCate")		: '';
		secSort		= $.cookie('mallListSort')		? $.cookie('mallListSort')		: '';
		secLimit	= $.cookie('mallListLimit')		? $.cookie('mallListLimit')		: '';
		secListType	= $.cookie('mallListType')		? $.cookie('mallListType')		: '';	
	}
	else {
		$.removeCookie("mallListKeyword",	{ path: '/' });
		$.removeCookie("mallListCate",	{ path: '/' });
		$.removeCookie('mallListSort',	{ path: '/' });
		$.removeCookie('mallListLimit',	{ path: '/' });
		$.removeCookie('mallListType',	{ path: '/' });
	}	
}

var sort		= secSort		? secSort		: defaultSort;
var limit		= secLimit		? secLimit		: defaultLimit;
var listType	= secListType	? secListType	: '4';
var page		= secPage		? secPage		: defaultPage;
var listReset	= 0;	

if(page != defaultPage || sort != defaultSort || limit != defaultLimit) var listReset	= 1;

if(typeof(is_mobile) == 'undefined') {
	$("form[name=listSearchForm] select[name='sort']").val(sort).heapbox({'width' : '130px', 'onChange':function(value) { changeSL(); } });
	$("form[name=listSearchForm] select[name='limit']").val(limit).heapbox({'width' : '130px', 'onChange':function(value) { changeSL(1); } });
}
else {	
	$("form[name=listSearchForm] select[name='sort']").val(sort).heapbox({'width' : '100%', 'onChange':function(value) { changeSL(); } });
	$("form[name=listSearchForm] select[name='limit']").val(limit).heapbox({'width' : '100%', 'onChange':function(value) { changeSL(1); } });
}

if(secKeyword) {
	$("form[name=listSearchForm] input[name='orig_keyword']").val(secKeyword);
	var tmp_keyword = secKeyword.split('|*|');
	for(i = 0; i < tmp_keyword.length; i ++) {
		keywordInitAdd(tmp_keyword[i]);
	}
}

if(secCate) {
	$("form[name=listSearchForm] input[name='cate']").val(secCate);
	var tmp_cate = secCate.split('|*|');
	for(i = 0; i < tmp_cate.length; i ++) {
		cateInitAdd(tmp_cate[i]);
	}
}


$(function() {
	
	if(typeof(is_mobile) == 'undefined') {

		$('.cateList ul li').hover(function(e){
			cate = $(this).attr("data-cate");
			if($(".cateSubMenu" + cate).length) $(".cateSubMenu" + cate).stop().slideDown("fast");
		}
		, 
		function() {
			cate = $(this).attr("data-cate");
			if($(".cateSubMenu" + cate).length) $(".cateSubMenu" + cate).stop().slideUp("fast");
		});

	}
	else {		
		$('.cateList ul li').click(function(e){
			var cate =$(this).attr('data-cate');

			if($(this).find(".cateSubMenu").length) {
				if($(this).find("i").hasClass('rotate180')) {
					if($(".cateSubMenu" + cate).length) $(".cateSubMenu" + cate).stop().slideUp("fast");
					$(this).find("i").removeClass('rotate180').addClass('rotate180r');
					ckCateOpen = 0;
				}
				else {
					if($(".cateSubMenu" + cate).length) $(".cateSubMenu" + cate).stop().slideDown("fast", (function(){ ckCateOpen = cate; }));	
					$(this).find("i").removeClass('rotate180r').addClass('rotate180');						
				}
			}
			else {
				window.location.href = "index.php?channel=list&cate=&cate=" + cate;
			}
		});	
	}

	$(".searchCate").click(function(e) {
		sendCateAdd($(this).attr('data-cate'));
		return false;
	});

	$('.icon_th' + listType).removeClass('cursorPoint').trigger('click');	

});