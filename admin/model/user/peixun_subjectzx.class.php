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

class peixun_subjectzx_controller extends adminCommon
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
            'param' => 'end',
            'name' => '咨询时间',
            'value' => $this->timeSection
        );

        return $search_list;
    }

    /**
     * 会员 - 培训 - 咨询留言
     * 2019-06-06 hjy
     */
    public function index_action()
    {
        $pxWhereData = array();

        $endStr = intval($_POST['end']);
        //咨询时间条件
        if (!empty($endStr)) {
            if ($endStr == 1) {
                $pxWhereData['ctime'] = array('>=', strtotime(date("Y-m-d")));
            } else {
                $pxWhereData['ctime'] = array('>=', strtotime('-' . $endStr . 'day'));
            }
        }
        $rtStr = intval($_POST['r_time']);
        //回复时间条件
        if (!empty($rtStr)) {
            if ($rtStr == 1) {
                $pxWhereData['reply_time'] = array('>=', strtotime(date("Y-m-d")));
            } else {
                $pxWhereData['reply_time'] = array('>=', strtotime('-' . $rtStr . 'day'));
            }
        }

        $keywordStr = trim($_POST['keyword']);
        $typeStr = trim($_POST['type']);

        //咨询内容条件
        if (!empty($keywordStr) && $typeStr == 3) {
            $pxWhereData['content'] = array('like', $keywordStr);
        }

        //回复内容条件
        if (!empty($keywordStr) && $typeStr == 4) {
            $pxWhereData['reply'] = array('like', $keywordStr);
        }

        //咨询人条件
        $resumeM = $this->MODEL('resume');
        $comM = $this->MODEL('company');
        $ltM = $this->MODEL('lietou');
        $userId = array();
        if (!empty($keywordStr) && $typeStr == 1) {
            $member = $resumeM->getResumeList(array('name' => array('like', $keywordStr)), array('field' => '`uid`'));
            if ($member) {
                $userId = array_column($member, 'uid');
            }

            $company = $comM->getChCompanyList(array('name' => array('like', $keywordStr)), array('field' => '`uid`'));
            if ($company) {
                $userId = array_merge($userId, array_column($company, 'uid'));
            }

            $ltinfo = $ltM->getList(array('realname' => array('like', $keywordStr)), array('field' => '`uid`'));
            if ($ltinfo) {
                $userId = array_merge($userId, array_column($ltinfo, 'uid'));
            }
            $userId = $userId ? array_unique($userId) : array(0);
        }
        if (!empty($userId)) {
            $pxWhereData['uid'] = array('in', pylode(',', $userId));
        }

        //提取分页
        $pageM = $this->MODEL('page');
        $pages = $pageM->adminPageList('px_zixun', $pxWhereData, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];

        $pxM = $this->MODEL('train');

        //分页数大于0的情况下 执行列表查询
        $list = array();
        if ($pages['total'] > 0) {
            //limit order 只有在列表查询时才需要
            if (!empty($_POST['order']) && !empty($_POST['t'])) {
                $pxWhereData['orderby'] = $_POST['t'] . ',' . $_POST['order'];
            } else {
                $pxWhereData['orderby'] = 'id';
            }

            $pxWhereData['limit'] = $pages['limit'];
            $list = $pxM->getPxzxList($pxWhereData);
        }

        if (!empty($list)) {
            $pxuid = $comuid = array();
            foreach ($list as $lv) {
                $pxuid[] = $lv['s_uid'];
                $comuid[] = $lv['uid'];
            }
            //补充培训用户相关的信息
            $bcWhereData = array('uid' => array('in', pylode(',', $pxuid)));
            $pxField = '`uid`, `name`';
            $pxList = $pxM->getList($bcWhereData, array('field' => $pxField));
            $pxListIndex = array();
            if (!empty($pxList)) {
                foreach ($pxList as $pxV) {
                    $pxListIndex[$pxV['uid']] = $pxV;
                }
            }
            //补充个人用户相关的信息
            $reWhereData = array('uid' => array('in', pylode(',', $comuid)));
            $reList = $resumeM->getResumeList($reWhereData, array('field' => '`uid`, `name`'));
            $reListIndex = array();
            if (!empty($reList)) {
                foreach ($reList as $reV) {
                    $reListIndex[$reV['uid']] = $reV;
                }
            }
            //补充企业用户相关的信息
            $comList = $comM->getList($reWhereData, array('field' => '`uid`, `name`'));
            $comListIndex = array();
            if (!empty($comList)) {
                foreach ($comList['list'] as $comV) {
                    $comListIndex[$comV['uid']] = $comV;
                }
            }
            //补充猎头用户相关的信息
            $ltList = $ltM->getList($reWhereData, array('field' => '`uid`, `realname`'));
            $ltListIndex = array();
            if (!empty($ltList)) {
                foreach ($ltList as $ltV) {
                    $ltListIndex[$ltV['uid']] = $ltV;
                }
            }
            //补充进相关信息
            foreach ($list as $Lk => $Lv) {
                $list[$Lk]['content'] = str_replace('"', "", $Lv['content']);
                $list[$Lk]['reply'] = str_replace('"', "", $Lv['reply']);
                if (isset($pxListIndex[$Lv['s_uid']])) {
                    $list[$Lk]['pxname'] = $pxListIndex[$Lv['s_uid']]['name'];
                }
                if (isset($reListIndex[$Lv['uid']]) && $Lv['usertype'] == 1) {
                    $list[$Lk]['zname'] = $reListIndex[$Lv['uid']]['username_n'];
                }
                if (isset($comListIndex[$Lv['uid']]) && $Lv['usertype'] == 2) {
                    $list[$Lk]['zname'] = $comListIndex[$Lv['uid']]['name'];
                }
                if (isset($ltListIndex[$Lv['uid']]) && $Lv['usertype'] == 3) {
                    $list[$Lk]['zname'] = $ltListIndex[$Lv['uid']]['realname'];
                }

                $list[$Lk]['train_url'] = Url('train', array('c' => 'agencyshow', 'id' => $Lv['s_uid']));
            }
        }

        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page','pxuidcs'));
    }
    public function  getSearchData_action(){
        $search_list = $this->set_search();
        $this->render_json(0,'',compact('search_list'));
    }
    /**
     * 会员 - 培训 - 咨询留言
     * 删除咨询留言
     * 2019-06-06 hjy
     */
    function del_action()
    {
        if (empty($_POST['del'])) {
            $this->render_json(1, '参数错误');
        }

        $del = $_POST['del'];

        $pxM = $this->MODEL('train');
        $return = $pxM->delPxzx($del);
        if ($return['errcode'] == 9) {
            $this->admin_json(0, '课程咨询(ID:' . pylode(',', $del) . ')删除成功');
        } else {
            $this->render_json(1, '课程咨询删除失败');
        }
    }
}

?>