<?php
#配对屏幕分享程序


//判断applist是否是在对方的app列表里，
//使用packagename来甄别
function isInObjList($applist,$packagename)
{
    $length = count($applist);
    for($x=0;$x<$length; $x++)
    {
        $item = $applist[$x];
        if($item->packagename == $packagename)
        {
            return TRUE; 
        }
    }
    return FALSE;

}


$redis = new Redis();
$redis->connect("127.0.0.1",6379);

$queue = "queue";
$user = $_POST['user'];

$logName = "makepair.log";
file_put_contents($logName,print_r($user,true),FILE_APPEND);

$current_user = $redis->get($queue);
if(empty($current_user))
{
    $redis->set($queue,$user);
    echo $user."enter queue,just waiting others to make pair";
    exit("");
}
if($current_user == $user)
{
    echo $user."has been in  queue,just waiting others to make pair";
    exit("");
}

//获取到等待配对的对象。比对两者app的差异
//被配对对象使用心跳来获取到自己被配对上的信息
else
{
    $I_single_have = array();
   $my_applist_str = $redis->get($user); 
   $obj_applist_str = $redis->get($user); 
   $my_applist = unserialize($my_applist_str);
   $obj_applist = unserialize($obj_applist_str);
   file_put_contents($logName,print_r(count($my_applist),true),FILE_APPEND);
   file_put_contents($logName,print_r(count($obj_applist),true),FILE_APPEND);

   $myLength = count($my_applist);
   for($i = 0; $i < $myLength; $i++)
   {
       $item = $my_applist[$i];
       $res =  isInObjList($obj_applist,$item->packagename);
       if($res == FALSE)
       {
           array_push($I_single_have,$item); 
       }
   
   }

   $Obj_single_have = array();
   $objLength = count($obj_applist);
   for($k = 0; $k < $objLength; $k++)
   {
   
       $item = $obj_applist[$k];
       $res =  isInObjList($my_applist,$item->packagename);
       if($res == FALSE)
       {
           array_push($Obj_single_have,$item); 
       }
   
   }

   file_put_contents($logName,print_r(count($I_single_have),true),FILE_APPEND);
   file_put_contents($logName,print_r(count($Obj_single_have),true),FILE_APPEND);

   #存到配对内存中
   #
   $my_pair_res = serialize($Obj_single_have); 
   $redis->set('pair'.$user,$my_pair_res);
   $obj_pair_res = serialize($I_single_have); 
   $redis->set('pair'.$current_user,$obj_pair_res);

   $data = array();
   $pairLength = count($Obj_single_have);
   while($i= 0; $i < $pairLength; $i++)
   {
       $item = $Obj_single_have[$i];
       $data[] = array('name'=>$item->name,'version'=>$iten->version,'packagename'=>$item->packagename,'versionname'=>$item->versionname);
   }
   //还差返回username
   echo json_encode($data);


}

?>
