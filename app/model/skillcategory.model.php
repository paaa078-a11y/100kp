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
class skillcategory_model extends model{
    /*
     * 获取自身及子类id集
     * $table	: 查询的表格名字符串
     * $id		: 查询父类id，数组形式的id集合
     * $type	: 'onlyson' 只获取子类id合集
     */
    function sonclass($table,$id=array(),$type='all'){
        $ids	=	array();
        if(count($id)){
            $class	=	$this	->	select_all($table,array('keyid'=>array('in',pylode(',',$id))),"id");
            if($class&&is_array($class)){
                foreach($class as $val){
                    $ids[]	=	$val['id'];
                }
            }
            if($ids&&is_array($ids)){
                $cl	=	$this	->	select_all($table,array('keyid'=>array('in',pylode(',',$ids))),"id");
            }
            if($cl){
                foreach($cl as $v){
                    $ids[]	=	$v['id'];
                }
            }
            $ids	=	array_unique($ids);
            if($type!='onlyson'){
                $ids	=	array_merge($id,$ids);
            }
        }
        return $ids;
    }

    function getSonByVariable($variable){
        $settlement = $this->getSkillClass(array('variable' => array('=', $variable)));
        $res = array();
        if ($settlement) {
            $res = $this->select_all('skill_class', array('keyid' => array('=', $settlement['id'])), 'id, name');
        }
        return $res;
    }

    /*
     * 获取单个分类
     * $whereData 	查询条件
     * $field		自定义查询字段
     */
    function getSkillClass($whereData=array(),$field='*'){
        $one	=	$this	->	select_once('skill_class',$whereData,$field);
        return	$one;
    }
    /*
     * 获取技能类别列表
     * $whereData 	查询条件
     * $field		自定义查询字段
     */
    function getSkillClassList($whereData=array(),$field='*'){
        $list	=	$this	->	select_all('skill_class',$whereData,$field);
        return	$list;
    }
    /*
     * 添加或更新技能类别
     * $addData 	提交分类数据
     * $whereData 	更新分类查询条件
     */
    function addSkillClass($addData=array(),$whereData = array()){

        if(!empty($whereData)){
            if($addData['name']){//修改名称
                $type	=	'名称';
            }else{
                unset($addData['name']);
            }
            if($addData['sort']){//修改排序
                $type	=	'排序';
            }else{
                unset($addData['sort']);
            }
            $this	->	update_once('skill_class',$addData,$whereData);
            $showid	=	$whereData['id']	?	"(ID:".$whereData['id'].")"	:	'';

            $return['error'] = 0;
            $return['msg'] = "技能分类" . $showid . $type . "修改成功";
        }else{
            $namestring = "'" . @implode("','", $addData['name']) . "'";
            $name				=	array();
            foreach ($addData['name'] as $val){
                if($val){
                    $name[]		= 	$val;
                }
            }

            if($addData['keyid']){
                $where['keyid'] =	$addData['keyid'];
            }
            if(count($name)){
                $where['name']  =	array('in',"'".@implode("','",$name)."'");
            }
            //检查提交的类别名称是否有同名
            $class			=	$this	->	getSkillClassList($where);
            $valueData			=	array();

            if(empty($class)){//没有同名类则正常添加
                if($addData['ctype'] == '1'){//添加的是一级分类
                    foreach ($name as $key => $val){
                        foreach ($addData['variable'] as $k => $v){
                            if($k == $key){
                                $valueData[$key]['name']		= $val;
                                $valueData[$key]['variable']	= trim($v);
                            }
                        }
                    }
                }else{//添加二级分类
                    foreach ($name as $key => $val){
                        $valueData[$key]['name'] 	= $val;
                        $valueData[$key]['keyid'] 	= intval($addData['keyid']);
                    }
                }
                $ucid 			=	$this	->	DB_insert_multi('skill_class',$valueData);
                $return['msg']	=	$ucid ? 2 : 3;
                if ($ucid) {
                    $return['error'] = 0;
                    $return['msg'] = '技能分类(' . $namestring . ')添加成功';
                } else {
                    $return['error'] = 1;
                    $return['msg'] = '技能分类(' . $namestring . ')添加失败';
                }
            }else{//有同名类，给出提示
                $return['error'] = 1;
                $return['msg'] = '技能分类已存在';
            }
        }
        return	$return;
    }
    /*
     * 删除技能分类
     * $data 		自定义数组数据
     *				$data['type'] :one 单个删除
     *							  :all 多个删除
     * $whereData 	删除分类查询条件
     */
    function delSkillClass($whereData=array(),$data=array()){

        $id					=	array();
        $sclass				=	$this	->	getSkillClassList($whereData,'id');
        foreach ($sclass as $key => $value) {
            $id[]			=	$value['id'];
        }
        $ids				=	$this	->	sonclass('skill_class',$id);//获取当前项及其所有子类的id集合
        if(count($ids)){
            $result	=	$this	->	delete_all('skill_class',array('id'=>array('in',pylode(',',$ids))), '');
        }
        $return['error'] = $result ? 0 : 1;
        $msgDetail = '(ID：' . pylode(',', $ids) . ')';
        $return['msg'] = $result ? '技能分类' . $msgDetail . '删除成功' : '技能分类删除失败';
        return	$return;
    }
}
?>
