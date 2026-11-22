<?php

/**
 * Class followLog_controller
 *
 * @author shiy
 * @version 7.0
 */

class followLog_controller extends adminCommon
{

    function getFollowLog_action()
    {

        $crmM   =   $this->MODEL('crm');

        $where  =   array();

        if (isset($_POST['crm_uid']) && intval($_POST['crm_uid']) > 0) {

            $where['auid']  =   $_POST['crm_uid'];
        }else if ($_POST['crm_uid'] == 'all') {                        // 全部下属
            $where['PHPYUNBTWSTART_W']  =   '';
            if (isset($where['auid'])){
                unset($where['auid']);
            }
            $where['auid'][]         =   array('>', 0, '');
            $where['auid'][]         =   array('in', $_POST['orgUids'], '');
            $where['PHPYUNBTWEND_W']    =   '';
        }
        if (isset($_POST['follow_type']) && !empty($_POST['follow_type'])) {

            $where['type']  =   $_POST['follow_type'];
        }
        $keywordStr =   trim($_POST['keyword']);
        $typeStr    =   intval($_POST['type']);
        if (isset($keywordStr) && !empty($keywordStr)) {

            if ($typeStr == 1){

                $comM   =   $this->MODEL('company');
                $comArr =   $comM->getList(array('name' => array('like', trim($_POST['keyword']))), array('field' => 'uid'));

                $uidS   =   array();
                foreach ($comArr['list'] as $k => $v) {
                    $uidS[]     =   $v['uid'];
                }

                $where['uid']   =   array('in', pylode(',', $uidS));
            }else if ($typeStr == 2){

                $where['content']   =   array('like', $keywordStr);
            }
        }

        if (isset($_POST['time']) && !empty($_POST['time'])) {

            $start  =   date('Y-m-d', $_POST['time'][0] / 1000);
            $end    =   date('Y-m-d', $_POST['time'][1] / 1000);

            $where['PHPYUNBTWSTART_A']  =   '';
            $where['ftime'][]           =   array('>=', strtotime($start . " 00:00:00"));
            $where['ftime'][]           =   array('<=', strtotime($end . " 23:59:59"));
            $where['PHPYUNBTWEND_A']    =   '';
        }

        $page   =   !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize   =   !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);

        $pageM  =   $this->MODEL('page');
        $pages  =   $pageM->adminPageList('crmnew_concern', $where, $page, array('limit' => $pageSize));

        $list   =   array();
        $total  =   intval($pages['total']);
        if ($total > 0) {
            if (isset($_POST['order'])) {

                $where['orderby'] = $_POST['t'].', '.$_POST['order'];
            } else {

                $where['orderby'] = 'id,desc';
            }
            $where['limit'] = $pages['limit'];
            $list = $crmM->getConcernList($where, array('utype' => 'crm'));
        }

        $result =   array(

            'list'      =>  $list,
            'total'     =>  intval($total),
            'pageSize'  =>  $pageSize,
            'pageSizes' =>  $pages['page_sizes']
        );
        $this->render_json(0, '', $result);
    }

    function getFollowStaticData_action()
    {

        $adminM     =   $this->MODEL('admin');
        $crmUser    =   $adminM->getList(array('did'=>0,'is_crm'=>1), array('field' => '`uid`,`name`'));

        $cacheM     =   $this->MODEL('cache');
        $cache      =   $cacheM->GetCache(array('crm'));

        $followType =   $cache['crmdata']['follow_way'];
        $followWay  =   array();
        foreach ($followType as $v) {
            $followWay[] = array('id' => $v, 'name' => $cache['crmclass_name'][$v]);
        }

        $data['followType'] =   $followWay;
        $data['crmUser']    =   $crmUser;

        $data['contactOssUrl']  =   $this->config['sy_outcall_oss'];
        $this->render_json(0, 'ok', $data);
    }

    function delFollowLog_action()
    {

        $crmM   =   $this->MODEL('crm');
        $delId  =   $_POST['id'];
        if (!empty($delId)) {

            $return =   $crmM->delConcern($delId);

            if ($return['errcode'] == '9') {

                $this->admin_json(0, $return['msg']);
            } else {

                $this->render_json(1, $return['msg']);
            }
        } else {

            $this->render_json(1, '参数错误，请重试！');
        }
    }

}