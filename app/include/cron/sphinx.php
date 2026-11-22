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
    查询每个主索引的最大id，记录到admin_config表
    查询每个主索引的总数量，记录到admin_config表
*/
global $db_config,$db, $sphinxTable;

//先删除旧数据
$deleteWhere = array();
foreach($sphinxTable as $t){
  $deleteWhere [] = "'{$t}_max_id'";
  $deleteWhere [] = "'{$t}_count'";
}
$deleteWhere = implode(',', $deleteWhere);
$db->query("DELETE FROM {$db_config['def']}admin_config where `name` in ($deleteWhere)");

//增加新数据
$addValues = array();
foreach($sphinxTable as $t){
  $id = $db->query("SELECT id FROM {$db_config['def']}{$t} order by id desc limit 1");
  $id = $db->fetch_array($id);
  $addValues [] = "('{$t}_max_id','{$id['id']}')";

  $count = $db->query("SELECT count(id) as num FROM {$db_config['def']}{$t}");
  $count = $db->fetch_array($count);
  $addValues [] = "('{$t}_count','{$count['num']}')";  
}
$addValues = implode(',', $addValues);

$db->query("INSERT INTO {$db_config['def']}admin_config (`name`,`config`) VALUES $addValues ");

//再生成网站缓存
$query = $db->query("SELECT * FROM {$db_config['def']}admin_config");
while($row = $db->fetch_array($query)){
    if($row['name']=='sy_member_icon_arr' || $row['name']=='sy_member_iconv_arr'){
        $configarr[$row['name']]	=	empty($row['config']) ? '' : unserialize($row['config']);
    }else{
        $configarr[$row['name']]	=	$row['config'];
    }
}
made_web(PLUS_PATH.'config.php',ArrayToString($configarr),'config');

//===================== 生成索引 =====================
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
?>