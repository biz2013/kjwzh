<?php
$LoginEdUserName = isset($_COOKIE['h_userName']) ? $_COOKIE['h_userName'] : "";
$LoginEdPassWord = isset($_COOKIE['h_passWord']) ? $_COOKIE['h_passWord'] : "";
$memberLogged_userName = isset($_COOKIE['m_username'])?$_COOKIE['m_username']:'';
//验证是否登录
if(strlen($LoginEdUserName)>0 && strlen($LoginEdPassWord)>0)
{
	error_log("admin pass login, expand session");
	$expire = time() + 60 * 30;
	setcookie("h_userName", $LoginEdUserName, $expire,'/');
	setcookie("h_passWord", $LoginEdPassWord, $expire,'/');

	$sql = "select * from `h_admin` where h_userName = '{$LoginEdUserName}' LIMIT 1";
	$rsAdmin = $db->get_one($sql);
}
else
{
	echo '<html><head><title>登录提示</title><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>';
	HintAndTurnTopFrame("您未登录或登录超时，请您重新登录！\\n\\n安全起见，您若20分钟未操作后台，将自动超时！","login.php");
	echo '</body></html>';
	exit();
}

$qxArr = array(
	'网站配置'=>array(
		'基本配置'=>'config.php',
		
		'滚动公告'=>'service.php',
		'推荐会员提成配置'=>'config_com.php',
		'提现配置'=>'config_withdraw.php',
		'理财套餐设置'=>'farm.php',
		'玩家公告'=>'news.php?location=' . urlencode('网站主栏目') . '&mid=108',
	),
	'会员管理'=>array(
		'会员列表'=>'member.php',
		'会员物品列表'=>'cw.php',
		'推荐结构'=>'rr.php',
		'商城商品管理'=>'goods.php',
		'商城订单列表'=>'order.php',
		'会员登录记录'=>'log1.php',
	),
	'资金流水'=>array(
	    '加减资金'=>'point2add.php',
		'资金流水明细'=>'log3.php',
		'加减积分'=>'point3add.php',
		
		
	),
	'会员充值'=>array(
		'充值管理'=>'cz.php',
	),
	
	'玩家提现'=>array(
		'提现管理'=>'tk.php',
	),
	'消息管理'=>array(
		'会员消息列表'=>'log4.php',
		'发送消息给会员'=>'msg_send.php',
		'收到的会员消息'=>'log4.php?stype=' . urlencode('管理员收件'),
	),
	'工具'=>array(
		'清空数据'=>'tool_clear.php',
		'调整时间'=>'tool_time.php',
	),
	'管理员帐号'=>array(
		'帐号管理'=>'admin.php',
	),
);
