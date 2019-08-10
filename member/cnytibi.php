<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/conn.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/webConfig.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/simple_header.php';
require_once '../entities/UserWallet.php';
require_once '../entities/UserAccount.php';
require_once '../entities/UserWalletExternal.php';
require_once '../entities/UserAccount.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$rs = $db->get_one("select *,(select count(id) from `h_member` where h_parentUserName = a.h_userName and h_isPass = 1 and h_isLock=0) as comMembers from `h_member` a where h_userName = '{$memberLogged_userName}'");
$userwallet = new UserWallet();
$userwallet->load($db, $memberLogged_userName, 'CNYF');
if (empty($userwallet->walletCrypto)) {
    error_log('cnytibi.php: create new wallet for user ' . $memberLogged_userName);
    $userwallet->create($db, $memberLogged_userName, 'CNYF');
    $userwallet->load($db, $memberLogged_userName, 'CNYF');
}

$userwalletexternal = UserWalletExternal::load_by_username($db, $memberLogged_userName, 'CNYF');
$externaladdress = !is_null($userwalletexternal) ? $userwalletexternal->walletAddress : '';
$user = UserAccount::load($db, $memberLogged_userName);

$pageTitle = '钱包提币 - ' . $webInfo['h_webName'] . ' - ' . '会员中心';  ;
$body_style ="background:#fff;";
generateHeader($pageTitle, $webInfo['h_keyword'], $webInfo['h_description']);
?>
<body style="<?php echo $body_style; ?>">
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

<script>
function get_confirmation_text() {
    return "您将转人民币" + $('#amount').val() + "到外部钱包 " + $('#address').val() + "，请按确认键转账";
}
</script>
<div class="container" style="<?php echo $body_style; ?>">
    <h3>钱包提币</h3>
    <div class="row">
    <div class="alert alert-success col-xs-* col-sm-*" role="alert" id='success_msg' >
    </div>
    <div class="alert alert-danger col-xs-* col-sm-*" role="alert" id='error_msg'>
    </div>
    </div>
    <div class="row">
    <div class="alert alert-info  col-xs-* col-sm-*">系统禁止内部地址互转</div> 
    <form class="form-horizontal" id="form_cnyredeem" >
        <div class="form-group">
        <label class="control-label col-sm-1">您的余额:</label>
        <div class="col-sm-11">
            <input type="text" class="form-control" id="balance" value="<?php echo $rs['h_point2'];?>" readonly>
        </div>
        </div>
        <div class="form-group">
        <label class="control-label col-sm-1" for="pwd">转账金额:</label>
        <div class="col-sm-11">          
            <input type="text" class="form-control" id="amount" placeholder="注意有0.01元转账费用" name="amount">
        </div>
        </div>
        <div class="form-group">
        <label class="control-label col-sm-1" for="pwd">转账地址:</label>
        <div class="col-sm-11">          
            <input type="text" class="form-control" id="address" placeholder="请输入您的外部钱包地址" value="<?php echo $externaladdress ?>" name="address">
        </div>
        </div>
        <div class="form-group">        
            <div class="col-sm-offset-1 col-sm-11">
            <?php if ($user->balance > 0) :?>
                <button type="button" class="btn btn-large btn-primary" id="btn_redeem">转帐</button>
            <?php else :?>
                您的余额为负，请先充值或充币
            <?php endif ?>
            </div>
        </div>
    </form>
    </div>
    <!-- Message Modal -->
    <div class="modal" id="confirmationDialog" role="dialog">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header ">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">转账到外部钱包</h4>
            </div>
            <div class="modal-body">
               <div id="confirmation_content"></div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-success" id="confirm_redeem">确认</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
            </div>
        </div>
        </div>
    </div>

    <!-- Message Modal -->
    <div class="modal" id="errorMessage" role="dialog">
        <div class="modal-dialog modal-xs">
        <div class="modal-content">
            <div class="modal-header alert alert danger">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" id="errorTitle"></h4>
            </div>
            <div class="modal-body">
               <span class="error_with_icon" id="errorBody"></span>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div>
        </div>
    </div>
    <!-- End Message Modal -->
    <div id="wait" style="display:none;width:64px;height:64px; position:absolute;top:50%;left:50%;padding:2px;">
         <img src='/images/Loading_blue.gif' width="50" height="50" /><br>处理中</div>
</div>
<script>
    $(document).ready(function(){
        $("#success_msg").hide();
        $("#error_msg").hide();
        $(document).ajaxStart(function(){
            $("#wait").css("display", "block");
        });
        $(document).ajaxComplete(function(){
            $("#wait").css("display", "none");
        });

        var click_redeemed = false;
        var click_confirmed = false;
        $("#confirmationDialog").on('show.bs.modal', function(){
            click_redeemed = true;
        });
        $("#confirmationDialog").on('hidden.bs.modal', function(){
            click_redeemed = false;
            click_confirmed = false;
        });
        
        $("#confirm_redeem").click(function () {
            if (click_confirmed) {
                return;
            }

            click_confirmed = true;
            $("#confirmationDialog").modal("hide");
            $.post("/controller/process_cnyredeem.php",
                    $("#form_cnyredeem").serialize()
                ).done(function(resp) {
                    $("#success_msg").text("转账成功");
                    var balance = parseFloat($("#balance").val());
                    var amountVal= parseFloat($("#amount").val());
                    balance = balance - amountVal - 0.01;
                    $("#balance").val(balance.toFixed(2).toString());
                    $("#success_msg").show();
                }).fail(function(xhr, textstatus, errorThrow) {
                    $("#error_msg").text("转账请求遇到错误: " + xhr.responseText + "(" + xhr.status + " " + errorThrow + ")");
                    $("#error_msg").show();
                });
        });

        $("#btn_redeem").click(function(){
            if (click_redeemed) {
                return;
            }

            $("#success_msg").hide();
            $("#error_msg").hide();
            var balance = parseFloat($("#balance").val());
            var amountVal= parseFloat($("#amount").val());
            var amount=isNaN(amountVal)?0.0:amountVal;
            if (amount <= 0) {
                $("#errorTitle").text("输入错误");
                $("#errorBody").text("请输入转账金额");
                $("#errorMessage").modal({backdrop: "static"});
                return;
            }

            var address = $("#address").val().trim();
            if (address.length < 34) {
                $("#errorTitle").text("输入错误");
                $("#errorBody").text("请输入转账用的外部地址");
                $("#errorMessage").modal({backdrop:"static"});
                return;                
            }

            if ((balance - amount - 0.01).toFixed(2) < 0) {
                $("#errorTitle").text("输入错误");
                $("#errorBody").text("提币金额超过您的余额（包括1分手续费）");
                $("#errorMessage").modal({backdrop: "static"});
                return;                
            }

            $("#confirmation_content").text("您将转人民币" + $("#amount").val() + "到外部钱包 " + $("#address").val() + "，请按确认键转账");
            $("#confirmationDialog").modal({ backdrop: "static"});
        });
    });

</script>

<?php
require_once 'inc_footer.php';
?>
