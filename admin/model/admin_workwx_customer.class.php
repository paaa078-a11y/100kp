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

class admin_workwx_customer_controller extends adminCommon
{

    function index_action()
    {

        $where      =   array();

        $keywordStr =   trim($_GET['keyword']);

        if (isset($_GET['keyword']) && !empty($keywordStr)) {

            $where['name']      =   array('like', base64_encode($keywordStr));
            $urlarr['keyword']  =   $_GET['keyword'];
        }

        if (isset($_GET['userid'])) {

            $where['userid']    =   $_GET['userid'];
            $urlarr['userid']   =   $_GET['userid'];
        }

        $urlarr        	=   $_GET;
        $urlarr['page']	=	"{{page}}";

        $pageurl		=	Url($_GET['m'],$urlarr,'admin');
        $pageM			=   $this->MODEL('page');
        $pages			=   $pageM->pageList('work_customer', $where, $pageurl, $_GET['page']);

        if($pages['total'] > 0){
            if($_GET['order']){

                $where['orderby']   =   $_GET['t'].','.$_GET['order'];
                $urlarr['order']    =	$_GET['order'];
                $urlarr['t']	    =	$_GET['t'];
            }else{

                $where['orderby']   =  array('createtime, desc');
            }

            $where['limit'] =  $pages['limit'];

            $workWxM        =   $this->MODEL('workwx');
            $customerList   =   $workWxM->getWorkCustomerList($where);
            $this->yunset('list', $customerList);
        }

        $this->yuntpl(array('admin/work/customer'));
    }

    function customerNum_action()
    {

        $workM  =   $this->MODEL('workwx');

        echo $workM->customerNum($_GET['chat_id']);
    }

    function info_action()
    {

        if ($_GET['userid']){

            $workM  =   $this->MODEL('workwx');

            $customerInfo   =   $workM->getWorkCustomerInfo(array('external_userid' => $_GET['userid']));

            $this->yunset('customerInfo', $customerInfo);
        }

        $this->yuntpl(array('admin/work/customer_info'));
    }

    /**
     * @desc 批量获取客户详情
     */
    public function syncCustomer_action()
    {

        $workWxM = $this->MODEL('workwx');

        $return = $workWxM->syncCustomerList(array('cursor' => $_POST['cursor'], 'page' => $_POST['page'], 'limit' => $_POST['limit']));

        echo json_encode($return);

        exit();;
    }
}

?>