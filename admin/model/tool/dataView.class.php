<?php

/**
 * Class dataView_controller
 *
 * @author shiy
 * @version 7.0
 */

class dataView_controller extends adminCommon
{

    function index_action()
    {


        $dataVConfig    =   array();

        $cache          =   $this->MODEL('cache')->GetCache('datavconfig');

        $diyData        =   !empty($cache['datavConfig']['diydata']) ? json_decode($cache['datavConfig']['diydata'], true) : array();

        $dataVConfig['diyData']     =   $diyData;

        if ($this->config['sy_datavurl'] && $this->config['sy_datav_token']) {

            $url        =   $this->config['sy_datavurl'].'/index.php?token='.$this->config['sy_datav_token'];
        } else {
            if ($this->config['sy_datavurl'] && strpos($this->config['sy_datavurl'], $this->config['sy_weburl']) != false) {

                $dataVUrl   =   $this->config['sy_datavurl'];
            } else {

                $dataVUrl   =   $this->config['sy_weburl'].'/datav';
            }
            $url        =   $this->newUrl($dataVUrl);
        }

        $dataVConfig['dataVUrl']    =   $this->config['sy_datavurl'] ? $this->config['sy_datavurl'] : $this->config['sy_weburl'];
        $dataVConfig['url']         =   $url;
        $this->render_json(0, '', $dataVConfig);
    }

    private function newUrl($url)
    {

        $characters     =   '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString   =   '';
        for ($i = 0; $i < 6; $i++) {

            $randomString   .=  $characters[rand(0, strlen($characters) - 1)];
        }

        $config['sy_datavurl']      =   $url;
        $config['sy_datav_token']   =   $randomString;

        $configM    =   $this->MODEL('config');

        $configM->setConfig($config);

        $this->web_config();

        return $url.'/index.php?token='.$randomString;
    }

    public function setNewUrl_action()
    {

        $url    =   $_POST['url'] && strpos($_POST['url'], $this->config['sy_weburl']) != false ? $_POST['url'] : $this->config['sy_weburl'].'/datav';

        $data   =   array();
        if ($url) {

            $_url   =   $this->newurl($url);
            $data['url']    =   $_url;

            $error  =   0;
            $msg    =   '数据大屏链接生成成功！';
        } else {

            $error  =   1;
            $msg    =   '数据大屏链接生成失败！';
        }

        $this->admin_json($error, $msg, $data);

    }

    public function setDiyData_action()
    {

        if (!empty($_POST)) {

            $diyData    =   array(

                'datavtitle'    =>  trim($_POST['datavtitle']),
                'allcomnum'     =>  intval($_POST['allcomnum']),
                'daycomnum'     =>  intval($_POST['daycomnum']),
                'allusernum'    =>  intval($_POST['allusernum']),
                'dayusernum'    =>  intval($_POST['dayusernum']),
                'alljobnum'     =>  intval($_POST['alljobnum']),
                'dayjobnum'     =>  intval($_POST['dayjobnum']),
                'allzphnum'     =>  intval($_POST['allzphnum']),
                'dayzphnum'     =>  intval($_POST['dayzphnum']),
                'yearuser_monthreg' =>  intval($_POST['yearuser_monthreg']),
                'yearuser_monthreg_rand'    =>  intval($_POST['yearuser_monthreg_rand']),
                'monthreg_user' =>  intval($_POST['monthreg_user']),
                'dayreg_user'   =>  intval($_POST['dayreg_user']),
                'year_sqnum'    =>  intval($_POST['year_sqnum']),
                'year_yqnum'    =>  intval($_POST['year_yqnum']),
                'year_chatnum'  =>  intval($_POST['year_chatnum']),
                'year_lrnum'    =>  intval($_POST['year_lrnum']),
                'year_ljnum'    =>  intval($_POST['year_ljnum']),
                'yearcom_regnum'    =>  intval($_POST['yearcom_regnum']),
                'yearcom_monthreg'  =>  intval($_POST['yearcom_monthreg']),
                'yearcom_monthreg_rand' =>  intval($_POST['yearcom_monthreg_rand']),
                'hothy_sqnum'       =>  intval($_POST['hothy_sqnum']),
                'hothy_sqnum_rand'  =>  intval($_POST['hothy_sqnum_rand']),
                'hothy_jobs'        =>  intval($_POST['hothy_jobs']),
                'hothy_jobs_rand'   =>  intval($_POST['hothy_jobs_rand']),
                'hotjob_sqnum'      => intval($_POST['hotjob_sqnum']),
                'hotjob_sqnum_rand' =>  intval($_POST['hotjob_sqnum_rand']),
                'hotjob_jobs'       =>  intval($_POST['hotjob_jobs']),
                'hotjob_jobs_rand'  =>  intval($_POST['hotjob_jobs_rand']),
            );

            $config['diydata']  =   json_encode($diyData, JSON_UNESCAPED_UNICODE);

            $configM    =   $this->MODEL('config');

            $configM->setDatavConfig($config);

            include(LIB_PATH."cache.class.php");
            $cacheClass =   new cache(PLUS_PATH, $this->obj);
            $cacheClass->datavconfig_cache("datav.cache.php"); // 大屏自定义数据配置缓存

            $this->admin_json(0, '大屏基数配置设置成功！');
        }else{

            $this->render_json(1, '参数错误，请重试！');
        }
    }
}

?>