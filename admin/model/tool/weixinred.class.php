<?php

class weixinred_controller extends adminCommon{
    
    function index_action(){
        
        $data = array(
            'sy_wxredpack_nick'    => $this->config['sy_wxredpack_nick'],
            'sy_wxredpack_ip'    => $this->config['sy_wxredpack_ip'],
            'sy_wxredpack_remark'    => $this->config['sy_wxredpack_remark'],
            'sy_wxredpack_type'    => $this->config['sy_wxredpack_type'] ? $this->config['sy_wxredpack_type'] : '1',

            'sy_wxredpack_actname1'    => $this->config['sy_wxredpack_actname1'],
            'sy_wxredpack_isopen1'    => $this->config['sy_wxredpack_isopen1'],
            'sy_wxredpack_pro1'    => $this->config['sy_wxredpack_pro1'],
            'sy_wxredpack_money1'    => $this->config['sy_wxredpack_money1'],
            'sy_wxredpack_money_max1'    => $this->config['sy_wxredpack_money_max1'],
            'sy_wxredpack_wishing1'    => $this->config['sy_wxredpack_wishing1'],

            'sy_wxredpack_actname2'    => $this->config['sy_wxredpack_actname2'],
            'sy_wxredpack_isopen2'    => $this->config['sy_wxredpack_isopen2'],
            'sy_wxredpack_pro2'    => $this->config['sy_wxredpack_pro2'],
            'sy_wxredpack_money2'    => $this->config['sy_wxredpack_money2'],
            'sy_wxredpack_money_max2'    => $this->config['sy_wxredpack_money_max2'],
            'sy_wxredpack_wishing2'    => $this->config['sy_wxredpack_wishing2'],

            'sy_wxredpack_actname3'    => $this->config['sy_wxredpack_actname3'],
            'sy_wxredpack_isopen3'    => $this->config['sy_wxredpack_isopen3'],
            'sy_wxredpack_pro3'    => $this->config['sy_wxredpack_pro3'],
            'sy_wxredpack_money3'    => $this->config['sy_wxredpack_money3'],
            'sy_wxredpack_money_max3'    => $this->config['sy_wxredpack_money_max3'],
            'sy_wxredpack_wishing3'    => $this->config['sy_wxredpack_wishing3'],

            'sy_wxredpack_actname4'    => $this->config['sy_wxredpack_actname4'],
            'sy_wxredpack_isopen4'    => $this->config['sy_wxredpack_isopen4'],
            'sy_wxredpack_pro4'    => $this->config['sy_wxredpack_pro4'],
            'sy_wxredpack_money4'    => $this->config['sy_wxredpack_money4'],
            'sy_wxredpack_money_max4'    => $this->config['sy_wxredpack_money_max4'],
            'sy_wxredpack_wishing4'    => $this->config['sy_wxredpack_wishing4'],

            'redArr'  =>  array(
                array('name'=>'其他','val'=>'0'),
                array('name'=>'商品促销','val'=>'1'),
                array('name'=>'抽奖','val'=>'2'),
                array('name'=>'虚拟物品兑奖','val'=>'3'),
                array('name'=>'企业内部福利','val'=>'4'),
                array('name'=>'渠道分润','val'=>'5'),
            )
        );
        
        $this->render_json(0,'ok', $data);
    }

    function save_action(){
        
        $_POST =  $this->post_trim($_POST);
        
        $configM  =  $this->MODEL('config');
        
        $configM -> setConfig($_POST);
        
        $this->web_config();
        
        $this->render_json(0,'微信红包配置更新成功！');
    }

    function wxlog_action(){
        
        $wxredpackM =   $this->MODEL('wxredpack');
        $where = array();
        if(trim($_POST['keyword'])){
            
            $_POST['keyword']    =   trim($_POST['keyword']);

            if($_POST['type']=='1'){

                $where['re_nick']   =   array('like',trim($_POST['keyword']));

            }elseif($_POST['type']=='2'){

                $where['username']  =   array('like',trim($_POST['keyword']));

            }

        }


        $pageM          =   $this->MODEL('page');
        $pages          =   $pageM->adminPageList('wxredpack', $where,$_POST['page'],array('limit'=>$_POST['limit']));

        if ($pages['total'] > 0) {

            $where['orderby']   =   'time,desc';
        
            $where['limit']     =   $pages['limit'];
            $list               =   $wxredpackM -> getWxRedPackList($where);
        }

        $data['list']           =   !empty($list['list'])?$list['list']:array();
        $data['total']          =   $pages['total'];
        $data['page_sizes']     =   $pages['page_sizes'];
        $data['page_size']      =   $pages['page_size'];
        $this->render_json(0, '', $data);
    }

}
?>