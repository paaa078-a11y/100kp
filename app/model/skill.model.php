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
class skill_model extends model
{
    function getList($whereData, $data = array())
    {
        $skill = array();
        $field = $data['field'] ? $data['field'] : '*';
        $List = $this->select_all('skill', $whereData, $field);

        if ($List) {
            foreach($List as $key => $val){
                $id[]	=	$val['id'];
            }
            foreach ($List as $key => $val) {
                $List[$key]['welfare_n'] = explode(',', $val['welfare']);
                $List[$key]['cover_n'] = checkpic($val['cover']);
                $List[$key]['wap_bg_n'] = checkpic($val['cover']);
                // 查询报名数
                $List[$key]['bm_num'] = $this->select_num('skill_bm', array('skill_id' => $val['id'])) + $val['bnum'];

                !empty($val['addtime']) && $List[$key]['addtime_n'] = date('Y-m-d H:i', $val['addtime']);
                !empty($val['last_update']) && $List[$key]['last_update_n'] = date('Y-m-d H:i', $val['last_update']);
            }
        }

        require_once('skillcategory.model.php');
        $skillcategory            =   new skillcategory_model($this->db, $this->def);
        $class			    =	$skillcategory -> getSkillClassList(array('keyid'=>'1'));
        foreach ($class  as  $k  =>  $v){
            foreach ($List as $lv) {
                if($v['id'] == $lv['lesson']) {
                    $class[$k]['id'] = $lv['lesson'];
                }
            }
        }

        $skill['list'] = $List;
        return $skill;
    }

    function upskill($whereData = array(), $upData = array(), $data = array())
    {

        if (!empty($upData) && !empty($whereData)) {
            $nid = $this->update_once('skill', $upData, $whereData);
        }
        return $nid;
    }

    function getInfo($whereData = array(), $data = array())
    {
        $data['field'] = empty($data['field']) ? '*' : $data['field'];
        $Info = $this->select_once('skill', $whereData, $data['field']);
        
        if (!empty($Info['cover'])){
            $Info['cover_n'] = checkpic($Info['cover']);
            $Info['wap_bg_n'] = checkpic($Info['cover']);
        }
        if (!empty($Info['wx_qr_code'])){
            $Info['wx_qr_code_n'] = checkpic($Info['wx_qr_code']);
        }
        if (!empty($Info['welfare'])){
            $Info['welfare_n'] = explode(',', $Info['welfare']);
        }
        if (!empty($data['bm'])) {// 查询报名数
            $Info['bm_num'] = $this->select_num('skill_bm', array('skill_id' => $Info['id']))+ $Info['bnum'];
        }

        return $Info;
    }

    function addskill($setData)
    {
        $return['error'] = 1;
        if (empty($setData)) {
            $return['msg'] = '缺少参数';
            return $return;
        }

        $id = !empty($setData['id']) ? intval($setData['id']) : 0;
        $timeStr = time();
        $setData['last_update'] = $timeStr;
        $setData['welfare'] = trim(implode(',', $setData['welfare']));

        //根据id来判断是新增还是修改
        if (empty($id)) {
            $setData['addtime'] = $timeStr;
            $nbid = $this->insert_into('skill', $setData);

            if ($nbid) {
                $return['error'] = 0;
                $return['msg'] = '课程（ID：' . $nbid . '）添加成功';
            } else {
                $return['msg'] = '课程添加失败';
            }
        } else {
            $nbid = $this->upskill(array('id' => $id), $setData);
            if ($nbid) {
                $return['error'] = 0;
                $return['msg'] = '课程（ID：' . $id . '）修改成功';
            } else {
                $return['msg'] = '课程修改失败';
            }
        }
        return $return;
    }

    function del($del)
    {
        $return = array();
        if (!empty($del)) {
            if (is_array($del)) {
                $delid = pylode(',', $del);
            } else {
                $delid = $del;
            }
            $result =   $this->delete_all('skill', array('id' => array('in', $delid)),'');
            $this->delete_all('skill_bm', array('skill_id' => array('in', $delid)),'');
            $return['errcode']  =   $result ? 0 : 1;
            $return['msg']      =   $result ? '删除课程（ID：' . $delid . '）成功' : '删除课程失败';
        } else {
            $return['msg']      =   '请选择您要删除的课程';
            $return['errcode']  =   1;
        }
        return $return;
    }

    function jobNum()
    {
        $jobAllNum      =   $this->select_num('skill');
        if ($jobAllNum > 0) {
            $arr['jobAllNum']       =   $jobAllNum;
        }
        return json_encode($arr);
    }
}