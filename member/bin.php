<?php
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/include/conn.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/webConfig.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/member/logged_data.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/pay/pay.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/CNYFundTool.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/UserWallet.php';


if($act == 'pa'){
	if(!$memberLogged){
		echo '您没有登录，请登录后再操作！';
		exit;
	}
	
	$sql = "update `h_member` set h_a1 = '" . $a1 . "',h_q1 = '" . $q1 . "',h_a2 = '" . $a2 . "',h_q2 = '" . $q2 . "',h_a3 = '" . $a3 . "',h_q3 = '" . $q3 . "' where h_userName = '{$memberLogged_userName}'";
	$db->query($sql);
	
	echo '更新成功';
}
else if($act == 'pa_qq'){
	if(!$memberLogged){
		echo '您没有登录，请登录后再操作！';
		exit;
	}
	
	$sql = "update `h_member` set h_qq = '" . $qq . "' where h_userName = '{$memberLogged_userName}'";
	$db->query($sql);
	
	echo '更新成功';
}
else if($act == 'msg_get'){
	if(!$memberLogged){
		echo '您没有登录，请登录后再操作！';
		exit;
	}
	
	$rs = $db->get_one("select * from `h_member_msg` where (h_userName = '{$memberLogged_userName}' or h_toUserName = '{$memberLogged_userName}' or h_toUserName = '[所有会员]') and id = '{$id}'");
	if(!$rs){
		echo '未找到该信息';
		exit;
	}
	echo $rs['h_info'];
	
	//更新为已读
	if($rs['h_toUserName'] == $memberLogged_userName){
		if(!$rs['h_isRead']){
			$sql = "update `h_member_msg` set ";
			$sql .= "h_isRead = '1', ";
			$sql .= "h_readTime = '" . date('Y-m-d H:i:s') . "' ";
			$db->query($sql);
		}
	}
}
else if($act == 'msg_post'){
	if(!$memberLogged){
		echo '您没有登录，请登录后再操作！';
		exit;
	}
	
	if(strlen($username) <= 0){
		echo '请输入正确的玩家编号';
		exit;
	}
	if(strlen($info) <= 0){
		echo '内容不可为空';
		exit;
	}
	if($memberLogged_userName == $username){
		echo '请不要写给自己';
		exit;
	}
	
	if($username != '[管理员]'){
		$rs = $db->get_one("select * from `h_member` where h_userName = '{$username}'");
		if(!$rs){
			echo '您输入的玩家编号不存在';
			exit;
		}
	}
	
	//写给对方
	$sql = "insert into `h_member_msg` set ";
	$sql .= "h_userName = '" . $memberLogged_userName . "', ";
	$sql .= "h_toUserName = '" . $username . "', ";
	$sql .= "h_info = '" . $info . "', ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_actIP = '" . getUserIP() . "' ";
	$db->query($sql);
	
	echo '信件已发出';
}
else if($act == 'point2_shop_buy'){
	if(!$memberLogged){
		echo '您没有登录，请登录后再操作！';
		exit;
	}
	
	if(strlen($goodsIds) <= 0){
		echo '您没有选择商品';
		exit;
	}
	if(strlen($goodsNums) <= 0){
		echo '商品数量错误';
		exit;
	}
	$goodsIdsArr = explode(',',$goodsIds);
	$goodsNumsArr = explode(',',$goodsNums);
	if(count($goodsIdsArr) != count($goodsNumsArr)){
		echo '拆解数据出错1';
		exit;
	}
	$goodsIN = array();
	for($ci = 0;$ci < count($goodsIdsArr);$ci++){
		$id = intval($goodsIdsArr[$ci]);
		$num = intval($goodsNumsArr[$ci]);
		if($id <= 0 || $num <= 0){
			echo '拆解数据出错2';
			exit;
		}
		$goodsIN[$id] = $num;
	}
	
	if(strlen($address) <= 0){
		echo '请填写收货地址';
		exit;
	}
	if(strlen($postcode) <= 0){
		echo '请填写邮政编码';
		exit;
	}
	if(strlen($fullname) <= 0){
		echo '请填写收货人';
		exit;
	}
	if(strlen($tel) <= 0){
		echo '请填写手机号码';
		exit;
	}
	
	/*
	if(strlen($pwdII) <= 0){
		echo '请输入安全密码';
		exit;
	}
	$pwdII = md5($pwdII);
	*/

	$rs = $db->get_one("select * from `h_member` where h_userName = '{$memberLogged_userName}'");
	if(!$rs){
		echo '未找到您的登录信息';
		exit;
	}
	/*
	if($rs['h_point2'] < $num){
		echo '您的元不足';
		exit;
	}
	if($rs['h_passWordII'] != $pwdII){
		echo '安全密码错误';
		exit;
	}
	*/
	
	//循环检测和购买
	$query = "select * from `h_point2_shop` where id in ({$goodsIds})";
	$result = $db->query($query);
	if($db->num_rows($result) <= 0){
		echo '未找到任何商品';
		exit;
	}
	$moneySum = 0;
	while($rs_list = $db->fetch_array($result)){
		//先遍历金额
		$moneySum += intval($rs_list['h_money']) * $goodsIN[$rs_list['id']];
		
		//等级是否符合
		if($rs_list['h_minMemberLevel'] > $rs['h_level']){
			echo '您不符合' , $rs_list['h_title'] , '的购买条件';
			exit;
		}
	}
	//判断金额是否够
	if($moneySum > intval($rs['h_point2'])){
		echo '抱歉，您的余额(' , $rs['h_point2'] , ')不足以支付：' , $moneySum;
		exit;
	}
	//重新遍历并购买
	$oid = date('YmdHis') . rndNum(3);
	$db->move_first($result);
	$money = 0;
	while($rs_list = $db->fetch_array($result)){
		$num = intval($rs_list['h_money']) * $goodsIN[$rs_list['id']];
		
		$money += $num;
		
		//扣钱
		$sql = "update `h_member` set ";
		$sql .= "h_point2 = h_point2 - {$num} ";
		$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
		$db->query($sql);
		
		//记录扣钱
		$sql = "insert into `h_log_point2` set ";
		$sql .= "h_userName = '" . $memberLogged_userName . "', ";
		$sql .= "h_price = '-" . $num . "', ";
		$sql .= "h_type = '商城购物', ";
		$sql .= "h_about = '" . $rs_list['h_title'] . "，数量：" . $goodsIN[$rs_list['id']] . "', ";
		$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
		$sql .= "h_actIP = '" . getUserIP() . "' ";
		$db->query($sql);
		
		//增加进购物车
		$sql = "insert into `h_member_shop_cart` set ";
		$sql .= "h_oid = '" . $oid . "', ";
		$sql .= "h_userName = '" . $memberLogged_userName . "', ";
		$sql .= "h_pid = '" . $rs_list['id'] . "', ";
		$sql .= "h_num = '" . $goodsIN[$rs_list['id']] . "', ";
		$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
		$sql .= "h_title = '" . $rs_list['h_title'] . "', ";
		$sql .= "h_pic = '" . $rs_list['h_pic'] . "', ";
		$sql .= "h_money = '" . $rs_list['h_money'] . "' ";
		$db->query($sql);
		//echo $sql;
	}
	
	//记录进订单 
	$sql = "insert into `h_member_shop_order` set ";
	$sql .= "h_oid = '" . $oid . "', ";
	$sql .= "h_userName = '" . $memberLogged_userName . "', ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_addrAddress = '" . $address . "', ";
	$sql .= "h_addrPostcode = '" . $postcode . "', ";
	$sql .= "h_addrFullName = '" . $fullname . "', ";
	$sql .= "h_addrTel = '" . $tel . "', ";
	$sql .= "h_remark = '" . $remark . "', ";
	$sql .= "h_money = '" . $money . "', ";
	$sql .= "h_state = '待发货' ";
	$db->query($sql);
		 //进行物品元的结算，以及提成发放
	settle_farm_day($memberLogged_userName);
	echo '购买成功';
}
else if($act == 'point2_withdraw'){
  
  	
	if(!$memberLogged){
		echo '您没有登录，请登录后再操作！';
		exit;
	}
	
	$num = intval($num);
	if($num <= 0){
		echo '提现至少要' , $webInfo['h_withdrawMinMoney'] , '元';
		exit;
	}

	/*if(strlen($alipayUserName) <= 0){
		echo '请输入收款账号';
		exit;
	}
	
	if(!preg_match("/^((\(\d{3}\))|(\d{3}\-))?1\d{10}$/", $alipayUserName)){
		exit("收款账号错误");
	}
	
	if(strlen($alipayFullName) <= 0){
		echo '请输入收款姓名';
		exit;
	}*/
    /*if(strlen($h_passWordII) <= 0){
          //echo '请输入安全密码';
          //exit;
      }
	$pwdII = md5($h_passWordII);
    */	
	$rs = $db->get_one("select *,(select count(id) from `h_member` where h_parentUserName = a.h_userName and h_isPass = 1) as comMembers from `h_member` a where h_userName = '{$memberLogged_userName}'");
	if(!$rs){
		echo '未找到您的登录信息';
		exit;
	}
    /*if($rs['h_passWordII']!=$pwdII){
   		//echo '密码错误';
		//exit;
    }*/
  
	if($rs['h_point2'] < $num){
		echo '您的元不足';
		exit;
	}
	if($rs['comMembers'] < $webInfo['h_withdrawMinCom']){
		echo '您的账号至少要直推' , $webInfo['h_withdrawMinCom'] , '个人才能提现';
		exit;
	}
	/*
	if($rs['h_passWordII'] != $pwdII){
		echo '安全密码错误';
		exit;
	}*/

	error_log("redeem: pass basic checking");
	
	//扣钱
	$db->set_autocommit(FALSE);
	$db->begin_trans();
	$sql = "update `h_member` set ";
	$sql .= "h_alipayUserName = '" . $alipayUserName . "', ";
	$sql .= "h_alipayFullName = '" . $alipayFullName . "', ";
	$sql .= "h_point2 = h_point2 - {$num} ";
	$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
	$db->query($sql);
	
	//记录扣钱
	$sql = "insert into `h_log_point2` set ";
	$sql .= "h_userName = '" . $memberLogged_userName . "', ";
	$sql .= "h_price = '-" . $num . "', ";
	$sql .= "h_type = '申请提现', ";
	$sql .= "h_about = '', ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_actIP = '" . getUserIP() . "' ";
	$db->query($sql);
	
	//提现下单
	$out_trade_no = date('YmdHis').rand(100000,999999);

	$sql = "insert into `h_withdraw` set ";
	$sql .= "h_userName = '" . $memberLogged_userName . "', ";
	$sql .= "h_money = '" . $num . "', ";
	$sql .= "h_fee = '" . ($num * $webInfo['h_withdrawFee']) . "', ";
	$sql .= "h_bank = '" . $h_fullName . "', ";
	$sql .= "h_bankFullname = '" . $alipayFullName . "', ";
	$sql .= "h_bankCardId = '" . $alipayUserName . "', ";
	
	//$sql .= "qrcode = '" . $qrcode . "', ";
	$sql .= "h_state = '待审核', ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_imgs = '" . $qrcode . "', ";
	$sql .= "out_trade_no = '" . $out_trade_no . "', ";
	$sql .= "h_actIP = '" . getUserIP() . "' ";
	$db->query($sql);
	
	error_log("redeem: create db withdraw records");

	// the fee rate is negative 
	$total_fee = $num + $num * $webInfo['h_withdrawFee'];
	$subject = 'withdraw:'.$memberLogged_userName;
	if (FCBPayConfig::INTESTMODE) {
		$config['notify_url'] = FCBPayConfig::THISSITEDEV . '/notify.php';
		$config['return_url'] = FCBPayConfig::THISSITEDEV . '/return.php';	
	}else {
		$config['notify_url'] = FCBPayConfig::THISSITEPROD . '/notify.php';
		$config['return_url'] = FCBPayConfig::THISSITEPROD . '/return.php';
	}

	$config['out_trade_no'] = $out_trade_no;
	$config['subject'] = $subject;

	$qrcode_url = '';
	if (FCBPayConfig::INTESTMODE) {
		$qrcode_url = FCBPayConfig::THISSITEDEV . '/member/getpaymentqrcode.php?out_trade_no=' . $out_trade_no;
	} else {
		$qrcode_url = FCBPayConfig::THISSITEPROD . '/member/getpaymentqrcode.php?out_trade_no=' . $out_trade_no;
	}

	$config['total_fee'] = $total_fee*100;
	$config['attach'] = 'weixin=' . $rs['h_weixin'] . ';username='.$memberLogged_userName . ';' . $qrcode_url;
	$config['payment_account'] = "{$alipayUserName}";

	error_log("redeem: finish create api cal request");
	//记录提现记录
	$sql = "insert into `order` set ";
	$sql .= "username = '" . $memberLogged_userName . "', ";
	$sql .= "out_trade_no = '{$out_trade_no}', ";
	$sql .= "subject = '{$subject}', ";
	$sql .= "total_fee = " . $total_fee . ", ";
	$sql .= "type = 'withdraw', ";
	$sql .= "submit_time = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "ip = '" . getUserIP() . "' ";
	//echo $sql;
	$db->query($sql);
	
	error_log("redeem: about to call redeem api");
	try {
		// load user wallet and create it if it does not exist
		error_log("withdraw: load user wallet of " . $memberLogged_userName);
		$userwallet = UserWallet::load_by_username($db, $memberLogged_userName, 'CNYF');
		if (is_null($userwallet)) {
			$userwallet->create($db, $memberLogged_userName, 'CNYF');
		}

		$wallet = new Wallet($db, 'CNYF');
		$cnytool = new CNYFundTool($wallet);
		$operationComment = 'withdraw: userId' . $userwallet->userId . '(' . $memberLogged_userName . ') amount: ' . $total_fee . ' to: ' . FCBPayConfig::REDEEMTARGETCNYFADDRESS;

		$pay = new pay();
		$data  = $pay->applyredeem($config);
	
		if ($data['return_code']=='SUCCESS'){
			error_log("redeem: call to redeem api succeeded");

			$transId = $cnytool->sendMoney(FCBPayConfig::REDEEMTARGETCNYFADDRESS, $num, $operationComment);
			error_log($operationComment . ' get transId ' . $transId);

			$log['data'] = $data;
			$log['debug_info'] = $pay->get_debug_info();
			$sql = "insert into `log` set ";
			$sql .= "logtime = '" . date('Y-m-d H:i:s') . "',";
			$sql .= "type = 'withdraw api test',";
			$sql .= "data = '" . json_encode($data,320) . "' ";
			$db->query($sql);
	
			$sql = "update `order` SET trx_bill_no='{$data['trx_bill_no']}' where out_trade_no ='{$out_trade_no}'";
			$db->query($sql);
			$db->commit();
			$db->set_autocommit(TRUE);
			echo '申请提现成功';
	
		} else {
			$db->rollback();
			$db->set_autocommit(TRUE);
			echo '申请提现失败';
		}
	
	} catch (PayException $pe) {
		$db->rollback();
		$db->set_autocommit(TRUE);
        $err_message = '提现错误:' . $pe->getMessage() . ".  请稍后再试.";
		error_log("redeem: hit exception " . $pe->getMessage());
		echo $err_message;
	}
}


else if($act == 'rrr'){
	
	
	if(!$memberLogged){
		echo '您没有登录，请登录后再操作！';
		exit;
	}
	
	//$num = intval($num);
	$num=$_GET['ccc'];
	$pay=$_GET['pay'];
	
	if($num <= 0){
		echo '充值金额不正确';
		exit;
	}
/*
	if(strlen($pwdII) <= 0){
		echo '请输入安全密码';
		exit;
	}
	$pwdII = md5($pwdII);
	*/
	
	if($pay <= 0){
		echo '请选择付款方式';
		exit;
	}

	
	$rs = $db->get_one("select *,(select count(id) from `h_member` where h_parentUserName = a.h_userName and h_isPass = 1) as comMembers from `h_member` a where h_userName = '{$memberLogged_userName}'");
	if(!$rs){
		echo '未找到您的登录信息';
		exit;
	}
	
	
	/*
	if($rs['h_passWordII'] != $pwdII){
		echo '安全密码错误';
		exit;
	}*/
	/*
	//扣钱
	$sql = "update `h_member` set ";
	$sql .= "h_point2 = h_point2 + {$num} ";
	$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
	$db->query($sql);
	
	//记录扣钱
	$sql = "insert into `h_log_point2` set ";
	$sql .= "h_userName = '" . $memberLogged_userName . "', ";
	$sql .= "h_price = '+" . $num . "', ";
	$sql .= "h_type = '在线充值', ";
	$sql .= "h_about = '', ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_actIP = '" . getUserIP() . "' ";
	$db->query($sql);
	
	//提现下单
*/ 
    $kkk = rand(10000000,99999999);
	$sql = "insert into `h_recharge` set ";
	$sql .= "h_userName = '" . $memberLogged_userName . "', ";
	$sql .= "h_money = '" . $num . "', ";
//	$sql .= "h_fee = '" . ($num * $webInfo['h_withdrawFee']) . "', ";
	$sql .= "h_bank = '" . $pay . "', ";
	$sql .= "h_bankFullname = '" . $kkk . "', ";
	$sql .= "h_state = 0, ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_actIP = '" . getUserIP() . "' ";
	$db->query($sql);
	
	//echo '申请充值成功';
	header("Location: /member/czorder.php?oid=".$kkk);
	
}

else if($act == 'point2_sell_confirm'){
	if(!$memberLogged){
		echo '您没有登录，请登录后再操作！';
		exit;
	}
	
	$rss = $db->get_one("select * from `h_point2_sell` where id = '{$id}'");
	if(!$rss){
		echo '未找到您指定的元信息';
		exit;
	}
	if($rss['h_userName'] != $memberLogged_userName){
		echo '抱歉，越权操作';
		exit;
	}
	if($rss['h_state'] != '等待卖家确认收款'){
		echo '抱歉，本订单状态，无法确认';
		exit;
	}
	
	//确认
	$sql = "update `h_point2_sell` set ";
	$sql .= "h_state = '交易完成' ";
	$sql .= ",h_confirmTime = '" . date('Y-m-d H:i:s') . "' ";
	$sql .= "where id = '{$id}'";
	$db->query($sql);
	
	$num = $rss['h_money'];
	
	//转币给买家
	$sql = "update `h_member` set ";
	$sql .= "h_point2 = h_point2 + {$num} ";
	$sql .= "where h_userName = '" . $rss['h_buyUserName'] . "' ";
	$db->query($sql);
	
	//记录
	$sql = "insert into `h_log_point2` set ";
	$sql .= "h_userName = '" . $rss['h_buyUserName'] . "', ";
	$sql .= "h_price = '" . $num . "', ";
	$sql .= "h_type = '元拍卖交易完成', ";
	$sql .= "h_about = '交易ID：" . $id . "', ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_actIP = '" . getUserIP() . "' ";
	$db->query($sql);
	
	echo '修改成功';
}
else if($act == 'point2_sell_quit'){
	if(!$memberLogged){
		echo '您没有登录，请登录后再操作！';
		exit;
	}
	
	$rss = $db->get_one("select * from `h_point2_sell` where id = '{$id}'");
	if(!$rss){
		echo '未找到您指定的元信息';
		exit;
	}
	if($rss['h_userName'] != $memberLogged_userName){
		echo '抱歉，越权操作';
		exit;
	}
	if($rss['h_state'] != '挂单中'){
		echo '抱歉，本订单状态，无法撤单';
		exit;
	}
	
	//放弃
	$sql = "update `h_point2_sell` set ";
	$sql .= "h_isDelete = 1 ";
	$sql .= ",h_state = '卖家放弃' ";
	$sql .= ",h_deleteTime = '" . date('Y-m-d H:i:s') . "' ";
	$sql .= "where id = '{$id}'";
	$db->query($sql);
	
	$num = $rss['h_money'];
	
	//还钱给卖家
	$sql = "update `h_member` set ";
	$sql .= "h_point2 = h_point2 + {$num} ";
	$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
	$db->query($sql);
	
	//记录还钱
	$sql = "insert into `h_log_point2` set ";
	$sql .= "h_userName = '" . $memberLogged_userName . "', ";
	$sql .= "h_price = '" . $num . "', ";
	$sql .= "h_type = '取消元购买', ";
	$sql .= "h_about = '您对发布的拍卖元进行撤单，交易ID：" . $id . "', ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_actIP = '" . getUserIP() . "' ";
	$db->query($sql);
	
	echo '修改成功';
}else if($act == 'pi_pwdII'){
	if(!$memberLogged){
		echo '您没有登录，请登录后再操作！';
		exit;
	}
	
	if(strlen($pwd) <= 0){
		echo '请输入当前安全密码';
		exit;
	}
	if($pwd2 != $pwd3){
		echo '您两次输入的新密码不一样，请重新输入';
		exit;
	}
	$pwd = md5($pwd);
	
	$rs = $db->get_one("select * from `h_member` where h_userName = '{$memberLogged_userName}'");
	if(!$rs){
		echo '未找到您的登录信息';
		exit;
	}
	if($rs['h_passWordII'] != $pwd){
		echo '当前安全密码错误';
		exit;
	}
	
	$pwd2 = md5($pwd2);
	
	//更新
	$sql = "update `h_member` set ";
	$sql .= "h_passWordII = '" . $pwd2 . "' ";
	$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
	$db->query($sql);
			
	echo '更新成功，请牢记新的安全密码';
}else if($act == 'pi_pwd'){
	if(!$memberLogged){
		echo '您没有登录，请登录后再操作！';
		exit;
	}
	
	if(strlen($pwd) <= 0){
		echo '请输入当前登录密码';
		exit;
	}
	if($pwd2 != $pwd3){
		echo '您两次输入的新密码不一样，请重新输入';
		exit;
	}
	$pwd = md5($pwd);
	
	$rs = $db->get_one("select * from `h_member` where h_userName = '{$memberLogged_userName}'");
	if(!$rs){
		echo '未找到您的登录信息';
		exit;
	}
	if($rs['h_passWord'] != $pwd){
		echo '当前登录密码错误';
		exit;
	}
	
	$pwd2 = md5($pwd2);
	
	//更新
	$sql = "update `h_member` set ";
	$sql .= "h_passWord = '" . $pwd2 . "' ";
	$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
	$db->query($sql);
			
	echo '更新成功，请牢记新的登录密码';
}else if($act == 'pi'){
	if(!$memberLogged){
		echo '您没有登录，请登录后再操作！';
		exit;
	}
	
	$rs = $db->get_one("select * from `h_member` where h_userName = '{$memberLogged_userName}'");
	if(!$rs){
		echo '未找到您的登录信息';
		exit;
	}
	
	//更新
	$sql = "update `h_member` set ";
	$sql .= "h_fullName = '" . $fullname . "' ";
	$sql .= ",h_weixin = '" . $weixin . "' ";
	/*$sql .= ",h_alipayUserName = '" . $alipayUserName . "' ";
	$sql .= ",h_alipayFullName = '" . $alipayFullName . "' ";
	$sql .= ",h_addrAddress = '" . $addrAddress . "' ";
	$sql .= ",h_addrPostcode = '" . $addrPostcode . "' ";
	$sql .= ",h_addrFullName = '" . $addrFullName . "' ";
	$sql .= ",h_addrTel = '" . $addrTel . "' ";*/
	$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
	$db->query($sql);
			
	echo '更新成功';
	
	
}else if($act == 'farm_shop_buy'){
  	if(!$memberLogged){
		echo '您没有登录，请登录后再操作！';
		exit;
	}
	
	if(strlen($goodsIds) <= 0){
		echo '您没有选择商品';
		exit;
	}
	if(strlen($goodsNums) <= 0){
		echo '商品数量错误';
		exit;
	}
	$goodsIdsArr = explode(',',$goodsIds);
	$goodsNumsArr = explode(',',$goodsNums);
	if(count($goodsIdsArr) != count($goodsNumsArr)){
		echo '拆解数据出错1';
		exit;
	}
	$goodsIN = array();
	for($ci = 0;$ci < count($goodsIdsArr);$ci++){
		$id = intval($goodsIdsArr[$ci]);
		$num = intval($goodsNumsArr[$ci]);
		if($id <= 0 || $num <= 0){
			echo '拆解数据出错2';
			exit;
		}
		$goodsIN[$id] = $num;
	}
	/*
	if(strlen($pwdII) <= 0){
		echo '请输入安全密码';
		exit;
	}
	$pwdII = md5($pwdII);
	*/

	$rs = $db->get_one("select * from `h_member` where h_userName = '{$memberLogged_userName}'");
	if(!$rs){
		echo '未找到您的登录信息';
		exit;
	}
	
	$parentUserName = trim($rs['h_parentUserName']);
	$first_buy = $rs['first_buy'] + 0;
	/*
	if($rs['h_point2'] < $num){
		echo '您的金币不足';
		exit;
	}
	if($rs['h_passWordII'] != $pwdII){
		echo '安全密码错误';
		exit;
	}
	*/
	
	//循环检测和购买
	$query = "select * from `h_farm_shop` where id in ({$goodsIds})";
	$result = $db->query($query);
	if($db->num_rows($result) <= 0){
		echo '未找到任何商品';
		exit;
	}
	$moneySum = 0;
	while($rs_list = $db->fetch_array($result)){
		//先遍历金额
		$moneySum += intval($rs_list['h_money']) * $goodsIN[$rs_list['id']];
		
		
		//判断总数量是否超出
		$rs1 = $db->get_one("select sum(h_num) as sumNum from `h_member_farm` where h_pid = '{$rs_list['id']}'");

		if($rs1){
			if(intval($rs1['sumNum'])  >= $rs_list['h_allMaxNum']){
				echo $rs_list['h_title'] , '总发行' , $rs_list['h_allMaxNum'] , '个，当前已售完！';
				exit;
			}
		}
		
		
		//判断总数量是否超出
		$rs1 = $db->get_one("select sum(h_num) as sumNum from `h_member_farm` where h_userName = '{$memberLogged_userName}' and h_pid = '{$rs_list['id']}'"); // and h_isEnd = 0
		if($rs1){
			if((intval($rs1['sumNum']) + $goodsIN[$rs_list['id']]) > $rs_list['h_allMaxNum']){
				echo $rs_list['h_title'] , '同时只能存在' , $rs_list['h_allMaxNum'] , '个，您当前最多只能购买' , ($rs_list['h_allMaxNum'] - intval($rs1['sumNum'])) , '个';
				exit;
			}
		}
		//判断今天购买是否超量
		//$rs1 = $db->get_one("select sum(h_num) as sumNum from `h_member_farm` where h_userName = '{$memberLogged_userName}' and h_pid = '{$rs_list['id']}' and h_isEnd = 0 and timestampdiff(day,h_addTime,sysdate()) = 0");
		$rs1 = $db->get_one("select sum(h_num) as sumNum from `h_member_farm` where h_userName = '{$memberLogged_userName}' and h_pid = '{$rs_list['id']}' and datediff(h_addTime,sysdate() + interval 8 hour) = 0"); // and h_isEnd = 0
		if($rs1){
			if((intval($rs1['sumNum']) + $goodsIN[$rs_list['id']]) > $rs_list['h_dayBuyMaxNum']){
				echo $rs_list['h_title'] , '每天最多只能购买' , $rs_list['h_dayBuyMaxNum'] , '个，您今天最多还能购买' , ($rs_list['h_dayBuyMaxNum'] - intval($rs1['sumNum'])) , '个';
				exit;
			}
		}
		//等级是否符合
		if($rs_list['h_minMemberLevel'] > $rs['h_level']){
			echo '您不符合' , $rs_list['h_title'] , '的购买条件';
			exit;
		}
	}
	//判断金额是否够
	if($moneySum > intval($rs['h_point2'])){
		echo '抱歉，您的余额(' , $rs['h_point2'] , ')不足以支付：' , $moneySum;
		exit;
	}
	//重新遍历并购买
	$moneyAll = 0;
	$db->move_first($result);
	while($rs_list = $db->fetch_array($result)){
		$num = intval($rs_list['h_money']) * $goodsIN[$rs_list['id']];
		
		$moneyAll += $num;
		
		//扣钱
		$sql = "update `h_member` set ";
		$sql .= "h_point2 = h_point2 - {$num} ";
		$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
		$db->query($sql);
		
		//记录扣钱
		$sql = "insert into `h_log_point2` set ";
		$sql .= "h_userName = '" . $memberLogged_userName . "', ";
		$sql .= "h_price = '-" . $num . "', ";
		$sql .= "h_type = '购买产品', ";
		$sql .= "h_about = '" . $rs_list['h_title'] . "数量：" . $goodsIN[$rs_list['id']] . "', ";
		$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
		$sql .= "h_actIP = '" . getUserIP() . "' ";
		$db->query($sql);
		
		//增加物品
		$sql = "insert into `h_member_farm` set ";
		$sql .= "h_userName = '" . $memberLogged_userName . "', ";
		$sql .= "h_pid = '" . $rs_list['id'] . "', ";
		$sql .= "h_num = '" . $goodsIN[$rs_list['id']] . "', ";
		$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
		$sql .= "h_endTime = '" . date('Y-m-d H:i:s',strtotime('+' . ($rs_list['h_life']) . ' day')) . "', ";
		$sql .= "h_lastSettleTime = NULL, ";
		$sql .= "h_settleLen = '0', ";
		$sql .= "h_isEnd = '0', ";
		$sql .= "h_title = '" . $rs_list['h_title'] . "', ";
		$sql .= "h_pic = '" . $rs_list['h_pic'] . "', ";
		$sql .= "h_point2Day = '" . $rs_list['h_point2Day'] . "', ";
		$sql .= "h_life = '" . $rs_list['h_life'] . "', ";
		$sql .= "cjfh = '" . $rs_list['cjfh'] . "' ,";
		$sql .= "h_money = '" . $rs_list['h_money'] . "' ";
		
		$db->query($sql);
		//echo $sql;
	}
	
	//系统消息
	$sql = "insert into `h_member_msg` set ";
	$sql .= "h_userName = '[系统消息]', ";
	$sql .= "h_toUserName = '" . $memberLogged_userName . "', ";
	$sql .= "h_info = '恭喜您购买物品成功，本次共消费{$moneyAll}金币', ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_actIP = '" . getUserIP() . "' ";
	$db->query($sql);	
    //发放直推奖，首先检测是否第一次购买
    settle_farm_ztj($memberLogged_userName,$moneyAll);


	//这里没这个奖
	/*if($parentUserName && $first_buy == 0 ){ //初次购买，给上家提成
	    $rs = $db->get_one("select * from `h_config` limit 1");
		$rate = $rs['h_point2ComBuy'] + 0;
		
		if($rate > 0){//给上家提成
			$num = $moneyAll*$rate/100;
			$sql = "update `h_member` set ";
			$sql .= "h_point2 = h_point2 + {$num} ";
			$sql .= "where h_userName = '" . $parentUserName . "' ";
			$result=$db->query($sql);
		
			
			//记录加钱
			$sql = "insert into `h_log_point2` set ";
			$sql .= "h_userName = '" . $parentUserName . "', ";
			$sql .= "h_price = '" . $num . "', ";
			$sql .= "h_type = '奖励', ";
			$sql .= "h_about = '玩家" . $memberLogged_userName . " 初次购物奖励', ";
			$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
			$sql .= "h_actIP = '" . getUserIP() . "' ";
			$db->query($sql);
			
			$sql = "update `h_member` set ";
			$sql .= "first_buy=1 ";
			$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
			$db->query($sql);
		}
	}*/
	//进行物品金币的结算，以及提成发放
        //settle_farm_day($memberLogged_userName);
	echo '恭喜您购买成功';
	
}else if($act == 'point2_transfer'){
	if(!$memberLogged){
		echo '您没有登录，请登录后再操作！';
		exit;
	}
	
	$num = intval($num);
	if($num <= 0){
		echo '至少需要转账数量1';
		exit;
	}
	if(strlen($username) <= 0){
		echo '请输入正确的玩家编号';
		exit;
	}
	if($memberLogged_userName == $username){
		echo '请不要转给自己';
		exit;
	}
	if(strlen($pwdII) <= 0){
		echo '请输入二级密码';
		exit;
	}
	$pwdII = md5($pwdII);
	
	$rs = $db->get_one("select * from `h_member` where h_userName = '{$username}'");
	if(!$rs){
		echo '您输入的玩家编号不存在';
		exit;
	}
	
	$rs = $db->get_one("select * from `h_member` where h_userName = '{$memberLogged_userName}'");
	if(!$rs){
		echo '未找到您的登录信息';
		exit;
	}
	if($rs['h_point2'] < $num){
		echo '您的元不足';
		exit;
	}
	if($rs['h_passWordII'] != $pwdII){
		echo '安全密码错误';
		exit;
	}
	
	//转入
	$sql = "update `h_member` set ";
	$sql .= "h_point2 = h_point2 + {$num} ";
	$sql .= "where h_userName = '" . $username . "' ";
	$db->query($sql);
	
	//扣钱
	$sql = "update `h_member` set ";
	$sql .= "h_point2 = h_point2 - {$num} ";
	$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
	$db->query($sql);
	
	//记录扣钱
	$sql = "insert into `h_log_point2` set ";
	$sql .= "h_userName = '" . $memberLogged_userName . "', ";
	$sql .= "h_price = '-" . $num . "', ";
	$sql .= "h_type = '元转账', ";
	$sql .= "h_about = '转出给玩家" . $username . "', ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_actIP = '" . getUserIP() . "' ";
	$db->query($sql);
	
	//记录加钱
	$sql = "insert into `h_log_point2` set ";
	$sql .= "h_userName = '" . $username . "', ";
	$sql .= "h_price = '" . $num . "', ";
	$sql .= "h_type = '元转账', ";
	$sql .= "h_about = '玩家" . $memberLogged_userName . "转入', ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_actIP = '" . getUserIP() . "' ";
	$db->query($sql);
	
	//系统消息
	$sql = "insert into `h_member_msg` set ";
	$sql .= "h_userName = '[系统消息]', ";
	$sql .= "h_toUserName = '" . $username . "', ";
	$sql .= "h_info = '玩家" . $memberLogged_userName . "转入{$num}元', ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_actIP = '" . getUserIP() . "' ";
	$db->query($sql);
	
	echo '转账成功';
}else if($act == 'point1_transfer'){
	if(!$memberLogged){
		echo '您没有登录，请登录后再操作！';
		exit;
	}
	
	$num = intval($num);
	if($num <= 0){
		echo '至少需要转账数量1';
		exit;
	}
	if(strlen($username) <= 0){
		echo '请输入正确的玩家编号';
		exit;
	}
	if($memberLogged_userName == $username){
		echo '请不要转给自己';
		exit;
	}
	if(strlen($pwdII) <= 0){
		echo '请输入安全密码';
		exit;
	}
	$pwdII = md5($pwdII);
	
	$rs = $db->get_one("select * from `h_member` where h_userName = '{$username}'");
	if(!$rs){
		echo '您输入的玩家编号不存在';
		exit;
	}
	
	$rs = $db->get_one("select * from `h_member` where h_userName = '{$memberLogged_userName}'");
	if(!$rs){
		echo '未找到您的登录信息';
		exit;
	}
	if($rs['h_point1'] < $num){
		echo '您的激活币不足';
		exit;
	}
	if($rs['h_passWordII'] != $pwdII){
		echo '安全密码错误';
		exit;
	}
	
	//转入
	$sql = "update `h_member` set ";
	$sql .= "h_point1 = h_point1 + {$num} ";
	$sql .= "where h_userName = '" . $username . "' ";
	$db->query($sql);
	
	//扣钱
	$sql = "update `h_member` set ";
	$sql .= "h_point1 = h_point1 - {$num} ";
	$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
	$db->query($sql);
	
	//记录扣钱
	$sql = "insert into `h_log_point1` set ";
	$sql .= "h_userName = '" . $memberLogged_userName . "', ";
	$sql .= "h_price = '-" . $num . "', ";
	$sql .= "h_type = '激活币转账', ";
	$sql .= "h_about = '转出给玩家" . $username . "', ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_actIP = '" . getUserIP() . "' ";
	$db->query($sql);
	
	//记录加钱
	$sql = "insert into `h_log_point1` set ";
	$sql .= "h_userName = '" . $username . "', ";
	$sql .= "h_price = '" . $num . "', ";
	$sql .= "h_type = '激活币转账', ";
	$sql .= "h_about = '玩家" . $memberLogged_userName . "转入', ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_actIP = '" . getUserIP() . "' ";
	$db->query($sql);
	
	//系统消息
	$sql = "insert into `h_member_msg` set ";
	$sql .= "h_userName = '[系统消息]', ";
	$sql .= "h_toUserName = '" . $username . "', ";
	$sql .= "h_info = '玩家" . $memberLogged_userName . "转入{$num}激活币', ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_actIP = '" . getUserIP() . "' ";
	$db->query($sql);
	
	echo '转账成功';
}else if($act == 'act_mer'){
	if(!$memberLogged){
		echo '您没有登录，请登录后再操作！';
		exit;
	}
	
	if(strlen($username) <= 0){
		echo '请输入正确的玩家编号';
		exit;
	}
	
	$rs = $db->get_one("select * from `h_member` where h_userName = '{$username}'");
	if(!$rs){
		echo '您输入的玩家编号不存在';
		exit;
	}
	if($rs['h_isPass']){
		echo '该玩家已经激活，不需要重复激活';
		exit;
	}
	
	$actParentMember = $rs['h_parentUserName'];
	
	$rs = $db->get_one("select * from `h_member` where h_userName = '{$memberLogged_userName}'");
	if(!$rs){
		echo '未找到您的登录信息';
		exit;
	}
	if($rs['h_point1'] < $webInfo['h_point1Member']){
		echo '您的激活币不足';
		exit;
	}
	
	//激活
	$sql = "update `h_member` set ";
	$sql .= "h_isPass = '1', ";
	$sql .= "h_point2 = '{$webInfo['h_point1MemberPoint2']}' ";
	$sql .= "where h_userName = '" . $username . "' ";
	$db->query($sql);
	
	//扣钱
	$sql = "update `h_member` set ";
	$sql .= "h_point1 = h_point1 - {$webInfo['h_point1Member']} ";
	$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
	$db->query($sql);
	
	//记录扣钱
	$sql = "insert into `h_log_point1` set ";
	$sql .= "h_userName = '" . $memberLogged_userName . "', ";
	$sql .= "h_price = '-" . $webInfo['h_point1Member'] . "', ";
	$sql .= "h_type = '激活玩家', ";
	$sql .= "h_about = '激活玩家" . $username . "', ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_actIP = '" . getUserIP() . "' ";
	$db->query($sql);
	
	//激活奖
	//bonus_member_act($username,$actParentMember);
	
	//检测其上家是否达到升级的条件
	$isUpdate = member_chk_update_level($actParentMember);
	
	echo '激活成功';
	
	//系统消息
	if($webInfo['h_point1MemberPoint2'] > 0){
		$sql = "insert into `h_member_msg` set ";
		$sql .= "h_userName = '[系统消息]', ";
		$sql .= "h_toUserName = '" . $username . "', ";
		$sql .= "h_info = '激活帐号，玩家" . $memberLogged_userName . "转入{$webInfo['h_point1MemberPoint2']}元', ";
		$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
		$sql .= "h_actIP = '" . getUserIP() . "' ";
		$db->query($sql);
	}
	
	if($isUpdate){
		if($memberLogged_userName == $actParentMember){
			echo '，您的会员等级已经提升了1级，请退出登录后重新登录查看！';
			
			//系统消息
			$sql = "insert into `h_member_msg` set ";
			$sql .= "h_userName = '[系统消息]', ";
			$sql .= "h_toUserName = '" . $memberLogged_userName . "', ";
			$sql .= "h_info = '恭喜，您的会员等级已经提升了1级！', ";
			$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
			$sql .= "h_actIP = '" . getUserIP() . "' ";
			$db->query($sql);			
		}
	}
}else if($act == 'chkun'){
	if(strlen($username) <= 0){
		echo '请输入正确的玩家编号';
		exit;
	}
	
	$rs = $db->get_one("select * from `h_member` where h_userName = '{$username}'");
	if(!$rs){
		echo '您输入的玩家编号不存在';
		exit;
	}
	
	echo '玩家姓名：' , strlen($rs['h_fullName']) <= 0 ?'（未填写）':$rs['h_fullName'] , '，激活状态：' , ($rs['h_isPass']?'√激活':'×未激活') , '，注册时间：' , $rs['h_regTime'], '，上级编号：' , $rs['h_parentUserName'];
}else if($act == 'lottery_click'){
	if(!$memberLogged){
		echo 'cjgo(0,0)';
		exit;
	}
	
	$rs = $db->get_one("select * from `h_member` where h_userName = '{$memberLogged_userName}'");
	if(!$rs){
		echo 'cjgo(0,0)';
		exit;
	}
	if($rs['h_jifen'] < $webInfo['h_point2Lottery']){
		echo 'cjgo(' , $rs['h_jifen'] , ',0)';
		exit;
	}
	
	//扣钱
	$sql = "update `h_member` set ";
	$sql .= "h_jifen = h_jifen - {$webInfo['h_point2Lottery']} ";
	$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
	$db->query($sql);
	
	//记录扣钱
	$sql = "insert into `h_log_point2` set ";
	$sql .= "h_userName = '" . $memberLogged_userName . "', ";
	$sql .= "h_price = '-" . $webInfo['h_point2Lottery'] . "', ";
	$sql .= "h_type = '大转盘抽奖', ";
	$sql .= "h_about = '参加抽奖活动', ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_actIP = '" . getUserIP() . "' ";
	$db->query($sql);
	
	//1 = 恭喜您抽中一等奖 iphone6 plus 手机一部,系统已自动填写订单,请到您的商城订单里查看
	//2 = 恭喜您抽中二等奖 TCL么么哒3S 手机一部,系统已自动填写订单,请到您的商城订单里查看
	//3 = 恭喜您抽中三等奖 男款毛衫 一件,系统已自动填写订单,请到您的商城订单里查看
	//4 = 恭喜您抽中四等奖 20元,系统已经转入您的账户中
	//5 = 恭喜您抽中五等奖 8元,系统已经转入您的账户中
	//6 = 再接再厉,大奖等着你
	$prizeConfig = array($webInfo['h_lottery1']
						,$webInfo['h_lottery2']
						,$webInfo['h_lottery3']
						,$webInfo['h_lottery4']
						,$webInfo['h_lottery5']
						,$webInfo['h_lottery6']);
	
	$rnd = mt_rand(1,10000);
	$pb = 0;
	$winIndex = -1;
	for($ci = 0;$ci < count($prizeConfig);$ci++){
		$pb += $prizeConfig[$ci];
		if($pb >= $rnd){
			//中了
			$winIndex = $ci;
			break;
		}
	}
	//修正 为最后一个，即未中奖
	if($winIndex == -1){
		$winIndex = count($prizeConfig) - 1;
	}
	
	switch($winIndex){
		case 0:
			//188.88元
			//加钱
			$sql = "update `h_member` set ";
			$sql .= "h_point2 = h_point2 + 188.88 ";
			$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
			$db->query($sql);
			
			//记录加钱
			$sql = "insert into `h_log_point2` set ";
			$sql .= "h_userName = '" . $memberLogged_userName . "', ";
			$sql .= "h_price = '188.88', ";
			$sql .= "h_type = '大转盘抽奖', ";
			$sql .= "h_about = '喜中特等奖 188.88元', ";
			$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
			$sql .= "h_actIP = '" . getUserIP() . "' ";
			$db->query($sql);
			break;
		case 1:
			//58.88元
			//加钱
			$sql = "update `h_member` set ";
			$sql .= "h_point2 = h_point2 + 58.88 ";
			$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
			$db->query($sql);
			
			//记录加钱
			$sql = "insert into `h_log_point2` set ";
			$sql .= "h_userName = '" . $memberLogged_userName . "', ";
			$sql .= "h_price = '58.88', ";
			$sql .= "h_type = '大转盘抽奖', ";
			$sql .= "h_about = '喜中二等奖 58.88元', ";
			$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
			$sql .= "h_actIP = '" . getUserIP() . "' ";
			$db->query($sql);
			break;
		case 2:
			//28.88元
			//加钱
			$sql = "update `h_member` set ";
			$sql .= "h_point2 = h_point2 + 28.88 ";
			$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
			$db->query($sql);
			
			//记录加钱
			$sql = "insert into `h_log_point2` set ";
			$sql .= "h_userName = '" . $memberLogged_userName . "', ";
			$sql .= "h_price = '28.88', ";
			$sql .= "h_type = '大转盘抽奖', ";
			$sql .= "h_about = '喜中三等奖 28.88元', ";
			$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
			$sql .= "h_actIP = '" . getUserIP() . "' ";
			$db->query($sql);
			break;
		case 3:
						//28.88元
			//加钱
			$sql = "update `h_member` set ";
			$sql .= "h_point2 = h_point2 + 28.88 ";
			$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
			$db->query($sql);
			
			//记录加钱
			$sql = "insert into `h_log_point2` set ";
			$sql .= "h_userName = '" . $memberLogged_userName . "', ";
			$sql .= "h_price = '28.88', ";
			$sql .= "h_type = '大转盘抽奖', ";
			$sql .= "h_about = '喜中四等奖 28.88元', ";
			$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
			$sql .= "h_actIP = '" . getUserIP() . "' ";
			$db->query($sql);
			break;
		case 4:
			//18.88元
			//加钱
			$sql = "update `h_member` set ";
			$sql .= "h_point2 = h_point2 + 18.88 ";
			$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
			$db->query($sql);
			
			//记录加钱
			$sql = "insert into `h_log_point2` set ";
			$sql .= "h_userName = '" . $memberLogged_userName . "', ";
			$sql .= "h_price = '18.88', ";
			$sql .= "h_type = '大转盘抽奖', ";
			$sql .= "h_about = '喜中五等奖 18.88元', ";
			$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
			$sql .= "h_actIP = '" . getUserIP() . "' ";
			$db->query($sql);
			break;
		case 5:
			//12.88元
			//加钱
			$sql = "update `h_member` set ";
			$sql .= "h_point2 = h_point2 + 12.88 ";
			$sql .= "where h_userName = '" . $memberLogged_userName . "' ";
			$db->query($sql);
			
			//记录加钱
			$sql = "insert into `h_log_point2` set ";
			$sql .= "h_userName = '" . $memberLogged_userName . "', ";
			$sql .= "h_price = '12.88', ";
			$sql .= "h_type = '大转盘抽奖', ";
			$sql .= "h_about = '喜中参与奖 12.88元', ";
			$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
			$sql .= "h_actIP = '" . getUserIP() . "' ";
			$db->query($sql);
			break;
	}
	
	echo 'cjgo(' , ($rs['h_jifen'] - $webInfo['h_point2Lottery']) , ',' , ($winIndex + 1) , ')';
}else if($act == 'lottery_win_list'){
	echo '中奖名单:<br>';
	
	$query = "select * from `h_log_point2` where h_type = '大转盘抽奖' and h_price >= 0 order by h_addTime desc,id desc LIMIT 10";
	$result = $db->query($query);
	$ci = 0;
	while($rs_list = $db->fetch_array($result)){
		echo '玩家:' , $rs_list['h_userName'] , ' ' , $rs_list['h_about'] , ' ' , $rs_list['h_addTime'] , '<br>';
	}
}else if($act == 'point2_sell_list'){
	$query = "select * from `h_point2_sell` where h_state = '挂单中' order by h_addTime asc,id asc";
	$result = $db->query($query);
	$ci = 0;
	echo '<table class="table table-striped table-hover"><tr><td>单号ID</td><td>挂单金额</td><td>挂单时间</td><td>操作</td></tr>';
	while($rs_list = $db->fetch_array($result)){
		echo '<tr><td>' , $rs_list['id'] , '</td><td>' , $rs_list['h_money'] , '</td><td>' , $rs_list['h_addTime'] , '</td><td><button type="button" class="btn btn-danger btn-xs" onClick="jinbi_qianggou(' , $rs_list['id'] , ')">我要抢购</button></td></tr>';
	}
	echo '</table>';
}else if($act == 'rr'){
	if($memberLogged){
		echo '[';
                $id=isset($id)?$id:"";
		if(strlen($id) <= 0){
			echo '{';
			echo 'id:"' , $memberLogged_userName , '"';
			echo ', pId:"' . $memberLogged_userName . '"';
			echo ', name:"[0] ' , $memberLogged_userName , ' [' , ($memberLogged_isPass?'√激活':'×未激活') , '] "';
			echo ', isParent:true';
			echo ', icon:"/ui/zTree_v3/css/zTreeStyle/img/diy/1_open.png"';
			echo '}';
                        $lv=-1; 
		}else{
			$query = "select *,(select count(id) from `h_member` where h_parentUserName = a.h_userName) as comMembers from `h_member` a where h_parentUserName = '{$id}' order by h_regTime asc,id asc";
			$result = $db->query($query);
			$ci = 0;
			while($rs_list = $db->fetch_array($result)){
				$ci++;
				if($ci > 1){
					echo ',';
				}
				
				echo '{';
				echo 'id:"' , $rs_list['h_userName'] , '"';
				echo ', pId:"' , $memberLogged_userName , '"';
				echo ', name:"[' , ($lv + 1) , '] ' , $rs_list['h_userName'] , ' [' , ($rs_list['h_isPass']?'√激活':'×未激活') , '] [' , get_member_level_name($rs_list['h_level']) , ']"';
				if($rs_list['comMembers'] > 0){
					echo ', isParent:true';
				}else{
					echo ', isParent:false';
				}
				echo ', icon:"/ui/zTree_v3/css/zTreeStyle/img/diy/1_open.png"';
				echo '}';
			}
	        }

		echo ']';
	}else{
		echo '[]';
	}
}else if($act == 'reg'){
	//act=reg&x1=15988888888&x2=11111111111&x3=111111&x4=111111&x8=111111&x9=111111&x10=1475&callback=jQuery1113005947103416005339_1454217007416&_=1454217007417
	//play('0x11')
	if(strlen($comMember) != 11){
		echo '{"state":false,"msg":"推荐人帐号错误，请检查！"}';
		exit;
	}
	if(strlen($username) != 11){
		echo '{"state":false,"msg":"玩家编号错误，请检查！"}';
		exit;
	}
	if(strlen($pwd) < 6 || strlen($pwd) > 32){
		echo '{"state":false,"msg":"登录密码6-32位任意字符，请检查！"}';
		exit;
	}
	if($pwd != $pwd2){
		echo '{"state":false,"msg":"两次输入的登录密码不一致，请检查！"}';
		exit;
	}
	if(strlen($pwdII) < 6 || strlen($pwdII) > 32){
		echo '{"state":false,"msg":"资金密码6-32位任意字符，请检查！"}';
		exit;
	}
	if($pwdII != $pwdII2){
		echo '{"state":false,"msg":"两次输入的资金密码不一致，请检查！"}';
		exit;
	}

	session_start();
        /* comment out due to uslessness */
	/*if($vCode != $_SESSION['code']){
		//echo '{"state":false,"msg":"验证码错误，请检查！"}';
		//exit;
	}*/
	
	$rs = $db->get_one("select * from `h_member` where h_userName = '{$comMember}'");
	if(!$rs){
		echo '{"state":false,"msg":"您填写的推荐人帐号并不存在，请检查！"}';
		exit;
	}
	
	$rs = $db->get_one("select * from `h_member` where h_userName = '{$username}'");
	if($rs){
		echo '{"state":false,"msg":"您填写的会员帐号（手机号码）已经存在，请换一个！"}';
		exit;
	}

    $ucip = getUserIP();
	$rs = $db->get_one("select * from `h_member` where h_regIP = '{$ucip}'");
	if($rs){
		//echo '{"state":false,"msg":"一个IP只能注册一次！"}';
		//exit;
	}
		
	$pwd = md5($pwd);
	$pwdII = md5($pwdII);
	
	//写入..
	$sql = "insert into `h_member` set ";
	$sql .= "h_parentUserName = '" . $comMember . "', ";
	$sql .= "h_userName = '" . $username . "', ";
	$sql .= "h_passWord = '" . $pwd . "', ";
	$sql .= "h_passWordII = '" . $pwdII . "', ";
	$sql .= "h_level = '4', ";
	$sql .= "h_isPass = '1', ";
	$sql .= "h_regTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_regIP = '" . getUserIP() . "' ";
	$db->query($sql);
	
	//如果有注册奖励，进行奖励
	bonus_member_reg($username,$comMember);
	
	echo '{"state":true,"msg":"您的账户已经注册成功,请牢记您的[玩家编号]和[密码]"}';
	
}else if($act == 'login'){
	//act=dl&x1=11111111111&x2=111111&x4=true&callback=jQuery111306571672858652009_1454218265556&_=1454218265557
	//cjgo(0,0)
	//play('0x25')
        try {
	if(strlen($username) != 11){
		echo '{"state":false,"msg":"玩家编号错误，请检查！"}';
		exit;
	}
	if(strlen($pwd) < 6 || strlen($pwd) > 32){
		echo '{"state":false,"msg":"登录密码6-32位任意字符，请检查！" . $pwd}';
		exit;
	}
	
	$rs = $db->get_one("select * from h_member where h_userName = '{$username}'");
	if(!$rs){
		echo '{"state":false,"msg":"账户或密码错误，请检查！"}';
		exit;
	}
	
	$pwdhash = md5($pwd);
	if($pwdhash != $rs['h_passWord']){
		echo '{"state":false,"msg":"账户或密码错误，请检查! pwdhash:' . $pwdhash . '  dbhash:  ' . $rs['h_passWord'] . '" }';
		exit;
	}
	
	if(!$rs['h_isPass']){
		echo '{"state":false,"msg":"您的账户未激活，请联系上家激活再登录！"}';
		exit;
	}
	
	if($rs['h_isLock']){
		echo '{"state":false,"msg":"账户被限制登录！"}';
		exit;
	}
	// if($rs){
		// echo {"state":"1"};
	// }
	// xiu gai
	$expire = time() + 60 * 30;
	setcookie("m_username", $rs['h_userName'],$expire,'/');
	setcookie("m_password", $rs['h_passWord'],$expire,'/');
	setcookie("m_fullname", $rs['h_fullName'],NULL,'/');
	setcookie("m_level", $rs['h_level'],NULL,'/');
	setcookie("m_isPass", $rs['h_isPass'],NULL,'/');
	setcookie("m_userId", $rs['id'], NULL, '/');
	
	$sql = "update `h_member` set ";
	$sql .= "h_lastTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_lastIP = '" . getUserIP() . "', ";
	$sql .= "h_logins = h_logins + 1 ";
	$sql .= "where h_userName = '" . $rs['h_userName'] . "' ";
	$db->query($sql);
	
	$sql = "insert into `t_log_login_member` set ";
	$sql .= "h_userName = '" . $rs['h_userName'] . "', ";
	$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
	$sql .= "h_ip = '" . getUserIP() . "' ";
	$db->query($sql);
	
	//登录成功了，进行物品元的结算，以及提成发放

	
	echo '{"state":true,"msg":"登录成功"}';
        } catch (Throwable $e) {
           echo '{"state":false,"msg":"' . $e->getMessage() . '"}';
        }
}
