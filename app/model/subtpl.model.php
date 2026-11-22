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
class subtpl_model extends model{

    function getTpls($whereData=array(),$data=array()){
		$ListNew		=	array();
		$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		$List			=	$this -> select_all('xcx_sub_tpl',$whereData,$data['field']);
		if(!empty( $List )){
			$ListNew['list']	=	$List;
		}
		return	$ListNew;
    }

    function getTpl($whereData=array(),$data=array()){
        $data['field']  =	empty($data['field']) ? '*' : $data['field'];
        $tpl = $this->select_once('xcx_sub_tpl',$whereData,$data['field']);
        return $tpl;
	}

    function getTplNum($where = array()){
        $num = $this->select_num('xcx_sub_tpl', $where);
        return $num ? $num : 0;
    }

    /*
     * 添加订阅模板
     * $setData 要添加的订阅模板信息 array(array('name'=> ***, 'tpl_id' => ***), array('name'=> ***, 'tpl_id' => ***))
     */
    function addTpl($setData=array()){
    	$rt = 1;
    	foreach ($setData as $v) {
    		$exist = $this->getTpl(array('name' => $v['name']));
    		if ($exist) {// 要添加的模板记录已存在
    			$nid = $this->upTpl(array('name' => $v['name']), array('tpl_id' => $v['tpl_id']));
			} else {
                $nid = $this->insertTpl(array('name' => $v['name'], 'tpl_id' => $v['tpl_id']));
			}
			if (!$nid) {// 新增或更新失败时，跳出循环，并返回添加失败
				$rt = 0;
				break;
			}
		}
		return $rt;
    }

    function insertTpl($data=array()){
		if(!empty($data)){
			(!isset($data['stime']) || !$data['stime']) && $data['stime'] = time();
			$nid = $this->insert_into('xcx_sub_tpl',$data);
		}
		return $nid;
    }

    function upTpl($whereData=array(),$data=array()){
		if(!empty($whereData)){
			$nid = $this->update_once('xcx_sub_tpl',$data,$whereData);
		}
		return $nid;
    }

    /**
     * 删除
     * @param string $id  因有批量删除，故传入的$id为字符串型 ;如  1 或 1,2,3
     */
    public function delTpl($id){
        $return['layertype'] = 0;
        if (!empty($id)){
            if(is_array($id)){
                $id = pylode(',', $id);
                $return['layertype'] = 1;
            }else if(strpos($id, ',')){
                $return['layertype']  =  1;
            }
            $nid = $this->delete_all('xcx_sub_tpl',array('id'=>array('in',$id)),'');
            if ($nid){
                $return['msg'] = '订阅模板(ID:'.$id.')删除成功';
                $return['errcode'] = '9';
            }else{
                $return['msg'] = '订阅模板(ID:'.$id.')删除失败';
                $return['errcode']  =  '8';
            }
        }else{
            $return['msg'] = '请选择您要删除的订阅模板';
            $return['errcode']  =  '8';
        }
        return $return;
    }
}
?>
