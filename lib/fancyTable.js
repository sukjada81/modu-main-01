/*!
 * jQuery fancyTable plugin
 * https://github.com/myspace-nu
 *
 * Copyright 2018 Johan Johansson
 * Released under the MIT license
 */
(function($) {

	$.fn.fancyTable = function(options) {
		var settings = $.extend({
			inputStyle: "",
			inputPlaceholder: "Search...",
			pagination: false,
			paginationClass: "num",
			paginationClassActive: "selected",
			paginationClassDefault: "default",
			pagClosest: 3,
			perPage: 10,
			sortable: true,
			searchable: true,
			onInit: function(){ },
			onUpdate: function(){ },
		  	testing: false
		}, options);
		var instance = this;
		fancyTableObj = this; 		
		this.tableUpdate = function (elm) {
			elm.fancyTable.matches = 0;
			$(elm).find("tbody tr").each(function() {
				var n=0;
				var match = true;
				var globalMatch = false;
				$(this).find("td").each(function() {
					if(!settings.globalSearch && elm.fancyTable.searchArr[n] && !(new RegExp(elm.fancyTable.searchArr[n],"i").test($(this).html()))){
						match = false;
					} else if(settings.globalSearch && (!elm.fancyTable.search || (new RegExp(elm.fancyTable.search,"i").test($(this).html())))){
						globalMatch = true;
					}
					n++;
				});
				if((settings.globalSearch && globalMatch) || (!settings.globalSearch && match)){
					elm.fancyTable.matches++
					if(!settings.pagination || (elm.fancyTable.matches>(elm.fancyTable.perPage*(elm.fancyTable.page-1)) && elm.fancyTable.matches<=(elm.fancyTable.perPage*elm.fancyTable.page))){
						$(this).show();						
					} else {
						$(this).hide();
					}
				} else {
					$(this).hide();
				}
			});
			elm.fancyTable.pages = Math.ceil(elm.fancyTable.matches/elm.fancyTable.perPage);

			if(settings.pagination && elm.fancyTable.pages>1){
				var paginationElement = (elm.fancyTable.paginationElement) ? $(elm.fancyTable.paginationElement) : $(elm).find(".paging");
				paginationElement.empty();
				for(var n=1; n<=elm.fancyTable.pages; n++){
					if(n==1 || (n>(elm.fancyTable.page-(settings.pagClosest+1)) && n<(elm.fancyTable.page+(settings.pagClosest+1))) || n==elm.fancyTable.pages){
						var a = $("<a>",{
							html:n,
							"data-n": n,
							style:"margin:0.4em;  font-family: 'Montserrat', sans-serif; font-size:16px;  ",
							class:settings.paginationClass+" "+((n==elm.fancyTable.page)?settings.paginationClassActive:settings.paginationClassDefault)
						}).css("cursor","pointer").bind("click",function(){
							elm.fancyTable.page = $(this).data("n");
							instance.tableUpdate(elm);
						});
						if(n==elm.fancyTable.pages && elm.fancyTable.page<(elm.fancyTable.pages-settings.pagClosest-1)){
							paginationElement.append($("<span>...</span>"));
						}
						paginationElement.append(a);
						if(n==1 && elm.fancyTable.page>settings.pagClosest+2){
							paginationElement.append($("<span>...</span>"));
						}
					}
				}
			}
			settings.onUpdate.call(this,elm);
			if(typeof(deliveryConfReset)=='function') {
				deliveryConfReset();
			}
			else if(typeof(memberLevelReset)=='function') {
				memberLevelReset();
			}
		};
		this.tableSort = function (elm) {
			if(typeof elm.fancyTable.sortColumn !== "undefined" && elm.fancyTable.sortColumn < elm.fancyTable.nColumns){
				$(elm).find("thead th div.sortArrow").each(function(){
					$(this).remove();
				});
				var sortArrow = $("<div>",{"class":"sortArrow"}).css({"margin":"0.1em","display":"inline-block","width":0,"height":0,"border-left":"0.4em solid transparent","border-right":"0.4em solid transparent"});
				sortArrow.css(
					(elm.fancyTable.sortOrder>0) ?
					{"border-top":"0.4em solid #fff"} :
					{"border-bottom":"0.4em solid #fff"}
				);
				$(elm).find("thead th a").eq(elm.fancyTable.sortColumn).append(sortArrow);
				var rows = $(elm).find("tbody tr").toArray().sort(
					function(a, b) {
					
						var stra = $(a).find("td").eq(elm.fancyTable.sortColumn).html();
						if(stra.indexOf('input')>0) {
							if(stra.indexOf('checkbox')>0) {
								if(typeof($(a).find("td").eq(elm.fancyTable.sortColumn).find("input:checked").val())=='undefined') stra = 2;
								else stra = $(a).find("td").eq(elm.fancyTable.sortColumn).find("input:checked").val();
							}
							else stra = $(a).find("td").eq(elm.fancyTable.sortColumn).find("input").val();
						}

						var strb = $(b).find("td").eq(elm.fancyTable.sortColumn).html();
						if(strb.indexOf('input')>0) {
							if(strb.indexOf('checkbox')>0) {
								if(typeof($(b).find("td").eq(elm.fancyTable.sortColumn).find("input:checked").val())=='undefined') strb = 2;
								else strb = $(b).find("td").eq(elm.fancyTable.sortColumn).find("input:checked").val();								
							}
							else strb = $(b).find("td").eq(elm.fancyTable.sortColumn).find("input").val();
						}

						if(elm.fancyTable.sortAs[elm.fancyTable.sortColumn] == 'numeric'){
							return((elm.fancyTable.sortOrder>0) ? parseFloat(stra)-parseFloat(strb) : parseFloat(strb)-parseFloat(stra));
						} 
						else {
							return((stra<strb)?-elm.fancyTable.sortOrder:(stra>strb)?elm.fancyTable.sortOrder:0);
						}
					}
				);
				$(elm).find("tbody").empty().append(rows);
			}
		};

		this.each(function() {
			if($(this).prop("tagName")!=="TABLE"){
				console.warn("fancyTable: Element is not a table.");
				return true;
			}
			var elm = this;
			fancyElm = elm;
			elm.fancyTable = {
				nColumns: ($(elm).find("td").first().parent().find("td").length > 0) ? $(elm).find("td").first().parent().find("td").length : settings.dColumns  ,
				nRows : $(this).find("tbody tr").length,
				perPage : settings.perPage,
				page : 1,
				pages : 0,
				matches : 0,
				searchArr : [],
				search : "",
				sortColumn : settings.sortColumn,
				sortOrder : 1,
				sortAs:[], // undefined or numeric
				paginationElement : settings.paginationElement
			};
			if($(elm).find("tbody").length==0){
				var content = $(elm).html();
				$(elm).empty();
				$(elm).append("<tbody>").append($(content));
			}
			if($(elm).find("thead").length==0){
				$(elm).prepend($("<thead>"));
				// Maybe add generated headers at some point
				//var c=$(elm).find("tr").first().find("td").length;
				//for(var n=0; n<c; n++){
				//	$(elm).find("thead").append($("<th></th>"));
				//}
			}
			if(settings.sortable){
				var n=0;
				$(elm).find("thead th").each(function() {
					if($(this).attr('data-sortas')!='no'){
						elm.fancyTable.sortAs.push(($(this).data('sortas')=='numeric')?'numeric':'');
						var content = $(this).html();
						var a = $("<a>",{
							html:content,
							"data-n": n,
							class:""
						}).css("cursor","pointer").bind("click",function(){
							if(elm.fancyTable.sortColumn == $(this).data("n")){
								elm.fancyTable.sortOrder=-elm.fancyTable.sortOrder;
							} else {
								elm.fancyTable.sortOrder=1;
							}
							elm.fancyTable.sortColumn = $(this).data("n");
							instance.tableSort(elm);
							instance.tableUpdate(elm);
						});
						$(this).empty();
						$(this).append(a);
					}
					n++;
				});
			}
			if(settings.searchable){
				var searchHeader = $("<tr>");
				if(settings.globalSearch){
					var searchField = $("<input id='fancyInput' type='text' style='width:99%' placeholder='"+settings.inputPlaceholder+"'><p id='fancySearch' class='searchIcon' style='top:7px; right:1%'><i class='xi-search colorGray masterTooltip' title='검색'></i></p><p id='fancyReset' class='searchIcon' style='top:7px; right:3%; display:none;'><i class='xi-close colorGray masterTooltip' title='취소'></i></p>",{
						style:"width:100%;"+settings.inputStyle
					}).bind("keydown",function(e){
						if (e.keyCode == 13) {
							elm.fancyTable.search = $(this).val();
							instance.tableUpdate(elm);
							$('#fancyReset').show();
						}						
					}).bind("keyup",function(e){
						if($(this).val()=='') {
							elm.fancyTable.search = '';
							instance.tableUpdate(elm);
							$('#fancyReset').hide();
						}						
					});

					var th = $("<td>",{ style:"padding:2px; background:#fff;", position:"relative" }).attr("colspan", elm.fancyTable.nColumns);
					$(searchField).appendTo($(th));
					$(th).appendTo($(searchHeader));
				} 
				else {
					var n=0;
					$(elm).find("td").first().parent().find("td").each(function() {
						elm.fancyTable.searchArr.push("");
						var searchField = $("<input>",{
							"data-n": n,
							"placeholder": settings.inputPlaceholder,
							style:"width:100%;"+settings.inputStyle
						}).bind("change paste keyup",function(){
							elm.fancyTable.searchArr[$(this).data("n")] = $(this).val();
							instance.tableUpdate(elm);
						});
						var th = $("<th>",{ style:"padding:2px;"});
						$(searchField).appendTo($(th));
						$(th).appendTo($(searchHeader));
						n++;
					});
				}
				searchHeader.appendTo($(elm).find("thead"));				
				
				$(elm).find("tbody").prepend("<tr><td colspan='5' style='background:#fff;height:66px; border:0; display:none;' class='table-fixed-empty' /></tr>");

				$('#fancySearch').click(function(){					
					elm.fancyTable.search = $('#fancyInput').val();
					instance.tableUpdate(elm);
					$('#fancyReset').show();
				});

				$('#fancyReset').click(function(){
					$('#fancyInput').val('');
					elm.fancyTable.search = $('#fancyInput').val();
					instance.tableUpdate(elm);
					$('#fancyReset').hide();
				});
			}
			// Sort
			instance.tableSort(elm);
			if(settings.pagination && !settings.paginationElement){
				$(elm).find("tfoot").remove();
				$(elm).append($("<tfoot><tr></tr></tfoot>"));
				$(elm).find("tfoot tr").append($("<td class='paging'></td>",{ }).attr("colspan",elm.fancyTable.nColumns));
			}
			instance.tableUpdate(elm);
			settings.onInit.call(this,elm);
		});
		return this;
	};	

}(jQuery));