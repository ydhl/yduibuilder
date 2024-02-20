<?php
namespace app\project;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;
use function yangzie\yze_merge_query_string;

$project = $this->get_data('project');
$activities = $this->get_data('activities');
$page = $this->get_data('page');
$this->master_view = 'master/project';
?>
<h3><i class="iconfont icon-activity fs-2"></i> <?= __('Activity')?></h3>
<ul class="nav nav-pills mt-3 mb-3">
    <li class="nav-item">
        <a class="nav-link <?= !@$_GET['type'] ? 'active' : ''?>" href="/project/<?= $project->uuid?>"><?= __('All')?></a>
    </li>
    <?php foreach (Activity_Model::get_types() as $name => $label){ ?>
    <li class="nav-item">
        <a class="nav-link <?= @$_GET['type']==$name ? 'active' : ''?>" href="<?= yze_merge_query_string('', ['type'=>$name])?>"><?= $label?></a>
    </li>
    <?php }?>
</ul>
<div id="activity-container">
<?php
if ($activities) {
foreach ($activities as $activity){
    $user = $activity->get_project_member()->get_user();
?>
    <div class="d-flex mt-2 border-bottom pb-2  fs-7 ">
        <div class="flex-shrink-0 flex-grow-0">
            <div class="rounded-circle overflow-hidden" alt="<?= $user->nick_name?>" style="width: 30px;height: 30px;background-image: url('<?= $user->avatar?:'/logo2.svg'?>');background-size: cover;background-position: center">
            </div>
        </div>
        <div class="ms-2 flex-fill">
            <div class="text-muted fw-bold"><?= $user->nickname?></div>
            <div>
                <?= html_entity_decode($activity->content)?>
            </div>
        </div>
        <div class="flex-shrink-0 flex-grow-0 text-muted"><?= dateTimeFormatToString($activity->created_on)?></div>
    </div>
<?php }
}else{?>
    <div class="d-flex justify-content-center align-items-center p-5">
        <i class="iconfont icon-blank fs-1 text-muted"></i>
        <?= __('Empty')?>
    </div>
<?php }?>
</div>
<script>
    var page = <?= $page+1?>;
    var hasMore = true;
    var isLoading = false;
    document.addEventListener('scroll', function (event){
        if (!hasMore || isLoading) return;
        var body = $('body').get(0);
        var rect = body.getBoundingClientRect();
        if (body.clientHeight + 20 + body.scrollTop > rect.height){
            isLoading = true;
            $.get('/project/<?= $project->uuid?>.part',{page: page}, function(html){
                $('#activity-container').append(html)
                isLoading = false;
            })
        }
    })

</script>
