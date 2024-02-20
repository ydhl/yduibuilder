<?php
namespace app\api;
use app\build\Build_Model;
use app\project\Page_Bind_Style_Model;
use app\project\Page_Model;
use app\project\Project_Model;
use app\project\Style_Model;
use yangzie\YZE_DBAImpl;
use yangzie\YZE_FatalException;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_JSON_View;
use function yangzie\__;

/**
* 处理页面的style
* @version $Id$
* @package api
*/
class Style_Controller extends YZE_Resource_Controller {
    private $page;
    private $loginUser;
    private $project;
    private $project_member;
    public function valid($page_uuid)
    {
        $this->loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $this->page = find_by_uuid(Page_Model::CLASS_NAME, $page_uuid);
        if (!$this->page) throw new YZE_FatalException(__('Page not found'));
        $this->project = $this->page->get_project();
        $this->project_member = $this->project->get_member($this->loginUser->id);
        if (!$this->project_member) throw new YZE_FatalException(__('Project not found'));
    }

    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    public function index() {
        $request = $this->request;
        $this->layout = '';
        $page_uuid = trim($request->get_from_get("page_uuid"));
        $term     = trim($request->get_from_get("term"));
        $this->valid($page_uuid);
        $where = "project_id=:pid and is_deleted=0";
        $params = [':pid'=>$this->project->id];
        if ($term) {
            $where .= ' and class_name like :like';
            $params[':like'] = "%{$term}%";
        }

        $data = [];
        $class = [];

        // 在没有传入ui type 时，会同时查询ui type和class name
        $styles = Style_Model::from()->where($where)->select($params);
        foreach ($styles as $style){
            $total = $style->get_used_count();
            $curr_page_count = $style->get_used_count($this->page->id);
            $item = [
                'id'=>$style->uuid,
                'text'=>$style->class_name,
                'desc'=> $total ? sprintf(__("%s on this page, %s on other page"), $curr_page_count, $total-$curr_page_count, ) : __('Not used'),
            ];
            $class[] = $item;
        }
        if ($class){
            $data[] = ['text'=>__('Existing Classes'), 'children'=>$class];
        }
        return YZE_JSON_View::success($this, $data);
    }
    // 封装成一个ui item返回
    public function detail() {
        $request = $this->request;
        $this->layout = '';
        $uuid = trim($request->get_from_get("uuid"));
        $style = find_by_uuid(Style_Model::CLASS_NAME, $uuid);
        if (!$style) throw new YZE_FatalException(__('Style not found'));
        return YZE_JSON_View::success($this, ["meta"=>json_decode(html_entity_decode($style->meta))]);
    }
    // 返回指定的selector的使用情况
    public function used() {
        $request = $this->request;
        $this->layout = '';
        $uuid = trim($request->get_from_get("uuid"));
        $page_uuid = trim($request->get_from_get("page_uuid"));
        $style = find_by_uuid(Style_Model::CLASS_NAME, $uuid);
        $page = $page_uuid ? find_by_uuid(Page_Model::CLASS_NAME, $page_uuid) : null;
        if (!$style) throw new YZE_FatalException(__('Style not found'));
        $items = Page_Bind_Style_Model::from('pbs')
            ->left_join(Page_Model::CLASS_NAME, 'p', 'p.id = pbs.page_id and p.is_component=0')
            ->left_join(Page_Model::CLASS_NAME, 'u', 'u.id = pbs.page_id and u.is_component=1')
            ->where('pbs.style_id=:id')->select([':id'=>$style->id]);
//        print_r($items->get_sql().'');
//        ;
        $pageUI = [];
        $otherPages = [];
        $uicomponents = [];
        foreach ($items as $item){
            if ($item['p']->id == $page->id){// 当前页面使用selector的元素
                $uiconfig = $page->find_ui_item($item['pbs']->uiid);
                if ($uiconfig) $pageUI[] = ['type'=>$uiconfig->type, 'uuid'=>$uiconfig->meta->id, 'title'=>$uiconfig->meta->title];
            }else if ($item['p']){// 其他页面使用selector
                $otherPages[] = ['uuid'=>$item['p']->uuid, 'title'=>$item['p']->name];
            }
            if ($item['u']){// 组件使用selector
                $uicomponents[] = ['type'=>$item['u']->page_type, 'uuid'=>$item['u']->uuid, 'title'=>$item['u']->name];
            }
        }
        return YZE_JSON_View::success($this, ["thisPage"=>$pageUI,'otherPage'=>$otherPages,'component'=>$uicomponents]);
    }
    // 预览css 代码
    public function code() {
        $request = $this->request;
        $this->layout = '';
        $uuid = trim($request->get_from_get("uuid"));
        $style = find_by_uuid(Style_Model::CLASS_NAME, $uuid);
        if (!$style) throw new YZE_FatalException(__('Style not found'));

        // 这里已bootstrap来编译自定义的style样式，用于前端预览css 代码
        $project = $style->get_project();
        $relativePath = "../";
        $imgAssetPath = $relativePath."assets/img/";
        $ui = 'bootstrap';
        $frontedFramework = 'html';
        $pageViewFile = YZE_APP_PATH."modules/build/views/code/web/{$ui}_{$frontedFramework}/page.view.php";
        include_once $pageViewFile;
        $pageView = "app\\modules\\build\\views\\code\\web\\{$ui}_{$frontedFramework}\\Page_View";

        // 这里构造一个container用于编译style 代码
        $sampleUIConfig = ['type'=>'Container','meta'=>json_decode(html_entity_decode($style->meta), true)];

        $build = new Build_Model($this, null,0, $relativePath, $sampleUIConfig);
        $build->set_img_Asset_Path($imgAssetPath);
        $build->set_project($project);

        $view = $pageView::create_View($build);
        $styles = $view->build_style(false);
        ob_start();
        foreach ($styles as $selector=>$style){
            $build->output_code($selector.' {', 0);
            $build->output_code($style, 1);
            $build->output_code('}', 0);
        }

        return YZE_JSON_View::success($this, ob_get_clean());
    }
    // 拉取绑定数据
    public function post_save(){
        $request = $this->request;
        $this->layout = '';
        $data = json_decode(file_get_contents("php://input"));
        $this->valid($data->page_uuid);
        $class_name = trim($data->class_name);
        if (!$class_name) throw new YZE_FatalException(__('Please input selector name'));

        $meta = $data->meta;
        $style = Style_Model::get_by_name($class_name);
        if (!$style) {
            $style = new Style_Model();
            $style->set('uuid', Style_Model::uuid())
                ->set('project_id', $this->project->id);
        }
        $style->set('class_name', $class_name)
            ->set('meta', json_encode($meta))
            ->save();


        return YZE_JSON_View::success($this);
    }
    // 删除没有使用的selector
    public function post_cleanup(){
        $request = $this->request;
        $this->layout = '';
        $project_uuid = trim($request->get_from_post("project_uuid"));
        $project = find_by_uuid(Project_Model::CLASS_NAME, $project_uuid);
        if (! $project) throw new YZE_FatalException(__('project not found'));
        $sql = "delete s 
    from style as s 
    left join page_bind_style as pbs on pbs.style_id=s.id 
    where s.project_id=".$project->id." and pbs.id is null";
        YZE_DBAImpl::get_instance()->exec($sql);

        return YZE_JSON_View::success($this);
    }
    // 重命名selector
    public function post_rename(){
        $request = $this->request;
        $this->layout = '';
        $uuid = trim($request->get_from_post("uuid"));
        $name = trim($request->get_from_post("name"));
        $style = find_by_uuid(Style_Model::CLASS_NAME, $uuid);
        if (! $style) throw new YZE_FatalException(__('style not found'));
        $style->set('class_name', $name)->save();
        return YZE_JSON_View::success($this);
    }
    // uiitem 绑定selector，并返回所有绑定的selector的style定义
    public function post_bind(){
        $request = $this->request;
        $this->layout = '';
        $data = json_decode(file_get_contents("php://input"), true);
        $uiid = trim($data["uiid"]);
        $selectors = $data["selector"];
        $this->valid($data['page_uuid']);
        $styles = find_by_uuids(Style_Model::CLASS_NAME, $selectors);
        // 删除重新绑定
        Page_Bind_Style_Model::from()->where('page_id=:pid and uiid=:uiid')
            ->delete([':pid'=>$this->page->id, ':uiid'=>$uiid]);

        $meta = [];
        foreach($styles as $style){
            $bind = new Page_Bind_Style_Model();
            $bind->set('uuid', Page_Bind_Style_Model::uuid())
                ->set('uiid', $uiid)
                ->set('page_id', $this->page->id)
                ->set('style_id', $style->id)
                ->save();
            $meta = array_merge((array)$meta, (array)json_decode(html_entity_decode($style->meta)));
        }
        //把所有selector的样式合并后返回
        return YZE_JSON_View::success($this, ['meta'=>$meta?:null]);
    }
    // 加载uiitem 绑定的selector
    public function bind(){
        $request = $this->request;
        $this->layout = '';
        $uiid = trim($request->get_from_get("uiid"));
        $page_uuid = trim($request->get_from_get("page_uuid"));
        $this->valid($page_uuid);
        $data = [];
        foreach (Page_Bind_Style_Model::from('pbs')
            ->left_join(Style_Model::CLASS_NAME, 's', 's.id = pbs.style_id')
            ->where('pbs.page_id=:pid and pbs.uiid=:uiid')
            ->select([':pid'=>$this->page->id, ':uiid'=>$uiid]) as $item){
            $data[] = [
                'text'=>$item['s']->class_name,
                'id'=>$item['s']->uuid
            ];
        }
        return YZE_JSON_View::success($this, $data);
    }

    public function exception(\Exception $e){
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }
}
?>
