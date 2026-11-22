<?php
/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2021 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/
class staff_controller extends staff_base_controller{
    // 综合平台触发分站外呼更新通话记录
    function refresh_action(){
        //校验网站外呼key
        if (trim($_GET['token']) != trim($this->config['sy_outcall_appkey'])) {
            echo 'fail';
        }
        $callLogM = $this->MODEL('staffcalllog');
        $callLogM->refreshCallLogByLogId();
        echo 'success';
    }
}
?>
