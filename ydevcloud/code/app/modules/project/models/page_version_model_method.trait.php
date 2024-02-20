<?php
namespace app\project;
use yangzie\YZE_Hook;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
/**
 *
 *
 * @version $Id$
 * @package project
 */
trait Page_Version_Model_Method{
    // 这里实现model的业务方法
    public static function save_version($member_id, Page_Model $page_model, $commit_message=''){
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $last_version = $page_model->get_last_version();

        $version = new Page_Version_Model();
        $version->set(Page_Version_Model::F_CONFIG, $page_model->config)
            ->set(Page_Version_Model::F_SCREEN, $page_model->screen)
            ->set(Page_Version_Model::F_UUID, Page_Model::uuid())
            ->set(Page_Version_Model::F_CREATED_ON, date("Y-m-d H:i:s"))
            ->set(Page_Version_Model::F_PROJECT_MEMBER_ID, $member_id)
            ->set(Page_Version_Model::F_INDEX, $last_version->index + 1)
            ->set(Page_Version_Model::F_PAGE_ID, $page_model->id)
            ->set(Page_Version_Model::F_MESSAGE, mb_substr($commit_message, 0, 999))
            ->save();


        $page_model->set(Page_Model::F_LAST_VERSION_ID, $version->id)
            ->save();

        // 保存的权限, 如果有数量限制的，超过限制的则新增一条，删除一条最旧的
        $setting = user_permission();
        $userSetting = $loginUser->get_account_setting();
        $userProjectLimit = intval($userSetting['limit']['Page History']);
        $configProjectLimit = intval($setting[$loginUser->user_type]['items'][$loginUser->account_type]['limit']['Page History']);
        $limit = $userProjectLimit?:$configProjectLimit;
        $savedHistoryCount = Page_Version_Model::from()->where('page_id=:pid')->count('id', [':pid'=>$page_model->id]);
        if ($savedHistoryCount > $limit){
            $firstVersion = Page_Version_Model::from()->where('page_id=:pid')->order_By('id', 'ASC')->get_Single([':pid'=>$page_model->id]);
            if ($firstVersion){
                $firstVersion->remove();
            }
        }


        return $version;
    }
}?>
