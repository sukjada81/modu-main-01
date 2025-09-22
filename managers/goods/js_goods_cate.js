function getCateSubInfoGoods(cate) {
	if(!cate) return;
	
	var data = new FormData();
	data.append('cate', cate);

	$.ajax({
			url: '../../managers/goods/cate_sub_info_json.php', 
			type: 'POST',
			data: data, 
			cache: false,
			dataType: 'json',
			processData: false, 
			contentType: false, 
			success: function(data, textStatus, jqXHR) {					
				if(typeof(data.error) === 'undefined') {						
					
					var insertCode = '';
					var num = cate_dep + 1;
					var ck = 0;

					$.each(data, function(key, value) {
						insertCode =  '<li class="cateItem'+num+'" id="'+data[key].id+'">'+data[key].name+'</li>';
						$('#cateSelect'+num).append(insertCode);
						ck = 1;
					});
					
					if(ck==0) {
						$('#unLink'+num).html('<i class="xi-close"></i>').fadeIn(100);
						for(i=num+1;i<5; i++) {
							$('#unLink'+i).html('<i class="fas fa-unlink"></i>');
						}	
					}
					else {
						$('#unLink'+num).fadeOut(100);
					}

					cateClickSet(num);
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

function cateClickSet(num) {
	$('.cateItem'+num).on('mousedown', function(e){
		
		cate = $(this).attr('id');
		cate_dep = num;
		
		$('.cateItem'+num).removeClass('select');
		$(this).addClass('select');
		
		cate_name = $(this).html();
		eval("cate_location_name" + num + " = cate_name");

		for(i=(num+1);i<5; i++) {
			$('#cateSelect'+i).html('');
		}	
					
		getCateSubInfoGoods(cate);
		
	});
}


function cateInsert(cate_no, cate_name, cate_rep) {

	var orig_cateCnt = cateCnt;
	
	if(!cate_no) cate_no = cate;

	if(popup==1) {
		if($("form[name=goodsForm] input:radio[name=proc_type2]:checked").val()=='1') {
			if($("#emptyCate").css('display')=='none') {
				alertify.alert("대표분류는 하나만 선택 가능 하면 수정시 기존 등록된 분류 삭제 후 다시 시도 하시면 됩니다.");	
				return false;
			}
		}
	}

	for(i=1; i<=cateCnt; i++) {
		if(cate_no == $("form[name=goodsForm] input:hidden[name=cate_no"+i+"]").val()) {
			alertify.alert("이미 등록되어있는 상품분류 입니다.");
			return;
		}
	}

	if(!cate_name) {
		cate_name = "";
		for(i=1;i<=cate_dep;i++) {
			cate_name += eval("cate_location_name"+i);				
		}
		cate_name = str_replace("→"," > ",cate_name);			
	}

	if(!cate_rep) {
		if($("#emptyCate").css('display')=='block') cate_rep = "checked='checked'";
		else cate_rep = '';
	}
	else {
		if(cate_rep=='1') cate_rep = "checked='checked'";
		else cate_rep = '';
	}

	if(popup=='1') {
		if($("form[name=goodsForm] input:radio[name=proc_type2]:checked").val()==3 || $("form[name=goodsForm] input:radio[name=proc_type2]:checked").val()==4) {
			cate_rep = '';
		}
	}
	
	var insertCode = '	<tr data-idx='+cateCnt+'>';
	insertCode += '			<td style="width:10%">'+cateCnt+'</td>';
	insertCode += '			<td style="width:50%">'+cate_name+'</td>';
	insertCode += '			<td style="width:20%">'+cate_no+'<input type="hidden" name="cate_no'+cateCnt+'" value="'+cate_no+'" /></td>';
	insertCode += '			<td style="width:10%"><label><input type="radio" name="cate_rep" value="'+cate_no+'" '+cate_rep+' /><span class="radio"></span></label></td>';
	insertCode += '			<td style="width:10%"><span id="cateIcon1_'+cateCnt+'"><i class="xi-close masterTooltip" title="삭제하기"></i></span></td>';
	insertCode += '		</tr>';

	$("#cateList tbody").append(insertCode);
	
	$('#cateIcon1_'+orig_cateCnt).find('.xi-close').click(function(e){
		cateDelete(orig_cateCnt);
	});
	
	$("#emptyCate").hide();
	$("form[name=goodsForm] input[name=cate_max_cnt]").val(cateCnt);

	cateCnt++;		
	reOrder();
	resetTooltip();		
	
	if(cate_dep>1) {
		for(i=cate_dep;i<5; i++) {
			$('#unLink'+i).html('<i class="fas fa-unlink"></i>').fadeIn(100);
			$('#cateSelect'+i).html('');
		}
	}
	else {
		$('#unLink2').html('<i class="fas fa-unlink"></i>');
	}						
	$('.cateItem1').removeClass('select');	

	cate = '';
	cate_dep = 1;		

	if(!$("form[name=goodsForm] input[name=cate_cnt]").val()) $("form[name=goodsForm] input[name=cate_cnt]").val(1);
	else $("form[name=goodsForm] input[name=cate_cnt]").val($("form[name=goodsForm] input[name=cate_cnt]").val()+1);	
}

function cateDelete(idx) {		
	$('form[name=goodsForm] input[name=cate_no'+idx+']').parent('td').parent('tr').remove();
	if(!$('form[name=goodsForm] input[name=cate_rep]:checked').val()) {
		if(popup=='1') {
			if($("form[name=goodsForm] input:radio[name=proc_type2]:checked").val()==3 || $("form[name=goodsForm] input:radio[name=proc_type2]:checked").val()==4) {
				reOrder();
				return;
			}
		}
		ck_cnt = 0;
		for(i=1; i<=cateCnt; i++) {
			cate_no = $("form[name=goodsForm] input:hidden[name=cate_no"+i+"]").val();
			if(cate_no) {
				$("form[name=goodsForm] input:radio[name=cate_rep]:input[value="+cate_no+"]").prop('checked', true);
				ck_cnt = 1;
				break;
			}
		}
		if(ck_cnt==0) {
			$("form[name=goodsForm] input[name=cate_cnt]").val('');
			$("#emptyCate").show();
		}
		else {
			$("form[name=goodsForm] input[name=cate_cnt]").val($("form[name=goodsForm] input[name=cate_cnt]").val()-1);
		}
	}		

	reOrder();		
}

function reOrder() {		
	$("#cateList tbody tr td:nth-child(1)").text(function() {
		return $(this).parent().index("#cateList tbody tr") + 1;
	});
	
	if(popup==0) {
		$('.sn_name').summernote('airObject');
		$('.sn_detail').summernote('airObject');
	}
}

var insertCode = '';
for(i=0, cnt = CATEname.length; i < cnt; i ++){			
	insertCode	=  '<li class="cateItem1" id="' + CATEnum[i][0] + '">' + CATEname[i][0] + '</li>';
	$('#cateSelect1').append(insertCode);
}


$(function() {
	
	$('#cateAddBtn').click(function(e){

		if(!cate) {
			alertify.alert("먼저 상품분류를 선택 하시기 바랍니다.")
			return;
		}
		
		var ckName = eval("cate_location_name"+cate_dep);
		if(ckName.indexOf("→")!=-1) {
			alertify.alert("하위 분류가 존재하는 상품분류입니다.<br />하위분류를 선택 하시기 바랍니다.");
			return;
		}

		cateInsert();	
		
	});
	
	cateClickSet(1);

});