
// PHP str_replace를 JS에서 흉내내는 전역 함수 — 최소 변경 패치
if (typeof window.str_replace !== 'function') {
  window.str_replace = function (search, replace, subject) {
    return (subject ?? '').toString().split(search).join(replace);
  };
}

// PHP number_format 대체 — 최소 변경 폴리필
if (typeof window.number_format !== 'function') {
  window.number_format = function (n, decimals = 0) {
    // 숫자 변환(콤마 제거 후)
    const num = parseFloat(String(n ?? '').replace(/,/g, '')) || 0;
    // 소수 자릿수 고정
    const fixed = Number.isFinite(decimals) && decimals >= 0
      ? num.toFixed(decimals)
      : Math.round(num).toString();
    // 천단위 콤마
    return fixed.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  };
}

$('#air_pos_name').append('<p class="requireIcon" style="left:950px"><i class="fas fa-pen-square masterTooltip" title="필수 입력사항 입니다."></i></p>');	
$('#air_pos_name').append('<p class="requireIcon size09" style="left:980px; right:0"><span class="colorOrange" id="air_pos_name_cnt">0</span> / 55</p>');	
$('#air_pos_detail').append('<p class="requireIcon size09" style="left:970px; right:0"><span class="colorOrange" id="air_pos_detail_cnt">0</span> / 220</p>');	

function vendorPrice(price2) {
	var commission = $("form[name=goodsForm] input[name='commission']").val();
	if(price2) 	{
		var price2 = parseInt(str_replace(",","", price2));
		if(price2 > 0) {			
			var price = (price2 * 100) / (100 - commission);
			price = priceLimit2(price);
			var commission_won = number_format(price - price2);

			$('form[name=goodsForm] input[name=commission_won]').val(commission_won);
			$('form[name=goodsForm] input[name=price]').val(number_format(price));			
		}
	}
	else {
		var price = parseInt(str_replace(",","",$('form[name=goodsForm] input[name=price]').val()));
		if(price > 0) {
			var orig_price = price * ((100 - commission) / 100);
			orig_price = priceLimit2(orig_price);
			var commission_won = number_format(price - orig_price);

			$('form[name=goodsForm] input[name=commission_won]').val(commission_won);
			$('form[name=goodsForm] input[name=orig_price]').val(number_format(orig_price));
		}
	}
}

function hqPrice() {
	var price1 = parseInt(str_replace(",","",$('form[name=goodsForm] input[name=price]').val()));		

	if(price1 > 0) {
		var price2 = parseInt(str_replace(",","",$('form[name=goodsForm] input[name=orig_price]').val()));
		var margin_won = number_format(price1 - price2);
		var margin = number_format(((price1 - price2) * 100) / price1, 2);
		$('form[name=goodsForm] input[name=commission_won]').val(margin_won);
		$('form[name=goodsForm] input[name=commission]').val(margin);
	}
}

function otherImageInsert(img, img_name) {
	if(!img) return;
	
	var orig_otherImageCnt = otherImageCnt;
	var insertCode = '<li data-idx="'+img_name+'">';
	var img_name2 = img_name.split(".");
	insertCode += '		<div class="otherImg">';
	insertCode += '			<img id="'+img_name2[0]+'" src='+img+' alt="otherImage'+otherImageCnt+'" />';
	insertCode += '			<div class="btnBoxIcon">';
	insertCode += '				<span id="otherImageIcon_'+otherImageCnt+'"><i class="xi-close masterTooltip" title="삭제하기"></i></span>';
	insertCode += '			</div>';
	insertCode += '		</div>';
	insertCode += '	  </li>';

	$('#otherImageSortable').append(insertCode);
	
	$('#otherImageIcon_'+orig_otherImageCnt).find('.xi-close').click(function(e){
		imageDelete($(this).closest('li'), 'other');
	});

	otherImageCnt++;
	other_image_cnt++;
	resetTooltip();
	resetContent();

	$('#otherImageSortable').sortable({
		placeholder: "highlight",
		cursor : "move",
		update : function(e, ui) {
			$('form[name=goodsForm] input[name=other_image_order]').val($(this).sortable("toArray", { attribute : 'data-idx' }));
		}
	});
	
	$('form[name=goodsForm] input[name=other_image_order]').val($('#otherImageSortable').sortable("toArray", { attribute : 'data-idx' }));
	
	$("#otherImageSortable").disableSelection();
	$("#otherImageEmpty").hide();
}

function relatedGoodsGet(uid) {

	alertify.closeAll('iframeDialog');

	var ck_value	= $('form[name=goodsForm] input[name=related_goods_order]').val().split(',');
	if(typeof(uid)=='string') var ck_uid = uid.split(',');
	else var ck_uid = uid;
	
	var uid = [];
	for(i=0, cnt=ck_uid.length; i<cnt; i++) {			
		if(ck_value.indexOf(ck_uid[i])==-1) uid.push(ck_uid[i]);
	}
	
	if(uid.length == 0) {
		alertify.alert("이미 등록된 상품 입니다.");
		return;		
	}

	var data = new FormData();

	data.append('uid',uid);

	$.ajax({
		url: '../../managers/goods/goods_info_json.php', 
		type: 'POST',	
		data: data, 
		cache: false,
		dataType: 'json',
		processData: false, 
		contentType: false, 
		success: function(data, textStatus, jqXHR) {
			if(typeof(data.error) === 'undefined') {											
				$.each(data, function(key, value) {
					relatedGoodsInsert(data[key].uid, data[key].image, data[key].name, data[key].price);					
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

function relatedGoodsInsert(uid, img, name, price) {
	if(!uid) return;

	var insertCode = '<li data-idx="'+uid+'">';
	insertCode += '		<div class="relatedGoods">';
	insertCode += '			<img src="'+img+'" alt="'+name+'" />';
	insertCode += '			<span id="relatedGoodsIcon_'+uid+'"><i class="xi-close masterTooltip" title="해제하기"></i></span>';
	insertCode += '			<p>['+price+'] '+name+'</p>';
	insertCode += '		</div>';
	insertCode += '	  </li>';

	$('#relatedGoodsSortable').append(insertCode);
	
	$('#relatedGoodsIcon_'+uid).find('.xi-close').click(function(e){
		relatedGoodsDelete($(this).parent('span').parent('div').parent('li'));
	});

	resetTooltip();
	resetContent();

	$('#relatedGoodsSortable').sortable({
		placeholder: "highlight",
		cursor : "move",
		update : function(e, ui) {
			$('form[name=goodsForm] input[name=related_goods_order]').val($(this).sortable("toArray", { attribute : 'data-idx' }));
		}
	});
	
	$('form[name=goodsForm] input[name=related_goods_order]').val($('#relatedGoodsSortable').sortable("toArray", { attribute : 'data-idx' }));
	
	$("#relatedGoodsSortable").disableSelection();

	$("#relatedGoodsEmpty").hide();
}

function relatedGoodsDelete($t) {
	$t.remove();	
	$('form[name=goodsForm] input[name=related_goods_order]').val($('#relatedGoodsSortable').sortable("toArray", { attribute : 'data-idx' }));
	if(!$('form[name=goodsForm] input[name=related_goods_order]').val()) $("#relatedGoodsEmpty").show();
	resetContent();
}

function ckmileageType() {
	if($('form[name=goodsForm] input[name=mileage_type]:checked').val()=='4') {
		dipslayDarkShow('form[name=goodsForm] input[name=mileage_common]');
		$("#level_mileage").hide();
		resetTooltip();
	}
	else {
		if($('form[name=goodsForm] input[name=mileage_type]:checked').val()=='3') {
			$("#level_mileage").show();
			$(".level_mileage").attr('readonly',false);
		}
		else {
			$("#level_mileage").hide();
			$(".level_mileage").attr('readonly',true);
		}
		dipslayDarkHide('form[name=goodsForm] input[name=mileage_common]');
	}
}

function ckDeliveryType() {
	if($('form[name=goodsForm] input[name=delivery_type]:checked').val()=='4' || $('form[name=goodsForm] input[name=delivery_type]:checked').val()=='5') {
		dipslayDarkShow('form[name=goodsForm] input[name=delivery_price]');
		resetTooltip();
	}
	else {
		dipslayDarkHide('form[name=goodsForm] input[name=delivery_price]');
	}
	
	if($('form[name=goodsForm] input[name=delivery_type]:checked').val()=='5') {
		dipslayDarkShow('form[name=goodsForm] input[name=delivery_type_qty]');
	}
	else {
		dipslayDarkHide('form[name=goodsForm] input[name=delivery_type_qty]');		
	}

	if($('form[name=goodsForm] input[name=delivery_type]:checked').val()=='1') $(".delivery_im_areas").hide();
	else $(".delivery_im_areas").show();
}

function putImage(src, num) {
	$("#image"+num).attr("src", src);
	$("#btn_image"+num).hide();
	$("#image"+num).fadeIn(100);
	$("#btn2_image"+num).fadeIn(100);

	$('.inputMessage, .inputMessage_arrow').fadeOut('200').remove();
}

function vendorChange(value) {
	if(!value) return;
	
	var data = new FormData();

	data.append('id', value);

	$.ajax({
		url: '../../managers/goods/vendor_info_json.php', 
		type: 'POST',	
		data: data, 
		cache: false,
		dataType: 'json',
		processData: false, 
		contentType: false, 
		success: function(data, textStatus, jqXHR) {
			if(typeof(data.error) === 'undefined') {
				if(data.commission) {
					$('#orig_price').html('공급가');
					$('#commission_won').html("수수료");
					$('#commission').html('수수료율 <i class="xi-help xi-x colorDGary masterTooltipR" title="상품개별 수수료율을 적용하시면 판매자에 설정된 수수료율과 별도로 상품에 개별 수수료율을 설정 하실 수 있습니다."></i>');
					if($('form[name=goodsForm] input[name=commission_type]').val() == 0) {
						$("form[name=goodsForm] input[name='commission']").val(data.commission);
						$('#commi_individual').show();
						$('#commi_public').hide();
					}
					else {
						$("form[name=goodsForm] input[name='commission']").removeClass('darks');	
						$('#commi_individual').hide();
						$('#commi_public').show();
					}
					temp_commission = data.commission;
					//$("form[name=goodsForm] input[name='orig_price']").attr('readonly',true).addClass('darks');
					$(".conf_delivery").html(data.delivery);
					if(data.disable1)	$(".delivery_disable1").addClass("data.disable1");
					else				$(".delivery_disable1").removeClass("data.disable1");
					if(data.disable2)	$(".delivery_disable2").prop("disabled", true);
					else				$(".delivery_disable2").prop("disabled", false);				

					vendorPrice();
					resetTooltip();
				}
				else alertify.error("입점사 정보가 없습니다.");
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

function requireGet(uid) {

	alertify.closeAll('iframeDialog');

	var data = new FormData();

	data.append('uid',uid);

	$.ajax({
		url: '../../managers/goods/goods_info_json.php', 
		type: 'POST',	
		data: data, 
		cache: false,
		dataType: 'json',
		processData: false, 
		contentType: false, 
		success: function(data, textStatus, jqXHR) {
			if(typeof(data.error) === 'undefined') {											
				$.each(data, function(key, value) {
					if(data[key].require_info) {						
						require_info = data[key].require_info.split('|*|');
						$("#requireGSortable").multiBox('reset');
						for(z=0,cnt=require_info.length; z<cnt; z++) {
							require_info2 = require_info[z].split('|');														
							$("#requireGSortable").multiBox('add2', [{'name' : 'require_name', 'value' : require_info2[0]},{'name' : 'require_info', 'value' : require_info2[1]},{'name' : 'require_info_help', 'value' : require_info2[2]}]);
						}		
					}
					else {
						alertify.alert(data[key].name + "상품은 필수정보가 없습니다.");						
					}
					$("form[name=goodsForm] select[name='require_select']").val('').heapbox('update');
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

function informationGet(uid) {

	alertify.closeAll('iframeDialog');

	var data = new FormData();

	data.append('uid',uid);

	$.ajax({
		url: '../../managers/goods/goods_info_json.php', 
		type: 'POST',	
		data: data, 
		cache: false,
		dataType: 'json',
		processData: false, 
		contentType: false, 
		success: function(data, textStatus, jqXHR) {
			if(typeof(data.error) === 'undefined') {											
				$.each(data, function(key, value) {					
					if(data[key].delivery_info) {						
						$(".sn_delivery_info").html(data[key].delivery_info);
						$(".sn_delivery_info").summernote('reset');
					}					
					if(data[key].refund_info) {
						$(".sn_refund_info").html(data[key].refund_info);
						$(".sn_refund_info").summernote('reset');
					}
					if(data[key].exchange_info) {
						$(".sn_exchange_info").html(data[key].exchange_info);
						$(".sn_exchange_info").summernote('reset');
					}
					if(data[key].as_info) {
						$(".sn_as_info").html(data[key].as_info);					
						$(".sn_as_info").summernote('reset');
					}					
					$("form[name=goodsForm] select[name='information_use']").val('3').heapbox('update');
					$('.infoMenuDetail').slideDown("fast");
					$('#delivery_info').show();
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

$("#requireGSortable").multiBox({'sortable':'1','order':'form[name=goodsForm] input[name=require_order]','focus':'2','list' : [{'name':'','width':'7','type':'','enter':0},{'name':'require_name','width':'26','type':'text','enter':0},{'name':'require_info','width':'60','type':'text_holder','enter':1},{'name':'','width':'7','type':'icon','enter':0}]});	

$("#makingSortable").multiBox({'sortable':'1','order':'form[name=goodsForm] input[name=making_order]','focus':'1','autoNum':'0','list' : [{'name':'making_name','width':'25','type':'text','enter':0},{'name':'making_info','width':'65','type':'text_holder','enter':1},{'name':'','width':'10','type':'icon','enter':0}]});	

$(function() {

	$('.sn_name').summernote({
		'maxTextLength' : '55',
		'airMode' : true,
		'airPos' : 'air_pos_name',
		'lang' : 'ko-KR',
		'dialogsFade' : true,
		'nextFocus' : $("form[name=goodsForm] input[name='price']")
	});			

	$('.sn_detail').summernote({
		'maxTextLength' : '220',
		'airMode' : true,
		'airPos' : 'air_pos_detail',
		'lang' : 'ko-KR',
		'dialogsFade' : true,
		'nextFocus' : $("form[name=goodsForm] input[name='brand']")
	});			
	
	$('.sn_explains').summernote({						
		'lang' : 'ko-KR',
		'height' : '300px',
		'dialogsFade' : true,
		'imgSync' : true,
		callbacks: {
			onBlur: function() {
				imageStatus();
			}    
		}
	});	

	$('.sn_delivery_info').summernote({						
		'lang' : 'ko-KR',
		'height' : '200px',
		'dialogsFade' : true,
		'imgSync' : false,
		callbacks: {
			onImageUpload:function( files,editor,welEditable) {
				snUpImage(files, this, 'information_use/goods/|'+temp_upload+'|/1' );
			}
		}
	});	

	$('.sn_refund_info').summernote({						
		'lang' : 'ko-KR',
		'height' : '200px',
		'dialogsFade' : true,
		'imgSync' : false,
		callbacks: {
			onImageUpload:function( files,editor,welEditable) {
				snUpImage(files, this, 'information_use/goods/|'+temp_upload+'|/2' );
			}
		}
	});	

	$('.sn_exchange_info').summernote({						
		'lang' : 'ko-KR',
		'height' : '200px',
		'dialogsFade' : true,
		'imgSync' : false,
		callbacks: {
			onImageUpload:function( files,editor,welEditable) {
				snUpImage(files, this, 'information_use/goods/|'+temp_upload+'|/3' );
			}
		}
	});	

	$('.sn_as_info').summernote({						
		'lang' : 'ko-KR',
		'height' : '200px',
		'dialogsFade' : true,
		'imgSync' : false,
		callbacks: {
			onImageUpload:function( files,editor,welEditable) {
				snUpImage(files, this, 'information_use/goods/|'+temp_upload+'|/4' );
			}
		}
	});	

	$('form[name=goodsForm] input[name=price]').blur(function(e){
		$(this).val(priceLimit($(this).val()));
		if($("form[name=goodsForm] select[name='vendor']").length) {
			if($("form[name=goodsForm] select[name='vendor']").val()) vendorPrice();
			else hqPrice();
		}
		else {
			vendorPrice();
		}
	});

	$('form[name=goodsForm] input[name=orig_price]').blur(function(e){
		$(this).val(priceLimit($(this).val()));
		if($("form[name=goodsForm] select[name='vendor']").length) {
			if($("form[name=goodsForm] select[name='vendor']").val()) vendorPrice($(this).val());
			else hqPrice();
		}
		else {
			vendorPrice($(this).val());
		}
	});

	$('form[name=goodsForm] input[name=commission]').blur(function(e){
		if($("form[name=goodsForm] select[name='vendor']").length) {
			if($("form[name=goodsForm] select[name='vendor']").val()) vendorPrice();
			else hqPrice();
		}
		else {
			vendorPrice();
		}
	});

	$('#commi_individual').click(function(e){
		$("form[name=goodsForm] input[name='commission']").attr('readonly',false).removeClass('darks');
		$("form[name=goodsForm] input[name='commission_type']").val(1);
		$('#commi_individual').hide();
		$('#commi_public').show();
	});

	$('#commi_public').click(function(e){
		$("form[name=goodsForm] input[name='commission']").val(temp_commission).attr('readonly',true).addClass('darks');
		$("form[name=goodsForm] input[name='commission_type']").val(0);
		$('#commi_public').hide();
		$('#commi_individual').show();
		if($("form[name=goodsForm] select[name='vendor']").val()) vendorPrice();
	});

	$('.imageBtn').on('change', function(e){
		
		if(!CommonCheckImage($(this))) return;

		if(e.target.files[0]) {			
			var num = $(this).attr("data-num");
			var reader = new FileReader();
			reader.onload = function (e) {
				var image = new Image();
				image.src = e.target.result;

				image.onload = function(){                             
					$("#image"+num).attr("src", this.src);
					$("#btn_image"+num).hide();
					$("#image"+num).fadeIn(100);
					$("#btn2_image"+num).fadeIn(100);

					$('.inputMessage, .inputMessage_arrow').fadeOut('200').remove();
				};                      
			}
			reader.readAsDataURL(e.target.files[0]);
		}
	});

	$('#btn2_image1, #btn2_image2, #btn2_image3').find('.fa-edit').click(function(e){
		var num = $(this).parent().attr("data-num");
		$("form[name=goodsForm] input[name='image" + num + "']").trigger('click');
		if($("form[name=goodsForm] input[name='del_image" + num + "']").length) {
			$("form[name=goodsForm] input[name='del_image" + num + "']").val('0');
		}
	});

	$('#btn2_image1, #btn2_image2, #btn2_image3').find('.xi-close').click(function(e){
		var num = $(this).parent().attr("data-num");
		$("form[name=goodsForm] input[name='image" + num + "']").val('');
		if($("form[name=goodsForm] input[name='del_image" + num + "']").length) {
			$("form[name=goodsForm] input[name='del_image" + num + "']").val('1');
		}
		$("#image" + num).attr("src", "../common/img/blank.gif");
		$("#image" + num).hide();
		$("#btn2_image" + num).hide();
		$("#btn_image" + num).fadeIn(100);
	});
	

	$('#qty_type').click(function(e){
		var ckLimit = $('form[name=goodsForm] input[name=qty_type]');
		var ckQty	= $('form[name=goodsForm] input[name=qty]');
		if($(this).hasClass('colorOrange')) {					
			ckLimit.val('0');
			ckQty.attr('readonly',false);
			ckQty.removeClass('darks');
			$(this).removeClass('colorOrange');
		}
		else {
			ckLimit.val('1');
			ckQty.attr('readonly',true);					
			ckQty.addClass('darks');
			$(this).addClass('colorOrange');					
		}
	});

	$('#require_autofill').click(function(e){			
		if($('form[name=goodsForm] input[name=require_order]').val()) {
			var rq_order = $('form[name=goodsForm] input[name=require_order]').val().split(',');
			var rq_cnt = rq_order.length; 
		
			if($('#require_autofill').prop("checked")) {
				var info = "상품상세 참조";
			}
			else var info = "";			

			for(i=0; i<rq_cnt; i++) {
				$('form[name=goodsForm] input[name=require_info'+rq_order[i]+']').val(info);
			}
		}
	});

	$('#require_view').click(function(e){			
		if($(this).prop("checked")) {
			$('.requireConf').slideUp("fast", function() { $('.sn_detail').summernote('airObject'); });				
		}
		else {
			$('.requireConf').slideDown("slow", function() { $('.sn_detail').summernote('airObject'); });								
		}
	});		

	$('form[name=goodsForm] input[name=mileage_type]').click(function(e){
		ckmileageType();
	});

	$('form[name=goodsForm] input[name=delivery_type]').click(function(e){
		ckDeliveryType();
	});
	
	$('#addGoods').click(function(e){
		iframeView("popup_goods_search.php?type=relatedGoods", "상품검색");
	});	

	$(".iconSelect2").find('li').each(function(i) {
		$t = jQuery(this);			
		$t.click(function(e){	
			if($(this).hasClass('selected')) {
				$(this).removeClass('selected');
				$(this).children("input").prop('checked', false);
			}
			else {			
				$(this).addClass('selected');
				$(this).children("input").prop('checked', true);
			}		
		});
	});
	
	if($("form[name=goodsForm] select[name='vendor']").length) {
		if($("form[name=goodsForm] select[name='vendor']").val()) vendorPrice();
		else hqPrice();
	}
	else vendorPrice();

	if($('form[name=goodsForm] input[name=qty_type]').val()==1) $('#qty_type').trigger('click');
	if($("form[name=goodsForm] input[name='detail_image_only']").val() == 0) {
		$('#contentMenuDetailSub2').trigger('click');	
	}
	if($("form[name=goodsForm] select[name='related_goods_type']").val()==4) $('#relatedGoods').show();
	if($("form[name=goodsForm] select[name='information_use']").val()==3) {
		$('.infoMenuDetail').show();
		$('#delivery_info').show();
	}	
	if($("form[name=goodsForm] select[name='vendor']").length) { 
		vendorChange($("form[name=goodsForm] select[name='vendor']").val());
	}
	if($('form[name=goodsForm] input[name=commission_type]').val() == 1) {
		if($('#commi_individual').length) $('#commi_individual').trigger('click');
	}

	$('.optionConf').hide();
	ckmileageType();
	ckDeliveryType();		

	tplProcInit();
	$('.sn_detail').summernote('airObject');
	//$('.sn_name').summernote({focus: true});
	imageStatus();
});

$(window).on('resize', function() { 
	$('.sn_name').summernote('airObject');
	$('.sn_detail').summernote('airObject');
});	