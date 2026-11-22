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
class index_controller extends common{

    function index_action(){

        $categoryM			=	$this -> MODEL('skillcategory');
        $class			    =	$categoryM -> getSkillClassList(array('keyid'=>array('>',0)));

        $where      =   array();
        $skillM		=	$this -> Model('skill');
        $List       =   $skillM->getList($where);


        foreach ($class  as  $k  =>  $v){
            foreach ($List['list'] as $lv) {
                if($v['id'] == $lv['lesson']) {
                    $class[$k]['lesson'][] = $lv;
                }
            }
        }

        $this -> yunset('class', $class);
        $this -> seo('skill');
        $this -> yun_tpl(array('index'));
    }

    function show_action()
    {
        $skillM		=	$this -> Model('skill');
        $id     =   (int)$_GET['id'];
        $info = $skillM->getInfo(array('id' => $id));

        $this->yunset('res', $info);

        $this->data = array('skill_name' => $info['title']);
        $this->seo('skill_show');
        $this->yun_tpl(array('show'));
    }

    function baoming_action(){
        if($_POST['submit']) {
            $skillbm = $this->MODEL('skillbm');

            $skill_id = !empty($_POST['name']) ? intval($_POST['skill_id']) : 0;
            $name = !empty($_POST['name']) ? trim($_POST['name']) : '';
            $tel = !empty($_POST['tel']) ? trim($_POST['tel']) : '';

            if (!$skill_id || !$name || !$tel) {
                $this->ACT_layer_msg('参数错误', 8);
            }
			if (CheckMobile($_POST['tel']) == false) {
                $this->ACT_layer_msg('请填写正确的手机号！', 8);
            }
            $return = $skillbm->addBaoming(array('skill_id' => $skill_id, 'name' => $name, 'tel' => $tel));

            if($return['code'] == 9) {
                $this->ACT_layer_msg($return['msg'], 9, 1);
            }else{
                $this->ACT_layer_msg($return['msg'], 8);
            }
        }
    }

    function prestr_action(){

        $skillM		=	$this -> Model('skill');

        $page        =   intval($_POST['page']);

        $id         =   intval($_POST['id']);

        $num         =   $skillM -> JobNum(array('id' => $id));

        $data['num'] =   $num;

        $limit       =   intval($_POST['limit']);

        $maxpage     =   intval(ceil($num/$limit));

        if ($page > $maxpage){
            $page    =   $maxpage;
        }

        $start       =     $page * $limit;

        if (intval($_POST['updown'])==1){

            $skillpage   =   max(1,($page-1));

        }else if (intval($_POST['updown'])==2){

            $skillpage   =   min($maxpage,($page+1));

        }

        if($skillpage == 1){

            $start  =  0;

        }else{

            $start   =   ($skillpage-1)*$limit;
        }

        $listA    =   $skillM -> getList(array('id' => $id, 'orderby' => 'last_update,desc', 'limit'=>array($start, $limit)));
        $skill_list    =   $listA['list'];
        $list     =   "";


        $list .="<div class='pages' style='margin-top:20px'>
                            <a href=\"javascript:void(0);\" onclick=\"page('$_POST[id]','$skillpage','5','1');\">上一页</a>
                            <a href=\"javascript:void(0);\" onclick=\"page('$_POST[id]','$skillpage','5','2');\">下一页</a>
                        </div>";
        $data['list']=$list;

        echo json_encode($data);
    }

}
?>