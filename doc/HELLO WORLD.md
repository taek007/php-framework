HELLO WORLD
===

一个关于HELLO WORLD 的教程！！！
打开home/controller 创建 Index.class.php 

控制器层将会调取model层的Index下的index方法，获取数据。

控制器层将会通过assign()方法分配获得的变量以及display()方法渲染模板。


```
<?php
namespace controller; 
class Index extends Common{
    
    public function index(){  
       $obj = M('index');
       $a = $obj->index();
       $this->assign('a',$a);
       $this->display();
    }
?>

```


创建Model层
打开home/model 创建Index.class.php

```
<?php
namespace model;

class IndexModel extends Model{
    public function index(){
        return '<h2>HELLO WORLD!!!</h2><Br/>';
    }
    
?>
```
创建View层
打开home/view/default 创建Index_index.php

模板输出变量
```
<?php

echo $a; 

?>

```

最终效果 访问链接 http://hdcms.com/index.php?c=index&a=index
![输入图片说明](https://static.oschina.net/uploads/img/201505/14150156_22YS.png "在这里输入图片标题")

