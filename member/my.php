<!DOCTYPE html>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/conn.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/webConfig.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/member/inc_header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/member/current_user_info.php';
?>
<html>
	<head>
    <meta charset="utf-8">
    <title>个人中心</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="format-detection" content="telephone=no">
	<link href="/zjmcss/style.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../css/style.css">
	<link href="/zjmcss/global.css" rel="stylesheet" type="text/css">
<link href="/zjmcss/amazeui.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="../css/main_response.css">
<link rel="stylesheet" href="../css/head.css">
<link rel="stylesheet" href="../css/foot.css">
<link rel="stylesheet" href="../css/iconfont/iconfont.css">
<link rel="stylesheet" href="../css/font-awesome-4.7.0/css/font-awesome.min.css">
<link type="text/css" rel="stylesheet" href="../js/layer/skin/layer.css">
<link type="text/css" rel="stylesheet" href="../css/gboy.css">
<script type="text/javascript" src="../js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="../js/jquery.SuperSlide.2.1.1.js"></script>
<script type="text/javascript" src="../js/jquery.touchSlider.js"></script>   
<script type="text/javascript" src="../js/mui.min.js"></script>
<script type="text/javascript" src="../js/layer.min.js"></script><link style="" id="layui_layer_skinlayercss" href="http://../js/layer/skin/layer.css" rel="stylesheet">
<script type="text/javascript" src="../js/gboy.js"></script>
	<script src="../js/ajaxfileupload.js" type="text/javascript"></script>
<style>
.header {background: #29a7e2;}
.mian_qd{background:#fff;}
.wallet{margin:0 auto;text-align:center;}
.wallet p{padding-top:10px;}
.wallet p img{margin:0px auto;}
li img{display:inherit;}
.mian_zxr p a {background: #29a7e2;}
.mian_qd ul li a{width:25%;padding-top:15px;}
.mian_qd ul li a p{padding-top:10%;}
.mian_qd ul li:last-child a{border-right:none;}
</style>
	<script>
	      function uploadFile() {  
			$.ajaxFileUpload({
					url:'/index.php?m=member&c=index&a=face',
					secureuri:false ,
					fileElementId:'face',
					dataType: 'text',
					success: function (data,status)  
					{
						var data = $.parseJSON(data);
						
						 if(data.status=='0'){
							 
							 //alert('头像上传失败');
							 gboy.msg(data.message,'',0);
						 }else{
						 
						   $('#face_url').attr('src',data.result+'?+Math.random();');
						   //location.href='/index.php?m=member&c=index&a=index';
							 
						 }
					  
					},
					error: function (data, status, e)
					{
						alert(e);
					}
				});
			return false;

		}  
	</script>

    </head>
	<body>
<div class="header">
    <div class="header_l">
      <div align="left"></div>
    </div>
    <div class="header_c"> <h1>个人中心</h1></div>
    <!-- <span><a href=""><img src="" alt=""></a></span> -->
</div>

<div class="wallet">
    <p align="center">
		<input name="face" id="face" style="display:none;" onChange="uploadFile()" type="file">
		<img src="/ui/touxiang.png" align="center" alt="" name="face_url" width="200" height="200" id="face_url">
	</p>
   <li>
     <div align="center" style="padding-top:8px;font-size:16px;color:#ff0000;"> 可提余额<?php echo $current_user_info['h_point2'];?>元</br></div>
   </li> 
   </br>
<p><a href="/" onClick="ewm();" style="font-size:16px;color:#fff;"> 退出登录 </a></p>
</div>
<style>

.winBox {
	height:40px;
	overflow:hidden;
	position:relative;
}
.scroll {
	width:100%;
	position:absolute;
	left:0px;
	top:0px;
}
.scroll li {
	width:100%;
	float:left;
	line-height:40px;
	text-align:center;
}
</style>
<div class="mian_zx">
<div class="box">
  <div class="winBox">
    <ul class="scroll">
		<li><h3><?php echo $webInfo['h_serviceQQ']; ?></h3></li>
		<li><h3><?php echo $webInfo['h_serviceQQ']; ?></h3></li>
		<li><h3><?php echo $webInfo['h_serviceQQ']; ?></h3></li>
		<li><h3><?php echo $webInfo['h_serviceQQ']; ?></h3></li>
		<li><h3><?php echo $webInfo['h_serviceQQ']; ?></h3></li>
		<li><h3><?php echo $webInfo['h_serviceQQ']; ?></h3></li>
    </ul>
  </div>
</div>


    <div class="mian_zxr">
        <p><a href="/member/jintix.php">提现</a></p>
    </div>
            
</div>
<div class="mian_zxr">
        <p><a href="/member/jincz.php">充值</a></p>
</div>
<div class="mian_qd">

    <ul>
        <!--li>
            <a href="/index.php?m=member&amp;c=order&amp;a=play"></a> </li-->
        <li><a href="/member/point2_withdraw_log.php" class="bordertop">
		<i class="iconfont">
                <img src="/member/mod/tx.png" width="30" height="30"></i>
                <p>提现记录</p>
          </a>      </li>
        <li>
            <a href="/member/jl.php" class="bordertop">
                <i class="iconfont"><img src="/member/mod/jy.png" width="30" height="30"></i>
                <p>交易记录</p>
          </a>      </li>
		          <li>
            <a href="/member/my_farm.php" class="bordertop">
                <i class="iconfont"><img src="/member/mod/yg.png" width="30" height="30"></i>
                <p>已购理财</p>
      </a>      </li>
      
       <li>
            <a href="/member/cz.php" class="bordertop">
                <i class="iconfont"><img src="/member/mod/jy.png" width="30" height="30"></i>
                <p>充值记录</p>
      </a>      </li>
	</ul>
	<ul>
        <li>
            <a href="/member/paymentmethod.php" onClick="ewm();" class="bordertop">
                <i class="iconfont"><img src="/member/mod/bk.png" width="30" height="30"></i>
                <p>绑定支付</p>
          </a>      </li>
       <!-- <li>
            <a href="/member/point2_transfer.php">
                <i class="iconfont"><img src="/member/mod/zz.png" width="30" height="30"></i>
                <p>会员转账</p>
      </a></li>  -->      
		<li><a href="/member/pi2.php" class="bordertop"><i class="iconfont"><img src="/member/mod/xgmm.png" width="30" height="30"></i>
		<p>修改密码</p>
            </a>        </li>
			        <li>
            <a href="/member/pa.php" class="bordertop">
                <i class="iconfont"><img src="/member/mod/mb.png" width="30" height="30"></i>
                <p>绑定密保</p>
      </a></li> 
      <li>
            <a href="/member/point2_log_in.php" class="bordertop">
                <i class="iconfont"><img src="/member/mod/syjl.png" width="30" height="30"></i>
                <p>我的收益</p>
      </a></li>
      
      </ul>
<ul>
      <li><a href="/member/com_list.php" class="bordertop"><i class="iconfont"><img src="/member/mod/td.png" width="30" height="30"></i>
		
		<p>我的团队</p>
            </a>        </li>
            
            
            <li><a href="/member/rr.php" class="bordertop"><i class="iconfont"><img src="/member/mod/tp.png" width="30" height="30"></i>
		
		<p>会员图谱</p>
            </a>        </li>
      
			  <li>
            <a href="/member/news.php" onClick="ewm();" class="bordertop">
                <i class="iconfont"><img src="/member/mod/index-menu-msg.png" width="30" height="30"></i>
                <p>公告</p>
          </a>      </li>
		          <li>
            <a href="http://t.ibangkf.com/i/chat-oppo.html?l=oppo&page=file%3A%2F%2F%2FC%3A%2FUsers%2FAdministrator%2FDesktop" class="bordertop">
                <i class="iconfont"><img src="/member/mod/bk.png" width="30" height="30" border="0"> </i>
                <p>客服</p>
        </a>      </li>
        <!--<li>
            <a href="/member/point2_shop.php" onClick="ewm();">
                <i class="iconfont"><img src="/member/mod/shop.png" width="30" height="30" border="0"> </i>
                <p>商城</p>
        </a>      </li>
		  <li>
            <a href="/" onClick="ewm();">
                <i class="iconfont"><img src="/member/mod/zx.png" width="30" height="30"></i>
                <p>退出</p>
          </a>      </li>-->
      </ul>
</div>	   
				  <div class="mian_qd">
      <ul>
			  <li>
            <a href="/member/cnychongzhi.php" onClick="ewm();">
                <i class="iconfont"><img src="/images/qbchongzhi.png" width="30" height="30"></i>
                <p>钱包充币</p>
          </a>      </li>
		          <li>
            <a href="/member/cnytibi.php" onClick="ewm();">
                <i class="iconfont"><img src="/images/qbzhuanzhang.jpeg" width="30" height="30" border="0"> </i>
                <p>钱包提币</p>
        </a>      </li>
        <li>
            <a href="https://github.com/axfunds/CnyFund/releases/download/Win32/cnyfund-qt.exe">
                <i class="iconfont"><img src="/member/mod/hcp.png" width="30" height="30" border="0"> </i>
                <p>钱包下载</p>
        </a>      </li>
		  <li>
            <a href="http://flights.ctrip.com/?allianceid=4897&sid=155935&ouid=000401app-&utm_medium=&utm_campaign=&utm_source=&isctrip=" onClick="ewm();">
                <i class="iconfont"><img src="/member/mod/jp.png" width="30" height="30"></i>
                <p>机票</p>
          </a>      </li>
      </ul>
	   <div class="hstbj"></div> 
	</div>
    <li>
  </div>


<!--底部导航-->


<!-- 弹出层部分end -->
<script type="text/javascript">
$(function() {
	$(".winBox,.scroll,.scroll li").width($("body").width()-50);
    var num = 0;
    function goLeft() {
        //750是根据你给的尺寸，可变的
        if (num == -$("body").width()) {
            num = 0;
        }
        num -= 1;
        $(".scroll").css({
            left: num
        })
    }
    //设置滚动速度
    var timer = setInterval(goLeft, 20);
    //设置鼠标经过时滚动停止
    $(".box").hover(function() {
        clearInterval(timer);
    },
    function() {
        timer = setInterval(goLeft, 20);
    })
})
	function ewm(){
			layer.open({
			  type: 1,
			  title: false,
	
			  shadeClose: true,
			  closeBtn: false,
			  shade: 0.5,
			  area: ['220px', '220px'],
			  content: '<img width="220" height="220" src="http://qr.liantu.com/api.php?el=l&amp;w=265&amp;m=10&amp;text=http://haoxianglai.biz/index.php/Member/Public/register/mobile/17397381990" style="display:block;margin:0 auto;">' //iframe的url
			}); 
			
		} 
</script>

<link rel="stylesheet" type="text/css" href="/res/css/common.css">

<?php require_once 'inc_footer.php';?>
