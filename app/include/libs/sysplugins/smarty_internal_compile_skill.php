<?php

class Smarty_Internal_Compile_Skill extends Smarty_Internal_CompileBase{
    public $required_attributes = array('item');
    public $optional_attributes = array('title', 'key','ispage', 'limit', 'bm');
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
		$where = "\'1\'";

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
			$limit = PageNav($paramer,$_GET,"skill",$where,$Purl,\'\',\'0\',$_smarty_tpl);
		}
		//排序字段默认为更新时间
		if($paramer[\'order\']){
			$order = " ORDER BY `".str_replace("\'","",$paramer[order])."`";
		}else{
			$order = " ORDER BY id";
		}
		//排序规则 默认为倒序
		if($paramer[\'id\']){
			$sort = $paramer[\'id\'];
		}else{
			$sort = " DESC";
		}
		$where.=$order.$sort.$limit;
		'.$name.'=$db->select_all("skill",$where);
		if(is_array('.$name.')){
			foreach('.$name.' as $key=>$value){
				'.$name.'[$key][\'url\'] = Url("skill",array("c"=>"show","id"=>$value[id]));
				'.$name.'[$key][\'welfare_n\'] = explode(",", $value[welfare]);
				'.$name.'[$key][\'cover_n\'] = checkpic($value[cover]);
				'.$name.'[$key][\'wap_cover_n\'] = checkpic($value[wap_cover]);
				
				if ($paramer[\'bm\']) {
				    '.$name.'[$key][\'bm_num\'] = $db->select_num("skill_bm", "skill_id = $value[id]");
				}
			}
		}';
        //自定义标签 END
        // global $DiyTagOutputStr;
        // $DiyTagOutputStr[]=$OutputStr;
        return SmartyOutputStr($this,$compiler,$_attr,'skill',$name,$OutputStr,$name);
    }
}
class Smarty_Internal_Compile_Skillelse extends Smarty_Internal_CompileBase{
    public function compile($args, $compiler, $parameter){
        $_attr = $this->getAttributes($compiler, $args);

        list($openTag, $nocache, $item, $key) = $this->closeTag($compiler, array('skill'));
        $this->openTag($compiler, 'skillelse', array('skillelse', $nocache, $item, $key));

        return "<?php }\nif (!\$_smarty_tpl->tpl_vars[$item]->_loop) {\n?>";
    }
}
class Smarty_Internal_Compile_Skillclose extends Smarty_Internal_CompileBase{
    public function compile($args, $compiler, $parameter){
        $_attr = $this->getAttributes($compiler, $args);
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }

        list($openTag, $compiler->nocache, $item, $key) = $this->closeTag($compiler, array('skill', 'skillelse'));

        return "<?php } ?>";
    }
}
