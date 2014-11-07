<?php


$redis = new Redis();
$redis->connect("127.0.0.1",6379);
$user = $_POST['user'];
$logName = "check_applist_log";
if(empty($user))
{
    file_put_contents($logName,print_r("user is empty"."\r\n",true),FILE_APPEND);
    echo "fail";
    exit();
}

if(!file_exists("./".$user))
{
    file_put_contents($logName,print_r($user."app pic file not exists"."\r\n",true),FILE_APPEND);
    echo "no";
    exit();
}


$applist = $redis->get($user); 
if(empty($applist))
{
    file_put_contents($logName,print_r($user."app redis list  not exists"."\r\n",true),FILE_APPEND);
    echo "no";
    exit();
}

file_put_contents($logName,print_r($user."check applist request coming"."\r\n",true),FILE_APPEND);
echo "yes";

?>
