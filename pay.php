<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/conn.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/webConfig.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/pay/pay.php';

$ccc = isset($_REQUEST['ccc'])?$_REQUEST['ccc']:0;
if($ccc==0){
	exit('<script language="javascript">alert("请填写支付金额");window.history.back(-1);</script>');
}

$total_fee = $ccc*100;

$pay = new pay();
$out_trade_no = date('YmdHis').rand(100000,999999);
$subject = 'chongzhi';
$config['notify_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/notify.php';
$config['return_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/return.php';
$config['out_trade_no'] = $out_trade_no;
$config['subject'] = $subject;
$config['total_fee'] = $total_fee;
$config['attach'] = 'username='.$_COOKIE['m_username'];

		//记录充值记录
		$sql = "insert into `order` set ";
		$sql .= "username = '" . $_COOKIE['m_username'] . "', ";
		$sql .= "out_trade_no = '{$out_trade_no}', ";
		$sql .= "subject = '{$subject}', ";
		$sql .= "total_fee = " . $ccc . ", ";
		$sql .= "submit_time = '" . date('Y-m-d H:i:s') . "', ";
		$sql .= "ip = '" . getUserIP() . "' ";
		//echo $sql;
		$db->query($sql);
		
$data  = $pay->applypurchase($config);
//VAR_DUMP($data);
if($data['return_code']=='FAILED'){
	exit('<script language="javascript">alert("'.$data['return_msg'].'");window.history.back(-1);</script>');
}elseif($data['return_code']=='SUCCESS'){
	header('Location:'.$data['payment_url']);
}
?>