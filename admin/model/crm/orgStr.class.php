<?php

/**
 * Class orgStr_controller
 *
 * @author shiy
 * @version 7.0
 */

class orgStr_controller extends adminCommon
{

    //  组织架构
    public function index_action()
    {

        $crmM   =   $this->MODEL('crm');
        $orgList=   $crmM->getOrgList(array('orderby' => 'sort,desc'), array('type' => 'org'));
        $this->render_json(0, '', $orgList);
    }

    //  变更部门名称
    public function upOrgName_action()
    {

        $id     =   intval($_POST['id']);
        $name   =   trim($_POST['name']);

        $crmM   =   $this->MODEL('crm');
        $return	=	$crmM->upCrmOrg($id,array('name' => $name));

        if ($return['errcode'] == '9'){

            $this->admin_json(0, '部门名称变更成功！');
        }else{

            $this->render_json(1, '操作失败，请稍候重试！');
        }
    }

    //  删除组织架构
    public function delOrg_action()
    {

        $crmM   =   $this->MODEL('crm');
        $delId  =   $_POST['id'];
        if (!empty($delId)) {

            $return =   $crmM->delOrg($delId);

            if ($return['errcode'] == 9) {

                $this->admin_json(0, $return['msg']);
            } else {

                $this->render_json(1, $return['msg']);
            }
        } else {

            $this->render_json(1, '参数错误，请重试！');
        }
    }

    //  新增子部门
    public function addOrg_action()
    {

        $_POST      =   $this->post_trim($_POST);
        $nameArr    =   explode('-', trim($_POST['name']));
        $name       =   array();
        foreach ($nameArr as $k => $v) {
            $value  =   trim($v);
            if (!empty($value)) {

                $name[] =   $value;
            }
        }
        if (empty($name)){

            $this->render_json(1, '请先填写子部门名称！');
        }

        $fid    =   intval($_POST['fid']);
        $level  =   intval($_POST['level']);

        $crmM   =   $this->MODEL('crm');

        $orgList=   $crmM->getOrgList(array('name' => array('in',"'".@implode("','",$name)."'")));

        $nameArr=   array();
        if (!empty($orgList)) {
            foreach ($orgList as $ok => $ov) {

                $nameArr[]  =   $ov['name'];
            }
        }
        if ($nameArr) {
            $this->render_json(1,'部门' . pylode(',', $nameArr) . '已存在！');
        }
        $orgAdd     =   array();
        $newNameArr =   array();
        foreach ($name as $key => $val) {
            if (!in_array($val, $nameArr)) {
                $newNameArr[]   =   $val;
                $orgAdd[$key]['name']   =   $val;
                $orgAdd[$key]['fid']    =   $fid;
                $orgAdd[$key]['level']  =   $level+1;
                $orgAdd[$key]['sort']   =   0;
            }
        }
        if (!empty($orgAdd)){

            $value  =   array_values($orgAdd);
            $this->obj->DB_insert_multi('crmorg', $value);

            $this->admin_json(0, '新部门（'.implode('，', $newNameArr).'）添加成功！');
        }else{

            $this->render_json(1,'操作失败，请稍候重试！');
        }
    }

    //  部门成员
    public function getUserList_action()
    {

        $crmM   =   $this->MODEL('crm');
        $orgInfo=   $crmM->getOrgInfo(array('id'  =>  $_POST['orgId']));

        $orgUser=   $orgInfo['musers'];
        if (!empty($orgUser)){
            foreach ($orgUser as $k => $v){

                $orgUser[$k]['power_n'] =   $v['power'] == 1 ? true : false;
                $orgUser[$k]['spower_n']=   $v['spower'] == 1 ? true : false;
            }
        }

        $data   =   array(
            'orgUser'   =>  $orgUser,
            'orgUid'    =>  $orgInfo['muserids'] ? $orgInfo['muserids'] : [],
            'allUser'   =>  $orgInfo['ausers']
        );
        $this->render_json(0, '', $data);
    }

    //  部门成员调配
    public function saveOrgUser_action()
    {

        $uids   =   $_POST['uid'];
        $orgId  =   intval($_POST['orgId']);

        if (!empty($uids) && !empty($orgId)) {

            $crmM = $this->MODEL('crm');
            $code = $crmM->configCrmOrg(array('id' => $orgId, 'uids' => $uids));

            if ($code == 1){

                $this->admin_json(0, '部门（ID：'.$orgId.'）成员（UID：'.pylode('，', $uids).'）调配成功！');
            }else{

                $this->render_json(1, '操作失败，请稍候重试！');
            }
        }else{

            $this->render_json(1, '参数错误，请稍候重试！');
        }
    }

    //  设置部门成员权限
    public function setPower_action()
    {

        $uid    =   intval($_POST['uid']);
        $typeStr=   trim($_POST['type']);
        $power  =   intval($_POST['power']);

        if ($typeStr == 'power'){

            $data   =   array('power' => $power);
            $msg    =   $power == 1 ? '部门权限授权' : '部门权限回收';
        }elseif ($typeStr == 'spower'){

            $data   =   array('spower' => $power);
            $msg    =   $power == 1 ? '子部门权限授权' : '子部门权限回收';
        }

        $crmM   =   $this->MODEL('crm');
        $code   =   $crmM->configCrmPower($uid, $data);

        if ($code == 1){

            $this->admin_json(0, '成员（UID：'.$uid.'）'.$msg.'成功！');
        }else{

            $this->render_json(1, '权限操作失败，请稍候重试！');
        }
    }

    //  删除部门成员
    public function delOrgUser_action()
    {

        $crmM   =   $this->MODEL('crm');
        $delId  =   $_POST['uid'];
        if (!empty($delId)) {

            $return =   $crmM->delOrgUser($delId);

            if ($return['errcode'] == 9) {

                $this->admin_json(0, $return['msg']);
            } else {

                $this->render_json(1, $return['msg']);
            }
        } else {

            $this->render_json(1, '参数错误，请重试！');
        }
    }

}