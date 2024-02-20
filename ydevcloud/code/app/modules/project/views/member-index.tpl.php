<?php
namespace app\project;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$project = $this->get_data('project');
$members = $project->get_members();
$this->master_view = 'master/project';
$loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
$myMember = $project->get_member($loginUser->id);
?>
<h3 class="mb-3"><i class="iconfont icon-members fs-2"></i> <?= __('Member')?></h3>

<div class="d-flex flex-wrap">
    <?php
    foreach ($members as $member){
        $user = $member->get_user();
    ?>
    <div class='card me-3 mb-3' style="width: 13rem;">
        <div class='card-body'>
            <div class="d-flex">
                <div title="<?= $user->nick_name?>" class='border rounded-circle flex-grow-0  flex-shrink-0'
                     style="width: 50px;height: 50px; background-size:cover; background-position:center;background-image: url(<?= $user->avatar ? UPLOAD_SITE_URI.$user->avatar : '/logo2.svg'?>)" ></div>
                <div class='ps-3'>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-truncate" style="width: 6rem;"><?= $user->nickname?:$user->get_escape_cellphone()?></div>
                        <?php if ($myMember->role==Project_Member_Model::ROLE_ADMIN){?>
                        <div class="dropdown flex-grow-0  flex-shrink-0">
                            <i class="iconfont icon-more" type="button" data-bs-toggle="dropdown" aria-expanded="false"></i>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a href="#" data-size="sm" data-url="/project/<?= $project->uuid?>/invite-member?uuid=<?= $member->uuid?>"
                                       data-title="<?= __('Change Role')?>" class="dropdown-item yd-dialog">
                                        <i class="iconfont icon-edit"></i> <?= __('Change Role')?></a></li>
                                    <li><a href="#" data-url="/project/<?= $project->uuid?>/remove-member?uuid=<?= $member->uuid?>"
                                           data-redirect="reload"
                                           data-content="<?= __('Remove is safely, all member\'s content is still there, and you can always invite back')?>" class="dropdown-item yd-confirm-post text-danger">
                                            <i class="iconfont icon-remove"></i> <?= __('Remove')?></a></li>
                        </div>
                        <?php }?>
                    </div>
                    <div>
                        <span class="text-muted"><?php echo __($member->role);?></span>
                    <?php
                    if ($member->is_creater){
                        echo ' <small class="text-info">'.__('Creater').'</small>';
                    }
                    if (!$member->is_invited){
                        echo ' <small class="text-danger">'.__('Inviting').'</small>';
                    }
                    ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php }?>
    <?php if ($myMember->role==Project_Member_Model::ROLE_ADMIN){?>
    <div class='card border-primary mb-3' style="width: 12rem;">
        <div class='card-body d-flex justify-content-center align-items-center'>
            <button class="btn btn-primary yd-dialog" data-title="<?= __('Invite Member')?>" data-url="/project/<?= $project->uuid?>/invite-member" type="button"><?= __('Invite Member')?></button>
        </div>
    </div>
    <?php }?>
</div>
