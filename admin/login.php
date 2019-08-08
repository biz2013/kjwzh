<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/conn.php';
?>
<HTML>
<HEAD>
<TITLE>登录</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
<style type="text/css">
body,td,th {font-size: 12px;}
body {margin-left: 0px;margin-top: 0px;margin-right: 0px;margin-bottom: 0px;}
.txt {color: #02439F;font-weight: bold;}
.input1 {border:1px solid #3E73B7;height:20px;width:115px;}
.input2 {border:1px solid #3E73B7;height:20px;width:102px;}
form {margin:0px;}
body{
	background: url(../ui/images/bg_admin.png);
	background-size: 100% 100%;
	background-repeat: no-repeat;
}
</style>
<script language="javascript" src="../js/function.js"></script>
</HEAD>
<BODY>
<div class="wxh-container">
<?php
$clause = array_key_exists("clause", $_GET)? $_GET['clause'] : "";
if($clause == "chklogin")
{
	session_start();
	if($code != $_SESSION['code']){hintAndBack('验证码错误!',1);}
	if('' == $loginName){hintAndBack('请输入帐号!',1);}
	if('' == $loginPwd){hintAndBack('请输入密码!',1);}
	
	$md5Pwd = md5($loginPwd);
	$rs = $db->get_one("SELECT * FROM `h_admin` where h_userName = '$loginName' and h_passWord = '$md5Pwd'");
	if(!$rs)
	{
		hintAndBack('帐号或密码错误!',1);
	}
	else
	{
		if($rs['h_isPass'] == 0)
		{
			hintAndBack('您的帐号为锁定状态，不允许登录!',1);
		}
		else
		{
			session_start();
			$expire = time() + 60 * 30;
			setcookie("h_userName", $loginName, $expire,'/');
			setcookie("h_passWord", $md5Pwd, $expire,'/');
			
			turnToPage("index.php");
		}
	}
	
	exit();
}

footer();
?>
<br><br><br><br>
<form method="post" action="?clause=chklogin" onSubmit="return chkAdminLoginForm()">
	  <TABLE WIDTH=662 BORDER=0 align="center" CELLPADDING=0 CELLSPACING=0>
			<TR>
				<TD ROWSPAN=3>
					<IMG SRC="../images/login/weboa_login_1.gif" WIDTH=191 HEIGHT=381 ALT=""></TD>
				<TD>
					<IMG SRC="../images/login/weboa_login_2.gif" WIDTH=443 HEIGHT=269 ALT=""></TD>
				<TD ROWSPAN=3>
					<IMG SRC="../images/login/weboa_login_3.gif" WIDTH=28 HEIGHT=381 ALT=""></TD>
			</TR>
			<TR>
				<TD width="443" height="73" align="center" bgcolor="#FFFFFF"><table width="436" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td height="28"><span class="txt">帐号：</span>
                      <input type="text" name="loginName" id="loginName" class="input1" maxlength="25" tabindex="1">
                      &nbsp;&nbsp;<span class="txt">密码：</span>
                    <input type="password" name="loginPwd" id="loginPwd" class="input1" maxlength="25" tabindex="2">
					<br></td>
                    <td width="90" rowspan="2"><input type="image" name="imageField" src="../images/login/loginjoin.gif"></td>
                  </tr>
                  <tr>
                    <td height="28"><span class="txt">验证码：</span>
                      <input type="text" name="code" class="input2" maxlength="4" tabindex="3">
					  &nbsp;&nbsp;<img src="../include/getCode.php" height="20" align="absmiddle" style="cursor:pointer" onClick="this.src=this.src+'?'+Math.random();"></td>
                  </tr>
                </table>
			    </TD>
			</TR>
			<TR>
				<TD>
					<IMG SRC="../images/login/weboa_login_5.gif" WIDTH=443 HEIGHT=39 ALT=""></TD>
			</TR>
	  </TABLE>
</form>
<script language="javascript">
	$$$("loginName").focus();
</script>
<script src="http://apps.bdimg.com/libs/jquery/1.6.4/jquery.min.js"></script>
<script>
$(window).ready(function(){
	$(".wxh-container").height($(window).height());
	$(".wxh-form").css({"margin-top":($(window).height()-$(".wxh-form").height())/2+"px"});
})
</script>
</div>
</body>
</HTML>
