<?php
namespace app\project;
use app\vendor\Env;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$members = $this->get_data('members');
$invite_members = $this->get_data('invite_members');
$env = Env::package();
?>
<?php if ($invite_members){?>
    <div class="card bg-light shadow-sm mb-3">
        <div class="card-header"><?= __('Invite Projects')?></div>
        <table class="table-striped table table-borderless table-hover align-middle m-0">
            <thead>
            <tr>
                <th></th>
                <th><?= __('Project Name')?></th>
                <th><?= __('Technology')?></th>
                <th><?= __('Role')?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($invite_members as $project_member){
                $project = $project_member->get_project();
                $frontendFramework = $project->get_setting_value(Env::FRONTEND_FRAMEWORK);
                $backendFramework = $project->get_setting_value(Env::FRAMEWORK);
                $ui = $project->get_setting_value(Env::UI).'@'.$project->get_setting_value(Env::UI_VERSION);
                $logo = $project->get_setting_value('logo');
                ?>
                <tr>
                    <td>
                        <?php if ($logo){?>
                        <img src="<?= UPLOAD_SITE_URI.$logo?>" class="rounded-circle" style="width: 30px;height: 30px;object-fit: cover"/>
                        <?php }?>
                    </td>
                    <td><a href="/project/<?= $project->uuid?>"><?= $project->name?></a></td>
                    <td>
                        <?php
                        if ($frontendFramework) echo "<small class='badge bg-success me-1'>{$env[$frontendFramework]['name']}</small>";
                        if ($backendFramework) echo "<small class='badge bg-secondary me-1'>{$backendFramework}</small>";
                        if ($ui) echo "<small class='badge bg-info'>{$ui}</small>";
                        ?>
                    </td>
                    <td><?php echo $project_member->get_role_desc();
                        if ($project_member->is_creater){
                            echo ' <span class="badge bg-secondary">'.__('Creater').'</span>';
                        }
                        ?></td>
                    <td class="text-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success join-project" data-uuid="<?= $project->uuid?>"><?= __('Join Project')?></button>
                            <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item notjoin-project" href="#" data-uuid="<?= $project->uuid?>"><?= __('Disagree')?></a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php }?>
            </tbody>
        </table>
    </div>
<?php }?>
<h4 class="border-bottom p-3 text-muted d-flex justify-content-between align-items-center">
    <?= __('My Projects')?>
    <button data-url="/project/add" class="btn btn-primary yd-dialog"  data-title="<?= __("Add Project")?>"><?= __('Add Project')?></button>
</h4>
<table class="table-striped table table-borderless table-hover align-middle">
    <thead>
    <tr>
        <th></th>
        <th><?= __('Project Name')?></th>
        <th><?= __('Type')?></th>
        <th><?= __('Technology')?></th>
        <th><?= __('Role')?></th>
        <th></th>
    </tr>
    </thead>
    <tbody>
        <?php foreach ($members as $project_member){
            $project = $project_member->get_project();

            $frontendFramework = $project->get_setting_value(Env::FRONTEND_FRAMEWORK);
            $backendFramework = $project->get_setting_value(Env::FRAMEWORK);
            $ui = $project->get_setting_value(Env::UI).'@'.$project->get_setting_value(Env::UI_VERSION);
            $logo = $project->get_setting_value('logo');
            ?>
            <tr>
                <td>
                    <?php if ($logo){?>
                    <img src="<?= UPLOAD_SITE_URI.$logo?>" class="rounded-circle" style="width: 30px;height: 30px;object-fit: cover"/>
                    <?php }?>
                </td>
                <td><a href="/project/<?= $project->uuid?>"><?= $project->name?></a></td>
                <td><?= __($project->end_kind)?></td>
                <td>
                    <?php
                    if ($frontendFramework) echo "<small class='badge bg-success me-1'>{$env[$frontendFramework]['name']}</small>";
                    if ($backendFramework) echo "<small class='badge bg-secondary me-1'>{$backendFramework}</small>";
                    if ($ui) echo "<small class='badge bg-info'>{$ui}</small>";
                    ?>
                </td>
                <td><?php echo $project_member->get_role_desc();
                if ($project_member->is_creater){
                    echo ' <span class="badge bg-secondary">'.__('Creater').'</span>';
                }
                ?></td>
                <th>
                    <?php if (!$project_member->is_creater){?>
                       <button class="btn btn-sm btn-outline-primary yd-confirm-post" data-redirect="reload"
                               data-content="<?= __("Are you sure you want to quit this project?")?>" data-url="/project/quit.json?uuid=<?= $project->uuid?>">退出</button>
                    <?php }?>
                </th>
            </tr>
        <?php }?>
    </tbody>
</table>
