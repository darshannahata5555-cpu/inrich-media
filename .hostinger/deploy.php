<?php
$root='/home/u128113746/domains/inrichmedia.in/public_html';
$work=dirname($root).'/.inrich-release-20261116-ec03752';
if(!is_dir($work)&&!mkdir($work,0700,true))throw new Exception('Cannot prepare workspace');
$lock=fopen($work.'/lock','c');if(!flock($lock,LOCK_EX|LOCK_NB))exit;
if(is_file($work.'/complete.json')){echo "INRICH_DEPLOY_ALREADY_COMPLETE\n";exit;}
$url='https://codeload.github.com/darshannahata5555-cpu/inrich-media/zip/ec037523c6946c2b57d19c2a3703a0338ea99adb';
$hash='32b1d9b86f63a1cfbfb6d113ee99656b2c2c182cfa349e99bb72ef793048697a';
$ch=curl_init($url);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_TIMEOUT=>120,CURLOPT_USERAGENT=>'InrichDeployment/1.0']);
$data=curl_exec($ch);$http=curl_getinfo($ch,CURLINFO_HTTP_CODE);
if($data===false||$http!==200)throw new Exception('Archive download failed '.$http);
if(hash('sha256',$data)!==$hash)throw new Exception('Archive checksum mismatch');
if(file_put_contents($work.'/source.zip',$data)===false)throw new Exception('Cannot stage archive');
$zip=new ZipArchive();if($zip->open($work.'/source.zip')!==true)throw new Exception('Cannot open archive');
$prefix='inrich-media-ec037523c6946c2b57d19c2a3703a0338ea99adb/';$files=[];
for($i=0;$i<$zip->numFiles;$i++){
$name=$zip->getNameIndex($i);
if(strpos($name,$prefix)!==0||substr($name,-1)==='/')continue;
$rel=substr($name,strlen($prefix));
if($rel===''||strpos($rel,'..')!==false||$rel[0]==='/')throw new Exception('Invalid archive path');
$dest=$work.'/release/'.$rel;if(!is_dir(dirname($dest)))mkdir(dirname($dest),0700,true);
$bytes=$zip->getFromIndex($i);if($bytes===false)throw new Exception('Cannot read file');
if(in_array(pathinfo($rel,PATHINFO_EXTENSION),['html','js','css','txt','json'],true)){
$bytes=str_replace('/inrich-media','',$bytes);
$first=explode('/',$rel)[0];
if(!in_array($first,['404','404.html','_not-found'],true))$bytes=str_replace('noindex, nofollow','index, follow',$bytes);
}
if(file_put_contents($dest,$bytes)===false)throw new Exception('Cannot stage file');
$files[]=$rel;
}
$zip->close();if(count($files)!==230||!in_array('index.html',$files,true))throw new Exception('Unexpected release contents');
$ht=file_get_contents($root.'/.htaccess');if($ht===false||strpos($ht,'DirectoryIndex index.html index.php')===false)throw new Exception('Unexpected routing configuration');
if(!is_dir($work.'/previous'))mkdir($work.'/previous',0700,true);
if(!is_file($work.'/previous/.htaccess')&&!copy($root.'/.htaccess',$work.'/previous/.htaccess'))throw new Exception('Cannot back up routing');
foreach($files as $rel){
$dest=$root.'/'.$rel;
if(is_file($dest)&&!is_file($work.'/previous/'.$rel)){
$bak=$work.'/previous/'.$rel;if(!is_dir(dirname($bak)))mkdir(dirname($bak),0700,true);
if(!copy($dest,$bak))throw new Exception('Cannot back up existing file');
}
}
usort($files,function($a,$b){return ($a==='index.html')-($b==='index.html');});
foreach($files as $rel){
$dest=$root.'/'.$rel;if(!is_dir(dirname($dest)))mkdir(dirname($dest),0755,true);
$tmp=$dest.'.inrich-deploy-tmp';
if(!copy($work.'/release/'.$rel,$tmp))throw new Exception('Cannot copy release file');
chmod($tmp,0644);if(!rename($tmp,$dest))throw new Exception('Cannot publish release file');
}
$result=['domain'=>'inrichmedia.in','source'=>'darshannahata5555-cpu/inrich-media','commit'=>'ec037523c6946c2b57d19c2a3703a0338ea99adb','files'=>count($files),'deployed_at'=>gmdate('c'),'backup_directory'=>$work.'/previous'];
file_put_contents($work.'/complete.json',json_encode($result,JSON_PRETTY_PRINT));
echo "INRICH_DEPLOY_OK ".json_encode($result)."\n";

