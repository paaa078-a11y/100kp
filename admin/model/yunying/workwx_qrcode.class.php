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

class workwx_qrcode_controller extends adminCommon
{
    function index_action()
    {
        $where  = array();
        $keyStr = trim($_POST['keyword']);
        if (!empty($keyStr)){
            $where['name'] = array('like', $keyStr);
        }
        if ($_POST['scene']){
            $where['scene'] = $_POST['scene'];
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('work_qrcode', $where, $page, array('limit' => $pageSize));
        if ($pages['total'] > 0) {
            $where['limit'] = $pages['limit'];
            $workWxM = $this->MODEL('workwx');
            $messageList = $workWxM->getChatQRCodeList($where);
        }
        $data['list'] = $messageList ? $messageList : array();
        $data['total'] = intval($pages['total']);
        $data['perPage'] = $pageSize;
        $data['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0,'ok', $data);
    }
    /**
     * @desc 数据统计分析
     */
    function analyse_action()
    {
        $workM = $this->MODEL('workwx');
        $state = $_POST['state'];
        $inAll = $this->obj->select_num('work_group_member', array('state' => $state));
        $outAll = $this->obj->select_num('work_group_member', array('state' => $state, 'delete_time' => array('>', 0)));
        $inToday = $this->obj->select_num('work_group_member', array('state' => $state, 'join_time' => array('>', strtotime(date('Y-m-d')))));
        $outToday = $this->obj->select_num('work_group_member', array('state' => $state, 'delete_time' => array('>', strtotime(date('Y-m-d')))));
        $rt = array('inAll' => $inAll, 'inToday' => $inToday, 'outAll' => $outAll, 'outToday' => $outToday);
        //查询报表数据
        $time_begin = isset($_POST['time_begin']) ? $_POST['time_begin'] : date('Y-m-d', strtotime('-30 day'));
        $time_end = isset($_POST['time_end']) ? $_POST['time_end'] : date('Y-m-d');
        $timeBegin = strtotime($time_begin);
        $timeEnd = strtotime($time_end . ' 23:59:59');
        $names = array();//横坐标名称（时间日期）
        $values = array();//纵坐标的值
        $dataGroupNames = array();//柱行代表数据的名称
        // 新增客户
        $InWhere['state'] = $state;
        $InWhere['join_time'][] = array('>=', $timeBegin);
        $InWhere['join_time'][] = array('<=', $timeEnd, 'AND');
        $InWhere['groupby'] = 'date';
        $InWhere['orderby'] = array('date,asc');
        $field = "count(state) as `num`, from_unixtime(join_time,'%Y-%m-%d') as `date`";
        $InRow = $workM->getQRCodeCustomerList($InWhere, array('field' => $field));
        $totalIn = 0;// 新增总计
        if (count($InRow) < 1) {
            $in = array('names' => array(), 'values' => array());
        } else {
            $inNames = array();
            $inValues = array();
            foreach ($InRow as $Ir) {
                $inNames[] = $Ir['date'];
                $inValues[] = $Ir['num'];
                $totalIn += $Ir['num'];
            }
            $in = array('names' => $inNames, 'values' => $inValues);
        }
        //  流失客户
        $outWhere['state'] = $state;
        $outWhere['delete_time'][] = array('>=', $timeBegin);
        $outWhere['delete_time'][] = array('<=', $timeEnd, 'AND');
        $outWhere['groupby'] = 'date';
        $outWhere['orderby'] = 'date,asc';
        $outField = "count(state) as num, from_unixtime(`delete_time`,'%Y-%m-%d') as `date`";
        $OutRow = $workM->getQRCodeCustomerList($outWhere, array('field' => $outField));
        $totalOut = 0;
        if (count($OutRow) < 1) {
            $out = array('names' => array(), 'values' => array());
        } else {
            $outNames = array();
            $outValues = array();
            foreach ($OutRow as $or) {
                $outNames[] = $or['date'];
                $outValues[] = $or['num'];
                $totalOut += $or['num'];
            }
            $out = array('names' => $outNames, 'values' => $outValues);
        }
        $names = array_merge($in['names'], $out['names']);
        $names = array_unique($names);
        usort($names, 't_sort');
        $inValues = array();
        $outValues = array();
        $inK = 0;
        $outK = 0;
        foreach ($names as $n) {
            if (in_array($n, $in['names'])) {
                $inValues[] = $in['values'][$inK];
                $inK++;
            } else {
                $inValues[] = 0;
            }
            if (in_array($n, $out['names'])) {
                $outValues[] = $out['values'][$outK];
                $outK++;
            } else {
                $outValues[] = 0;
            }
        }
        $in['values'] = $inValues;
        $out['values'] = $outValues;
        $dataGroupNames[] = '入群客户';
        $dataGroupNames[] = '退群客户';
        $values[] = array('name' => '入群客户', 'type' => 'line', 'data' => $in['values']);
        $values[] = array('name' => '退群客户', 'type' => 'line', 'data' => $out['values']);
        $rt['names'] = $names;
        $rt['values'] = $values;
        $rt['dataGroupNames'] = $dataGroupNames;
        $rt['totalIn'] = $totalIn;
        $rt['totalOut'] = $totalOut;
        $this->render_json(0, '', $rt);
    }

    /**
     * 查询群聊信息
     */
    function getWorkGroup_action()
    {
        $workM      =   $this->MODEL('workwx');
        $data       =   array('field' => '`chat_id`,`name`');
        if (isset($_POST['id']) && !empty($_POST['id'])){
            $data['extra_id']   =   $_POST['id'];
        }
        $groupArr   =   $workM->getWorkGroup(array('status' => 0, 'orderby' => 'create_time'), $data);
        $chatArr    =   array();
        foreach ($groupArr as $k => $v) {

            $chatArr[$k]['value']   =   $v['chat_id'];
            $chatArr[$k]['label']   =   $v['name']."【".$v['number']."人】";
        }
        $this->render_json(0, '', $chatArr);
    }

    /**
     * 保存微群活码
     */
    function save_action()
    {
        $workM = $this->MODEL('workwx');
        $picture = '';
        $info = $_POST;
        if (!$info['name']) {
            $this->render_json(1, '请输入活码名称');
        }
        if (count($info['chat_id_list_arr']) == 0) {
            $this->render_json(1, '请选择对应群聊');
        }
        if ($info['auto_create_room'] == '1' && !$info['room_base_name']) {
            $this->render_json(1, '请输入群名称前缀');
        }
        if ($info['auto_create_room'] == '1' && !$info['room_base_id']) {
            $this->render_json(1, '请输入群起始序号');
        }
        $data = array(
            'name' => trim($info['name']),
            'remark' => trim($info['remark']),
            'scene' => intval($info['scene']),
            'auto_create_room' => $info['auto_create_room'],
            'room_base_name' => trim($info['room_base_name']),
            'room_base_id' => trim($info['room_base_id']),
            'chat_id_list' => $info['chat_id_list_arr'],
            'xcx_title' => $info['xcx_title']
        );
        if ($info['scene'] == 1 && isset($info['xcx_use']) && $info['xcx_use']){
            $data['xcx_use'] = 1;
        } else {
            $data['xcx_use'] = 2;
        }
        // 新上传图片文件处理
        foreach ($_FILES['xcxicon'] as $nk => $nv) {
            foreach ($nv as $nkk => $nvv) {
                $iconpic_file[$nkk][$nk] = $nvv;
            }
        }
        if($_FILES['xcxicon']['tmp_name']){
            $upArr = array(
                'file' => $iconpic_file[0],
                'dir' => 'work'
            );
            $uploadM = $this->MODEL('upload');
            if(!empty($upArr)){
                $pic = $uploadM->newUpload($upArr);
                if(!empty($pic['picurl'])){
                    $picture = $pic['picurl'];
                    $data['xcx_icon'] = $picture;
                }
            }
        }
        if($info['id']){
            $QRCodeInfo = $this->obj->select_once('work_qrcode', array('id' => $info['id']), '`xcx_icon`');
            if (!empty($QRCodeInfo) && $picture == ''){
                $data['xcx_icon'] = $QRCodeInfo['xcx_icon'];
            }
            $data['config_id'] = $info['config_id'];
            $data['state'] = $info['state'];
            $return = $workM->upChatQRCode($data, array('id' => intval($info['id'])));
        }else{
            $data['state'] = "Phpyun_zp_NO.".rand(0,10000);
            $return = $workM->addChatQRCode($data);
        }
        if ($return['errcode'] == 9){
            $this->admin_json(0, $return['errmsg']);
        }else{
            $this->render_json(1, $return['errmsg']);
        }
    }

    /**
     * @desc 删除客户群活码
     */
    function delQRCode_action()
    {
        $workM = $this->MODEL('workwx');
        $del = $_POST['del'];
        if (is_array($del)) {
            $where['id'] = array('in', pylode(',', $del));
        } else {
            $where['id'] = $del;
        }
        $return = $workM->delChatQRCode($where);
        if ($return['errcode'] == 9) {
            $this->admin_json( 0, $return['errmsg']);
        } else {
            $this->render_json( 1, $return['errmsg']);
        }
    }
}
?>