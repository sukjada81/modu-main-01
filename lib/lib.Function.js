(function($){
  $.fn.btobPopup = function(options) {

    var options = $.extend({
      name: "",
      link: "",
      position: 8,
      content: "",
      cookie: "",
      width: "",
      height: "",
      posx: "",
      posy: "",
      img: "",
      mobile: 0
    }, options);

    function popupShow() {
      $(popupId).html(popupContent).css("display", "flex").hide().fadeIn(800);
    }

    function popupClose(obj) {
      var popupId2 = $(obj).attr("data-id");
      if (options.position == 1 || options.position == 4 || options.position == 7) $(popupId2).removeClass('popup-slide-right').addClass('popup-slide-right-rev');
      else if (options.position == 3 || options.position == 6 || options.position == 9) $(popupId2).removeClass('popup-slide-left').addClass('popup-slide-left-rev');
      else if (options.position == 8) $(popupId2).removeClass('popup-slide-top').addClass('popup-slide-top-rev');
      else if (options.position == 2) $(popupId2).removeClass('popup-slide-bottom').addClass('popup-slide-bottom-rev');
      $(popupId2).fadeOut(800);

      if (options.cookie) {
        if (options.mobile != 1) {
          if (!$(popupId2).find(".today").prop("checked")) return;
        }
        var pop_cookie = options.cookie.split('|');
        for (var i = 0; i < pop_cookie.length; i++) {
          $.cookie("popup_" + pop_cookie[i], 1, { expires: 1 });
        }
      }
    }

    function sliderInit(owidth, oheight) {
      var _SlideshowTransitions = [
        { $Duration: 1200, x: -0, $During: { $Left: [0, 0.7] }, $Easing: { $Left: $Jease$.$InCubic, $Opacity: $Jease$.$Linear }, $Opacity: 2, $Outside: true },
        { $Duration: 1200, x: 0, $SlideOut: true, $Easing: { $Left: $Jease$.$InCubic, $Opacity: $Jease$.$Linear }, $Opacity: 2, $Outside: true }
      ];
      var options = {
        $AutoPlay: 1,
        $AutoPlaySteps: 1,
        $Idle: 4000,
        $PauseOnHover: 1,
        $ArrowKeyNavigation: 1,
        $SlideDuration: 500,
        $MinDragOffsetToSlide: 20,
        $SlideWidth: parseInt(owidth),
        $SlideHeight: parseInt(oheight),
        $SlideSpacing: 0,
        $UISearchMode: 1,
        $PlayOrientation: 1,
        $DragOrientation: 3,
        $SlideshowOptions: { $Class: $JssorSlideshowRunner$, $Transitions: _SlideshowTransitions, $TransitionsOrder: 1 },
        $BulletNavigatorOptions: { $Class: $JssorBulletNavigator$, $ChanceToShow: 2, $ActionMode: 3, $Rows: 1, $SpacingX: 10, $SpacingY: 10 },
        $ArrowNavigatorOptions: { $Class: $JssorArrowNavigator$, $ChanceToShow: 1 },
        $ThumbnailNavigatorOptions: { $Class: $JssorThumbnailNavigator$, $ChanceToShow: 2, $ActionMode: 0, $NoDrag: true, $Orientation: 2 }
      };
      var jssor_slider = new $JssorSlider$(slideId, options);
    }

    var randId = Math.round(Math.random() * 99999999);
    var popupId = '#btob-popup' + randId;          // 접두어 변경
    var slideId = '#slider_container' + randId;

    // 클래스/ID 모두 btob로 생성
    $('<div/>', { id: 'btob-popup' + randId, class: 'btobPopup' }).appendTo('body');

    var bottoms = '';
    if (options.cookie) {
      bottoms = '<div class="popup-bottom">오늘하루 보이지 않기 <label><input type="checkbox" class="today" value="1" checked="checked" /><span style="transform:scale(0.8);"></span></label></div>';
    }

    options.link = options.link.replace(/\s/g, "");

    var popupContent = '';
    if (options.img == "2") {
      var pop_img  = options.content.split('|');
      var pop_name = options.name.split('|');
      var pop_link = options.link.split('|');

      var contents = '<div id="' + slideId + '" class="slider_container">\
                        <div data-u="slides" class="slides">';
      for (var i = 0; i < pop_img.length; i++) {
        contents += '<div><img data-u="image" src="' + pop_img[i] + '" data-link="' + (pop_link[i] || '') + '"/><div data-u="thumb">' + (pop_name[i] || '') + '</div></div>';
      }
      contents +=   '</div>\
                      <div data-u="thumbnavigator" class="thumbnavigator">\
                        <div data-u="slides">\
                          <div data-u="prototype" class="prototype">\
                            <div data-u="thumbnailtemplate" class="thumbnailtemplate"></div>\
                          </div>\
                        </div>\
                      </div>\
                      <div u="navigator" class="navigator">\
                        <div u="prototype" style=" position: absolute; width: 12px; height: 12px;"></div>\
                      </div>\
                      <div data-u="arrowleft" class="arrowleft arrow" data-autocenter="2" data-scale="0.75" data-scale-left="0.75">\
                        <i class="xi-angle-left-thin xi-2x"></i>\
                      </div>\
                      <div data-u="arrowright" class="arrowright arrow" data-autocenter="2" data-scale="0.75" data-scale-right="0.75">\
                        <i class="xi-angle-right-thin xi-2x"></i>\
                      </div>\
                    </div>';

      popupContent = '<div class="popup_box"><div class="popup-close" data-id="' + popupId + '"></div><div class="popup-content">' + contents + '</div> ' + bottoms + ' </div>';
    } else {
      popupContent = '<div class="popup_box"><div class="popup-close" data-id="' + popupId + '"></div><div class="popup-content popup-one"><a href="' + options.link + '">' + options.content + '</a></div>' + bottoms + '</div>';
    }

    popupShow();

    if (options.mobile == 1) {
      var defWidth  = parseInt($(".btobPopup").css('width'));   // 여기 변경
      var defHeight = parseInt((defWidth * options.height) / options.width);
      options.width  = defWidth;
      options.height = defHeight;
    }

    if (options.img == "2") {
      $(popupId).find('.slider_container').css({ "width": options.width, "height": options.height });
      $(popupId).find('.slider_container .slides').css({ "width": options.width, "height": options.height });
      $(popupId).find('.slider_container .thumbnavigator').css({ "width": options.width });
      sliderInit(options.width, options.height);
    }

    if (options.width) {
      $(popupId).css("width", options.width);
      $(popupId).find(".popup-one").css("width", options.width);
    }
    if (options.height) {
      $(popupId).css('height', options.height);
      $(popupId).find(".popup-one").css("height", options.height);
    }
    if (!options.img) $(popupId).find(".popup-content").addClass("editer");

    var w = window.innerWidth || document.documentElement.clientWidth;
    var h = window.innerHeight || document.documentElement.clientHeight;
    var left = (w / 2) - (parseInt($(popupId).css('width')) / 2);
    var top  = (h / 2) - (parseInt($(popupId).css('height')) / 2);

    if (options.position == 0) {
      $(popupId).css({ "top": options.posx + 'px', "left": options.posy + 'px' });
    }
    else if (options.position == 5) {
      $(popupId).addClass('position' + options.position);
      $(popupId).css({ "left": left, "top": top });
    }
    else {
      $(popupId).addClass('position' + options.position);
      if (options.position == 1 || options.position == 4 || options.position == 7) $(popupId).addClass('popup-slide-right');
      else if (options.position == 3 || options.position == 6 || options.position == 9) $(popupId).addClass('popup-slide-left');
      else if (options.position == 8) {
        $(popupId).addClass('popup-slide-top');
        $(popupId).css({ "left": left });
      }
      else if (options.position == 2) {
        $(popupId).addClass('popup-slide-bottom');
        $(popupId).css({ "left": left });
      }
    }

    $(popupId).find(".popup-close").on('click', function () {
      popupClose(this);
    });

    $(popupId).find(".slides").find('img').on('click', function () {
      var link = $(this).attr('data-link');
      if (link) window.location.href = link;
    });

    return this;
  };
})(jQuery);
