<?php
namespace model; 
class Model{
  
    protected $db_config="";
    protected $db_mode="";
    private $db=null; 

    public function __construct() { 
        $this->db = new \system\Mysqli($this->db_config,$this->db_mode); 
    }

    /**
     * sql语句查询
     * @param type $sql
     * @return type
     */
    public function query($sql){ 
        $vals=array();
        $args = func_num_args(); 
        if ($args>1){
            for ($i = 1; $i < $args; $i++) { 
                $data = func_get_arg($i); 
                if(is_array($data)){
                    foreach ($data as $value) {
                        $vals[] = array("FValue" => $value, "FType" => $this->db->getargType($value));
                    }
                }else{ 
                    $vals[] = array("FValue" => $data, "FType" => $this->db->getargType($data));
                } 
            }
        }
        return $this->db->query($sql,$vals);
    }
    /**
     * 获取一行记录
     * @param type $table 表名
     * @param type $condition 条件
     * @param string $field 字段
     * @return array
     */
    public function get_row($table,$condition,$field='*'){  
        return $this->db->getRow($table,$condition,$field='*');
    }
    /**
     * 获取总数
     * @return int
     */
    public function getTotalCount(){
        return $this->db->getTotalCount();
    } 
    /**
     * 获取多行数据
     * @param type $table 表
     * @param type $condition 条件 数组形式
     * @param type $order 排序
     * @param type $p 页码
     * @param type $size 数量
     * @param string $field 字段
     * @param type $total 是否同时获取总数
     * @return type array
     */
    public function get_list($table,$condition=array(),$order="",$p=1,$size=0,$field="*",$total=false){
        return $this->db->getList($table,$condition,$order,$p,$size,$field,$total);
    }
    /**
     * 添加数据
     * @param type $table 
     * @param type $data
     * @return type
     */
    public function insert($table,$data){
        return $this->db->insert($table,$data);
    }
    
    /**
     * 批量插入数据
     * @param type $table 表
     * @param type $data 二维数组
     * array(
     *    array(
     *      'id'=>2
     *      'name'=>'z'
     *    ),
     *    array(
     *      'id'=>3,
     *      'name'=>'s'
     *    )
     * )
     * @return type
     */
    public function insertAll($table,$data){
        return $this->db->insertAll($table,$data);
    }
    /**
     * 修改数据
     * @param type $table 表
     * @param type $data 数据数组
     * @param type $condition 条件数组
     * @return type int受影响的函数
     */
    public function update($table,$data,$condition){
        return $this->db->update($table,$data,$condition);
    }
    
    /**
     * 删除数据
     * @param type $table 表
     * @param type $condition 条件数组
     * @return type int受影响的函数
     */
    public function delete($table,$condition){
        return $this->db->delete($table,$condition);
    }
    
    /**
     * 获取错误信息
     * @return type array
     */
    public function getError(){
        return $this->db->getError();
    }
    
}
