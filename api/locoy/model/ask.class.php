<?php
/*
* $Author ：okmad
*
* 官网: https://www.seomoban.cn
*
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class ask_controller extends common{
	
	function addquestion_action(){
		include(APP_PATH."data/api/locoy/locoy_config.php");
		if($locoyinfo['locoy_online']!=1){
			echo "4-接口未开启";die;
		}
		if($locoyinfo['locoy_key']!=trim($_GET['key'])){
			echo "5-密钥错误";die;
		}
		if(!$_POST['title']){
			echo "2-标题不能为空";die;
		}
		$row=$this->obj->DB_select_once("question","`title`='".trim($_POST['title'])."'");
		if(is_array($row)){
			echo "3-问题已存在";die;
		}
		$cid = (int)$_POST['cid'];
		if(!$cid){
			$classRow = $this->obj->DB_select_once("q_class","1 order by id asc","id");
			if($classRow){
				$cid = $classRow['id'];
			}
		}
		$uid = (int)$_POST['uid'];
		$nickname = $_POST['nickname'];
		$auto_user = (int)$_POST['auto_user'];
		if(!$uid){
			if($auto_user == 1){
				$userInfo = $this->create_random_user($locoyinfo, $nickname);
				$uid = $userInfo['uid'];
				$nickname = $userInfo['nickname'];
			}else{
				$memberRow = $this->obj->DB_select_once("member","`usertype`='1' order by uid asc","uid,username");
				if($memberRow){
					$uid = $memberRow['uid'];
					if(!$nickname){
						$nickname = $memberRow['username'];
					}
				}
			}
		}
		if(!$nickname){
			$userRow = $this->obj->DB_select_once("member","`uid`='".$uid."'","username");
			if($userRow){
				$nickname = $userRow['username'];
			}
		}
		$value = "";
		$value .= "`title`='".trim($_POST['title'])."',";
		$value .= "`cid`='".$cid."',";
		$value .= "`uid`='".$uid."',";
		$value .= "`nickname`='".$nickname."',";
		$content = $_POST['content'];
		$value .= "`content`='".$content."',";
		if($_POST['add_time']){
			$value .= "`add_time`='".strtotime($_POST['add_time'])."',";
		}else{
			$value .= "`add_time`='".time()."',";
		}
		if($_POST['visit']){
			$value .= "`visit`='".trim($_POST['visit'])."',";
		}else{
			$row=explode('-',$locoyinfo['locoy_rand']);
			if(is_array($row)){
				$rand=rand(trim($row[0]),trim($row[1]));
			}else{
				$rand=!trim($row)?0:$row;
			}
			$value .= "`visit`='".$rand."'";
		}
		if($_POST['is_recom']){
			$value .= ",`is_recom`='".trim($_POST['is_recom'])."'";
		}
		if($_POST['state']){
			$value .= ",`state`='".trim($_POST['state'])."'";
		}else{
			$value .= ",`state`='1'";
		}
		$value .= ",`ip`='".fun_ip_get()."'";
		$question_id = $this->obj->DB_insert_once("question",$value);
		if($question_id){
			echo "1-发布成功";die;
		}else{
			echo "0-发布失败";die;	
		}
	}

	function addanswer_action(){
		include(APP_PATH."data/api/locoy/locoy_config.php");
		if($locoyinfo['locoy_online']!=1){
			echo "4-接口未开启";die;
		}
		if($locoyinfo['locoy_key']!=trim($_GET['key'])){
			echo "5-密钥错误";die;
		}
		if(!$_POST['qid'] || !$_POST['content']){
			echo "2-参数缺失";die;
		}
		$qid = (int)$_POST['qid'];
		$questionRow = $this->obj->DB_select_once("question","`id`='".$qid."'");
		if(!is_array($questionRow)){
			echo "6-问题不存在";die;
		}
		$uid = (int)$_POST['uid'];
		$nickname = $_POST['nickname'];
		$usertype = (int)$_POST['usertype'];
		$auto_user = (int)$_POST['auto_user'];
		if(!$usertype){
			$usertype = 1;
		}
		if(!$uid){
			if($auto_user == 1){
				$userInfo = $this->create_random_user($locoyinfo, $nickname);
				$uid = $userInfo['uid'];
				$nickname = $userInfo['nickname'];
			}else{
				$memberRow = $this->obj->DB_select_once("member","`usertype`='1' order by uid asc","uid,username");
				if($memberRow){
					$uid = $memberRow['uid'];
					if(!$nickname){
						$nickname = $memberRow['username'];
					}
				}
			}
		}
		if(!$nickname){
			$userRow = $this->obj->DB_select_once("member","`uid`='".$uid."'","username");
			if($userRow){
				$nickname = $userRow['username'];
			}
		}
		$value = "";
		$value .= "`qid`='".$qid."',";
		$value .= "`cid`='".$questionRow['cid']."',";
		$value .= "`uid`='".$uid."',";
		$value .= "`nickname`='".$nickname."',";
		$value .= "`usertype`='".$usertype."',";
		$value .= "`content`='".trim($_POST['content'])."',";
		if($_POST['add_time']){
			$value .= "`add_time`='".strtotime($_POST['add_time'])."',";
		}else{
			$value .= "`add_time`='".time()."',";
		}
		if($_POST['support']){
			$value .= "`support`='".trim($_POST['support'])."',";
		}
		if($_POST['status']){
			$value .= "`status`='".trim($_POST['status'])."',";
		}else{
			$value .= "`status`='1'";
		}
		$answer_id = $this->obj->DB_insert_once("answer",$value);
		if($answer_id){
			$this->obj->DB_update_all("question","`answer_num`=`answer_num`+1,`lastupdate`='".time()."'","`id`='".$qid."'");
			echo "1-发布成功";die;
		}else{
			echo "0-发布失败";die;	
		}
	}

	function addask_action(){
		include(APP_PATH."data/api/locoy/locoy_config.php");
		if($locoyinfo['locoy_online']!=1){
			echo "4-接口未开启";die;
		}
		if($locoyinfo['locoy_key']!=trim($_GET['key'])){
			echo "5-密钥错误";die;
		}
		if(!$_POST['title']){
			echo "2-标题不能为空";die;
		}
		$row=$this->obj->DB_select_once("question","`title`='".trim($_POST['title'])."'");
		if(is_array($row)){
			echo "3-问题已存在";die;
		}
		$cid = (int)$_POST['cid'];
		if(!$cid){
			$classRow = $this->obj->DB_select_once("q_class","1 order by id asc","id");
			if($classRow){
				$cid = $classRow['id'];
			}
		}
		$auto_user = (int)$_POST['auto_user'];
		if($auto_user == 1){
			$question_user = $this->create_random_user($locoyinfo, $_POST['question_nickname']);
			if(!$question_user){
				echo "7-创建提问用户失败";die;
			}
		}else{
			$question_uid = (int)$_POST['question_uid'];
			if($question_uid){
				$userRow = $this->obj->DB_select_once("member","`uid`='".$question_uid."'","uid,username");
				$question_user = array('uid'=>$userRow['uid'], 'nickname'=>$_POST['question_nickname']?$_POST['question_nickname']:$userRow['username']);
			}else{
				$memberRow = $this->obj->DB_select_once("member","`usertype`='1' order by uid asc","uid,username");
				$question_user = array('uid'=>$memberRow['uid'], 'nickname'=>$_POST['question_nickname']?$_POST['question_nickname']:$memberRow['username']);
			}
		}
		$question_value = array();
		$question_value[] = "`title`='".trim($_POST['title'])."'";
		$question_value[] = "`cid`='".$cid."'";
		$question_value[] = "`uid`='".$question_user['uid']."'";
		$question_value[] = "`nickname`='".$question_user['nickname']."'";
		$question_value[] = "`content`='".$_POST['question_content']."'";
		if($_POST['add_time']){
			$add_time = strtotime($_POST['add_time']);
		}else{
			$add_time = time();
		}
		$question_value[] = "`add_time`='".$add_time."'";
		if($_POST['visit']){
			$question_value[] = "`visit`='".trim($_POST['visit'])."'";
		}
		if($_POST['is_recom']){
			$question_value[] = "`is_recom`='".trim($_POST['is_recom'])."'";
		}
		$question_value[] = "`state`='1'";
		$question_value[] = "`ip`='".fun_ip_get()."'";
		$question_id = $this->obj->DB_insert_once("question",implode(",",$question_value));
		
		if(!$question_id){
			echo "0-问题发布失败";die;
		}
		
		$answer_content = $_POST['answer_content'];
		if(!$answer_content){
			echo "1-发布成功";die;
		}
		
		if($auto_user == 1){
			$answer_user = $this->create_random_user($locoyinfo, $_POST['answer_nickname']);
			if(!$answer_user){
				echo "1-发布成功(回答用户创建失败)";die;
			}
		}else{
			$answer_uid = (int)$_POST['answer_uid'];
			if($answer_uid){
				$userRow = $this->obj->DB_select_once("member","`uid`='".$answer_uid."'","uid,username");
				$answer_user = array('uid'=>$userRow['uid'], 'nickname'=>$_POST['answer_nickname']?$_POST['answer_nickname']:$userRow['username']);
			}else{
				$memberRow = $this->obj->DB_select_once("member","`usertype`='1' order by uid desc","uid,username");
				$answer_user = array('uid'=>$memberRow['uid'], 'nickname'=>$_POST['answer_nickname']?$_POST['answer_nickname']:$memberRow['username']);
			}
		}
		$answer_value = array();
		$answer_value[] = "`qid`='".$question_id."'";
		$answer_value[] = "`cid`='".$cid."'";
		$answer_value[] = "`uid`='".$answer_user['uid']."'";
		$answer_value[] = "`nickname`='".$answer_user['nickname']."'";
		$answer_value[] = "`usertype`='1'";
		$answer_value[] = "`content`='".trim($answer_content)."'";
		$answer_value[] = "`add_time`='".($add_time + rand(60, 3600))."'";
		$answer_value[] = "`status`='1'";
		$answer_id = $this->obj->DB_insert_once("answer",implode(",",$answer_value));
		
		if($answer_id){
			$this->obj->DB_update_all("question","`answer_num`=`answer_num`+1,`lastupdate`='".time()."'","`id`='".$question_id."'");
			echo "1-发布成功";die;
		}else{
			echo "1-发布成功(回答发布失败)";die;
		}
	}

	function getquestionlist_action(){
		include(APP_PATH."data/api/locoy/locoy_config.php");
		if($locoyinfo['locoy_online']!=1){
			echo json_encode(array('code'=>4,'msg'=>'接口未开启'));die;
		}
		if($locoyinfo['locoy_key']!=trim($_GET['key'])){
			echo json_encode(array('code'=>5,'msg'=>'密钥错误'));die;
		}
		$where = "1";
		if($_GET['cid']){
			$where .= " and `cid`='".(int)$_GET['cid']."'";
		}
		if($_GET['state']){
			$where .= " and `state`='".(int)$_GET['state']."'";
		}
		$limit = (int)$_GET['limit'];
		if(!$limit){
			$limit = 20;
		}
		$list = $this->obj->DB_select_all("question",$where." order by id desc limit ".$limit);
		if(is_array($list)){
			foreach($list as $k=>$v){
				$list[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
			}
			echo json_encode(array('code'=>1,'data'=>$list));die;
		}else{
			echo json_encode(array('code'=>0,'msg'=>'暂无数据'));die;
		}
	}

	function getclasslist_action(){
		include(APP_PATH."data/api/locoy/locoy_config.php");
		if($locoyinfo['locoy_online']!=1){
			echo json_encode(array('code'=>4,'msg'=>'接口未开启'));die;
		}
		if($locoyinfo['locoy_key']!=trim($_GET['key'])){
			echo json_encode(array('code'=>5,'msg'=>'密钥错误'));die;
		}
		$list = $this->obj->DB_select_all("q_class","1 order by sort asc,id asc");
		if(is_array($list)){
			echo json_encode(array('code'=>1,'data'=>$list));die;
		}else{
			echo json_encode(array('code'=>0,'msg'=>'暂无数据'));die;
		}
	}

	function create_random_user($locoyinfo, $nickname=''){
		$length = $locoyinfo['locoy_length'] ? $locoyinfo['locoy_length'] : 8;
		$prefix = $locoyinfo['locoy_name'] ? $locoyinfo['locoy_name'] : 'user';
		$password = $locoyinfo['locoy_pwd'] ? $locoyinfo['locoy_pwd'] : '123123';
		
		$salt = substr(uniqid(rand()),-6);
		$pass = md5(md5($password).$salt);
		$ip = fun_ip_get();
		$time = time();
		
		$username = $this->generate_username($prefix, $length);
		
		$uid = $this->obj->DB_insert_once("member","`username`='".$username."',`password`='$pass',`usertype`='1',`status`='1',`salt`='$salt',`reg_date`='$time',`reg_ip`='$ip',`source`='6'");
		
		if(!$uid){
			return false;
		}
		
		$this->obj->DB_insert_once("member_statis","`uid`='$uid'");
		$resume_value = "`uid`='$uid',`name`='".$username."',`ctime`='$time',`lastupdate`='$time',`r_status`='1'";
		$this->obj->DB_insert_once("resume",$resume_value);
		
		if(!$nickname){
			$nickname = $username;
		}
		
		return array('uid'=>$uid, 'nickname'=>$nickname);
	}

	function generate_username($prefix, $length){
		$chars = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","0","1","2","3","4","5","6","7","8","9");
		$rand_str = "";
		for($i=0;$i<$length;$i++){
			$rand = rand(0,61);
			$rand_str .= $chars[$rand];
		}
		return $prefix . $rand_str;
	}
}
?>
