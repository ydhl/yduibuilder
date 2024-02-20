<?php
namespace app\project;
use app\common\Option_Model;
use app\user\User_Model;
use app\vendor\Env;
use yangzie\YZE_FatalException;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use function yangzie\__;

/**
 *
 *
 * @version $Id$
 * @package project
 */
trait Project_Model_Method{
    private $home_page;
    private $setting;
    public function get_home_page(){
        if (!$this->home_page && $this->home_page_id){
            $this->home_page = Page_Model::find_by_id($this->home_page_id);
        }
        return $this->home_page;
    }

    /**
     * 按module》function》page的格式返回页面
     * @return array [moduleid=>[uuid=>,name=>,function=>[functionid=>[uuid=>,name=>,page=>[PageModel]]]]
     */
    public function get_module_function_pages(){
        $params = [':pid'=>$this->id];
        $where = 'm.project_id=:pid and p.is_deleted=0 and m.is_deleted=0';
        $query = Page_Model::from('p')
            ->left_join(Module_Model::CLASS_NAME, 'm', 'm.id = p.module_id')
            ->left_join(Function_Model::CLASS_NAME, 'f', 'f.id = p.function_id')
            ->where($where);
        $pages = [];
        foreach ($query->select($params) as $obj){
            if (! $pages[$obj['m']->id]){
                $pages[$obj['m']->id] = ['uuid'=>$obj['m']->uuid, 'name'=>$obj['m']->name, 'function'=>[]];
            }
            if (!@$pages[$obj['m']->id]['function'][$obj['f']->id]){
                @$pages[$obj['m']->id]['function'][$obj['f']->id] = ['uuid'=>$obj['f']->uuid, 'name'=>$obj['f']->name, 'page'=>[]];
            }
            $pages[$obj['m']->id]['function'][$obj['f']->id]['page'][] = $obj['p'];
        }
        return $pages;
    }
    /**
     * 获取指定用户的project_member
     * @param $uid
     * @return Project_Member_Model
     */
    public function get_member($uid){
        return Project_Member_Model::from()->where('project_id=:id and user_id=:uid and is_deleted=0')->get_Single([':id'=>$this->id, ':uid'=>$uid]);
    }

    /**
     * 返回项目的所有成员
     * @return array
     */
    public function get_members(){
        $members = [];
        foreach( Project_Member_Model::from('m')
            ->left_join(User_Model::CLASS_NAME, 'u', 'u.id = m.user_id')
            ->where('m.project_id=:id and m.is_deleted=0 and u.is_deleted=0')
            ->select([':id'=>$this->id]) as $obj){
            $obj['m']->set_user($obj['u']);
            $members[] = $obj['m'];
        }
        return $members;
    }
    public function get_member_count(){
        return Project_Member_Model::from()->where('project_id=:id and is_deleted=0')->count('id', [':id'=>$this->id]);
    }
    public function can_edit($uid){
        $member = $this->get_member($uid);
        return ($member && $member->can_edit());
    }
    public function get_setting(){
        if (!$this->setting){
            $this->setting = [];
            foreach (Project_Setting_Model::from()->where('project_id=:id')->select([':id'=>$this->id]) as $item){
                if (in_array($item->name, ['color', 'colorDark', 'fontFace'])) {
                    $this->setting[$item->name] = json_decode(html_entity_decode($item->value));
                }else{
                    $this->setting[$item->name] = $item->value;
                }
            }
        }
        return $this->setting;
    }
    public function get_setting_value($name){
        $setting = $this->get_setting();
        return $setting[$name];
    }
    public function get_technology() {
        $setting = $this->get_setting();
        $info = [];
        if ($setting['frontend']) $info[] = $setting['frontend'];
        if ($setting['ui_version']) $info[] = $setting['ui'].'('.$setting['ui_version'].')';
        if ($setting['frontend_framework_version']) $info[] = $setting['frontend_framework'].'('.$setting['frontend_framework_version'].')';
        if ($setting['frontend_language_version']) $info[] = $setting['frontend_language'].'('.$setting['frontend_language_version'].')';
        if ($setting['backend']) $info[] = $setting['backend'];
        if ($setting['framework_version']) $info[] = $setting['framework'].'('.$setting['framework_version'].')';
        if ($setting['backend_language_version']) $info[] = $setting['backend_language'].'('.$setting['backend_language_version'].')';

        return join(' ', $info);
    }
    public function get_modules() {
        return Module_Model::from()->where('project_id=:id and is_deleted=0')->select([':id'=>$this->id]);
    }

    /**
     * 获取ui builder项目地址，地址以/结尾
     * @return string
     */
    public static function get_ui_builder_url(){
//        return 'http://localhost:9999/';
        return UI_BUILDER_URI;
    }

    /**
     * 为新注册的用户创建demo项目
     * @param $user_id
     */
    public static function createDemoProject($user_id){
        $project = new Project_Model();
        $project->set('name', __('Project Manage System (Demo)'))
            ->set('uuid', self::uuid())
            ->set('desc', __('You can edit this demo as you want'))
            ->set('end_kind', 'pc')
            ->save();
        $project_member = new Project_Member_Model();
        $project_member->set('uuid', self::uuid())
            ->set('user_id', $user_id)
            ->set('project_id', $project->id)
            ->set('role', 'admin')
            ->set('is_creater', 1)
            ->save();

        foreach ([
                     'ui'=>'bootstrap',// UI库
                     'ui_version'=>'4.6.0',
                     'frontend' => 'web',
                     'frontend_framework' => 'html5',
                     'frontend_framework_version' => '5.0',
                     'frontend_language'=>'javascript',
                     'frontend_language_version'=> 'ECMAScript 5'
                 ] as $name=>$value){
            $setting = new Project_Setting_Model();
            $setting->set('name', $name)
                ->set('value', $value)
                ->set('project_id', $project->id)
                ->set('uuid', self::uuid())
                ->save();
        }

        $project_module = new Module_Model();
        $project_module->set('uuid', self::uuid())
            ->set('name', __('Project Manage'))
            ->set('project_id', $project->id)
            ->set('desc', __('Project manage such as add, edit...'))
            ->set('folder', 'project')
            ->save();

        $function = new Function_Model();
        $function->set('uuid', self::uuid())
            ->set('name', __('Project Profile'))
            ->set('module_id', $project_module->id)
            ->set('desc', __('such as query export ....'))
            ->save();

        $pageConfig = [
            'type'=> 'Page',
            'meta'=>['id'=>'', 'isContainer'=> true, 'title'=>''],
            'items'=>[]
        ];
        $list_page = new Page_Model();
        $uuid = self::uuid();
        $pageConfig['meta']['id'] = $uuid;
        $pageConfig['meta']['title'] = __('Project List Page');
        $list_page->set('uuid', $uuid)
            ->set('name', __('Project List Page'))
            ->set('module_id', $project_module->id)
            ->set('function_id', $function->id)
            ->set('project_id', $project->id)
            ->set('create_user_id', $user_id)
            ->set('config', json_encode($pageConfig))
            ->set('file', 'project.html')
            ->save();

        $add_page = new Page_Model();
        $uuid = self::uuid();
        $pageConfig['meta']['id'] = $uuid;
        $pageConfig['meta']['title'] = __('Add Project Page');
        $add_page->set('uuid', $uuid)
            ->set('name', __('Add Project Page'))
            ->set('module_id', $project_module->id)
            ->set('function_id', $function->id)
            ->set('project_id', $project->id)
            ->set('create_user_id', $user_id)
            ->set('config', json_encode($pageConfig))
            ->set('file', 'add-project.html')
            ->save();

        $project_member->set(Project_Member_Model::F_LAST_FUNCTION_ID, $function->id)
            ->set(Project_Member_Model::F_LAST_PAGE_ID, $add_page->id)
        ->save();
        $project->set(Project_Model::F_LAST_FUNCTION_ID, $function->id)
            ->set(Project_Model::F_LAST_PAGE_ID, $add_page->id)
            ->save();

        $customer_module = new Module_Model();
        $customer_module->set('uuid', self::uuid())
            ->set('name', __('Customer Manage'))
            ->set('project_id', $project->id)
            ->set('desc', __('Customer info manage, such as add, edit'))
            ->set('folder', 'customer')
            ->save();
        $function = new Function_Model();
        $function->set('uuid', self::uuid())
            ->set('name', __('Customer Profile'))
            ->set('module_id', $customer_module->id)
            ->set('desc', __('List, Search, Export....'))
            ->save();

        $list_page = new Page_Model();
        $uuid = self::uuid();
        $pageConfig['meta']['id'] = $uuid;
        $pageConfig['meta']['title'] = __('Customer List Page');
        $list_page->set('uuid', $uuid)
            ->set('name', __('Customer List Page'))
            ->set('module_id', $customer_module->id)
            ->set('function_id', $function->id)
            ->set('project_id', $project->id)
            ->set('create_user_id', $user_id)
            ->set('config', json_encode($pageConfig))
            ->set('file', 'customer.html')
            ->save();

        $add_page = new Page_Model();
        $uuid = self::uuid();
        $pageConfig['meta']['id'] = $uuid;
        $pageConfig['meta']['title'] = __('Add Customer Page');
        $add_page->set('uuid', $uuid)
            ->set('name', __('Add Customer Page'))
            ->set('module_id', $customer_module->id)
            ->set('function_id', $function->id)
            ->set('project_id', $project->id)
            ->set('create_user_id', $user_id)
            ->set('config', json_encode($pageConfig))
            ->set('file', 'add-customer.html')
            ->save();


    }

    /**
     * 获取iconfont的icon
     * @return array|mixed
     */
    public function get_icons(){
        $iconfont = YZE_UPLOAD_PATH.'project/'.$this->uuid.'/iconfont/iconfont.css';
        if (!file_exists($iconfont)) return [];
        $file_content = file_get_contents($iconfont);
        preg_match_all("/\.icon-(?P<icon>[^:]+):/m", $file_content, $matches);
        return $matches['icon'];
    }
    /**
     * 取得项目前端使用的包，包含环境自带的system包（如使用的库默认依赖的包）和用户自己添加的user包,<br/>
     * 按依赖的先后顺序进行返回, system中的包不能删除，user中包可增加，删除
     * <br/>
     * <br/>
     * 每个包的格式为包名@版本号
     * <br/>
     * <br/>
     * @return array ['system'=>[],'user'=>[]]
     */
    public function get_front_project_packages() {
        $project_setting = $this->get_setting();
        $return = ['system'=>[], 'user'=>[]];

        if ($project_setting['ui']) $return['system'] = array_merge($return['system'], Env::pickDependencyPackage($project_setting['ui']."@".$project_setting['ui_version']));
        if ($project_setting['framework']) $return['system'] = array_merge($return['system'], Env::pickDependencyPackage($project_setting['framework']."@".$project_setting['framework_version']));
        if ($project_setting['frontend_framework']) $return['system'] = array_merge($return['system'], Env::pickDependencyPackage($project_setting['frontend_framework']."@".$project_setting['frontend_framework_version']));
        if ($project_setting['backend_language']) $return['system'] = array_merge($return['system'], Env::pickDependencyPackage($project_setting['backend_language']."@".$project_setting['backend_language_version']));
        if ($project_setting['frontend_language']) $return['system'] = array_merge($return['system'], Env::pickDependencyPackage($project_setting['frontend_language']."@".$project_setting['frontend_language_version']));

        // 数据库中查找用户添加的其他依赖
        // 用户添加的依赖，编译器并不知道怎么生成使用代码，所以去掉用户添加依赖的功能
        $return['user'] = [];
        return $return;
    }

    /**
     * 取得项目后端使用的包，包含环境自带的fix包（如使用的库默认依赖的包）和用户自己添加的user包,<br/>
     * 按依赖的先后顺序进行返回, fix中的包不能删除，user中包可增加，删除
     * <br/>
     * <br/>
     * 每个包的格式为包名@版本号
     * <br/>
     * <br/>
     * @return array ['fix'=>[],'user'=>[]]
     */
    public function get_backend_project_packages() {
        $project_setting = $this->get_setting();
    }
    public function recyclePageCount() {
        return Page_Model::from('p')
            ->left_join(Module_Model::CLASS_NAME, 'm', 'm.id = p.module_id')
            ->where('p.is_deleted=1 and m.project_id=:pid and p.page_type="page"')->count('id', [':pid'=>$this->id]);
    }
}?>
