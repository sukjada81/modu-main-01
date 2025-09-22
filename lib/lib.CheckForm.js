/**
 * 쇼핑몰 폼 체크용 
 * Author: kim gyu bok - http://funcher.kr
 * 수정: 다음(카카오) 우편번호만 사용
 */

var regExpDomain	= /^(http(s?)\:\/\/)?(([a-zA-Z0-9-]{1,63}[.])|([a-zA-Z0-9-]{1,63}[.])([a-zA-Z0-9-]{1,63}[.])|([a-zA-Z0-9-]{1,63}[.])([a-zA-Z0-9-]{1,63}[.])([a-zA-Z0-9-]{1,63}[.]))(museum|travel|aero|arpa|asia|edu|gov|mil|mobi|coop|info|name|biz|cat|com|int|jobs|net|org|pro|tel|a[cdefgilmnoqrstuwxz]|b[abdefghijlmnorstvwyz]|c[acdfghiklmnoruvxyz]|d[ejkmoz]|e[ceghrstu]|f[ijkmor]|g[abdefghilmnpqrstuwy]|h[kmnrtu]|i[delmnoqrst]|j[emop]|k[eghimnprwyz]|l[abcikrstuvy]|m[acdefghklmnopqrstuvwxyz]|n[acefgilopruz]|om|p[aefghklmnrstwy]|qa|r[eosuw]|s[abcdeghijklmnortuvyz]|t[cdfghjklmnoprtvwz]|u[agkmsyz]|v[aceginu]|w[fs]|y[etu]|z[amw])(\/\S+|\/|)$/gi;
var regExpEmail		= /^[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*\.[a-zA-Z]{2,3}$/i;
var regExpDate		= /^(19|20)\d{2}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[0-1])$/; 
var regEngNum		= /^[A-Za-z0-9+]*$/;
var regExpId		= /^[a-z][a-z0-9]{5,11}$/g;
var regExpIdBo		= /^[a-z][a-z0-9]{2,11}$/g;
var regExpPasswd1	= /^[a-zA-Z0-9\{\}\[\]\/?.,;:|\)*~`!^\-_+<>@\#$%&\\\=\(\'\"]{8,20}$/g;
var regExpPasswd2	= /^(?=.*[a-zA-Z]).{8,20}$/;
var regExpPasswd3	= /^(?=.*[0-9]).{8,20}$/;
var regExpPasswd4	= /^(?=.*[\{\}\[\]\/?.,;:|\)*~`!^\-_+<>@\#$%&\\\=\(\'\"]).{8,20}$/;
var regExpFileName	= /[^(가-힣ㄱ-ㅎㅏ-ㅣa-zA-Z0-9_.\-)]/g;
var listKeyCheck	= 0;
var tmpCheckId		= '';

$(function() {

	$("form").attr('autocomplete','off');
	
	$("input:text, input:password").each(function(){
		$(this).attr('autocomplete','off');
	});
	
	$("button:submit, input:submit, input:image").click(function(e) {		
		if($(this).attr('class') == 'tag-remove') return;

		e.preventDefault();
		e.stopPropagation();

		var f  = this.form;		
		return formSubmitCkeck(f);
	});

	$("input:text, input:password").blur(function(e) {
		$('.inputMessageBox').fadeOut('200').remove();
		listKeyCheck = 0;
		
		$t = jQuery(this);

		if(($t.attr('data-check-email') == 1 || $t.attr('data-check-domain') == 1 || $t.attr('data-check-id') == 1 || $t.attr('data-check-bo_id') == 1 || $t.attr('data-check-password') > 0 || $t.attr('data-check-file-name') == 1 || $t.attr('data-check-tel') == 1 || $t.attr('data-check-cell') == 1 || $t.attr('data-check-birth') == 1 || $t.attr('data-check-comp_num') == 1) && jQuery.trim($t.val())) {
			
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
				sum += parseInt((sno.charAt(8) * 5) / 10, 10);
				at = sum % 10;
				if(at != 0) att = 10 - at;  

				if(sno.charAt(9) != att) {
					displayMessage($t, '올바른 사업자등록번호가 아닙니다.');
					return;
				}
			}

			if($t.attr('data-check-bo_id') == 1) {
				if(tmpCheckId == $t.val()) return;
				if($t.attr('readonly')) return;

				if($t.val().match(regExpIdBo)==null) {
					displayMessage($t, '아이디는 영문시작, 영소문자, 숫자로 3~12자만 가능합니다.');
					return;
				}
				else {					
					$.getJSON( "board_id_check_json.php?id=" + $t.val(), 
						function(data) { 						
							$.each(data, function(key, value) {
								if(data[key].status == 1) {
									alertify.error(data[key].error);
									$t.val('').trigger('focus');
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

			if($t.attr('data-check-id') == 1) {
				if(tmpCheckId == $t.val()) return;
				if($t.attr('readonly')) return;

				if($t.val().match(regExpId)==null) {
					displayMessage($t, '아이디는 영문시작, 영소문자, 숫자 조합 6~12자만 가능합니다.');
					return;
				}
				else {					
					$.getJSON( "vendor_id_check_json.php?id=" + $t.val(), 
						function(data) { 						
							$.each(data, function(key, value) {
								if(data[key].status == 1) {
									alertify.error(data[key].error);
									$t.val('').trigger('focus');
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

	$("label").hover(function(e) {
		a = $(this).children("input").attr('type');
		if(a=='checkbox') $(this).children("span").addClass("chover");
		else $(this).children("span").addClass("rhover");
		
	}, function() {
		a = $(this).children("input").attr('type');
		if(a=='checkbox') $(this).children('span').removeClass("chover");
		else $(this).children('span').removeClass("rhover");
	});

	$('body').find("input:text, input:password, input:file, textarea").each(function(i) {
		$t = jQuery(this);
		if($t.prop("required")) {
			iconType = '';
			if($t.hasClass('only_num_format') || $t.hasClass('only_num') || $t.hasClass('only_num_jum')) {
				iconType = 'Num';
			}
			else {			
				if($t.parent().is('div')) iconType = 'Div';
				else if($t.parent().is('p')) iconType = 'P';
			}
			$t.parent().append('<p class="requireIcon' + iconType + '"><i class="fas fa-pen-square masterTooltip" title="필수 입력사항 입니다."></i></p>');
		}
		else if($t.attr("data-zip-search")) {
			$t.parent().append('<p class="searchIcon"><i class="xi-search masterTooltip" title="검색"></i></p>');
		}
		else if($t.attr("data-search-btn")) {
			$t.parent().append('<p class="searchIcon2 searchIconBtn"><i class="xi-search masterTooltip" title="검색"></i></p>');
		}
		else if($t.attr("data-search-btn2")) {
			$t.parent().append('<p class="searchIcon3 searchIconBtn2"><i class="xi-search"></i></p>');
		}
		
		if($t.attr("data-date-picker")) {
			iconType = '';
			if($t.attr("data-date-picker") == '2') iconType = 'Rq';
			$t.parent().append('<p class="dateIcon' + iconType + '"><i class="xi-calendar-check xi-x masterTooltip" title="날자검색"></i></p>');		
			$t.datepicker();
		}
	});	

	// 검색 아이콘(자동완성 X, 다음 팝업만)
	$(document).on('click', '.searchIcon', function(){
		var $in = $(this).parent().children('input');
		var kw = $.trim($in.val());
		if(kw.length < 2){
			$in.focus();
			alertify.alert("두글자 이상 입력 하시기 바랍니다.");
			return false;
		}
		var fullName = $in.attr('name') || '';
		var vls = fullName.replace(/_postcode_search$/,'');
		execDaumPostcode(vls, kw);
	});

	$('.searchIconBtn').click(function(e){
		// 기존: AJAX + submit → 제거. 필요 시 버튼 의미 재정의.
		i = $(this).parent().children('input');
		if(!jQuery.trim(i.val())) {
			i.focus();			
			return false;
		}
		// 여기는 폼 제출 용도로 남김 (원래 로직 유지)
		ckCookie();
		$(i).parents('form').submit();
	});		

	$('.searchIconBtn2').click(function(e){
		i = $(this).parent().children('input');
		if(!jQuery.trim(i.val())) {
			i.focus();			
			return false;
		}
		// 기존 searchResultLoad() 그대로 유지 (주소검색과 무관)
		searchResultLoad && searchResultLoad();
	});		

	$('.dateIcon, .dateIconRq').click(function(e){
		i = $(this).parent().children('input');
		$(i).trigger('focus');
	});		

	$('.only_eng_num').keypress(function(event) {
		if(event.which && (event.which < 48 || event.which > 127) ) {
			event.preventDefault();
		}
		if(event.which && (event.which > 57 && event.which < 65) ) {			
			event.preventDefault();
		}

    }).keyup(function(){
        if( $(this).val() != null && $(this).val() != '' ) {
			$(this).val( $(this).val().replace(/[^0-9a-zA-Z]/g, '') );
        }
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
		$(this).val($(this).val().replace(/[^0-9-]/g, '').replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,'));
	});
}

function addKeydownEvent(obj) {
	$("input:text, input:password").unbind('keydown').bind('keydown',function(e) {		
		listKeyCheck = 1;
		
		var $m		= jQuery(this);
		var mVal	= jQuery.trim($m.val());

		if (e.keyCode == 13) {

			if($(this).attr('class') == 'heap-search') return false;

			if($m.attr('id') == 'fancyInput') {
				$('#fancySearch').trigger('click');
			}

			if($m.hasClass("tag-input")) {
				if(mVal) return true;
			}

			e.preventDefault();
			e.stopPropagation();
			
			var f  = this.form;
			if(!f) f = eval("document." + $m.attr("forms"));

			// 변경: data-zip-search면 항상 다음 팝업 호출
			if($m.attr("data-zip-search") == 1 && mVal) {
				var fullName = $m.attr('name') || '';
				var vls = fullName.replace(/_postcode_search$/,'');
				execDaumPostcode(vls, mVal);
				return true;
			}
			if($m.attr("data-no-enter") == 1 && mVal) return true;		
			
			if(mVal && mVal != '0') {				
				if($m.hasClass("data-ck-enter")) {					
					$m.blur();
					return true;
				}		
				
				if($m.attr("data-on-enter")==1) {
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
			
			if($m.attr("data-next-focus") == 1) {				
				$m.blur();
				$("form[name=goodsForm] input[name='image1']").trigger('click');
			}
			else if($m.attr("data-next-focus") == 2) {
				$('.sn_detail').summernote({focus: true});
			}
			if($m.attr("data-next-focus") == 3) {
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

	$f.find("input, select, textarea").each(function(i) {
		$t = jQuery(this);

		if(typeof($t.attr("data-notefor")) != 'undefined') {			
			if($($t.attr("data-notefor")).summernote('code') != '<p><br></p>') {
				$t.val($($t.attr("data-notefor")).summernote('code'));				
			}
		}	
		
		if($t.prop("required")) {

			if($t.prop("name")=="explains"){				
				if($("form[name=goodsForm] input[name='detail_image_only']").val() == '1'){
					if(!$('form[name=goodsForm] input[name=detail_image_order]').val()) {
						alertify.alert("상세이미지 하나 이상은 등록 하셔야 됩니다.");
						result = false;
						return false;	
					}
				}
				else {
					if(!jQuery.trim($t.val())) {					
						$($t.attr("data-notefor")).summernote({focus: true});
						alertify.alert("상세설명을 입력 하시기 바랍니다.");
						result = false;
						return false;	
					}
				}
			}
			else if($t.prop("name") == "content"){				
				if(!jQuery.trim($t.val())) {					
					$($t.attr("data-notefor")).summernote({focus: true});
					alertify.alert("내용을 입력 하시기 바랍니다.");
					result = false;
					return false;					
				}
			}
			else if($t.prop("type") == 'file') {
				if(!jQuery.trim($t.val())) {
					alertify.alert($t.attr("data-msg") + ' 등록 해 주세요.');					
					result = false;
					return false;				
				}
			}
			else  if(!jQuery.trim($t.val()) || jQuery.trim($t.val()) == '0') {				
				if($t.prop("type") == 'hidden') {
					if(typeof($t.attr("data-fname")) != 'undefined') {
						var position = $($t.attr("data-fname")).position();
						var contentPosition = position.top + 160;
						$("html, body").animate({ scrollTop: contentPosition }, 100);
					}

					if(typeof($t.attr("data-notefor")) != 'undefined') {
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

				$("html, body").animate({ scrollTop: '-=100px' }, 100);

				result = false;
				return false;

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
		if($(".btnSubmitBox .displayInline .shine, .btnSubmitBoxPop .displayInline .shine").hasClass('shineSend')) return;
		$(".btnSubmitBox .displayInline .shine, .btnSubmitBoxPop .displayInline .shine").addClass('shineSend'); 
	}
	else {
		ckCookie && ckCookie();
	}
	
	if(!$(f).attr('action').length) {
		if($(f).attr('data-submit-proc').length) eval($(f).attr('data-submit-proc') + "()");
		return;
	}
	
	f.submit();	
	return false;
}

function displayMessage($m, t) {
	if(!t) {
		var ttl = " 입력";		
		if($m.attr("data-msg") && $m.attr("data-msg").indexOf("이미지") > 0) ttl = " 등록";
		t = ($m.attr("data-msg") || '') + ttl + '해주세요.';
	}
	$m.focus();

	$('.inputMessageBox').fadeOut('200').remove();
	$m.parent().append('<div class="inputMessageBox"><p class="inputMessage"><i class="xi-pen size07"></i>&nbsp;'+ t + '</p><p class="inputMessage_arrow"></p></div>');
	
	var tops = 10;
	if($m.parent().is('div') || $m.parent().is('p')) tops = 20;
	$('.inputMessageBox').css({opacity:"0.1"}).animate({top:'-=' + tops + 'px', opacity:1.0}, 200,(function(){ $m.trigger('focus'); }));
}

/** 
 * 주소검색: 항상 다음(카카오) 팝업만 사용
 * name 규칙: xxx_postcode, xxx_address1, xxx_address2, xxx_postcode_search
 */
function zipSearch(name) {
	// 검색 입력창
	var $input = $("input[name='"+name+"_postcode_search']");

	// 검색 아이콘 클릭 → 카카오 팝업
	$input.parent().find('.searchIcon').off('click').on('click', function(){
		var kw = $.trim($input.val());
		if(kw.length < 2){
			$input.focus();
			alertify.alert("두글자 이상 입력 하시기 바랍니다.");
			return false;
		}
		execDaumPostcode(name, kw);
	});
}

function zipSearch2(name) {
	// name 자체가 address 입력 name이면, 그 입력값으로 팝업 실행할 수 있게 훅 제공
	var $input = $("input[name='"+name+"']");
	$input.parent().find('.requireIcon').off('click').on('click', function(){
		var kw = $.trim($input.val());
		// name이 xxx_address1 형태라면 base name 추출
		var vls = name.replace(/_address1$/,'');
		execDaumPostcode(vls, kw);
	});
}

// 다음 우편번호 호출 (버그 수정: name -> vls)
function execDaumPostcode(vls, keyword) {
	var width = 500; // 팝업 너비
	var height = 600; // 팝업 높이
	
	new daum.Postcode({			
		width: width, 
	    height: height,
		oncomplete: function(data) {
			$("input[name='"+vls+"_postcode']").val(data.zonecode);
			$("input[name='"+vls+"_address1']").val(data.roadAddress || data.jibunAddress || '');
			$("input[name='"+vls+"_address2']").trigger('focus');
			$("input[name='"+vls+"_postcode_search']").val('');
		}
	}).open({
		q: keyword || '', 
		left: (window.screen.width / 2) - (width / 2), 
		top: (window.screen.height / 2) - (height / 2)
	});
}

// Set caret position easily in jQuery
// Written by and Copyright of Luke Morton, 2011
// Licensed under MIT
(function ($) {
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
    
    $.caretPos = function (el) {
        if ("selection" in document) {
            var range = el.createTextRange();
            try {
                range.setEndPoint("EndToStart", document.selection.createRange());
            } catch (e) {
                return 0;
            }
            return range.text.length;
        } else if (el.selectionStart != null) {
            return el.selectionStart;
        }
    };

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

    $.fn.caretToStart = function () {
        return this.caret(0);
    };

    $.fn.caretToEnd = function () {
        return this.queue(function (next) {
            $.caretTo(this, $(this).val().length);
            next();
        });
    };
}(jQuery));
