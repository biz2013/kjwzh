<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/conn.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/webConfig.php';
//require_once $_SERVER['DOCUMENT_ROOT'] . '/member/logged_data.php';
isset($pageTitle) or $pageTitle ='';

function login($db, $username, $pwd, $userIP) {
	
	if (strlen($username) != 11){
		return '{"state": false,"msg":"玩家编号错误，请检查！"}';
	}
	if (strlen($pwd) < 6 || strlen($pwd) > 32){
		return '{"state":false,"msg":"登录密码6-32位任意字符，请检查！"}';
	}
	
	$rs = $db->get_one("select * from h_member where h_userName = '{$username}'");
	if (!$rs){
		return '{"state":false,"msg":"用户不存在，请检查！"}';
	}

	$pwdhash = md5($pwd);
	if($pwdhash != $rs['h_passWord']){
		return '{"state":false,"msg":"账户或密码错误，请检查!" }';
	}

	if(!$rs['h_isPass']){
		return '{"state":false,"msg":"您的账户未激活，请联系上家激活再登录！"}';
	}

	if($rs['h_isLock']){
		return '{"state":false,"msg":"账户被限制登录！"}';
	}

	try {
		$expire = time() + 60 * 30;
		setcookie("m_username", $rs['h_userName'],$expire,'/');
		setcookie("m_password", $rs['h_passWord'],$expire,'/');
		setcookie("m_fullname", $rs['h_fullName'],NULL,'/');
		setcookie("m_level", $rs['h_level'],NULL,'/');
		setcookie("m_isPass", $rs['h_isPass'],NULL,'/');
		setcookie("m_userId", $rs['id'], NULL, '/');

		$sql = "update `h_member` set ";
		$sql .= "h_lastTime = '" . date('Y-m-d H:i:s') . "', ";
		$sql .= "h_lastIP = '" . $userIP . "', ";
		$sql .= "h_logins = h_logins + 1 ";
		$sql .= "where h_userName = '" . $rs['h_userName'] . "' ";
		error_log("Login update: " . $sql);
		$db->query($sql);

		$sql = "insert into `t_log_login_member` set ";
		$sql .= "h_userName = '" . $rs['h_userName'] . "', ";
		$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
		$sql .= "h_ip = '" . $userIP . "' ";
		error_log("Login logging: " . $sql);
		$db->query($sql);

		return '{"state":true,"msg":"登录成功"}';
	} catch (Throwable $e) {
		return '{"state":false,"msg":"' . $e->getMessage() . '"}';
	}
}


$error_msg = "";
$status = FALSE;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$username = $_POST['username'];
	$password = $_POST['password'];
	$userIP = getUserIP();
    error_log("Login attemp by " . $username . " from " . $userIP);
	$result_string = login($db, $username, $password, $userIP);
	error_log("Login return " . $result_string);
	$result = json_decode($result_string);
	$error_msg = (isset($result) && !$result->state) ? $result->msg : "";
	$status =  (isset($result)) ? ($result->state == 1) : FALSE; 
	error_log("the state is " . $status . " msg " . $error_msg . " result state " . $result->state . " gettype " . gettype($status));
}


?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $pageTitle . $webInfo['h_webName'] . ' - ' . '会员登陆'; ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
<link href="/res/css/home.css" rel="stylesheet" type="text/css" media="all" />
<link href="/res/css/css.css" rel="stylesheet" type="text/css" media="all" />

<script type="text/javascript" src="/ui/js/jquery.min.js"></script>
<script type="text/javascript" src="/ui/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/ui/js/bootstrap-confirmation.min.js"></script>
<script type="text/javascript" src="/ui/js/jquery.backstretch.min.js"></script>
<script type="text/javascript" src="/ui/js/long.js"></script>
<script type="text/javascript" src="/ui/layer/layer.js"></script>

</head>

<body style="background:#fff;">

<!--top-->

<div class="top">
	<div class="box">
        <a href="/" class="return"><img src="/res/picture/return.png"></a>
        登录    
    </div>
</div>



<img src="/images/logo2.png" class="lo_logo">

<div class="login_lo">
<form name="id_logi_form" id="id_login_form" class="form-horizontal" action="/member/login.php" method="post" >
   <input style="display:none;" type="checkbox" id="remember" value="1" checked='CHECKED'>
	<div class="box">
    	<div class="lo_1">
        	<img src="/res/picture/yong.png">
            <input type="text" name="username" id="username" size="60" maxlength="60" style="color:#ccc" value="请输入手机号" onfocus="if(this.value=='请输入手机号'){this.value=''};this.style.color='black';" onblur="if(this.value==''||this.value=='请输入手机号'){this.value='请输入手机号';this.style.color='#ccc';}">
        </div>
        <div class="lo_1">
        	<img src="/res/picture/mi.png">
            <input type="password" name="password" id="pwd"  size="60" maxlength="60" style="color:#ccc" value="请输入密码" onfocus="if(this.value=='请输入密码'){this.value=''};this.style.color='black';" onblur="if(this.value==''||this.value=='请输入密码'){this.value='请输入密码';this.style.color='#ccc';}">
        </div>
        <a href="javascript:;" class="lo_login denglugo">登录</a>
    </div>
</form>
</div>

<script>
    $(document).ready(function(){
    	$(".denglugo").click(function () {
			denglu_go();
			return false;
		});
		function denglu_go(){
			var username=$("#username").val();
			var pwd=$("#pwd").val();
			//var x3=$("#x3").val();
			var remember=$("#remember").prop('checked');
			if(username==""){
				tishi4('请输入您的玩家编号','#username')
				return false;
				}
			if(!checkMobile(username)){
				tishi4('玩家编号应该是手机号码形式的11位数字','#username')
				return false;
				}
			if(pwd==""){
				tishi4('请输入您的密码','#pwd')
				return false;
			}

			$("#id_login_form").submit();
		}
		<?php if ($_SERVER['REQUEST_METHOD'] == 'POST') :?>
		var error_msg = "<?php echo $error_msg; ?>";
		var result_status = <?php if ($status) :?>true<?php else :?>false<?php endif; ?>;
		if (!result_status) {
			layer.alert(error_msg);
		} else {
			window.location.href = '/member/index.php';
		}
		<?php endif; ?>
	});
</script>
</body>
</html>
