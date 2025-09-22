function cartSumProc(ck) {

	var total_price		= 0;
	var total_sum		= 0;
	var total_delivery	= 0;
	var total_discount	= 0;
	var total_cnt		= 0;
	var total_mileage	= 0;	
	
	$(".cartVendorList").each(function(i){								
		var vendor				= $(this).attr('data-vendor');
		var delivery_type		= $(this).attr('data-delivery-type');
		var delivery_price1		= parseInt($(this).attr('data-delivery-price1'));
		var delivery_price2		= parseInt($(this).attr('data-delivery-price2'));
		var check_price			= 0;
		var sum_price			= 0;
		var sum_delivery		= 0;
		var sum_discount		= 0;
		var sum_cnt				= 0;
		var sum_mileage			= 0;
		var delivery_free		= 0;
		var sum_delivery_option = new Array();

		$(".select_" + vendor).each(function(i){
			if($(this).prop("checked")) {
				var uid					= $(this).val();
				var total				= parseInt(str_replace(",", "", $('.listItem' + uid).find('.total_price').html()));
				var g_delivery_type		= $('.listItem' + uid).attr('data-delivery-type');
				var g_delivery_type_qty	= $('.listItem' + uid).attr('data-delivery-type-qty');
				var g_delivery_price	= parseInt($('.listItem' + uid).attr('data-delivery-price'));
				var mileage				= $('.listItem' + uid).attr('data-mileage');
				var qty					= parseInt($("form[name=cartForm] input[name='qty_" + uid + "']").val());
				var option_use			= $('.listItem' + uid).attr('data-option-use');
				var g_uid				= $('.listItem' + uid).attr('data-guid');
				
				if($('.listItem' + uid).find('.total_orig_price').length > 0) {
					var orig_total		= parseInt(str_replace(",", "", $('.listItem' + uid).find('.total_orig_price').html()));
					sum_discount += orig_total - total;
					total = orig_total;
				}
				sum_price += total;
				if(g_delivery_type == '1' && delivery_type == 'P') check_price += total;
				else if(g_delivery_type == '2') delivery_free = 1;
				else if(g_delivery_type == '4') sum_delivery += g_delivery_price;
				else if(g_delivery_type == '5') {
					if(option_use == '1') {
						if(!sum_delivery_option[g_uid]) {
							var option_qty = 0;
							$(".select_" + vendor).each(function(i2){
								if($(this).prop("checked")) {
									var uid2	= $(this).val();	
									if($('.listItem' + uid2).attr('data-guid') == g_uid) {
										option_qty += parseInt($("form[name=cartForm] input[name='qty_" + uid2 + "']").val());
									}
								}									
							});
							var tmp_qty = Math.ceil(option_qty / parseInt(g_delivery_type_qty));
							sum_delivery += g_delivery_price * tmp_qty;
							sum_delivery_option[g_uid] = tmp_qty;
						}	
					}
					else {
						var tmp_qty = Math.ceil(qty / parseInt(g_delivery_type_qty));
						sum_delivery += g_delivery_price * tmp_qty;
					}
				}
				sum_cnt ++;
				sum_mileage	+= mileage * qty;

			}
		});
		
		$(".listSum_" + vendor).find(".listItemPrices").html(number_format(sum_price));
		$(".listSum_" + vendor).find(".listItemDiscount").html(number_format(sum_discount));
		
		if(sum_cnt > 0) {
			if(check_price > 0 && delivery_free == 0 && delivery_type != 'F') {
				if(sum_price < delivery_price1) sum_delivery += delivery_price2;
			}
		}
		
		$(".listSum_" + vendor).find(".listItemDelivery").html(number_format(sum_delivery));
		$(".listSum_" + vendor).find(".listItemTotal").html(number_format(sum_price - sum_discount + sum_delivery));
		
		total_price		+= sum_price;
		total_delivery	+= sum_delivery;
		total_discount	+= sum_discount;
		total_cnt		+= sum_cnt;
		total_sum		+= (sum_price - sum_discount + sum_delivery);
		total_mileage	+= sum_mileage;
	});
	
	$(".cartSum").find(".itemCnt").html(number_format(total_cnt));
	$(".cartSum").find(".itemDiscount").html(number_format(total_discount));
	$(".cartSum").find(".itemDelivery").html(number_format(total_delivery));
	$(".cartSum").find(".itemPrice").html(number_format(total_price));
	$(".cartSum").find(".itemSum").html(number_format(total_sum));
	$(".cartSum").find(".itemMileage").html(number_format(total_mileage));

	if($("#btnFixOrder").length) {
		$("#btnFixOrder").find(".itemCnt").html(number_format(total_cnt));
		$("#btnFixOrder").find(".itemSum").html(number_format(total_sum));
	}	
	
	if(!ck) {
		var ret = [];
		$("form[name=cartForm] input[name='item[]']:checkbox:checked").each(function(i) {
			ret.push($(this).val());
		});
		
		var data = new FormData();
		data.append('item', ret);
		data.append('mode', 'selects');
		
		$.ajax({
				url: 'php/goods_cart_post_json.php', 
				type: 'POST',
				data: data, 
				cache: false,
				dataType: 'json',
				processData: false, 
				contentType: false, 
				success: function(data, textStatus, jqXHR) {					
					if(typeof(data.error) === 'undefined') {						
						//alertify.success("선택상품이 변경되었습니다.");	
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
}

function qtyProc(uid, type, ck) {
	var qty_obj		= $("form[name=cartForm] input[name='qty_" + uid + "']");
	if(qty_obj.val() == '') qty_obj.val(1);
	var qty				= parseInt(qty_obj.val());
	var orig_qty		= qty;
	var price			= parseInt(str_replace(",", "", $('.listItem' + uid).attr("data-price")));
	var orig_price		= parseInt(str_replace(",", "", $('.listItem' + uid).attr("data-orig-price")));
	var mileage			= parseInt(str_replace(",", "", $('.listItem' + uid).attr("data-mileage")));
		
	if(type == 'plus') qty += 1;
	else if(type == 'minus') {								
		if(qty > 1) qty -= 1;
	}
	qty_obj.val(qty);

	price		= price * qty;
	orig_price	= orig_price * qty;
	mileage		= mileage * qty; 

	$(".listItem" + uid).find(".total_price").html(number_format(price));
	$(".listItem" + uid).find(".mileage_price").html(number_format(mileage) + 'P');
	if($('.listItem' + uid).find('.total_orig_price').length > 0) {
		$(".listItem" + uid).find(".total_orig_price").html(number_format(orig_price));
	}

	if(!ck) {
		var data = new FormData();
		data.append('uid', uid);
		data.append('qty', qty);
		data.append('mode', 'qtys');
		
		$.ajax({
				url: 'php/goods_cart_post_json.php', 
				type: 'POST',
				data: data, 
				cache: false,
				dataType: 'json',
				processData: false, 
				contentType: false, 
				success: function(data, textStatus, jqXHR) {					
					if(typeof(data.error) === 'undefined') {						
						//alertify.success("수량이 변경되었습니다.");	
					}
					else {
						if(typeof(data.qty) != 'undefined') {
							if(data.qty == 0) {
								window.location.href = '?channel=cart';
								return;
							}
							qty_obj.val(data.qty);							
						}
						else qty_obj.val(orig_qty);

						qtyProc(uid, '', 1);					

						alertify.error(data.error);
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alertify.error(textStatus);
				}		
			});	
	}

	cartSumProc(1);
}

function cartListDelete(uid) {
	if(!uid) return; 

	var data = new FormData();
	data.append('uid', uid);
	data.append('mode', 'delete');
	
	$.ajax({
			url: 'php/goods_cart_post_json.php', 
			type: 'POST',
			data: data, 
			cache: false,
			dataType: 'json',
			processData: false, 
			contentType: false, 
			success: function(data, textStatus, jqXHR) {					
				if(typeof(data.error) === 'undefined') {						
					
					if(uid == 'all') {
						$(".listTopSelect").hide();
						$(".cartVendorList").hide();	
						$(".cartSum").hide();
						$(".emptyList").show();
						$(".total_cnt").html('0');
					}
					else {
						if(typeof(uid)=='string') var ck_uid = uid.split(',');
						else var ck_uid = uid;

						for(i = 0, cnt = ck_uid.length; i < cnt; i ++) {			
							$(".listItem" + ck_uid[i]).remove();
							$(".total_cnt").html(parseInt($(".total_cnt").html()) - 1);
						}
					}

					if(parseInt($(".total_cnt").html()) == 0) {
						$(".listTopSelect").hide();
						$(".cartVendorList").hide();	
						$(".cartSum").hide();
						$(".emptyList").show();					
						if($(".btnFixOrder").length) {
							$(".btnFixOrder").hide();
							$("#fixMenu").show();		
						}
					}
					
					alertify.success("장바구니에서 삭제 되었습니다.");
					checkCartList();
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

function checkCartList() {
	$(".cartVendorList").each(function(i){								
		var vendor = $(this).attr('data-vendor');			
		if($(this).find(".listItem").length == 0) $(this).remove();
	});
	cartSumProc(1);
}

function option_change1(o_uid, uid, qty) {
	$(".listItem" + o_uid).remove();
	$("form[name=cartForm] input[name='qty_" + uid + "']").val(qty);
	qtyProc(uid, '', 1);
	alertify.closeAll('iframeDialog');
}

function option_change2(uid, qty, op_name) {
	$(".listItem" + uid).find(".option_name").html(op_name);
	$("form[name=cartForm] input[name='qty_" + uid + "']").val(qty);
	qtyProc(uid, '', 1);
	alertify.closeAll('iframeDialog');
}

function option_change3(uid, qty) {
	$("form[name=cartForm] input[name='qty_" + uid + "']").val(qty);
	qtyProc(uid, '', 1);
	alertify.closeAll('iframeDialog');
}

function cartPost() {		
	var ret = [];
	$("form[name=cartForm] input[name='item[]']:checkbox:checked").each(function(i) {
		ret.push($(this).val());
	});

	if(ret.length==0) {
		alertify.error("주문하실 상품을 선택 해 주세요.")
		return;
	}

	if(isLogin == 0) {
		window.location.href	= "index.php?channel=login&channel2=order";
		return;
	}
	else window.location.href = "index.php?channel=order";
}	


$(function() {

	$('.btnOption').click(function(e) {	
		var uid		= $(this).parents(".listItem").attr("data-uid");
		var g_uid	= $(this).parents(".listItem").attr("data-guid");
		
		if(typeof(is_mobile) != 'undefined') iframeWidth = '100%';
		else iframeWidth = '600px';
		iframeHeight = '.78';
		iframeView("php/popup_option.php?uid=" + uid + "&g_uid=" + g_uid, "옵션변경");		
	});	

	$(".cartVendorList").each(function(i){								
		var vendor = $(this).attr('data-vendor');

		$("form[name=cartForm] input[name='select_" + vendor + "']").click(function(e) {
			if($(this).prop("checked")) {
				$(".select_" + vendor).each(function(i2){
					if(!$(this).prop("disabled")) $(this).prop("checked", true);
				});
			}
			else $(".select_" + vendor).prop("checked", false);

			cartSumProc();
		});
	});

	$("form[name=cartForm] input[name='select_all']").click(function(e) {
		if($(this).prop("checked")) {
			$(".selectAll").each(function(i2){
				if(!$(this).prop("disabled")) $(this).prop("checked", true);
			});
		}
		else $(".selectAll").prop("checked", false);

		cartSumProc();
	});

	$('.btnFavGoods').click(function() {		
		if(isLogin == 0) {
			alertify.error("먼저 로그인을 하시기 바랍니다.");
			return;
		}
		var uid = $(this).attr("data-guid");
		addFavoriteGoods2(uid);
	});		

	$('.listItemX').click(function() {		
		var uid	= $(this).parents(".listItem").attr("data-uid");
		cartListDelete(uid);			
	});

	$('.btnSecDelete').click(function() {
		var ret = [];
		$("form[name=cartForm] input[name='item[]']:checkbox:checked").each(function(i) {
			ret.push($(this).val());
		});

		if(ret.length==0) {
			alertify.error("선택된 상품이 없습니다.")
			return;
		}

		cartListDelete(ret);			
	});

	$(".btnDirectOrder").click(function(e){
		var uid = $(this).parents(".listItem").attr("data-uid");
		
		var data = new FormData();
		data.append('uid', uid);
		data.append('mode', 'direct');
		
		$.ajax({
				url: 'php/goods_cart_post_json.php', 
				type: 'POST',
				data: data, 
				cache: false,
				dataType: 'json',
				processData: false, 
				contentType: false, 
				success: function(data, textStatus, jqXHR) {					
					if(typeof(data.error) === 'undefined') {						
						if(isLogin == 0) {
							window.location.href	= "index.php?channel=login&channel2=order&direct=1";
							return;
						}
						else window.location.href = "index.php?channel=order&direct=1";
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

	$('.btnAllDelete').click(function() {		
		cartListDelete('all');
	});

	$("form[name=cartForm] input[name='item[]']").click(function(e) {
		cartSumProc();
	});

	$(".item_plus").click(function(e) {
		var uid			= $(this).parents('.listItem').attr("data-uid");				
		qtyProc(uid, 'plus');
	});

	$(".item_minus").click(function(e) {
		var uid			= $(this).parents('.listItem').attr("data-uid");
		qtyProc(uid, 'minus');
	});

	$(".store").click(function(e) {
		var vendor			= $(this).parents('.cartVendorList').attr("data-vendor");
		if(vendor) {
			window.location.href = "index.php?channel=store&vendor=" + vendor;
		}
	});

	$(".qty_").change(function(e){
		var uid			= $(this).parents('.listItem').attr("data-uid");				
		qtyProc(uid, '');
	});	

	cartSumProc(1);

});

/********************************* 네이버 페이용 *****************************/
function cart_nc(){	
	
	var ret = [];
	$("form[name=cartForm] input[name='item[]']:checkbox:checked").each(function(i) {
		ret.push($(this).val());
	});

	if(ret.length==0) {
		alertify.error("주문하실 상품을 선택 해 주세요.")
		return;
	}

	window.open("php/naverGoodsCart.php"); 
}
/********************************* 네이버 페이용 *****************************/