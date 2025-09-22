function optionTableCreate(op_order, op_cnt) {
	
	$("#optionListTable").multiBox('resetCount');
	
	var html = '<div class="optionTableTop" >\
					<table class="listIn fixed-header" style="width:calc(100% - 17px);">\
					<col width="6%" />\
			';
		
	opPer = Math.round(40 / op_cnt);
	for(i=1; i<=op_cnt; i++) {				
		html += '	<col width="'+opPer+'%" />';
	}	

	html += '		<col width="15%" />\
					<col width="12%" />\
					<col width="7%" />\
					<col />\
					<col width="7%" />\
					<thead>\
					<tr>\
						<th class="opth">번호</th>\
			';
	
	optionBox = [];
	optionBox[0] = {'name':'','width':'6','type':'','enter':0};
	
	for(i=1; i<=op_cnt; i++) {
		option_name	= $("#optionTable").find('input[name=option_name'+op_order[i-1]+']').val();
		html += '		<th class="opth">'+option_name+'</th>';
		optionBox[i] = {'name':'option_value'+i,'width':opPer,'type':'ttl','enter':0};
	}

	optionBox[i] = {'name':'option_price','width':'15','type':'num','enter':0};
	optionBox[i+1] = {'name':'option_qty','width':'12','type':'qty','enter':0}
	optionBox[i+2] = {'name':'option_used','width':'7','type':'checkbox','enter':0}
	optionBox[i+3] = {'name':'option_code','width':'','type':'text','enter':0}			
	optionBox[i+4] = {'name':'','width':'7','type':'icon','enter':0}

	html += '			<th class="opth">추가금액 <i class="xi-help xi-x colorDGary masterTooltipR" title="추가금액에 대한 매입가(공급가)는 판매가의 마진율(수수료율)이 적용 됩니댜. 차감일경우 -금액을 입력 하시면 됩니다."></i></th>\
						<th class="opth">재고 <i class="xi-help xi-x colorDGary masterTooltip" title="무제한 아이콘을 클릭하면 무제한으로 설정 됩니다."></i></th>\
						<th class="opth">노출</th>	\
						<th class="opth">자체 품목코드</th>\
						<th class="opth">처리</th>\
					</tr>\
					</thead>\
					<tbody></tbody>\
					</table>\
				</div>\
				<div class="optionTableBody">\
					<table class="listIn" id="optionListTable">\
					<col width="6%" />\
			';
	for(i=1; i<=op_cnt; i++) {				
		html += '	<col width="'+opPer+'%" />';
	}	

	html += '		<col width="15%" />\
					<col width="12%" />\
					<col width="7%" />\
					<col />\
					<col width="7%" />\
					<tbody></tbody>\
					</table>\
				</div>\
			';
	
	$('#optionDiv').html(html);			
	resetTooltip();
	$("#optionListTable").multiBox({'sortable':'1','order':'form[name=goodsForm] input[name=optionList_order]','focus':'2','list' : optionBox});
}

function optionGet(uid) {

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
					if(data[key].option_info) {						
						option_info = data[key].option_info.split('|*|');
						for(z=0,cnt=option_info.length; z<cnt; z++) {
							option_info2 = option_info[z].split('|');							
							$("#optionTable").multiBox('add2', [{'name' : 'option_name', 'value' : option_info2[0]},{'name' : 'option_info', 'value' : option_info2[1]}]);							
						}		
					}
					else {
						alertify.alert(data[key].name + "상품은 옵션이 없습니다.");						
					}
					$("form[name=goodsForm] select[name='option_select']").val('').heapbox('update');
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

$("#optionTable").multiBox({'sortable':'1','order':'form[name=goodsForm] input[name=option_order]','list' : [{'name':'','width':'7','type':'','enter':0},{'name':'option_name','width':'26','type':'text','enter':0},{'name':'option_info','width':'60','type':'tag','enter':1},{'name':'','width':'7','type':'icon','enter':0}]});	

$(function() {

	$('form[name=goodsForm] input[name=option_use]').click(function(e){
		if($('form[name=goodsForm] input[name=option_use]:checked').val()=='1') {
			$('.optionConf').slideDown("slow", function() { $('.sn_detail').summernote('airObject'); });
			$('#qtyTr').hide();
		}
		else {
			$('.optionConf').slideUp("fast", function() { $('.sn_detail').summernote('airObject'); });
			$('#qtyTr').show();
		}
		$('.sn_detail').summernote('airObject');
	});

	$('#op_set').click(function(e){

		if($('form[name=goodsForm] input[name=option_order]').val()) {
			var op_order = $('form[name=goodsForm] input[name=option_order]').val().split(',');
			var op_cnt = op_order.length; 
		}
		else {
			alertify.alert('등록된 옵션이 없습니다. 먼저 옵션을 등록하시기 바랍니다.');
			return false;
		}
		
		var total_cnt	= 0;
		for(i = 0; i < op_cnt; i ++) {		
			tmp	= $("#optionTable").find('input[name=option_info'+op_order[i]+']').val().split(',');
			tmp_cnt	= tmp.length;
			if(total_cnt == 0)	total_cnt = tmp_cnt;
			else				total_cnt = total_cnt * tmp_cnt;
		}

		if(total_cnt > 1000) {
			alertify.alert('옵션품목은 최대 1,000개 까지만 가능 합니다.');
			return false;
		}


		$(this).find('.text').hide().html('<i class="xi-spinner-2 xi-spin xi-x colorWhite"></i>').show("fast", function(){
			optionTableCreate(op_order, op_cnt);
			var optionArrName = [];
			var optionArrInfo = [];
			
			for(i=0; i<op_cnt; i++) {		
				optionArrName[i] = $("#optionTable").find('input[name=option_name'+op_order[i]+']').val();		
				optionArrInfo[i] = $("#optionTable").find('input[name=option_info'+op_order[i]+']').val().split(',');
			}
			
			var optionValue = getCombinations(optionArrInfo); 
			$.each(optionValue, function(key, value) {
				optionBox = [];
				for(i=0; i<value.length; i++) {
					//optionBox[i] = {'name':'option_title'+(i+1),'value':optionArrName[i]};
					optionBox[i] = {'name':'option_value'+(i+1),'value':value[i]};
				}
				optionBox[i] = {'name' : 'option_used', 'value' : '1'}
				
				$("#optionListTable").multiBox('add_option', optionBox);
			});

			$("#optionListTable").multiBox('reset_option');

			if(popup==0) {
				$('.sn_detail').summernote('airObject');
			}

			$("#op_set").find('.text').html('<i class="fas fa-th-list"></i> 옵션품목 만들기');
		});
		
	});

	$('#op_reset').click(function(e){
		$("#optionListTable").multiBox('resetCount');
		$('#optionDiv').html('');	
		if(popup==0) {
			$('.sn_detail').summernote('airObject');
		}
	});

	$('form[name=goodsForm] input[name=option_use]:checked').trigger('click');

});