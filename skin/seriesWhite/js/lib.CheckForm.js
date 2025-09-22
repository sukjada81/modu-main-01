var regExpDomain	= /^(http(s?)\:\/\/)?(([a-zA-Z0-9-]{1,63}[.])|([a-zA-Z0-9-]{1,63}[.])([a-zA-Z0-9-]{1,63}[.])|([a-zA-Z0-9-]{1,63}[.])([a-zA-Z0-9-]{1,63}[.])([a-zA-Z0-9-]{1,63}[.]))(museum|travel|aero|arpa|asia|edu|gov|mil|mobi|coop|info|name|biz|cat|com|int|jobs|net|org|pro|tel|a[cdefgilmnoqrstuwxz]|b[abdefghijlmnorstvwyz]|c[acdfghiklmnoruvxyz]|d[ejkmoz]|e[ceghrstu]|f[ijkmor]|g[abdefghilmnpqrstuwy]|h[kmnrtu]|i[delmnoqrst]|j[emop]|k[eghimnprwyz]|l[abcikrstuvy]|m[acdefghklmnopqrstuvwxyz]|n[acefgilopruz]|om|p[aefghklmnrstwy]|qa|r[eosuw]|s[abcdeghijklmnortuvyz]|t[cdfghjklmnoprtvwz]|u[agkmsyz]|v[aceginu]|w[fs]|y[etu]|z[amw])(\/\S+|\/|)$/gi;
var regExpEmail		= /^[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*\.[a-zA-Z]{2,3}$/i;
var regExpDate		= /^(19|20)\d{2}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[0-1])$/; 
var regExpId		= /^[a-z][a-z0-9]{5,11}$/g;
var regExpPasswd1	= /^[a-zA-Z0-9\{\}\[\]\/?.,;:|\)*~`!^\-_+<>@\#$%&\\\=\(\'\"]{8,20}$/g;
var regExpPasswd2	= /^(?=.*[a-zA-Z]).{8,20}$/;
var regExpPasswd3	= /^(?=.*[0-9]).{8,20}$/;
var regExpPasswd4	= /^(?=.*[\{\}\[\]\/?.,;:|\)*~`!^\-_+<>@\#$%&\\\=\(\'\"]).{8,20}$/;
var regExpFileName	= /[^(가-힣ㄱ-ㅎㅏ-ㅣa-zA-Z0-9_.\-)]/g;
var listKeyCheck	= 0;
var tmpCheckId		= '';

$(function() {

	$("form").attr('autocomplete','off');
	
	$("input:text, input:password").each(function(i){
		$(this).attr('autocomplete','off');
	});
	
	$("button:submit, input:submit, input:image").click(function(e) {
		e.preventDefault();
		e.stopPropagation();

		var f  = this.form;
		
		return formSubmitCkeck(f);
		
	});

	$("input:text, input:password, input:radio, input:checkbox").blur(function(e) {

		$('.inputMessageBox').fadeOut('200').remove();

		listKeyCheck = 0;
		
		$t = jQuery(this);

		if($t.attr('data-check-mileage') == 1) {
			if(!jQuery.trim($t.val())) $t.val('0');
			mileageCheck();
			return;
		}

		if(($t.attr('data-check-email') == 1 || $t.attr('data-check-domain') == 1 || $t.attr('data-check-id') == 1 || $t.attr('data-check-name') == 1 || $t.attr('data-check-vid') == 1  || $t.attr('data-check-password') > 0 || $t.attr('data-check-file-name') == 1 || $t.attr('data-check-tel') == 1 || $t.attr('data-check-cell') == 1 || $t.attr('data-check-birth') == 1 || $t.attr('data-check-comp_num') == 1) && jQuery.trim($t.val())) {
			
			ckVar = 0;
			if($t.attr('data-check-email') == 1 && $t.val().match(regExpEmail) == null) {
				displayMessage($t, '이메일주소가 유효하지 않습니다.');
				return;
			}

			if($t.attr('data-check-domain') == 1) {
				if($t.val().indexOf('http')== -1) $t.val('http://' + $t.val());
				if($t.val().match(regExpDomain) == null){
					displayMessage($t, '도메인주소가 유효하지 않습니다.');
					return;
				}
			}

			if($t.attr('data-check-name') == 1) {				
				if($t.val().match(regExpFileName) != null){				
					displayMessage($t, '특수문자 \'_.-\'만 가능 합니다.');
					return;
				}
			}

			if($t.attr('data-check-file-name') == 1) {				
				if($t.val().match(regExpFileName) != null){				
					displayMessage($t, '파일명은 특수문자 \'_.-\'만 가능 합니다.');
					return;
				}
			}

			if($t.attr('data-check-tel') == 1) {			
				if(jQuery.trim($t.val()).length < 9){
					displayMessage($t, '올바른 전화번호가 아닙니다.');
					return;
				}
			}

			if($t.attr('data-check-cell') == 1) {
				if(jQuery.trim($t.val()).length < 10){
					displayMessage($t, '올바른 휴대폰번호가 아닙니다.');
					return;
				}
			}

			if($t.attr('data-check-birth') == 1) {
				if(jQuery.trim($t.val()).length != 8){
					displayMessage($t, '생년월일을 정확히 입력 하세요. ex)19990101');
					return;
				}
			}

			if($t.attr('data-check-comp_num') == 1) {
				if(jQuery.trim($t.val()).length != 10){
					displayMessage($t, '사업자등록번호는 10자리 숫자입니다.');
					return;
				}

				var sum = 0;				
				var at	= 0;
				var att	= 0;
				var sno	= jQuery.trim($t.val());
				sum = (sno.charAt(0) * 1) + (sno.charAt(1)*3) + (sno.charAt(2) * 7) + (sno.charAt(3) * 1) + (sno.charAt(4) * 3) + (sno.charAt(5) * 7) + (sno.charAt(6) * 1) + (sno.charAt(7) * 3) + (sno.charAt(8) * 5);
				sum += parseInt((sno.charAt(8) * 5) / 10);
				at = sum % 10;
				if(at != 0) att = 10 - at;  

				if(sno.charAt(9) != att) {
					displayMessage($t, '올바른 사업자등록번호가 아닙니다.');
					return;
				}
			}

			if($t.attr('data-check-id') == 1) {
				if(tmpCheckId == $t.val()) return;

				if($t.val().match(regExpId)==null) {
					displayMessage($t, '아이디는 영문시작, 영소문자, 숫자 조합 6~12자만 가능합니다.');
					return;
				}
				else {	
					tmpObj = $t;
					$.getJSON( "php/id_check_json.php?id=" + $t.val(), 
						function(data) { 						
							$.each(data, function(key, value) {
								if(data[key].status == 1) {
									alertify.error(data[key].error);
									tmpObj.val('').trigger('focus');
									return;
								}
								else {
									tmpCheckId = $t.val();
									alertify.success("아이디를 사용 하실 수 있습니다.");
								}
							});													
						}						
					)
					.fail(function() {
						alertify.error("아이디 중복체크 에러");
					});
				}				
			}

			if($t.attr('data-check-vid') == 1) {
				if(tmpCheckId == $t.val()) return;
				if($t.attr('readonly')) return;

				if($t.val().match(regExpId)==null) {
					displayMessage($t, '아이디는 영문시작, 영소문자, 숫자 조합 6~12자만 가능합니다.');
					return;
				}
				else {	
					tmpObj = $t;
					$.getJSON( "php/vendor_id_check_json.php?id=" + $t.val(), 
						function(data) { 						
							$.each(data, function(key, value) {
								if(data[key].status == 1) {
									alertify.error(data[key].error);
									tmpObj.val('').trigger('focus');
									return;
								}
								else {
									tmpCheckId = $t.val();
									alertify.success("아이디를 사용 하실 수 있습니다.");
								}
							});													
						}						
					)
					.fail(function() {
						alertify.error("아이디 중복체크 에러");
					});
				}				
			}

			if($t.attr('data-check-password') == 1) {
				if($t.val().match(regExpPasswd1)==null) {
					displayMessage($t, '비밀번호는 영문, 숫자, 특수문자 2가지 이상 조합 8~20자만 가능합니다.');
					return;
				}
				else { 
					var ck_cnt = 3;
					if($t.val().match(regExpPasswd2)==null) ck_cnt--;
					if($t.val().match(regExpPasswd3)==null) ck_cnt--;
					if($t.val().match(regExpPasswd4)==null) ck_cnt--;

					if(ck_cnt<2) {
						displayMessage($t, '비밀번호는 영문, 숫자, 특수문자 2가지 이상 조합으로 하셔야 됩니다.');
						return;
					}
				}
			}
			else if($t.attr('data-check-password') == 2) {
				ck_field = $t.attr('data-check-field');
				if($t.val() != eval(ck_field + ".value") ) {
					displayMessage($t, '비밀번호가 일치하지 않습니다.');
					return;
				}
			}			
		}

	});


	$('.inputBox').find("label").hover(function(e) {		
		a = $(this).children("input").attr('type');
		if(a=='checkbox') $(this).children("span").addClass("chover");		
		else $(this).children("span").addClass("rhover");		
		
	}, function() {
		a = $(this).children("input").attr('type');
		if(a=='checkbox') $(this).children('span').removeClass("chover")
		else $(this).children('span').removeClass("rhover")		
	});

	
	$('.inputBox').find("input:text, input:password").each(function(i) {
		
		$t = jQuery(this);
		if($t.attr("data-title")) {
			if($t.attr("data-detail"))	tmp_detail	= '<span>' + $t.attr("data-detail") + '</span>';
			else						tmp_detail	= '';
			if($t.prop("required") && !$t.attr("data-icon-no"))		req_icon	= '&nbsp;<i class="xi-check-circle"></i>';
			else													req_icon	= '';
			$t.parent().append('<p class="inputTitle">' + $t.attr("data-title") + req_icon + tmp_detail + '</p>');		
			if($t.val().length) {
				$t.parent().find('.inputTitle').animate({top:'5px'}, 'fast');
			}
		}
		
		if($t.attr("data-zip-search")) {
			$t.parent().append('<p class="searchIcon"><i class="xi-search" title="주소검색"></i></p>');
		}

	});	

	$('.listTopSelect, .cs_faq_search').find("input:text").each(function(i) {
		$t = jQuery(this);
		if($t.attr("data-search-btn")) {
			$t.parent().append('<p class="searchIconList searchIconBtn"><i class="xi-search" title="검색"></i></p>');
		}
	});

	$('.content_list').find("input:text").each(function(i) {
		$t = jQuery(this);
		if($t.attr("data-search-btn")) {
			$t.parent().append('<p class="searchIconList searchIconBtnList"><i class="xi-search" title="검색"></i></p>');
		}
	});

	$('.inputBox').find("input:text, input:password").focus(function(e) {		
		$t = jQuery(this);		
		if($t.attr("data-title")) {
			if(jQuery.trim($t.val()).length == 0 && parseInt($t.parent().find('.inputTitle').css('top')) == '20') {
				$t.parent().find('.inputTitle').animate({top:'5px'}, 'fast');
			}
		}

	});	

	$('.inputBox').find("input:text, input:password").blur(function(e) {
		
		$t = jQuery(this);		
		if($t.attr("data-title")) {
			if(jQuery.trim($t.val()).length == 0 && parseInt($t.parent().find('.inputTitle').css('top')) == '5' ) {
				$t.parent().find('.inputTitle').animate({top:'20px'}, 'fast');
			}
		}

	});	

	$('.inputTitle').click(function(e){
		$(this).parent().children('input').trigger('focus');		
	});



	$('.searchIconBtn').click(function(e){
		i = $(this).parent().children('input');
		if(jQuery.trim(i.val()).length == 0) {
			displayMessage($(i));
			return false;
		}
		
		if(i.attr("forms")) f = eval("document." + i.attr("forms"));
		else f = i.form;
		
		$.cookie("mallListPage", 1);
		if(typeof(sendSearch) != 'function') $(i).parents('form').submit();	
		else sendSearch();

	});		

	$('.searchIconBtnList').click(function(e){
		i = $(this).parent().children('input');
		if(jQuery.trim(i.val()).length == 0) {
			displayMessage($(i));
			return false;
		}
		
		if(i.attr("forms")) f = eval("document." + i.attr("forms"));
		else f = i.form;

		$(i).parents('form').submit();

	});		

	$('.dateIcon').click(function(e){
		i = $(this).parent().children('input');
		$(i).trigger('focus');
	});		

	$('.only_num').keypress(function(event) {
		if(event.which && (event.which < 48 || event.which > 57) ) {
            event.preventDefault();
        }
    }).keyup(function(){
        if( $(this).val() != null && $(this).val() != '' ) {
          $(this).val( $(this).val().replace(/[^0-9]/g, '') );
        }
    });

	$('.only_num2').keypress(function(event) {
		if(event.which && (event.which < 48 || event.which > 57) ) {
            event.preventDefault();
        }
    });

	$('.only_num_jum').keypress(function(event) {
		if(event.which && (event.which < 46 || event.which > 57) ) {
            event.preventDefault();
        }		
    }).keyup(function(){
        if( $(this).val() != null && $(this).val() != '' ) {
            $(this).val( $(this).val().replace(/[^0-9.]/g, '') );
        }
    });

	$('.only_num_jum').blur(function(event) {
		if(!$(this).val()) $(this).val('0');
		$(this).val( parseFloat($(this).val()).toFixed(2) );
	});

	$('.only_num_format').keyup(function(event){		
		var position = $(this).caret();
		var length	 = $(this).val().length;
		if(jQuery.trim($(this).val()) != null && jQuery.trim($(this).val()) != '' ) {
	        $(this).val($(this).val().replace(/[^0-9]/g, '').replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,'));
			if(length!=$(this).val().length) $(this).caret(position+1);
			else $(this).caret(position);
		}		
	});

	addKeydownEvent();	
});

function only_num_formatCk() {
	$('.only_num_format').each(function(){
		$(this).val($(this).val().replace(/[^0-9]/g, '').replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,'));
	});
}

function addKeydownEvent(obj) {
/*
	$("input:text, input:password, textarea").unbind('blur').bind('blur', function() {
		listKeyCheck = 0;
	});
*/		
	$("input:text, input:password").unbind('keydown').bind('keydown',function(e) {		
		listKeyCheck = 1;
		
		var $m		= jQuery(this);
		var mVal	= jQuery.trim($m.val());

		if (e.keyCode == 13) {

			if($m.hasClass("tag-input")) {
				if(mVal) return true;
			}

			e.preventDefault();
			e.stopPropagation();
			
			var f  = this.form;
			if(!f) f = eval("document." + $m.attr("forms"));

			// ===== 여기 변경: Enter 시 카카오 주소검색만 호출 =====
			if($m.attr("data-zip-search") == 1 && mVal) {
				if (typeof(element_wrap) != 'undefined') execDaumPostcode2('', mVal);
				else execDaumPostcode('', mVal);
				return true;
			}

			if($m.attr("data-zip-search") == 2 && mVal) {
				if (typeof(element_wrap) != 'undefined') execDaumPostcode2($m.attr("data-zip-name") || '', mVal);
				else execDaumPostcode($m.attr("data-zip-name") || '', mVal);
				return true;
			}
			// ================================================

			if($m.attr("data-no-enter") == 1 && mVal) return true;		
			
			if(mVal.length) {	
				if($m.hasClass("data-ck-enter")) {					
					$m.blur();
					return true;
				}		
				
				if($m.attr("data-on-enter") == 1) {
					$m.trigger('blur');
					return formSubmitCkeck(f);
				}
			}
			else {			
				if($m.prop("required")) {
					displayMessage($m);
					return false;
				}				
			}

			if($m.attr("data-nextFocus") == 1) {				
				$m.blur();				
			}
			else {
				var nextEl = findNextTabStop(this);			
				if($(nextEl).hasClass("taginput")) $('#' + $(nextEl).attr("name") + '_tag').trigger('focus');
				else nextEl.focus();	
			}
		};
		
	});	 	
}


function findNextTabStop(el) {
    var universe = document.querySelectorAll('input, textarea');
    var list = Array.prototype.filter.call(universe, function(item) {return item.tabIndex >= "0"});
    var index = list.indexOf(el);	
	
	for(i=1; i<200; i++) {		
		if($(list[index + i]).attr("name")) {
			if($(list[index + i]).attr("type")=='file' || $(list[index + i]).attr("type")=='checkbox' || $(list[index + i]).attr("readonly")=="readonly" || $(list[index + i]).attr("type")=='radio' || $(list[index + i]).attr("type")=='hidden') continue;
			else break;
		}
		else continue;
	}	
	
	return list[index + i] || list[0];
}


function formSubmitCkeck(f) {

	var $f = jQuery(f);
	var $t;
	var result = true;

	if($('.inputMessageBox').length) return;
	
	$f.find("input, select, radio, checkbox, textarea, file").each(function(i) {
		$t = jQuery(this);

		if(typeof($t.attr("data-notefor")) != 'undefined') {
			if($($t.attr("data-notefor")).summernote('code') != '<p><br></p>') {
				$t.val($($t.attr("data-notefor")).summernote('code'));
			}
		}	
		
		if($t.prop("required")) {

			if($t.prop("name") == "content"){				
				if(!jQuery.trim($t.val())) {					
					$($t.attr("data-notefor")).summernote({focus: true});
					alertify.alert("내용을 입력 하시기 바랍니다.");
					result = false;
					return false;					
				}
			}

			if($t.prop("type") == 'radio' || $t.prop("type") == 'checkbox') {
				if(!$f.find('input[name=' + $t.attr('name') + ']:' + $t.prop("type") + ':checked').val()) {
					if(typeof($t.attr("data-msg-done")) != 'undefined') {
						alertify.alert($t.attr("data-msg-done"));
					}
					else displayMessage($t, $t.attr("data-msg") + ' 선택해주세요.');

					result = false;
					return false;
				}
			}
			else if($t.prop("type") == 'file') {
				if(!jQuery.trim($t.val()).length) {
					if(typeof($t.attr("data-msg-done")) != 'undefined') {
						alertify.alert($t.attr("data-msg-done"));
					}
					else displayMessage($t, $t.attr("data-msg") + ' 선택해주세요.');

					result = false;
					return false;
				}
			}
			else {

				if(!jQuery.trim($t.val()).length) {
					
					if($t.prop("type") == 'hidden') {

						if(typeof($t.attr("data-fname")) != 'undefined') {
							var position = $($t.attr("data-fname")).position();
							var contentPosition = position.top + 160;
							$("html, body").animate({ scrollTop: contentPosition }, 100);
						}

						if(typeof($t.attr("data-notefor"))!='undefined') {
							$($t.attr("data-notefor")).summernote({focus: true});
						}

						alertify.alert($t.attr("data-msg"));
						
						result = false;
						return false;	
						
					}
					else if($t.prop("type").indexOf('select') != -1) {

						alertify.alert($t.attr("data-msg") + '선택 해 주세요');

						result = false;
						return false;	
					}				
										
					displayMessage($t);					

					result = false;
					return false;

				}
			}
		}
		
		if($t.hasClass('only_num_format')) $t.val(str_replace(",","",$t.val()));		
	});


	if(!result)
		return false;

	$f.find("input").each(function(i) {
		$t = jQuery(this);

		if(!$t.attr("name")) $t.attr("name", $t.attr("data-toname"));		
	});

	if($f.attr("data-return")) {
		eval($f.attr("data-return") + "()");
		return;
	}

	if($f.attr("target") == 'HFrm') {		
		if($(".btnShineBox .displayInline .shine").length) {
			if($(".btnShineBox .displayInline .shine").hasClass('shineSend')) return;
			$(".btnShineBox .displayInline .shine").addClass('shineSend'); 
		}

		if($(".shineButtonBlack.orderPost").length) {
			if($(".shineButtonBlack.orderPost").hasClass('shineSend2')) return;
			$(".shineButtonBlack.orderPost").addClass('shineSend2'); 
		}

		if($(".btnOrderHide").length) {
			if($(".btnOrderHide").hasClass('shineSend2')) return;
			$(".btnOrderHide").addClass('shineSend2'); 
		}
	}
	else {
		$.cookie("mallListPage", 1);
		if($f.attr("name") == 'listSearchForm') {
			sendSearch();
			return;
		}
	}	
	
	if(!$(f).attr('action').length) {		
		if($(f).attr('data-submit-proc').length) eval($(f).attr('data-submit-proc') + "()");
		return;
	}
	
	f.submit();	
	return false;
}

function displayMessage($m, t) {

	if(!t) t = $m.attr("data-msg") + ' 입력해주세요.';
	
	var self = $m;
	
	$('.inputMessageBox').fadeOut('200').remove();
	$m.parent().append('<div class="inputMessageBox" style="position:absolute"><p class="inputMessage"><i class="xi-pen size07"></i>&nbsp;'+ t + '</p><p class="inputMessage_arrow"></p></div>');
	$('.inputMessageBox').css({opacity:"0.1"}).animate({top:'-=20px', opacity:1.0}, 200,(function(){ }));
	$m.trigger('focus'); 
	
}

var zipSearchObj	= null;
var zipSearchObj2	= null;

function zipSearch(name) {
	if(!name) name = '';

	// ===== 여기 변경: 서버 요청 제거, 카카오만 사용 =====
	var zipSearch = $("input[name='" + name + "postcode_search']").autocomplete({
		source: function( request, response ) {
			// 바로 카카오 주소검색 호출
			if(typeof(element_wrap) != 'undefined') execDaumPostcode2(name, this._value());
			else execDaumPostcode(name, this._value());
			// 자동완성 리스트는 사용하지 않음
			response([]);
		},
		focus: function() {
			return false;
		},
		select: function(event, ui) {
			return false;
		},
		minLength: 2,
		showKey: 1
	}).autocomplete( "instance" );
	// ================================================

	if(name)	zipSearchObj2	= zipSearch;
	else		zipSearchObj	= zipSearch;

	$(function() { 
		zipSearch.element.parent().children('.searchIcon').off('click').on('click', function(e){
			if(jQuery.trim(zipSearch._value()).length < 2) {
				zipSearch.element.focus();
				displayMessage(zipSearch.element, "두글자 이상 입력 하시기 바랍니다.");
				return false;
			}
			// 아이콘 클릭도 카카오만 실행
			if(typeof(element_wrap) != 'undefined') execDaumPostcode2(name, zipSearch._value());
			else execDaumPostcode(name, zipSearch._value());
		});		
	});		
}

var area_arr = {'강원':'강원도','경기':'경기도','경남':'경상남도','경북':'경상북도','광주':'광주광역시','대구':'대구광역시','대전':'대전광역시','부산':'부산광역시','서울':'서울특별시','세종':'세종특별자치시','울산':'울산광역시','인천':'인천광역시','전남':'전라남도','전북':'전라북도','제주':'제주특별자치도','충남':'충청남도','충북':'충청북도'};


function execDaumPostcode2(name, keyword) {
	// 현재 scroll 위치를 저장해놓는다.
	var currentScroll = Math.max(document.body.scrollTop, document.documentElement.scrollTop);
	new daum.Postcode({
		oncomplete: function(data) {
			tmps	= data.roadAddress.split(' ');
			if(tmps[0]) {
				tmps[0] = area_arr[tmps[0]];
			}
			if(tmps[0]) address = tmps.join(' ');
			else address = data.roadAddress;
			
			$("input[name='" + name + "postcode']").trigger('focus').val(data.zonecode);
			$("input[name='" + name + "postcode']").parent().find('.inputTitle').css("top", "5px");
			$("input[name='" + name + "address1']").trigger('focus').val(address);
			$("input[name='" + name + "address1']").parent().find('.inputTitle').css("top", "5px");
			$("input[name='" + name + "address2']").trigger('focus');
			$("input[name='" + name + "postcode_search']").val('').trigger('blur');

			if(typeof(addressCheck) != 'undefined') addressCheck();
			// iframe을 넣은 element를 안보이게 한다.
			// (autoClose:false 기능을 이용한다면, 아래 코드를 제거해야 화면에서 사라지지 않는다.)
			element_wrap.style.display = 'none';

			// 우편번호 찾기 화면이 보이기 이전으로 scroll 위치를 되돌린다.
			document.body.scrollTop = currentScroll;
		},
		// 우편번호 찾기 화면 크기가 조정되었을때 실행할 코드를 작성하는 부분. iframe을 넣은 element의 높이값을 조정한다.
		onresize : function(size) {
			element_wrap.style.height = size.height+'px';
		},
		width : '100%',
		height : '100%'
	}).embed(element_wrap);

	// iframe을 넣은 element를 보이게 한다.
	element_wrap.style.display = 'block';
}

// 다음 우편번호 호출
function execDaumPostcode(name, keyword) {
	var width = 500; //팝업의 너비
	var height = 600; //팝업의 높이
	
	new daum.Postcode({			
		width: width, 
	    height: height,
		oncomplete: function(data) {
			
			tmps	= data.roadAddress.split(' ');
			if(tmps[0]) {
				tmps[0] = area_arr[tmps[0]];
			}
			if(tmps[0]) address = tmps.join(' ');
			else address = data.roadAddress;
			
			$("input[name='" + name + "postcode']").trigger('focus').val(data.zonecode);
			$("input[name='" + name + "postcode']").parent().find('.inputTitle').css("top", "5px");
			$("input[name='" + name + "address1']").trigger('focus').val(address);
			$("input[name='" + name + "address1']").parent().find('.inputTitle').css("top", "5px");
			$("input[name='" + name + "address2']").trigger('focus');
			$("input[name='" + name + "postcode_search']").val('').trigger('blur');

			if(typeof(addressCheck) != 'undefined') addressCheck();
		}
	}).open({
		q: keyword, 
		left: (window.screen.width / 2) - (width / 2), 
		top: (window.screen.height / 2) - (height / 2)
	});
}

// Set caret position easily in jQuery
// Written by and Copyright of Luke Morton, 2011
// Licensed under MIT
(function ($) {
    // Behind the scenes method deals with browser
    // idiosyncrasies and such
    $.caretTo = function (el, index) {
        if (el.createTextRange) { 
            var range = el.createTextRange(); 
            range.move("character", index); 
            range.select(); 
        } else if (el.selectionStart != null) { 
            el.focus(); 
            el.setSelectionRange(index, index); 
        }
    };
    
    // Another behind the scenes that collects the
    // current caret position for an element
    
    // TODO: Get working with Opera
    $.caretPos = function (el) {
        if ("selection" in document) {
            var range = el.createTextRange();
            try {
                range.setEndPoint("EndToStart", document.selection.createRange());
            } catch (e) {
                // Catch IE failure here, return 0 like
                // other browsers
                return 0;
            }
            return range.text.length;
        } else if (el.selectionStart != null) {
            return el.selectionStart;
        }
    };

    // The following methods are queued under fx for more
    // flexibility when combining with $.fn.delay() and
    // jQuery effects.

    // Set caret to a particular index
    $.fn.caret = function (index, offset) {
        if (typeof(index) === "undefined") {
            return $.caretPos(this.get(0));
        }
        
        return this.queue(function (next) {
            if (isNaN(index)) {
                var i = $(this).val().indexOf(index);
                
                if (offset === true) {
                    i += index.length;
                } else if (typeof(offset) !== "undefined") {
                    i += offset;
                }
                
                $.caretTo(this, i);
            } else {
                $.caretTo(this, index);
            }
            
            next();
        });
    };

    // Set caret to beginning of an element
    $.fn.caretToStart = function () {
        return this.caret(0);
    };

    // Set caret to the end of an element
    $.fn.caretToEnd = function () {
        return this.queue(function (next) {
            $.caretTo(this, $(this).val().length);
            next();
        });
    };
}(jQuery));
