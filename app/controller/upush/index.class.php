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

class index_controller extends common
{

    function index_action()
    {

        if (!empty($_POST)) {

            $crmM   =   $this->MODEL('crm');
            $typeStr=   intval($_POST['type']);

            if ($typeStr == 1) {

                $return =   $crmM->getRecInfo(array('id' => $_POST['id']), array('from' => 'u'));
            } else if ($typeStr == 2) {

                $return =   $crmM->getRecJobInfo(array('id' => $_POST['id']), array('from' => 'u'));
            }

            $data['list']   =   $return['list'];
            $data['info']   =   $return['info'];

            echo json_encode(array('error' => 0, 'data' => $data));
            exit();
        }

        if ($_GET['str']) {

            if (is_weixin()) {

                if ($this->config['wx_appid'] && $this->config['wx_appsecret']) {
                    $signPackage = getWxJsSdk();
                    $this->yunset('signPackage', $signPackage);
                }
                $this->yunset('isweixin', 1);

            }

            include_once LIB_PATH.'shortLink.php';
            $intStr     =   b62to10($_GET['str']);               //  解析随机字符串

            $typeStr    =   substr($intStr, 0, 1);  //  取整数第一位

            if ($typeStr == 1){         //  简历推送

                $crmResume  =   $this->obj->select_once('crm_resume', array('ids' => $intStr));

                $id =   intval($crmResume['id']);
                $this->yunset('id', $id);
                $this->obj->update_once('crm_resume', array('hits' => array('+', 1)), array('id' => $id));

                $this->yunset('title', '简历推送 - '.$this->config['sy_webname']);
                $this->yuntpl(array('wap/crm_resume'));
            }else if ($typeStr == 2){   //  职位推广

                $crmJob     =   $this->obj->select_once('crm_job', array('ids' => $intStr));

                $id =   intval($crmJob['id']);
                $this->yunset('id', $id);
                $this->obj->update_once('crm_job', array('hits' => array('+', 1)), array('id' => $id));

                $title      =   !empty($crmJob['title']) ? $crmJob['title'] : '职位推广 - '.$this->config['sy_webname'];
                $desc       =   !empty($crmJob['desc']) ? $crmJob['desc'] : '职位推广 - '.$this->config['sy_webname'];
                $shareLogo  =   !empty($crmJob['logo']) ? checkpic($crmJob['logo']) : checkpic($this->config['sy_crmrecshare_logo']);
                $this->yunset('title' , $title);
                $this->yunset('desc', $desc);
                if ($this->config['sy_seo_rewrite'] == 1) {

                    $url    =   $this->config['sy_weburl'].'/u/'.$_GET['str'];
                } else {

                    $url    =   $this->config['sy_weburl'].'/index.php?m=upush&str='.$_GET['str'];
                }
                $this->yunset(array('title' => $title, 'description' => $desc, 'share_url' => $url, 'share_logo' => $shareLogo, 'Info' => array('photo' => $shareLogo)));

                $this->yuntpl(array('wap/crm_job'));
            }
        }
    }
}

?>