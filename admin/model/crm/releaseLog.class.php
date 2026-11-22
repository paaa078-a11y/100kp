<?php

/**
 * Class releaseLog_controller
 *
 * @author shiy
 * @version 7.0
 */

class releaseLog_controller extends adminCommon
{

    function getReleaseLog_action()
    {

        $crmM   =   $this->MODEL('crm');

        $where  =   array();

        if ($_POST['crm_uid']) {

            $where['auid']  =   $_POST['crm_uid'];
        }

        $keywordStr =   trim($_POST['keyword']);
        if (isset($keywordStr) && !empty($keywordStr)) {

            $comM   =   $this->MODEL('company');
            $comArr =   $comM->getList(array('name' => array('like', trim($_POST['keyword']))), array('field' => 'uid'));

            $uidS   =   array();
            foreach ($comArr['list'] as $k => $v) {
                $uidS[]     =   $v['uid'];
            }

            $where['uid']   =   array('in', pylode(',', $uidS));
        }

        if (isset($_POST['time']) && !empty($_POST['time'])) {

            $start  =   date('Y-m-d', $_POST['time'][0] / 1000);
            $end    =   date('Y-m-d', $_POST['time'][1] / 1000);

            $where['PHPYUNBTWSTART_A']  =   '';
            $where['ctime'][]           =   array('>=', strtotime($start . " 00:00:00"));
            $where['ctime'][]           =   array('<=', strtotime($end . " 23:59:59"));
            $where['PHPYUNBTWEND_A']    =   '';
        }

        $page   =   !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize   =   !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->adminPageList('crm_release_log', $where, $page, array('limit' => $pageSize));

        $list   =   array();
        $total  =   intval($pages['total']);
        if ($total > 0) {
            if (isset($_POST['order'])) {

                $where['orderby'] = $_POST['t'] . ', ' . $_POST['order'];
            } else {

                $where['orderby'] = array('ctime,desc','id,desc');
            }
            $where['limit'] = $pages['limit'];
            $list   =   $crmM->getReleaseLogList($where);
        }

        $result     =   array(

            'list'      =>  $list,
            'total'     =>  intval($total),
            'pageSize'  =>  $pageSize,
            'pageSizes' =>  $pages['page_sizes'],
        );
        $this->render_json(0, '', $result);
    }
    function getIndexStaticData_action(){
        $adminM     =   $this->MODEL('admin');
        $crmUser    =   $adminM->getList(array('did'=>0,'is_crm'=>1), array('field' => '`uid`,`name`'));
        $data['crmUser']          =  $crmUser;
        
        $this->render_json(0, 'ok', $data);
    }
    function delReleaseLog_action()
    {

        $crmM   =   $this->MODEL('crm');
        $delId  =   $_POST['id'];
        if (!empty($delId)) {

            $return =   $crmM->delReleaseLog($delId);

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