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

    public function index_action()
    {

        $workM = $this->MODEL('workwx');

        $queryString    =   explode('&', urldecode($_SERVER['QUERY_STRING']));
        $data           =   array();
        foreach ($queryString as $k => $v) {
            if ($k == 0) {

                $data['msg_signature'] = str_replace('msg_signature=', '', $v);
            } elseif ($k == 1) {

                $data['timestamp'] = str_replace('timestamp=', '', $v);
            } elseif ($k == 2) {

                $data['nonce'] = str_replace('nonce=', '', $v);
            } elseif ($k == 3) {

                $data['echostr'] = str_replace('echostr=', '', $v);
            }
        }
        if ($_GET['echostr']) {

            $workM->valid($data);
        } else {

            $postStr    =   file_get_contents("php://input");

            $workM->callBackEvent($data, $postStr);
        }
    }

}

?>