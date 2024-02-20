<?php

namespace app\common;

use yangzie\YZE_Session_Context;
use function yangzie\__;

$oauth_user = $_SESSION['oauth_user'];
?>

<div class="d-flex vh-100 justify-content-center align-content-center align-items-center">
    <div class="card" style="width: 30rem;">
        <div class="card-body">
            <h5 class="card-title d-flex justify-content-between align-items-center">
                <div class=""><?= __("Cellphone") ?></div>
                <div class="rounded-circle overflow-hidden" style="width: 20px;height: 20px">
                    <img src="<?= $oauth_user->avatar?>" style="width: 20px;">
                </div>
            </h5>
            <h6 class="card-subtitle mb-2 mt-3 text-muted"><?= vsprintf(__("Hi %s, Please input your cellphone"), $oauth_user->displayName) ?></h6>
            <div class="input-group mb-3">
                <select class="form-control flex-grow-0" style="width: 8rem;" id="region">
                    <option value="86"><?= __('中国 +86')?></option>
                </select>
                <input type="text" class="form-control flex-fill" id="cellphone" placeholder="<?= __("Please input your cellphone") ?>" aria-label="<?= __("Please input your cellphone") ?>" aria-describedby="sms-code">
                <button class="btn btn-secondary yd-spin-btn" onclick="send_sms(this)" type="button" id="sms-code"><?= __("Send code") ?></button>
            </div>
            <div class="input-group mb-3 <?= YZE_Session_Context::get_instance()->get("sms") ? "" : "d-none"?>" id="sms-form">
                <span class="input-group-text" id="basic-addon1"><?= __("Code")?></span>
                <input type="text" class="form-control" id="sms" placeholder="<?= __("Please input you received code") ?>" aria-label="<?= __("Please input you received code") ?>" aria-describedby="basic-addon1">
            </div>
            <button type="button" class="btn mb-2 btn-primary btn-block yd-spin-btn" onclick="post_auth(this)"><?= __("Submit") ?></button>

        </div>
    </div>
</div>
<script>
    function send_sms(obj) {
        $.post("/sms", {region: $("#region").val(), cellphone: $("#cellphone").val()}, function (rst) {
            $("#sms-form").removeClass("d-none");
            YDJS.spin_clear('#sms-code');
            if (rst.success) {
                YDJS.toast("<?= __("Sms code send success") ?>", YDJS.ICON_SUCCESS);
            } else {
                YDJS.toast(rst.msg || "<?= __("Sms code send failed") ?>", YDJS.ICON_ERROR);
            }
        });
    }
    function post_auth(obj) {
        $.post("/bind", {region: $("#region").val(), cellphone: $("#cellphone").val(), "sms": $("#sms").val()}, function (rst) {
            YDJS.spin_clear(obj);
            if (rst.success) {
                window.location.href="/dashboard";
            } else {
                YDJS.toast(rst.msg || "<?= __("Bind failed") ?>", YDJS.ICON_ERROR);
            }
        });
    }
</script>
