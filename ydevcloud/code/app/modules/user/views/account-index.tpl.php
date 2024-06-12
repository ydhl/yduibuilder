<?php
namespace app\user;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;


$this->master_view = 'master/profile-master';
$loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
$account_setting = user_permission();
?>
<style>
    .account-active {
        border-bottom: 4px solid var(--bs-primary);
    }
</style>
<div>
    <h2><?= __("Account")?></h2>
    <div class="d-flex">
        <div class="col-4">
            <label class="form-label text-muted"><?= __('User type')?></label>
            <div><?= __($loginUser->user_type)?></div>
        </div>
        <div class="col-4">
            <label class="form-label text-muted"><?= __('Account type')?></label>
            <div><?= __($loginUser->account_type)?></div>
        </div>
        <div class="col-4">
            <label class="form-label text-muted"><?= __('Account Due time')?></label>
            <div><?= !$loginUser->account_duedate ? __('Infinite') : $loginUser->account_duedate?>
                <?php if ($loginUser->account_duedate){?>
                <span class="text-primary fs-7"><i class="iconfont icon-tips"></i><?= __("If overdue, you can not edit any project")?></span>
                <?php }?>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-center mt-5">
        <a class="fs-2 m-3 text-decoration-none account-toggle" href="javascript:;" onclick="toggleAccount(this,'individual')"><?= __("Individual")?></a>
        <a class="fs-2 m-3 text-decoration-none account-toggle" href="javascript:;" onclick="toggleAccount(this,'team')"><?= __("Team")?></a>
    </div>
    <?php foreach ($account_setting as $userType=>$accounts){?>
        <div class="text-muted m-2 text-center account-type d-none <?=$userType?>"><?= $accounts['desc']?></div>
        <div class="d-flex mt-5 justify-content-center account-type d-none <?=$userType?>">
            <?php foreach ($accounts['items'] as $accountType=>$setting){?>
                <div class="card me-5">
                    <div class="card-body shadow text-center">
                        <div class="fs-2 m-3 text-uppercase"><?= __($accountType)?></div>
                        <div class="text-muted"><?= __($setting['desc'])?></div>
                        <div class="fs-1 m-5"><?= __($setting['price_info'])?></div>
                        <!--<button type="button" class="btn btn-primary btn-success mb-3"><?/*= __('Buy')*/?></button>-->
                    </div>
                    <table class="table table-borderless table-hover m-0">
                        <tr class="align-middle">
                            <td colspan="2" class="text-muted fs-7"><?= __('Product', 'account')?></td>
                        </tr>
                        <?php foreach ($setting['product'] as $rightItem => $rightValue){?>
                        <tr class="align-middle">
                            <td><?= $rightItem?></td>
                            <td><?php
                                if ($rightValue=="y"){
                                    echo "<span class='text-success'><i class='iconfont icon-check'></i></span>";
                                }else{
                                    echo "<span class='text-danger'><i class='iconfont icon-remove'></i></span>";
                                }
                                ?></td>
                        </tr>
                        <?php }?>
                        <tr class="align-middle">
                        <td colspan="2" class="text-muted fs-7"><?= __('Limit', 'account')?></td>
                        </tr>
                    <?php foreach ($setting['limit'] as $rightItem => $rightValue){?>
                        <tr class="align-middle">
                            <td><?= __($rightItem)?></td>
                            <td><?php  if ($rightValue=='-'){
                                    echo __("Unlimited");
                                }else if($rightValue=='n'){
                                    echo "<span class='text-danger'><i class='iconfont icon-remove'></i></span>";
                                }else{
                                    echo $rightValue;
                                }?></td>
                        </tr>
                    <?php }?>
                </table>
            </div>
            <?php }?>
        </div>
    <?php }?>
</div>
<script>
    function toggleAccount(btn, type) {
        $('.account-type').addClass('d-none');
        $('.account-toggle').removeClass('account-active')
        $('.'+type).removeClass('d-none');
        $(btn).addClass('account-active')
    }
    $(function() {
        $('.account-toggle:first-child').trigger('click');
    });
</script>
