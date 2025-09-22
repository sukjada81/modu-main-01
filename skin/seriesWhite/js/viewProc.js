var mobile_option_open = 0;

function option_value_proc(obj) {
	if($(obj).hasClass('soldout')) return; 

	var num		=	 $(obj).parent().attr("data-num");
	var vls			= $(obj).attr("data-value");
	var info		= $(obj).attr("data-info");
	
	if(num > 0) {
		var tmps		= vls.split("|");
		var option_name	= tmps[num];
	}
	else option_name	= vls;

	$(".option_tnum" + num).removeClass("select");
	$(".option_tnum" + num).find("i").removeClass("rotate180").addClass("rotate180r");
	$(".option_snum" + num).slideUp("fast");
	
	if(info) {
		option_add(vls, info);
	}
	else {
		$(".option_name" + num).html(option_name);

		var secNum	= parseInt(num) + 1;
		var data = new FormData();
		
		data.append('uid', $("form[name=goodsForm] input[name='uid']").val());
		data.append('value', vls);

		if(typeof(viewType) != 'undefined') path = "";
		else								path = "php/";

		$.ajax({
			url: path + 'goods_option_info_json.php', 
			type: 'POST',	
			data: data, 
			cache: false,
			dataType: 'json',
			processData: false, 
			contentType: false, 
			success: function(data, textStatus, jqXHR) {
				if(typeof(data.error) === 'undefined') {
					$(".option_snum" + secNum).html('');
					$.each(data, function(key, value) {						
						if(data[key].option_info) {
							if(data[key].option_soldout == 1)	{ 
								soldout1 = "soldout";
								soldout2 = " [품절]";
							} 
							else soldout1 = soldout2 = "";
							
							price = "";
							if(data[key].option_price > 0)	price = " ( +" + number_format(data[key].option_price) + "원)";
							else if(data[key].option_price < 0)	{
								price = " ( -" + number_format(- data[key].option_price) + "원)";
							}

							insertCode = '<div class="option_value ' + soldout1 + '" data-value="' + vls + '|' + data[key].option_value + '" data-info="' + data[key].option_info + '">' + data[key].option_value + price + soldout2 + '</div>';
						}
						else {
							insertCode = '<div class="option_value" data-value="' + vls + '|' + data[key].option_value + '" data-info="">' + data[key].option_value + '</div>';
						}
						$(".option_snum" + secNum).append(insertCode);
					});
					$(".option_tnum" + secNum).show();

					$(".option_value").unbind('click').bind('click',function(e) {
						option_value_proc(this);
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

}

function option_add(vls, info) {
	vls = vls.split('|');
	title = new Array();
	for (i = 0; i < vls.length; i ++) {
		title[i] = $(".option_tnum" + i).attr("data-name") + " : " + vls[i];
		$(".option_name" + i).html($(".option_tnum" + i).attr("data-name"));
		if(i > 0) $(".option_snum" + i).html('');
	}
	title	= implode(" / ", title);

	info	= info.split('|');
	
	if($(".option_item_" + info[0]).length) { 
		alertify.error("이미 선택된 옵션 입니다.");
		return;
	}

	if($("form[name=goodsForm] input[name='coupon_down_yn']").val() == '1') {
		price = info[4];
	}
	else {
		price	= parseInt(str_replace(",", "", $("form[name=goodsForm] input[name='price']").val()));	
		if(info[1] != 0) price += parseInt(info[1]);
	}

	if(typeof(viewType) != 'undefined') $(".option_list").html('');
	if(typeof(is_mobile) != 'undefined')	var empty = 10;
	else									var empty = 20;

	var qty_ment = "";
	if(info[2] == 0) qty_ment = ' <span>(' + number_format(info[3]) + '개 남음)</span>';	
		
	var html = '<div class="option_item clearfix option_item_' + info[0] + '" data-uid="' + info[0] + '" data-price="' + info[1] + '" data-qty_type="' + info[2] + '" data-qty="' + info[3] + '" data-goods_price="' + info[4] + '"">\
					<div class="option_item_name">' + title + qty_ment + '</div>\
					<div class="option_item_x"><i class="xi-close-thin xi-x"></i></div>\
					<div class="empty' + empty + '"></div>\
					<div>\
						<div class="option_item_minus"><i class="xi-minus-thin xi-x"></i></div>\
						<div class="option_item_input"><input type="text" class="op_qty' + info[0] + ' only_num" value="1" /></div>\
						<div class="option_item_plus"><i class="xi-plus-thin xi-x"></i></div>\
						<div class="option_item_coupon">COUPON</div>\
						<div class="option_item_price"><span class="option_price">' + number_format(price) + '</span>원</div>\
					</div>\
				</div>\
			';
	$(".option_list").append(html);
	
	$(".option_item_" + info[0]).find(".option_item_plus").click(function(e){
		option_qty_proc(info[0], 'plus');
	});

	$(".option_item_" + info[0]).find(".option_item_minus").click(function(e){
		option_qty_proc(info[0], 'minus');
	});

	$(".option_item_" + info[0]).find(".op_qty" + info[0]).change(function(e){
		option_qty_proc(info[0], '', this);
	});

	$(".option_item_" + info[0]).find(".option_item_x").click(function(e){
		option_del_proc(info[0]);
	});

	if($("form[name=goodsForm] input[name='coupon_down_yn']").val() == '1') $(".option_item_" + info[0]).find(".option_item_coupon").show();

	if(typeof(viewType) != 'undefined') {
		$(".option_item_" + info[0]).find(".option_item_x").hide();
		$(".option_item_" + info[0]).find(".op_qty" + info[0]).val(option_qty);
		option_qty_proc(info[0], '');
	}

	price_total();
}

function option_qty_proc(uid, type, obj) {
	if(obj) var qty_obj			= $(obj);
	else 	var qty_obj			= $(".option_item_" + uid).find(".op_qty" + uid);

	if(qty_obj.val() == '') qty_obj.val(1);
	
	var qty				= parseInt(qty_obj.val());
	var ck_qty_type		= $(".option_item_" + uid).attr("data-qty_type");
	var ck_qty			= parseInt($(".option_item_" + uid).attr("data-qty"));
	var ck_ttl			= "재고수량";

	if(goods_limit_qty > 0) {
		ck_qty_type		= 0;
		ck_qty			= goods_able_qty;
		ck_ttl			= "구매제한수량";
	}
	
	if(type == 'plus') qty += 1;
	else if(type == 'minus') {								
		if(qty > 1) qty -= 1;
	}

	$(".option_item_" + uid).find(".op_qty" + uid).val(qty);

	if(ck_qty_type == 0) {
		if(qty > ck_qty) {
			$(".option_item_" + uid).find(".op_qty" + uid).val(ck_qty);									
			alertify.error(ck_ttl + number_format(ck_qty) + "개를 초과하여 수량이 변경 되었습니다.");
		}
	}
	
	if($("form[name=goodsForm] input[name='coupon_down_yn']").val() == '1') {
		var price			= parseInt($(".option_item_" + uid).attr("data-goods_price"));		
	}
	else {
		var price			= parseInt(str_replace(",", "", $("form[name=goodsForm] input[name='price']").val()));
		var price2			= parseInt($(".option_item_" + uid).attr("data-price"));
	
		if(price2 != 0) price += price2;
	}
	
	price	= price * qty;

	$(".option_item_" + uid).find(".option_price").html(number_format(price));

	price_total();
}

function qty_proc(type, obj) {
	if(obj) var qty_obj	= $(obj);
	else 	var qty_obj	= $(".qty_");

	if(qty_obj.val() == '') qty_obj.val(1);

	var qty				= parseInt(qty_obj.val());
	var ck_qty_type		= $("form[name=goodsForm] input[name='qty_type']").val();
	var ck_qty			= $("form[name=goodsForm] input[name='save_qty']").val();
	
	if(goods_limit_qty > 0) {
		ck_qty_type		= 0;
		ck_qty			= goods_able_qty;
	}

	if(type == 'plus') qty += 1;
	else if(type == 'minus') {								
		if(qty > 1) qty -= 1;
	}

	$(".qty_").val(qty);

	if(ck_qty_type == 0) {
		if(qty > ck_qty) {
			$(".qty_").val(ck_qty);									
			alertify.error("재고수량 " + number_format(ck_qty) + "개를 초과하여 수량이 변경 되었습니다.");
			return;
		}
	}
	
	if($("form[name=goodsForm] input[name='coupon_down_yn']").val() == '1') {
		var price			= parseInt(str_replace(",", "", $("form[name=goodsForm] input[name='coupon_price']").val()));
	}
	else {
		var price			= parseInt(str_replace(",", "", $("form[name=goodsForm] input[name='price']").val()));
	}
	
	price	= price * qty;

	$(".option_price").html(number_format(price));

	price_total();
}

function option_del_proc(uid) {
	$(".option_item_" + uid).remove();
	if($(".option_list").children().length == 0) {
		var price = parseInt(str_replace(",", "", $("form[name=goodsForm] input[name='price']").val()));
		$(".total_price").html(number_format(price));
	}
	else price_total();
}

function price_total() {
	var total = 0;
	
	$(".goodsDetail .option_list .option_item").each(function(i){								
		total += parseInt(str_replace(",", "", $(this).find(".option_price").html()));
	});

	$(".total_price").html(number_format(total));	
}

function sleep (delay) {
	var start = new Date().getTime();
	while (new Date().getTime() < start + delay);
}

function orderProc() {

	var uid			= $("form[name=goodsForm] input[name='uid']").val();
	var option_cnt	= $(".goodsDetail .option_list .option_item").length;

	if(option_cnt == 0) {
		alertify.error("먼저 옵선을 선택 하시고 주문하실 상품을 추가 해 주세요.");
		return;
	}

	var data	= new FormData();
		
	data.append('uid', uid);

	if($("form[name=goodsForm] input[name='type']").val() == 1) data.append('direct', 1);
	
	$(".goodsDetail .option_list .option_item").each(async function(i){
		o_uid	= $(this).attr("data-uid");
		
		if($("form[name=goodsForm] input[name='option_cnt']").val() == 0) {
			data.append('option', 0);
			data.append('qty', $(".qty_").val());		
			opt_name = "";
		}
		else {
			data.append('option', o_uid);
			data.append('qty', $(".option_item_" + o_uid).find(".op_qty" + o_uid).val());	
			opt_name = $(".option_item_" + o_uid).find(".option_item_name").html() + " ";
		}

		data.append('opt_name', opt_name);
		
		if(i == 0)	data.append('start', 1);
		else		data.append('start', 0);

		if(typeof(is_mobile) != 'undefined') {
			mobile_option_open = 0;
			$('.btnFixClose').trigger('click');
		}
	
		await $.ajax({
			url: 'php/goods_cart_json.php', 
			type: 'POST',	
			data: data, 
			cache: false,
			dataType: 'json',
			processData: false, 
			contentType: false, 
			success: function(data, textStatus, jqXHR) {
				if(typeof(data.error) === 'undefined') {
					if(data[0].ok == 1) {
						name = "";
						if($("form[name=goodsForm] input[name='option_cnt']").val() == 1) {
							name = $(".option_item_" + data[0].option).find(".option_item_name").html();
						}
						alertify.success(name + " 상품이 장바구니에 담겼습니다.");
						$("#btnQuickCart .cart_cnt").html(parseInt($("#btnQuickCart .cart_cnt").html()) + 1);
					}
					else if(data[0].ok == 2) {
						alertify.success(data[0].opt_name + "상품의 장바구니 수량이 " + data[0].qty + "개로 변경 되었습니다.");
					}

					if(parseInt(option_cnt) - 1 == i) {
						if($("form[name=goodsForm] input[name='type']").val() == 0) {
							alertify.confirm('장바구니확인', '장바구니로 이동 하시겠습니까?', 
								function() { window.location.href = "index.php?channel=cart"; }, 
								function(){ 
									if($("form[name=goodsForm] input[name='option_cnt']").val() != 0)  $(".option_list").html(''); 
									var price = parseInt(str_replace(",", "", $("form[name=goodsForm] input[name='price']").val()));
									$(".total_price").html(number_format(price));
								});
						}
						else {
							if(isLogin == 0) {
								window.location.href	= "index.php?channel=login&channel2=order&direct=1";
								return;
							}
							window.location.href = "index.php?channel=order&direct=1";
						}					
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
	});
}


function couponDownOk() {
	$(".coupon_down").addClass("coupon_down_ok");
	$(".coupon_down span").html($(".coupon_down span").html() + '완료');
	$(".coupon_down").find('.xi-download').hide();
	$("form[name=goodsForm] input[name='coupon_down_yn']").val(1);

	$(".goodsDetail .option_list .option_item").each(function(i){
		if($("form[name=goodsForm] input[name='option_cnt']").val() == 0) {
			qty		= parseInt($(".qty_").val());
			price	= parseInt(str_replace(",", "", $("form[name=goodsForm] input[name='coupon_price']").val()));

			price	= price * qty;
			$(".option_price").html(number_format(price));
			$(".option_item_coupon").show();
		}
		else {
			o_uid	= $(this).attr("data-uid");
			qty		= parseInt($(".option_item_" + o_uid).find(".qty" + o_uid).val());
			price	= parseInt($(this).attr("data-goods_price"));

			price	= price * qty;
			$(".option_item_" + o_uid).find(".option_price").html(number_format(price));
			$(".option_item_" + o_uid).find(".option_item_coupon").show();
		}
	});

	price_total();
}

$(function() {

	if(favGoodsSelect == 1) {
		$(".btnFavGoods").addClass("colorOrange");			
	}

	if(favStoreSelect == 1) {
		$(".btnFavStore").addClass("colorOrange");			
	}

	if($("form[name=goodsForm] input[name='option_cnt']").val() == 0) {
		$(".goods_fixed .option_list").html($(".goodsDetail .option_list").html());
		
		$(".option_item_plus").click(function(e){
			qty_proc('plus');
		});

		$(".option_item_minus").click(function(e){
			qty_proc('minus');
		});

		$(".qty_").change(function(e){
			qty_proc('', this);
		});		
	}

	$(".option_title").click(function(e){
		var num		= parseInt($(this).attr("data-num"));

		if(num > 0) {
			if($(".option_snum" + num).children().length == 0) {
				var name = $(".option_tnum" + (num -1)).attr("data-name")
				alertify.error(name + "을(를) 먼저 선택 하시기 바랍니다.");
				return;
			}								
		}

		for(i = (num + 1); i < parseInt($("form[name=goodsForm] input[name='option_cnt']").val()); i ++) {
			if($(this).find(".option_tnum" + i).hasClass("select")) {
				$(this).find(".option_tnum" + i).trigger('click');
			}
		}

		if($(this).hasClass("select")) {
			$(this).removeClass("select");
			$(this).find("i").removeClass("rotate180").addClass("rotate180r");
			$(this).parent().find(".option_snum" + num).slideUp("fast");
		}
		else {
			$(this).addClass("select");
			$(this).find("i").removeClass("rotate180r").addClass("rotate180");
			$(this).parent().find(".option_snum" + num).slideDown("fast");
		}							
	});

	$(".option_value").click(function(e){
		option_value_proc(this);
	});

	$('.btnCart').click(function(e){
		$("form[name=goodsForm] input[name='type']").val('0');
		orderProc();
	});
	
	$('.btnOrder').click(function(e){		
		$("form[name=goodsForm] input[name='type']").val('1');
		orderProc();
	});

	$('.coupon_down').click(function(e){
		if($("form[name=goodsForm] input[name='coupon_down_yn']").val() == '1') return;
		var uid		= $("form[name=goodsForm] input[name='uid']").val();
		var c_uid	= $(this).attr('data-cuid');	
		
		var data	= new FormData();
		
		data.append('uid', uid);
		data.append('c_uid', c_uid);
	
		$.ajax({
			url: 'php/goods_coupon_down_json.php', 
			type: 'POST',	
			data: data, 
			cache: false,
			dataType: 'json',
			processData: false, 
			contentType: false, 
			success: function(data, textStatus, jqXHR) {
				if(typeof(data.error) === 'undefined') {
					alertify.success("쿠폰이 발급되었습니다.");	
					couponDownOk();					
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

/********************************* 네이버 페이용 *****************************/
function buy_nc(){	
				
	var uid			= $("form[name=goodsForm] input[name='uid']").val();
	var option_cnt	= $(".goodsDetail .option_list .option_item").length;

	if(option_cnt == 0) {
		alertify.error("먼저 옵선을 선택 하시고 주문하실 상품을 추가 해 주세요.");
		return;
	}
	
	var option = '';
	$(".goodsDetail .option_list .option_item").each(function(i){
		o_uid	= $(this).attr("data-uid");
		
		if($("form[name=goodsForm] input[name='option_cnt']").val() == 0) {
			option = '|*|0|' + $(".qty_").val();
		}
		else {
			option += '|*|' + o_uid + '|' + $(".option_item_" + o_uid).find(".op_qty" + o_uid).val();
		}
	});

	window.open("php/naverGoodsInfo.php?uid=" + uid + "&option=" + option, ""); 
}

function not_buy_nc(){
	alertify.error("품절된 상품입니다.");
	return false;
}

function wishlist_nc(url) { 
	// 네이버 체크아웃으로 찜 정보를 등록하는 가맹점 페이지 팝업 창 생성. 
	// 해당 페이지에서 찜 정보 등록 후 네이버 체크아웃 찜 페이지로 이동. 
	window.open(url, "", "scrollbars=yes, width=400, height=267"); 
	return false; 
}
/********************************* 네이버 페이용 *****************************/