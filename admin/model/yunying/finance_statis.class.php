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
class finance_statis_controller extends adminCommon{

    //图表类型 bar 柱形图，line折线图，pie饼形图
    private $chartType = 'line';

    //消费渠道
    private $typeMapping = array(	1 => '会员充值（购买会员）',2 => '积分充值',3 => '银行转帐',4 => '金额充值',
        5 => '购买增值包',6 => '课程订购',7 => '购买小程序',8 => '分享红包推广',
        9 => '悬赏红包',10 => '职位置顶',11 => '职位紧急',12 => '职位推荐',
        13 => '自动刷新',14 => '简历置顶',15 => '委托简历',16 => '刷新职位',
        17 => '刷新兼职',18 => '刷新猎头职位',19 => '下载简历',20 => '发布职位',
        21 => '发布兼职',22 => '发布猎头职位',23 => '面试邀请',24 => '兼职推荐',
        25 => '店铺招聘',26 => '购买广告位',27 => '创建子账号',28 => '招聘会报名',
        29 => '发布供求任务',30 => '刷新供求任务',31 => '购买聊天数量', 32 => '网络招聘会报名',
        33 => '购买视频面试'
    );

    //收支总计页面
    public function index_action(){
        $StatisM		=			$this->MODEL('statis');

        //查询报表数据
        $time_begin_7 	= 			isset($_POST['time_begin']) ? $_POST['time_begin'] : date('Y-m-d 00:00:00', strtotime('-7 day'));
        $time_end 		= 			isset($_POST['time_end']) ? $_POST['time_end'] :date('Y-m-d H:i:s');
        $isAllTime 		= 			isset($_POST['isAllTime']) ? $_POST['isAllTime'] : 0;
        $time_begin_14  = strtotime($time_begin_7)-(86400*7);
        //查询30条内的信息，展示到页面第一屏
        if($isAllTime != 1){
            $timeBegin						= 			strtotime($time_begin_7);
        }else{
            $timeBegin 						= 			strtotime(date('Y-m-d 00:00:00', strtotime('-30 year')));
        }
        $timeEnd 							= 			strtotime($time_end);

        list($in, $out, $net_income) 		= 		$StatisM->getStatisTotal($timeBegin, $timeEnd, '');
        // 上周
        list($in_7, $out_7, $net_income_7) 	= 		$StatisM->getStatisTotal($time_begin_14, $timeBegin, '');
        $yields = bcdiv($in-$in_7,$in_7,2)*100;
        $payRate = bcdiv($out-$out_7,$out_7,2)*100;
        $netRate= bcdiv($net_income-$net_income_7,$net_income_7,2)*100;
        $companyorderM=			$this->MODEL('companyorder');
        $comwhere['order_price'] = array('>', 1); // 订单金额需要大于1
        // 总成交数
        $comwhere['order_time'][]         	=   		array('>=', $timeBegin);
        $comwhere['order_time'][]         	=           array('<=', $timeEnd,'AND');
        $orderNumber = $companyorderM->getCompanyOrderNum($comwhere);
        $comwhere_7['order_time'][]         	=   		array('>=', $time_begin_14);
        $comwhere_7['order_time'][]         	=   		array('>=', $timeBegin);
        $orderNumber_7 = $companyorderM->getCompanyOrderNum($comwhere);
        $orderRate = bcdiv($orderNumber-$orderNumber_7,$orderNumber_7,2)*100;

        $data  = array('time' => '近30', 'in' => $in, 'out' => $out, 'net_income' => $net_income,'orderNumber'=>$orderNumber);
        $rate  = array('yields'=>$yields,'payRate'=>$payRate,'netRate'=>$netRate,'orderRate'=>$orderRate);
        $this->render_json(0,'',['data'=>$data,'rate'=>$rate]);
    }

    public function shouzhi_action(){
        //		//如果不是查询全部数据，组织查询的开始、结束时间
        if(is_array($_POST['time']) && $_POST['time']){
            $time = $_POST['time'];
            $time_begin = $time[0]? str_replace(array('T','Z'),' ',$time[0]): date('Y-m-d 00:00:00', strtotime('-1 year'));
            $time_end  = $time[1] ? str_replace(array('T','Z'),' ',$time[1]) :date('Y-m-d H:i:s');
        }else{
            $time_begin 	= 			isset($_POST['time_begin']) ? $_POST['time_begin'] : date('Y-m-d 00:00:00', strtotime('-1 year'));
            $time_end 		= 			isset($_POST['time_end']) ? $_POST['time_end'] :date('Y-m-d H:i:s');
        }
        $isAllTime 		= 			isset($_POST['isAllTime']) ? $_POST['isAllTime'] : 0;
        $isList         = $_POST['isList']?$_POST['isList']:'0';
        $CompanyorderM							=			$this->MODEL('companyorder');
        $PackM									=			$this->MODEL('pack');
        if($isAllTime != 1){
            $timeBegin 							= 			strtotime($time_begin);
            $timeEnd 							= 			strtotime($time_end);
            $dateBegin 							= 			date('Y-m-d', $timeBegin);
            $dateEnd 							= 			date('Y-m-d', $timeEnd);
            $title 								=			"收支统计 - {$dateBegin}~{$dateEnd}";
        }else{
            $title 								= 			"收支统计 - 全部数据";
        }
        //收支总计 查询：in毛收入，out支出/提现，net_income净收入
        $values 								= 			array();//纵坐标的值
        $dataGroupNames 						= 			array();//柱行代表数据的名称
        $comwhere['order_price'] = array('>', 1); // 订单金额需要大于1
        //毛收入
        $comwhere['order_state']				=			2;
        if($isAllTime != 1){
            $comwhere['order_time'][]         	=   		array('>=', $timeBegin);
            $comwhere['order_time'][]         	=           array('<=', $timeEnd,'AND');
        }
        $comwhere['groupby']					=			'date';
        $comwhere['orderby']					=			array('date,asc');
        $comfield 								=			"sum(order_price) as `num`, from_unixtime(order_time,'%Y-%m-%d') as `date`";
        $row									=			$CompanyorderM->getList($comwhere,array('field'=>$comfield));
        $totalIn 								= 			0;//收入总计
        if(count($row) < 1){
            $in 								= 			array('names' => array(), 'values' => array());
        }else{
            $inNames 							= 			array();
            $inValues 							= 			array();
            foreach($row as $r){
                $inNames [] 					= 			$r['date'];
                $inValues [] 					= 			$r['num'];
                $totalIn 						+= 			$r['num'];
            }
            $in 								 = 			array('names' => $inNames, 'values' => $inValues);
        }
        //支出/提现
        $memwhere['order_state']		=			1;
        if($isAllTime != 1){
            $memwhere['time'][]         =   		array('>=', $timeBegin);
            $memwhere['time'][]         =           array('<=', $timeEnd,'AND');
        }

        $memwhere['groupby']			=			'date';
        $memwhere['orderby']			=			'date';

        $memfield						= 			"sum(order_price) as num, from_unixtime(`time`,'%Y-%m-%d') as `date`";
        $mrow							=			$PackM->getList($memwhere,array('field'=>$memfield));
        $totalOut 						= 			0;
        if(count($mrow) < 1){
            $out 						= 			array('names' => array(), 'values' => array());
        }else{
            $outNames 					= 			array();
            $outValues 					=			array();
            foreach($mrow as $r){
                $outNames [] 			= 		   $r['date'];
                $outValues [] 			= 		   $r['num'];
                $totalOut 				+= 		   $r['num'];
            }
            $out 						= 		  array('names' => $outNames, 'values' => $outValues);
        }
        //净收入 综合names、values
        $names 							= 		 array_unique(array_merge($in['names'], $out['names']));
        usort($names,'t_sort');
        $inValues 						= 		  array();
        $outValues 					    = 		  array();
        $inK 							= 		  0;
        $outK 							= 		  0;
        foreach($names as $n){
            if(in_array($n, $in['names'])){
                $inValues [] 			=		 $in['values'][$inK];
                $inK ++;
            }else{
                $inValues [] 			= 		0;
            }
            if(in_array($n, $out['names'])){
                $outValues [] 			= 		$out['values'][$outK];
                $outK ++;
            }else{
                $outValues [] 			= 		0;
            }
            $netIncomeValues [] 		= 		end($inValues) - end($outValues);
        }
        $in['values'] 				    = 		$inValues;
        $out['values'] 					= 		$outValues;
        $dataGroupNames [] 				= 		'毛收入';
        $dataGroupNames [] 				= 		'支出/提现';
        $dataGroupNames [] 				= 		'净收入';
        $values[] 						= 		array('name' => '毛收入','type' => $this->chartType,'data' => $in['values']);
        $values[] 						= 		array('name' => '支出/提现','type' => $this->chartType,'data' => $out['values']);
        $values[] 						=   	array('name' => '净收入','type' => $this->chartType,'data' => $netIncomeValues);
        $money =array('totalIn'=> $totalIn, 'totalOut' => $totalOut, 'totalNetIncome' => $totalIn - $totalOut);
        if ($isList){
            $list = array();
            foreach ($names as $k=> $v){
                $list[] = array('times'=>$v,'maosr'=>$inValues[$k],'zctx'=>$outValues[$k],'jinsr'=>$netIncomeValues[$k]);
            }
            $list[] = array('times'=>'总计','maosr'=>$totalIn,'zctx'=>$totalOut,'jinsr'=>$totalIn - $totalOut);
            $data = 		array('title' => $title);

            $this->render_json('0','',['list'=>$list,'data'=>$data]);
        }else{
            $data = 		array('title' => $title,'names' => $names, 'values' => $values, 'dataGroupNames' => $dataGroupNames);
            $this->render_json('0','',array('data'=>$data,'money'=>$money));
        }
    }


    public function income_action(){

        //查询报表数据
        $time_begin 	= 			isset($_POST['time_begin']) ? $_POST['time_begin'] : date('Y-m-d 00:00:00', strtotime('-7 day'));
        $time_end 		= 			isset($_POST['time_end']) ? $_POST['time_end'] :date('Y-m-d H:i:s');
        $isAllTime 		= 			isset($_POST['isAllTime']) ? $_POST['isAllTime'] : 0;

        $CompanyorderM					=			$this->MODEL('companyorder');

        //如果不是查询全部数据，组织查询的开始、结束时间
        if($isAllTime != 1){
            $timeBegin 					= 			strtotime($time_begin);
            $timeEnd 					 =	 		strtotime($time_end);
            $dateBegin 					 = 			date('Y-m-d', $timeBegin);
            $dateEnd 					 = 			date('Y-m-d', $timeEnd);
            $title 						 = 			"收益渠道统计 - {$dateBegin}~{$dateEnd}";
        } else{
            $title 						  =		 	"收益渠道统计 - 全部数据";
        }
        $names 							  = 			 array();//扇形每块的名称（收入渠道）
        $values							  = 			 array();//扇形每块的值

        $comwhere['order_price'] = array('>', 1); // 订单金额需要大于1
        $comwhere['order_state']		  =			2;
        if($isAllTime != 1){
            $comwhere['order_time'][]     =   		 array('>=', $timeBegin);
            $comwhere['order_time'][]     =          array('<=', $timeEnd,'AND');
        }
        $comwhere['groupby']			  =			'type';
        $comwhere['orderby']			  =			array('num,asc');
        $field 							  = 		'sum(order_price) as `num`, `type`';
        $row							  =			$CompanyorderM->getList($comwhere,array('field'=>$field));
        $total 							  = 		0;
        foreach($row as $r){
            if(isset($this->typeMapping[$r['type']])){
                $names [] 						= 		$this->typeMapping[$r['type']];
                $rr['value'] 					= 		$r['num'];
                $rr['name'] 					= 		$this->typeMapping[$r['type']];
                $values [] 						= 		$rr;
                $total 						   += 		$r['num'];
            }
        }
        $data = array('title' => $title,'names' => $names, 'values' => $values );
        $this->render_json('0','',['data'=>$data,'total'=>$total]);
    }

    public function city_action(){
        $CompanyorderM							=			$this->MODEL('companyorder');
        $CompanyM								=			$this->MODEL('company');
        //查询报表数据
        $time_begin = 			isset($_POST['time_begin']) ? $_POST['time_begin'] : date('Y-m-d 00:00:00', strtotime('-7 day'));
        $time_end 	= 			isset($_POST['time_end']) ? $_POST['time_end'] :date('Y-m-d H:i:s');
        $isAllTime 	= 			isset($_POST['isAllTime']) ? $_POST['isAllTime'] : 0;
        //查询30条内的信息，展示到页面第一屏
        if($isAllTime != 1){
            $timeBegin 	= 			strtotime($time_begin);
        }else{
            $timeBegin 	= 			strtotime(date('Y-m-d 00:00:00', strtotime('-30 year')));
        }
        $timeEnd 		= 			strtotime("now");
        //如果不是查询全部数据，组织查询的开始、结束时间
        if($isAllTime != 1){
            $timeBegin 							= 			strtotime($time_begin);
            $timeEnd 							= 			strtotime($time_end);
            $dateBegin 							= 			date('Y-m-d', $timeBegin);
            $dateEnd 							= 			date('Y-m-d', $timeEnd);
            $title 								= 			"消费最多地区统计 - {$dateBegin}~{$dateEnd}";
        }else{
            $title 								= 			"消费最多地区统计 - 全部数据";
        }
        $names 	=$values = 			array();//扇形每块的名称（收入渠道）
        $topNum 								= 			10;//统计消费最多10个地区
        $orwhere['order_price'] = array('>', 1); // 订单金额需要大于1
        $orwhere['order_state']					=			2;
        if($isAllTime != 1){
            $orwhere['order_time'][]     		=   		array('>=', $timeBegin);
            $orwhere['order_time'][]     		=           array('<=', $timeEnd,'AND');
        }
        $orwhere['groupby']						=			'uid';
        $orwhere['orderby']						=			array('num,asc');
        $orwhere['limit']						=			$topNum * 10;;
        $field 									= 			'sum(order_price) as `num`, `uid`';
        $row									=			$CompanyorderM->getList($orwhere,array('field'=>$field));
        $uidArr =$uidValue	= array();
        foreach($row as $r){
            $uidArr [] 							= 			$r['uid'];
            $uidValue[$r['uid']] 				= 			$r['num'];
        }
        $comwhere['uid']						=			array('in',pylode(',',$uidArr));
        $comwhere['cityid']						=			array('>',0);
        $data									=			$CompanyM->getChCompanyList($comwhere,array('field'=>'`uid`,`cityid`,`provinceid`'));
        $total 									= 			0;
        foreach($data as $r){
            if(array_key_exists($r['cityid'], $values) ){
                $values [$r['cityid']]['value'] 		+= 			$uidValue[$r['uid']];
            }else{
                $values [$r['cityid']]['value'] 		= 			$uidValue[$r['uid']];
                $values [$r['cityid']]['pid'] 			= 			$r['provinceid'];
                $values [$r['cityid']]['cid'] 			= 			$r['cityid'];
            }
        }
        usort($values,'my_sort');
        $city 						= 			$this->MODEL('cache')->GetCache(array('city'));
        $arr 						= 			array();
        $i 							= 			0;
        foreach($values as $r){
            $names [] 				=			 $city['city_name'][$r['pid']] . '-' . $city['city_name'][$r['cid']];
            $rr['value'] 			=			 $r['value'];
            $rr['name'] 			= 			 $city['city_name'][$r['pid']] . '-' . $city['city_name'][$r['cid']];
            $arr [] 				= 			 $rr;
            $total 					+= 			 $r['value'];
            $i ++;
            if($i == $topNum){
                break;
            }
        }
        $values	 		= 			$arr;
        $this->render_json(0,'', array('title' => $title,'names' => $names, 'values' => $values ) );
    }

    public function hangye_action(){
        //查询报表数据
        $time_begin 					= 			isset($_POST['time_begin']) ? $_POST['time_begin'] : date('Y-m-d 00:00:00', strtotime('-7 day'));
        $time_end 						= 			isset($_POST['time_end']) ? $_POST['time_end'] :date('Y-m-d H:i:s');
        $isAllTime 						= 			isset($_POST['isAllTime']) ? $_POST['isAllTime'] : 0;
        //查询30条内的信息，展示到页面第一屏
        if($isAllTime != 1){
            $timeBegin 					= 			strtotime($time_begin);
        }else{
            $timeBegin 					= 			strtotime(date('Y-m-d 00:00:00', strtotime('-30 year')));
        }
        $timeEnd						= 			strtotime("now");
        //如果不是查询全部数据，组织查询的开始、结束时间
        if($isAllTime != 1){
            $timeBegin 					= 			strtotime($time_begin);
            $timeEnd 					= 			strtotime($time_end);
            $dateBegin 					= 			date('Y-m-d', $timeBegin);
            $dateEnd 					= 			date('Y-m-d', $timeEnd);
            $title						= 			"消费最多行业统计 - {$dateBegin}~{$dateEnd}";
        }else{
            $title 						= 			"消费最多行业统计 - 全部数据";
        }
        $names 							= 			array();//扇形每块的名称（收入渠道）
        $values 						= 			array();//扇形每块的值
        $topNum 						=			 10;//统计消费最多10个行业
        $comwhere['order_price'] = array('>', 1); // 订单金额需要大于1
        $comwhere['order_state']		=			 2;//只查询已付款订单
        if($isAllTime != 1){
            $comwhere['order_time'][]    =   	array('>=', $timeBegin);
            $comwhere['order_time'][]    =      array('<=', $timeEnd,'AND');
        }
        $comwhere['groupby']			 =			'uid';
        $comwhere['orderby']			 =			array('num,desc');
        $comwhere['limit']				 =			$topNum * 10;
        $comfield						 =			'sum(order_price) as `num`, `uid`';
        $CompanyorderM					 =			$this->MODEL('companyorder');
        $row							 =			$CompanyorderM->getList($comwhere,array('field'=>$comfield));
        $uidArr 						 = 			array();
        $uidValue 						 = 			array();
        foreach($row as $r){
            $uidArr [] 					 = 			$r['uid'];
            $uidValue[$r['uid']] 		 = 			$r['num'];
        }
        $companywhere['uid']			 =			array('in',pylode(',',$uidArr));
        $companywhere['hy']				 =			array('>',0);
        $CompanyM						 =			$this->MODEL('company');
        $data							 =			$CompanyM->getChCompanyList($companywhere,array('field'=>'`uid`,`hy`'));

        foreach($data as $r){
            if(array_key_exists($r['hy'], $values) ){
                $values [$r['hy']]['value'] 	+= 		$uidValue[$r['uid']];
            }else{
                $values [$r['hy']]['value'] 	 = 		$uidValue[$r['uid']];
                $values [$r['hy']]['hy'] 		 = 		$r['hy'];
            }
        }

        usort($values,'my_sort');
        $hy 				= 				$this->MODEL('cache')->GetCache(array('hy'));
        $total =    $i  	= 				0;
        $arr 				=				array();

        foreach($values as $r){

            $names [] 		= 				$hy['industry_name'][$r['hy']];
            $rr['value'] 	= 				$r['value'];
            $rr['name'] 	= 				$hy['industry_name'][$r['hy']];
            $arr [] 		=				$rr;
            $total 			+= 				$r['value'];
            $i ++;
            if($i == $topNum){
                break;
            }
        }
        $values = 	$arr;
        $this->render_json('0','',array('title' => $title,'names' => $names, 'values' => $values ));
    }

    /**
     */
    public function  vip_companyorder_action(){
        //查询报表数据
        $time_begin 					= 			isset($_POST['time_begin']) ? $_POST['time_begin'] : date('Y-m-d 00:00:00', strtotime('-7 day'));
        $time_end 						= 			isset($_POST['time_end']) ? $_POST['time_end'] :date('Y-m-d H:i:s');
        $isAllTime 						= 			isset($_POST['isAllTime']) ? $_POST['isAllTime'] : 0;
        if($isAllTime != 1){
            $timeBegin 					= 			strtotime($time_begin);
        }else{
            $timeBegin 					= 			strtotime(date('Y-m-d 00:00:00', strtotime('-30 year')));
        }
        $timeEnd						= 			strtotime("now");

        $comwhere['order_price'] = array('>', 1); // 订单金额需要大于1
        $comwhere['order_state']		=			 2;//只查询已付款订单
        $comwhere['type']		        =			 1;//只查询已付款订单
        if($isAllTime != 1){
            $comwhere['order_time'][]    =   	array('>=', $timeBegin);
            $comwhere['order_time'][]    =      array('<=', $timeEnd,'AND');
        }
        $comwhere['groupby']			 =			'rating';
        $comwhere['orderby']			 =			array('num,desc');
        $comfield						 =			'count(1) as `num`, `rating`';
        $CompanyorderM					 =			$this->MODEL('companyorder');
        $row							 =			$CompanyorderM->getList($comwhere,array('field'=>$comfield));
        if (!$row){
            $this->render_json('0','',array('list' => [] ));
        }
        $valueKey = [];
        foreach ($row as $value){
            $value['rating'] && $valueKey[] = $value['rating'];
        }
        $ratingarr  =   array();
        $ratingM    =   $this->MODEL('rating');
        $rating     =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort','id'=>array('in',pylode(',',$valueKey))), array('field' => '`id`,`name`'));
        if (!empty($rating)) {
            foreach ($rating as $v) {
                $ratingarr[$v['id']]    = $v['name'];
            }
        }
        foreach($row as $r){
            $rr['value'] 					= 		$r['num'];
            $rr['name'] 					= 		!empty($r['rating']) && !empty($ratingarr[$r['rating']]) ? $ratingarr[$r['rating']] : '未知套餐';
            $values [] 						= 		$rr;
        }
        $this->render_json('0','',['values' => $values]);

    }


    /**
     */
    public function  vipprice_companyorder_action(){
        //查询报表数据
        $time_begin 					= 			isset($_POST['time_begin']) ? $_POST['time_begin'] : date('Y-m-d 00:00:00', strtotime('-7 day'));
        $time_end 						= 			isset($_POST['time_end']) ? $_POST['time_end'] :date('Y-m-d H:i:s');
        $isAllTime 						= 			isset($_POST['isAllTime']) ? $_POST['isAllTime'] : 0;
        if($isAllTime != 1){
            $timeBegin 					= 			strtotime($time_begin);
        }else{
            $timeBegin 					= 			strtotime(date('Y-m-d 00:00:00', strtotime('-30 year')));
        }
        $timeEnd						= 			strtotime("now");

        $comwhere['order_price'] = array('>', 1); // 订单金额需要大于1
        $comwhere['order_state']		=			 2;//只查询已付款订单
        $comwhere['type']		        =			 1;//只查询已付款订单
        if($isAllTime != 1){
            $comwhere['order_time'][]    =   	array('>=', $timeBegin);
            $comwhere['order_time'][]    =      array('<=', $timeEnd,'AND');
        }
        $comwhere['groupby']			 =			'rating';
        $comwhere['orderby']			 =			array('num,desc');
        $comfield						 =			'sum(order_price) as `num`, `rating`';
        $CompanyorderM					 =			$this->MODEL('companyorder');
        $row							 =			$CompanyorderM->getList($comwhere,array('field'=>$comfield));
        if (!$row){
            $this->render_json('0','',array('list' => [] ));
        }
        $valueKey = [];
        foreach ($row as $value){
            $value['rating'] && $valueKey[] = $value['rating'];
        }
        $ratingarr  =   array();
        $ratingM    =   $this->MODEL('rating');
        $rating     =   $ratingM->getList(array('category' => '1', 'orderby' => 'sort','id'=>array('in',pylode(',',$valueKey))), array('field' => '`id`,`name`'));
        if (!empty($rating)) {
            foreach ($rating as $v) {
                $ratingarr[$v['id']]    = $v['name'];
            }
        }
        foreach($row as $r){
            $rr['value'] 					= 		$r['num'];
            $rr['name'] 					= 		!empty($r['rating']) && !empty($ratingarr[$r['rating']]) ? $ratingarr[$r['rating']] : '未知套餐';
            $values [] 						= 		$rr;
        }
        $this->render_json('0','',['values' => $values]);

    }




    /**
     *
     */
    public function  create_renewal_compare_action(){
        //查询报表数据
        $time_begin 					= 	$_POST['time_begin'] ?strtotime($_POST['time_begin']) : strtotime(date('Y-m-d'))-(86400*7);
        $time_end 						= 	$_POST['time_end'] ? strtotime($_POST['time_end']) :strtotime(date('Y-m-d H:i:s'));
        $comwhere['order_price'] = array('>', 1); // 订单金额需要大于1
        $comwhere['order_state']		=	 2;//只查询已付款订单
        $comwhere['type']		        =	 1;//类型
        $comwhere['order_time'][]    =   	array('>=', $time_begin);
        $comwhere['order_time'][]    =      array('<=', $time_end,'AND');
        $comwhere['groupby']			 =			'uid';
        $comwhere['orderby']			 =			array('num,desc');
        $comfield						 =			'count(1) as `num`, `uid`';
        $CompanyorderM					 =			$this->MODEL('companyorder');
        $rows							 =			$CompanyorderM->getList($comwhere,array('field'=>$comfield));
        if (!$rows){
            $this->render_json('1');
        }
        $createNum = 0;
        $renewalNum = 0;
        foreach ($rows as $value){
            $where = array(
                'uid'=>$value['uid'],
                'type'=>1,
                'order_state'=>2,
                'order_price' => array('>', 1),
                'orderby'	=>	array('order_time,asc')
            );
            $row = $CompanyorderM->getOrderInfoByParams($where);
            if($row['order_time'] >=$time_begin){
                $createNum++;
                if ($value['num']>1){
                    $num = $value['num'] -1;
                    $renewalNum +=$num;
                }
            }else{
                $renewalNum +=$value['num'];
            }
        }
        $values = array(
            array(
                'value' 	=> 		$createNum,
                'name' 		=> 		'新开单'
            ),
            array(
                'value' 	=> 		$renewalNum,
                'name' 		=> 		'续费'
            ),
        );
        $this->render_json('0','',['values' => $values]);
    }



    /**
     *
     */
    public function  create_renewal_compare_price_action(){
        //查询报表数据
        $time_begin 					= 	$_POST['time_begin'] ?strtotime($_POST['time_begin']) : strtotime(date('Y-m-d'))-(86400*7);
        $time_end 						= 	$_POST['time_end'] ? strtotime($_POST['time_end']) :strtotime(date('Y-m-d H:i:s'));
        $comwhere['order_price'] = array('>', 1); // 订单金额需要大于1
        $comwhere['order_state']		=	 2;//只查询已付款订单
        $comwhere['type']		        =	 1;//类型
        $comwhere['order_time'][]    =   	array('>=', $time_begin);
        $comwhere['order_time'][]    =      array('<=', $time_end,'AND');
        $comwhere['groupby']			 =			'uid';
        $comwhere['orderby']			 =			array('num,desc');
        $comfield						 =			'count(1) as `num`, sum(order_price) price ,`uid`';
        $CompanyorderM					 =			$this->MODEL('companyorder');
        $rows							 =			$CompanyorderM->getList($comwhere,array('field'=>$comfield));
        if (!$rows){
            $this->render_json('1');
        }
        $createNum = 0;
        $renewalNum = 0;
        foreach ($rows as $value){
            $where = array(
                'uid'=>$value['uid'],
                'type'=>1,
                'order_state'=>2,
                'order_price' => array('>', 1),
                'orderby'	=>	array('order_time,asc')
            );
            $row = $CompanyorderM->getOrderInfoByParams($where);
            if($row['order_time'] >=$time_begin){
                $createNum = bcadd($createNum, $row['order_price'], 2);
                if ($value['num']>1){
                    $price = bcsub($value['price'], $row['order_price'], 2);
                    $renewalNum = bcadd($renewalNum, $price, 2);
                }
            }else{
                $renewalNum = bcadd($renewalNum, $value['price'], 2);
            }
        }
        $values = array(
            array(
                'value' 	=> 		$createNum,
                'name' 		=> 		'新开单'
            ),
            array(
                'value' 	=> 		$renewalNum,
                'name' 		=> 		'续费'
            ),
        );
        $this->render_json('0','',['values' => $values]);
    }


    /**
     * @return 类型消费图标统计
     */
    public function xiaofei_action(){
        //查询报表数据
        $time_begin 					= 	$_POST['time_begin'] ?strtotime($_POST['time_begin']) : strtotime(date('Y-m-d'))-(86400*7);
        $time_end 						= 	$_POST['time_end'] ? strtotime($_POST['time_end']) :strtotime(date('Y-m-d H:i:s'));
        $start_time = $time_begin;
        $end_time = $time_end;
        $xfSql = "SELECT 
                type,FROM_UNIXTIME(order_time,'%Y-%m-%d') as ordertimeYmd,count(1) cou,sum(order_price) num FROM `{$this->def}company_order`
                WHERE order_time >={$start_time} AND order_time <={$end_time} AND `order_state` = 2 AND `order_price` > 1 
                GROUP BY type,ordertimeYmd order by ordertimeYmd asc  ";

        include_once(CONFIG_PATH.'db.data.php');
        $ordertype = @$arr_data['ordertype'];
        $xfList = $this->obj->DB_query_all($xfSql, 'all');
        if (!$xfList){
            $this->render_json(1,'数据不存在');
        }
        // 处理数据
        $xzhou =[];
        // 订单中的种类有多少
        $newXfList = [];
        $dataGroupNames = [];
        $dataGroup = [];
        foreach ($xfList as $value){
            if (!in_array($value['ordertimeYmd'],$xzhou)){
                $xzhou[] = $value['ordertimeYmd'];
            }
            if(!in_array($value['type'],$dataGroup)){
                $dataGroup[] = $value['type'];
                $dataGroupNames[$value['type']] = $ordertype[$value['type']];
            }
            $newXfList[$value['ordertimeYmd'].'_'.$value['type']] = $value;
        }
        $values = array();
        foreach ($dataGroup as $group){
            $rts =array(
                "name"=> $dataGroupNames[$group],
                "type"=> "line",
            );
            $data = array();
            foreach ($xzhou as $value){
                $data[] = isset($newXfList[$value.'_'.$group])?$newXfList[$value.'_'.$group]['num']:0;
            }
            $rts['data'] = $data;
            $values[] = $rts;
        }
        sort($dataGroupNames);

        $this->render_json('0','',array('values'=>$values,'dataGroupNames'=>$dataGroupNames,'names'=>$xzhou));
    }


    public function xiaofei_list_action(){
        //查询报表数据
        $time_begin 					= 	$_POST['time_begin'] ?strtotime($_POST['time_begin']) : strtotime(date('Y-m-d'))-(86400*7);
        $time_end 						= 	$_POST['time_end'] ? strtotime($_POST['time_end']) :strtotime(date('Y-m-d H:i:s'));
        $comwhere['order_price'] = array('>', 1); // 订单金额需要大于1
        $comwhere['order_state']		=	 2;//只查询已付款订单
        $comwhere['order_time'][]    =   	array('>=', $time_begin);
        $comwhere['order_time'][]    =      array('<=', $time_end,'AND');
        $comwhere['groupby']			 =			'type';
        $comfield						 =			'sum(order_price) price ,type';
        $CompanyorderM					 =			$this->MODEL('companyorder');
        $rows							 =			$CompanyorderM->getList($comwhere,array('field'=>$comfield));
        if (!$rows){
            $this->render_json('1');
        }
        include(CONFIG_PATH.'db.data.php');
        $ordertype = $arr_data['ordertype'];
        foreach ($rows as &$row){
            $row['price'] = $this->isFloat($row['price']);
            $row['name'] =$ordertype[$row['type']];
        }
        $this->render_json('0','',['list'=>$rows]);
    }

    function  isFloat($num){
        $b=(int)$num;
        if($num-$b==0){
            return $b;
        } else{
            return $num;
        }
    }
}
?>