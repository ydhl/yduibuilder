<?php
namespace app\front;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$langs = ['zh-cn'=>'中文', 'en'=>'English'];
$language = YZE_Hook::do_hook(YZE_HOOK_GET_LOCALE)?:'en';
?>
<div style="height: calc(100vh - 50px)" class="d-flex linear-gradient-bg justify-content-center align-items-center">
    <div class="d-flex flex-column align-items-center text-muted">
        <img src="/logo.png" style="width: 300px"/>
        <div class="m-3 fw-light"><?= __('Cross Platform, Cross Framework, Cross Language and Cross Terminal online development tools')?></div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <a class="btn btn-sm btn-danger" href="/signin/code"><?= __('Try it now')?></a>
            <div class="dropdown ms-3">
                <button class="btn btn-sm btn-danger dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?= $langs[$language]?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?lang=en">English</a></li>
                    <li><a class="dropdown-item" href="?lang=zh-cn">中文</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div style="height: calc(100vh - 50px)">
    <div class="container">
        <div class="row p-5">
            <div class="col-12 col-sm-6">
                <h1 class="text-muted"><?= __('YDECloud = Prototype design + front-end development')?></h1>
                <p class="text-muted pt-3 pb-3" style="line-height: 2"><?= __('The biggest pain point of software development is the communication cost.For the functional interaction development of conventional application systems,YDECloud does the "UI prototype design" and "front-end functions development" at one time and export the real project code.The functional interface designed and expressed by the designer is the final experience interface,allowing developers to focus more on the back-end logic.')?></p>
                <a class="btn btn-danger" href="/signin/code"><?= __('Try it now')?></a>
            </div>
            <div class="col-12 col-sm-6 ps-3 mt-5">
                <img class="w-100" src="/img/ydhl-ydecloud-case.png">
            </div>
        </div>
    </div>
</div>
<div style="height:100px;" class="p-3 text-center">
    &copy;<a class="text-decoration-none" href="http://yidianhulian.com" target="_blank"><?= __("Guizhou YiDianHuLian Information Technology Co., Ltd")?></a><br/>
    <?= __('Since 2010, it has focused on providing software development technical services')?><br/>
    <?= __('To hire us, please contact leeboo # yidianhulian.com')?>
</div>
