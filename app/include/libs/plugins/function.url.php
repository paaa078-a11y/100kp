<?php
function smarty_function_url($paramer,$template){
	global $config,$seo;
	if(!$paramer['index'] && $paramer['m']=='member'){
		$index='member';
		unset($paramer['m']);
	}else{
		$index=$paramer['index'];
	}
    
     unset($paramer['index']);
     $url  =  get_url($paramer,$config,$seo,$paramer['m'],$index,$template);
     if(strpos($_SERVER['REQUEST_URI'], '/m/') !== false){
        $url = str_replace('/wap', '/m', $url);
  
            $parse = parse_url($url);
            if(isset($parse['host']) && $parse['host'] != $_SERVER['HTTP_HOST']){
                $url = str_replace($parse['host'], $_SERVER['HTTP_HOST'], $url);
            }
        
    }
	return $url;
}
?>