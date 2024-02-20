<?php
namespace app\common;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;
use function yangzie\yze_module_asset_url;
$prefixSorted = getCellphoneRegionCode();
?>

<div class="d-flex linear-gradient-bg flex-column vh-100 justify-content-center align-content-center align-items-center">
    <img src="/logo.png" style="width: 15rem;" alt="<?= APPLICATION_NAME?>">
    <div class="card mt-5 col-11 col-sm-4">
        <div class="card-header"><?= __('Sign in YDE Cloud')?></div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label"><?= __('Email Or Cellphone(+Region)')?></label>
                <div class="input-group">
                    <input type="text" class="form-control" id="email" autocomplete="off">
                    <select class="border" style="width: 80px" disabled id="region">
                        <option value=""><?= __('region:')?></option>
                        <?php foreach ($prefixSorted as $item) {?>
                            <option value="<?= $item['prefix']?>"><?= '+'.$item['prefix'].' '.$item['en']?></option>
                        <?php }?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><?= __('Password')?></label>
                <input type="password" class="form-control" id="password">
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-primary yd-spin-btn" id="btn-signin-pwd"><?= __('Sign In')?></button>
            </div>
            <small class="text-muted"><?= __('If you not have account, please sign in by code')?></small>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <a class="btn btn-secondary btn-sm" href="/signin/code"><?= __('Sign In By Code')?></a>
            <a href="/ydlogin/ydhl.php?state=ydevclod"
               class="btn btn-light"><img class="oauth-logo rounded-circle" style="width:23px;" src="<?= yze_module_asset_url('/img/ydhl.png')?>"></a>
        </div>
    </div>
</div>
