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

class skillbm_model extends model
{
    function getList($whereData, $data = array())
    {
        $skill = array();
        $field = $data['field'] ? $data['field'] : '*';
        $List = $this->select_all('skill_bm', $whereData, $field);

        if (!empty($List)) {
            $bmIds = array();
            $skillids = array();
            foreach ($List  as  $k  =>  $v){
                $bmIds[] = $v['id'];
                if($v['skill_id'] && !in_array($v['skill_id'],$skillids)){
                    $skillids[]=$v['skill_id'];
                }
            }
            if ($data['utype'] == 'admin') {
                $this->update_once('skill_bm', array('isread' => 1), array('id' => array('in', pylode(',', $bmIds))));
            }

            require_once ('skill.model.php');
            $skillM = new skill_model($this->db, $this->def);
            $skills = $skillM->getList(array('id' => array('in', pylode(',', $skillids))));

            $skillData = array();
            foreach ($skills['list'] as $v) {
                $skillData[$v['id']] = $v;
            }
            foreach ($List as $k => $v){
                $List[$k]['skill_name']  =  (isset($skillData[$v['skill_id']]['title']) && $skillData[$v['skill_id']]['title']) ? $skillData[$v['skill_id']]['title'] : '-';
                !empty($v['time']) && $List[$k]['time_n'] = date('Y-m-d H:i', $v['time']);
            }
        }
        $skill['list'] = $List;

        return $skill;
    }

    function getInfo($whereData = array(), $data = array())
    {
        $data['field'] = empty($data['field']) ? '*' : $data['field'];
        $Info = $this->select_once('skill_bm', $whereData, $data['field']);
        return $Info;
    }

    function addBaoming($data = array())
    {
        $return['code'] = 8;
        if($data) {
             $data['time'] = time();
             $data['isread'] = 0;

            // 查询当前课程是否已经报名过
            $bmInfo = $this->select_once('skill_bm', array('skill_id' => $data['skill_id'], 'tel' => $data['tel']), 'id');
            if ($bmInfo) {
                $return['msg'] = '已报名过该课程！';
                return	$return;
            }

            $id = $this->insert_into('skill_bm', $data);

            if (!empty($id)) {

                $return['msg'] = '报名成功！';

                $return['code'] = 9;

                return	$return;
            } else {

                $return['msg'] = '提交失败，请重新报名课程！';

                return	$return;
            }

        }
    }

    function del($del)
    {
        $return = array();
        if (!empty($del)) {
            if (is_array($del)) {
                $delid = pylode(',', $del);
                $return['layertype'] = 1;
            } else {
                $return['layertype'] = 0;
                $delid = $del;
            }
            $result =   $this->delete_all('skill_bm', array('id' => array('in', $delid)),'');
            $return['errcode']  =   $result ? 9 : 8;
            $return['msg']      =   $result ? '报名记录（ID：' .  $delid. '）删除成功' : '报名记录删除失败';
        } else {
            $return['msg']      =   '请选择您要删除的报名记录';
            $return['errcode']  =   '8';
        }
        return $return;
    }

    public function getskillList( $whereData , $data=array()) {
        $select = isset($data['field']) ? $data['field'] : '*';
        $list = $this->select_all('skill', $whereData, $select);

        return $list;
    }

}