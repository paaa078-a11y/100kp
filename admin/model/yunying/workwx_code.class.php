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

class workwx_code_controller extends adminCommon
{
    function index_action()
    {
        $where = array();
        $keyStr = trim($_POST['keyword']);
        if (!empty($keyStr)){
            $where['name'] = array('like', $keyStr);
        }
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pageSize = !empty($_POST['pageSize']) ? intval($_POST['pageSize']) : intval($this->config['sy_listnum']);
        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('work_code', $where, $page, array('limit' => $pageSize));
        if ($pages['total'] > 0) {
            $where['limit'] = $pages['limit'];
            $workWxM = $this->MODEL('workwx');
            $messageList = $workWxM->getChatCodeList($where);
        }
        $data['list'] = $messageList ? $messageList : array();
        $data['total'] = intval($pages['total']);
        $data['perPage'] = $pageSize;
        $data['pageSizes'] = $pages['page_sizes'];
        $this->render_json(0,'ok', $data);
    }

    /**
     * 查询成员信息
     */
    function getWorkUser_action()
    {
        $userList = $this->obj->select_all('work_user', array('is_follow' => 1), '`userid`,`name`');
        $userArr = array();
        foreach ($userList as $k => $v) {
            $userArr[$k]['value'] = $v['userid'];
            $userArr[$k]['label'] = !empty($v['name']) ? base64_decode($v['name']) : $v['userid'];
        }
        $this->render_json(0, '', $userArr);
    }

    /**
     * 保存：配置客户联系「联系我」方式
     */
    function save_action()
    {
        $workM = $this->MODEL('workwx');
        if (!trim($_POST['name'])) {
            $this->render_json(1, '请输入渠道码名称');
        }
        $data = array(
            'name' => trim($_POST['name']),
            'type' => intval($_POST['type']),
            'remark' => trim($_POST['remark']),
            'skip_verify' => isset($_POST['skip_verify']) && $_POST['skip_verify'] == 'true' ? 1 : 2
        );
        $data['user'] = is_array($_POST['user_arr']) ? implode(',', $_POST['user_arr']) : $_POST['user_arr'];
        if (!$data['user']) {
            $this->render_json(1, '请选择员工');
        }
        if($_POST['id']){
            $data['config_id'] = $_POST['config_id'];
            $data['state'] = $_POST['state'];
            $return = $workM->upChatCode($data, array('id' => intval($_POST['id'])));
        }else{
            $data['state'] = 'Phpyun_ZP_NO'.rand(0, 10000);
            $return	= $workM->addChatCode($data);
        }
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['errmsg']);
        } else {
            $this->render_json(1, $return['errmsg']);
        }
    }

    /**
     * @desc 数据统计分析
     */
    function analyse_action()
    {
        $workM = $this->MODEL('workwx');
        $state = $_POST['state'];
        $inAll = $this->obj->select_num('work_customer', array('state' => $state));
        $outAll = $this->obj->select_num('work_customer', array('state' => $state, 'delete_time' => array('>', 0)));
        $inToday = $this->obj->select_num('work_customer', array('state' => $state, 'createtime' => array('>', strtotime(date('Y-m-d')))));
        $outToday = $this->obj->select_num('work_customer', array('state' => $state, 'delete_time' => array('>', strtotime(date('Y-m-d')))));
        $rt = array('inAll' => $inAll, 'inToday' => $inToday, 'outAll' => $outAll, 'outToday' => $outToday);
        //查询报表数据
        $time_begin =   isset($_POST['time_begin']) ? $_POST['time_begin'] : date('Y-m-d', strtotime('-30 day'));
        $time_end   =   isset($_POST['time_end']) ? $_POST['time_end'] : date('Y-m-d');
        $timeBegin = strtotime($time_begin);
        $timeEnd = strtotime($time_end . ' 23:59:59');
        $names = array();//横坐标名称（时间日期）
        $values = array();//纵坐标的值
        $dataGroupNames = array();//柱行代表数据的名称
        //  新增客户
        $InWhere['state'] = $state;
        $InWhere['createtime'][] = array('>=', $timeBegin);
        $InWhere['createtime'][] = array('<=', $timeEnd, 'AND');
        $InWhere['groupby'] = 'date';
        $InWhere['orderby'] = array('date,asc');
        $field = "count(state) as `num`, from_unixtime(createtime,'%Y-%m-%d') as `date`";
        $InRow = $workM->getCodeCustomerList($InWhere, array('field' => $field));
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
        $outWhere['orderby'] = 'date';
        $outField = "count(state) as num, from_unixtime(`delete_time`,'%Y-%m-%d') as `date`";
        $OutRow = $workM->getCodeCustomerList($outWhere, array('field' => $outField));
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
        $dataGroupNames[] = '新增客户';
        $dataGroupNames[] = '流失客户';
        $values[] = array('name' => '新增客户', 'type' => 'line', 'data' => $in['values']);
        $values[] = array('name' => '流失客户', 'type' => 'line', 'data' => $out['values']);
        $rt['names'] = $names;
        $rt['values'] = $values;
        $rt['dataGroupNames'] = $dataGroupNames;
        $rt['totalIn'] = $totalIn;
        $rt['totalOut'] = $totalOut;
        $this->render_json(0, '', $rt);
    }

    /**
     * @desc 删除：配置客户联系「联系我」方式
     */
    function delCode_action()
    {
        $workM = $this->MODEL('workwx');
        $del = $_POST['del'];
        if (is_array($del)) {
            $where['id'] = array('in', pylode(',', $del));
        } else {
            $where['id'] = $del;
        }
        $return = $workM->delChatCode($where);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['errmsg']);
        } else {
            $this->admin_json(1, $return['errmsg']);
        }
    }
}
?>