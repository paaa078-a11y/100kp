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

class admin_workwx_qrcode_controller extends adminCommon
{

    function index_action()
    {

        $search_list[]  =   array("param" => "scene", "name" => '入群场景', "value" => array("1" => "小程序", "2" => "二维码"));
        $this->yunset("search_list", $search_list);

        $where  = array();

        $keyStr =   trim($_GET['keyword']);
        if (!empty($keyStr)){

            $where['name']      =   array('like', $keyStr);
            $urlarr['keyword']  =   $_GET['keyword'];
        }

        if ($_GET['scene']){

            $where['scene'] =   $_GET['scene'];
            $urlarr['scene']=   $_GET['scene'];
        }

        $urlarr         =   $_GET;

        $urlarr['page'] =   "{{page}}";
        $pageurl        =   Url($_GET['m'], $urlarr, 'admin');

        $pageM          =   $this->MODEL('page');
        $pages          =   $pageM->pageList('work_qrcode', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {

            $where['limit'] =   $pages['limit'];

            $workWxM        =   $this->MODEL('workwx');
            $messageList    =   $workWxM->getChatQRCodeList($where);
            $this->yunset('list', $messageList);
        }

        $this->yuntpl(array('admin/work/qrcode'));
    }

    /**
     * 查询群聊信息
     */
    function getWorkGroup_action()
    {

        $workM      =   $this->MODEL('workwx');

        $groupArr   =   $workM->getWorkGroup(array('status' => 0, 'orderby' => 'create_time'), array('field' => '`chat_id`,`name`'));

        if ($_POST['id']) {

            $QRCode     =   $this->obj->select_once('work_qrcode', array('id' => $_POST['id']), '`chat_id_list`');
            $chatIdArr  =   explode(',', $QRCode['chat_id_list']);
        }

        $chatArr    =   array();

        foreach ($groupArr as $k => $v){

            $chatArr[$k]['value']   =   $v['chat_id'];
            $chatArr[$k]['name']    =   $v['name']."【".$v['number']."人】";

            if (isset($chatIdArr) && !empty($chatIdArr)){
                foreach ($chatIdArr as $ck => $cv){
                    if ($v['chat_id'] == $cv){

                        $chatArr[$k]['selected']    =   true;
                    }
                }
            }
        }

        echo json_encode($chatArr);die;
    }

    /**
     * 创建企业群发
     */
    function add_action()
    {

        if ($_GET['id']){

            $workM  =   $this->MODEL('workwx');

            $info   =   $workM->getChatQRCodeInfo(array('id' => $_GET['id']));

            $this->yunset('info', $info);
        }

        $this->yuntpl(array('admin/work/add_qrcode'));
    }

    /**
     * 保存微群活码
     */
    function save_action()
    {

        $workM  =   $this->MODEL('workwx');
        $picture=   '';
        $data   =   array(

            'name'              =>  trim($_POST['name']),
            'remark'            =>  trim($_POST['remark']),
            'scene'             =>  intval($_POST['scene']),
            'auto_create_room'  =>  $_POST['auto_create_room'],
            'room_base_name'    =>  trim($_POST['room_base_name']),
            'room_base_id'      =>  trim($_POST['room_base_id']),
            'chat_id_list'      =>  $_POST['chat_id'],
            'xcx_title'         =>  $_POST['xcx_title']
        );

        if ($_POST['scene'] == 1 && isset($_POST['xcx_use'])){

            $data['xcx_use']    =   1;
        }

        if($_FILES['file']['tmp_name']){
            $upArr    =  array(
                'file'  =>  $_FILES['file'],
                'dir'   =>  'work'
            );

            $uploadM	=	$this -> MODEL('upload');

            if(!empty($upArr)){
                $pic	=	$uploadM->newUpload($upArr);
                if(!empty($pic['picurl'])){
                    $picture	=	$pic['picurl'];
                    $data['xcx_icon']   =   $picture;
                }
            }
        }

        if($_POST['id']){

            $QRCodeInfo         =   $this->obj->select_once('work_qrcode', array('id' => $_POST['id']), '`xcx_icon`');

            if (!empty($QRCodeInfo) && $picture == ''){

                $data['xcx_icon']   =   $QRCodeInfo['xcx_icon'];
            }

            $data['config_id']  =   $_POST['config_id'];
            $data['state']      =   $_POST['state'];

            $return =	$workM->upChatQRCode($data, array('id'=>intval($_POST['id'])));
        }else{

            $data['state']      =   "Phpyun_zp_NO.".rand(0,10000);
            $return =	$workM->addChatQRCode($data);
        }

        if ($return['errcode'] == 9){

            $this->ACT_layer_msg($return['errmsg'],9,"index.php?m=admin_workwx_qrcode",2,1);
        }else{

            $this->ACT_layer_msg($return['errmsg'],8,"index.php?m=admin_workwx_qrcode");
        }
    }

    /**
     * 下载客户群活码
     */
    function downQRCode_action()
    {
        $name       =   $_GET['name'];

        $src        =   $_GET['src'];
        $content    =   CurlGet($src);

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

        $inAll      =   $this->obj->select_num('work_group_member', array('state' => $state));
        $outAll     =   $this->obj->select_num('work_group_member', array('state' => $state, 'delete_time' => array('>', 0)));
        $inToday    =   $this->obj->select_num('work_group_member', array('state' => $state, 'join_time' => array('>', strtotime(date('Y-m-d')))));
        $outToday   =   $this->obj->select_num('work_group_member', array('state' => $state, 'delete_time' => array('>', strtotime(date('Y-m-d')))));

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
        $InWhere['join_time'][] =   array('>=', $timeBegin);
        $InWhere['join_time'][] =   array('<=', $timeEnd, 'AND');

        $InWhere['groupby']     =   'date';
        $InWhere['orderby']     =   array('date,asc');

        $field      =   "count(state) as `num`, from_unixtime(join_time,'%Y-%m-%d') as `date`";
        $InRow      =   $workM->getQRCodeCustomerList($InWhere, array('field' => $field));

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
        $outWhere['orderby']        =   'date,asc';

        $outField   =   "count(state) as num, from_unixtime(`delete_time`,'%Y-%m-%d') as `date`";
        $OutRow     =   $workM->getQRCodeCustomerList($outWhere, array('field' => $outField));

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

        $dataGroupNames [] = '入群客户';
        $dataGroupNames [] = '退群客户';

        $values[]   =   array('name' => '入群客户', 'type' => 'line', 'data' => $in['values']);
        $values[]   =   array('name' => '退群客户', 'type' => 'line', 'data' => $out['values']);
        $data       =   array('names' => $names, 'values' => $values, 'dataGroupNames' => $dataGroupNames);

        $this->yunset($data);

        $this->yunset(array('totalIn' => $totalIn, 'totalOut' => $totalOut));

        $this->yuntpl(array('admin/work/qrcode_analyse'));
    }

    /**
     * @desc 删除客户群活码
     */
    function delQRCode_action()
    {

        $workM  =   $this->MODEL('workwx');

        $del    =   $_GET['del'];

        if (is_array($del)) {

            $where['id']    =   array('in', pylode(',', $del));
        } else {

            $where['id']    =   $del;
        }

        $return =   $workM->delChatQRCode($where);

        $this->layer_msg($return['errmsg'], $return['errcode'], $return['layertype'], $_SERVER['HTTP_REFERER']);
    }
}

?>