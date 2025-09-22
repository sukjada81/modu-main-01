function setListCookie(page) {

	$.cookie("mallUrl",	defaultUrl,	{ path: '/' });

	$.cookie("mallPage",		page,	{ path: '/' });
	if($("form[name=searchForm] select[name='sort']").length) {
		$.cookie("mallSort",		$("form[name=searchForm] select[name='sort'] option:selected").attr("value"),	{ path: '/' });
	}
	if($("form[name=searchForm] select[name='limit']").length) {
		$.cookie("mallLimit",		$("form[name=searchForm] select[name='limit'] option:selected").attr("value"),	{ path: '/' });		
	}
	if($("form[name=searchForm] select[name='status']").length) {
		$.cookie("mallStatus",		$("form[name=searchForm] select[name='status'] option:selected").attr("value"),	{ path: '/' });		
	}
}

function ckCookie() {

	if($.cookie('mallUrl')) {			
		$.removeCookie('mallUrl',	{ path: '/' });
		$.removeCookie('mallPage',	{ path: '/' });
		$.removeCookie('mallSort',	{ path: '/' });
		$.removeCookie('mallLimit',	{ path: '/' });
		$.removeCookie('mallStatus',	{ path: '/' });
	}

}

function pageClickReset() {
	$('.paging a').click(function(e){
		getListPage($(this).attr('href'));
		return false;
	});
}

function timelinyResetCallBackSearch(total) {
	if($('#listTotal').length) $('#listTotal').html(total);

	if(total == '0') {
		$('#listArea').html('<div class="emptyList">등록된 내역이 없거나 선택한 조건에 맞는 내역이 없습니다.</div>');
		$('.paging').hide();
	}
	else {		
		$('#listArea .emptList').remove();
		$('.paging').show();
	}	
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

	var sort			= $("form[name=searchForm] select[name='sort']").val();
	var limit			= $("form[name=searchForm] select[name='limit']").val();
	if(!limit) limit	= $("form[name=searchForm] select[name='limit'] option:first").val();
	
	var sendUrl			= defaultUrl + '&sort=' + sort + '&limit=' + limit;

	if($("form[name=searchForm] input[name='s_date']").length) {
		var s_date		= $("form[name=searchForm] input[name='s_date']").val();
		sendUrl += '&s_date=' + s_date;
	}
	
	if($("form[name=searchForm] input[name='e_date']").length) {
		var e_date		= $("form[name=searchForm] input[name='e_date']").val();
		sendUrl += '&e_date=' + e_date;
	}
	
	if($("form[name=searchForm] select[name='status']").length) {
		var status		= $("form[name=searchForm] select[name='status']").val();
		sendUrl += '&status=' + status;
	}
	
	if(pagingType == 1) $('.paging').timeliny('reset', sendUrl, 0, ck, page);
	else getListPage(sendUrl + '&page=1');		
}

function timelinyResetCallBack() {	
	$("html, body").animate({ scrollTop: $("#listArea").position().top + 100 }, 100);
	
	if($('.review').length) {
		$('.review').find('.image').unbind('click').bind('click',function(e) {
			var uid = $(this).attr("data-uid");
			if(typeof(is_mobile) != 'undefined') iframeWidth = '100%';
			else iframeWidth = '850px';
			iframeHeight = '.8';
			iframeView("php/popup_review_view.php?uid=" + uid, "구매후기");	
		});			
	}

	if(typeof(timelinyResetCallBackSub) != 'undefined') timelinyResetCallBackSub();
}

var secPage	= secSort = secLimit = secStatus = "";

if($.cookie('mallUrl')) {
	if($.cookie('mallUrl') == defaultUrl) {
		secPage		= $.cookie('mallPage');
		secSort		= $.cookie('mallSort')		? $.cookie('mallSort')		: '';
		secLimit	= $.cookie('mallLimit')		? $.cookie('mallLimit')		: '';
		secStatus	= $.cookie('mallStatus')	? $.cookie('mallStatus')	: '';	
	}
	else {
		$.removeCookie('mallSort',	{ path: '/' });
		$.removeCookie('mallLimit',	{ path: '/' });
		$.removeCookie('mallStatus',	{ path: '/' });
	}
}

var sort		= secSort		? secSort		: defaultSort;
var limit		= secLimit		? secLimit		: defaultLimit;
var page		= secPage		? secPage		: defaultPage;
if(typeof(defaultStatus) == 'undefined') defaultStatus = '';
var status		= secStatus		? secStatus		: defaultStatus; 
var listReset	= 0;	

if(parseInt(page) > parseInt(lastPage)) page = lastPage;

if(page != defaultPage || sort != defaultSort || limit != defaultLimit) var listReset	= 1;
if(status) {
	if(status != defaultStatus) var listReset	= 1;
}

var heapboxWidth = '130px';
if(typeof(is_mobile) != 'undefined') var heapboxWidth = '100%';

if($("form[name=searchForm] select[name='sort']").length) {
	$("form[name=searchForm] select[name='sort']").val(sort).heapbox({'width' : heapboxWidth, 'onChange':function(value) { changeSL(); } });
}
if($("form[name=searchForm] select[name='limit']").length) {
	$("form[name=searchForm] select[name='limit']").val(limit).heapbox({'width' : heapboxWidth, 'onChange':function(value) { changeSL(1); } });
}
if($("form[name=searchForm] select[name='status']").length) {
	$("form[name=searchForm] select[name='status']").val(status).heapbox({'width' : heapboxWidth, 'onChange':function(value) { changeSL(1); } });
}

$(function() {
	
	$('.resetIconBtn').click(function(e){		
		ckCookie();
		var param = "";
		$("form[name=searchForm]").find("input").each(function(i) {
			if($(this).prop("type")=='hidden') {
				if($(this).val()) {
					if(param) param += "&" + $(this).attr("name") + "=" + $(this).val();
					else param = "?" + $(this).attr("name") + "=" + $(this).val();
				}
			}
		});

		window.location.href = document.searchForm.action + param;
	});

});