<?php
/******************************************************************
* =======================================================
* 클래스: 쇼핑몰을 위한 페이징 클래스
*
* 제 작: gubok kim (email : http://funcher.kr,  homepage : http://funcher.kr)
*
* 제작일: 2021.01.01
*
* =======================================================
******************************************************************/


class listPaging {
	
	var $table_name;					// 디비 테이블명	
	var $search_variable;				// 검색에 사용되는 변수
	var $list_variable;					// 리스트 출력에 사용되는 필드명
	var $list_show_variable;			// 리스트 출력에 사용되는 필드명(노출항목 설정)	
	var $field_where		= '';		// 테이블에 없는 field 검색시 
	var $addstring;						// 링크 추가 파라미터
	var $pagestring;                    // 링크 페이징용 추가 파라미터 
	var $default_where;					// 기본 검색 조건 쿼리(atotal_record 에 사용)
	var $default_query;					// atotal_record 기본쿼리
	var $where;							// 검색 조건 쿼리	
	var $where2;						// 검색 조건 쿼리2 (or)
	var $swhere;						// JOIN 사용시 검색 조건 서브쿼리
	var $swhere2;						// JOIN 사용시 검색 조건 서브쿼리2 (or)
	var $squery;						// JOIN 사용시 쿼리정의 함수명
	var $cquery;						// 쿼스텀 쿼리정의 함수명
	var $re_uid				= 0;		// 정렬 re_uid ASC 사용시 1
	var $check_where		= 0;		// 검색조건 사용 유무 0 : 검색조건 없음, 1 : 기본 field, keyword 만검색, 2 : 검색조건 있음 
	var $is_board			= 0;		// 게시판일 경우 $tplBo 사용;
			
	var $page				= 1;		// 현재페이지
	var $page_record_num	= '10';     // 한페이지에 보여줄 레코드 수
	var $page_link_num		= '10';     // 한페이지에 보여줄 페이지 수	
	
	var $atotal_record;					// 총 레코드 수
	var $total_record;					// 총 레코드 수(검색)
	var $total_page;					// 총 페이지 수
	var $total_block;					// 총 블럭 수
	var $block				= 1;		// 현재블럭
	var $page_start;					// 화면에 뿌려질 시작 페이지
	var $page_end;						// 화면에 뿌려질 마지막 페이지
	var $prev_page;						// 이전페이지
	var $next_page;						// 다음페이지
	var $url;							// 링크주소	
	var $type;							// 타입 (0 : 일반, 1 : 노출항목 설정 ( $filed_able_arr에 정의 )

/*
####################################################################
     ::: 생성자 :::               
####################################################################
*/

	function __construct($table_name, $page_record_num='', $page_link_num='') {
		
		$this->search_variable							= array();
		$this->list_variable							= array();
		$this->list_show_variable						= array();
		$this->table_name								= $table_name;
		if($page_record_num)	$this->page_record_num	= $page_record_num;
		if($page_link_num)		$this->page_link_num	= $page_link_num;
		$this->url										= $_SERVER['PHP_SELF'];
		$this->defaultParam								= "";
		$this->type										= 0;
			 
	}


/*
####################################################################
     ::: 변수 & addstring & where 정의 :::          
####################################################################
*/

	function make_string() {

		$value = "";

		foreach ($this->search_variable as $k => $v) {
			if(preg_match("/keyword/i",$k)) $value = isset($_POST[$k]) ?  urldecode($_POST[$k]) :  ((isset($_GET[$k])) ?  urldecode($_GET[$k]) : '');
			else $value = isset($_POST[$k]) ? $_POST[$k] : ((isset($_GET[$k])) ? $_GET[$k] : '');
			
			if(gettype($value) == 'array') $value = join("|", $value);

			if($v > 0 && strlen($value) > 0) $this->addstring	.= "&{$k}={$value}";

			if($v == 1 && strlen($value) > 0) {
				if($k == 'id' && (defined('__MYPAGE__') || defined('__MYPAGE2__'))) {
					$this->where	.= "&& ( a.id = '".add_escape_re_string($value)."' || a.o_id = '".add_escape_re_string($value)."' )";
				}
				else if($k == 'vendor') {
					if($value == 'X')	$this->where	.= "&& a.{$k} = '' ";
					else				$this->where	.= "&& a.{$k} = '{$value}' ";
				}
				else $this->where	.= "&& a.{$k} = '{$value}' ";
			}			
			else if($v == 2 && strlen($value) > 0) {				
				$this->where	.= $GLOBALS[$k.'_where']($value);
			}
			else if($v == 21 && strlen($value) > 0) {				
				$this->where2	.= $GLOBALS[$k.'_where']($value);
			}
			else if($v == 3 && strlen($value) > 0) {				
				$this->swhere	.= $GLOBALS[$k.'_where']($value);
			}
			else if($v == 31 && strlen($value) > 0) {				
				$this->swhere2	.= $GLOBALS[$k.'_where']($value);
			}
			else if($v == 4 && strlen($value) > 0) {
				if($value == 1) $this->where	.= "&& a.{$k} = '' ";
				else			$this->where	.= "&& a.{$k} != '' ";
			}		

			$GLOBALS[$k] = $value;
		}

		if($this->where2) {
			$where2 = substr($this->where2, 3);
			$this->where .= "&& ({$where2})";
		}

		if($this->swhere2) {
			$where2 = substr($this->swhere2, 3);
			$this->swhere .= "&& ({$where2})";
		}

		if($this->where || $this->swhere) $this->check_where = 2;
		if($this->default_where) $this->where .= " && a.{$this->default_where} ";

		for($i=1; $i<5; $i++) {
			if($i == 1) $i2 = '';
			else $i2 = $i;

			if(!isset($GLOBALS['field'.$i2])) continue;

			if($GLOBALS['field'.$i2] && strlen($GLOBALS['keyword'.$i2])) {
				$GLOBALS['keyword'.$i2] = trim($GLOBALS['keyword'.$i2]);
				$this->addstring	.= "&field{$i2}=".$GLOBALS['field'.$i2]."&keyword{$i2}=".urlencode($GLOBALS['keyword'.$i2]);
				if($GLOBALS['field'.$i2] == 'multi' || $GLOBALS['field'.$i2] == 'multi2' || $GLOBALS['field'.$i2] == 'multi3') {
					$where = array();

					if(isset($GLOBALS['multi_array'])) {
						foreach($GLOBALS['multi_array'] as $k => $v) {
							if($v == 'name') $where[] = "INSTR(REPLACE(a.{$v}, ' ', ''), '".str_replace(' ' , '', $GLOBALS['keyword'.$i2])."')";
							else if($v == 'id') $where[] = "a.{$v} = '".add_escape_re_string($GLOBALS['keyword'.$i2])."'";
							else if($v == 'keyword') $where[] = "INSTR(a.{$v}, ',".$GLOBALS['keyword'.$i2].",')";
							else $where[] = "INSTR(a.{$v}, '".$GLOBALS['keyword'.$i2]."')";
						}
					}
					
					if($GLOBALS['field'.$i2] == 'multi2') {
						$where2 = array();
						foreach($GLOBALS['multi2_array'] as $k => $v) {
							if($v == 'vendor') $where2[] = "{$v} = '".add_escape_re_string($GLOBALS['keyword'.$i2])."'";
							else $where2[] = "INSTR({$v}, '".$GLOBALS['keyword'.$i2]."')";
						}

						$where[] = "a.order_num IN ( SELECT order_num FROM mallRN_order_goods WHERE ".JOIN(" || ",$where2)." )";
						unset($where2);
					}

					if($GLOBALS['field'.$i2] == 'multi3') {
						$where3 = array();
						foreach($GLOBALS['multi3_array'] as $k => $v) {
							if($v == 'id') $where3[] = "{$v} = '".add_escape_re_string($GLOBALS['keyword'.$i2])."'";
							else $where3[] = "INSTR({$v}, '".$GLOBALS['keyword'.$i2]."')";
						}
						$where[] = "a.order_num IN ( SELECT order_num FROM mallRN_order_info WHERE ".JOIN(" || ",$where3)." )";
						unset($where3);
					}

					$this->where	.= "&& (".JOIN(" || ",$where).") ";					
				}
				else if($this->field_where && in_array($GLOBALS['field'.$i2], $this->field_where)) {
					$this->where	.= $GLOBALS[$GLOBALS['field'.$i2].'FieldWhere']($GLOBALS['keyword'.$i2]);
				}
				else if($GLOBALS['field'.$i2] == 'name') $this->where	.= "&& INSTR(REPLACE(a.".$GLOBALS['field'.$i2].", ' ', ''), '".str_replace(' ' , '', $GLOBALS['keyword'.$i2])."') ";
				else if($GLOBALS['field'.$i2] == 'id') $this->where	.= "&& a.id	= '".add_escape_re_string($GLOBALS['keyword'.$i2])."'";
				else {
					if($this->is_board == 1) {
						$bo_field = "";
						if($GLOBALS['field'.$i2] == 'S')		$bo_field = "subject";
						else if($GLOBALS['field'.$i2] == 'C')	$bo_field = "content";
						else if($GLOBALS['field'.$i2] == 'N')	$bo_field = "name";
						else if($GLOBALS['field'.$i2] == 'I')	$bo_field = "id";
						else if($GLOBALS['field'.$i2] == 'SC') {
							$this->where	.= "&& ( INSTR(a.subject, '".$GLOBALS['keyword'.$i2]."') || INSTR(a.content, '".$GLOBALS['keyword'.$i2]."') ) ";
						}

						if($bo_field) {
							if($bo_field == 'id')	$this->where	.= "&& a.{$bo_field} = '".$GLOBALS['keyword'.$i2]."' ";	
							else					$this->where	.= "&& INSTR(a.{$bo_field}, '".$GLOBALS['keyword'.$i2]."') ";
						}
						unset($bo_field);
					}
					else {
						$this->where	.= "&& INSTR(a.".$GLOBALS['field'.$i2].", '".$GLOBALS['keyword'.$i2]."') ";
					}
				}

				if($i == 1) $this->check_where = 1;
				else $this->check_where = 2;
			}
		}

		$GLOBALS['searchstring'] = $this->addstring;
		
		if($this->is_board == 1) {
			$GLOBALS['sort']		= "notice ASC, c.idx ASC, c.main ASC, c.sub ASC";
		}
		else {
			if(!$GLOBALS['sort']) $GLOBALS['sort']	= "uid DESC";
			$this->addstring .= "&sort={$GLOBALS['sort']}";	
		}

		if($GLOBALS['limit']) {	
			if(!is_numeric($GLOBALS['limit'])) $GLOBALS['limit'] = $this->page_record_num;
			$this->page_record_num = $GLOBALS['limit'];
			$this->addstring .= "&limit={$GLOBALS['limit']}";				
		}
		else $GLOBALS['limit'] = $this->page_record_num;

		$this->pagestring	= $this->addstring;

		if(!$GLOBALS['page']) {
			$GLOBALS['page']	= 1;
			$this->page			= 1;
		}
		else {
			$this->page			= $GLOBALS['page'];
			$this->addstring	.= "&page={$this->page}";
		}

		$GLOBALS['addstring'] = $this->addstring;		
	}


/*
####################################################################
     :::  전체 레코드 수 :::          
####################################################################
*/

	function total_all_record() {
		global $mysql;
		
		if(!$this->atotal_record) {	
			if($this->default_query) {
				$sql = $this->default_query;
			}
			else {
				$where = "";
				if($this->default_where) $where = "WHERE {$this->default_where}";
				$sql = "SELECT COUNT(*) FROM {$this->table_name} {$where}";
			}
			$this->atotal_record = $mysql->get_one($sql);
		}
		
		if($this->where || $this->swhere) {
			$this->total_record();
		}
		else {
			$this->total_record = $this->atotal_record;
			$this->make_page();
			$GLOBALS['TOTAL'] = $this->atotal_record;
		}

		$GLOBALS['ATOTAL'] = $this->atotal_record;

	}


/*
####################################################################
     :::  검색결과 레코드 수 :::          
####################################################################
*/

	function total_record() {
		global $mysql;
		
		if(!$this->total_record) {
			if($this->swhere) {				
				$sql = $GLOBALS[$this->squery."Total"]($this->swhere, $this->where);
			}
			else {			
				$where = "";
				if($this->where) $where = "WHERE ".substr($this->where, 3);

				if($this->cquery) $sql = $GLOBALS[$this->cquery."Total"]($this->where);
				else $sql = "SELECT COUNT(*) FROM {$this->table_name} a {$where}";			
			}		
			//echo $sql;	
			$this->total_record = $mysql->get_one($sql);
		}

		$GLOBALS['TOTAL'] = $this->total_record;

		$this->make_page();
		
	}

 
/*
####################################################################
     :::  :::          
####################################################################
*/

	function print_record() {
		global $mysql, $tpl, $tplBo;

		if(!$this->total_record) $this->total_record();
		
		if($this->atotal_record) $this->pagestring = $this->pagestring."&atotal_record={$this->atotal_record}&total_record={$this->total_record}";
		else $this->pagestring = $this->pagestring."&total_record={$this->total_record}";
		$GLOBALS['pagestring'] = $this->pagestring;

		$start_record	= $this->page_record_num * ($this->page - 1);
		$start_num		= $this->total_record - (($this->page - 1) * $this->page_record_num);
		
		if($this->re_uid == 1) {			
			if(!$this->where) {
				if($GLOBALS['sort'] == 'uid DESC') $GLOBALS['sort'] = 're_uid ASC';
				else if($GLOBALS['sort'] == 'uid ASC') $GLOBALS['sort'] = 're_uid DESC';
			}
		}
		
		if($this->swhere) {
			$sql = $GLOBALS[$this->squery."Print"]($this->swhere, $this->where, $start_record, $this->page_record_num);
		}
		else {			
			$where = "";
			if($this->where) $where = "WHERE ".substr($this->where, 3);

			if($this->cquery) {
				$sql = $GLOBALS[$this->cquery."Print"]($this->where, $start_record, $this->page_record_num);
			}
			else {
				if(!$GLOBALS['sort'] || $GLOBALS['sort'] == 'null') $GLOBALS['sort'] = "uid DESC";
				$sql = "SELECT c.* FROM ( SELECT a.uid FROM {$this->table_name} a {$where} ) b JOIN {$this->table_name} c ON b.uid = c.uid ORDER BY c.{$GLOBALS['sort']} LIMIT {$start_record},{$this->page_record_num}";
			}
		}				
		$mysql->query($sql);

		$GLOBALS['NUM_ASC'] = $start_record + 1;
		while($row = $mysql->fetch_array()) {
			
			$GLOBALS['NUM']		= $start_num;
			$GLOBALS['UID']		= @$row['uid'];

			foreach ($this->list_variable as $k => $v) {								

				if(!isset($row[$k])) $row[$k] = " ";
				
				if(strlen($row[$k]) != 0) {
					if($v=='date') {
						if($row[$k])	$GLOBALS[strtoupper($k)] = date("Y-m-d", $row[$k]);
						else			$GLOBALS[strtoupper($k)] = '-';
					}
					else if($v=='datetime') {
						if($row[$k])	$GLOBALS[strtoupper($k)] = date("Y-m-d H:i:s", $row[$k]);
						else			$GLOBALS[strtoupper($k)] = '-'; 
					}
					else if($v=='datetime2') {
						if($row[$k])	$GLOBALS[strtoupper($k)] = date("m-d H:i", $row[$k]);
						else			$GLOBALS[strtoupper($k)] = '-'; 
					}
					else if($v=='number') $GLOBALS[strtoupper($k)]	= number_format($row[$k], CONF_FLOAT_CNT);
					else if($v=='float') $GLOBALS[strtoupper($k)]	= number_format($row[$k],2);
					else if($v=='goods_price') $GLOBALS[strtoupper($k)]	= getGoodsPrice($row[$k], $row['uid'], $row['exhibition']);
					else if($v=='cate') $GLOBALS[strtoupper($k)]	= getCateAllName($row[$k], 1);
					else if($v=='cate_no') $GLOBALS[strtoupper($k)] = getCateAllName($row[$k], 0);
					else if($v=='icon') $GLOBALS[strtoupper($k)]	= getIcon($row[$k]);
					else if($v=='iconup') $GLOBALS[strtoupper($k)]	= getIconUp($row[$k]);
					else if($v=='link') $GLOBALS[strtoupper($k)]	= returnCheckLink($row[$k]);
					else if($v=='area') {
						$tmps = explode(" ", $row[$k]);
						$GLOBALS[strtoupper($k)]	= str_replace(array('광역시','특별시','특별자치도'), '', $tmps[0]);
					}
					else if($v=='array') {
						//if($row[$k] == 0) continue;
						@$GLOBALS[strtoupper($k)]	= $GLOBALS[$k.'_array'][$row[$k]];
					}
					else if($v == 'vendor' || $v == 'vendor_isset') { 
						if(isset($GLOBALS[$k.'_array'][$row[$k]]))	$GLOBALS[strtoupper($k)] = $GLOBALS[$k.'_array'][$row[$k]];
						else										$GLOBALS[strtoupper($k)] = "삭제된 판매사";	
						$GLOBALS[strtoupper($k).'_ID']	= stripslashes($row[$k]);
						if($v == 'vendor_isset') $tpl->parse("is_vendor");	
					}
					else if(preg_match("/function/i",$v)) {
						$tmps = explode("|",$v);
						if(count($tmps)==13) $GLOBALS[strtoupper($k)] = $GLOBALS[$k.'_function']($row[$k],$row[$tmps[1]],$row[$tmps[2]],$row[$tmps[3]],$row[$tmps[4]],$row[$tmps[5]],$row[$tmps[6]],$row[$tmps[7]],$row[$tmps[8]],$row[$tmps[9]],$row[$tmps[10]],$row[$tmps[11]],$row[$tmps[12]]);
						else if(count($tmps)==12) $GLOBALS[strtoupper($k)] = $GLOBALS[$k.'_function']($row[$k],$row[$tmps[1]],$row[$tmps[2]],$row[$tmps[3]],$row[$tmps[4]],$row[$tmps[5]],$row[$tmps[6]],$row[$tmps[7]],$row[$tmps[8]],$row[$tmps[9]],$row[$tmps[10]],$row[$tmps[11]]);
						else if(count($tmps)==11) $GLOBALS[strtoupper($k)] = $GLOBALS[$k.'_function']($row[$k],$row[$tmps[1]],$row[$tmps[2]],$row[$tmps[3]],$row[$tmps[4]],$row[$tmps[5]],$row[$tmps[6]],$row[$tmps[7]],$row[$tmps[8]],$row[$tmps[9]],$row[$tmps[10]]);
						else if(count($tmps)==10) $GLOBALS[strtoupper($k)] = $GLOBALS[$k.'_function']($row[$k],$row[$tmps[1]],$row[$tmps[2]],$row[$tmps[3]],$row[$tmps[4]],$row[$tmps[5]],$row[$tmps[6]],$row[$tmps[7]],$row[$tmps[8]],$row[$tmps[9]]);
						else if(count($tmps)==9) $GLOBALS[strtoupper($k)] = $GLOBALS[$k.'_function']($row[$k],$row[$tmps[1]],$row[$tmps[2]],$row[$tmps[3]],$row[$tmps[4]],$row[$tmps[5]],$row[$tmps[6]],$row[$tmps[7]],$row[$tmps[8]]);
						else if(count($tmps)==8) $GLOBALS[strtoupper($k)] = $GLOBALS[$k.'_function']($row[$k],$row[$tmps[1]],$row[$tmps[2]],$row[$tmps[3]],$row[$tmps[4]],$row[$tmps[5]],$row[$tmps[6]],$row[$tmps[7]]);
						else if(count($tmps)==7) $GLOBALS[strtoupper($k)] = $GLOBALS[$k.'_function']($row[$k],$row[$tmps[1]],$row[$tmps[2]],$row[$tmps[3]],$row[$tmps[4]],$row[$tmps[5]],$row[$tmps[6]]);
						else if(count($tmps)==6) $GLOBALS[strtoupper($k)] = $GLOBALS[$k.'_function']($row[$k],$row[$tmps[1]],$row[$tmps[2]],$row[$tmps[3]],$row[$tmps[4]],$row[$tmps[5]]);
						else if(count($tmps)==5) $GLOBALS[strtoupper($k)] = $GLOBALS[$k.'_function']($row[$k],$row[$tmps[1]],$row[$tmps[2]],$row[$tmps[3]],$row[$tmps[4]], '');
						else if(count($tmps)==4) $GLOBALS[strtoupper($k)] = $GLOBALS[$k.'_function']($row[$k],$row[$tmps[1]],$row[$tmps[2]],$row[$tmps[3]], '', '');
						else if(count($tmps)==3) $GLOBALS[strtoupper($k)] = $GLOBALS[$k.'_function']($row[$k],$row[$tmps[1]],$row[$tmps[2]], '', '', '');
						else if(count($tmps)==2) $GLOBALS[strtoupper($k)] = $GLOBALS[$k.'_function']($row[$k],$row[$tmps[1]], '', '', '', '');
						else $GLOBALS[strtoupper($k)] = $GLOBALS[$k.'_function']($row[$k], '', '', '', '', '');
					}
					else $GLOBALS[strtoupper($k)] = stripslashes($row[$k]);
				}
				else {
					$GLOBALS[strtoupper($k)] = '';
					if($v == 'vendor') $GLOBALS[strtoupper($k)] = "본사";
					if($v == 'vendor' || $v == 'vendor_isset') $GLOBALS[strtoupper($k).'_ID'] = "";
					else if($k == 'goods_order' || $v == 'function') $GLOBALS[strtoupper($k)] = "-";
					else if($k == 'answer' && $v == 'function') {
						$GLOBALS[strtoupper($k)] = $GLOBALS[$k.'_function']($row[$k], '', '', '', '', '');
					}
				}
			}			
			unset($tmps);
			
			if($this->type == 1) {
				foreach ($this->list_show_variable as $k => $v) {
					$tpl->parse("is_list_".$v);	
					$tpl->parse("loop_list_show");
				}
			}

			if($this->is_board == 1)	$tplBo->parse("loop_list");
			else						$tpl->parse("loop_list");

			$start_num--;
			$GLOBALS['NUM_ASC']++;

		}
	}

/*
####################################################################
     :::  페이징 정보 설정 :::          
####################################################################
*/

	function make_page() {

		$this->total_page = ceil($this->total_record / $this->page_record_num);				// 전체페이지
		$GLOBALS['total_page'] = $this->total_page;
		$this->total_block = ceil($this->total_page / $this->page_link_num);				// 전체 블럭
		$this->block = ceil($this->page / $this->page_link_num);							// 현재 블럭 
		if($this->page > $this->total_page) $this->page = $this->total_page;				
		$this->page_end = ceil($this->block * $this->page_link_num);						// 마지막 페이지
		$this->page_start = ($this->page_end - $this->page_link_num) + 1;					// 시작 페이지				
		$this->prev_page = ($this->block*$this->page_link_num)-$this->page_link_num;		// 이전 페이지

	}

	
/*
####################################################################
     ::: 페이징 출력 :::          
     페이징을 출력한다. 
####################################################################
*/

	function print_page($type="", $separator="", $img_path="") {
		
		$paging = "<div class='pageList'>";

		if($this->block > 1) {		

			switch($type) {
				default :
					$paging .= "<a href='{$this->url}?{$this->defaultParam}{$this->pagestring}&page={$this->prev_page}' class='prevPage masterTooltip' title='이전 목록'><i class='xi-angle-left-thin'></i></a>";
					$paging .= "<a href='{$this->url}?{$this->defaultParam}{$this->pagestring}&page=1' class='number masterTooltip' title='첫 페이지'>1</a>";
					$paging .= "<span class='jumjum'><i class='xi-ellipsis-h'></i></span>";
				break;
			}					

		}
		else {

			switch($type) {
				default :
					$paging .= "<span class='prevPage'><i class='xi-angle-left-thin'></i></span>";	
				break;
			}
			
		}

		for($i = $this->page_start; $i <= $this->page_end; $i++) {
			
			if($i != $this->page_start) $pseparator = $separator;
			
			if($this->page == $i) { 				
				switch($type) {
					default :
						$paging .= "<span class='selected'>{$i}</span>";
					break;
				}
			} 
			else {
				switch($type) {					
					default :
						$paging .= "<a href='{$this->url}?{$this->defaultParam}{$this->pagestring}&page={$i}' class='number'>{$i}</a>";
					break;
				}
			}
				
			$this->next_page = $i + 1;			
			if($this->next_page == $this->total_page + 1) {
				break;
			}
		}

		if($this->block < $this->total_block) {
			switch($type) {
				default :
					$paging .= "<span class='jumjum'><i class='xi-ellipsis-h'></i></span>";	
					$paging .= "<a href='{$this->url}?{$this->defaultParam}{$this->pagestring}&page={$this->total_page}' class='number masterTooltip' title='마지막 페이지'>{$this->total_page}</a>";
					$paging .= "<a href='{$this->url}?{$this->defaultParam}{$this->pagestring}&page={$this->next_page}' class='nextPage masterTooltip' title='다음 목록'><i class='xi-angle-right-thin'></i></a>";
				break;
			}			
		}
		else {

			switch($type) {
				default :
					$paging .= "<span class='nextPage'><i class='xi-angle-right-thin'></i></span>";	
				break;
			}
			
		}

		$paging .= '</div>';

		return $paging;
	}


/*
####################################################################
     ::: 페이징 close :::          
     class의 사용한 메모리를 지운다
####################################################################
*/
	
	function close() {
	
		unset($this->table_name, $this->search_variable, $this->list_variable, $this->list_show_variable, $this->field_where, $this->addstring, $this->pagestring, $this->default_where, $this->where, $this->swhere, $this->squery, $this->cquery, $this->url);

    }


}  // End Class

?>
