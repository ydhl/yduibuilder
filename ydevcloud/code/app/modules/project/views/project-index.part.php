<?php
namespace app\project;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;
use function yangzie\yze_merge_query_string;

$activities = $this->get_data('activities');
$page = $this->get_data('page');
$this->layout = "";
if ($activities) {
foreach ($activities as $activity){
    $user = $activity->get_project_member()->get_user();
?>
    <div class="d-flex mt-2 border-bottom pb-2  fs-7 ">
        <div class="flex-shrink-0 flex-grow-0">
            <img class='border rounded-circle flex-grow-0  flex-shrink-0' style="width: 30px;max-height: 30px" alt="<?= $user->nick_name?>" src="<?= $user->avatar?:'/logo2.svg'?>"/>
        </div>
        <div class="ms-2 flex-fill">
            <div class="text-muted fw-bold"><?= $user->nickname?></div>
            <div>
                <?= $activity->content?>
            </div>
        </div>
        <div class="flex-shrink-0 flex-grow-0 text-muted"><?= dateTimeFormatToString($activity->created_on)?></div>
    </div>
<?php }
}else{?>
    <script>
        hasMore = false;
    </script>

    <div class="d-flex justify-content-center align-items-center p-1 text-muted">
        <?= __('No More')?>
    </div>
<?php }?>
<script>
    page = <?= $page+1?>;
</script>
