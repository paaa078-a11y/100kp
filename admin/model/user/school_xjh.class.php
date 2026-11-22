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
class school_xjh_controller extends adminCommon{
	//设置高级搜索功能

	function index_action(){
		$schoolM   =   $this->MODEL('school');
		if($_POST['keyword']){
			$keyword  			=   trim($_POST['keyword']);
			$where['address'] 	= 	array('like',$keyword);		
		}
		if($_POST['status']){
			$status   			=   intval($_POST['status']);
            if($status==4){
                $where['r_status']			=	  array('>', 1);
            }else{
                $where['status']	=  	$status == 3 ? 0 : $status;
                $where['r_status']			=	  array('=', 1);
            }
		}
		if($_POST['state']){
			$state  =   intval($_POST['state']);
			if($state==1){
				$where['stime']		= 	array(">",time());
			}else if($state==2){
				 $where['stime']	= 	array("<",time());
				 $where['etime']	= 	array(">",time());
			}else if($state==3){
				$where['etime']		= 	array("<",time());
			}
		}
		if($_POST['adtime']){
			if($_POST['adtime']=='1'){
			    $where['ctime']	    =  	array('>=',strtotime('today'));
			}else{
				$where['ctime']		=  	array('>=',strtotime('-'.intval($_POST['adtime']).' day'));
			}
		}
        $pageM = $this->MODEL('page');

        $pages = $pageM->adminPageList('school_xjh', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];
        if($pages['total']  > 0){
            //limit order 只有在列表查询时才需要
            if($_POST['order']){
                if($_POST['t']=='atnnum'){
                    $atnM		=   $this->MODEL('atn');
                    $atnwhere['xjhid']		=	array('<>',0);
                    $atnwhere['groupby']	=	'xjhid';
                    $atnwhere['orderby']	=	array('count(xjhid),'.$_POST['order']);
                    $alist		=	$atnM ->getatnList($atnwhere,array('field'=>'`xjhid`'));

                    foreach($alist as $val){
                        $auid[]	=	$val['xjhid'];
                    }
                    $clist		=	$atnM ->getatnList(1,array('field'=>'`id`'));
                    if($clist && is_array($clist)){
                        foreach($clist as $val){
                            if(in_array($val['id'],$auid)==false){
                                $lxjh[]		=	$val['id'];
                            }
                        }
                    }
                    if($_POST['order']=="desc"){
                        $xid	=	pylode(',',$auid).','.pylode(',',$lxjh);
                    }elseif($_POST['order']=="asc"){
                        $xid	=	pylode(',',$lxjh).','.pylode(',',$auid);
                    }
                    $where['id']			=	array('in',$xid);
                }else{
                    $where['orderby']		=	$_POST['t'].','.$_POST['order'];
                }
            }else{
                $where['orderby']		=	array('r_status,asc','ctime,desc');
            }
        }
        $where['limit'] 	=   $pages['limit'];
        $list		=   $schoolM -> getSchoolXjhList($where);
        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
	}

    public function  getSearchData_action(){
        $search_list[]	=	array("param"=>"status","name"=>'审核状态',"value"=>array("3"=>"未审核","1"=>"已审核","2"=>"未通过","4"=>"已锁定"));
        $search_list[]	=	array("param"=>"state","name"=>'举办状态',"value"=>array("1"=>"待举办","2"=>"开讲中","3"=>"已结束"));
        $search_list[]	=	array("param"=>"adtime","name"=>'发布时间',"value"=>array("1"=>"今天","3"=>"最近三天","7"=>"最近七天","15"=>"最近半月","30"=>"最近一个月"));
        $this->render_json(0, 'ok', compact('search_list'));
    }
	function listxjh_action(){
		$atnM	=	$this -> MODEL('atn');
        $where['sc_usertype']   =   2;
		if ($_POST['id']){
            $where['xjhid']         =   intval($_POST['id']);

        }
        $pageM = $this->MODEL('page');

        $pages = $pageM->adminPageList('atn', $where, $_POST['page'], array('limit' => $_POST['limit'], 'maxPage' => true));
        extract($pages);
        $limit = $pages['limit'][1];
        if($pages['total']  > 0){
            $where['limit']	= 	$pages['limit'];
            $list		=	$atnM -> getatnList($where,array('utype'=>'xjh'));
        }
        $this->render_json(0, 'ok', compact('list', 'total', 'page_sizes', 'limit', 'page'));
	}


	function add_action(){
		$schoolM		=   $this->MODEL('school');
		$companyM		=	$this->MODEL('company');
		if($_POST['id']){
			$id			=	intval($_POST['id']);
			$return		=	$schoolM -> getSchoolXjhInfo($id, array('cache'=>'1'));
			$info = $return['info'];
			$company	=		$companyM->getInfo($return['info']['uid'],array('field'=>'`uid`,r_status'));
			$school		=	$schoolM -> getSchoolAcademyList('',array('field'=>'`id`,`schoolname`'));
			$academy = $school['list'];

            $this->render_json(0, 'ok', compact('info', 'company','academy'));
		}
	}
	//详情及修改
	function save_action(){
		$schoolM	= 	$this ->  MODEL('school');
		if($_POST['r_status']==1){
			$status  		=  1;
		}else{
			$status  		=	0;	
		}
		$post		=	array(
			'provinceid' 	=>  $_POST['provinceid'],
			'cityid'    	=>  $_POST['cityid'],
			'schoolid'   	=>  $_POST['schoolid'],
			'address'  		=>  $_POST['address'],
			'uid'           =>	$_POST['uid'],
			'status'  		=>  $status,
			'r_status'  	=>  $_POST['r_status'],
		);
		$data		=	array(
			'post'		=>	$post,
			'id'		=>	$_POST['id'],
			'stime'     =>  $_POST['stime'],
			'etime'     =>  $_POST['etime'],
		);
		$return		=	$schoolM -> addSchoolXjh($data);

        $this->render_json($return['errcode']==9?0:1, $return['msg']);
	}
    //删除用户关注宣讲会
	function dellsit_action(){
		$atnM	=	$this -> Model('atn');
		$delID	=	$_POST['del'];
		$return	=	$atnM -> delAtnAll($delID,array('type'=>'admin'));
        $this->admin_json($return['errcode']==9?0:1, $return['msg']);
	}
	function del_action(){
    
		$schoolM	=	$this -> Model('school');
		$delID		=	is_array($_POST['del']) ? $_POST['del'] : $_POST['id'];
		$return		=	$schoolM -> delSchoolxjh($delID,array('utype'=>'admin'));

        $this->render_json($return['errcode']==9?0:1, $return['msg']);
	}

	function status_action(){
    
		$schoolM    		=   $this->MODEL('school');
		$data['statusbody']	=   trim($_POST['lock_info']);
		$data['lock_info']	=   trim($_POST['lock_info']);
		$data['status']     =   $_POST['status'];
		$pid				=   intval($_POST['pid']);
		$id                 =   $schoolM     ->  upSchoolxjh($pid,$data);
		$info               =   $schoolM     ->  getSchoolXjhInfo($pid,array('field'=>'uid'));
    
		if(!empty($info)){
			/* 消息前缀 */		
			$tagName  			=	'校招宣讲会';
			/* 处理审核信息 */
			if ($_POST['status'] == 2){
				$pid            	=  	intval($_POST['pid']);
				$id                 =   $schoolM -> upSchoolxjh($pid,$data);
				$list               =   $schoolM -> getSchoolXjhInfo($id,array('field'=>'uid'));
				$info				=	$list['info'];

				$statusInfo  =  $tagName.':审核未通过';
				if($_POST['lock_info']){
					$statusInfo  .=  ' , 原因：'.$_POST['lock_info'];
				}
				$msg  =  $statusInfo;
			}elseif($_POST['status'] == 1){
				$msg  =  $tagName.':已审核通过';
			}
			//发送系统通知
			$sysmsgM	=	$this->MODEL('sysmsg');
			$sysmsgM -> addInfo(array('uid'=>$info['uid'],'usertype'=>4, 'content'=>$msg));
		}
        $this->render_json($id?0:1, $id?"校招宣讲会审核成功":"审核失败");
	}

	function xjhNum_action(){
    
		$MsgNum = $this->MODEL('msgNum');
		$data =  $MsgNum->xjhNum();
        $this->render_json(0, 'ok',json_decode($data));
	}
}
?>