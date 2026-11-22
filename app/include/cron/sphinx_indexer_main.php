<?php
/*
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
/************
* 计划任务：sphinx相关，定期
    更新主索引
*/
set_time_limit(0);

global $config;
$sphinxPath = $config['sphinx_path'];

$os = php_uname('s');
if(stristr($os, 'window')){//windows
  $batPath = APP_PATH . 'data/cron/sphinx/sphinx_indexer_main.bat';

  $diskName = dirname($sphinxPath);
  $diskName = rtrim($diskName, '\\');
  $diskName = rtrim($diskName, '/');

  $sphinxPath = str_ireplace($diskName, '', $sphinxPath);
  $sphinxPath = trim($sphinxPath, '\\');
  $sphinxPath = trim($sphinxPath, '/') . '/bin';

  //入口文件的路径
  $scriptPath = dirname($_SERVER['SCRIPT_FILENAME']);
  if(strstr($scriptPath, $diskName)){
    $appPath = str_ireplace($diskName, '', $scriptPath);
    $appPath = str_ireplace('\\', '/', $appPath);
    $appPath = trim($appPath, '/');
    $num = substr_count($appPath, '/') + 1;
    $path = '';
    for($i = 0; $i < $num; $i ++){
      $path .= '../';
    }
  
    exec("start $batPath $diskName $sphinxPath $path");
  }
  else{
    exec("start $batPath $diskName $sphinxPath");
  }
}
else{//linux
  $shPath = APP_PATH . 'data/cron/sphinx/sphinx_indexer_main.sh';
  exec("sh $shPath");
}
