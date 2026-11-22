<?php
class Smarty_Internal_Compile_Video extends Smarty_Internal_CompileBase{
    public $required_attributes = array('item');
    public $optional_attributes = array('name', 'key', 'len', 'limit', 'ispage','state','order','sort','rec','display','keyword');
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

        //自定义标签START
        $OutputStr=''.$name.'=array();$time=time();$paramer='.ArrayToString($_attr,true).';
		global $db,$db_config,$config;
		$ParamerArr = GetSmarty($paramer,$_GET,$_smarty_tpl);
		$paramer = $ParamerArr[arr];
		$Purl =  $ParamerArr[purl];
        global $ModuleName;

        if(!$Purl["m"]){
            $Purl["m"]=$ModuleName;
        }

		$where = "`display` = 1";
       
         
        if($paramer[rec] && $paramer[rec] == 1){    
            $where.=" and `rec`=\'".$paramer[rec]."\'";
        } 
 
		//查询条数
		if($paramer[limit]){
		
			$limit=" LIMIT ".$paramer[limit];
		}else{
		
			$limit=" LIMIT 20";
		}
		if($paramer[ispage]){
		
			$limit = PageNav($paramer,$_GET,"video",$where,$Purl,"",6,$_smarty_tpl);
		}
        
        $select = "*";
        
        if($paramer[rec] && $paremer[rec] == 2){
        
            $where .= " ORDER BY `rec` DESC, `sort` DESC";
        }else{
        
            $where .= " ORDER BY `sort` DESC";
		}
		'.$name.'=$db->select_all("video", $where.$limit, $select);
		
		if(is_array('.$name.')){
            $time = time();
            $uidArr = array();
            $vidArr = array();
            $res = array();
            foreach('.$name.' as $vk=>$vv){
                
                $vidArr[]   =   $vv[vid];
                $uidArr[]   =   $vv[uid];
            }
 
            foreach('.$name.' as $key=>$val){
            
				'.$name.'[$key]["pubdate_n"]=date(\'Y-m-d\',$val[pubdate]);
                '.$name.'[$key]["update_n"]=date(\'Y-m-d\', $val[lastupdate]);
			}
			if(!empty($vidArr)){
				$videoList=$db->select_all("video_info", "`id` IN (".pylode(",",$vidArr).")","`id`,`img`");
				foreach('.$name.' as $key=>$val) {
					foreach($videoList as $sk=>$sv) {
						if($val[vid]==$sv[id]){
						    if(!empty($val[pic])){
						        '.$name.'[$key][img]	=	checkpic($val[pic]);
						    }else{
							    '.$name.'[$key][img]	=	$sv[img];
							}
						}
					}
				}
			}
			if(!empty($uidArr)){
				$comList=$db->select_all("company", "`uid` IN (".pylode(",",$uidArr).")","`uid`,`name`");
				$jobList=$db->select_all("company_job", "`uid` IN (".pylode(",",$uidArr).") AND `state` = 1 AND `status` = 0 AND `r_status` = 1","`id`, `uid`");
				if(!empty($jobList)){
					foreach($jobList as $item) {
						$res[$item[uid]][] = $item;
					}
				}
				foreach('.$name.' as $key=>$val) {
					foreach($comList as $ck=>$cv) {
						if($val[uid]==$cv[uid]){
							'.$name.'[$key][com_name]	=	$cv[name];
						}
					}
					foreach($res as $k=>$v) {
						if($val[uid]==$k){
							'.$name.'[$key][job_num]	=	count($v);
						}
					}
					'.$name.'[$key][job_num]	=	'.$name.'[$key][job_num]>0?'.$name.'[$key][job_num]:0;
				}			
			}
		}';
        //自定义标签 END
        return SmartyOutputStr($this,$compiler,$_attr,'video',$name,$OutputStr,$name);
    }
}
class Smarty_Internal_Compile_Videoelse extends Smarty_Internal_CompileBase{
    public function compile($args, $compiler, $parameter){
        $_attr = $this->getAttributes($compiler, $args);

        list($openTag, $nocache, $item, $key) = $this->closeTag($compiler, array('video'));
        $this->openTag($compiler, 'videoelse', array('videoelse', $nocache, $item, $key));

        return "<?php }\nif (!\$_smarty_tpl->tpl_vars[$item]->_loop) {\n?>";
    }
}
class Smarty_Internal_Compile_Videoclose extends Smarty_Internal_CompileBase{
    public function compile($args, $compiler, $parameter){
        $_attr = $this->getAttributes($compiler, $args);
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }

        list($openTag, $compiler->nocache, $item, $key) = $this->closeTag($compiler, array('video', 'videoelse'));

        return "<?php } ?>";
    }
}
