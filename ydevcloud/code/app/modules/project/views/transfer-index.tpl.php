<?php
namespace app\project;
use app\common\Option_Model;
use app\vendor\Env;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$project = $this->get_data("project");
$members = $project->get_members();
$this->layout = '';
?>
<div class="container-fluid">
    <form method="post" id="transfer-form">
        <div class="d-flex flex-wrap">
        <?php
        $hasMember = false;
        foreach ($members as $member){
            if ($member->is_creater || !$member->is_invited) continue;
            $user = $member->get_user();
            $hasMember = true;
            ?>
            <div class='card me-3 mb-3' data-id="<?= $member->uuid?>" style="width: 10rem;">
                <div class='card-body'>
                    <div class="d-flex flex-column justify-content-center align-items-center">
                        <div title="<?= $user->nick_name?>" class='border rounded-circle flex-grow-0  flex-shrink-0' style="width: 50px;height: 50px; background-size:cover; background-position:center;background-image: url(<?= $user->avatar?:'/logo2.svg'?>)" ></div>
                        <div class='ps-3'>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-truncate" style="width: 6rem;"><?= $user->nickname?:$user->get_escape_cellphone()?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php }
        if(!$hasMember){?>
            <div class="text-center"><?= __('has no member, please invite member first')?></div>
        <?php }?>
        </div>
    </form>
</div>
<script>
    var transferToId = '';
    $('#transfer-form').delegate('.card', 'click', function (){
        transferToId = $(this).attr('data-id');
        $("#transfer-form .card").removeClass('border-primary shadow');
        $("[data-id="+transferToId+"]").addClass('border-primary shadow');
    })
</script>
