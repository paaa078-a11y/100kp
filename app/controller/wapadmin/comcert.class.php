<?php

/**
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2022 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */

class comcert_controller extends adminCommon
{
    function index_action()
    {
        if (!empty($_POST)) {

            $CompanyM   =   $this->MODEL('company');
            $where      =   array();

            $where['type']  =   3;

            $page       =   $_POST['page'];
            $limit      =   $_POST['limit'];

            if (isset($_POST['status'])) {
                $status = intval($_POST['status']);
                $where['status'] = $status == 3 ? 0 : $status;
            }

            $where['orderby']   =   array('status,asc', 'id,desc');

            if ($page) {

                $pagenav        =   ($page - 1) * $limit;
                $where['limit'] =   array($pagenav, $limit);
            } else {

                $where['limit'] =   $limit;
            }

            $list   =   $CompanyM->getCertList($where, array('utype' => 'comcert'));
            $data['list'] = count($list) ? $list : [];
            header('content-type:application/json; charset=utf-8');
            echo json_encode($data);
            die;
        }
        $this->yuntpl(array('wapadmin/comcert'));
    }

    function status_action()
    {

        $companyorder   =   $this->MODEL('companyorder');
        $companyM       =   $this->MODEL('company');

        if ($_POST['uid']) {

            $uid    =   $_POST['uid'];
            $status =   intval($_POST['status']);
            $comids =   @explode(',', $uid);

            if ($status != 1) {

                $yyzz_status    =   2;
            } else {

                $yyzz_status    =   1;

                // 如果是“审核通过”，判断之前是否有过“审核通过的记录”，没有则增加企业资质审核通过的积分（只有第一次审核通过才加积分）
                if (count($comids) > 1) {

                    $paywhere['com_id']     =   array('in', pylode(',', $comids));
                    $paywhere['pay_remark'] =   '认证企业资质';
                    $companypay             =   $companyorder->getPayList($paywhere, array('field' => 'com_id'));

                    foreach ($companypay as $k => $v) {
                        if (in_array($v, $uid)) {

                            unset($uid[$k]);
                        }
                    }
                    foreach ($uid as $v) {

                        $this->MODEL('integral')->invtalCheck($v, 2, 'integral_comcert', '认证企业资质');
                    }
                } elseif ($uid != '') {

                    $paywhere['com_id']     =   $uid;
                    $paywhere['pay_remark'] =   '认证企业资质';
                    $num                    =   $companyorder->getCompanyPayNum($paywhere);
                    if ($num < 1) {

                        $this->MODEL('integral')->invtalCheck($uid, 2, 'integral_comcert', '认证企业资质');
                    }
                }
            }

            $companycertData    =   array(

                'status'    =>  $_POST['status'],
                'statusbody'=>  $_POST['statusbody']
            );

            $id =   $companyM->upCertInfo(array('type' => '3', 'uid' => array('in', pylode(',', $uid))), $companycertData, array('utype' => 'admin'));

            // 审核通过时，职位免审核开启，管理勾选同步审核职位，未审核职位同步审核成功
            if ($this->config['com_free_status'] == '1' && $_POST['job_status'] && $yyzz_status == 1) {

                $jobM   =   $this->MODEL('job');
                $jobM->upInfo(array('state' => '1', 'r_status' => 1), array('state' => '0', 'uid' => array('in', pylode(',', $uid))));
                // 同步将企业设为已审核
                $companyData['r_status']    =   1;
            }

            // 修改审核状态
            $companyData['yyzz_status']     =   $yyzz_status;

            $companyM->upInfo($comids, '', $companyData);

            $ComA       =   $companyM->getList(array('uid' => array('in', pylode(',', $uid))), array('field' => 'uid,name,linkmail'));

            $company    =   $ComA['list'];

            if ($this->config['sy_email_set'] == '1') {
                if (is_array($company)) {

                    $notice =   $this->MODEL('notice');

                    foreach ($company as $v) {
                        if ($this->config['sy_email_comcert'] == '1' && $_POST['status'] > 0) {
                            if ($_POST['status'] == '1') {

                                $certinfo   =   '企业资质审核通过！';
                            } else {

                                $certinfo   =   '企业资质审核未通过！';
                            }
                            $notice->sendEmailType(array('email' => $v['linkmail'], 'certinfo' => $certinfo, 'comname' => $v['name'], 'uid' => $v['uid'], 'name' => $v['name'], 'type' => 'comcert'));
                        }
                    }
                }
            }

            /* 消息前缀 */
            foreach ($company as $v) {

                $uids[] =   $v['uid'];

                /* 处理审核信息 */
                if ($_POST['status'] == 2) {
                    $statusInfo     =   '很遗憾 , 贵公司企业资质未能通过审核';
                    if ($_POST['statusbody']) {
                        $statusInfo .=  ' , 原因：' . $_POST['statusbody'];
                    }
                    $msg[$v['uid']] = $statusInfo;
                } elseif ($_POST['status'] == 1) {

                    $msg[$v['uid']] =   '贵公司企业资质审核通过，招聘人才更轻松！';
                }
            }
            //发送系统通知
            $sysmsgM    =   $this->MODEL('sysmsg');
            $sysmsgM->addInfo(array('uid' => $uids, 'usertype' => 2, 'content' => $msg));
            if ($id) {

                $return['msg']      =   '企业资质审核(UID:' . $uid . ')设置成功！';
                $return['error']    =   9;
            } else {

                $return['msg']      =   '设置失败';
                $return['error']    =   8;
            }
        } else {

            $return['msg']      =   '非法操作！';
            $return['error']    =   8;
        }
        $this->layer_msg($return['msg'], 9, 0, '', '', $return['error']);
    }

    function del_action()
    {

        $CompanyM = $this->MODEL('company');

        if (intval($_GET['id'])) {

            $company    =   $CompanyM->getCertInfo(array('id' => intval($_GET['id']), 'type' => '3'), array('field' => '`uid`'));
            $CompanyM->upInfo($company['uid'], '', array('yyzz_status' => '0'));
            $delid      =   $CompanyM->delCert(intval($_GET['id']), array('type' => '3'));
            $delid ? $this->layer_msg($delid['msg'], $delid['errcode'], $delid['layertype'], 'index.php?c=comcert') : $this->layer_msg($delid['msg'], $delid['errcode'], $delid['layertype'], $_SERVER['HTTP_REFERER']);
        }
    }
}

?>