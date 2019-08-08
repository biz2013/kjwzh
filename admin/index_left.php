<?php
require_once '../include/conn.php';

require_once 'chkLogged.php';
?>
<HTML>
<HEAD>
<TITLE>管理导航栏目</TITLE>
<META http-equiv=Content-Type content="text/html; charset=utf-8">
<SCRIPT src="../js/prototype.js"></SCRIPT>
<style type="text/css">
<!--
BODY {
	SCROLLBAR-FACE-COLOR: #2b73f1; BACKGROUND: #0650d2; MARGIN: 0px; FONT: 9pt 宋体; SCROLLBAR-HIGHLIGHT-COLOR: #0650d2; SCROLLBAR-SHADOW-COLOR: #449ae8; SCROLLBAR-3DLIGHT-COLOR: #449ae8; SCROLLBAR-ARROW-COLOR: #02338a; SCROLLBAR-TRACK-COLOR: #0650d2; SCROLLBAR-DARKSHADOW-COLOR: #0650d2; TEXT-DECORATION: none
}
TABLE {
	BORDER-RIGHT: 0px; BORDER-TOP: 0px; BORDER-LEFT: 0px; BORDER-BOTTOM: 0px
}
TD {
	FONT: 12px 宋体
}
IMG {
	BORDER-RIGHT: 0px; BORDER-TOP: 0px; VERTICAL-ALIGN: bottom; BORDER-LEFT: 0px; BORDER-BOTTOM: 0px
}
A {
	FONT: 12px 宋体; COLOR: #000000; TEXT-DECORATION: none
}
A:link {
	COLOR: #000000; TEXT-DECORATION: none
}
A:hover {
	COLOR: #000000
}
.sec_menu {
	BACKGROUND: #d4ecf5;
	OVERFLOW: hidden;
	BORDER-LEFT: #ffffff 1px solid;
	BORDER-BOTTOM: #ffffff 1px solid;
	TEXT-ALIGN: right;
}
.menu_title {
	
}
.menu_title SPAN {
	FONT-WEIGHT: bold; LEFT: 8px; COLOR: #0f42a6; POSITION: relative; TOP: 2px
}
.menu_title2 {
	
}
.menu_title2 SPAN {
	FONT-WEIGHT: bold; LEFT: 8px; COLOR: #cc0000; POSITION: relative; TOP: 2px
}
.Glow {
	FILTER: Glow(Color=#ffffff, Strength=1) dropshadow(Color=#ffffff, OffX=0, OffY=1,)
}
-->
</style>
</HEAD>
<BODY>
<TABLE cellSpacing="0" cellPadding="0" width="180" align="center" border="0">
  <TBODY>
    <TR>
      <TD vAlign="top" height="44"><IMG src="../images/admin/title.gif"></TD>
    </TR>
  </TBODY>
</TABLE>
<TABLE cellSpacing="0" cellPadding="0" width="180" align="center">
  <TBODY>
    <TR>
      <TD class="menu_title" background="../images/admin/title_bg_quit.gif" height="26">&nbsp;&nbsp;<A href="Index_Main.php" target="main" onMouseOver="this.className='menu_title2';" onMouseOut="this.className='menu_title';"><B><span class="glow">管理首页</span></B></A><span class="glow"> | </span><A href="logout.php" target="_top" onMouseOver="javascript:this.className='menu_title2';" onMouseOut="javascript:this.className='menu_title';"><B><span class="glow">退出</span></B></A> </TD>
    </TR>
    <TR>
      <TD background="../images/admin/title_bg_admin.gif" height="97">
          <TABLE cellSpacing="0" cellPadding="0" width="150" align="center">
            <TBODY>
              <TR>
                <TD height="25">您的用户名：<?php echo $LoginEdUserName; ?></TD>
              </TR>
              <TR>
                <TD height="25">您的身份：后台管理员</TD>
              </TR>
              <TR>
                <TD height="25">&nbsp;</TD>
              </TR>
            </TBODY>
          </TABLE>
       </TD>
    </TR>
  </TBODY>
</TABLE>





<?php
$MenuId = 0;

$qx = $rsAdmin['h_permissions'];

foreach($qxArr as $key=>$arr){
	//先遍历，看有没有权限
	$hasItem = false;
	 foreach($arr as $key1=>$val1){
		if(stripos($qx,',' . $key1 . ',') !== false){
			$hasItem = true;
			break;
		}
	 }
	 
	 if(!$hasItem){
		 continue;
	 }
	 
	$MenuId = $MenuId + 1;
	$TitleBg = $MenuId;
	
	echo '<TABLE cellSpacing="0" cellPadding="0" width="167" align="center">
	  <TBODY>
		<TR>
		  <TD class="menu_title" onMouseOver="this.className=\'menu_title2\';" style="cursor:pointer;" onClick="javascript:new Element.toggle(\'submenu';
		  echo $MenuId;
		  echo '\');" onMouseOut="javascript:this.className=\'menu_title\';" background="../images/admin/admin_left_';
		  echo $TitleBg;
		  echo '.gif" height="28"><span class="glow">◎ ';
		  echo $key;
		  echo '</span></TD>
		</TR>
		<TR>
		  <TD style="display:none;" align="right"  id="submenu';
		  echo $MenuId;
		  echo '">
			<DIV class="sec_menu" style="width:165px;">
			  <TABLE cellSpacing="0" cellPadding="0" width="150" align="center">
				<TBODY>';
				
		 foreach($arr as $key1=>$val1){
			//if(stripos($qx,',' . $key1 . ',') !== false){
				echo '<TR>
					<TD height="20"><a href="' . $val1 . '" target="main">' . $key1 . '</a></TD>
				  </TR>';
			//}
		 }
				
				echo '</TBODY>
			  </TABLE>
			</DIV>
			<div style="height:5px;"></div>
			</TD>
		</TR>
	  </TBODY>
	</TABLE>';
}
?>


<?php footer(); ?>
</body>
</HTML>
