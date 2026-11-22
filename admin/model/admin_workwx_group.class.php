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

class admin_workwx_group_controller extends adminCommon
{

    function index_action()
    {

        if (!empty($_GET['keyword'])) {

            $where['name']      =   array('like', trim($_GET['keyword']));
            $urlarr['keyword']  =   $_GET['keyword'];
        }

        $urlarr        	=   $_GET;
        $urlarr['page']	=	"{{page}}";

        $pageurl		=	Url($_GET['m'],$urlarr,'admin');
        $pageM			=	$this -> MODEL('page');
        $pages			=	$pageM -> pageList('work_group',$where,$pageurl,$_GET['page']);

        if($pages['total'] > 0){

            //排序
            if($_GET['order']){

                $where['orderby']   =   $_GET['t'].','.$_GET['order'];
                $urlarr['order']    =	$_GET['order'];
                $urlarr['t']	    =	$_GET['t'];

            }else{
                $where['orderby']   =  'id';
            }

            $where['limit'] =   $pages['limit'];
            $workWxM        =   $this->MODEL('workwx');

            $groupChatList  =   $workWxM->getGroupChatList($where);

            $this->yunset('list', $groupChatList);
        }

        $this->yuntpl(array('admin/work/group'));
    }

    /**
     * 同步客户群列表
     */
    public function sync_action()
    {

        $workWxM=   $this->MODEL('workwx');
        $return =   $workWxM->syncGroupChatList(array('cursor' => $_POST['cursor'], 'page' => $_POST['page'], 'limit' => $_POST['limit']));
        echo json_encode($return);
        exit();
    }

    /**
     * 同步客户群
     */
    public function syncInfo_action()
    {

        if ($_POST){

            $workWxM=   $this->MODEL('workwx');
            $return =   $workWxM->syncGroupInfo(array('chat_id' => $_POST['chat_id']));
            echo json_encode($return);
            exit();;
        }
    }

    function info_action(){

        if ($_GET['chat_id']){

            $workM      =   $this->MODEL('workwx');
            $groupInfo  =   $workM->getGroupChatInfo(array('chat_id' => $_GET['chat_id']));
            $this->yunset('groupInfo', $groupInfo);

            $where          =   array('chat_id' => $_GET['chat_id']);

            $urlarr         =   $_GET;
            $urlarr['page'] =   "{{page}}";

            $pageurl        =   Url($_GET['m'], $urlarr, 'admin');
            $pageM          =   $this->MODEL('page');
            $pages          =   $pageM->pageList('work_group_member', $where, $pageurl, $_GET['page']);

            if($pages['total'] > 0){
                if($_GET['order']){

                    $where['orderby']   =   $_GET['t'].','.$_GET['order'];
                    $urlarr['order']    =	$_GET['order'];
                    $urlarr['t']	    =	$_GET['t'];
                }else{

                    $where['orderby']   =  'join_time, desc';
                }

                $where['limit'] =   $pages['limit'];
                $workWxM        =   $this->MODEL('workwx');
                $groupMemberList=   $workWxM->getGroupMemberList($where);
                $this->yunset('list', $groupMemberList);
            }
        }

        $this->yuntpl(array('admin/work/group_info'));
    }

    function memberNum_action()
    {

        if ($_GET['chat_id']){

            $workM  =   $this->MODEL('workwx');

            echo $workM->groupMemberNum($_GET['chat_id']);
        }
    }
}

?>