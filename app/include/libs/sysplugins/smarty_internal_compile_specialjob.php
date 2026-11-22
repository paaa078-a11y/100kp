<?php
class Smarty_Internal_Compile_Specialjob extends Smarty_Internal_CompileBase{
    public $required_attributes = array('item');
    public $optional_attributes = array('title', 'key', 'ctime', 'ispage', 'limit','delid','islt', 'istuiguang');
    public $shorttag_order = array('from', 'item', 'key', 'name');
    public function compile($args, $compiler, $parameter){
        $_attr = $this->getAttributes($compiler, $args);

        $from = $_attr['from'];
        $item = $_attr['item'];
        $name = $_attr['item'];
        $name=str_replace('\'','',$name);
        $name=$name?$name:'list';$name='$'.$name;
        if (!strncmp("\$_smarty_tpl->tpl_vars[$item]", $from, strlen($item) + 24)) {
            $compiler->trigger_template_error("item variable {$item} may not be the same variable as at 'from'", $compiler->lex->taglineno);
        }

        //自定义标签 START
        $OutputStr='global $db,$db_config,$config;$paramer='.ArrayToString($_attr,true).';'.$name.'=array();
		//处理传入参数，并且构造分页参数
		$ParamerArr = GetSmarty($paramer,$_GET);
		$paramer = $ParamerArr[arr];
		$Purl =  $ParamerArr[purl];
        global $ModuleName;
        if(!$Purl["m"]){
            $Purl["m"]=$ModuleName;
        }
		$where = "display=\'1\' ";
		//首页 搜索页 展示开启推广的
		if($paramer[\'istuiguang\']==1){
		    $where .= " AND tuiguang=\'1\' ";
		}

		if($paramer[\'ctime\']>0){
			$time=time()-$paramer[\'ctime\']*86400;
			$where.=" and `ctime`>".$time;
		}
		if($paramer[\'delid\']){
			$where.=" AND `id`<>\'".$paramer[\'delid\']."\'";
		}

		//查询条数
		if($paramer[limit]){
			$limit=" LIMIT ".$paramer[limit];
		}else{
			$limit=" LIMIT 20";
		}
		//自定义查询条件，默认取代上面任何参数直接使用该语句
		if($paramer[where]){
			$where = $paramer[where];
		}
		if($paramer[ispage]){
			$limit = PageNav($paramer,$_GET,"special",$where,$Purl,\'\',\'0\',$_smarty_tpl);
		}
		//排序字段默认为更新时间
		if($paramer[\'order\']){
			$order = " ORDER BY `".str_replace("\'","",$paramer[order])."`";
		}else{
			$order = " ORDER BY `sort` DESC,id";
		}
		//排序规则 默认为倒序
		if($paramer[\'sort\']){
			$sort = $paramer[\'sort\'];
		}else{
			$sort = " DESC";
		}
		$where.=$order.$sort.$limit;
		'.$name.'=$db->select_all("special_job",$where);
		if(is_array('.$name.')){
		    $sjidTp1 = $sjidTp2 = array();
			foreach('.$name.' as $val){
			    if ($val[\'type\'] == 2) {
			        $sjidTp2[] = $val[\'id\'];
			    } else {
			        $sjidTp1[] = $val[\'id\'];
			    }
			}
			$jobsearch=$db->select_all("special_job_search","`sid` in(".@implode(\',\',$sjidTp2).")","`sid`,`jobids`,`manual_jobids`");
			$joblist=$db->select_all("special_job_list","`sid` in(".@implode(\',\',$sjidTp1).") group by sid","count(`id`) as `job_num`,`sid`");
			$spjobnums = array();
			if($jobsearch&&is_array($jobsearch)){
				foreach($jobsearch as $k=>$v){
				    $autoNum = $v[\'jobids\'] ? count(explode(\',\', $v[\'jobids\'])) : 0;
				    $manualNum = $v[\'manual_jobids\'] ? count(explode(\',\', $v[\'manual_jobids\'])) : 0;
				    $spjobnums[$v[\'sid\']] = $autoNum + $manualNum;
				}
			}
			if($joblist&&is_array($joblist)){
				foreach($joblist as $k=>$v){
				    $spjobnums[$v[\'sid\']] = $v[\'job_num\'];
				}
			}
			foreach('.$name.' as $key=>$value){
				'.$name.'[$key][\'sjlist_pic_n\'] = checkpic($value[\'sjlist_pic\']);
				'.$name.'[$key][\'wap_sjlist_pic_n\'] = checkpic($value[\'wap_sjlist_pic\']);
				'.$name.'[$key][\'url\'] = Url("special",array("c"=>"specialJob","id"=>$value[id]));
				'.$name.'[$key][\'job_num\'] = $spjobnums[$value[\'id\']];
			}
		}';
        //自定义标签 END
       // global $DiyTagOutputStr;
       // $DiyTagOutputStr[]=$OutputStr;
        return SmartyOutputStr($this,$compiler,$_attr,'specialjob',$name,$OutputStr,$name);
    }
}
class Smarty_Internal_Compile_Specialjobelse extends Smarty_Internal_CompileBase{
    public function compile($args, $compiler, $parameter){
        $_attr = $this->getAttributes($compiler, $args);

        list($openTag, $nocache, $item, $key) = $this->closeTag($compiler, array('specialjob'));
        $this->openTag($compiler, 'specialjobelse', array('specialjobelse', $nocache, $item, $key));

        return "<?php }\nif (!\$_smarty_tpl->tpl_vars[$item]->_loop) {\n?>";
    }
}
class Smarty_Internal_Compile_Specialjobclose extends Smarty_Internal_CompileBase{
    public function compile($args, $compiler, $parameter){
        $_attr = $this->getAttributes($compiler, $args);
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }

        list($openTag, $compiler->nocache, $item, $key) = $this->closeTag($compiler, array('specialjob', 'specialjobelse'));

        return "<?php } ?>";
    }
}
