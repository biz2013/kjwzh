<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/conn.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/webConfig.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/pay/pay.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/simple_header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/UserAccount.php';

function purchase($db, &$error_msg, &$payment_url, $user) {
    $amount = isset($_REQUEST['amount'])?$_REQUEST['amount']:0;
    if ($amount == 0) {
        error_log("amount is 0");
        $error_msg = '请填写支付金额';
        return;
    }

    $weixin = isset($_REQUEST['weixin'])?$_REQUEST['weixin']:'';
    if ($weixin === '') {
        error_log("weixin is empty");
        $error_msg = '请到绑定支付中输入微信昵称';
        return;
    }

    $total_fee = $amount*100;
    $pay = new pay();
    $out_trade_no = date('YmdHis').rand(100000,999999);
    $subject = 'chongzhi';
    if (FCBPayConfig::INTESTMODE) {
        $config['notify_url'] = FCBPayConfig::THISSITEDEV . '/notify.php';
        $config['return_url'] = FCBPayConfig::THISSITEDEV . '/return.php';	
    }else {
        $config['notify_url'] = FCBPayConfig::THISSITEPROD . '/notify.php';
        $config['return_url'] = FCBPayConfig::THISSITEPROD . '/return.php';
    }
    $config['out_trade_no'] = $out_trade_no;
    $config['subject'] = $subject;
    $config['total_fee'] = $total_fee;
    $config['attach'] = 'weixin=' . $weixin . ';username=' . $user->username;

    try {
        $data  = $pay->applypurchase($config);
        error_log(isset($data)? 'return data is ' . $data['return_code']: 'not returned from applypurchase');
        if ($data['return_code']=='FAIL') {
            error_log('return code say failed');
            if ($data['return_msg'] === '请您等您正在处理的充值购买请求被确认后再发新的请求') {
                $error_msg = '您还有未处理的充值，如已付款请耐心等待，如未付款请到充值记录里点记录后的【等待审核】链接，继续完成支付';
            } else {
                $error_msg = '充值错误: ' . $data['return_msg'];
            }
        }elseif ($data['return_code']=='SUCCESS') {
            $payment_url = $data['payment_url'];
            $trx_bill_no = $data['trx_bill_no'];
            if (empty($payment_url)) {
                $error_msg = '充值错误: 系统没有提供付款连接';
            } else {
                error_log('chongzhi: Call to applypurchase return code say succeeded, create purchase record');
                //记录充值记录
                $sql = "insert into `order` set ";
                $sql .= "username = '" . $user->username . "', ";
                $sql .= "out_trade_no = '{$out_trade_no}', ";
                $sql .= "subject = '{$subject}', ";
                $sql .= "total_fee = " . $amount . ", ";
                $sql .= "trx_bill_no = '" . $trx_bill_no . "',";
                $sql .= "submit_time = '" . date('Y-m-d H:i:s') . "', ";
                $sql .= "ip = '" . getUserIP() . "' ";
                error_log($sql);
                $db->query($sql);
    
                $pay_time = date('Y-m-d H:i:s');
                $sql = "insert into `h_recharge` set ";
                $sql .= "h_userName = '{$user->username}', ";
                $sql .= "h_money = '{$amount}', ";
                //	$sql .= "h_fee = '" . ($num * $webInfo['h_withdrawFee']) . "', ";
                $sql .= "h_bank = 0, ";
                $sql .= "h_bankFullname = 'out_trade_no:{$out_trade_no}', ";
                $sql .= "h_state = 0, h_isReturn=0, ";
                $sql .= "h_addTime = '{$pay_time}', ";
                $sql .= "out_trade_no = '{$out_trade_no}', ";
                $sql .= "h_actIP = '" . getUserIP() . "' ";
                $rc = $db->query($sql);
                error_log($sql);
            }
        }else {
          error_log('return code say unknow');
          error_log("System return unknown response " . $data['return_code']);
          error_log(VAR_DUMP($data));
          $error_msg = '充值错误: 系统返回不正确的结果: ' . $data['return_code'];
        }
    }catch (PayException $pe) {
        $error_msg = '充值错误:' . $pe->getMessage() . ".  请稍后再试.";
        error_log("chongzhi: hit exception " . $pe->getMessage());
    }

}

$pageTitle = '金币充值 - ';

$body_style ="background:#fff; margin-top:56px;";

error_log("chongzhi: user " . $memberLogged_userName);
$user = UserAccount::load($db, $memberLogged_userName);
$errMsg = '';
$paymentUrl = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log("Call purchase");
    purchase($db, $errMsg, $paymentUrl, $user);
    error_log("Done purchase(" . $user->username . "): error message:" . $errMsg . ' paymenturl:' . $paymentUrl);
    if (empty($errMsg)) {
      header('Location:' . "/member/purchase_qrcode.php?amount=" . $_REQUEST['amount'] . "&payment_qrcode_url=" . urlencode($paymentUrl));
    }
}

generateHeader($pageTitle, $webInfo['h_keyword'], $webInfo['h_description']);

?>
<style>
.error_with_icon
{
    background-image: url('/images/error_icon.png');
    background-repeat: no-repeat;
    background-size: 28px 28px;
    padding-top: 5px;
    padding-left: 40px;  /* width of the image plus a little extra padding */
    height: 100px;
    display: block;  /* may not need this, but I've found I do */
}
</style>
<body style="<?php echo $body_style; ?>">
<div class="container" >
    <div class="row">
        <form name="id_purchase_form" id="id_purchase_form" class="form-horizontal" action="/member/jincz.php" method="post" >
        <input name="weixin" type="hidden" id="weixin" value="<?php echo $user->weixin ?>"/>
        <h3>充值</h3>
        <div class="alert alert-info col-sm-*">每次限额<?php echo FCBPayConfig::MAXPURCHASE ?>元，12小时内到账</div>
        <div class="alert alert-success col-sm-*" role="alert" id='success_msg'></div>
        <div class="alert alert-danger col-sm-*" role="alert" id='error_msg'></div>
        <div class="form-group">
            <label class="col-xs-4 control-label" for="balance">您的余额(元)</label>
            <div class="col-xs-8">
              <input name="balance" class="form-control" ype="text" id="balance" value="<?php echo $user->balance; ?>" readonly/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-4 control-label" for="amount">充值金额(元)</label>
            <div class="col-xs-8">
              <input name="amount" class="form-control" type="text" id="amount" value="100" placeholder="充值金额" />
            </div>
        </div>
        <div class="form-group">        
            <div class="col-xs-offset-4 col-xs-8">
            <?php  if (!isset($user->weixin) || trim($user->weixin) === ''): ?>
              请先到<b>绑定支付</b>添加微信昵称再进行充值
            <?php else: ?>
                  <button type="button" id="click_purchase" class="btn btn-big btn-primary">立即充值</button>
            <?php endif; ?>
            </div>
        </div>
        </form>
    </div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/include/inc_popup_msgbox.php'; ?>    
    <!-- the waiting icon -->
    <div id="wait" style="display:none;width:64px;height:64px; position:absolute;top:50%;left:50%;padding:2px;">
         <img src='/images/Loading_blue.gif' width="50" height="50" /><br>处理中</div>
</div>
<script>
    
    $(document).ready(function(){
        $("#success_msg").hide();
    <?php if (isset($errMsg) && strlen($errMsg) > ''): ?>
        $("#error_msg").text("<?php echo $errMsg ?>");
    <?php else:?>
        $("#error_msg").hide();
    <?php endif; ?>
        $("#wait").css("display", "none");
        function disableButton() {
            $("#click_purchase").prop('disabled', true);
        }
        $("#click_purchase").click(function () {
            disableButton();   
            $("#success_msg").hide();
            $("#error_msg").hide();
            var amountVal= parseFloat($("#amount").val());
            var amount=isNaN(amountVal)?0.0:amountVal;
            if (amount <= 0) {
                $("#errorTitle").text("输入错误");
                $("#errorBody").text("请输入充值金额");
                $("#errorMessage").modal({backdrop: "static"});
                $("#click_purchase").prop('disabled', false);
                return;
            }

            if (amount > <?php echo FCBPayConfig::MAXPURCHASE ?>) {
                $("#errorTitle").text("输入错误");
                $("#errorBody").text("充值金额不能超过<?php echo FCBPayConfig::MAXPURCHASE ?>");
                $("#errorMessage").modal({backdrop: "static"});
                $("#click_purchase").prop('disabled', false);
                return;                
            }
            $("#wait").css("display", "block");
            $("#id_purchase_form").submit();
        });
    });
</script>

<?php
require_once 'inc_footer.php';
?>
