<?php
require_once 'header.php';

require_once '../include/pager.php';
$clause = array_key_exists("clause", $_GET)? $_GET['clause'] : "";

switch($clause)
{
	case "addinfo":
		menu();
		addinfo();
		break;
	case "saveinfo":
		saveinfo();
		break;
	case "editinfo":
		menu();
		editinfo();
		break;
	case "saveeditinfo":
		saveeditinfo();
		break;
	case "unlockinfo":
		unlockinfo();
		break;
	case "lockinfo":
		lockinfo();
		break;
	case "delinfo":
		delinfo();
		break;
	default:
		menu();
		main();
		break;
}


function saveinfo()
{
	global $db,$id,$h_state,$h_reply;
	
	$query = "update `h_withdraw` SET h_state = '$h_state',h_reply = '$h_reply' where id = $id";
	$db->query($query);
	
	if($h_state == '审核失败'){
		$rs = $db->get_one("SELECT * FROM `h_withdraw` where id = $id");
		if($rs){
			if($rs['h_isReturn'] == 0){
				$num = $rs['h_money'];
				
				//返款
				$sql = "update `h_member` set ";
				$sql .= "h_point2 = h_point2 + {$num} ";
				$sql .= "where h_userName = '" . $rs['h_userName'] . "' ";
				$db->query($sql);
				
				//记录加钱
				$sql = "insert into `h_log_point2` set ";
				$sql .= "h_userName = '" . $rs['h_userName'] . "', ";
				$sql .= "h_price = '" . $num . "', ";
				$sql .= "h_type = '申请提现', ";
				$sql .= "h_about = '提现失败，原因：{$h_reply}', ";
				$sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
				$sql .= "h_actIP = '" . getUserIP() . "' ";
				$db->query($sql);
		
				$query = "update `h_withdraw` SET h_isReturn = '1' where id = $id";
				$db->query($query);
			}
		}
	}
	
	FJS_PAC('保存成功');
}


function main()
{
	global $db,$LoginEdUserName;
	
	global $stype,$keyword;
	$where = "";
	if(strlen($keyword) > 0){
		$where .= " and (h_userName like '%{$keyword}%')";
	}
	if(strlen($stype) > 0){
		$where .= " and h_state = '{$stype}'";
	}
	
	global $page;
	$list_num = 15;
	$total_count = $db->counter("`h_withdraw`", "1 = 1 {$where}", 'id');$page = (int)$page;$rowset = new Pager($total_count,$list_num,$page);$from_record = $rowset->_offset();$page_list = $rowset->link('?page=');
	$query = "select * from `h_withdraw` where 1 = 1 {$where} order by h_addTime desc,id desc LIMIT $from_record, $list_num";
	$result = $db->query($query);
	//$query = "Select * from `h_withdraw` order by h_addTime desc";
	//$result = $db->query($query);
	$rs_list = array();
	while($list = $db->fetch_array($result))
	{
		$rs_list[]=$list;
	}
?>
<iframe style="display: none" name="iframe_qpost" id="iframe_qpost"></iframe>
<table width="98%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#FFFFFF" class="tableborder">
  <tr>
    <td height="25" colspan="11" align="center" class="tdtitle">提现记录</td>
  </tr>
  <tr align="center"> 
    <td height="23" class="tdtitle-title">会员</td>
    <td class="tdtitle-title">提现金额</td>
    <td class="tdtitle-title">收款支付宝</td>
    <td class="tdtitle-title">申请时间</td>
    <td class="tdtitle-title">打款</td>
    <td class="tdtitle-title">相关操作</td>
  </tr>
<?php
foreach ($rs_list as $key=>$val)
{
?>
  <tr align="center" class="tdbottom" onMouseOver="javascript:this.className='tdbottomover';" onMouseOut="javascript:this.className='tdbottom';"> 
    <td height="25"><?php echo $val['h_userName']; ?></td>
    <td><?php echo $val['h_money'] , '-', $val['h_fee'] , '=' , ($val['h_money'] - $val['h_fee']); ?></td>
    <td><?php echo $val['h_bank']; ?>
<!--	if($val['h_imgs']){
		echo "<a  href=\"{$val['h_imgs']}\" title=\"点击查看大图\" target=\"_blank\">
		<img src=\"{$val['h_imgs']}\" style=\"max-width:120px;max-height；120px\" /></a>";
	}-->
	</td>
    <td><?php echo $val['h_addTime']; ?></td>
    <td>
<form action="?clause=saveinfo&id=<?php echo $val['id']; ?>" method="post" name="addinfo" target="iframe_qpost">
<select name="h_state">
<option value="待审核" <?php if($val['h_state'] == '待审核'){echo 'selected';} ?>>待审核</option>
<option value="已打款" <?php if($val['h_state'] == '已打款'){echo 'selected';} ?>>已打款</option>
<option value="审核失败" <?php if($val['h_state'] == '审核失败'){echo 'selected';} ?>>审核失败（还款）</option>
</select>
<input name="h_reply" size="30" value="<?php echo $val['h_reply']; ?>" placeholder="回复信息，250个字以内" type="text" />
<input name="" type="submit" value="提交" />
</form>
<?php if($val['h_isReturn']){echo '<span style="color:#ff0000">注意：该提现已经打还元给会员！</span>';} ?>
	</td>
    <td><!--<a href="?clause=editinfo&id=<?php echo $val['id']; ?>">修改</a> | -->
	<a style="cursor:pointer;" onClick="javascript:hintandturn('确定要删除吗？数据将不可恢复！','?clause=delinfo&id=<?php echo $val['id']; ?>',true);">删除</a></td>
  </tr>
<?php
}
?>
</table>
<?php
	if(count($rs_list) > 0) echo "<div style='text-align:center;'>$page_list</div>";
}



function menu()
{
	global $stype,$keyword;
?>
<table width="98%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#FFFFFF" class="tableborder">
  <tr> 
    <td height="25" class="tdtitle" align="center">相关操作</td>
  </tr>
  <tr> 
    <td height="23" class="tdbottom" align="center">
<form action="" method="get">
搜索：
<select name="stype">
<option value="">-=状态=-</option>
<option value="待审核" <?php if($stype == '待审核'){echo "selected";}?>>待审核</option>
<option value="已打款" <?php if($stype == '已打款'){echo "selected";}?>>已打款</option>
<option value="审核失败" <?php if($stype == '审核失败'){echo "selected";}?>>审核失败</option>
</select>
<input name="keyword" placeholder="会员编号" value="<?php echo $keyword;?>" type="text" />
<input type="submit" class="bttn" value="提交搜索" name="Submit">
</form>
    </td>
  </tr>
</table>
<br />
<?php
}



function unlockinfo()
{
	global $db,$id;

	$query = "update `h_withdraw` set h_isRead = 1 where id = $id";
	$db->query($query);
	
	turnToPage('?');
}

function lockinfo()
{
	global $db,$id;

	$query = "update `h_withdraw` set h_isRead = 0 where id = $id";
	$db->query($query);
	
	turnToPage('?');
}


function delinfo()
{
	global $db,$id;

	$query = "delete from `h_withdraw` where id = $id";
	$db->query($query);
	
	turnToPage('?');
}


footer();
?>
