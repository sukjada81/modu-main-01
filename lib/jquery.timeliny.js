/**
 * A jQuery plugin for creating interactive year based timelines.
 * Author: Sylvain Simao - https://github.com/maoosi * 
 * 
 */

;(function ( $, window, document, undefined ) {

    "use strict";

	/**
	 * Plugin object constructor.
	 */
	function Plugin(element, options) {

		// References to DOM and jQuery versions of element.
		var el = element;
		var $el = $(element);		
		var children = $el.children();

		// Extend default options with those supplied by user.
		options = $.extend({}, $.fn['timeliny'].defaults, options);

		/**
		 * Initialize plugin.
		 * @private
		 */
		function _init() {
			hook('onInit');

			_addElems();           
			_createWrapper();
			_createDots();			
			_fixBlockSizes();
			_clickBehavior();
      		_arrowBehavior();
			_createVerticalLine();
			_createNavigation();
			_updateTimelinePos();
			_resizeBehavior();
			_dragableTimeline();
			_touchableTimeline();
			_loaded();

			if(options.listReset == 1) _resetList();
		}

		/**
		 * reset plugin.
		 * @private
		 */

		function _reset() {
			$el.first().off('mousedown');			
			$(document).off('mousemove.timeliny');
			$(document).off('mouseup.timeliny');
			$el.html('');			

			_addElems();           
			_createWrapper();
			_createDots();			
			_fixBlockSizes();
			_clickBehavior();
      		_createVerticalLine();
			_createNavigation();
			_updateTimelinePos('click');
			_dragableTimeline();
			_touchableTimeline();
			_loaded();		
			
			_resetList();
			
			$('.only_num').keypress(function(event) {
				if(event.which && (event.which < 48 || event.which > 57) ) {
					event.preventDefault();
				}
			}).keyup(function(){
				if( $(this).val() != null && $(this).val() != '' ) {
				  $(this).val( $(this).val().replace(/[^0-9]/g, '') );
				}
			});			
		}

		
		/**
		 * Plugin is loaded
		 * @private
		 */
		function _loaded() {
			$el.addClass('loaded');

			var currPage= $el.find('.' + options.className + '-timeblock.active').first().attr('data-page');
			hook('afterLoad', [currPage]);
		}

		/**
		 * Add elements 
		 * @private
		 */
		function _addElems() {			

			options.block = Math.ceil(parseInt(options.page) / parseInt(options.boundaries));
			var startPage = (parseInt(options.boundaries) * (parseInt(options.block) - 1)) + 1;
			
			if((parseInt(startPage) + parseInt(options.boundaries)) < parseInt(options.lastPage)) {
				options.blockLastPage = (parseInt(startPage) + parseInt(options.boundaries) - 1);
			}
			else options.blockLastPage = parseInt(options.lastPage);			

			if(parseInt(options.block) > 1) $el.append('<div data-page="prev" class="btn"></div>');

			for (var y=startPage; y<=options.blockLastPage; y++) {				
				if(y==parseInt(options.page)) $el.append('<div data-page="' + y + '" class="active"></div>');
				else $el.append('<div data-page="' + y + '" class=""></div>');									
			}			

			if(parseInt(options.blockLastPage) < parseInt(options.lastPage)) $el.append('<div data-page="next" class="btn"></div>');

			children = $el.children();
		}

		/**
		 * Create wrapper
		 * @private
		 */
		function _createWrapper() {
			return $el.addClass(options.className).children().wrapAll( options.wrapper).wrapAll( '<div class="' + options.className + '-timeline"></div>' );
		}

		/**
		 * Fix sizes of timeline and timeblocks elements
		 * @private
		 */
		function _fixBlockSizes() {
			$el.find('.' + options.className + '-timeline').css('width', ''+ (children.length * options.pageBlockSize) +'px');
			$el.find('.' + options.className + '-timeliny-timeblock').css('width', '' + options.pageBlockSize + 'px');
		}

		/**
		 * Create html structure
		 * @private
		 */
		function _createDots() {
			children.each(function( index ) {
				var page = $(this).attr('data-page');				
			
				var dotHtml  = '<a href="#' + page + '" class="' + options.className + '-dot" data-page="' + page + '"></a>';
					dotHtml += '<span class="' + options.className + '-page">' + page + '</span>';

				$(this).addClass('' + options.className + '-timeblock').html(dotHtml);
			});
		}

		/**
		 * Create vertical line
		 * @private
		 */
		function _createVerticalLine() {
			$el.append('<div class="' + options.className + '-vertical-line"></div>');			
		}
		
		/**
		 * Create navigation
		 * @private
		 */
		function _createNavigation() {			

			if(parseInt(options.lastPage) < 2) { 
				$(".paging").hide();
				if($(".paging2").length) $(".paging2").hide();
				return;
			}
			
			$(".paging").show();
			if($(".paging2").length) $(".paging2").show();
			
			if(typeof(is_mobile) != 'undefined') {				
				$el.append('<div class="' + options.className + '-help"><div><i class="xi-long-arrow-left"></i> <i class="xi-touch xi-x"></i> <i class="xi-long-arrow-right"></i><div></div>');
			}
			else {
				$el.append('<div class="' + options.className + '-help" title="방향키로 이전/다음 페이지로 이동가능"><div class="' + options.className + '-help-box"><i class="xi-arrow-left"></i></div><div class="' + options.className + '-help-box"><i class="xi-arrow-right"></i><div></div>');
			}

			if(parseInt(options.lastPage) < 11) return;

			var naviHtml  = '<div class="naviFirst"><i class="xi-arrow-bottom xi-x rotate90" title="첫페이지" ></i></div>';
				if(options.block > 1) naviHtml  += '<div class="naviPrev"><i class="xi-angle-left" title="이전블럭" ></i></div>';
				naviHtml += '<div class="floatLeft"><input type="text" id="' + options.className + '-movePage" class="only_num" placeholder="이동할페이지" style="z-index:99999; text-align:center; border:1px solid #666; height: 14px; padding:5px; width:70px; font-size:14px; font-family: Montserrat, sans-serif;" /></div>';
				if(options.blockLastPage < options.lastPage) naviHtml  += '<div class="naviNext"><i class="xi-angle-right" title="다음블럭" ></i></div>';
				naviHtml += '<div class="naviLast"><i class="xi-arrow-top xi-x rotate90" title="마지막페이지"></i></div>';
			
			$el.append('<div class="' + options.className + '-navigation"> ' + naviHtml + '</div>');			

			$('.' + options.className + '-navigation').hover(function(e){	
				$(this).stop().animate({opacity:1}, 'fast');
				$('#' + options.className + '-movePage').focus();			
			}, 
			function() {
				$(this).stop().animate({opacity:0.2}, 'fast');
				$('#' + options.className + '-movePage').blur();	
			});
			
			$('.' + options.className + '-navigation .naviFirst').on('click', function(e) {	
				if(options.block!=1) {
					options.page = 1;
					_reset();
				}
				else goToPage(1);
			});

			$('.' + options.className + '-navigation .naviLast').on('click', function(e) {	
				var lastBlock = Math.ceil(parseInt(options.lastPage) / parseInt(options.boundaries));
				if(options.block!=lastBlock) {
					options.page = options.lastPage;
					_reset();
				}
				else goToPage(options.lastPage);
			});

			$('.' + options.className + '-navigation .naviPrev').on('click', function(e) {	
				options.page = (parseInt(options.boundaries) * (parseInt(options.block) - 1));
				_reset();
			});

			$('.' + options.className + '-navigation .naviNext').on('click', function(e) {	
				options.page = parseInt(options.blockLastPage) + 1;
				_reset();
			});

			$('#' + options.className + '-movePage').blur(function(e) {
				_movePage($(this).val());
				$(this).val('');
				
			});

			$('#' + options.className + '-movePage').keydown(function(e) {
				if (e.keyCode == 13) {
					_movePage($(this).val());
					$(this).val('');
				}
			});
		}

		/**
		 * Check block and Go to a page
		 * @public
		 */

		function _movePage(page) {		
								
			if(!page) return;
			page = parseInt(page);
			if(page < 1 || page > options.lastPage) return; 
			
			var currBlock = Math.ceil(page / parseInt(options.boundaries));
			
			if(currBlock != options.block) {
				options.page = page;
				_reset();
			}
			else goToPage(page);
		}

		/**
		 * Update the position of the timeline
		 * @private
		 */
		function _updateTimelinePos(callEvent) {
			
			var linePos = $el.find('.' + options.className + '-vertical-line').position().left;				
			var activeDotPos = $el.find('.' + options.className + '-timeblock.active').position().left;	
			var dotRadius = $el.find('.' + options.className + '-timeblock.active .' + options.className + '-dot').width() / 2;

			var diff = activeDotPos - linePos;
			var left;

			if (diff > 0) {
				left = '-' + (Math.abs(diff) + dotRadius + 1) +'';				
			} else {
				left = '+' + (Math.abs(diff) - dotRadius - 1) +'';				
				if(left.indexOf('+-') != -1) {
					left = (Math.abs(diff) - dotRadius - 1) +'';					
				}
			}
			
			$el.find('.' + options.className + '-timeline').stop().animate({
				left: left
			}, options.animationSpeed, function() {					
				if (typeof callEvent != 'undefined') {
					if (callEvent === 'click') {
						var currPage = $el.find('.' + options.className + '-timeblock.active').first().attr('data-page');
						if(currPage == options.page) return;

						options.page = currPage;
						hook('afterChange', [currPage]);

						if(currPage=='prev' || currPage=='next') {
							if(currPage=='prev') options.page = (parseInt(options.boundaries) * (parseInt(options.block) - 1));
							else options.page = parseInt(options.blockLastPage) + 1;							
							_reset();
						}
						else _resetList();
					}
					else if (callEvent === 'resize') hook('afterResize');
				}
			});
		}

	
		/**
		 * Listen for click event
		 * @private
		 */
		function _clickBehavior() {
			children.parent().find('.' + options.className + '-timeblock:not(.inactive) .' + options.className + '-dot').on('click', function(e) {				
				e.preventDefault();
				
				var currPage = $(this).parent().parent().find('.' + options.className + '-timeblock.active').attr('data-page');
				var nextPage = $(this).attr('data-page');

				if (currPage != nextPage) {
					hook('onLeave', [currPage, nextPage]);

					children.removeClass('active');
					$(this).closest('.' + options.className + '-timeblock').addClass('active');
				}
				_updateTimelinePos('click');

				return false;
			});
		}

		/**
		 * Arrow keys navigation
		 * @private
		 */
		function _arrowBehavior() {			

			$('html').keydown(function (e) {
				if(listKeyCheck == 1) return;

				if (e.which == 39) {
					var pages = $(this).find('.' + options.className + '-timeblock:not(.inactive) .' + options.className + '-dot');
					var currPage = $(pages).parent().parent().find('.' + options.className + '-timeblock.active').attr('data-page');
					var nextPage = $(pages).parent().parent().find('.' + options.className + '-timeblock.active').next().attr('data-page');
					goToPage(nextPage);
				} 
				else if (e.which == 37) {
					var pages = $(this).find('.' + options.className + '-timeblock:not(.inactive) .' + options.className + '-dot');
					var currPage = $(pages).parent().parent().find('.' + options.className + '-timeblock.active').attr('data-page');
					var prevPage = $(pages).parent().parent().find('.' + options.className + '-timeblock.active').prev().attr('data-page');
					goToPage(prevPage);
				}

			});			
		}

		/**
		 * Listen resize event
		 * @private
		 */
		function _resizeBehavior() {

			function debounce(callback, delay) {
				var timer;
				return function(){
					var args = arguments;
					var context = this;
					clearTimeout(timer);
					timer = setTimeout(function(){
						callback.apply(context, args);
					}, delay)
				}
			}

			$(window).on('resize.timeliny', debounce(function() {
				_updateTimelinePos('resize');
			}, 350));
		}

		/**
		 * Make the timeline draggable
		 * @private
		 */
		function _dragableTimeline() {

			var selected = null, x_pos = 0, x_elem = 0;

			// Will be called when user starts dragging an element
			function _drag_init(elem) {
				selected = elem;
				x_elem = x_pos - selected.offsetLeft;
			}

			// Will be called when user dragging an element
			function _move_elem(e) {
				x_pos = document.all ? window.event.clientX : e.pageX;
				if (selected !== null) {			
					selected.style.left = (x_pos - x_elem) + 'px';
				}
			}

			// Destroy the object when we are done
			function _stop_move() {				
				if (selected) {
					// active the closest elem
					var linePos = $el.find('.' + options.className + '-vertical-line').offset().left;
					var closestDotPage = null;
					var diff = 99999999999999999999999;

					children.parent().find('.' + options.className + '-timeblock:not(.inactive) .' + options.className + '-dot').each(function (index) {
						var currDotPos = $(this).offset().left;
						var currDiff = Math.abs(currDotPos - linePos);

						if (currDiff < diff) {
							//console.log($(this).attr('data-page'));
							closestDotPage = $(this).attr('data-page');
							diff = currDiff;
						}
					});

					$el.find('.' + options.className + '-dot[data-page=' + closestDotPage + ']').trigger('click');
					selected = null;
				}
			}

			// Bind the functions...
			$el.first().on('mousedown', function() {
				_drag_init($el.find('.'+ options.className +'-timeline')[0]);
				return false;
			});

			$(document).on('mousemove.timeliny', function(e) {
				_move_elem(e);
			});

			$(document).on('mouseup.timeliny', function() {
				_stop_move();
			});
		}

		/**
		 * Make the timeline touchable
		 * @private
		 */
		function _touchableTimeline() {

			var selected = null, x_pos = 0, x_elem = 0, x_start = 0;

			// Will be called when user starts dragging an element
			function _drag_init(elem, e) {
				if (selected == null) {
					selected	= elem;
					x_elem		= selected.offsetLeft;	
					x_start		= parseInt(e.originalEvent.changedTouches[0].clientX);	
				}
			}

			// Will be called when user dragging an element
			function _move_elem(e) {
				x_pos = parseInt(e.originalEvent.changedTouches[0].clientX);
				if (selected !== null) {					
					selected.style.left = x_elem + (x_pos - x_start) + 'px';
				}
			}

			// Destroy the object when we are done
			function _stop_move() {				
				if (selected) {
					// active the closest elem
					var linePos = $el.find('.' + options.className + '-vertical-line').offset().left;
					var closestDotPage = null;
					var diff = 99999999999999999999999;

					children.parent().find('.' + options.className + '-timeblock:not(.inactive) .' + options.className + '-dot').each(function (index) {
						var currDotPos = $(this).offset().left;
						var currDiff = Math.abs(currDotPos - linePos);

						if (currDiff < diff) {
							//console.log($(this).attr('data-page'));
							closestDotPage = $(this).attr('data-page');
							diff = currDiff;
						}
					});

					$el.find('.' + options.className + '-dot[data-page=' + closestDotPage + ']').trigger('click');
					selected = null;
				}
			}

			// Bind the functions...
			$el.first().on('touchstart', function(e) {				
				_drag_init($el.find('.'+ options.className +'-timeline')[0], e);				
			});	

			$(document).on('touchmove.timeliny', function(e) {
				_move_elem(e);
			});

			$(document).on('touchend.timeliny', function() {
				_stop_move();
			});
		}

		/**
		 * Go to a particular page
		 * @public
		 */
		function goToPage(page) {
			var selector = $el.find('.' + options.className + '-timeblock[data-page=' + page + ']:not(.inactive) .' + options.className + '-dot').first();
			if (selector.length > 0) {
				selector.trigger('click');
			}
		}


		/**
		 * List reset
		 * @public
		 */
		function _resetList(){
			
			var obj = $('#' + options.listArea);
			var listHeight = parseInt(obj.css('height'));
			
			obj.append('<div class="' + options.className + '-loading" style="text-align:center"><i class="xi-spinner-2 xi-x"></i><br /><br />Loading...</div>');
			var loadingHeight = parseInt($('.' + options.className + '-loading').css('height'));

			if(listHeight < loadingHeight) var top = 0;
			else var top = (listHeight - loadingHeight) / 2;

			$('.' + options.className + '-loading').css({top: top});

			var data = new FormData();
			data.append('reset', 1);

			$.ajax({
					url: options.url + '&page=' + options.page, 
					type: 'POST',
					data: data, 
					cache: false,
					dataType: 'json',
					processData: false, 
					contentType: false, 
					success: function(data, textStatus, jqXHR) {
						if(typeof(data.error) === 'undefined') {						
							$.each(data, function(key, value) {
								$('#' + options.listArea).html(data[key].listHtml);								
								if(typeof(setListCookie) == 'function') {
									setListCookie(options.page);
								}								
								hook('onListLoad', [options.page]);
							});
							if(typeof(timelinyResetCallBack) != 'undefined') timelinyResetCallBack();
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

		/**
		 * reset plugin.
		 * Usage: $('#el').timeliny('reset','url');
		 */
		function reset(url, lastPage, lastPage2, page) {
			
			if(!lastPage)	lastPage	= 0;
			if(!lastPage2)	lastPage2	= 0;
			if(!page)		page		= 1;

			options.page	= page;
			options.url		= url;

			if(lastPage) {
				options.lastPage = lastPage;				
				_reset();
			}
			else if(lastPage2) {				
				var data = new FormData();

				data.append('reset', 1);
				data.append('lastPage', 1);

				$.ajax({
						url: options.url,
						type: 'POST',
						data: data, 
						cache: false,
						dataType: 'json',
						processData: false, 
						contentType: false, 
						success: function(data, textStatus, jqXHR) {					
							if(typeof(data.error) === 'undefined') {						
								$.each(data, function(key, value) {
									options.lastPage = data[key].lastPage;
									if(typeof(timelinyResetCallBackSearch) != 'undefined') timelinyResetCallBackSearch(data[key].total);
									if(options.lastPage != '0') _reset();
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
			else _resetList();
		}


		/**
		 * Get/set options.
		 * Get usage: $('#el').timeliny('option', 'key');
		 * Set usage: $('#el').timeliny('option', 'key', value);
		 */
		function option (key, val) {
			if (val) {
				options[key] = val;
			} else {
				return options[key];
			}
		}

		/**
		 * Destroy plugin.
		 * Usage: $('#el').timeliny('destroy');
		 */
		function destroy() {
			// Iterate over each matching element.
			$el.each(function() {
				var el = this;
				var $el = $(this);

				// Destroy completely the element and remove event listeners
				$(window).off('resize.timeliny');
				$el.find('.' + options.className + '-timeblock:not(.inactive) .' + options.className + '-dot').off('click');
				$(document).off('mousemove.timeliny');
				$(document).off('mouseup.timeliny');
				$el.first().off('mousedown');
				$el.remove();
				hook('onDestroy');

				// Remove Plugin instance from the element.
				$el.removeData('plugin_timeliny');
			});
		}

		/**
		 * Callback hooks.
		 */
		function hook(hookName, args) {
			if (options[hookName] !== undefined) {
				// Call the user defined function.
				// Scope is set to the jQuery element we are operating on.
				options[hookName].apply(el, args);
			}
		}		

		// Initialize the plugin instance.
		_init();

		// Expose methods of Plugin we wish to be public.
		return {
			option: option,
			destroy: destroy,
			goToPage: goToPage,
			reset: reset
		};
	}

	/**
	 * Plugin definition.
	 */
	$.fn['timeliny'] = function(options) {
        //console.log(options);
		// If the first parameter is a string, treat this as a call to
		// a public method.
		if (typeof arguments[0] === 'string') {
			var methodName = arguments[0];
			var args = Array.prototype.slice.call(arguments, 1);
			var returnVal;
			this.each(function() {
				// Check that the element has a plugin instance, and that
				// the requested public method exists.
				if ($.data(this, 'plugin_timeliny') && typeof $.data(this, 'plugin_timeliny')[methodName] === 'function') {
					// Call the method of the Plugin instance, and Pass it
					// the supplied arguments.
					returnVal = $.data(this, 'plugin_timeliny')[methodName].apply(this, args);
				} else {
					throw new Error('Method ' +  methodName + ' does not exist on jQuery.timeliny');
				}
			});
			if (returnVal !== undefined){
				// If the method returned a value, return the value.
				return returnVal;
			} else {
				// Otherwise, returning 'this' preserves chainability.
				return this;
			}
			// If the first parameter is an object (options), or was omitted,
			// instantiate a new instance of the plugin.
		} else if (typeof options === "object" || !options) {
			return this.each(function() {
				// Only allow the plugin to be instantiated once.
				if (!$.data(this, 'plugin_timeliny')) {
					// Pass options to Plugin constructor, and store Plugin
					// instance in the elements jQuery data object.
					$.data(this, 'plugin_timeliny', new Plugin(this, options));
				}
			});
		}
	};

	// Default plugin options.
	// Options can be overwritten when initializing plugin, by
	// passing an object literal, or after initialization:
	// $('#el').timeliny('option', 'key', value);
	$.fn['timeliny'].defaults = {
		page: '1',
		block: '1',
		lastPage: '10',
		changePage: 0,
		blockLastPage: '',
		boundaries: '100',
		pageBlockSize: 60,		
		url: '',
		listReset: 0,
		listArea: 'listArea',
        className: 'timeliny',
		wrapper: '<div class="timeliny-wrapper"></div>',
		animationSpeed: 250,
		onInit: function() {},
		onDestroy: function() {},
		onListLoad: function(currPage) {},
		afterLoad: function(currPage) {},
		onLeave: function(currPage, nextPAge) {},
		afterChange: function(currPAge) {},
		afterResize: function() {}
	};

})( jQuery, window, document );
