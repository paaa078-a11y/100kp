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

class peixun_renzheng_controller extends adminCommon
{
    //时间区间
    public $timeSection = array(
        '1' => '今天',
        '3' => '最近三天',
        '7' => '最近七天',
        '15' => '最近半月',
        '30' => '最近一个月'
    );

    /**
     * 设置高级搜索功能
     * 高级搜索参数
     */
    public function set_search()
    {
        $search_list[] = array(
            'param' => 'status',
            'name' => '审核状态',
            'value' => array(
                3 => '未审核',
                1 => '已审核',
                2 => '未通过'
            )
        );
        $search_list[] = array(
            'param' => 'apply',
            'name' => '申请时间',
            'value' => $this->timeSection
        );

        return $search_list;
    }

    /**
     * 会员- 培训 - 培训认证审核
     * 2019-06-05 hjy
     */
    public function index_action()
    {

        $certWhereData = array();
        $certWhereData['type'] = 5;

        //审核状态条件
        if (!empty($_POST['status'])) {
            if ($_POST['status'] == 3) {
                $certWhereData['status'] = array('=', 0);
            } else {
                $certWhereData['status'] = array('=', $_POST['status']);
            }
        }

        //关键词条件
        $keywordStr = trim($_POST['keyword']);
        $pxM = $this->MODEL('train');
        $comids = array();
        if (!empty($keywordStr)) {
            $lt = $pxM->getList(array('name' => array('like', $keywordStr)), array('field' => '`uid`,`name`'));
            if (!empty($lt)) {
                foreach ($lt as $val) {
                    $comids[] = $val['uid'];
                }
            } else {
                $comids = array(0);
            }
        }
        if (!empty($comids)) {
            $certWhereData['uid'] = array('in', pylode(',', $comids));
        }

        //申请时间条件
        $endStr = intval($_POST['apply']);
        if (!empty($endStr)) {
            if ($endStr == 1) {
                $certWhereData['ctime'] = array('>=', strtotime(date("Y-m-d")));
            } else {
                $certWhereData['ctime'] = array('>=', strtotime('-' . $endStr . 'day'));
            }
        }

        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('company_cert', $certWhereData, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        //分页数大于0的情况下 执行列表查询
        $list = array();
        $companyM = $this->MODEL('company');
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if (!empty($_POST['order']) && !empty($_POST['t'])) {
                $certWhereData['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $certWhereData['orderby'] = array('status,asc', 'id,desc');
            }
            $certWhereData['limit'] = $pages['limit'];
            $list = $companyM->getCertList($certWhereData, array('utype' => 'pxcert'));
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }
    public function  getSearchData_action(){
        $search_list = $this->set_search();
        $this->render_json(0,'',compact('search_list'));
    }
    /**
     * 会员- 培训 - 培训认证审核
     * 审核认证 -> 保存审核数据
     * 2019-06-05 hjy
     */
    public function status_action()
    {
        if (empty($_POST['id']) || empty($_POST['uid']) || empty($_POST['status'])) {
            $this->render_json(1, '参数错误');
        }

        $pxUid = trim($_POST['uid']);
        $pxId = intval($_POST['id']);
        $pxStatus = intval($_POST['status']);

        //查询数据是否存在
        $pxM = $this->MODEL('train');
        $pxlist = $pxM->getList(array('uid' => array('in', $pxUid)), array('field' => '`linkmail`, `uid`, `name`'));
        if (empty($pxlist)) {
            $this->render_json(1, '数据异常');
        }

        if ($pxStatus != 1) {
            $yyzz_status = 0;
        } else {
            $yyzz_status = 1;
        }

        $notice = $this->MODEL('notice');
        $sysmsgM = $this->MODEL('sysmsg');
        $companyM = $this->MODEL('company');

        /* 消息前缀 */
        $tagName = '培训机构';

        foreach ($pxlist as $v) {
            $uids[] = $v['uid'];

            //修改lietou信息
            $pxM->upInfo(array('uid' => array('=', $v['uid'])), array('yyzz_status' => $yyzz_status));

            /* 处理审核信息 */
            if ($pxStatus == 2) {
                $statusInfo = $tagName . ':' . $v['name'] . '机构认证审核未通过 ';
                if ($_POST['statusbody']) {
                    $statusInfo .= ', 原因：' . trim($_POST['statusbody']);
                }
                $msg[$v['uid']] = $statusInfo;
            } elseif ($pxStatus == 1) {
                $msg[$v['uid']] = $tagName . ':' . $v['name'] . '机构认证已审核通过';
            }

            //发送email
            if (!empty($v['linkmail'])) {
                $notice->sendEmailType(array(
                        "uid" => $v['uid'],
                        "name" => $v['name'],
                        "email" => $v['linkmail'],
                        "comname" => $v['name'],
                        "type" => "comcert"
                    )
                );
            }
        }
        //发送系统通知
        $sysmsgM->addInfo(array('uid' => $uids, 'usertype' => 4, 'content' => $msg));
        //修改认证表中数据
        $upData = array(
            'status' => $pxStatus,
            'statusbody' => trim($_POST['statusbody'])
        );
        $id = $companyM->upCertInfo(array('id' => array('in', $pxId)), $upData, array('utype' => 'admin'));
        if ($id) {
            $this->admin_json(0, '培训机构认证审核(UID:' . $pxUid . ')设置成功');
        } else {
            $this->render_json(1, '培训机构认证审核设置失败');
        }
    }

    /**
     * 会员- 培训 - 培训认证审核
     * 审核认证 -> 删除审核数据
     * 2019-06-05 hjy
     */
    public function del_action()
    {
        if (empty($_POST['del'])) {
            $this->render_json(1, '参数错误');
        }

        $companyM = $this->MODEL('company');

        $del = $_POST['del'];

        $pxM = $this->MODEL('train');

        $pxM->upInfo(array('uid' => array('in', pylode(',', $del))), array('yyzz_status' => 0));

        $return = $companyM->delCert($del, array('type' => '5'));

        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    /**
     * 会员- 培训 - 培训认证审核
     * 数据统计
     * 2019-06-05 hjy
     */
    function pxcertNum_action()
    {
        $MsgNum = $this->MODEL('msgNum');
        echo $MsgNum->pxcertNum();
    }

    function pic_search()
    {
        $search_list[] = array(
            "param" => "status",
            "name" => '审核状态',
            "value" => array(
                '0' => '已审核',
                '1' => '未审核'
            )
        );

        return $search_list;
    }

    /**
     * 会员 - 培训 - 图片管理
     * 培训logo
     * 2019-06-05 hjy
     */
    public function logo_action()
    {
        $pxWhereData = array();
        $pxWhereData['logo'] = array('<>', '');

        $keywordStr = trim($_POST['keyword']);
        $typeStr = trim($_POST['type']);

        //机构名称条件
        if (!empty($keywordStr) && $typeStr == 1) {
            $pxWhereData['name'] = array('like', $keywordStr);
        }

        //uid条件
        if (!empty($keywordStr) && $typeStr == 2) {
            $pxWhereData['uid'] = array('=', $keywordStr);
        }

        if (isset($_POST['status']) && $_POST['status'] !== '') {
            $status = intval($_POST['status']);
            $pxWhereData['logo_status'] = $status;
        }

        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('px_train', $pxWhereData, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        $pxM = $this->MODEL('train');
        //分页数大于0的情况下 执行列表查询
        $list = array();
        if ($pages['total'] > 0) {
            $pxWhereData['orderby'] = array('logo_status,desc', 'uid,desc');

            $pxWhereData['limit'] = $pages['limit'];
            $list = $pxM->getList($pxWhereData, array('utype' => 'admin'));
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }
    public function  getLogoSearchData_action(){
        $search_list = $this->pic_search();
        $this->render_json(0,'',compact('search_list'));
    }
    /**
     * @desc 培训LOGO审核
     */
    function logoStatus_action()
    {
        if (empty($_POST['uid'])) {
            $this->render_json(1, '参数错误');
        }

        $trainM = $this->MODEL('train');

        $post = array(
            'logo_statusbody' => trim($_POST['statusbody']),
            'logo_status' => intval($_POST['status'])
        );

        $return = $trainM->statusLogo(pylode(',', $_POST['uid']), array('post' => $post));
        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    /**
     * 会员 - 培训 - 图片管理
     * 机构环境
     * 2019-06-05 hjy
     */
    public function show_action()
    {
        $pxswWhereData = array();

        $keywordStr = trim($_POST['keyword']);
        $typeStr = trim($_POST['type']);

        //机构名称条件
        if (!empty($keywordStr) && $typeStr == 3) {
            $pxswWhereData['title'] = array('like', $keywordStr);
        }

        //uid条件
        if (!empty($keywordStr) && $typeStr == 2) {
            $pxswWhereData['uid'] = array('=', $keywordStr);
        }
        $pxM = $this->MODEL('train');
        $comids = array();
        if (!empty($keywordStr) && $typeStr == 1) {
            $px = $pxM->getList(array('name' => array('like', $keywordStr)), array('field' => '`uid`'));
            if (!empty($px)) {
                foreach ($px as $val) {
                    $comids[] = $val['uid'];
                }
            } else {
                $comids = array(0);
            }
        }
        if (!empty($comids)) {
            $pxswWhereData['uid'] = array('in', pylode(',', $comids));
        }

        if (isset($_POST['status']) && $_POST['status'] !== '') {
            $status = intval($_POST['status']);
            $pxswWhereData['status'] = $status;
        }

        //提取分页
        $pageM = $this->MODEL('page');
        $pxM = $this->MODEL('train');
        $pages = $pageM->adminPageList('px_train_show', $pxswWhereData, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        //分页数大于0的情况下 执行列表查询
        $list = array();
        if ($pages['total'] > 0) {
            $pxswWhereData['orderby'] = array('status,desc', 'id,desc');
            $pxswWhereData['limit'] = $pages['limit'];
            $list = $pxM->getPxshowList($pxswWhereData);
        }

        if (!empty($list)) {
            foreach ($list as $v) {
                $uids[] = $v['uid'];
            }
            $bcWhereData = array('uid' => array('in', pylode(',', $uids)));
            //补充机构相关的信息
            $pxField = '`uid`, `name`';
            $pxList = $pxM->getList($bcWhereData, array('field' => $pxField));
            $pxListIndex = array();
            if (!empty($pxList)) {
                foreach ($pxList as $pxV) {
                    $pxListIndex[$pxV['uid']] = $pxV;
                }
            }
            foreach ($list as $key => $value) {
                $list[$key]['title'] = mb_substr($value['title'], 0, 15);
                if (isset($pxListIndex[$value['uid']])) {
                    $list[$key]['name'] = $pxListIndex[$value['uid']]['name'];
                }
            }
        }

        $this->render_json(0, 'ok', compact('search_list', 'list', 'total', 'page_sizes', 'limit', 'page'));
    }
    public function  getHjSearchData_action(){
        $search_list = $this->pic_search();
        $this->render_json(0,'',compact('search_list'));
    }
    /**
     * @desc 培训机构环境审核
     */
    function showStatus_action()
    {
        if (empty($_POST['id'])) {
            $this->render_json(1, '参数错误');
        }

        $trainM = $this->MODEL('train');

        $post = array(
            'status' => intval($_POST['status']),
            'statusbody' => trim($_POST['statusbody'])
        );

        $return = $trainM->statusShow(pylode(',', $_POST['id']), array('post' => $post));

        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    /**
     * 会员 - 培训 - 图片管理
     * 机构横幅
     * 2019-06-05 hjy
     */
    public function banner_action()
    {
        $pxbaWhereData = array();

        $keywordStr = trim($_POST['keyword']);
        $typeStr = trim($_POST['type']);
        $pxbaWhereData['pic'] = array('<>', '');
        //uid条件
        if (!empty($keywordStr) && $typeStr == 2) {
            $pxbaWhereData['uid'] = array('=', $keywordStr);
        }
        $pxM = $this->MODEL('train');
        $comids = array();
        if (!empty($keywordStr) && $typeStr == 1) {
            $px = $pxM->getList(array('name' => array('like', $keywordStr)), array('field' => '`uid`'));
            if (!empty($px)) {
                foreach ($px as $val) {
                    $comids[] = $val['uid'];
                }
            } else {
                $comids = array(0);
            }
        }
        if (!empty($comids)) {
            $pxbaWhereData['uid'] = array('in', pylode(',', $comids));
        }
        if (isset($_POST['status']) && $_POST['status'] !== '') {
            $status = intval($_POST['status']);
            $pxbaWhereData['status'] = $status;
        }

        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('px_banner', $pxbaWhereData, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        $pxM = $this->MODEL('train');

        //分页数大于0的情况下 执行列表查询
        $list = array();
        if ($pages['total'] > 0) {
            $pxbaWhereData['orderby'] = array('status,desc', 'id,desc');
            $pxbaWhereData['limit'] = $pages['limit'];
            $list = $pxM->getBannerList($pxbaWhereData);
        }

        if (!empty($list)) {
            foreach ($list as $v) {
                $uids[] = $v['uid'];
            }
            $bcWhereData = array('uid' => array('in', pylode(',', $uids)));
            //补充机构相关的信息
            $pxField = '`uid`, `name`';
            $pxList = $pxM->getList($bcWhereData, array('field' => $pxField));
            $pxListIndex = array();
            if (!empty($pxList)) {
                foreach ($pxList as $pxV) {
                    $pxListIndex[$pxV['uid']] = $pxV;
                }
            }
            foreach ($list as $key => $value) {
                if (isset($pxListIndex[$value['uid']])) {
                    $list[$key]['name'] = $pxListIndex[$value['uid']]['name'];
                }
            }
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }
    public function  getBannerSearchData_action(){
        $search_list = $this->pic_search();
        $this->render_json(0,'',compact('search_list'));
    }
    /**
     * @desc 企业环境审核
     */
    function bannerStatus_action()
    {
        if (empty($_POST['id'])) {
            $this->render_json(1, '参数错误');
        }

        $trainM = $this->MODEL('train');

        $status = intval($_POST['status']);

        $statusbody = trim($_POST['statusbody']);

        $post = array(
            'status' => $status,
            'statusbody' => $statusbody
        );

        $return = $trainM->statusBanner(pylode(',', $_POST['id']), array('post' => $post));

        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    /**
     * 会员 - 培训 - 图片管理
     * 保存图片
     * 2019-06-05 hjy
     */
    public function uploadsave_action()
    {
        $_POST = $this->post_trim($_POST);

        if (empty($_POST['type']) || empty($_POST['id'])) {
            $this->render_json(1, '参数错误');
        }

        $id = intval($_POST['id']);

        $pxM = $this->MODEL('train');
        $resType = '';
        $nbid = 0;
        if ($_POST['type'] == 'logo') {
            $row = $pxM->getInfo(array('uid' => array('=', $id)), array('field' => '`logo`'));
            if (empty($row)) {
                $this->render_json(1, '数据异常');
            }
            $data = array();

            $uploadRes = $this->uploadImg($_FILES, 'train');
            if ($uploadRes['errorcode'] == 9 && !empty($uploadRes['img'])) {
                $data['logo'] = $uploadRes['img'];
            } else {
                $this->render_json(1, '上传图片错误');
            }

            $nbid = $pxM->upInfo(array('uid' => array('=', $id)), $data);
            $resType = '培训logo';
        }

        if ($_POST['type'] == 'show') {
            $row = $pxM->getPxshowInfo(array('id' => array('=', $id)), array('field' => '`picurl`'));
            if (empty($row)) {
                $this->render_json(1, '数据异常');
            }
            $data = array();
            $uploadRes = $this->uploadImg($_FILES, 'show');
            if ($uploadRes['errorcode'] == 9 && !empty($uploadRes['img'])) {
                $data['picurl'] = $uploadRes['img'];
            } else {
                $this->render_json(1, '上传图片错误');
            }
            $data['title'] = $_POST['title'];
            $data['sort'] = $_POST['sort'];
            $data['ctime'] = time();
            $nbid = $pxM->upPxshowInfo(array('id' => array('=', $id)), $data);
            $resType = '机构环境';
        }

        if ($_POST['type'] == 'banner') {
            $row = $pxM->getBannerInfo(array('id' => array('=', $id)), array('field' => '`pic`'));
            if (empty($row)) {
                $this->render_json(1, '数据异常');
            }
            $data = array();
            $uploadRes = $this->uploadImg($_FILES, 'train');
            if ($uploadRes['errorcode'] == 9 && !empty($uploadRes['img'])) {
                $data['pic'] = $uploadRes['img'];
            } else {
                $this->render_json(1, '上传图片错误');
            }
            $nbid = $pxM->upBannerInfo(array('id' => array('=', $id)), $data);
            $resType = '机构横幅';
        }

        if (!empty($resType) && !empty($nbid)) {
            $this->admin_json(0, $resType . '(ID:' . $id . ')修改成功');
        } else {
            $this->render_json(1, $resType . '修改失败');
        }

    }

    /**
     * 上传图片
     */
    private function uploadImg($filesData, $typeS)
    {
        $return = array(
            'errorcode' => 8,
            'msg' => '',
            'img' => ''
        );

        if ($filesData['file']['tmp_name']) {
            $upArr = array(
                'file' => $filesData['file'],
                'dir' => $typeS
            );

            $uploadM = $this->MODEL('upload');

            $picr = $uploadM->newUpload($upArr);

            if (!empty($picr['msg'])) {
                $return['msg'] = $picr['msg'];
                return $return;
            } elseif (!empty($picr['picurl'])) {
                $return['errorcode'] = 9;
                $return['img'] = $picr['picurl'];
            }
        }

        return $return;
    }

    /**
     * 会员 - 培训 - 图片管理
     * 删除图片
     * 2019-06-06 hjy
     */
    public function delPic_action()
    {
        if (empty($_POST['type']) || empty($_POST['del'])) {
            $this->render_json(1, '参数错误');
        }

        $del = $_POST['del'];
        $ids = pylode(',', $del);

        $typeStr = trim($_POST['type']);
        $resType = '';
        $delid = 0;
        $pxM = $this->MODEL('train');
        if ($typeStr == 'logo') {
            $resType = '培训LOGO';

            $delid = $pxM->upInfo(array('uid' => array('in', $ids)), array('logo' => ''));
        }

        if ($typeStr == 'show') {
            $resType = '机构环境';

            $delid = $pxM->delPxshowInfo(array('id' => $del, 'utype' => 'admin'));
            $delid = $delid['cod'] == 9; // 基于删除方法特殊处理
        }

        if ($typeStr == 'banner') {
            $resType = '机构横幅';

            $delid = $pxM->delBanner(array('id' => $del));
        }

        if (!empty($resType) && !empty($delid)) {
            $this->admin_json(0, $resType . '(ID:' . $ids . ')删除成功');
        } else {
            $this->render_json(1, $resType . '删除失败');
        }
    }
}

?>