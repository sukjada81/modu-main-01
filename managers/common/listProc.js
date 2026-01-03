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
	var ourl = defaultUrl.split("?");	
	$.cookie("mallUrl",		ourl[0],													{ path: '/' });
	$.cookie("mallPage",	page,														{ path: '/' });
	$.cookie("mallSort",	$("form[name=listSearchForm] select[name='sort']").val(),	{ path: '/' });
	$.cookie("mallLimit",	$("form[name=listSearchForm] select[name='limit']").val(),	{ path: '/' });
}

function pageClickReset() {
	$('.paging a').click(function(e){
		getListPage($(this).attr('href'));
		return false;
	});	
}

function getListPage(url){
	var data = new FormData();
	data.append('reset', 1);

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

function timelinyResetCallBack() {
	
	if($('.optionView').length) {
		$('.optionView').unbind('click').bind('click',function(e) {
			iframeWidth = '1058px';
			iframeHeight = '.9';			
			iframeView("popup_goods_option.php?uid="+$(this).attr('data-uid'), "["+$(this).attr('gname')+"] 옵션 설정");			
		});		
	}

	if($('.btnCateMore').length) {
		$('.btnCateMore').unbind('click').bind('click',function(e) {
			$t = $('#cate_more_'+$(this).attr('data-uid'));
			if($t.css('display')=='none') {
				$t.slideDown("fast");
				$(this).addClass('rotate180');	
				$(this).removeClass('rotate180r');	
			}
			else {
				$t.slideUp("fast");
				$(this).addClass('rotate180r');		
				$(this).removeClass('rotate180');		
			}
		});
	}

	if($('.btnCrm').length) {
		$('.btnCrm').unbind('click').bind('click',function(e) {
			var id = $(this).attr("data-id");
			iframeWidth = '950px';
			iframeHeight = '.8';
			iframeView("../member/popup_member_crm.php?id=" + id, "회원 CRM보기");			
		});
	}
	
	if($('.btnVCrm').length) {
		$(".btnVCrm").each(function(i) {
			if(!$(this).find('.barId').html()) $(this).find('.barId').hide();
		});

		$('.btnVCrm').click(function(e) {
			var id = $(this).attr("data-id");
			if(!id) return;
			iframeWidth = '950px';
			iframeHeight = '.8';
			iframeView("../vendor/popup_vendor_crm.php?id=" + id, "판매사 CRM보기");			
		});	
	}

	if($(".btnDeliveryTracking").length) {
		$(".btnDeliveryTracking").unbind('click').bind('click',function(e) {
			var url = $(this).attr("data-url");
			var num	= $(this).attr("data-num");

			if(!url || !num) {
				alertify.error("배송정보가 입력되지 않았거나 잘못 되었습니다. 고객센테에 문의 하시기 바랍니다.");
				return false;
			}
		
			window.open(url + num, "");
		});
	}
	
	if(typeof(timelinyResetCallBackSub) != 'undefined') timelinyResetCallBackSub();

	checkAllRe();
	$("html, body").animate({ scrollTop: $("#listArea").position().top + 90 }, 100);
	if(typeof(animationLine) == 'function') animationLine();
}

function changeSL(ck, page) {
	if(!ck)	ck		= 0;
	if(!page) page	= 1;

	var sort			= $("form[name=listSearchForm] select[name='sort']").val();
	var limit			= $("form[name=listSearchForm] select[name='limit']").val();
	if(!limit) limit	= $("form[name=listSearchForm] select[name='limit'] option:first").val();

	if(pagingType == 1) {
		if(ck == 1) {
			var total		= defaultTotal;
			var lastPage	= Math.ceil(total / limit);
		}

		$('.paging').timeliny('reset',defaultUrl + '&sort=' + sort + '&limit=' + limit, lastPage, 0, page);
	}
	else getListPage(defaultUrl + '&sort=' + sort + '&limit=' + limit + '&page=1');
}	

var secPage = secSort = secLimit = '';

if($.cookie('mallUrl')) {

	if(document.URL.indexOf($.cookie('mallUrl')) == -1) {		
		if(str_replace("info.php", "list.php", document.URL).indexOf($.cookie('mallUrl'))==-1) {			
			$.removeCookie("mallUrl",	{ path: '/' });
			$.removeCookie("mallPage",	{ path: '/' });
			$.removeCookie("mallSort",	{ path: '/' });
			$.removeCookie("mallLimit", { path: '/' });					
		}
	}
	else {
		secPage		= $.cookie('mallPage');
		secSort		= $.cookie('mallSort');
		secLimit	= $.cookie('mallLimit');
	}

}

var sort		= secSort		? secSort		: defaultSort;
var limit		= secLimit		? secLimit		: defaultLimit;
var page		= secPage		? secPage		: defaultPage;
var listReset	= 0;	

defaultTotal	= str_replace(",", "", defaultTotal);

if(page != defaultPage || sort != defaultSort || limit != defaultLimit) var listReset	= 1;

$("form[name=listSearchForm] select[name='sort']").val(sort).heapbox({'width' : '200px', 'zindex':2, 'onChange':function(value) { changeSL(); } });
$("form[name=listSearchForm] select[name='limit']").val(limit).heapbox({'width' : '150px', 'zindex':2, 'onChange':function(value) { changeSL(1); } });

$(function() {
	if(typeof(listMove) != 'undefined') {
		if($('#listArea').prop('scrollWidth') == $('#listArea').prop('clientWidth')) {			
			$(".listIn").addClass('fixed-header');	
			$('#listArea').removeClass('scrolls');
		}

		$("#listArea").dblclick(function(e) {
			e.preventDefault();
			listAreaDown	= true;
			listAreaX		= e.pageX;
			listAreaLeft	= $(this).scrollLeft();
			$(this).css('cursor', 'move');
		});

		var timer;
		$('#listArea').on("mousedown",function(e){
			timer = setTimeout(function(){
				e.preventDefault();
				listAreaDown	= true;
				listAreaX		= e.pageX;
				listAreaLeft	= $('#listArea').scrollLeft();
				$('#listArea').css('cursor', 'move');
				}, 1000);
		}).on("mouseup mouseleave",function(){
			clearTimeout(timer);
		});

		$("body").mousemove(function(e) {
			if(listAreaDown){
				var newX = e.pageX;
				$("#listArea").scrollLeft(listAreaLeft - newX + listAreaX);
			}
		});

		$("body").mouseup(function(e){
			listAreaDown = false;
			$('#listArea').css('cursor', '');
		});

		$(window).on('resize', function() { 
			if($('#listArea').prop('scrollWidth') == $('#listArea').prop('clientWidth')) {			
				$(".listIn").addClass('fixed-header');	
				$('#listArea').removeClass('scrolls');
			}
			else {
				$(".listIn").removeClass('fixed-header');
				$('#listArea').addClass('scrolls');
			}
		});	
	}
});