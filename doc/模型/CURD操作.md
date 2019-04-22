CURD操作
===

HDCMS的数据库处理提供了一套CURD操作。
### 插入数据

写入操作使用 **insert（）**方法 ，使用示例如下：

insert(@param type $table , @param type $data)
包含两个参数，第一个是需要操作的表名，第二个需要操作的数据。
返回值=最新插入数据库的ID

```
    public function tadd($data){
       return $this->insert('small',$data);
    }
```
$data 数据从对应的控制器获得。

也可以批量写入数据，操作使用 **insertAll（**）方法 ，使用示例如下：

insertAll(@param type $table, @param type $data 二维数组)
包含两个参数，第一个是需要操作的表名，第二个需要操作的数据(二维数组)。
返回值=影响行数
```
    public function taddall($data){
         $this->insertAll('small', $data);
    }
```
### 查询数据

查询一行数据记录操作使用 
**get_row（）**
方法,使用示例如下：
```
    public function slt(){
        $condition = array('id'=>'5');
        return $this->get_row('small',$condition);     
    }
```
获取多行数据记录操作使用** get_list() **方法,使用示例如下：
```
    public function slt(){
       return $this->get_list("small");  
    }

```
获取总数记录操作使用**getTotalCount()** 方法。

    public function slt(){
        $condition = array();
        $this->get_list("small",$condition,'',1,1,"*",true);
        return $this->getTotalCount();    
    }
### 修改数据
修改数据使用**update()**方法,使用示例如下：
```
    public function update_data(){
        return $this->update('small',$data,$condition);
    }
```
### 删除数据

删除数据操作使用** delete（）**方法 ，使用示例如下：
```
    public function del(){
        return $this->delete('small',array('id'=>19));
    }

```
### 获取错误信息

操作使用** getError（）**方法 ，使用示例如下：
```
    public function taddall($data){
        return $this->insertAll('small', $data);
        dump($this->getError());
    }
```

如果以上方法不能满足实际需求，还可以使用** query()**,直接写SQL语句。使用示例如下：
```
    public function slt(){                
        $s = "SELECT * FROM small WHERE id=1";
        return $this->query($s);  
    }
```


