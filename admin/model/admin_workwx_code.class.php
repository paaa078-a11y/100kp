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

class admin_workwx_code_controller extends adminCommon
{

    function index_action()
    {

        $where  = array();

        $keyStr =   trim($_GET['keyword']);
        if (!empty($keyStr)){

            $where['name']      =   array('like', $keyStr);
            $urlarr['keyword']  =   $_GET['keyword'];
        }

        $urlarr         =   $_GET;

        $urlarr['page'] =   "{{page}}";
        $pageurl        =   Url($_GET['m'], $urlarr, 'admin');

        $pageM          =   $this->MODEL('page');
        $pages          =   $pageM->pageList('work_code', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {

            $where['limit'] =   $pages['limit'];

            $workWxM        =   $this->MODEL('workwx');
            $messageList    =   $workWxM->getChatCodeList($where);
            $this->yunset('list', $messageList);
        }

        $this->yuntpl(array('admin/work/code'));
    }
    /**
     * 查询成员信息
     */
    function getWorkUserId_action()
    {

        $userList   =   $this->obj->select_all('work_user', array('is_follow' => 1), '`userid`,`name`');

        if ($_POST['id']) {

            $code   =   $this->obj->select_once('work_code', array('id' => $_POST['id']), '`user`');

            $userIdArr  =   explode(',', $code['user']);
        }

        $userArr    =   array();

        foreach ($userList as $k => $v) {

            $userArr[$k]['value']   =   $v['userid'];
            $userArr[$k]['name']    =   !empty($v['name']) ? base64_decode($v['name']) : $v['userid'];

            if (isset($userIdArr) && !empty($userIdArr)) {
                foreach ($userIdArr as $uk => $uv) {
                    if ($v['userid'] == $uv) {

                        $userArr[$k]['selected'] = true;
                    }
                }
            }
        }
        echo json_encode($userArr);
        die;
    }

    /**
     * 查询成员信息
     */
    function getWorkUser_action()
    {

        $userList   =   $this->obj->select_all('work_user', array('is_follow' => 1), '`userid`,`name`');

        if ($_POST['id']) {

            $code   =   $this->obj->select_once('work_code', array('id' => $_POST['id']), '`user`');

            $userIdArr  =   explode(',', $code['user']);
        }

        $userArr    =   array();

        foreach ($userList as $k => $v) {

            $userArr[$k]['value']   =   $v['userid'];
            $userArr[$k]['name']    =   !empty($v['name']) ? base64_decode($v['name']) : $v['userid'];

            if (isset($userIdArr) && !empty($userIdArr)) {
                foreach ($userIdArr as $uk => $uv) {
                    if ($v['userid'] == $uv) {

                        $userArr[$k]['selected'] = true;
                    }
                }
            }
        }
        echo json_encode($userArr);
        die;
    }

    /**
     * 添加：配置客户联系「联系我」方式
     */
    function add_action()
    {

        $workM  =   $this->MODEL('workwx');

        if ($_GET['id']){

            $info   =   $workM->getChatCodeInfo(array('id' => $_GET['id']));

            $this->yunset('info', $info);
        }
        $this->yuntpl(array('admin/work/add_code'));
    }

    /**
     * 保存：配置客户联系「联系我」方式
     */
    function save_action()
    {

        $workM  =   $this->MODEL('workwx');

        $data   =   array(
            'name'              =>  trim($_POST['name']),
            'type'              =>  $_POST['type'],
            'remark'            =>  trim($_POST['remark']),
            'skip_verify'       =>  isset($_POST['skip_verify']) ? 1 : 2
        );

        if ($_POST['type'] == 1){

            $data['user']       =  $_POST['userid'];
        }else{

            $data['user']       =  $_POST['user'];
        }

        if($_POST['id']){

            $data['config_id']  =   $_POST['config_id'];
            $data['state']      =   $_POST['state'];
            $return =	$workM->upChatCode($data, array('id'=>intval($_POST['id'])));
        }else{
            $data['state']      =   'Phpyun_ZP_NO'.rand(0, 10000);
            $return	=	$workM->addChatCode($data);
        }

        if ($return['errcode'] == 9){

            $this->ACT_layer_msg($return['errmsg'],9,"index.php?m=admin_workwx_code",2,1);
        }else{

            $this->ACT_layer_msg($return['errmsg'],8,"index.php?m=admin_workwx_code");
        }
    }

    /**
     * 下载渠道码
     */
    function downCode_action()
    {

        $name   =   $_GET['name'];

        $src    =   $_GET['src'];
        $content=   CurlGet($src);

        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=".$name.'.png');

        echo $content;
        exit();
    }

    /**
     * @desc 数据统计分析
     */
    function analyse_action()
    {

        $workM  =   $this->MODEL('workwx');
        $state  =   $_GET['state'];
        $this->yunset('state', $state);

        $inAll      =   $this->obj->select_num('work_customer', array('state' => $state));
        $outAll     =   $this->obj->select_num('work_customer', array('state' => $state, 'delete_time' => array('>', 0)));
        $inToday    =   $this->obj->select_num('work_customer', array('state' => $state, 'createtime' => array('>', strtotime(date('Y-m-d')))));
        $outToday   =   $this->obj->select_num('work_customer', array('state' => $state, 'delete_time' => array('>', strtotime(date('Y-m-d')))));

        $this->yunset(array('inAll' => $inAll, 'inToday' => $inToday, 'outAll' => $outAll, 'outToday' => $outToday));

        if (isset($_GET['time'])) {

            $this->yunset('time', $_GET['time']);
        }

        //查询报表数据
        $time_begin =   isset($_GET['time_begin']) ? $_GET['time_begin'] : date('Y-m-d 00:00:00', strtotime('-30 day'));
        $time_end   =   isset($_GET['time_end']) ? $_GET['time_end'] : date('Y-m-d H:i:s');
        $this->yunset(array('defaultTimeBegin'=>$time_begin, 'defaultTimeEnd' => $time_end));

        $timeBegin  =   strtotime($time_begin);
        $timeEnd    =   strtotime($time_end);

        $names          =   array();    //横坐标名称（时间日期）
        $values         =   array();    //纵坐标的值
        $dataGroupNames =   array();    //柱行代表数据的名称

        //  新增客户
        $InWhere['state']       =   $state;
        $InWhere['createtime'][]=   array('>=', $timeBegin);
        $InWhere['createtime'][]=   array('<=', $timeEnd, 'AND');

        $InWhere['groupby']     =   'date';
        $InWhere['orderby']     =   array('date,asc');

        $field      =   "count(state) as `num`, from_unixtime(createtime,'%Y-%m-%d') as `date`";
        $InRow      =   $workM->getCodeCustomerList($InWhere, array('field' => $field));

        $totalIn    =   0;  // 新增总计

        if (count($InRow) < 1) {

            $in         =   array('names' => array(), 'values' => array());
        } else {
            $inNames    =   array();
            $inValues   =   array();

            foreach ($InRow as $Ir) {

                $inNames[]  =   $Ir['date'];
                $inValues[] =   $Ir['num'];
                $totalIn    +=  $Ir['num'];
            }
            $in         =   array('names' => $inNames, 'values' => $inValues);
        }

        //  流失客户
        $outWhere['state']          =   $state;
        $outWhere['delete_time'][]  =   array('>=', $timeBegin);
        $outWhere['delete_time'][]  =   array('<=', $timeEnd, 'AND');

        $outWhere['groupby']        =   'date';
        $outWhere['orderby']        =   'date';

        $outField   =   "count(state) as num, from_unixtime(`delete_time`,'%Y-%m-%d') as `date`";
        $OutRow     =   $workM->getCodeCustomerList($outWhere, array('field' => $outField));

        $totalOut   =   0;

        if (count($OutRow) < 1) {

            $out    =   array('names' => array(), 'values' => array());
        } else {
            $outNames   =   array();
            $outValues  =   array();
            foreach ($OutRow as $or) {
                $outNames[]     =   $or['date'];
                $outValues[]    =   $or['num'];
                $totalOut       +=  $or['num'];
            }
            $out        =   array('names' => $outNames, 'values' => $outValues);
        }

        $names  =   array_merge($in['names'], $out['names']);
        $names  =   array_unique($names);

        usort($names, 't_sort');

        $inValues   =   array();
        $outValues  =   array();

        $inK        =   0;
        $outK       =   0;

        foreach ($names as $n) {
            if (in_array($n, $in['names'])) {
                $inValues[] =   $in['values'][$inK];
                $inK++;
            } else {

                $inValues[] =   0;
            }
            if (in_array($n, $out['names'])) {

                $outValues[]= $out['values'][$outK];
                $outK++;
            } else {

                $outValues[]= 0;
            }
        }

        $in['values']   =   $inValues;
        $out['values']  =   $outValues;

        $dataGroupNames [] = '新增客户';
        $dataGroupNames [] = '流失客户';

        $values[]   =   array('name' => '新增客户', 'type' => 'line', 'data' => $in['values']);
        $values[]   =   array('name' => '流失客户', 'type' => 'line', 'data' => $out['values']);
        $data       =   array('names' => $names, 'values' => $values, 'dataGroupNames' => $dataGroupNames);

        $this->yunset($data);

        $this->yunset(array('totalIn' => $totalIn, 'totalOut' => $totalOut));

        $this->yuntpl(array('admin/work/code_analyse'));
    }

    /**
     * @desc 删除：配置客户联系「联系我」方式
     */
    function delCode_action()
    {

        $workM  =   $this->MODEL('workwx');

        $del    =   $_GET['del'];

        if (is_array($del)) {

            $where['id']    =   array('in', pylode(',', $del));
        } else {

            $where['id']    =   $del;
        }

        $return =   $workM->delChatCode($where);

        $this->layer_msg($return['errmsg'], $return['errcode'], $return['layertype'], $_SERVER['HTTP_REFERER']);
    }
}

?>