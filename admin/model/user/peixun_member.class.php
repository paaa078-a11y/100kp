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

class peixun_member_controller extends adminCommon
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
            'param' => 'rec',
            'name' => '推荐状态',
            'value' => array(
                1 => '已推荐',
                2 => '未推荐'
            )
        );
        $search_list[] = array(
            'param' => 'status',
            'name' => '审核状态',
            'value' => array(
                1 => '已审核',
                4 => '未审核',
                3 => '未通过',
                2 => '已锁定'
            )
        );
        $search_list[] = array(
            'param' => 'r_time',
            'name' => '注册时间',
            'value' => $this->timeSection
        );
        $search_list[] = array(
            'param' => 'l_time',
            'name' => '登录时间',
            'value' => $this->timeSection
        );

        return $search_list;
    }

    /**
     * 会员- 培训 - 培训用户列表
     * 全部培训
     * 从px_train中读取
     * 2019-07-17 hjy
     */
    public function index_action()
    {


        $pxWhereData = $memberWhereData = array();

        $keywordStr = trim($_POST['keyword']);
        $typeStr = intval($_POST['type']);
        $loginStr = intval($_POST['l_time']);
        $regStr = intval($_POST['r_time']);
        //用户名条件
        if (!empty($keywordStr) && $typeStr == 1) {
            $memberWhereData['username'] = array('like', $keywordStr);
        }
        if (!empty($loginStr)) {
            if ($loginStr == 1) {
                $memberWhereData['login_date'] = array('>=', strtotime(date('Y-m-d')));
            } else {
                $memberWhereData['login_date'] = array('>=', strtotime('-' . $loginStr . 'day'));
            }
        }
        if (!empty($regStr)) {
            if ($regStr == 1) {
                $memberWhereData['reg_date'] = array('>=', strtotime(date('Y-m-d')));
            } else {
                $memberWhereData['reg_date'] = array('>=', strtotime('-' . $regStr . 'day'));
            }
        }
        if (!empty($_POST['status'])) {
            if ($_POST['status'] == '4') {
                $pxWhereData['r_status'] = array('=', 0);
            } else {
                $pxWhereData['r_status'] = array('=', $_POST['status']);
            }
        }
        $memberUid = array();

        $memberM = $this->MODEL('userinfo');

        if (!empty($memberWhereData)) {
            $resWhere = $memberWhereData;
            $uidList = $memberM->getList($resWhere, array('field' => '`uid`'));
            if (!empty($uidList)) {
                foreach ($uidList as $uv) {
                    $memberUid[] = $uv['uid'];
                }
            } else {
                $memberUid = array(0);
            }
        }

        if (!empty($memberUid)) {
            $pxWhereData['uid'] = array('in', pylode(',', $memberUid));
        }

        //email条件
        if (!empty($keywordStr) && $typeStr == 3) {
            $pxWhereData['linkmail'] = array('like', $keywordStr);
        }
        //mobile条件
        if (!empty($keywordStr) && $typeStr == 4) {
            $pxWhereData['linktel'] = array('like', $keywordStr);
        }
        //机构名称条件
        $pxM = $this->MODEL('train');

        if (!empty($keywordStr) && $typeStr == 2) {
            $pxWhereData['name'] = array('like', $keywordStr);
        }
        $recStr = intval($_POST['rec']);
        if (!empty($recStr)) {
            if ($recStr == 2) {
                $re = 0;
            } else {
                $re = $recStr;
            }
            $pxWhereData['rec'] = array('=', $re);
        }

        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('px_train', $pxWhereData, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        //分页数大于0的情况下 执行列表查询
        $list = array();

        $CompanyM = $this->MODEL('company');
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if (!empty($_POST['order']) && !empty($_POST['t'])) {
                $pxWhereData['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $pxWhereData['orderby'] = 'uid';
            }
            $pxWhereData['limit'] = $pages['limit'];
            $list = $pxM->getList($pxWhereData);
        }

        if (!empty($list)) {
            $pxuid = array();
            foreach ($list as $lv) {
                $pxuid[] = $lv['uid'];
            }
            $bcWhereData = array('uid' => array('in', pylode(',', $pxuid)));
            //补充用户相关的信息
            $memberField = '`uid`, `login_date`, `reg_date`, `username`, `status`, `login_ip`, `usertype`';
            $memberField .= ', `email`, `moblie`,`wxid`,`unionid`';
            $memberList = $memberM->getList($bcWhereData, array('field' => $memberField));
            $memberListIndex = array();
            if (!empty($memberList)) {
                foreach ($memberList as $memberV) {
                    $memberListIndex[$memberV['uid']] = $memberV;
                }
            }

            $bcWhereData['type'] = 5;

            $certField = '`uid`,`check`,`status`';

            $certdata = array('field' => $certField);

            $certList = $CompanyM->getCertList($bcWhereData, $certdata);

            //补充进相关信息
            foreach ($list as $Lk => $Lv) {
                $list[$Lk]['train_name'] = $Lv['name'];

                if (isset($memberListIndex[$Lv['uid']])) {
                    $list[$Lk] = array_merge($list[$Lk], $memberListIndex[$Lv['uid']]);
                } else {
                    unset($list[$Lk]);
                    continue;
                }

                !empty($list[$Lk]['login_date']) && $list[$Lk]['login_date_n'] = date('Y-m-d H:i', $list[$Lk]['login_date']);
                !empty($list[$Lk]['reg_date']) && $list[$Lk]['reg_date_n'] = date('Y-m-d H:i', $list[$Lk]['reg_date']);

                foreach ($certList as $val) {
                    if ($Lv['uid'] == $val['uid']) {
                        $list[$Lk]['check'] = $val['check'];
                        $list[$Lk]['status_n'] = $val['status'];
                    }
                }

                if ($Lv['did'] < 1) {
                    $list[$Lk]['did'] = 0;
                }
            }
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }

    public function  getCacheData_action(){
        $search_list = $this->set_search();
        $data['search_list'] = $search_list;
        $cacheM = $this->MODEL('cache');
        $domain = $cacheM->GetCache('domain');
        $data['dname'] = (object)$domain['Dname'];
        $this->render_json(0,'',$data);
    }
    /**
     * 会员 - 培训 - 培训用户列表:全部培训
     * 锁定信息
     * 2019-06-04 hjy
     */
    public function lockinfo_action()
    {
        $memberM = $this->MODEL('userinfo');
        $userinfo = $memberM->getInfo(array('uid' => $_POST['uid']), array('field' => 'lock_info'));
        echo empty($userinfo['lock_info']) ? '' : $userinfo['lock_info'];
        die;
    }

    /**
     * 会员 - 培训 - 培训用户列表:全部培训
     * 审核培训
     * 2019-06-04 hjy
     */
    public function status_action()
    {
        if (empty($_POST['uid'])) {
            $this->render_json(1, '参数错误');
        }

        $userinfoM = $this->MODEL('userinfo');

        $post = array(
            'status' => intval($_POST['status']),
            'lock_info' => trim($_POST['statusbody'])
        );

        $return = $userinfoM->status(array('uid' => array('in', is_array($_POST['uid']) ? pylode(',', $_POST['uid']) : intval($_POST['uid'])), 'usertype' => 4), array('post' => $post));

        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    /**
     * 会员 - 培训 - 培训用户列表:全部培训
     * 设置推荐
     * 2019-06-04 hjy
     */
    public function rec_action()
    {
        $pxId = intval($_POST['uid']);
        $pxRec = intval($_POST['rec']);
        $upData = array();
        $upData['rec'] = $pxRec;
        if ($pxRec == 1) {
            $upData['r_status'] = 1;
        }
        $pxM = $this->MODEL('train');
        $nid = $pxM->upInfo(array('uid' => array('=', $pxId)), $upData);
        $sysmsgM = $this->MODEL('sysmsg');
        //发送会员通知
        if ($nid && $pxRec == 1) {
            $sysmsgM->addInfo(array('content' => '管理员设置培训机构推荐', 'usertype' => 4, 'uid' => $pxId));
        } elseif ($nid && $pxRec == 0) {
            $sysmsgM->addInfo(array('content' => '管理员操作：取消培训机构推荐', 'usertype' => 4, 'uid' => $pxId));
        }

        if ($nid) {
            $this->MODEL('log')->addAdminLog("培训机构(ID:" . $pxId . ")推荐设置成功！");
            $this->admin_json(0, "培训机构(ID:{$pxId})推荐设置成功");
        } else {
            $this->render_json(1, '培训机构推荐设置失败');
        }
    }

    /**
     * 会员 - 培训 - 培训用户列表:全部培训
     * 编辑培训用户
     * 2019-06-04 hjy
     */
    public function edit_action()
    {
        if (empty($_POST['uid'])) {
            $this->render_json(1, '参数错误');
        }

        //编辑页面
        $pxId = intval($_POST['uid']);

        //用户信息
        $memberM = $this->MODEL('userinfo');
        $memberInfo = $memberM->getInfo(array('uid' => $pxId));

        //培训信息
        $pxM = $this->MODEL('train');
        $info = $pxM->getInfo(array('uid' => array('=', $pxId)));

        $this->render_json(0, 'ok', compact('memberInfo', 'info'));
    }

    // 获取编辑需要的缓存
    public function getCache_action()
    {
        $cacheData = $this->MODEL('cache')->GetCache(array('com', 'subject'));

        /**
         * @var $comdata
         * @var $comclass_name
         * @var $subject_index
         * @var $subject_name
         */
        extract($cacheData);

        $jobPrList = array();
        if (!empty($comdata['job_pr'])) {
            foreach ($comdata['job_pr'] as $job_pr) {
                $jobPrList[] = ['id' => $job_pr, 'name' => $comclass_name[$job_pr]];
            }
        }

        $jobMunList = array();
        if (!empty($comdata['job_mun'])) {
            foreach ($comdata['job_mun'] as $job_mun) {
                $jobMunList[] = ['id' => $job_mun, 'name' => $comclass_name[$job_mun]];
            }
        }

        $subjectList = array();
        if (!empty($subject_index)) {
            foreach ($subject_index as $sival) {
                $subjectList[] = ['id' => $sival, 'name' => $subject_name[$sival]];
            }
        }

        $this->render_json(0, 'ok', compact('jobPrList', 'jobMunList', 'subjectList'));
    }

    /**
     * 培训用户保存
     */
    public function editSave_action()
    {
        $_POST = $this->post_trim($_POST);
        $mData = array(
            'email' => $_POST['email'],
            'moblie' => $_POST['moblie'],
        );

        $trainData = array(
            'name' => $_POST['name'],
            'sid' => $_POST['sid'],
            'pr' => $_POST['pr'],
            'provinceid' => $_POST['provinceid'],
            'cityid' => $_POST['cityid'],
            'threecityid' => $_POST['threecityid'],
            'mun' => $_POST['mun'],
            'address' => $_POST['address'],
            'linkman' => $_POST['linkman'],
            'linkphone' => $_POST['linkphone'],
            'linktel' => $_POST['moblie'],
            'sdate' => $_POST['sdate'],
            'content' => str_replace(array("&amp;", 'background-color:#ffffff', 'background-color:#fff', 'white-space:nowrap;'), array('&', 'background-color:', 'background-color:', 'white-space:'), $_POST['content']),
            'linkmail' => $_POST['email'],
            'linkqq' => $_POST['linkqq'],
            'website' => $_POST['website']
        );
        $trainM = $this->MODEL('train');
        $return = $trainM->upTrainInfo(array('uid' => $_POST['uid']), array('trainData' => $trainData, 'mData' => $mData, 'utype' => 'admin'));

        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    /**
     * @desc 后台培训身份列表 --  修改 -- 账户信息 -- 提交表单
     */
    function saveUser_action()
    {
        $post = $this->post_trim($_POST);

        if (!$post) {
            $this->render_json(1, '参数错误');
        }

        $uid = intval($_POST['uid']);

        $userInfoM = $this->MODEL('userinfo');

        $data = array(
            'username' => $_POST['username'],
            'password' => $_POST['password'],
            'status' => $_POST['status'],
            'lock_info' => $_POST['lock_info']
        );

        $result = $userInfoM->addMemberCheck($data, $uid, 'admin');

        if (!empty($result['msg'])) {
            $this->render_json(1, $result['msg']);
        } else {
            $return = $userInfoM->upInfo(array('uid' => $uid), $data);

            if ($return) {
                $this->admin_json(0, '培训用户(' . $uid . ')账户信息修改成功');
            } else {
                $this->render_json(1, '培训用户账户信息修改失败');
            }
        }
    }

    /**
     * 会员 - 培训 - 培训用户列表:全部培训
     * 删除培训用户
     * 2019-06-04 hjy
     */
    public function del_action()
    {
        if (empty($_POST['del'])) {
            $this->render_json(1, '参数错误');
        }

        if ($_POST['del']) {
            $uid = intval($_POST['del']);
        } elseif ($_POST['del']) {
            $uid = $_POST['del'];
        }

        $userinfoM = $this->MODEL('userinfo');
        $return = $userinfoM->delInfo($uid, 4, $_POST['delAccount']);

        if ($return['errcode'] == 9) {
            $this->admin_json(0, $return['msg']);
        } else {
            $this->render_json(1, $return['msg']);
        }
    }

    /**
     * 会员 - 培训 - 培训用户列表:全部培训
     * 设置分站
     * 2019-06-04 hjy
     */
    public function checksitedid_action()
    {
        if (empty($_POST['uid'])) {
            $this->render_json(1, '参数错误');
        }

        $post = $this->post_trim($_POST);
        $did = intval($post['did']);

        $uid = pylode(',', $post['uid']);
        if (empty($uid)) {
            $this->render_json(1, '请正确选择需分配用户');
        }

        $siteDomain = $this->MODEL('site');
        $Table = array('member', 'px_train', 'px_subject', 'company_cert', 'company_order', 'px_train_news', 'px_teacher');
        $didData = array('did' => $did);

        $siteDomain->updDid(array("company_pay"), array('com_id' => array('in', $uid)), $didData);
        $siteDomain->updDid(array("px_zixun"), array('s_uid' => array('in', $uid)), $didData);
        $siteDomain->updDid($Table, array('uid' => array('in', $uid)), $didData);

        $this->admin_json(0, "会员(ID:{$uid})分配站点成功！");
    }

    /**
     * 会员 - 培训 - 培训用户列表:全部培训
     * 数据统计
     * 2019-06-04 hjy
     */
    public function pxNum_action()
    {
        $MsgNum = $this->MODEL('msgNum');
        echo $MsgNum->pxNum();
    }

    /**
     * 列表-邮箱认证
     */
    public function emailstatus_action()
    {
        $trainM = $this->MODEL('train');
        $userinfoM = $this->MODEL('userinfo');

        if (empty($_POST['email'])) {
            $this->render_json(1, '请输入邮箱');
        } elseif (CheckRegEmail($_POST['email']) == false) {
            $this->render_json(1, '邮箱格式错误');
        }

        $uid = intval($_POST['uid']);
        $status = intval($_POST['status']);
        $email = trim($_POST['email']);

        $pInfo = $trainM->getInfo(array('uid' => $uid), array('field' => '`linkmail`,`email_status`'));

        if (!empty($pInfo)) {
            if ($pInfo['linkmail'] == $email && $pInfo['email_status'] == 1) {
                $this->render_json(1, '邮箱未变更，无需调整');
            }

            $data = array('email_status' => $status, 'linkmail' => $email);
            $nid = $trainM->upInfo(array('uid' => $uid), $data);

            if ($nid) {
                $emaildata = array('email' => $email, 'email_status' => $status);
                $userinfoM->upInfo(array('uid' => $uid), $emaildata);

                $this->obj->update_once('member', array('email' => '', 'email_status' => 0), array('uid' => array('<>', $uid), 'email' => $email));
                $this->obj->update_once('px_train', array('linkmail' => '', 'email_status' => 0), array('uid' => array('<>', $uid), 'linkmail' => $email));
                $this->obj->update_once('resume', array('email' => '', 'email_status' => 0), array('uid' => array('<>', $uid), 'email' => $email));
                $this->obj->update_once('company', array('linkmail' => '', 'email_status' => 0), array('uid' => array('<>', $uid), 'linkemail' => $email));
                $this->obj->update_once('lt_info', array('email' => '', 'email_status' => 0), array('uid' => array('<>', $uid), 'email' => $email));

                $msg = '新邮箱：' . $email;
                if (!empty($pInfo['linkmail']) && $pInfo['linkmail'] != $email) {
                    $msg .= '，原邮箱：' . $pInfo['linkmail'];
                }

                $this->MODEL('log')->addAdminLog("培训会员(ID" . $uid . ")认证邮箱【" . $email . "】");

                $this->admin_json(0, "邮箱认证成功(用户ID：{$uid}，{$msg})");
            } else {
                $this->render_json(1, '邮箱认证失败');
            }
        } else {
            $this->render_json(1, '数据异常');
        }
    }

    /**
     * 列表-手机认证
     */
    public function mobilestatus_action()
    {
        $post = $this->post_trim($_POST);

        $trainM = $this->MODEL('train');
        $userinfoM = $this->MODEL('userinfo');

        if (empty($post['mobile'])) {
            $this->render_json(1, '请输入手机号');
        } elseif (CheckMobile($post['mobile']) == false) {
            $this->render_json(1, '手机号格式错误');
        }

        $uid = intval($post['uid']);
        $status = $post['status'];
        $phone = intval($post['mobile']);

        $pInfo = $trainM->getInfo(array('uid' => $uid), array('field' => '`linktel`,`moblie_status`'));

        if (!empty($pInfo)) {
            if ($pInfo['linktel'] == $phone && $pInfo['moblie_status'] == 1) {
                $this->render_json(1, '手机号未变更，无需调整');
            }

            $data = array('linktel' => $phone, 'moblie_status' => $status);
            $nid = $trainM->upInfo(array('uid' => $uid), $data);

            if ($nid) {
                $memberdata = array('moblie' => $phone, 'moblie_status' => $status);

                $msg = '新手机号：' . $phone;
                if (!empty($pInfo['linktel'])) {
                    $msg .= '，原手机号：' . $pInfo['linktel'];
                }
                // 获取用户信息，用来判断旧手机号和用户名是否一致
                $member = $userinfoM->getInfo(array('uid' => $uid), array('field' => 'username,moblie'));
                if ($member['username'] == $member['moblie']) {

                    $memberdata['username'] = $phone;
                    $msg .= '，同步调整用户名为' . $phone;
                }
                // 有账号的用户名与新手机号一致的，将用户名改成新手机号
                $omb = $userinfoM->getInfo(array('username' => $phone), array('field' => 'uid'));
                if (!empty($omb)) {
                    // 如果现有数据中，存在用户名是这个手机号的，要修改
                    $userinfoM->upInfo(array('uid' => $omb['uid']), array('username' => $phone . '_s'));

                    $logDetail = '账号修改：账号（UID：' . $uid . '）认证手机号，因本账号用户名与该手机号相同，调整本账号（ID：' . $omb['uid'] . '）用户名（' . $phone . ' → ' . $phone . '_s）';

                    $logM = $this->MODEL('log');
                    $logM->addAdminLog($logDetail);
                }
                $userinfoM->upInfo(array('uid' => $uid), $memberdata);

                $this->obj->update_once('member', array('moblie' => '', 'moblie_status' => 0), array('uid' => array('<>', $uid), 'moblie' => $phone));
                $this->obj->update_once('resume', array('telphone' => '', 'moblie_status' => 0), array('uid' => array('<>', $uid), 'telphone' => $phone));
                $this->obj->update_once('company', array('linktel' => '', 'moblie_status' => 0), array('uid' => array('<>', $uid), 'linktel' => $phone));
                $this->obj->update_once('lt_info', array('moblie' => '', 'moblie_status' => 0), array('uid' => array('<>', $uid), 'moblie' => $phone));
                $this->obj->update_once('pr_train', array('linktel' => '', 'moblie_status' => 0), array('uid' => array('<>', $uid), 'linktel' => $phone));

                $this->render_json(0, "手机认证成功(用户ID：{$uid}，{$msg})");
            } else {
                $this->render_json(1, '手机认证失败');
            }
        } else {
            $this->render_json(1, '数据异常');
        }
    }

    /**
     * 列表-批量认证
     */
    public function batchfirm_action()
    {
        if (empty($_POST['uid']) || !is_array($_POST['uid']) || empty($_POST['type']) || !is_array($_POST['type']) || !isset($_POST['status']) || $_POST['status'] === '') {
            $this->render_json(1, '参数错误');
        }

        $TrainM = $this->MODEL('train');
        $UserinfoM = $this->MODEL('userinfo');
        $CompanyM = $this->MODEL('company');

        $uid = $_POST['uid'];
        $status = intval($_POST['status']);
        $type = $_POST['type'];
        $msg = array();

        $where['uid'] = array('in', pylode(',', $uid));

        $rows = $TrainM->getList($where, array('field' => '`uid`,`linktel`,`linkmail`,`email_status`,`moblie_status`'));

        if (in_array('email', $type) || in_array('mobile', $type)) {

            if (is_array($rows) && $rows) {
                if (in_array('email', $type)) {
                    array_push($msg, '邮箱');

                    foreach ($rows as $val) {
                        if ($val['linkmail'] || $val['email_status'] == 1) {
                            $emailuid[] = $val['uid'];
                        }
                    }

                    $emaildata = array('email_status' => $status);
                    $emailwhere['uid'] = array('in', pylode(',', $emailuid));

                    $UserinfoM->upInfo($emailwhere, $emaildata);
                    $TrainM->upInfo($emailwhere, $emaildata);
                }

                if (in_array('mobile', $type)) {
                    array_push($msg, '手机');

                    foreach ($rows as $val) {
                        if ($val['linktel'] || $val['moblie_status'] == 1) {
                            $moblieuid[] = $val['uid'];
                        }
                    }

                    $mobliewhere['uid'] = array('in', pylode(',', $moblieuid));
                    $mobliedata = array('moblie_status' => $status);

                    $UserinfoM->upInfo($mobliewhere, $mobliedata);
                    $TrainM->upInfo($mobliewhere, $mobliedata);
                }
            }
        }

        if (in_array('yyzz', $type)) {
            array_push($msg, '营业执照');

            if ($status != 0) {
                $yyzzwhere['uid'] = array('in', pylode(',', $uid));
                $yyzzwhere['type'] = 5;

                $yyzz = $CompanyM->getCertList($yyzzwhere, array('field' => '`uid`,`check`'));

                if (is_array($yyzz) && $yyzz) {
                    foreach ($yyzz as $val) {
                        if ($val['check']) {
                            $checkuid[] = $val['uid'];
                        }
                    }
                }
            } else {
                $checkuid[] = $uid;
            }

            $yyzzkwhere['uid'] = array('in', pylode(',', $checkuid));

            $yyzzkdata = array('yyzz_status' => $status);

            $TrainM->upInfo($yyzzkwhere, $yyzzkdata);

            $checkdata = array('status' => $status);

            $checwhere['uid'] = array('in', pylode(',', $checkuid));
            $checwhere['type'] = 5;

            $CompanyM->upCertInfo($checwhere, $checkdata, array('utype' => 'admin'));
        }

        $ty = $status = 1 ? '已认证' : '待认证';

        $this->admin_json(0, '(培训列表)' . implode(',', $msg) . '批量设置' . $ty . '成功(ID:' . pylode(',', $uid) . ')');
    }

    /**
     * 会员- 培训 - 培训认证审核
     * 审核认证 -> 获取审核数据
     * 2019-06-05 hjy
     */
    public function sbody_action()
    {
        $companyM = $this->MODEL('company');

        $userinfo = $companyM->getCertInfo(array('uid' => array('=', intval($_POST['uid'])), 'type' => 5), array('field' => '`statusbody`'));
        echo isset($userinfo['statusbody']) ? $userinfo['statusbody'] : '';
        die;
    }


    /**
     * 会员- 培训 - 培训认证审核
     * 审核认证 -> 保存审核数据
     * 2019-06-05 hjy
     */
    public function trainstatus_action()
    {
        $pxUid = trim($_POST['uid']);
        $pxStatus = intval($_POST['r_status']);
        if (empty($pxUid)) {
            $this->render_json(1, '参数错误');
        }

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
                $statusInfo = $tagName . ':' . $v['name'] . '审核未通过 ';

                if ($_POST['statusbody']) {
                    $statusInfo .= ', 原因：' . trim($_POST['statusbody']);
                }

                $msg[$v['uid']] = $statusInfo;
            } elseif ($pxStatus == 1) {
                $msg[$v['uid']] = $tagName . ':' . $v['name'] . '已审核通过';
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
        $id = $companyM->upCertInfo(array('uid' => array('in', $pxUid), 'type' => 5), $upData, array('utype' => 'admin'));
        if ($id) {
            $this->admin_json(0, '培训机构认证审核(UID:' . $pxUid . ')设置成功');
        } else {
            $this->render_json(1, '设置失败');
        }
    }

    // 培训用户跳转
    public function Imitate_action()
    {
        if (empty($_POST['uid'])) {
            $this->render_json(1, '参数错误');
        }

        $userinfoM = $this->MODEL('userinfo');

        $member = $userinfoM->getInfo(array('uid' => intval($_POST['uid'])), array('field' => '`uid`,`username`,`salt`,`email`,`password`,`usertype`,`did`'));
        if (empty($member)) {
            $this->render_json(1, '数据异常');
        }

        $this->cookie->unset_cookie($_SESSION['auid']);

        $this->cookie->add_cookie($member['uid'], $member['username'], $member['salt'], $member['email'], $member['password'], 4, $this->config['sy_logintime'], $member['did'], '1');

        $logM = $this->MODEL('log');

        $content = '管理员' . $_SESSION['ausername'] . '登录培训账户(ID:' . $member['uid'] . ')';

        $logM->addAdminLog($content);

        $this->render_json(0, 'ok', array('url' => $this->config['sy_weburl'] . '/member'));
    }

    /**
     * 会员  -  : 解绑日志
     */
    function unbindingLog_action()
    {
        $where['opera'] = 12;

        $where['PHPYUNBTWSTART_A'] = '';
        $where['content'][] = array('like', '解除绑定');
        $where['content'][] = array('like', '解绑', 'OR');
        $where['content'][] = array('like', '解除', 'OR');
        $where['PHPYUNBTWEND_A'] = '';

        $where['usertype'] = 4;

        if ($_POST['keyword']) {
            $type = intval($_POST['type']);
            $keyword = trim($_POST['keyword']);

            if ($type == 1) {
                $userInfoM = $this->MODEL('userinfo');
                $member = $userInfoM->getList(array('username' => array('like', $keyword)), array('field' => '`uid`'));
                if ($member) {
                    $muids = array();
                    foreach ($member as $val) {
                        $muids[] = $val['uid'];
                    }
                    $where['uid'] = array('in', pylode(',', $muids));
                }
            } elseif ($type == 2) {
                $where['content'] = array('like', $keyword);
            }
        }

        if ($_POST['time_start'] != "" && $_POST['time_end'] != "") {
            $where['PHPYUNBTWSTART'] = '';
            $where['ctime'][] = array('>=', strtotime($_POST['time_start']));
            $where['ctime'][] = array('<=', strtotime($_POST['time_end'] . ' 23:59:59'));
            $where['PHPYUNBTWEND'] = '';
        }

        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('member_log', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        //分页数大于0的情况下 执行列表查询
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = 'id';
            }
            $where['limit'] = $pages['limit'];

            $logM = $this->MODEL('log');
            $list = $logM->getMemlogList($where, array('utype' => 'admin'));
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }


    /**
     * 会员 - ： 解绑日志删除
     */
    function unbindingLogDel_action()
    {
        if (empty($_POST['del'])) {
            $this->render_json(1, '参数错误');
        }

        $del = $_POST['del'];
        $ids = pylode(',', $del);
        if (is_array($_POST['del'])) {
            $where = array('id' => array('in', $ids));
        } else {
            if (trim($_POST['del']) == 'all') {
                $ids = 'all';
                $where = array('usertype' => 4, 'opera' => '12');
            } else {
                $where = array('id' => $ids);
            }
        }

        $logM = $this->MODEL('log');

        $return = $logM->delMemlog($where);

        if ($return['errcode'] == 9) {
            $this->admin_json(0, $ids == 'all' ? '解绑记录清空成功' : '解绑记录（ID:' . $ids . '）删除成功');
        } else {
            $this->render_json(1, '解绑记录删除失败');
        }
    }

    // 会员日志-培训日志
    function log_action()
    {
        $logM = $this->MODEL('log');
        $memberM = $this->MODEL('userinfo');
        $where['usertype'] = 4;
        if (isset($_POST['uid'])) {
            $where['uid'] = intval($_POST['uid']);
        }

        $keywordStr = trim($_POST['keyword']);

        if (!empty($keywordStr)) {
            if ($_POST['type'] == 1) {
                $member = $memberM->getList(array('username' => array('like', $keywordStr)), array('field' => '`uid`,`username`'));
                foreach ($member as $v) {
                    $uid[] = $v['uid'];
                }
                $where['uid'] = array('in', pylode(",", $uid));
            } elseif ($_POST['type'] == 3) {
                $where['uid'] = $keywordStr;
            }
        }

        $contentStr = trim($_POST['content']);

        if (!empty($contentStr)) {
            $logDetailList = $this->obj->select_all('member_log_detail', array('detail' => array('like', $contentStr)), 'log_id');
            if (!empty($logDetailList)) {
                $logIds = array();
                foreach ($logDetailList as $lk => $lv) {

                    $logIds[] = $lv['log_id'];
                }

                $where['PHPYUNBTWSTART_A'] = '';
                $where['id'] = array('in', pylode(',', $logIds), '');
                $where['content'] = array('like', $contentStr, 'OR');
                $where['PHPYUNBTWEND_A'] = '';
            } else {
                $where['content'] = array('like', $contentStr);
            }
        }

        if (!empty($_POST['end'])) {
            if ($_POST['end'] == '1') {
                $where['ctime'] = array('>=', strtotime(date("Y-m-d 00:00:00")));
            } else {
                $where['ctime'] = array('>=', strtotime('-' . (int)$_POST['end'] . 'day'));
            }
        }

        if (!empty($_POST['operas'])) {
            $operaStr = intval($_POST['operas']);

            $operaSql = array(
                '1' => array('name' => array('职位')),
                '2' => array('name' => array('创建', '简历', '经历')),
                '3' => array('name' => array('下载')),
                '4' => array('name' => array('邀请')),
                '5' => array('name' => array('收藏', '关注', '备注')),
                '6' => array('name' => array('申请', '报名', '应聘', '委托')),
                '7' => array('name' => array('基本信息')),
                '8' => array('name' => array('修改密码')),
                '9' => array('name' => array('兼职')),
                '10' => array('name' => array('猎头职位')),
                '11' => array('name' => array('用户名', '身份')),
                '12' => array('name' => array('账号认证', '解除', '绑定', '验证', '资质', '执照', '认证')),
                '14' => array('name' => array('招聘会', '专题')),
                '15' => array('name' => array('地图', '助力')),
                '16' => array('name' => array('图片', '头像', 'LOGO', '环境', '产品', '新闻', '二维码', '横幅')),
                '17' => array('name' => array('兑换', '积分'), 'realId' => 17),
                '18' => array('name' => array('回复', '咨询', '留言', '系统消息')),
                '19' => array('name' => array('问答')),
                '20' => array('name' => array('培训师', '讲师')),
                '21' => array('name' => array('课程')),
                '22' => array('name' => array('新闻')),
                '23' => array('name' => array('举报')),
                '24' => array('name' => array('优惠券')),
                '25' => array('name' => array('悬赏', '推送')),
                '26' => array('name' => array('浏览', '黑名单')),
                '27' => array('name' => array('子账号')),
                '29' => array('name' => array('项目')),
                '30' => array('name' => array('聊天', '直聊')),
                '88' => array('name' => array('订单'))
            );

            if (array_key_exists($operaStr, $operaSql)) {
                if (count($operaSql[$operaStr]['name']) == 1) {
                    $where['content'] = array('like', $operaSql[$operaStr]['name'][0]);
                } else {
                    $where['PHPYUNBTWSTART'] = '';
                    foreach ($operaSql[$operaStr]['name'] as $oV) {
                        $where['content'][] = array('like', $oV, 'OR');
                    }
                    $where['PHPYUNBTWEND'] = '';
                }
            } elseif (!empty($operasStr)) {
                $where['opera'] = $operaStr;
            }
        }

        if (isset($_POST['parrs'])) {
            $where['type'] = intval($_POST['parrs']);
        }

        if ($_POST['time_start'] != "" && $_POST['time_end'] != "") {
            $where['ctime'][] = array('>=', strtotime($_POST['time_start']));
            $where['ctime'][] = array('<=', strtotime($_POST['time_end'] . " 23:59:59"));
        }

        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('member_log', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        $list = array();
        if ($pages['total'] > 0) {
            if ($_POST['order']) {
                $where['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $where['orderby'] = array('id,desc');
            }
            $where['limit'] = $pages['limit'];

            $list = $logM->getMemlogList($where, array('utype' => 'admin'));

            foreach ($list as $key => $val) {
                $list[$key]['train_url'] = Url('train', array('c' => 'agencyshow', 'look' => 'admin', 'id' => $val['uid']));
            }
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
    }
    public function  getLogSearchData_action(){
        $opera = array(7 => '基本信息', 8 => '修改密码', 11 => '修改账号', 12 => '账号认证', 20 => '讲师', 21 => '课程', 6 => '预约报名', 22 => '新闻', 16 => '图片', 17 => '积分兑换', 18 => '消息', 19 => '问答');
        $search_list[] = array('param' => 'operas', 'name' => '操作内容', "value" => $opera);

        $ad_time = array('1' => '今天', '3' => '最近三天', '7' => '最近七天', '15' => '最近半月', '30' => '最近一个月');
        $search_list[] = array("param" => "end", "name" => '操作时间', "value" => $ad_time);
        $this->render_json(0,'',compact('search_list'));
    }
    // 会员日志删除
    function logDel_action()
    {
        if (empty($_POST['del'])) {
            $this->render_json(1, '参数错误');
        }

        $logM = $this->MODEL('log');

        $del = $_POST['del'];
        $ids = pylode(',', $del);
        if (is_array($del)) {
            $where['id'] = array('in', $ids);
        } else {
            if (trim($_POST['del']) == 'all') {
                $ids = 'all';
                $where['usertype'] = 4;
            } else {
                $where['id'] = $ids;
            }
        }

        $return = $logM->delMemlog($where);

        if ($return['errcode'] == 9) {
            $this->admin_json(0, $ids == 'all' ? '培训日志清空成功' : '培训日志（ID:' . $ids . '）删除成功');
        } else {
            $this->render_json(1, '培训日志删除失败');
        }
    }
}

?>