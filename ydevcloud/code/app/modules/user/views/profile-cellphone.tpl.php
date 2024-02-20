<?php
namespace app\user;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;


$user = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
$this->master_view = 'master/profile-master';
$prefixSorted = getCellphoneRegionCode();
?>
<div class="w-50">
    <h3 class="mb-3"><?= __('Cellphone')?></h3>

    <div class="mb-3">
        <label class="form-label"><?= __('New Cellphone')?></label>
        <div class="input-group">
            <input type="text" class="form-control" value="<?= $user->cellphone?>" id="email" autocomplete="off">
            <select class="border" style="width: 80px" id="region">
                <option value=""><?= __('region:')?></option>
                <?php foreach ($prefixSorted as $item) {?>
                    <option value="<?= $item['prefix']?>"><?= '+'.$item['prefix'].' '.$item['en']?></option>
                <?php }?>
            </select>
            <button class="btn btn-primary yd-spin-btn" id="btn-sendcode" style="width: 110px" data-check="1" type="button"><?= __('Send Code')?></button>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label"><?= __('Code')?></label>
        <input type="text" class="form-control" id="code" placeholder="check your cellphone">
    </div>
    <div class="d-flex justify-content-between align-items-center">
        <button type="button" class="btn btn-primary yd-spin-btn" id="btn-cellphone"><?= __('Save')?></button>
    </div>
</div>