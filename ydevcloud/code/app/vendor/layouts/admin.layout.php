<?php
namespace yangzie;
$loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
$topMenu = $this->get_data("top_menu");
$langs = ['zh-cn'=>'中文', 'en'=>'English'];
$language = YZE_Hook::do_hook(YZE_HOOK_GET_LOCALE)?:'en';
?>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo $this->get_data("yze_page_title")?> － <?php echo APPLICATION_NAME?> - <?= __('cross platform, cross framework, cross language, cross terminal')?></title>
        <script> var LANG='<?= YZE_Hook::do_hook(YZE_HOOK_GET_LOCALE)?>'</script>
        <link href="/layui/css/layui.css" rel="stylesheet">
        <?php
        yze_css_bundle("all");
        yze_module_css_bundle();
        yze_js_bundle("all");
        yze_module_js_bundle();
        ?>
        <script src="/layui/layui.js"></script>
    </head>
    <body class="p-0 m-0">
        <nav class="navbar border-bottom navbar-expand-lg navbar-light bg-light shadow-sm sticky-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="/"><img src="/logo.png" style="height: 20px"/></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?= $topMenu=="dashboard"?'active':''?>" aria-current="page" href="/dashboard"><?= __('Dashboard')?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $topMenu=="project"?'active':''?>" href="/project"><?= __('Projects')?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $topMenu=="uicomponent"?'active':''?>" href="/myuicomponent"><?= __('UI Component')?></a>
                        </li>
                    </ul>
                    <div class="d-flex">
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="rounded-circle overflow-hidden" style="width: 30px;height: 30px;background-image: url('<?= $loginUser->avatar ?: '/logo2.svg'?>');background-size: cover;background-position: center">
                                </div>
                                <div class="pl-2">&nbsp;&nbsp;<?= $loginUser->nickname?></div>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                                <li><a class="dropdown-item" href="/profile"><?= __('Profile')?></a></li>
                                <li><a class="dropdown-item" href="/signout"><?= __('Quit')?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <div class="p-3" style="min-height: calc( 100% - 200px)">
            <div class="alert alert-danger d-sm-none"><?= __('Please open by pc browser for best best experience')?></div>
            <?php if($loginUser->hasAccountOverDue()){?>
                <div class="alert alert-danger"><?= __('Your account has expired. Please renew it')?></div>
            <?php }?>
            <?php echo $this->content_of_view();?>
        </div>
        <div class="text-muted text-center mt-5 pt-5 d-flex flex-wrap justify-content-center align-items-center align-content-center">
            <a href="http://yidianhulian.com" class="text-muted text-decoration-none" target="_blank">&copy;易点互联</a>
            <a class="text-muted ms-5 me-5 text-decoration-none" href="mailto:leeboo@yidianhulian.com">service@yidianhulian.com</a>
            <div class="dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?= $langs[$language]?>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?lang=en">English</a></li>
                    <li><a class="dropdown-item" href="?lang=zh-cn">中文</a></li>
                </ul>
            </div>
            <div class="ps-3"><?= VERSION?></div>
        </div>
    </body>
</html>
