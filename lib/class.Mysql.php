<?php

/******************************************************************
 * =======================================================
 * 클래스: DB프로그래밍을 위한 클래스
 *
 * 제 작: jinoos Lee (jinoos@korea.com)
 *
 * 수 정: gubok kim

 * 제작일: 2002.10.16
 *
 * 수정일: 2019.03.30
 *
 * =======================================================
 ******************************************************************/


class mysqlClass {

    var $dbHost;		//디비 서버
    var $dbUser;        //디비 유저
    var $dbPass;        //디비 패스워드
    var $dbName;        //디비 네임
    var $debug;         //디버그
    var $con;           // Connection Resource
    var $res;           // Result Set Resource
    var $res2;          // Result Set Resource
    var $res3;          // Result Set Resource
    var $res4;          // Result Set Resource
    var $query;         // Last use query
    var $affRows;       // affected row
    var $lockTable;     // 락이 걸린 테이블 배열
    var $insert_id;		// insert uid
    var $msgType;		// 에러메세지 타입(기본 : 일반페이지, 1 : 히든프레임, 2 : ajax)



    /*
    ####################################################################
         ::: 생성자 :::
         새롭게 인자를 받지 않으면 Conf에 저장되어 있는 정보를 가지고 접속한다.
         기본적으로 생성만 해놓고.. 대기메모리에 올린다.
    ####################################################################
    */

    function __construct($host = "", $user = "", $pass = "", $name = "", $debug = "") {
        $this->lockTable = 0;

        if($host)   $this->dbHost = $host;
        else        $this->dbHost = MYSQL_HOST;
        if($user)   $this->dbUser = $user;
        else        $this->dbUser = MYSQL_USER;
        if($pass)   $this->dbPass = $pass;
        else        $this->dbPass = MYSQL_PASSWD;
        if($name)   $this->dbName = $name;
        else        $this->dbName = MYSQL_DB;
        if($debug)  $this->debug  = $debug;
        else        $this->debug  = MYSQL_DEBUG;

    }



    /*
    ####################################################################
         ::: 환경 재세팅 :::
         새롭게 인자를 받지 않으면 Conf에 저장되어 있는 정보를 가지고 접속한다.
         클라스내에서 다른 환경으로 세팅
    ####################################################################
    */


    function setConf($host = "", $user = "", $pass = "", $name = "", $debug = "") {

        if($host)   $this->dbHost = $host;
        else        $this->dbHost = MYSQL_HOST;
        if($user)   $this->dbUser = $user;
        else        $this->dbUser = MYSQL_USER;
        if($pass)   $this->dbPass = $pass;
        else        $this->dbPass = MYSQL_PASSWD;
        if($name)   $this->dbName = $name;
        else        $this->dbName = MYSQL_DB;
        if($debug)  $this->debug  = $debug;
        else        $this->debug  = MYSQL_DEBUG;

    }

    /*
    ####################################################################
         ::: 디비 연결 :::
         기존 연결이 존재하면 재 연결 하지 않는다.
    ####################################################################
    */

    function connect() {
        if(is_resource($this->con)) {
            if(@mysqli_ping($this->con)!=1) {
                $this->con = mysqli_connect($this->dbHost, $this->dbUser, $this->dbPass) or $this->errorMsg("con");
                mysqli_select_db($this->con,$this->dbName) or $this->errorMsg("name");
                mysqli_query($this->con,"set names utf8");
                mysqli_query($this->con,"set session sql_mode='ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
            }
        }
        else {
            $this->con = mysqli_connect($this->dbHost, $this->dbUser, $this->dbPass) or $this->errorMsg("con");
            mysqli_select_db($this->con,$this->dbName) or $this->errorMsg("name");
            mysqli_query($this->con,"set names utf8");
            mysqli_query($this->con,"set session sql_mode='ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
        }
    }


    /*
    ####################################################################
         ::: 디비 선택 :::
         새롭게 인자를 받지 않으면 Conf에 저장되어 있는 디비를 선택한다.
    ####################################################################
    */

    function select_db($name = "") {

        $this->connect();
        if(!$name) $name = $this->dbName;
        mysqli_select_db($this->con,$this->dbName) or $this->errorMsg("name");

    }



    /*
    ####################################################################
         ::: 현재 디비명 리턴 :::
    ####################################################################
    */

    function now_db() {

        $this->connect();
        $this->query = "select database()";
        $result      = mysqli_query($this->con,$this->query);
        $row         = mysqli_fetch_row($result);
        mysqli_free_result($result);
        return $row[0];

    }


    /*
    ####################################################################
         ::: 쿼리 실행 :::
         쿼리를 실행하고 result 리소스를 객체에 저장한다.
         후에 fetch를 할때 resultSet 을 사용한다.
    ####################################################################
    */

    function query($query, $ck1="", $ck2="") {

        $this->connect();
        $this->query   = preg_replace('/(?<!`)\b(option|status|key|value)\b(?!`)/i', '`$1`', $query);

        $this->res     = mysqli_query($this->con,$this->query) or $this->errorMsg("que","", $ck1, $ck2);

        $this->affRows		= mysqli_affected_rows($this->con);
        $this->insert_id	= mysqli_insert_id($this->con);
    }

    function query2($query) {

        if(isset($GLOBALS['CK_CP_DB_ERROR'])) {
            if($GLOBALS['bSucc'] == 'false') return;
        }

        $this->connect();
        $this->query   = preg_replace('/(?<!`)\b(option|status|key|value)\b(?!`)/i', '`$1`', $query);
        $this->res2 = mysqli_query($this->con,$this->query) or $this->errorMsg("que");

    }

    function query3($query) {

        if(isset($GLOBALS['CK_CP_DB_ERROR'])) {
            if($GLOBALS['bSucc'] == 'false') return;
        }

        $this->connect();
        $this->query   = preg_replace('/(?<!`)\b(option|status|key|value)\b(?!`)/i', '`$1`', $query);
        $this->res3 = mysqli_query($this->con,$this->query) or $this->errorMsg("que");

    }

    function query4($query) {

        $this->connect();
        $this->query   = preg_replace('/(?<!`)\b(option|status|key|value)\b(?!`)/i', '`$1`', $query);
        $this->res4 = mysqli_query($this->con,$this->query) or $this->errorMsg("que");

    }

    /*
    ####################################################################
         ::: 쿼리 결과 :::
         mysql_result 함수와 유사
         row 존재, column 없음 : 해당 Row 를 Array로 Fetch해서 Array로 넘김
         row 없음, column 존재 : column의 내용을 모든 Row에서 뽑아 Array로 넘김
         row 존재, column 존재 : 해당 row의 해당 column의 내용만 뽑아 String로 넘김
         row 없음, column 없음 : 이중배열로 array[row][column]으로 넘김.
    ####################################################################
    */

    function result($row="", $column ="") {

        if(!$this->res) {
            $this->errorMsg("res");
            return false;
        }

        if($row >= $this->affRows) return false;

        $num = mysqli_num_rows($this->res);

        if(strlen($row)==0 && strlen($column)==0){

            for($i=0;$i<$num;$i++) {
                mysqli_data_seek($this->res, $i);
                $return_var[$i] = mysqli_fetch_assoc($this->res);
            }
            @mysqli_free_result($this->res);

            return $return_var;

        }
        else if(strlen($row)!=0 && strlen($column)==0){

            mysqli_data_seek($this->res, $row);
            $return_var = mysqli_fetch_assoc($this->res);
            return $return_var;

        }
        else if(strlen($row)==0 && strlen($column)!=0){

            for($i=0;$i<$num;$i++){
                $return_var[$i] = @mysqli_result($this->res, $i, $column);
            }
            return $return_var;

        }
        else{
            return @mysqli_result($result, $row, $column);
        }
    }


    /*
    ####################################################################
         :::  결과 Fetch 함수 모음:::
         mysql_fetch 와 유사
    ####################################################################
    */

    function fetch() {
        if(!$this->res) return false; return mysqli_fetch_assoc($this->res);
    }

    function fetch_assoc() {
        if(!$this->res) return false; return mysqli_fetch_assoc($this->res);
    }

    function fetch_row() {
        if(!$this->res) return false; return mysqli_fetch_row($this->res);
    }

    function fetch_array($no="") {
        if(!$no) {
            if(!$this->res) return false; return mysqli_fetch_array($this->res);
        }
        else if($no==2) {
            if(!$this->res2) return false; return mysqli_fetch_array($this->res2);
        }
        else if($no==3) {
            if(!$this->res3) return false; return mysqli_fetch_array($this->res3);
        }
        else if($no==4) {
            if(!$this->res4) return false; return mysqli_fetch_array($this->res4);
        }
    }

    function fetch_object($no="") {
        if(!$no) {
            if(!$this->res) return false; return mysqli_fetch_object($this->res);
        }
        else if($no==2) {
            if(!$this->res2) return false; return mysqli_fetch_object($this->res2);
        }
        else if($no==3) {
            if(!$this->res3) return false; return mysqli_fetch_object($this->res3);
        }
        else if($no==3) {
            if(!$this->res4) return false; return mysqli_fetch_object($this->res4);
        }
    }


    /*
    ####################################################################
         :::  쿼리 결과 row수 :::
         마지막 쿼리로 영향을 받은 row의 수를 리턴
    ####################################################################
    */

    function affected_rows() {

        return $this->affRows;

    }


    /*
    ####################################################################
         :::  쿼리 + 결과  :::
         결과 값 row가 1개인 경우 사용
         칼럼명을 Key로 갖는 배열로 바로 뽑아옴.
         사용법 : $data = $mysql->one_row($query);
    ####################################################################
    */

    function one_row($query) {

        $this->connect();
        $result = mysqli_query($this->con,$query) or $this->errorMsg("que2", $query);
        if(mysqli_num_rows($result) == 0) return false;
        $row    = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        return($row);

    }



    /*
    ####################################################################
         :::  쿼리 + 결과  :::
         결과 값 row가 1개, colomn이 1개인 경우
         사용법 : $data = $mysql->get_one($query);
    ####################################################################
    */
    function get_one($query) {

        $this->connect();
        $result = mysqli_query($this->con,$query) or $this->errorMsg("que2", $query);
        if(mysqli_num_rows($result) == 0) return false;

        $row    = mysqli_fetch_row($result);
        mysqli_free_result($result);
        return($row[0]);

    }

    /*
    ####################################################################
         :::  쿼리 + 결과  :::
         결과 값 colomn이 1개인 경우 ","로 구분 리턴
         사용법 : $data = $mysql->get_one($query);
    ####################################################################
    */
    function get_one_jum($query, $split = ",") {

        $this->connect();
        $result = mysqli_query($this->con,$query) or $this->errorMsg("que2", $query);
        if(mysqli_num_rows($result) == 0) return false;

        $rtn = array();
        while($row = mysqli_fetch_row($result)) {
            $rtn[] = $row[0];
        };
        mysqli_free_result($result);

        if(count($rtn)>0) return(join("{$split}", $rtn));
        else return false;

    }

    /*
    ####################################################################
         :::  리소스 해제 :::
         스크립트를 실행하는 동안 너무 많은 메모리를 사용하고 있다고 생각될 때 사용
         인자로 쓰인 result와 관계된 모든 메모리를 비웁니다.
    ####################################################################
    */

    function free_result($res = "default") {

        if($res == "default") return @mysqli_free_result($this->res);
        else                  return @mysqli_free_result($res);

    }


    /*
    ####################################################################
         :::  테이블 리스트 :::
         선택된 디비의 테이블 목록을 리턴 (테이블 존재 확인)
    ####################################################################
    */

    function table_list($dbName = "", $isTable="")
    {
        $this->connect();
        if(!$dbName) $dbName = $this->dbName;
        $result = mysqli_query($this->con, "show tables");

        $return = array();
        while($row = mysqli_fetch_row($result)) {
            $return[] = $row[0];
            if($isTable == $row[0]) return 1;
        }

        if($isTable) return 0;
        else return($return);
    }


    /*
    ####################################################################
         ::: 테이블 락 :::
         $table 는 Array 로 Key 에 테이블명 , Value에 락타입을 갖는 배열구조
         사용법 : $mysql->lock_table($tableLock);
         ex : $tableLock = array("firstTableName"=>"WRITE","secondTableName"=>"WRITE");
    ####################################################################
    */

    function lock($table) {

        $this->connect();
        $query = "LOCK TABLES ";
        $i=0;
        while(list($tableName, $lockType) = each($table))
        {
            if($i) $query .= ", ";
            $query .= $tableName." ".$lockType;
            $i++;
        }
        $this->lockTable = 1;
        return $this->query($this->con,$query);

    }


    /*
    ####################################################################
         ::: 테이블 언락 :::
         테이블에 걸린 락을 제거
    ####################################################################
    */

    function unlock() {

        $this->connect();
        $query = "UNLOCK TABLES";
        $this->lockTable = 0;
        return $this->query($this->con,$query);

    }


    /*
    ####################################################################
         ::: 에러 :::
         mysql_error()을 리턴한다
    ####################################################################
    */

    function error() {

        return mysqli_error();

    }


    /*
    ####################################################################
         ::: MESSAGE TYPE :::
         메세지 타입 값을 등록한다
    ####################################################################
    */

    function msgTYpe($vls) {

        $this->msgType = $vls;

    }


    /*
    ####################################################################
         ::: Insert number :::
         insert시 auto_incresement 키의  ID(마지막 번호)를 리턴
    ####################################################################
    */

    function InsertNo() {

        return $this->insert_id;

    }


    /*
    ####################################################################
         ::: 디비 close :::
         class의 사용한 메모리를 지우고 디비를 닫는다
    ####################################################################
    */

    function close() {

        if(is_resource($this->con)) {
            if(is_resource($this->res)) @mysqli_free_result($this->res);
            if($this->lockTable) $this->unlock();
            return @mysqli_close($this->con);
        }
        return true;

    }


    /*
    ####################################################################
         ::: 에러 메세지 :::
         에러 메세지 출력후 종료
    ####################################################################
    */

    function errorMsg($msg = "", $msg2 = "") {
        global $my_id;

        if($this->debug == "Y") {
            switch($msg) {
                case "con" : $str = "Connection 을 할수 없습니다!<br />dbHost : ".$this->dbHost.", dbUser : ".$this->dbUser;
                    break;
                case "name" : $str = "선택된 디비가 없습니다!<br />dbName : ".$this->dbName;
                    break;
                case "que" : $str = "쿼리 에러입니다!<br />query : ".str_replace("\n","<br />",$this->query);
                    break;
                case "que2" : $str = "쿼리 에러입니다!<br />query : ".$msg2;
                    break;
                case "res" : $str = "결과값이 없습니다!";
                    break;
            }

            $str2 = str_replace("\n","<br />",mysqli_error($this->con));
            $str .= "<br /><br />".$str2;
        }
        else {
            $str = "데이타 베이스 에러입니다!<br />관리자에게 문의 하시기 바랍니다.";
        }

        if($msg != 'con' && $msg != 'name') {

            switch($msg) {
                case "que2" :
                    $message = $msg2;
                    break;
                case "res" :
                    $message = "결과값이 없습니다!";
                    break;
                default :
                    $message = str_replace("\n","<br />",$this->query);
                    break;
            }

            $message	.= "<br /><br />".str_replace("\n","<br />",mysqli_error($this->con));
            $message	= addslashes($message);

            $signdate = time();

            $sql = "INSERT INTO mallRN_db_error_log SET
						id			= '{$my_id}',
						name		= '{$_SERVER['PHP_SELF']}',
						message		= '{$message}',
						signdate	= '{$signdate}'
					";
            $this->connect();
            mysqli_query($this->con, $sql);
        }

        if($this->msgType==1) {
            logMsg($str);
            exit;
        }
        else if($this->msgType==2) {
            echo  json_encode(array('error' => $str));
            exit;
        }

        if(isset($GLOBALS['CK_CP_DB_ERROR'])) {
            $GLOBALS['bSucc'] = 'false';
            return;
        }

        echo "	<!doctype html> 
				<html lang='ko'>
				<head>
				<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />	
				<title>Error</title>
				<style type='text/css'>
				  <!-- 
					@font-face {font-family: 'Noto Sans KR';font-style: normal;font-weight: 300;src: url(//fonts.gstatic.com/ea/notosanskr/v2/NotoSansKR-Light.woff2) format('woff2'),url(//fonts.gstatic.com/ea/notosanskr/v2/NotoSansKR-Light.woff) format('woff'),url(//fonts.gstatic.com/ea/notosanskr/v2/NotoSansKR-Light.otf) format('opentype');}\n
				.fontNoto { font-family: 'Noto Sans KR', sans-serif; font-weight:300; font-size:0.9em; line-height:1.8em }\n
					button[type=button].btns { border:0; overflow:hidden; z-index:1; color:#fff; text-align:center; cursor: pointer; background:#666; font-size: 0.8em; height:32px;  width:150px; line-height:2.5em; letter-spacing: 0.05em; justify-content: center; align-items: center; position: relative; box-shadow:0; -webkit-box-shadow:0; }\n
					button[type=button].btns::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background:#f48042; transform-origin: 0 0; transform: scale3d(0, 1, 1); transition: transform 100ms; }\n
					button[type=button].btns .text { color:#fff; position: relative; }\n
					button[type=button].btns:hover::before { transform-origin: 0 0; transform: scale3d(1, 1, 1); }\n
				  -->
				</style>
				</head>";

        echo " <center>
					<div style='margin-top:250px; width:500px; border:2px solid #f48042;box-shadow: 0px 0px 6px #aaa; -webkit-box-shadow: 0px 0px 6px #aaa;' class='fontNoto'>\n
						<div style='height:30px; line-height:1.8em; background-color:#f48042; text-align:center; color:#fff;'>ERROR</div>\n
						<div style='padding:30px 10px; text-align:center; background-color:#fff;font-size:0.9em'>{$str}</div>\n
						<center style='margin:20px 0;'><button class='btns' type='button' onclick='history.back();'><span class='text'> Move Back </span></button></center>\n
					</div>\n
				</center>";
        exit;
    }


} // End Class

?>