<?php

/**
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */

class skill_controller extends common
{
    function index_action(){
        $skill  = $this->MODEL('skill');
        $where = array();
        $where['orderby'] = array('last_update,desc');
        $res = $skill -> getList($where);


        $categoryM			=	$this -> MODEL('skillcategory');
        $wheredata['keyid'] =	array('>',0);
        $class			=	$categoryM -> getSkillClassList($wheredata);
        foreach ($class  as  $k  =>  $v){
            foreach ($res['list'] as $lv) {
                if($v['id'] == $lv['lesson']) {
                    $class[$k]['lesson'][] = $lv;
                }
            }
        }
        $this->yunset("class",$class);

        $this->yunset("headertitle","技能提升");
        $this->seo("skill");
        $this->yuntpl(array('wap/skill'));
    }

    function show_action()
    {
        $skill  = $this->MODEL('skill');
        $id     =   (int)$_GET['id'];
        $info = $skill->getInfo(array('id' => $id), array('bm' => 1));

        $this->yunset('res', $info);

        $this->data = array('skill_name' => $info['title']);
        $this->seo('skill_show');

        $this->yunset("headertitle","技能详情");
        $this->yuntpl(array('wap/skill_show'));
    }

    function baoming_action(){
        $skillbm			=		$this->MODEL('skillbm');

        $skill_id = !empty($_POST['name']) ? intval($_POST['skill_id']) : 0;
        $name = !empty($_POST['name']) ? trim($_POST['name']) : '';
        $tel = !empty($_POST['tel']) ? trim($_POST['tel']) : '';

        if (!$skill_id || !$name || !$tel) {
            $this->layer_msg('参数错误', 8, 0);
        }

        $return = $skillbm->addBaoming(array('skill_id' => $skill_id, 'name' => $name, 'tel' => $tel));

        $this->layer_msg($return['msg'], $return['code'], 0);
    }
}
?>