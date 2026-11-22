<?php
/*
 * widuu 2025/11 相关城市
 * ----------------------------------------------------------------------------
 *
 *
 * ============================================================================
*/
function smarty_function_relative($paramer,&$smarty){
	global $db,$db_config, $config;
    $where = [];
    if(isset($config['province'])){
        if($paramer['type'] == 'province'){
            $where['cityid'] = 0;
            $where['three_cityid'] = 0;
        }else if($paramer['type'] == 'city'){
            $where['province'] = $config['province'];
            $where['cityid <>'] = 0;
            $where['three_cityid'] = 0;
        }else if($paramer['type'] == 'area'){
            if($config['cityid'] == 0){
                 $where['province'] = $config['province'];
                 $where['three_cityid <>'] = '0';
            }else{
                $where['province'] = $config['province'];
                $where['cityid']   = $config['cityid'];
                $where['three_cityid <>'] = '0';
            }
        }
    }else{
        if($paramer['type'] == 'province'){
            $where['cityid'] = 0;
            $where['three_cityid'] = 0;
        }else if($paramer['type'] == 'city'){
           // $where['province'] = $config['province'];
            $where['cityid <>'] = 0;
            $where['three_cityid'] = 0;
        }else if($paramer['type'] == 'area'){
            $where['three_cityid <>'] = '0';
        }
    }
    $whereNew = '';
    foreach ($where as $k => $w){
        $whereNew .= ' ' . (strpos($k, '<>') === false ? $k .' =' : $k) .' '. $w . ' AND';
    }
    $whereNew = $whereNew . ' fz_type = 1 order by id ASC limit 10 ';
    // var_dump($whereNew);
    $list = $db->select_all('domain', $whereNew);
    $smarty->assign("$paramer[d_list]", isset($list[0]) ? $list : []);
	
}
?>