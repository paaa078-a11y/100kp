<?php

/**
 * Class config_controller
 *
 * @author shiy
 * @version 7.0
 */

class config_controller extends adminCommon
{

    /*
     * 管理员 CRM设置 基础配置
     */
    function baseConfig_action()
    {

        $ratingM    =   $this->MODEL('rating');
        $ratingList =   $ratingM->getList(array('category' => '1', 'orderby' => array('sort,desc')), array('field' => '`id`,`name`'));
        $base       =   array('sy_crm_duty' => $this->config['sy_crm_duty'], 'sy_free_id' => $this->config['sy_free_id'], 'sy_crm_link' => $this->config['sy_crm_link'], 'sy_crm_hour' => $this->config['sy_crm_hour'], 'sy_crm_deal_open' => $this->config['sy_crm_deal_open'], 'sy_crm_receive_grant' => $this->config['sy_crm_receive_grant'], 'crm_deal_rating' => $this->config['crm_deal_rating'], 'crm_jobtai_gyx_cycle' => $this->config['crm_jobtai_gyx_cycle'], 'crm_jobtai_hy_cycle' => $this->config['crm_jobtai_hy_cycle'], 'crm_jobtai_jgq_cycle' => $this->config['crm_jobtai_jgq_cycle'], 'crm_jobtai_qz_cycle' => $this->config['crm_jobtai_qz_cycle'], 'sy_crmrecshare_logo' => $this->config['sy_crmrecshare_logo']);
        $base['crm_admin_type'] = $this->config['crm_admin_type'] ? explode(',', $this->config['crm_admin_type']) : [];
        $base['workStartTime'] = '';
        $base['workEndTime'] = '';
        $base['restStartTime'] = '';
        $base['restEndTime'] = '';
        if ($this->config['crm_work_time']) {
            $worktime = explode('~', $this->config['crm_work_time']);
            $base['workStartTime'] = $worktime[0] ? $worktime[0] : '';
            $base['workEndTime'] = $worktime[1] ? $worktime[1] : '';
        }
        if ($this->config['crm_rest_time']) {
            $resttime = explode('~', $this->config['crm_rest_time']);
            $base['restStartTime'] = $resttime[0] ? $resttime[0] : '';
            $base['restEndTime'] = $resttime[1] ? $resttime[1] : '';
        }
        $adminM = $this->MODEL('admin');
        $adminTypes = $adminM->getAdminGroupList(array('did' => $this->config['did'], 'orderby' => 'id'), array('utype' => 'admin'));
        foreach ($adminTypes as &$v) {
            $v['group_type_n'] = $v['group_type'] ==  1 ? '普通分组' : '分站管理组';
        }

        $this->render_json(0, '', array(
            'base' => $base,
            'ratingList' => $ratingList,
            'picMaxSize' => $this->config['pic_maxsize'] ? $this->config['pic_maxsize'] : 5,
            'picType' => $this->config['pic_type'] ? $this->config['pic_type'] : 'jpg,png,jpeg,bmp,gif',
            'picPreUrl' => $this->config['sy_ossurl'],
            'adminTypes' => $adminTypes
        ));
    }

    /*
     * 管理员 CRM设置 提交基础配置
     */
    function saveConfig_action()
    {

        $configM    =   $this->MODEL('config');
        $uploadM    =   $this->MODEL('upload');
        if (isset($_POST['baseConfig']) && !empty($_POST['baseConfig'])){
            $configData =   array(

                'sy_crm_duty'   =>  $_POST['sy_crm_duty'],
                'sy_free_id'    =>  $_POST['sy_free_id'],
                'sy_crm_link'   =>  $_POST['sy_crm_link'],
                'sy_crm_hour'   =>  $_POST['sy_crm_hour'],
                'sy_crm_deal_open'  =>  $_POST['sy_crm_deal_open'],
                'sy_crm_receive_grant'  =>  $_POST['sy_crm_receive_grant'],
                'crm_jobtai_gyx_cycle'  =>  $_POST['crm_jobtai_gyx_cycle'],
                'crm_jobtai_hy_cycle'  =>  $_POST['crm_jobtai_hy_cycle'],
                'crm_jobtai_jgq_cycle'  =>  $_POST['crm_jobtai_jgq_cycle'],
                'crm_jobtai_qz_cycle'  =>  $_POST['crm_jobtai_qz_cycle'],
                'crm_deal_rating'   =>  $_POST['crm_deal_rating'],
                'crm_admin_type'   =>  $_POST['crm_admin_type'],
                'crm_work_time' => $_POST['worktime'] && $_POST['worktime'] != 'null' ? implode('~', explode(',', $_POST['worktime'])) : '',
                'crm_rest_time' => $_POST['resttime'] && $_POST['resttime'] != 'null' ? implode('~', explode(',', $_POST['resttime'])) : '',
            );
            if ($_FILES['logoFile']['tmp_name'] != '') {
                $upArr  =   array(

                    'file'  =>  $_FILES['logoFile'],
                    'dir'   =>  'crm'
                );
                $pic    =   $uploadM->newUpload($upArr);

                if (!empty($pic['msg'])) {

                    $this->render_json(1, $pic['msg']);
                } elseif (!empty($pic['picurl'])) {

                    $configData['sy_recruit_wx_code'] = $pic['picurl'];
                }
            }
            if ($_FILES['photoFile']['tmp_name'] != '') {
                $upArr  =   array(

                    'file'  =>  $_FILES['photoFile'],
                    'dir'   =>  'recruit'
                );
                $pic    =   $uploadM->newUpload($upArr);
                if (!empty($pic['msg'])) {

                    $this->render_json(1, $pic['msg']);
                } elseif (!empty($pic['picurl'])) {

                    $configData['sy_crmrecshare_logo']  =   $pic['picurl'];
                }
            }
            $configM->setConfig($configData);
            $this->web_config();

            $msg    =   'CRM基础配置保存成功';
            $this->admin_json(0, $msg);
        }else{

            $msg    =   '操作异常，请稍候重试！';
            $this->render_json(1, $msg);
        }
    }

    /*
     * 管理员 CRM设置 规则配置
     */
    function ruleConfig_action()
    {

        $crmM   =   $this->MODEL('crm');
        $list   =   $crmM->getCrmsetList();

        $ratingId   =   '';
        if (!empty($list)) {
            foreach ($list as $v) {
                if ($ratingId == '') {

                    $ratingId = $v['rating'];
                } else {

                    $ratingId .= ",".$v['rating'];
                }
            }
        }

        $ratingIdArr=   @explode(',', $ratingId);
        $expiredVip =   !in_array('999', $ratingIdArr) ? 1 : 2;

        $ratingM    =   $this->MODEL('rating');
        if ($ratingId != '') {
            $ratingList = $ratingM->getList(array('id' => array('notin', $ratingId), 'category' => '1', 'orderby' => array('sort,desc')), array('field' => '`id`,`name`'));
            $add    = !empty($ratingList) ? 1 : 2;
        } else {
            $ratingList = $ratingM->getList(array('category' => '1', 'orderby' => array('sort,desc')), array('field' => '`id`,`name`'));
            $add    =   1;
        }

        $this->render_json(0, '', array('list' => $list, 'add' => $add, 'expiredVip' => $expiredVip, 'ratingList' => $ratingList));
    }

    /*
     * 管理员 CRM设置 删除规则配置
     */
    function delRule_action()
    {

        if ($_POST['id']) {

            $crmM   =   $this->MODEL('crm');
            $return =   $crmM->delCrmset($_POST['id']);
            if ($return['errcode'] == '9') {

                $this->admin_json(0, $return['msg']);
            } else {

                $this->render_json(1, $return['msg']);
            }
        } else {

            $this->render_json(1, '参数异常，请重试！');
        }
    }

    /*
     * 管理员 CRM设置 提交规则配置
     */
    function saveRule_action()
    {
        $value  =   array(

            'rating'        =>  $_POST['ratingIdArr'] ? pylode(',', $_POST['ratingIdArr']) : '',
            'follow_day'    =>  $_POST['follow_day'] ? $_POST['follow_day'] : 0,
            'release_day'   =>  $_POST['release_day'] ? $_POST['release_day'] : 0,
            'deal_day'      =>  $_POST['deal_day'] ? $_POST['deal_day'] : 0,
            'claim_day'     =>  $_POST['claim_day'] ? $_POST['claim_day'] : 0,
            'bh_day'        =>  $_POST['bh_day'] ? $_POST['bh_day'] : 0
        );

        $crmM   =   $this->MODEL('crm');

        $data   =   array();
        if (isset($_POST['id']) && intval($_POST['id']) > 0){

            $data['id'] =   $_POST['id'];
        }

        $data['value']  =   $value;

        $return =   $crmM->addCrmset($data);

        if ($return['errcode'] == '9'){

            $this->admin_json(0, $return['msg']);
        }else{

            $this->render_json(1, $return['msg']);
        }
    }
}