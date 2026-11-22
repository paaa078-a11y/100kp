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

class coupon_prepaid_controller extends adminCommon
{
    function set_search()
    {
        $search_list[] = array("param" => "time", "name" => '有效期', "value" => array("1" => "未过期", "2" => "已过期"));
        $search_list[] = array("param" => "status", "name" => '使用状态', "value" => array("1" => "已使用", "2" => "未使用"));
        $search_list[] = array("param" => "type", "name" => '状态', "value" => array("1" => "可用", "2" => "不可用"));

        return $search_list;
    }

    // 列表
    function index_action()
    {
        if (trim($_POST['keyword'])) {
            if ($_POST['ctype'] == '2') {
                $where['username'] = array('like', trim($_POST['keyword']));
            } else {
                $where['card'] = array('like', trim($_POST['keyword']));
            }
        }
        if ($_POST['type']) {
            if ($_POST['type'] == '1') {
                $where['type'] = $_POST['type'];
            } else {
                $where['type'] = $_POST['type'];
            }
        }
        if ($_POST['status']) {
            if ($_POST['status'] == 1) {
                $where['uid'] = array('>', '0');
            } else {
                $where['uid'] = array('isnull');
            }
        }
        if ($_POST['time']) {
            if ($_POST['time'] == 1) {
                $where['etime'] = array('>', time());
            } else {
                $where['etime'] = array('<', time());
                $where['uid'] = array('isnull');
            }
        }

        $pageM = $this->MODEL('page');

        $pages = $pageM->adminPageList('prepaid_card', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];
        $list = array();
        if ($pages['total'] > 0) {
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = 'id';
            }

            $where['limit'] = $pages['limit'];
            $prepaidM = $this->MODEL('prepaid');
            $list = $prepaidM->getList($where, array('validity' => 1));
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }

    function index_base_data_action()
    {
        $search_list = $this->set_search();
        $this->render_json(0, 'ok', compact('search_list'));
    }

    function getExportFields_action(){

        $exportType = $this->getFields();
        $this->render_json(0, 'ok', compact('exportType'));

    }
    // 导出字段
    private function getFields()
    {
        $exportType = [
            'card' => '充值卡编号',
            'password' => '充值卡密码',
            'quota' => '抵用额度',
            'stime' => '开始时间',
            'etime' => '截止时间',
            'username' => '使用者',
            'uid' => '使用者UID',
            'utime' => '使用时间'
        ];

        return $exportType;
    }

    // 导出充值卡
    function xls_action()
    {
        if (empty($_POST['type']) || !is_array($_POST['type']) || empty($_POST['exportId']) || !is_array($_POST['exportId'])) {
            $this->render_json(1, '参数错误');
        }

        $where = array('orderby' => 'id');

        $fieldsList = $this->getFields();
        $fields = array_keys($fieldsList);

        foreach ($_POST['type'] as $fval) {
            if (in_array($fval, $fields)) {
                $type[] = $fval;
            }
        }

        $field = pylode(',', $type) . ',id';
        $prepaidM = $this->MODEL('prepaid');

        $ids = pylode(',', $_POST['exportId']);
        $where['id'] = array('in', $ids);

        $list = $prepaidM->getList($where, array('field' => $field));

        include_once LIB_PATH . 'libs/PHPExcel.php';
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0);

        $col = 'A';
        // 循环字段
        foreach ($type as $tval) {
            $width = 20;
            $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setWidth($width); // 设置列宽

            $objPHPExcel->getActiveSheet()->setCellValue($col . '1', $fieldsList[$tval]); // 设置表头
            $col++;
        }

        if ($list) {
            foreach ($list as $key => $val) {
                $col = 'A';
                // 循环字段
                foreach ($type as $tval) {
                    $text = $val[$tval];
                    if (in_array($tval, array('stime', 'etime', 'utime'))) { // 需要取格式化的字段
                        $text = $val[$tval . '_n'];
                    }
                    if (in_array($tval, array('card', 'password'))) { // 数字转字符的字段
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit($col . ($key + 2), $text, PHPExcel_Cell_DataType::TYPE_STRING); // 转类型
                    } else {
                        $objPHPExcel->getActiveSheet()->setCellValue($col . ($key + 2), $text); // 动态表格内容
                    }
                    $col++;
                }
            }
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        ob_start();
        $objWriter->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $data = [
            'file' => base64_encode($xlsData),
            'file_name' => '充值卡' . date('YmdHis') . '.xlsx'
        ];
        return $this->admin_json(0, "导出（ID：{$ids}）充值卡", $data);
    }

    // 批量生成充值卡
    function add_action()
    {
        if (!isset($_POST['quota']) || !isset($_POST['num']) || empty($_POST['stime']) || empty($_POST['etime'])) {
            $this->render_json(1, '参数错误');
        }

        $prepaidM = $this->MODEL('prepaid');

        $return = $prepaidM->addInfo($_POST);

        if ($return['errcode'] == '9') {
            $this->admin_json(0, '批量生成充值卡成功');
        } else {
            $this->render_json(1, '批量生成充值卡失败');
        }
    }

    // 修改
    function upcard_action()
    {
        if (empty($_POST['id']) || empty($_POST['password']) || !isset($_POST['quota']) || empty($_POST['stime']) || empty($_POST['etime'])) {
            $this->render_json(1, '参数错误');
        }

        $prepaidM = $this->MODEL('prepaid');

        $where['id'] = intval($_POST['id']);
        $where['utime'] = array('isnull');

        $return = $prepaidM->upInfo($_POST, $where);

        if ($return['errcode'] == '9') {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    // 是否可用状态修改
    function type_action()
    {
        if (empty($_POST['id']) || empty($_POST['type'])) {
            $this->render_json(1, '参数错误');
        }

        $prepaidM = $this->MODEL('prepaid');

        $postdata['type'] = intval($_POST['type']) === 1 ? 1 : 2;

        $where['id'] = intval($_POST['id']);

        $return = $prepaidM->upInfo($postdata, $where, array('rec' => '1'));

        if ($return['id']) {
            $this->admin_json(0, "充值卡(ID:" . $_POST['id'] . ")状态设置成功");
        } else {
            $this->render_json(1, '充值卡状态设置失败');
        }
    }

    // 删除
    function del_action()
    {
        $prepaidM = $this->MODEL('prepaid');

        $return = $prepaidM->delInfo($_POST['del']);

        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }
}

?>