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

class ltjob_controller extends com_controller
{
    function joblist_action()
    {
        $where = array(
            'uid'      => $this->member['uid'],
            'usertype' => $this->member['usertype']
        );
        
        $ltjobM		    =	$this -> MODEL('lietoujob');
        $data['total']  =   $ltjobM->getLtjobNum($where);
        
        $page   =  isset($_POST['page']) ? $_POST['page'] : 1;
        $limit  =  isset($_POST['limit']) ? $_POST['limit'] : 10;
        
        if ($page) {//分页
            
            $pagenav        =   ($page - 1) * $limit;
            $where['limit'] =   array($pagenav, $limit);
        } else {
            
            $where['limit'] =   $limit;
        }
        $where['orderby']  =  'lastupdate';
        
        $rows  =  $ltjobM->getList($where);
        
        $data['list']   =   count($rows) > 0 ? $rows : array();
        $data['statis'] =   $this->company_statis($this->member['uid'], $this->member['spid']);
        $this->render_json(1, 'ok', $data);
    }
    /**
     * 刷新猎头职位
     */
    function ltjobRefresh_action()
    {
        
        $ids    =   intval($_POST['ltjobid']);
        
        if (empty($ids)) {
            
            $this->render_json(1, '没有招聘中的职位');
        }
        
        $this->company_statis($this->member['uid'], $this->member['spid']);
        
        //检查是否达到每日最大操作次数
        $result     =   $this->day_check($this->member['uid'], 'refreshjob');
        if ($result['status'] != 1) {
            
            $this->render_json(2, $result['msg']);
        }

        $refresh['ltjobid']     =   $ids;
        $refresh['uid']         =   $this->member['uid'];
        $refresh['usertype']    =   $this->member['usertype'];
        $refresh['port']        =   $this->port;
        if (!empty($this->member['spid'])) {
            $refresh['spid']    =   $this->member['spid'];
        }
        
        $comtcM =   $this->MODEL('comtc');
        $return =   $comtcM->refresh_ltjob($refresh);
        
        if (isset($return['msg']) && !empty($return['msg'])) {
            
            $return['msg'] =    strip_tags($return['msg']);
        }
        if ($return['status'] == 1) {
            
            $this->render_json(0, $return['msg']);
        } else if ($return['status'] == 2) {
            
            $this->render_json(3, $return['msg'], $return);
        } else {
            
            $this->render_json(4, $return['msg']);
        }
    }
    // 猎头职位设置上架、下架
    function ltjobSet_action(){
        
        $ltjobM	=	$this -> MODEL('lietoujob');
        $logM	=	$this -> Model('log');
        
        if(!empty($_POST['id'])){
            if ($_POST['status'] == 0) {
                // 上架判断职位套餐数量
                $statisM    =   $this->Model('statis');
                $return     =   $statisM->getCom(array('uid' => $this->member['uid'], 'usertype' => $this->member['usertype'], 'upstatus' => 1, 'jobnum' => 1));
                if (!empty($return)) {
                    if ($return['errcode'] == 8) {

                        $this->render_json(8, $return['msg']);
                    }
                }

                $statis =   $this->company_statis($this->member['uid'], $this->member['spid']);
                if ($statis['rating_type'] == 1){

                    $statisM = $this->MODEL('statis');
                    $payDetail  =   '上架职位，消耗上架套餐数量：1';
                    $statisM->addStatisDetail(array('uid' => $this->uid, 'type' => 1, 'num' => 1, 'detail' => $payDetail, 'uri' => $_SERVER['REQUEST_URI']));
                }
            }

            $jobInfo=   $this->obj->select_once('lt_job', array('id' => intval($_POST['id'])),'`id`,`name`');
            $nid	=   $ltjobM->upInfo(array('id' => $_POST['id'], 'uid' => $this->member['uid']), array('zp_status' => intval($_POST['status'])));
            
            if ($nid){

                $logContent =   '职位更新：调整职位招聘状态';
                $logDetail  =   $_POST['status'] == 0 ? '调整高级职位《'.$jobInfo['name'].'》招聘状态：下架->上架' : '调整高级职位('.$jobInfo['name'].')招聘状态：上架->下架';
                $logM->addMemberLog($this->member['uid'], $this->member['usertype'], $logContent, 10, 2, $logDetail);

                $this->render_json(1, '设置成功');
            }else{

                $this->render_json(-1, '设置失败');
            }
        }else{

            $this->render_json(-1, '请选择要设置的职位');
        }
    }
    // 猎头职位删除
    function ltjobdel_action()
    {

        if ($_POST['id']){

            $jobInfo=   $this->obj->select_once('lt_job', array('id' => intval($_POST['id'])), '`id`,`name`');

            $ltJobM =   $this->MODEL('lietoujob');
            $return =   $ltJobM->delLietouJob($_POST['id'], array('uid' => $this->member['uid']));

            $logM       =   $this->MODEL('log');

            $logContent =   '职位管理：删除高级职位';
            $logDetail  =   '删除高级职位（ID：'.$_POST['id'].'，名称：'.$jobInfo['name'].'）';

            $logM->addMemberLog($this->member['uid'], $this->member['usertype'], $logContent, 10, 3, $logDetail);

            $this->render_json(0, $return['msg']);
        }else{

            $this->render_json(1, '参数错误！');
        }
    }
}