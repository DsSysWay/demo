<?php


function saveImage($user,$content,$filename){
   $png = base64_decode($content); 
    //file_put_contents($filename,print_r($content,true));
   
    $file = "./".$user."/".$filename.".png"; 
    file_put_contents("filename",$file,FILE_APPEND);
    file_put_contents($file,$png);
}



$logName = "upload_wall_paper";
$user = $_POST['user'];

if(!file_exists("./".$user))
{
    mkdir("./".$user,0777,true);
}

saveImage($user,$_POST['wall'],"wallpaper");
echo "success";


?>
