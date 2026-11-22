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
class hotjob_controller extends adminCommon{
    /**
     * @desc 后台  企业  名企设置/修改弹出
     */
    function hotjobinfo_action(){

        $ComM      =   $this -> MODEL('company');

        $id        =   intval($_GET['id']);
        $uid       =   intval($_GET['uid']);

        if(!empty($id)){
            $hotjob   =   $ComM -> getHotJob(array('uid'=>$id));
            $hotjob['time_start_n'] = date('Y-m-d', $hotjob['time_start']);
            $hotjob['time_end_n'] = date('Y-m-d', $hotjob['time_end']);
        }else if(!empty($uid)){

            $statisM  =   $this -> MODEL('statis');
            $statis   =   $statisM -> getInfo($uid, array('usertype'=>'2','field'=>'`rating` as `rating_id`,`rating_name` as `rating`'));
            $company  =   $ComM -> getInfo($uid,array('logo'=>1,'utype'=>'admin','field'=>'`name` as `username`, `uid`,`logo`'));

            $company['hot_pic']   =   $company['logo'];
            unset($company['logo']);

            $hotjob   =   array_merge($statis, $company);

            $hotjob['time_start']=time();
        }
        $returnData['hotjob'] = $hotjob;

        $this->render_json(0, 'ok', $returnData);
    }

    /**
     * @desc 取消（删除）名企招聘
     */
    function del_action(){
        if (is_array($_GET['del'])){
            $uid           =   $_GET['del'];
        }elseif ($_GET['del']){
            $uid           =   intval($_GET['del']);
        }

        $ComM      =   $this -> MODEL('company');

        $return    =   $ComM -> delHotJob($uid);

        if ($return) {
            $this->render_json(0, '名企(ID:'.pylode(',', $uid).')删除成功！');
        } else {
            $this->render_json(1, '删除失败！');
        }
    }

    /**
     * @desc 设置/修改名企
     */
    function save_action(){
        if($_POST['uimage']){

            $upArr    =  array(
                'base'  =>  $_POST['uimage'],
                'dir'   =>  'hotpic'
            );

            $uploadM  =  $this->MODEL('upload');

            $picr      =  $uploadM->newUpload($upArr);

            if (!empty($picr['msg'])){

                $this->render_json(8, $picr['msg']);

            }elseif (!empty($picr['picurl'])){

                $pic 	=  	$picr['picurl'];
            }


        }

        $ComM     =   $this -> MODEL('company');

        $hotJob   =   $ComM -> getHotJob(array('uid' => $_POST['uid']));

        if(!$hotJob){

            $com      =   $ComM -> getInfo(intval($_POST['uid']),array('field'=>'`logo`,`did`','logo'=>1));

            $hotJob['hot_pic']	=	$com['logo_n'];
            $hotJob['did']		=	$com['did'];
        }

        $value    =   array(
            'uid'             =>  intval($_POST['uid']),
            'username'        =>  trim($_POST['username']),
            'rating'          =>  trim($_POST['rating']),
            'hot_pic'         =>  trim($pic)?trim($pic):$hotJob['hot_pic'],
            'service_price'   =>  intval($_POST['service_price']),
            'time_start'      =>  strtotime($_POST['time_start']),
            'time_end'        =>  strtotime($_POST['time_end']),
            'sort'            =>  intval($_POST['sort']),
            'beizhu'          =>  trim($_POST['beizhu']),
            'rating_id'       =>  intval($_POST['rating_id'])
        );

        if($_POST['hotad']){

            if (empty($hotJob['hot_pic']) && empty($_POST['uimage'])){
                $this->render_json(8, '请上传企业展示LOGO');
            }

            $value['did'] =	  intval($hotJob['did']);

            $arr          =   $ComM -> addHotJob($value);

        }elseif($_POST['hotup']){

            $arr  =   $ComM -> upHotJob($_POST['uid'], $value);
        }

        $this->render_json($arr['errcode'], $arr['msg']);
    }
}
?>