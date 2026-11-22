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

class work_controller extends common
{

    function index_action()
    {

        $nonceStr   =   'phpyun';

        $LinkTicket     =   getJsApiTicket('link');
        $LinTicket      =   $LinkTicket['ticket'];
        $LinkTimestamp  =   $LinkTicket['ticket_time'];

        $agentTicket    =   getJsApiTicket('agent');
        $AgentTicket    =   $agentTicket['ticket'];
        $AgentTimestamp =   $agentTicket['ticket_time'];

        $url            =   $this->config['sy_weburl'].'/wap/index.php?c=work';

        $LinkString     =   'jsapi_ticket='.$LinTicket.'&noncestr='.$nonceStr.'&timestamp='.$LinkTimestamp.'&url='.$url;
        $LinkSignature  =   sha1($LinkString);

        $AgentString    =   'jsapi_ticket='.$AgentTicket.'&noncestr='.$nonceStr.'&timestamp='.$AgentTimestamp.'&url='.$url;
        $AgentSignature =   sha1($AgentString);

        $this->yunset(array('appId' => $this->config['wx_qy_corpid'], 'nonceStr' => $nonceStr, 'link_timestamp' => $LinkTimestamp, 'link_signature' => $LinkSignature, 'agentId' => $this->config['wx_photo_agentId'], 'agent_timestamp' => $AgentTimestamp, 'agent_signature' => $AgentSignature));

        $this->yunset('headertitle', '用户画像');
        $this->yuntpl(array('wap/work/index'));
    }

    /**
     * 查询用户信息
     */
    public function getUserInfo_action()
    {

        $workM      =   $this->MODEL('workwx');

        $userInfo   =   $workM->getCustomerInfo($_POST['userId']);

        echo json_encode($userInfo);
        die;
    }

    /**
     * 浏览记录
     */
    public function getLookList_action()
    {

        $workM      =   $this->MODEL('workwx');

        $page       =   $_POST['page'];
        $limit      =   $_POST['limit'];

        $where['uid']       =   $_POST['uid'];
        $where['orderby']	=	'datetime,desc';

        if ($page) {

            $pagenav        =   ($page - 1) * $limit;
            $where['limit'] =   array($pagenav, $limit);
        } else {

            $where['limit'] =   array('', $limit);
        }

        $list       =   $workM->getLookList($where);

        echo json_encode($list);
        die;
    }

    /**
     * 投递记录
     */
    public function getSqList_action()
    {

        $workM      =   $this->MODEL('workwx');

        $page       =   $_POST['page'];
        $limit      =   $_POST['limit'];

        $where['uid']       =   $_POST['uid'];
        $where['orderby']	=	'datetime,desc';

        if ($page) {

            $pagenav        =   ($page - 1) * $limit;
            $where['limit'] =   array($pagenav, $limit);
        } else {

            $where['limit'] =   array('', $limit);
        }

        $list       =   $workM->getSqList($where);

        echo json_encode($list);
        die;
    }

    /**
     * 职位数据
     */
    public function getJobList_action()
    {

        $workM      =   $this->MODEL('workwx');

        $page       =   $_POST['page'];
        $limit      =   $_POST['limit'];

        if (!empty($_POST['keyword'])){

            $where['PHPYUNBTWSTART_A']  =   '';
            $where['name']              =   array('like', trim($_POST['keyword']));
            $where['com_name']          =   array('like', trim($_POST['keyword']), 'OR');
            $where['PHPYUNBTWEND_A']    =   '';
        }

        $where['orderby']	=	'lastupdate,desc';

        if ($page) {

            $pagenav        =   ($page - 1) * $limit;
            $where['limit'] =   array($pagenav, $limit);
        } else {

            $where['limit'] =   array('', $limit);
        }

        $list   =   $workM->getMatchJobList($_POST['uid'], $where);

        echo json_encode($list);
        die;
    }
    /**
     * 行为轨迹
     */
    public function getLogList_action()
    {

        $workM      =   $this->MODEL('workwx');

        $page       =   $_POST['page'];
        $limit      =   $_POST['limit'];

        $where['uid']       =   $_POST['uid'];
        $where['orderby']	=	'time,desc';

        if ($page) {

            $pagenav        =   ($page - 1) * $limit;
            $where['limit'] =   array($pagenav, $limit);
        } else {

            $where['limit'] =   array('', $limit);
        }

        $list       =   $workM->getLogList($where);

        echo json_encode($list);
        die;
    }

    /**
     * 备注简历
     */
    public function remarkResume_action()
    {

        if ($_POST) {

            $workM  =   $this->MODEL('workwx');

            $return =   $workM->remarkResume(array('content' => $_POST['content']), array('uid' => $_POST['uid'], 'defaults' => 1));

            echo json_encode($return);
            die;
        }
    }

    public function auth_action()
    {

        $corpId     =   $this->config['wx_qy_corpid'];
        $redirectUrl=   UrlEncode($this->config['sy_weburl'].'/wap/index.php?c=work&a=auth');
        $state      =   'webauth@phpyun';
        $agentId    =   $this->config['wx_photo_agentId'];
        $authUrl    =   'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$corpId.'&redirect_uri='.$redirectUrl.'&response_type=code&scope=snsapi_privateinfo&state='.$state.'&agentid='.$agentId.'#wechat_redirect';
        $this->yunset(array('authUrl' => $authUrl));

        if (isset($_GET['code']) && isset($_GET['state']) && $_GET['state'] == $state){
            $this->yunset(array('code' => $_GET['code']));
            $num    =   2;
        }else{
            $num    =   1;
        }
        $this->yunset('num', $num);
        $this->yuntpl(array('wap/work/auth'));
    }

    /**
     * 网页授权，获取访问用户信息
     */
    public function getAuthUserInfo_action()
    {

        $workM      =   $this->MODEL('workwx');
        $userInfo   =   $workM->getAuthUserInfo($_POST['code']);
        echo json_encode($userInfo);
        die;
    }

    /**
     * 网页授权，获取访问用户敏感信息
     */
    public function getAuthDetailInfo_action()
    {

        $workM      =   $this->MODEL('workwx');
        $userInfo   =   $workM->getAuthUserDetail($_POST['user_ticket']);
        echo json_encode($userInfo);
        die;
    }
}
?>