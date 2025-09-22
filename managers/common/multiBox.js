;(function ( $, window, document, undefined ) {

    var pluginName = "multiBox",
        cnt = 1,
		addValue = [],
		defaults = {
			sortable: 1,
			autoNum: 1,			
			isSortable: false,			
			order:null,
			focus:null,
			list: []
		};
		

    function Plugin( element, options ) {        
	    /* Settings */
	    this.element = element;
        this.options = $.extend( {}, defaults, options );
        this._name = pluginName;
		this.cnt	= cnt;	
	    this.init();
		if(this.options.autoNum) this.autoNum = this.options.autoNum;
    }

	Plugin.prototype = {

		/*
		 * multiBox init
		*/
		init: function() {       
			this.instance = this.createInstance();			
		},

		/*
		*  Generate new ID for Table
		*/
		createInstance: function() {
			 return $(this.element).attr('id') || Math.round(Math.random() * 99999999);
		 },

		/* 
		*add values multiBox
		*/
		add: function(value) {			
			this.addValue = value;
			this._insert(1);	
			this.addValue = [];
		},	

		add2: function(value) {			
			$(this.element).find('#' + this.instance + (this.cnt - 1)).remove();
			this.cnt--;
			this.addValue = value;
			this._insert(1);		
			this.addValue = [];
			this._insert(1);	
		},
		
		add_option: function(value) {			
			this.addValue = value;
			this._insert_option();	
			this.addValue = [];
		},	

		reset: function() {			
			$(this.element).find('tbody').html('');
		},	

		count: function(value) {	
			op_cnt = this.cnt;
		},	

		/*
		 * insert multiBox
		*/
		_insert: function(focus) {		

			var self			= this;
			var list			= this.options.list;
			var name			= '';
			var width			= '';
			var type			= '';			
			var used			= '';
			var enter			= '';
			var ckAdd			= 0;
			var select_name		= new Array();
			var select_value	= new Array();
			var addValue		= new Array();
			
			if(!focus)	focus	= 0;
			$(this.addValue).each(function(){
				addValue[$(this).attr("name")] = $(this).attr("value");
				if(addValue[$(this).attr("name")]) ckAdd = 1;
			});			

			if(ckAdd==1) {				
				var ckTarget = 'class="target"';
				var ckEnter = "";
			}
			else {
				var ckTarget = "";
				var ckEnter = 'class="ckEnter"';
			}

			var insertCode = '	<tr data-idx="' + this.cnt + '" id="' + this.instance + this.cnt + '" ' + ckTarget + ' >';

			$(list).each(function(i){
				name	= $(this).attr("name");
				width	= $(this).attr("width");
				type	= $(this).attr("type");
				enter	= $(this).attr("enter");

				if(enter == 1) {
					enter2 = ckEnter;
					enter3 = "ckEnter";
				}
				else if(enter == 2) enter2 = ckEnter + ' data-next-focus="3"';
				else enter2 = enter3 = '';						

				if(name) {

					if(!addValue[name]) addValue[name] = '';

					if(type == 'text') {						
						insertCode += '	<td style="width:' + width + '%"><input type="text" name="' + name + self.cnt + '" value="' + addValue[name] + '" ' + enter2 + ' /></td>';
					}
					else if(type == 'text_holder') {
						var name2 = name + '_help';
						if(!addValue[name2] || addValue[name2] == 'undefined') addValue2 = "";
						else addValue2 = '<div class="info_help">- ' + str_replace("*", "<br />- ", addValue[name2]) + '</div>';
						insertCode += '	<td style="width:' + width + '%"><input type="text" name="' + name + self.cnt + '" value="' + addValue[name] + '" ' + enter2 + ' /><input type="hidden" name="' + name2 + self.cnt + '" value="' + addValue[name2] + '" />' + addValue2 + '</td>';
					}
					else if(type == 'num') {
						if(!addValue[name]) addValue[name] = "0";
						insertCode += '	<td style="width:' + width + '%"><input type="text" name="' + name + self.cnt + '" value="' + addValue[name] + '" ' + enter2 + ' class="only_num_format ' + enter3 + '" /></td>';
					}
					else if(type == 'qty') {						
						var name2 = str_replace("option_qty", "option_qty_type", name);
						var name3 = str_replace("option_qty", "option_uid", name);
						if(!addValue[name]) addValue[name] = "0";
						if(!addValue[name2]) addValue[name2] = "1";
						if(!addValue[name3]) addValue[name3] = "";
						insertCode += '	<td style="width:' + width + '%"><input type="hidden" name="' + name3 + self.cnt + '" value="' + addValue[name3] + '" /><input type="hidden" name="' + name2 + self.cnt + '" value="' + addValue[name2] + '" /><i class="xi-all xi-x colorGray masterTooltip" cnt="' + self.cnt + '" title="무제한"></i>&nbsp;&nbsp;<input type="text" name="' + name + self.cnt + '" value="' + addValue[name] + '" ' + enter2 + ' class="only_num_format" style="width:50%" /></td>';
					}
					else if(type == 'ttl') {
						var name2 = str_replace("option_value","option_title", name);
						insertCode += '	<td style="width:' + width + '%"><input type="hidden" name="' + name2 + self.cnt + '" value="' + addValue[name2] + '" /><input type="text" name="' + name + self.cnt + '" value="' + addValue[name] + '" class="writed alignCenter" /></td>';
					}
					else if(type == 'tag') {
						insertCode += '	<td style="width:' + width + '%"><input type="text" id="' + name + self.cnt + '" name="' + name + self.cnt + '" value="' + addValue[name] + '" class="taginput" /></td>';
					}
					else if(type == 'textarea') {
						insertCode += '	<td style="width:' + width + '%"><textarea name="' + name + self.cnt + '" ' + enter2 + '>' + addValue[name] + '</textarea></td>';
					}
					else if(type == 'textarea2') {
						insertCode += '	<td style="width:' + width + '%"><textarea name="' + name + self.cnt + '" placeholder="필수정보 항목[enter키]\n필수정보 항목[enter키]\n...">' + addValue[name] + '</textarea></td>';
					}
					else if(type == 'selectbox') {
						insertCode += '	<td style="width:' + width + '%; text-align:left;"><select name="' + name + self.cnt + '">';
						insertCode += '	<option value="">선택</option>';
						select_option[name].forEach(function(element, index, array){
							insertCode += '	<option value="' + element[1] + '">' + element[0] + '</option>';
						});
						insertCode += '	</select></td>';

						select_name[i]	= name + self.cnt;
						select_value[i]	=  addValue[name];
					}
					else if(type == 'hidden') {						
						if(name == 'delivery_num') {
							var tmp_val1 = 'delivery';
							var tmp_val2 = 'delivery';
							var tmp_val3 = ck_delivery_num;
						}
						else if(name == 'cate_num') {
							var tmp_val1 = 'goods';
							var tmp_val2 = 'cate';
							var tmp_val3 = ck_cate_num;
						}
							
						if(addValue[name]=='') {
							for(i=1; i < self.cnt; i++) {									
								if(tmp_val3 <= parseInt($('form[name=' + tmp_val1 + 'Form] input[name=' + tmp_val2 + '_num'+i+']').val())) {
									tmp_val3 = parseInt($('form[name=' + tmp_val1 + 'Form] input[name=' + tmp_val2 + '_num'+i+']').val()) + 1;
								}
							}
							addValue[name] = tmp_val3;
							$('form[name=' + tmp_val1 + 'Form] input[name=' + tmp_val2 + '_max_num]').val(tmp_val3);
						}

						insertCode += '	<td class="number" style="width:' + width + '%">' + addValue[name] + '<input type="hidden" name="' + name + self.cnt + '" value="' + addValue[name] + '" /></td>';

						if(name == 'delivery_num') {
							ck_delivery_num =  tmp_val3;
						}
						else if(name == 'cate_num') {
							ck_cate_num =  tmp_val3;
						}
					}
					else {
						if(addValue[name]==1) used = "checked='checked'";
						else {
							if(addValue[name].length == 1) used = '';
							else used = "checked='checked'";
						}
						insertCode += '	<td style="width:' + width + '%"><label><input type="checkbox" name="' + name + self.cnt + '" value="1" ' + used + ' /><span></span></label></td>';
					}
				}
				else {
					if(type=='icon') {
						insertCode += '	<td style="width:' + width + '%"><span id="Icon_' + self.instance + self.cnt + '" class="iconHide"><i class="xi-close masterTooltip" cnt="' + self.cnt + '" title="삭제하기"></i></span></td>';
					}
					else {
						insertCode += '	<td style="width:' + width + '%" class="number">' + self.cnt + '</td>';
					}
				}
			});
			
			insertCode += '		</tr>';

			$(this.element).find("tbody").append(insertCode);
			
			if(select_name.length > 1) {
				for(j = 1; j < select_name.length; j ++) {
					$("select[name=" + select_name[j] + "]").val(select_value[j]).heapbox({'width' : '90%', 'zindex': parseInt(100 - this.cnt) , 'onChange':function(value, obj) { 
							if(value) self._blur(obj);
						}	
					});
				}
			}

			$(this.element).find('#' + this.instance + this.cnt + ' .xi-close').click(function(e){				
				self._delete($(this).attr('cnt'));
			});

			if(this.instance == 'optionSortable') {
				$('#goods_option_info' + this.cnt).tagsInput({ 'placeholder' : '입력 하세요!', 'delimiter': [',', ';'] });
				$('#' + this.instance + this.cnt + ' #goods_option_info' + this.cnt + '_tag').blur(function(e){ 		
					self._blur('#' + str_replace("_tag", "", $(this).attr('id')));
				});			
			}
			else if(this.instance == 'optionTable') {
				$('#option_info' + this.cnt).tagsInput({ 'placeholder' : '입력 하세요!', 'delimiter': [',', ';'] });
				$('form[name=goodsForm] input[name=option_name' + this.cnt + ']').blur(function(e){ 
					self._blur(this);
				});			
			}
			else if(this.instance == 'requireSortable') {
				$('form[name=goodsForm] textarea[name=goods_require_info' + this.cnt + ']').blur(function(e){ 
					self._blur(this);
				});
			}
			else {
				$(this.element).find('#' + this.instance + this.cnt + ' .ckEnter').blur(function(e){   			
					self._blur(this);
				});					
			}

			$(this.element).find('#' + this.instance + this.cnt + ' .only_num_format').keyup(function(){
				if(jQuery.trim($(this).val()) != null && jQuery.trim($(this).val()) != '' ) {
					$(this).val($(this).val().replace(/[^0-9-]/g, '').replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,'));
				}		
			});

			if(ckAdd == 1) {
				if(this.instance == 'requireSortable') this._requireInfo(this.cnt);					
				$('#Icon_' + self.instance + self.cnt).fadeIn(200);
				if(this.options.sortable == 1) this._sortable();
			}
			else {
				if(this.options.focus && focus == 0) {				
					$(this.element).find('tbody tr td:nth-child(' + this.options.focus + ') input:text').focus();
				}
			}			
			
			this.cnt++;		
			this._resetTooltip();			
			if(this.options.sortable == 1 && this.isSortable) this._reOrder();		
			
			if(!popup) {
				if(ckAdd == 0) resetContent();
			}
			addKeydownEvent();			
		},

		/*
		 * insert multiBox
		*/
		_insert_option: function() {		

			var self			= this;
			var list			= this.options.list;
			var name			= '';
			var width			= '';
			var type			= '';			
			var used			= '';
			var enter			= '';
			var ckAdd			= 0;
			var select_name		= new Array();
			var select_value	= new Array();
			var addValue		= new Array();

			$(this.addValue).each(function(){
				addValue[$(this).attr("name")] = $(this).attr("value");				
			});			

			var ckTarget = 'class="target"';			
			
			var insertCode = '	<tr data-idx="' + this.cnt + '" id="' + this.instance + this.cnt + '" ' + ckTarget + ' >';

			$(list).each(function(i){
				name	= $(this).attr("name");
				width	= $(this).attr("width");
				type	= $(this).attr("type");
				enter	= $(this).attr("enter");

				enter2 = enter3 = '';						

				if(name) {

					if(!addValue[name]) addValue[name] = '';

					if(type == 'text') {						
						insertCode += '	<td style="width:' + width + '%"><input type="text" name="' + name + self.cnt + '" value="' + addValue[name] + '" ' + enter2 + ' /></td>';
					}
					else if(type == 'num') {
						if(!addValue[name]) addValue[name] = "0";
						insertCode += '	<td style="width:' + width + '%"><input type="text" name="' + name + self.cnt + '" value="' + addValue[name] + '" ' + enter2 + ' class="only_num_format ' + enter3 + '" style="font-size:14px;" /></td>';
					}
					else if(type == 'qty') {						
						var name2 = str_replace("option_qty", "option_qty_type", name);
						var name3 = str_replace("option_qty", "option_uid", name);
						if(!addValue[name]) addValue[name] = "0";
						if(!addValue[name2]) addValue[name2] = "1";
						if(!addValue[name3]) addValue[name3] = "";
						insertCode += '	<td style="width:' + width + '%"><input type="hidden" name="' + name3 + self.cnt + '" value="' + addValue[name3] + '" /><input type="hidden" name="' + name2 + self.cnt + '" value="' + addValue[name2] + '" /><i class="xi-all xi-x colorGray masterTooltip" cnt="' + self.cnt + '" title="무제한"></i>&nbsp;&nbsp;<input type="text" name="' + name + self.cnt + '" value="' + addValue[name] + '" ' + enter2 + ' class="only_num_format" style="font-size:14px; width:50%" /></td>';
					}
					else if(type == 'ttl') {
						var name2 = str_replace("option_value","option_title", name);
						insertCode += '	<td style="width:' + width + '%"><input type="hidden" name="' + name2 + self.cnt + '" value="' + addValue[name2] + '" /><input type="text" name="' + name + self.cnt + '" value="' + addValue[name] + '" class="writed alignCenter" /></td>';
					}
					else {
						if(addValue[name]==1) used = "checked='checked'";
						else {
							if(addValue[name].length == 1) used = '';
							else used = "checked='checked'";
						}
						insertCode += '	<td style="width:' + width + '%"><label><input type="checkbox" name="' + name + self.cnt + '" value="1" ' + used + ' /><span></span></label></td>';
					}
				}
				else {
					if(type=='icon') {
						insertCode += '	<td style="width:' + width + '%"><span id="Icon_' + self.instance + self.cnt + '"><i class="xi-close masterTooltip" cnt="' + self.cnt + '" title="삭제하기"></i></span></td>';
					}
					else {
						insertCode += '	<td style="width:' + width + '%" class="number">' + self.cnt + '</td>';
					}
				}
			});
			
			insertCode += '		</tr>';

			$(this.element).find("tbody").append(insertCode);
			
			$(this.element).find('#' + this.instance + this.cnt + ' .xi-close').click(function(e){				
				self._delete($(this).attr('cnt'));
			});

			
			$(this.element).find('#' + this.instance + this.cnt + ' .only_num_format').keyup(function(){
				if(jQuery.trim($(this).val()) != null && jQuery.trim($(this).val()) != '' ) {
					$(this).val($(this).val().replace(/[^0-9-]/g, '').replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,'));
				}		
			});

			$(this.element).find('#' + this.instance + this.cnt + ' .xi-all').click(function(e){
				var cnt2 = $(this).attr('cnt');				
				var ckLimit = $('form[name=goodsForm] input[name=option_qty_type' + cnt2 + ']');
				var ckQty	= $('form[name=goodsForm] input[name=option_qty' + cnt2 + ']');
				if($(this).hasClass('colorOrange')) {					
					ckLimit.val('0');
					ckQty.attr('readonly', false);
					ckQty.removeClass('darks');
					$(this).removeClass('colorOrange');
				}
				else {
					ckLimit.val('1');
					ckQty.attr('readonly', true);					
					ckQty.addClass('darks');
					$(this).addClass('colorOrange');					
				}
			});
			if($('form[name=goodsForm] input[name=option_qty_type' + this.cnt + ']').val() == '1') $(this.element).find('#' + this.instance + this.cnt + ' .xi-all').click();
						
			this.cnt++;					
		},

		/*
		 * delete multiBox
		*/
		_delete: function(idx) {
			$(this.element).find('#' + this.instance + idx).remove();				
			this._reOrder();
			if($(this.element).find('tbody tr td:nth-child(1)').length == 0) {
				if($('.emptyList').length) $('.emptyList').show();
				else this._insert();
			}
			if(!popup) {
				resetContent();
			}
		},

		/*
		 * blur multiBox
		*/
		_blur: function(t) {		
			var idx = $(t).parent('td').parent('tr').attr('data-idx');

			if($('#Icon_' + this.instance + idx).css('display') != 'none') {				
				if(this.instance == 'requireSortable') this._requireInfo(idx);	
				return;
			}

			var ckEnter = 1;
			
			if(this.instance == 'optionTable') {
				$(t).parent('td').parent('tr').find("input:text").each(function(i) {					
					if($(this).attr('name') && i == 0) {
						if(!jQuery.trim($(this).val())) ckEnter = 0;
					}
				});
			}
			else {
				$(t).parent('td').parent('tr').find("input:text, select").each(function(i) {					
					if($(this).attr('name')) {
						if(!jQuery.trim($(this).val())) ckEnter = 0;
					}
				});
			}
		
			if(ckEnter == 1) {
				if(this.instance == 'requireSortable') this._requireInfo(idx);	
				$(t).parent('td').parent('tr').addClass('target');
				$('#Icon_' + this.instance + idx).fadeIn(200);			
				this._sortable();
				this._insert();				
			}		
		},

		/*
		reorder multiBox
		*/
		_reOrder: function() {		
			var self = this;
			if(this.autoNum == 1) {
				$(this.element).find('tbody tr td:nth-child(1)').text(function() {
					num = 1;
					return $(this).parent().index('#' + self.instance + ' tbody tr') + num;
				});	
			}
			$(this.options.order).val($(this.element).find('tbody').sortable("toArray", { attribute : 'data-idx' }));

			if(this.instance == 'requireGSortable' || this.instance == 'optionListTable' || this.instance == 'optionTable') {				
				if($('.sn_detail').length) $('.sn_detail').summernote('airObject');
			}
		},
		
		/*
		sortable multiBox
		*/
		_sortable: function() {	
			var self = this;			
			
			$(this.element).find('.target').hover(function(e){	
				$(this).find('td').each(function(){
					$(this).css({'width':$(this).css('width')});

				});
			});

			$(this.element).find('tbody').sortable({
				placeholder: "highlight",
				items: ".target",
				cursor : "move",
				update : function(e, ui) {
					$(self.options.order).val($(this).sortable("toArray", { attribute : 'data-idx' }));
					if(self.autoNum==1) {
						$(self.element).find('tbody tr td:nth-child(1)').text(function() {
							num = 1;
							return $(this).parent().index('#' + self.instance + ' tbody tr') + num;
						});				
					}
				}
			});

			$(this.options.order).val($(this.element).find('tbody').sortable("toArray", { attribute : 'data-idx' }));

			this.isSortable = true;
		},

		/*
		resetTooltip multiBox
		*/
		_resetTooltip: function() {	
			$(this.element).find('.masterTooltip').tooltip({
				show: null,
				position: {
					my: "left-10 top",
					at: "left bottom"
				},
				open: function( event, ui ) {
					ui.tooltip.animate({ top: ui.tooltip.position().top + 10, opacity:'1'}, "fast" );
				}
			});
		},

		/*
		requireInfo multiBox
		*/
		_requireInfo: function(cnt) {	
			if(!jQuery.trim($('form[name=goodsForm] textarea[name=goods_require_info' + cnt + ']').val())) return;
		
			$('form[name=goodsForm] textarea[name=goods_require_info' + cnt + ']').addClass('writed');

			var tagify = $('form[name=goodsForm] textarea[name=goods_require_info' + cnt + ']').val().split("\n");
			var insertCode = '<div id="tagify_' + cnt + '">';
			for (i=0; i < tagify.length; i++) {
				if(jQuery.trim(tagify[i]) == '') continue;
				insertCode += '<div class="tagifyItem" num="' + cnt + '">' + tagify[i] + '</div>';
			}
			insertCode += '</div>';				

			$('form[name=goodsForm] textarea[name=goods_require_info' + cnt + ']').parent('td').append(insertCode);	
			
			$('.tagifyItem').click(function(e){					
				var cnt2 = $(this).attr('num');
				$('form[name=goodsForm] textarea[name=goods_require_info' + cnt2 + ']').removeClass('writed').focus();
				$('#tagify_' + cnt2).remove();
			});
		}

	}

	$.fn[pluginName] = function ( options, optional ) {

        return this.each(function () {	
			if (!$.data(this, "plugin_" + pluginName)) {
					$.data(this, "plugin_" + pluginName, new Plugin( this, options ));
			}
			else {				
				multiBoxInst = $.data(this, "plugin_" + pluginName);		

				switch(options) {
					case "add" :								
						multiBoxInst.add(optional);
					break;
					case "add2" :								
						multiBoxInst.add2(optional);
					break;
					case "add_option" :								
						multiBoxInst.add_option(optional);
					break;
					case "reset_option" :								
						multiBoxInst._sortable();
						multiBoxInst._resetTooltip();
						multiBoxInst._reOrder();
						resetContent();
						addKeydownEvent();	
					break;
					case "resetCount" :
						multiBoxInst.cnt = 1;						
					break;
					case "reset" :						
						multiBoxInst.reset();
						multiBoxInst.cnt = 2;	
					break;
				}
				
			}	
        });
    };

})( jQuery, window, document );