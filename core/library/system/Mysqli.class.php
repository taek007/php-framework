<?php

namespace system;
/**
 * mysqli数据库操作类
 * @author  zh 2681674909@qq.com 2015-04-14
 */
class Mysqli {

    private $dbconfig;       //数据库配置
    protected $error;          //错误消息 
    protected $affected_rows = 0; //影响行数 
    protected $totalcount = 0; 
    private $mysqli = null;   //  当前数据库连接实例
    private $config=array(
        "DB_HOST"=>"127.0.0.1",
        "DB_HOST_WRITE"=>"127.0.0.1",
        "DB_USER"=>"root",
        "DB_NAME"=>"test",
        "DB_CHARSET"=>"utf8",
        "DB_PWD"=>"root",
        "DB_PORT"=>3306
    );
  
    
    function __construct($dbconfig = '', $mode = 'read') {
        $this->dbconfig = $dbconfig ? $dbconfig :C("DB_COFING"); 
        $config = C("DB.".$this->dbconfig);
        if($config){
            $this->config = array_merge($this->config,$config);
        }
        $this->connect();
    }

    
    function __destruct() { 
        @mysqli_close($this->mysqli);
        $this->mysqli = null;
    }

    private function connect($dbname = "", $mode = "read") {
        if ($mode == 'write') {
            $host = $this->config['DB_HOST'];
        } else {
            $host = $this->config['DB_HOST_WRITE'];
        }
        if (!$dbname) {
            $dbname = $this->config['DB_NAME'];
        }
        $this->mysqli = mysqli_connect($host, $this->config['DB_USER'], $this->config['DB_PWD'], $dbname, $this->config['DB_PORT']);
        if (mysqli_connect_errno()) {
            $this->error['errormsg'] = mysqli_connect_error();
            exit();
        }
        mysqli_query($this->mysqli, "SET NAMES " . $this->config['DB_CHARSET'] . ";");
        $this->mode = $mode;
    }

    /**
     * @todo 切换数据库的模式,读或写
     */
    public function changeMode($mode = 'write') {
        $this->connect("", $mode);
    }

    /**
     * @todo 选择数据库
     */
    public function changeDb($dbname, $mode = 'read') {
        $this->connect($dbname, $mode);
    } 

    /**
     * @todo 更改数据库连接
     */
    public function changeDbConnect($config, $mode = 'read') {
        $this->config = $config;
        $this->connect($this->config['DB_NAME'], $mode);
    } 
    
    /**
     * @todo 获取影响行数
     */
    public function getAffectRows() {
        return intval($this->affected_rows);
    }

    public function getTotalCount() {
        return intval($this->totalcount);
    }

    private function escape($str){
        return mysqli_escape_string($this->mysqli, $str);
    }

        //////////////////////////////////////////////////////////
    //执行sql语句查询
    public function query($sql,$vals='') { 
        if(!$vals){
            $sql = $this->escape($sql);
        }
        $rs = $this->excute($sql, $vals,false); 
        return $rs;
    }
 
    function refValues($arr){
        if (strnatcmp(phpversion(),'5.3') >= 0){
            $refs = array();
            foreach($arr as $key => $value)
                $refs[$key] = &$arr[$key]; 
            return $refs;
        }
       
        return $arr;
    }
    
    /**
     * @todo 预处理执行SQL
     */
    private function excute($sql, $vals = NULL,$close=true,$find=false) { 
        $stmt = $this->mysqli->stmt_init();   
        $stmt->prepare($sql);
        if ($vals && is_array($vals)) { 
            $data=array();
            $str='';
            foreach ($vals as $k=>$o) {
                $str.=$o['FType']; 
                $data[$k+1]=$o['FValue'];
            }
            $data[0]=$str;  
            ksort($data); 
            call_user_func_array(array($stmt, 'bind_param'), $this->refValues($data)); 
        }
        $res = $stmt->execute();
        if (!$res) {
            $this->error['sql'] = $sql;
            $this->error['errormsg'] = mysqli_error($this->mysqli);
            return;
        }  
        if($find){ //获取查询数量
            $stmt->store_result();
            $this->totalcount = $stmt->num_rows;
            $stmt->free_result(); 
            $stmt->close();
            return $this->totalcount;
        } 
        if($close){  
            $this->affected_rows = $this->mysqli->affected_rows;
            $result = $this->affected_rows;
        }else{ 
            $results=array();
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            } 
            call_user_func_array(array($stmt, 'bind_result'), $this->refValues($parameters));
            while ($stmt->fetch()) {
                $x = array();
                foreach ($row as $key => $val) {
                    $x[$key] = $val;
                }
                $results[] = $x;
            }
            $result = $results;
            
        }
        $stmt->close(); 
        return  $result;
    }

    /**
     * @todo 获取最新插入数据库的ID
     */
    public function getInsertId() {
        return mysqli_insert_id($this->mysqli);
    }
    
    public function getError(){
        return $this->error;
    }

    //新增数据
    public function insert($table, $data) {
        $vals = $mark = $field = array();
        if ($data && is_array($data)) {
            foreach ($data as $key => $v) {
                $field[] = '`' . $key . '`';
                $mark[] = '?';
                $vals[] = array("FValue" => $v, "FType" => $this->getargType($v));
            }
        }
        $sql = 'INSERT INTO `' . $table . '`(' . implode(",", $field) . ') VALUES(' . implode(",", $mark) . ')';
        $rs = $this->excute($sql, $vals,true);  
        return $rs?$this->getInsertId():false;
    }

    /**
     * 批量新增数据
     * @param type $table  表
     * @param type $data  二维数组
     * @return type  受影响的行数
     */
    public function insertAll($table,$data) {
        $vals = $field = array();
        if ($data && is_array($data)) {
            foreach ($data as $v) {
                if(is_array($v)){
                    $mark = array();
                    foreach ($v as $key => $vo) {
                        $field[$key] = '`' . $key . '`'; 
                        $mark[]='"'.$this->escape($vo).'"';
                    }
                    $vals[]='('.  implode(",", $mark).')';
                }  
            }
        }
        $sql = 'INSERT INTO `' . $table . '`(' . implode(",", $field) . ') VALUES' . implode(",", $vals);
        $rs = $this->excute($sql,"",true);  
        return $rs?$this->affected_rows:false;
    }
    
    
    /**
     * @todo 更新数据
     */
    public function update($table, $data, $condition = array()) {
        if (!$table || !$data)
            return;

        $vals = $mark = $field = $field2 = array();
        foreach ($data as $k => $v) {
            $field[] = '`' . $k . '`=?';
            $vals[] = array("FName" => $k, "FValue" => $v, "FType" => $this->getargType($v));
        }
        $sql = 'UPDATE `' . $table . '` SET' . implode(',', $field);
        if ($condition && is_array($condition)) {
            foreach ($condition as $key => $v) {
                $field2[] = '`' . $key . '`=?';
                $vals[] =array("FName" => $key, "FValue" => $v, "FType" => $this->getargType($v));
            }
            $sql.=' WHERE ' . implode(" and ", $field2);
        }
        $rs = $this->excute($sql, $vals);
        return $rs?$this->affected_rows:false; 
    }

    /**
     * @todo 删除数据
     */
    public function delete($table, $condition) {
        if (!$condition)
            return;
        $vals = $field = array();
        if (is_array($condition)) {
            foreach ($condition as $key => $v) {
                $field[] = '`' . $key . '`=?';
                $vals[] = array("FValue" => $v, "FType" => $this->getargType($v));
            }
        }
        $sql = 'DELETE FROM `' . $table . '` WHERE ' . implode(" and ", $field);
        $rs = $this->excute($sql, $vals,true); 
        return $rs?$this->affected_rows:false; 
    }

    /**
     * 获取一行数据
     * @param type $table 表
     * @param type $condition 条件数组
     * @param type $field 字段
     */
    public function getRow($table, $condition, $field = "*") {
        if (!$table || !$condition || !is_array($condition))
            return;
        $vals = $temp = array();
        foreach ($condition as $key => $v) {
            if(is_array($v)){
                $temp[] = '`' . $key . '`'.$v[0].'?';
                $vals[] = array("FValue" => $v[1], "FType" => $this->getargType($v[1]));
            }else{
                $temp[] = '`' . $key . '`=?';
                $vals[] = array("FValue" => $v, "FType" => $this->getargType($v));
            }
        }
        if (is_array($field)) {
            $field = implode(",", $field);
        } 
        $sql = 'SELECT ' . $field . ' FROM `' . $table . '` WHERE  ' . implode(' and ', $temp);
        $rs = $this->excute($sql, $vals,false);
        if($rs){
            return $rs[0];
        }
        return false;
    }
    
    /**
     * 获取多行数据
     * @param type $table
     * @param type $condition
     * @param type $p
     * @param type $size
     * @param type $field
     * @param total 获取总数量
     */
    public function getList($table,$condition=array(),$order="",$p=1,$size=0,$field="*",$total=false){
        if(!$table) return; 
        $vals = $temp = array();
        if($condition && is_array($condition)){ 
            foreach ($condition as $key => $v) {
                if(is_array($v)){
                    $temp[] = '`' . $key . '`'.$v[0].'?';
                    $vals[] = array("FValue" => $v[1], "FType" => $this->getargType($v[1]));
                }else{
                    $temp[] = '`' . $key . '`=?';
                    $vals[] = array("FValue" => $v, "FType" => $this->getargType($v));
                } 
            }
        }
        if (is_array($field)) {
            $f = array();
            foreach ($field as $v) {
                $f[]='`'.$v.'`';
            }
            $field = implode(",", $f);
        }  
        $sql = 'SELECT ' . $field . ' FROM `' . $table . '` ';
        if($temp){
            $sql.=' WHERE '. implode(' and ', $temp);
        }
        $tmlsql = preg_filter('/SELECT\s(.*)\sFROM(.*)/i',"SELECT COUNT(*) as total FROM $2",trim($sql)); 
        if($order){
            $sql.=' ORDER BY '.$order;
        }  
        if($size){
            $start = ($p-1)*$size;
            $sql.=' LIMIT '.$start.','.$size;
        }
        $rs = $this->excute($sql, $vals,false); 
        if($size && $total){ //分页获取时候获取总数量
            $rs0 = $this->excute($tmlsql,$vals,false); 
            $this->totalcount = $rs0[0]['total'];
            $data['total'] = $this->totalcount;
            $data['data'] = $rs;
            return $data;
        }
        return $rs;
    }
    
     
    /**
     * bulid condition
     * @param string|array $condition
     * @return string|array
     */
    protected function buildCondition($condition) {
        if (is_array($condition)) {
            $temp = array();
            $logic=" and ";
            if(isset($condition['_logic'])){
                $logic=$condition['_logic'];
                unset($condition['_logic']);
            }
            foreach ($condition as $key => $vo) {
                if (is_numeric($vo)) {
                    $temp[] = " `{$key}` = '{$vo}' ";
                } else if(is_array($vo)) {
                    if(isset ($vo['_logic'])){
                        $_logic = $vo['_logic'];
                        unset($vo['_logic']);
                        $tmp =array();
                        foreach ($vo as $k=>$v) {
                            $tmp[]= " {$k} = '{$v}' ";
                        }
                        $temp[]= '('.implode(" {$_logic} ", $tmp).')' ;
                    }else if($vo[2]=="in"){
                        $ids = $vo[1];
                        if(is_array($vo[1])){
                            $ids = implode(",", $vo[1]);
                        }
                        $temp[] = " `{$vo[0]}` in({$ids}) ";
                    }else if($vo[2]=="find_in_set"){ 
                        $temp[] = " FIND_IN_SET('{$vo[1]}',{$vo[0]}) ";
                    }else if($vo[2]=="like"){
                        if(isset($vo[3]) && $vo[3]=="left"){
                            $temp[] = " `{$vo[0]}` like '%{$vo[1]}' ";
                        }else if(isset($vo[3]) && $vo[3]=="right"){
                            $temp[] = " `{$vo[0]}` like '{$vo[1]}%' ";
                        }else{
                            $temp[] = " `{$vo[0]}` like '%{$vo[1]}%' ";
                        }
                    }else{
                        $temp[] = " `{$vo[0]}` {$vo[2]} '{$vo[1]}' ";
                    } 
                }else{
                    $temp[] = " `{$key}` = '{$vo}' ";
                }
            }
            $condition = implode(" {$logic} ", $temp);
             
             
        }
        return $condition;
    }

    /**
     * @todo 获取数据的类型
     */
    public function getargType($s) {
        if (is_integer($s))
            return "i";
        if (is_float($s))
            return "d";
        if (is_string($s))
            return "s";
        if (is_object($s))
            return "b";
        return "s";
    }

    /**
     * 获取表字段
     * @param type $table
     * @param type $select_map
     * @return type
     */
    public function getField($table, $select_map = array()) {
        $fields = array();
        $q = $this->query("DESC `$table`");
        if (!$q)
            return;
        foreach ($q as $r) {
            $Field = $r['Field'];
            $Type = $r['Type'];
            $type = 'varchar';
            $cate = 'other';
            $extra = null;
            if (preg_match('/^id$/i', $Field))
                $cate = 'id';
            else if (preg_match('/^_time/i', $Field))
                $cate = 'integer';
            else if (preg_match('/^_number/i', $Field))
                $cate = 'integer';
            else if (preg_match('/_id$/i', $Field))
                $cate = 'fkey';
            if (preg_match('/text/i', $Type)) {
                $type = 'text';
                $cate = 'text';
            }
            if (preg_match('/date/i', $Type)) {
                $type = 'date';
                $cate = 'time';
            } else if (preg_match('/int/i', $Type)) {
                $type = 'int';
            } else if (preg_match('/(enum|set)\((.+)\)/i', $Type, $matches)) {
                $type = strtolower($matches[1]);
                eval("\$extra=array($matches[2]);");
                $extra = array_combine($extra, $extra);

                foreach ($extra AS $k => $v) {
                    $extra[$k] = isset($select_map[$k]) ? $select_map[$k] : $v;
                }
                $cate = 'select';
            }

            $fields[] = array(
                'name' => $Field,
                'type' => $type,
                'extra' => $extra,
                'cate' => $cate,
            );
        }
        return $fields;
    }

    /** 
     * @todo 开始事务
     */
    public function begin() {
        $this->mysqli->autocommit(FALSE);
    }

    /** 
     * @todo 事务提交
     */
    public function commit() {
        $this->mysqli->commit();
        $this->mysqli->autocommit(TRUE);
    }

    /** 
     * @todo 事务回滚
     */
    public function rollback() {
        $this->mysqli->rollback();
        $this->mysqli->autocommit(TRUE);
    }

}
