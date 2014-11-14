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
        if($item['packagename']== $packagename)
        {
            return TRUE; 
        }
    }
    return FALSE;

}

//return value is json_encode  string
function constructJsonUnit($user,$diff_applist,$all_applist)
{
    $diff_data = array();
    $length = count($diff_applist);

    for($i=0; $i<$length;$i++)
    {
        $item = $diff_applist[$i];
        $unit = array('name'=>$item['name'],'version'=>$item['version'],'packagename'=>$item['packagename'],'versionname'=>$item['versionname']);
        array_push($diff_data,$unit);
    }

    $data = array();
    $length = count($all_applist);

    for($i=0; $i<$length;$i++)
    {
        $item = $all_applist[$i];
        $unit = array('name'=>$item['name'],'version'=>$item['version'],'packagename'=>$item['packagename'],'versionname'=>$item['versionname']);
        array_push($data,$unit);
    }

    //还差返回username
    $res = array('user'=>$user,'diff_applist'=>$diff_data,"all_applist"=>$data); 
    $json_res = json_encode($res);
    return $json_res;

}

$redis = new Redis();
$redis->connect("127.0.0.1",6379);

$queue = "queue";
$user = $_POST['user'];
if(empty($user))
{
    echo "error! user is empty";
    exit("");
}
$logName = "pair_log";
file_put_contents($logName,print_r((__LINE__).':'.$user.'|'."\r\n",true),FILE_APPEND);
//第一步先查看下是否自己已经被匹配删了
$result = $redis->get('pair'.$user);
if(!empty($result))
{
    #已经被匹配上了呵呵直接返回
    file_put_contents($logName,print_r((__LINE__).':'.$user.'has been pair!'.$result.'|'."\r\n",true),FILE_APPEND);
//第一步先查看下是否自己已经被匹配删了
    //删除匹配结果
    echo $result; 
   # $result = $redis->delete('pair'.$user);
    exit();
}
$current_user = $redis->get($queue);
if(empty($current_user))
{
    $redis->set($queue,$user);
    echo $user."error! enter queue,just waiting others to make pair";
    exit("");
}


file_put_contents($logName,print_r((__LINE__).':'.'queue current user:'.$current_user."\r\n",true),FILE_APPEND);
file_put_contents($logName,print_r((__LINE__).':'.'login user:'.$user."\r\n",true),FILE_APPEND);

if($current_user == $user)
{
    echo $user."error! has been in  queue,just waiting others to make pair";
    exit("");
}

//获取到等待配对的对象。比对两者app的差异
//被配对对象使用心跳来获取到自己被配对上的信息
else
{
    $I_single_have = array();
    $my_applist_str = $redis->get($user); 
    $obj_applist_str = $redis->get($current_user); 
    $my_applist = json_decode($my_applist_str,true);
    $obj_applist = json_decode($obj_applist_str,true);

    $myLength = count($my_applist);
    for($i = 0; $i < $myLength; $i++)
    {
        $item = $my_applist[$i];
        $res =  isInObjList($obj_applist,$item['packagename']);
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
        $res =  isInObjList($my_applist,$item['packagename']);
        if($res == FALSE)
        {
            array_push($Obj_single_have,$item); 
        }

    }


    #存到配对内存中
    #数据结构应该为：user，被配对对象的差异差异applist
    #$my_pair_res = serialize($Obj_single_have); 
    #$redis->set('pair'.$user,$my_pair_res);
    #存储被匹配对象的匹配结果供匹配对象拉到
    $pair_obj_res = constructJsonUnit($user,$I_single_have,$my_applist);
    file_put_contents($logName,print_r((__LINE__).':'.'I_single_have:'.count($I_single_have)."\r\n",true),FILE_APPEND);
    file_put_contents($logName,print_r((__LINE__).':'.'applist_by_makepair:'.$pair_obj_res."\r\n",true),FILE_APPEND);
    $redis->set('pair'.$current_user,$pair_obj_res);



   $final_result = constructJsonUnit($current_user,$Obj_single_have,$obj_applist);
    file_put_contents($logName,print_r((__LINE__).':'.'Obj_single_have:'.count($Obj_single_have)."\r\n",true),FILE_APPEND);
   file_put_contents($logName,print_r((__LINE__).':'.'applist_by_makepair:'.$final_result,true),FILE_APPEND);
   echo $final_result;
   file_put_contents($logName,print_r((__LINE__).':'.'make pair response success',true),FILE_APPEND);
   //配对完毕，清理队列等待数据
 #  $redis->delete($queue);


}

?>
