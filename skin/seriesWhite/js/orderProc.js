function totalProc() {
	$(".cartSum").find(".itemDiscount2").html(number_format($("form[name=orderForm] input[name='use_coupon']").val()));

	var price		= parseInt(str_replace(",", "", $(".cartSum").find(".itemPrice").html()));
	var discount	= parseInt(str_replace(",", "", $(".cartSum").find(".itemDiscount").html()));
	var discount2	= parseInt(str_replace(",", "", $(".cartSum").find(".itemDiscount2").html()));
	var mileage		= parseInt(str_replace(",", "", $(".cartSum").find(".itemMileage2").html()));
	var delivery	= parseInt(str_replace(",", "", $(".cartSum").find(".itemDelivery").html()));

	var totals		= price - discount - discount2 - mileage + delivery;

	if(totals < 0 && mileage > 0) {
		ck_mileage	= mileage + totals;
		totals		= 0;
		$("form[name=orderForm] input[name='use_mileage']").val(number_format(ck_mileage));
		$(".cartSum").find(".itemMileage2").html(number_format(ck_mileage));
	}


	$(".cartSum").find(".itemSum").html(number_format(totals));
	
	if($("#btnFixOrder").length) {
		$("#btnFixOrder").find(".itemSum").html(number_format(totals));
	}	

	$("form[name=orderForm] input[name='pay_total']").val(totals);

	if($("form[name=orderForm] input[name='pay_total']").val() == 0) {
		$(".pay_type").hide();
		$(".pay_typeM").show().trigger('click');			
	}
	else {
		$(".pay_type").show();
		$(".pay_typeM").hide();
		$(".pay_type:first-child").trigger('click');
	}

}

function mileageCheck() {
	var obj				= $("form[name=orderForm] input[name='use_mileage']");		
	var use_mileage		= parseInt(str_replace(",", "", obj.val()));
	var have_mileage	= parseInt(str_replace(",", "", obj.attr("data-mileage")));
	var price			= parseInt(str_replace(",", "", $(".cartSum").find(".itemPrice").html()));
	var discount		= parseInt(str_replace(",", "", $(".cartSum").find(".itemDiscount").html()));
	var discount2		= parseInt(str_replace(",", "", $(".cartSum").find(".itemDiscount2").html()));
	var delivery		= parseInt(str_replace(",", "", $(".cartSum").find(".itemDelivery").html()));
	var total			= price - discount - discount2 + delivery;
	
	if(have_mileage < use_mileage) obj.val(number_format(have_mileage));
	if(total < use_mileage) obj.val(number_format(total));
	
	$(".cartSum").find(".itemMileage2").html(obj.val());

	totalProc();
}


function addressCheck() {
	var direct		= $("form[name=orderForm] input[name='direct']").val();	
	var address		= $("form[name=orderForm] input[name='address1']").val();
	var postcode	= $("form[name=orderForm] input[name='postcode']").val();

	$(".add_delivery").html('0');
	$(".cartSum").find(".itemDelivery").html(number_format(parseInt(str_replace(",", "", default_delivery))));
	totalProc();
	$(".addDelivery").hide();

	if(!address) return;
	
	$(".cartVendorList .listInfo").each(function(i) {
		vendor		= $(this).attr("data-vendor");
		delivery3	= $(this).attr("data-delivery3");

		if(delivery3 == 0) {			
			var data = new FormData();
			data.append('direct', direct);
			data.append('vendor', vendor);
			data.append('address', address);
			data.append('postcode', postcode);
			
			$.ajax({
					url: 'php/order_address_delivery_json.php', 
					type: 'POST',
					data: data, 
					cache: false,
					dataType: 'json',
					processData: false, 
					contentType: false, 
					success: function(data, textStatus, jqXHR) {					
						if(typeof(data.error) === 'undefined') {						
							if(data.price > 0) {
								$(".add_delivery").html(number_format(parseInt(str_replace(",", "", $(".add_delivery").html())) + parseInt(data.price)));
								$(".cartSum").find(".itemDelivery").html(number_format(parseInt(str_replace(",", "", $(".cartSum").find(".itemDelivery").html())) + parseInt(data.price)));
								totalProc();
								alertify.success("추가배송비가 적용 되었습니다.");
								$(".addDelivery").show();
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
	});
}

function cp_proc(order_num, mny, cp, goods_info, addInfo, payment_escrow_yn, ediDate, hashString) {
	
	if($("form[name=orderForm] input[name='cell']").val()) var cell = $("form[name=orderForm] input[name='cell']").val();
	else var cell = $("form[name=orderForm] input[name='cell2']").val();

	if(cp == 'KCP') {
		var method_array	= new Array();
		method_array["C"]	= "100000000000";
		method_array["R"]	= "010000000000";
		method_array["V"]	= "001000000000";
		method_array["H"]	= "000010000000";
		
		$("form[name=order_info] input[name='ordr_idxx']").val(order_num);
		$("form[name=order_info] input[name='good_mny']").val(mny);
		$("form[name=order_info] input[name='buyr_name']").val($("form[name=orderForm] input[name='name']").val());
		$("form[name=order_info] input[name='buyr_tel1']").val();
		$("form[name=order_info] input[name='buyr_tel2']").val(cell);
		$("form[name=order_info] input[name='buyr_mail']").val(cell);
		$("form[name=order_info] input[name='pay_method']").val(method_array[$("form[name=orderForm] input[name='pay_type']").val()]);
		$("form[name=order_info] input[name='rcvr_name']").val($("form[name=orderForm] input[name='name2']").val());
		$("form[name=order_info] input[name='rcvr_tel1']").val($("form[name=orderForm] input[name='cell2']").val());
		$("form[name=order_info] input[name='rcvr_tel2']").val($("form[name=orderForm] input[name='cell2']").val());
		$("form[name=order_info] input[name='rcvr_mail']").val($("form[name=orderForm] input[name='email']").val());
		$("form[name=order_info] input[name='rcvr_zipx']").val($("form[name=orderForm] input[name='postcode']").val());
		$("form[name=order_info] input[name='rcvr_addr1']").val($("form[name=orderForm] input[name='address1']").val());
		$("form[name=order_info] input[name='rcvr_addr2']").val($("form[name=orderForm] input[name='address2']").val());
		$("form[name=order_info] input[name='param_opt_1']").val(addInfo);
		$("form[name=order_info] input[name='escw_used']").val(payment_escrow_yn);
		$("form[name=order_info] input[name='pay_mod']").val(payment_escrow_yn);

		pay_post();
	}
	else if(cp == 'NICEPAY') {
		var method_array	= new Array();
		method_array["C"]	= "CARD";
		method_array["R"]	= "BANK";
		method_array["V"]	= "VBANK";
		method_array["H"]	= "CELLPHONE";

		$("form[name=payForm] input[name='Moid']").val(order_num);
		$("form[name=payForm] input[name='Amt']").val(mny);
		$("form[name=payForm] input[name='BuyerName']").val($("form[name=orderForm] input[name='name']").val());
		$("form[name=payForm] input[name='BuyerTel']").val(cell);
		$("form[name=payForm] input[name='BuyerEmail']").val($("form[name=orderForm] input[name='email']").val());
		$("form[name=payForm] input[name='PayMethod']").val(method_array[$("form[name=orderForm] input[name='pay_type']").val()]);
		$("form[name=payForm] input[name='ReqReserved']").val(addInfo);
		$("form[name=payForm] input[name='TransType']").val(payment_escrow_yn);
		$("form[name=payForm] input[name='EdiDate']").val(ediDate);
		$("form[name=payForm] input[name='SignData']").val(hashString);

		nicepayStart();
	}	
	else if(cp == 'INICIS') {
		var method_array	= new Array();
		method_array["C"]	= "Card";
		method_array["R"]	= "DirectBank";
		method_array["V"]	= "VBank";
		method_array["H"]	= "HPP";

		$("form[name=payForm] input[name='oid']").val(order_num);
		$("form[name=payForm] input[name='price']").val(mny);
		$("form[name=payForm] input[name='buyername']").val($("form[name=orderForm] input[name='name']").val());
		$("form[name=payForm] input[name='buyertel']").val(cell);
		$("form[name=payForm] input[name='buyeremail']").val($("form[name=orderForm] input[name='email']").val());
		$("form[name=payForm] input[name='gopaymethod']").val(method_array[$("form[name=orderForm] input[name='pay_type']").val()]);
		$("form[name=payForm] input[name='merchantData']").val(addInfo);	
		$("form[name=payForm] input[name='timestamp']").val(ediDate);
		$("form[name=payForm] input[name='signature']").val(hashString);

		if(payment_escrow_yn == '1') $("form[name=payForm] input[name='acceptmethod']").val($("form[name=payForm] input[name='acceptmethod']").val() + ':useescrow');

		paybtn();
	}	

}

function mobile_cp_proc(order_num, mny, cp, goods_info, addInfo, payment_escrow_yn, ediDate, hashString) {

	if($("form[name=orderForm] input[name='cell']").val()) var cell = $("form[name=orderForm] input[name='cell']").val();
	else var cell = $("form[name=orderForm] input[name='cell2']").val();

	if(cp == 'KCP') {
		var method_array	= new Array();
		method_array["C"]	= "CARD";
		method_array["R"]	= "BANK";
		method_array["V"]	= "VCNT";
		method_array["H"]	= "MOBX";
		
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='ordr_idxx']").val(order_num);
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='good_mny']").val(mny);
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='buyr_name']").val($("form[name=orderForm] input[name='name']").val());
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='buyr_tel1']").val(cell);
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='buyr_tel2']").val(cell);
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='buyr_mail']").val($("form[name=orderForm] input[name='email']").val());
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='pay_method']").val(method_array[$("form[name=orderForm] input[name='pay_type']").val()]);
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='rcvr_name']").val($("form[name=orderForm] input[name='name2']").val());
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='rcvr_tel1']").val($("form[name=orderForm] input[name='cell2']").val());
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='rcvr_tel2']").val($("form[name=orderForm] input[name='cell2']").val());
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='rcvr_mail']").val($("form[name=orderForm] input[name='email']").val());
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='rcvr_zipx']").val($("form[name=orderForm] input[name='postcode']").val());
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='rcvr_addr1']").val($("form[name=orderForm] input[name='address1']").val());
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='rcvr_addr2']").val($("form[name=orderForm] input[name='address2']").val());
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='good_name']").val($("form[name=orderForm] input[name='good_name']").val());
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='bask_cntx']").val($("form[name=orderForm] input[name='bask_cntx']").val());
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='quotaopt']").val($("form[name=orderForm] input[name='payment_install_range']").val());
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='param_opt_1']").val(addInfo);
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='escw_used']").val(payment_escrow_yn);
		$("#HFrmOrder").contents().find("form[name=order_info] input[name='pay_mod']").val(payment_escrow_yn);

		$("#HFrmOrder").show();
		$("#contents").hide();

		window.name = "orderForm";

		$('#HFrmOrder')[0].contentWindow.pay_post();
	}
	else if(cp == 'NICEPAY') {
		var method_array	= new Array();
		method_array["C"]	= "CARD";
		method_array["R"]	= "BANK";
		method_array["V"]	= "VBANK";
		method_array["H"]	= "CELLPHONE";

		$("#HFrmOrder").contents().find("form[name=payForm] input[name='Moid']").val(order_num);
		$("#HFrmOrder").contents().find("form[name=payForm] input[name='Amt']").val(mny);
		$("#HFrmOrder").contents().find("form[name=payForm] input[name='BuyerName']").val($("form[name=orderForm] input[name='name']").val());
		$("#HFrmOrder").contents().find("form[name=payForm] input[name='BuyerTel']").val(cell);
		$("#HFrmOrder").contents().find("form[name=payForm] input[name='BuyerEmail']").val($("form[name=orderForm] input[name='email']").val());
		$("#HFrmOrder").contents().find("form[name=payForm] input[name='PayMethod']").val(method_array[$("form[name=orderForm] input[name='pay_type']").val()]);
		$("#HFrmOrder").contents().find("form[name=payForm] input[name='SelectQuota']").val($("form[name=orderForm] input[name='payment_install_range']").val());
		$("#HFrmOrder").contents().find("form[name=payForm] input[name='GoodsName']").val($("form[name=orderForm] input[name='good_name']").val());
		$("#HFrmOrder").contents().find("form[name=payForm] input[name='ReqReserved']").val(addInfo);
		$("#HFrmOrder").contents().find("form[name=payForm] input[name='TransType']").val(payment_escrow_yn);
		$("#HFrmOrder").contents().find("form[name=payForm] input[name='EdiDate']").val(ediDate);
		$("#HFrmOrder").contents().find("form[name=payForm] input[name='SignData']").val(hashString);

		$("#HFrmOrder").show();
		$("#contents").hide();

		window.name = "orderForm";

		$('#HFrmOrder')[0].contentWindow.nicepayStart();
	}
	else if(cp == 'INICIS') {
		var method_array	= new Array();
		method_array["C"]	= "CARD";
		method_array["R"]	= "BANK";
		method_array["V"]	= "VBANK";
		method_array["H"]	= "MOBILE";

		$("#HFrmOrder").contents().find("form[name=mobileweb] input[name='P_OID']").val(order_num);
		$("#HFrmOrder").contents().find("form[name=mobileweb] input[name='P_AMT']").val(mny);
		$("#HFrmOrder").contents().find("form[name=mobileweb] input[name='P_UMANE']").val($("form[name=orderForm] input[name='name']").val());
		$("#HFrmOrder").contents().find("form[name=mobileweb] input[name='P_MOBILE']").val(cell);
		$("#HFrmOrder").contents().find("form[name=mobileweb] input[name='P_EMAIL']").val($("form[name=orderForm] input[name='email']").val());
		$("#HFrmOrder").contents().find("form[name=mobileweb] input[name='P_INI_PAYMENT']").val(method_array[$("form[name=orderForm] input[name='pay_type']").val()]);
		$("#HFrmOrder").contents().find("form[name=mobileweb] input[name='P_GOODS']").val($("form[name=orderForm] input[name='good_name']").val());
		$("#HFrmOrder").contents().find("form[name=mobileweb] input[name='P_QUOTABASE']").val($("form[name=orderForm] input[name='payment_install_range2']").val());		
		$("#HFrmOrder").contents().find("form[name=mobileweb] input[name='P_NOTI']").val(addInfo);		
		if(payment_escrow_yn == '1') $("#HFrmOrder").contents().find("form[name=mobileweb] input[name='P_RESERVED']").val($("#HFrmOrder").contents().find("form[name=mobileweb] input[name='P_RESERVED']").val() + '&useescrow=Y');
		
		$("#HFrmOrder").show();
		$("#contents").hide();

		window.name = "orderForm";
	
		$('#HFrmOrder')[0].contentWindow.on_pay();
	}	
	
}


function mobile_cp_cancel(order_num) {
	
	mobile_cp_cancel2();
	cp_cancel(order_num);
}

function mobile_cp_cancel2() {
	
	$("#HFrmOrder").hide();
	$("#contents").show();	
	$(".btnOrderHide").removeClass('shineSend2'); 
}


function cp_cancel(order_num) {
	if(!order_num) {
		if($("form[name=order_info] input[name='ordr_idxx']").length) order_num = $("form[name=order_info] input[name='ordr_idxx']").val();
		else if($("form[name=payForm] input[name='Moid']").length) order_num = $("form[name=payForm] input[name='Moid']").val();
		else if($("form[name=payForm] input[name='oid']").length) order_num = $("form[name=payForm] input[name='oid']").val();
	}
	if(!order_num) return;
	
	var data = new FormData();
	data.append('order_num', order_num);
		
	$.ajax({
			url: 'php/order_cp_cancel_json.php', 
			type: 'POST',
			data: data, 
			cache: false,
			dataType: 'json',
			processData: false, 
			contentType: false, 
			success: function(data, textStatus, jqXHR) {					
				if(typeof(data.error) === 'undefined') {						
					if($(".shineButtonBlack.orderPost").length) {
						$(".shineButtonBlack.orderPost").removeClass('shineSend2'); 
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

$(function() {
	
	$(".btnMileageAll").click(function() {
		var obj				= $("form[name=orderForm] input[name='use_mileage']");
		var have_mileage	= parseInt(str_replace(",", "", obj.attr("data-mileage")));	
		obj.trigger('focus').val(number_format(have_mileage));
		mileageCheck();			
	});

	$(".btnViewGoods").click(function() {
		$(".cartList").find(".listItem").show();
		$(this).hide();
		$(".btnHideGoods").show();	
	});

	$(".btnHideGoods").click(function() {
		$(".cartList").find(".listItem").hide();
		$(this).hide();
		$(".btnViewGoods").show();	
	});

	$(".recentAddr1").click(function() {
		$(".recentAddr2").removeClass("select");
		$(".recentAddr1").addClass("select");
		$(".recent_address").show();
		$(".new_address").hide();
		
		$(".btnRecentAddr1").trigger('click');			
	});

	$(".recentAddr2").click(function() {
		$(".recentAddr1").removeClass("select");
		$(".recentAddr2").addClass("select");
		$(".recent_address").hide();
		$(".new_address").show();		
		
		$("form[name=orderForm] input[name='name2']").val('').trigger('blur');
		$("form[name=orderForm] input[name='cell2']").val('').trigger('blur');
		$("form[name=orderForm] input[name='postcode']").val('').trigger('blur');
		$("form[name=orderForm] input[name='address1']").val('').trigger('blur');
		$("form[name=orderForm] input[name='address2']").val('').trigger('blur');

		addressCheck();
	});

	$(".btnRecentAddr, .btnRecentAddr1").click(function() {
		$("form[name=orderForm] input[name='name2']").val($(this).attr("data-name"));
		$("form[name=orderForm] input[name='cell2']").val($(this).attr("data-cell"));
		$("form[name=orderForm] input[name='postcode']").val($(this).attr("data-postcode"));
		$("form[name=orderForm] input[name='address1']").val($(this).attr("data-address1"));
		$("form[name=orderForm] input[name='address2']").val($(this).attr("data-address2"));
		
		$(".recent_name").html($(this).attr("data-name") + ' / ' + $(this).attr("data-cell"));
		$(".recent_address2").html("(" + $(this).attr("data-postcode") + ') ' + $(this).attr("data-address1") + ' ' + $(this).attr("data-address2"));
		
		$(".btnRecentAddr").removeClass("select");
		$(this).addClass("select");

		addressCheck();
	});

	$(".pay_type").click(function() {
		var vls = $(this).attr("data-vls");
		
		$(".pay_type_info").hide();

		if(vls == 'M') {
			if(!parseInt($("form[name=orderForm] input[name='use_mileage']").val()) ||  parseInt($("form[name=orderForm] input[name='pay_total']").val()) > 0) return;
		}
		
		$("form[name=orderForm] input[name='pay_type']").val(vls);
		$(".pay_type").removeClass('select');
		$(".pay_type" + vls).addClass('select');
		$(".pay_type" + vls + "_info").show();
		
		if(vls == 'B') {
			$("form[name=orderForm] input[name='remittance_name']").prop("required", true);
			$("form[name=orderForm] select[name='remittance_bank']").prop("required", true);
		}
		else {
			$("form[name=orderForm] input[name='remittance_name']").prop("required", false);
			$("form[name=orderForm] select[name='remittance_bank']").prop("required", false);
		}

		if(vls == 'C' || vls == 'H' || vls == 'M') {
			$(".cash_receipts").hide();
			$("form[name=orderForm] select[name='cash_receipts_type']").prop("required", false); 
			$("form[name=orderForm] input[name='cash_receipts_num']").prop("required", false); 
		}
		else {
			$(".cash_receipts").show();
			$("form[name=orderForm] select[name='cash_receipts_type']").prop("required", default_required); 
			$("form[name=orderForm] input[name='cash_receipts_num']").prop("required", default_required); 
		}		
	});

	$(".pay_type:first-child").trigger('click');

	recentTriggrt();
	
	totalProc();

});