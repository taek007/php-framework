<?php
namespace controller; 
class Index extends Common{
    
    public function index(){  
       // echo 'hello welcome to hdcms!';
       // $conn = mssql_connect('192.168.10.25','sa','123') or die("Couldn't connect to SQL Server on");     
        //$db = mssql_select_db('LogData',$conn) or die("Couldn't open database");
       // Phpinfo();
        $dbhost = '192.168.10.25'; 
	$dbuser = 'sa'; //你的mssql用户名 
 	$dbpass = '123'; //你的mssql密码 
	$dbname = 'LogData'; //你的mssql库名 

        $connection = odbc_connect("Driver={SQL Server};Server=$dbhost;Database=$dbname",$dbuser,$dbpass);
        if(!$connection){
            die("连接失败");
        }

        $sql="exec proc_login_diff5 '2015-05-11','1')"; 
        $stmt=odbc_prepare($connection, "exec proc_login_diff5(2015-05-11,0)"); 
        $stmt->odbc_execute();
        // // $sql2 = "call proc_login_diff5('2015-02-11',partner)";
       // $exec=odbc_exec($connection,$sql); 
        // $result = $exec->fetch(PDO::FETCH_ASSOC);
        //print_r($exec);
    }
}
