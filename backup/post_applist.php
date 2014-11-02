<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);




#class AppInfo{
#     private $name; #     private $version;
#     private $packagename;
#     private $versionname;
#     //app  logo 的url 规则是：http://domain/$user/$name.png
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


file_put_contents($logName,print_r($user."\r\n",true),FILE_APPEND);

$length = count($list);


if(!file_exists("./".$user))
{
    mkdir("./".$user,0777,true);
}

file_put_contents($logName,print_r($length."\r\n",true),FILE_APPEND);
for($x=0;$x<$length;$x++)
{
    $item = $list[$x];
    var_dump($item);
    //指针形式不行
    $value =  $item['name']."|".$item['version']."|".$item['packagename']."|".$item['versionname']."\n"; 
    echo $value;
    saveImage($user,$item['icon'],$item['name']);
   # file_put_contents($logName,print_r($item['icon'],true),FILE_APPEND);


    //
    $app = array('name'=>$item['name'],'version'=>$item['version'],'packagename'=>$item['packagename'],'versionname'=>$item['versionname']);
    array_push($applist,$app);
}

$backgroud = $_POST['wall'];
if(!empty($backgroud))
{
    saveImage($user,$backgroud,"backgroud");
    file_put_contents($logName,print_r((__LINE__).":".$backgroud,true),FILE_APPEND);
}

#$store_list = array("list"=>$applist);
$store_list = $applist;
$array_string  = json_encode($store_list);

file_put_contents($logName,print_r("store json: ".$array_string,true),FILE_APPEND);
$redis->set($user,$array_string);

?>