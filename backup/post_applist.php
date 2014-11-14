<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);




#class AppInfo{
#     private $name; #     private $version;
#     private $packagename;
#     private $versionname;
#     //app  logo 的url 规则是：http://domain/$user/$packagename.png
#     //按照这种规则去拉取图片就行
#     function _construct($name,$version,$packagename,$versionname)
#     {
#         $this->name = $name;
#         $this->version = $version;
#         $this->packagename = $packagename;
#         $this->versionname = $versionname;
#     }

#}

function saveImage($user,$content,$filename){
   $png = base64_decode($content); 
    //file_put_contents($filename,print_r($content,true));
   $filename = str_replace(".","_",$filename); 
   
    $file = "./".$user."/".$filename.".png"; 
    file_put_contents("filename",$file,FILE_APPEND);
    file_put_contents($file,$png);
}


$redis = new Redis();
$redis->connect("127.0.0.1",6379);

$logName = "php_log";
$list = json_decode($_POST['list'],true);
$user = $_POST['user'];
$applist = array();

file_put_contents("test",print_r($list,true),FILE_APPEND);
file_put_contents($logName,print_r((__LINE__).":".$user."\r\n",true),FILE_APPEND);

$length = count($list);
if($length == 0)
{
    file_put_contents($logName,print_r((__LINE__).":".$user." list is 0 \r\n",true),FILE_APPEND);
    echo "fail";
    exit();
}

if(!file_exists("./".$user))
{
    mkdir("./".$user,0777,true);
}

file_put_contents($logName,print_r((__LINE__).":".$length."\r\n",true),FILE_APPEND);

for($x=0;$x<$length;$x++)
{
    $item = $list[$x];
    //指针形式不行
    $value =  $item['name']."|".$item['version']."|".$item['packagename']."|".$item['versionname']."\n"; 
    saveImage($user,$item['icon'],$item['packagename']);
   # file_put_contents($logName,print_r($item['icon'],true),FILE_APPEND);

    $app = array('name'=>$item['name'],'version'=>$item['version'],'packagename'=>$item['packagename'],'versionname'=>$item['versionname']);
    file_put_contents($logName,print_r((__LINE__).":".$app['name']." add to list \r\n",true),FILE_APPEND);
    array_push($applist,$app);
}

file_put_contents($logName,print_r((__LINE__).":"." out of for loop \r\n",true),FILE_APPEND);

file_put_contents($logName,print_r((__LINE__).":"."applist length: ".count($applist),true),FILE_APPEND);
#$store_list = array("list"=>$applist);
#$store_list = $applist;
$array_string  = json_encode($applist);

file_put_contents($logName,print_r((__LINE__).":"."store json: ".$array_string,true),FILE_APPEND);
$result = $redis->set($user,$array_string);


file_put_contents($logName,print_r((__LINE__).":".$result,true),FILE_APPEND);


echo "success";

?>
