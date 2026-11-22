<?php

/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/

class skill_controller extends wxapp_controller
{
    function list_action()
    {
        $skill = $this->MODEL('skill');

        $res = array();

        $where = array();
        $where['orderby'] = array('last_update,desc');
        $data = $skill->getList($where, array('bm' => 1));
        $res['list'] = !empty($data['list']) ? $data['list'] : [];

        $res['adList']  =  $this->ad_wxapp(array('class_id' => 514)); // 获取广告

        $categoryM = $this -> MODEL('skillcategory');
        $wheredata['keyid'] = array('>',0);
        $res['classlist']		=	$categoryM -> getSkillClassList($wheredata);

        foreach ($res['classlist']  as  $k  =>  $v){
            foreach ($res['list'] as $lv) {
                if($v['id'] == $lv['lesson']) {
                    $res['classlist'][$k]['lesson'][] = $lv;
                }
            }
        }

        if ($this->plat == 'mini'){
            // 小程序用seo
            $seo            =  $this->seo('skill','','','',false, true);
            $data['seo']    =  $seo;
        }

        $this->render_json(0, '', $res);
    }

    function info_action()
    {
        $id = $_POST['id'];

        $skill = $this->MODEL('skill');

        $info = $skill->getInfo(array('id' => $id), array('bm' => 1));

        // app用分享数据
        if ($this->plat == 'app'){
            
            $data['shareData']  =  array(
                'url'       =>  Url('wap',array('c'=>'skill','a'=>'show','id'=>$id)),
                'title'     =>  $info['title'],
                'summary'   =>  '',
                'imageUrl'  =>  $info['wap_bg_n']
            );
        }
        // 小程序用seo
        if ($this->plat == 'mini'){
            $seodata['skill_name']       =   $info['title'];
            $seodata['skill_desc']       =   strip_tags($info['introduce']);
            $this->data     =   $seodata;
            $seo            =  $this->seo('skill_show','','','',false, true);
            $data['seo']    =  $seo;
        }
        $data['info'] = $info;

        $this->render_json(0, '', $data);
    }

    function baoming_action()
    {
        $skill_id = !empty($_POST['name']) ? intval($_POST['skill_id']) : 0;
        $name = !empty($_POST['name']) ? trim($_POST['name']) : '';
        $tel = !empty($_POST['tel']) ? trim($_POST['tel']) : '';

        if (!$skill_id || !$name || !$tel) {
            $this->render_json(1, '参数错误');
        }

        $skill = $this->MODEL('skillbm');
        $result = $skill->addBaoming(array('skill_id' => $skill_id, 'name' => $name, 'tel' => $tel));

        $this->render_json($result['code'] == 9 ? 0 : 1, $result['msg']);
    }
}