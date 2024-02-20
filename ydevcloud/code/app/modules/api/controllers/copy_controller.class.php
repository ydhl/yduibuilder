<?php
namespace app\api;
use app\project\Function_Model;
use app\project\Page_Bind_Style_Model;
use app\project\Page_Model;
use app\project\Page_User_Model;
use app\project\Project_Member_Model;
use app\vendor\Save_Model_Helper;
use yangzie\YZE_DBAImpl;
use yangzie\YZE_FatalException;
use yangzie\YZE_Hook;
use yangzie\YZE_Model;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use \yangzie\YZE_JSON_View;
use function yangzie\__;

/**
*
* @version $Id$
* @package api
*/
class Copy_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }

    /**
     * 前端复制ui或者使用组件，这是需要把所复制的ui中的id重现生成新的id，并且对应的更改ui id的绑定关系, 目前只有style；
     * 原组件的事件绑定和io绑定，复制后取消
     */
    public function post_ui(){
        $request = $this->request;
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $this->layout = '';
        $data = json_decode(file_get_contents("php://input"));
        $page_uuid = $data->page_uuid;
        $uiconfig  = $data->uiconfig;
        $uiids = [];
        Page_Model::all_sub_item_uiid($uiconfig, $uiids);
        if (!$uiids) return YZE_JSON_View::success($this, $uiconfig);
        $page = find_by_uuid(Page_Model::CLASS_NAME, $page_uuid);
        if (!$page) throw new YZE_FatalException(__('page not found'));
        $oldID2newID = [];
        foreach ($uiids as $uiid=>$type){
            $newID = uuid(5,null, $page_uuid);
            $oldID2newID[$uiid] = $newID;
        }
        $sql = "insert into page_bind_style(uuid, page_id, style_id, uiid) values";
        $insertValues = [];

        foreach (Page_Bind_Style_Model::from()
            ->where("page_id=:pid and uiid in ('".join("','", array_keys($uiids))."') and is_deleted=0")
                     ->select([':pid'=>$page->id]) as $oldBind){
            if (!$oldID2newID[$oldBind->uiid]) continue;
            $insertValues[] = "(uuid(), {$page->id}, {$oldBind->style_id}, '{$oldID2newID[$oldBind->uiid]}')";
        }

        if ($insertValues) YZE_DBAImpl::get_instance()->exec($sql.join(',', $insertValues));

        $uiconfig = Page_Model::replace_uiid($uiconfig, $oldID2newID);

        return YZE_JSON_View::success($this, $uiconfig);
    }

    public function exception(\Exception $e){
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }
}
?>
