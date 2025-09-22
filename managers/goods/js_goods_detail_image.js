function detailImageInsert(img, img_name) {
	if(!img) return;
	
	var orig_detailImageCnt = detailImageCnt;
	var insertCode = '<li data-idx="'+img_name+'">';
	var img_name2 = img_name.split(".");
	insertCode += '		<div class="detailImg">';
	insertCode += '			<img id="'+img_name2[0]+'" src='+img+' alt="detailImage'+detailImageCnt+'" />';
	insertCode += '			<div class="detailBtn">';
	insertCode += '				<p id="detailImageIcon2_'+detailImageCnt+'" class="btnDetailUse" src='+img+' name='+img_name+'>노출하기</p>';
	insertCode += '				<p id="detailImageIcon3_'+detailImageCnt+'" class="detailUsed">노출중</p>';
	insertCode += '			</div>';
	insertCode += '			<div class="btnBoxIcon">';
	insertCode += '				<span id="detailImageIcon_'+detailImageCnt+'"><i class="xi-search xi-x masterTooltip" title="확대"></i>&nbsp;<i class="xi-close masterTooltip" title="삭제하기"></i></span>';
	insertCode += '			</div>';
	insertCode += '		</div>';
	insertCode += '	  </li>';

	$('#detailImageSortable').append(insertCode);
	
	$('#detailImageIcon2_'+orig_detailImageCnt).click(function(e){
		if($('.sn_explains').length) $('.sn_explains').summernote('insertImage', $(this).attr('src'), $(this).attr('name'));
		else $(default_editer).summernote('insertImage', $(this).attr('src'), $(this).attr('name'));
		$('#detailImageIcon2_'+orig_detailImageCnt).hide();
		$('#detailImageIcon3_'+orig_detailImageCnt).show();			
	});

	$('#detailImageIcon_'+orig_detailImageCnt).find('.xi-x').click(function(e){
		imageView(img, img_name);
	});

	$('#detailImageIcon_'+orig_detailImageCnt).find('.xi-close').click(function(e){
		imageDelete($(this).closest('li'),'detail');
	});

	if($("#contentMenuDetailSub2").hasClass('selected')) {
		$(".detailBtn").show();
	}

	detailImageCnt++;		
	resetTooltip();
	resetContent();

	$('#detailImageSortable').sortable({
		placeholder: "highlight",
		cursor : "move",
		update : function(e, ui) {
			$('form[name=goodsForm] input[name=detail_image_order]').val($(this).sortable("toArray", { attribute : 'data-idx' }));
		}
	});
	
	$('form[name=goodsForm] input[name=detail_image_order]').val($('#detailImageSortable').sortable("toArray", { attribute : 'data-idx' }));
	
	$("#detailImageSortable").disableSelection();
	$("#detailImageEmpty").hide();

	if($('.sn_detail').length) $('.sn_detail').summernote('airObject');
}

function imageDelete($t, type) {		

	var mode2 = $('form[name=goodsForm] input[name=mode]').val();

	if(mode2 == 'modify') {
		$t.remove();
		resetContent();
		if(type == 'other') {
			$('form[name=goodsForm] input[name=other_image_order]').val($('#otherImageSortable').sortable("toArray", { attribute : 'data-idx' }));							
			other_image_cnt --;									
			if(other_image_cnt == 0) $("#otherImageEmpty").show();
		}
		else {
			$('form[name=goodsForm] input[name=detail_image_order]').val($('#detailImageSortable').sortable("toArray", { attribute : 'data-idx' }));	
			if(!$('form[name=goodsForm] input[name=detail_image_order]').val()) $("#detailImageEmpty").show();
			$('.sn_detail').summernote('airObject');
		}
		return;
	}

	var data = new FormData();

	data.append('temp_upload',temp_upload);
	data.append('mode2', mode2);

	$.ajax({
		url: '../../managers/goods/goods_image_post_json.php?mode=delete&name='+$t.attr('data-idx'), 
		type: 'POST',	
		data: data, 
		cache: false,
		dataType: 'json',
		processData: false, 
		contentType: false, 
		success: function(data, textStatus, jqXHR) {
			if(typeof(data.error) === 'undefined') {														
				alertify.success($t.attr('data-idx') + ' 이미지가 삭제 되었습니다');															
				$t.remove();
				resetContent();
				if(type == 'other') {
					$('form[name=goodsForm] input[name=other_image_order]').val($('#otherImageSortable').sortable("toArray", { attribute : 'data-idx' }));				
					other_image_cnt --;
					if(other_image_cnt == 0) $("#otherImageEmpty").show();
				}
				else {
					$('form[name=goodsForm] input[name=detail_image_order]').val($('#detailImageSortable').sortable("toArray", { attribute : 'data-idx' }));	
					if(!$('form[name=goodsForm] input[name=detail_image_order]').val()) $("#detailImageEmpty").show();
					$('.sn_detail').summernote('airObject');
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

function imageStatus() {
	var $t; 
	var name;
	if($('.sn_explains').length) var code = $('.sn_explains').summernote('code');
	else var code = $(default_editer).summernote('code');

	$('#detailImageSortable').find("li").each(function(i) {
		$t = jQuery(this);
		name = $t.attr("data-idx");

		if(code.indexOf(name)!=-1) {
			$t.find(".detailImg .detailBtn .detailUsed").show();
			$t.find(".detailImg .detailBtn .btnDetailUse").hide();			
		}
		else {
			$t.find(".detailImg .detailBtn .detailUsed").hide();
			$t.find(".detailImg .detailBtn .btnDetailUse").show();			
		}
	});	
}	

$(function() {
	
	$('#other_image, #detail_image').on('change', function(e){			
		var files = e.target.files;
		var data = new FormData();
		
		var cnt = 0;
		$.each(files, function(key, value) {
			var ext = value.name.split('.').pop().toLowerCase();	
			if($.inArray(ext, ["jpg", "jpeg", "gif", "png"]) == -1) {
				alertify.error("[" + value.name + "] 이미지 파일이 아닙니다.");				
			}
			else {
				data.append(key, value);
				cnt++;
			}
		});

		if(cnt == 0) alertify.error("등록 가능 한 이미지 파일이 없습니다.");	

		type = str_replace("_image", "", this.id);		

		if(type=='other') {
			if(other_image_cnt + cnt > 10) {
				alertify.alert('추가 이미지는 10개까지 등록이 가능 합니다.');
				return;
			}
		}

		data.append('temp_upload', temp_upload);
		data.append('type', type);
		data.append('mode2', $('form[name=goodsForm] input[name=mode]').val());

		$.ajax({
			url: '../../managers/goods/goods_image_post_json.php?files', 
			type: 'POST',
			data: data, 
			cache: false,
			dataType: 'json',
			processData: false, 
			contentType: false, 
			success: function(data, textStatus, jqXHR) {
				if(typeof(data.error) === 'undefined') {				
				
					$.each(data, function(key, value) {
						if(type=='other') {
							otherImageInsert(data[key].img, data[key].img_name);								
						}
						else {
							detailImageInsert(data[key].img, data[key].img_name);			
						}
						alertify.success(data[key].img_name + ' 이미지가 등록 되었습니다');
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

		$(this).val('');
		
	});

	if($("#detailImageSortable").length) {
		
		if($("#otherImageSortable".length)) var dropZone = $("#detailImageSortable, #otherImageSortable");
		else var dropZone = $("#detailImageSortable");

		dropZone.on('dragenter',function(e){
			e.stopPropagation();
			e.preventDefault();            
			$(this).css('background-color','#e3f2fc');

		});
		dropZone.on('dragleave',function(e){
			e.stopPropagation();
			e.preventDefault();

			var type = str_replace("ImageSortable", "", this.id);
		
			if(type == 'other') var type_color = "#fff";
			else				var type_color = "#efefef";

			$(this).css('background-color', type_color);
		});
		dropZone.on('dragover',function(e){
			e.stopPropagation();
			e.preventDefault();			
			$(this).css('background-color','#e3f2fc');
		});
		dropZone.on('drop',function(e){
			e.preventDefault();
			var type = str_replace("ImageSortable", "", this.id);

			if(type == 'other') var type_color = "#fff";
			else				var type_color = "#efefef";

			$(this).css('background-color', type_color);
			
			var files = e.originalEvent.dataTransfer.files;
			if(files != null){
				if(files.length < 1) {
					alertify.error('업로드 가능 한 이미지 파일이 없습니다.');
					return;
				}
								
				var data	= new FormData();				
				var cnt		= 0;				
				
				$.each(files, function(key, value) {
					var ext = value.name.split('.').pop().toLowerCase();	
					if($.inArray(ext, ["jpg", "jpeg", "gif", "png"]) == -1) {
						alertify.error("[" + value.name + "] 이미지 파일이 아닙니다.");				
					}
					else {
						data.append(key, value);
						cnt++;
					}
				});

				if(cnt == 0) alertify.error("등록 가능 한 이미지 파일이 없습니다.");	

				if(type=='other') {
					if(other_image_cnt + cnt > 10) {
						alertify.alert('추가 이미지는 10개까지 등록이 가능 합니다.');
						return;
					}
				}

				data.append('temp_upload', temp_upload);
				data.append('type', type);
				data.append('mode2', $('form[name=goodsForm] input[name=mode]').val());

				$.ajax({
					url: '../../managers/goods/goods_image_post_json.php?files', 
					type: 'POST',
					data: data, 
					cache: false,
					dataType: 'json',
					processData: false, 
					contentType: false, 
					success: function(data, textStatus, jqXHR) {
						if(typeof(data.error) === 'undefined') {				
						
							$.each(data, function(key, value) {
								if(type=='other') {
									otherImageInsert(data[key].img, data[key].img_name);								
								}
								else {
									detailImageInsert(data[key].img, data[key].img_name);			
								}
								alertify.success(data[key].img_name + ' 이미지가 등록 되었습니다');
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
		});
	}

});