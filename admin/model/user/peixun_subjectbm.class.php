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

class peixun_subjectbm_controller extends adminCommon
{
    //时间区间
    public $timeSection = array(
        '1' => '今天',
        '3' => '最近三天',
        '7' => '最近七天',
        '15' => '最近半月',
        '30' => '最近一个月'
    );

    //设置高级搜索功能
    public function set_search()
    {
        $search_list[] = array(
            'param' => 'time',
            'name' => '报名时间',
            'value' => $this->timeSection
        );
        $search_list[] = array(
            'param' => 'isprice',
            'name' => '收费方式',
            'value' => array(
                1 => '在线收费',
                2 => '到场收费'
            )
        );

        return $search_list;
    }

    /**
     * 会员 - 培训 - 课程报名
     * 2019-06-05 hjy
     */
    public function index_action()
    {


        $bmWhereData = array();

        $keywordStr = trim($_POST['keyword']);
        $typeStr = trim($_POST['type']);

        //客户名称条件
        if (!empty($keywordStr) && $typeStr == 1) {
            $bmWhereData['name'] = array('like', $keywordStr);
        }

        //报名时间条件
        $endStr = intval($_POST['time']);
        if (!empty($endStr)) {
            if ($endStr == 1) {
                $bmWhereData['ctime'] = array('>=', strtotime(date("Y-m-d")));
            } else {
                $bmWhereData['ctime'] = array('>=', strtotime('-' . $endStr . 'day'));
            }
        }

        $pxM = $this->MODEL('train');
        $comids = $subWhereData = array();
        //课程名称条件
        if (!empty($keywordStr) && $typeStr == 2) {
            $subWhereData['name'] = array('like', $keywordStr);
        }
        //收费方式条件
        $ispriceStr = intval($_POST['isprice']);
        if (!empty($ispriceStr)) {
            $subWhereData['isprice'] = array('=', $ispriceStr);
        }
        if (!empty($subWhereData)) {
            $pxsub = $pxM->getSubList($subWhereData, array('field' => '`id`'));
            if (!empty($pxsub)) {
                foreach ($pxsub as $val) {
                    $comids[] = $val['id'];
                }
            } else {
                $comids = array(0);
            }
        }
        if (!empty($comids)) {
            $bmWhereData['sid'] = array('in', pylode(',', $comids));
        }

        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('px_baoming', $bmWhereData, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        //分页数大于0的情况下 执行列表查询
        $list = array();
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if (!empty($_POST['order']) && !empty($_POST['t'])) {
                $bmWhereData['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $bmWhereData['orderby'] = 'id';
            }

            $bmWhereData['limit'] = $pages['limit'];
            $list = $pxM->getBmList($bmWhereData, array('scene' => 'detail'));

            if ($list) {
                //补充进相关信息
                foreach ($list as $Lk => $Lv) {
                    $list[$Lk]['subject_url'] = Url('train', array('c' => 'subshow', 'id' => $Lv['sid']));
                    $list[$Lk]['train_url'] = Url('train', array('c' => 'agencyshow', 'id' => $Lv['s_uid']));
                }
            }
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }
    public function  getSearchData_action(){
        $search_list = $this->set_search();
        $this->render_json(0,'',compact('search_list'));
    }
    // 获取报名信息
    public function getInfo_action()
    {
        if (empty($_POST['id'])) {
            $this->render_json(1, '参数错误');
        }

        $id = intval($_POST['id']);

        $pxM = $this->MODEL('train');
        $companyM = $this->MODEL('company');
        $info = $pxM->getBmInfo(array('id' => array('=', $id)));
        $subject = $pxM->getSubInfo(array('id' => array('=', $info['sid'])), array('field' => '`name`,`price`,`isprice`'));
        $train = $pxM->getInfo(array('uid' => array('=', $info['s_uid'])), array('field' => '`name`'));
        $info['trainname'] = $train['name'];
        $orderField = '`id`,`sid`,`order_state`,`order_id`,`order_price`';
        $order = $companyM->getOrderInfo(array('sid' => array('=', $id), 'type' => array('=', 6)), array('field' => $orderField));

        /* @var $arr_data */
        include(APP_PATH . "/config/db.data.php");
        $order['order_state_n'] = $arr_data['paystate'][$order['order_state']];

        $this->render_json(0, 'ok', compact('order', 'subject', 'info'));
    }

    /**
     * 会员 - 培训 - 课程报名
     * 确认收费
     * 2019-06-05 hjy
     */
    public function setpay_action()
    {
        $del = intval($_POST['id']);
        if (empty($del)) {
            $this->render_json(1, '参数错误');
        }

        $companyM = $this->MODEL('company');
        $row = $companyM->getOrderInfo(array('sid' => array('=', $del)));
        if ($row['order_state'] == 1 || $row['order_state'] == 3) {
            $nid = $this->MODEL('qrorder')->upuser_statis($row);
            if ($nid) {
                $this->admin_json(0, '订单记录(ID:' . $del . ')确认成功');
            } else {
                $this->render_json(1, '确认失败,请销后再试');
            }
        } else {
            $this->render_json(1, '订单已确认，请勿重复操作');
        }
    }

    /**
     * 会员 - 培训 - 课程报名
     * 删除课程报名
     * 2019-06-05 hjy
     */
    public function del_action()
    {
        if (empty($_POST['del'])) {
            $this->render_json(1, '参数错误');
        }

        $del = $_POST['del'];
        $ids = pylode(',', $del);

        $pxM = $this->MODEL('train');
        $delRes = $pxM->delBm(array('id' => $ids));

        if ($delRes['errcode'] == 9) {
            $this->admin_json(0, $delRes['msg']);
        } else {
            $this->render_json(1, $delRes['msg']);
        }
    }
}

?>