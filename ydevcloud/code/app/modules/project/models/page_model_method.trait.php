<?php
namespace app\project;
use app\user\User_Model;
use yangzie\YZE_FatalException;
use yangzie\YZE_JSON_View;
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
trait Page_Model_Method{
    private $versionCount;
    private $versions;
    private static $subPages = [];
    public function set_sub_page(Page_Model $subPage){
        $this->subPages[$subPage->uuid] = $subPage;
    }
    public function get_sub_page($subPage){
        return $this->subPages[$subPage->uuid];
    }
    public function get_Version_Count() {
        if (!isset($this->versionCount)){
            $this->versionCount = Page_Version_Model::from()->where('page_id=:pid')->count('id', [':pid'=>$this->id]);
        }
        return $this->versionCount;
    }
    public function get_versions() {
        if (!isset($this->versions)){
            $this->versions = Page_Version_Model::from()->where('page_id=:pid')->order_By('id', 'desc')->select([':pid'=>$this->id]);
        }
        return $this->versions;
    }
    public function remove_gone_uiid(){
        Page_Bind_Event_Model::remove_gone_uiid($this);// 连带删除关联的bind api，bind api action，bind variable，action，mutation
        Page_Bind_Io_Model::remove_gone_uiid($this);
        Page_Bind_Style_Model::remove_gone_uiid($this);
        Uicomponent_Instance_Model::remove_gone_uiid($this);
        Page_Bind_Api_Model::remove_gone_uiid($this);
    }
    public function remove() {
        Page_User_Model::from()->where('page_id=:pid')->delete([':pid'=>$this->id]);
        Page_Version_Model::from()->where('page_id=:pid')->delete([':pid'=>$this->id]);
        Page_Bind_Io_Model::from()->where('page_id=:pid')->delete([':pid'=>$this->id]);
        Page_Bind_Data_Model::from()->where('page_id=:pid')->delete([':pid'=>$this->id]);
        foreach (Action_Model::from()->where('page_id=:pid')->select([':pid'=>$this->id]) as $action){
            $action->remove();
        }
        Uicomponent_Event_Model::from()->where('page_id=:pid')->delete([':pid'=>$this->id]);
        Page_Bind_Event_Model::from()->where('page_id=:pid')->delete([':pid'=>$this->id]);
        Page_Bind_Style_Model::from()->where('page_id=:pid')->delete([':pid'=>$this->id]);
        Page_Bind_API_Action_Model::from()->where('page_id=:pid')->delete([':pid'=>$this->id]);
        Page_Bind_Api_Model::from()->where('page_id=:pid')->delete([':pid'=>$this->id]);
        Uicomponent_Instance_Model::from()->where('page_id=:pid')->delete([':pid'=>$this->id]);
        Page_Bind_State_Model::from()->where('page_id=:pid')->delete([':pid'=>$this->id]);
        Page_Bind_Variable_Model::from()->where('from_page_id=:pid')->delete([':pid'=>$this->id]);
        Page_Bind_Variable_Model::from()->where('to_page_id=:pid')->delete([':pid'=>$this->id]);
        parent::remove();
    }

    public function fetchSubPageIds($config=null, &$subPageIds=[]){
        if (!$config){
            $config = json_decode(html_entity_decode($this->config));// 不能转化成关联数组，这回导致空对象的{}json_encode被转化成[]
        }
        if ($config->subPageId){
            $subPageIds[] = $config->subPageId;
        }
        foreach ((array)$config->items as $item){
            $this->fetchSubPageIds($item, $subPageIds);
        }
    }

    private function replaceSubpage($config, $subPageId, $subpageConfig){
        if ($config->subPageId == $subPageId){
            // 只复制目标页面的内容
            $config->type = $subpageConfig->type;
            $config->pageType = $subpageConfig->pageType;
            $config->meta = $subpageConfig->meta;
            $config->items = $subpageConfig->items;
            $config->events = $subpageConfig->events;
            return $config;
        }
        foreach ((array)$config->items as $index=>$item){
            $config->items[$index] = $this->replaceSubpage($item, $subPageId, $subpageConfig);
        }
        return $config;
    }

    private function markSubpageDeleted($config, $subPageId){
        if ($config->subPageId == $subPageId){
            $config->subPageDeleted = true;
            return $config;
        }
        foreach ((array)$config->items as $index=>$item){
            $config->items[$index] = $this->markSubpageDeleted($item, $subPageId);
        }
        return $config;
    }

    /**
     * 返回uibase对象
     * @return mixed
     */
    public function get_ui_config(){
        return json_decode(html_entity_decode($this->config));// 不能转化成关联数组，这回导致空对象的{}json_encode被转化成[]
    }

    private function find_sub_pages($subPageIds) {
        $pages = [];
        $notFoundPageIds = [];
        foreach ($subPageIds as $subPageId){
            if (self::$subPages[$subPageId]) {
                $pages[$subPageId] = self::$subPages[$subPageId];
            }else{
                $notFoundPageIds[] = $subPageId;
            }
        }
        if ($notFoundPageIds){
            $pages = array_merge($pages, find_by_uuids(Page_Model::CLASS_NAME, $notFoundPageIds));
        }
        if ($pages){
            self::$subPages = array_merge(self::$subPages, $pages);
        }
        return $pages;
    }

    /**
     * 获取page 的config，主要是对于item有通过subPageId引用其他页面的情况下，把引用页面的配置获取后更新到对应的item中;
     * 同时返回对应的style
     *
     * @return YZE_JSON_View
     */
    public function get_config(){
        static $fetchedConfig = [];
        if ($fetchedConfig[$this->uuid]){
            return $fetchedConfig[$this->uuid];
        }
        $config = json_decode(html_entity_decode($this->config));// 不能转化成关联数组，这回导致空对象的{}json_encode被转化成[]
        $subPageIds = [];
        $this->fetchSubPageIds($config, $subPageIds);
        $subPageIds = array_unique(array_diff($subPageIds, [$this->uuid]));// 排除自己，组件自己使用自己的情况时死循环：这时组件就用config中的内容，不用在调用get config
        if ($subPageIds){
            $pages = $this->find_sub_pages($subPageIds);
            foreach ($subPageIds as $subPageId){
                $page = $pages[$subPageId];
                if ($page){
                    $pageConfig = $page->get_config();
                    $config = $this->replaceSubpage($config, $page->uuid, $pageConfig);
                }else{ // 页面被删除了
                    $config = $this->markSubpageDeleted($config, $subPageId);
                }
            }
        }

        $dataBind = ['in'=>[],'out'=>[],'bound'=>[]];
        foreach (Page_Bind_Io_Model::from('io')
                ->left_join(Page_Bind_Data_Model::CLASS_NAME, 'd', 'd.uuid = io.from_uuid and io.from_class=:cls')
                ->where('d.page_id=:pid and io.page_id=:pid and io.is_deleted=0 and d.is_deleted=0')
                ->select([":pid"=>$this->id, ':cls'=>Page_Bind_Data_Model::class]) as $info){

            $bindDataModel = $info['d'];
            $allParents = [];
            $path = [];
            $bindData = $bindDataModel->find_data($info['io']->data_id, $bindDataModel->get_data_model(), $allParents, $path);
            if($bindData['name'])array_unshift($path, $bindData['name']);
            $path = array_reverse(array_filter($path));
            if ($info['io']->type == 'in') {
                $dataBind['in'][$info['io']->uiid] = [
                    'path' => join('.', $path)
                ];
            }else{
                $dataBind['out'][$info['io']->uiid][$info['io']->output_as] = join('.', $path);
                $dataBind['bound'][$info['io']->uiid][$info['io']->bound_as] = join('.', $path);
            }
        }
        $this->merge_data_bind($config, $dataBind);

        $events = [];
        foreach (Page_Bind_Event_Model::from()->where('page_id=:pid and is_deleted=0')->select([":pid"=>$this->id]) as $event){
            foreach (explode(',', $event->uiid) as $uiid){
                if (!$uiid) continue;
                if (!$events[$uiid])$events[$uiid] = [];
                $events[$uiid][] = $event->uuid;
            }
        }
        $this->merge_event($config, $events);

        $styles = [];
        foreach (Page_Bind_Style_Model::from('bs')
                     ->left_join(Style_Model::CLASS_NAME,'s', 's.id=bs.style_id')
                     ->where('bs.page_id=:pid and bs.is_deleted=0 and s.is_deleted=0')
            ->group_By('style_id','bs')
            ->select([':pid'=>$this->id]) as $item){
            $uiid = $item['bs']->uiid;
            if (!$uiid) continue;
            if (!$styles[$uiid]) $styles[$uiid] = [];
            // json_decode 不能带解码成关联数组，因为json中的空对象{}会被解码成php数组[]
            $metas = json_decode(html_entity_decode($item['s']->meta));
            foreach ($metas as $key => $value){
                if (!$styles[$uiid][$key]) {
                    $styles[$uiid][$key] = $value;
                }else{
                    $styles[$uiid][$key] = array_merge((array)$styles[$uiid][$key], (array)$value);
                }
            }
        }

        $this->merge_selector($config, $styles);
        $fetchedConfig[$this->uuid] = $config;
        return $config;
    }
    private function merge_event(&$config, $events){
        if ($events[$config->meta->id]){
            $config->events = $events[$config->meta->id];
        }
        foreach ((array)$config->items as &$subconfig){
            $this->merge_event($subconfig, $events);
        }
    }
    private function merge_data_bind(&$config, $dataBind){
        if ($dataBind['in'][$config->meta->id]){
            $config->dataIn = $dataBind['in'][$config->meta->id];
        }
        if ($dataBind['out'][$config->meta->id]){
            $config->dataOut = $dataBind['out'][$config->meta->id];
        }
        if ($dataBind['bound'][$config->meta->id]){
            $config->dataBound = $dataBind['bound'][$config->meta->id];
        }
        foreach ((array)$config->items as &$subconfig){
            $this->merge_data_bind($subconfig, $dataBind);
        }
    }
    private function merge_selector(&$config, $styles){
        if ($styles[$config->meta->id]){
            $config->meta->selector = $styles[$config->meta->id];
        }
        foreach ((array)$config->items as &$subconfig){
            $this->merge_selector($subconfig, $styles);
        }
    }

    // 这里实现model的业务方法
    public function get_last_version(){
        $objs =  Page_Version_Model::from('pv')
            ->left_join(Project_Member_Model::CLASS_NAME, 'pm', 'pm.id = pv.project_member_id')
            ->left_join(User_Model::CLASS_NAME, 'u', 'pm.user_id = u.id')
            ->where('pv.page_id = :pid and pv.id=:pvid')
            ->get_Single([':pid'=>$this->id, ':pvid'=>$this->last_version_id]);
        if (@!$objs['pv']) return null;
        $objs['pm']->set_user($objs['u']);
        $objs['pv']->set_project_member($objs['pm']);
        return $objs['pv'];
    }

    /**
     * 导出文件名，不包含扩展名
     * html html5框架下的html文件
     * vue vue单文件
     * wxmp 微信文件
     *
     * @param $target
     * @return string
     */
    public function get_export_file_name($target){
        if (strtolower($target) == "html"){
            return $this->file ?: "Page{$this->id}";
        }
        if (strtolower($target) == "vue"){
            return ($this->file ?: "Page{$this->id}");
        }
        if (strtolower($target) == "wxmp"){
            return ($this->file ?: "Page{$this->id}");
        }
    }

    /**
     * 返回页面在对应的项目中的保存路径，是参考于项目根目录的路径，使用的地方根据情况可能需要加上/,./等之类等变成相对路径
     * html html5框架下的html文件
     * vue vue单文件
     * wxmp 微信文件
     *
     * @return string
     */
    public function get_save_path($target){
        if (strtolower($target) == "html"){
            if ($this->page_type != 'page'){
                return $this->page_type.'/' . $this->get_export_file_name($target).".html";
            }else{
                $module = $this->get_module();
                $folder = $module->folder ?: $module->name;
                $page_file = $folder . '/' . $this->get_export_file_name($target).".html";
                return $page_file;
            }
        }
        if (strtolower($target) == "vue"){
            $module = $this->get_module();
            $moduleName = $module->folder ?: 'module'.$module->id;
            $folder = 'views/'.$moduleName;
            $pageName = $this->file ?: ($this->get_export_file_name($target).".vue");
            return $folder . '/' . $pageName;
        }
        if (strtolower($target) == "wxmp"){
            $module = $this->get_module();
            $moduleName = $module->folder ?: 'module'.$module->id;
            $folder = 'pages/'.$moduleName;
            $pageName = ($this->file ?: $this->get_export_file_name($target));
            return  $folder . '/' . $pageName;
        }
    }
    public static function findUiItem($config, $uiid) {
        if ($config->meta->id == $uiid) return $config;
        foreach ($config->items as $item){
            $finded = self::findUiItem($item, $uiid);
            if ($finded) return $finded;
        }
        return null;
    }

    /**
     * 查找当前页面中的指定id的元素。
     *
     * 注意该方法从该页面的config中查找，如果页面有通过subPageId应用其他组件的情况，可能存在其他组件改变了，但该页面中的config没有改变
     * 的情况，这时可以通过get_config()把完整的config取出来后通过Page_Model::findUiItem($config, $uiid)来查找
     *
     * @param $uiid
     * @return mixed|null
     */
    public function find_ui_item($uiid) {
        $config = json_decode(html_entity_decode($this->config));
        return self::findUiItem($config, $uiid);
    }

    /**
     * 找出config中所有ui的id数组和引用的subpageid
     * @param $config array uiconfig 对象
     * @param $uiids array 返回uiid和其type[uiid=>type]
     * @param $subPageId array 返回包含的subpageid及其父ui的uiid
     * @param $parent_id string $subPageId的父uiid
     * @return array [id=>type]
     */
    public static function all_sub_item_uiid($config, &$uiids = [], &$subPageId=[], $parent_id=''){
        $uiids[$config->meta->id] = $config->type;
        if ($config->subPageId){
            $subPageId[$config->subPageId] = $parent_id;
        }
        if ($config->items){
            foreach ($config->items as $item){
                self::all_sub_item_uiid($item, $uiids, $subPageId, $config->meta->id);
            }
        }
    }

    /**
     * 把uiconfig中所有ui的id替换成$oldId2newId中的id，如果$oldId2newId中没有，则不替换
     *
     * @param $config uiconfig对象
     * @param $oldId2newId
     * @return object
     */
    public static function replace_uiid($config, $oldId2newId){
        $config->meta->id = $oldId2newId[$config->meta->id] ?: $config->meta->id;
        if ($config->items) {
            foreach ($config->items as $index=>$item) {
                $config->items[$index] = self::replace_uiid($item, $oldId2newId);
            }
        }
        return $config;
    }
    public function get_instance_count(){
        return Uicomponent_Instance_Model::from()->where('is_deleted=0 and uicomponent_page_id=:pid')->count('id', [':pid'=>$this->id]);
    }

    /**
     * 从指定的$config节点查找uuid的父级，从上往下找
     *
     * @param $uiid
     * @param $index
     * @param $config stdClass
     * @return mixed|null
     */
    public function find_parent($uiid, &$index, $config) {
        foreach ((array)@$config->items as $i => $item){
            if ($item->meta->id == $uiid) {
                $index = $i;
                return $config;
            }
            $finded = $this->find_parent($uiid, $index, $item);
            if ($finded) {
                return $finded;
            }
        }
        $index = -1;
        return null;
    }

    /**
     * 检查check uuid是否是parent uiid的下级元素
     * @param $check_uiid
     * @param $parent_ui_Config stdClass
     * @return bool
     */
    public function is_sub_ui($check_uiid, $parent_ui_Config){
        if (!$parent_ui_Config) return false;
        foreach ($parent_ui_Config->items as $ui){
            if ($ui->meta->id == $check_uiid) return true;
            if ($ui->items){
               $rst = $this->is_sub_ui($check_uiid, $ui);
               if ($rst){
                   return true;
               }
            }
        }
        return false;
    }

    private function copy_bind_style($copyFromPage, $old2newIDs, $renameUIID=true) {
        $dba = YZE_DBAImpl::get_instance();
        $models = Page_Bind_Style_Model::from()->where('page_id=:pid and is_deleted=0')->select([':pid'=>$copyFromPage->id]);

        $columns = Page_Bind_Style_Model::$columns;
        unset($columns['id'],$columns['created_on'],$columns['modified_on']);
        $columns = array_keys($columns);

        $sql = "insert into page_bind_style(".join(',', $columns).") values ";
        $values = [];
        foreach ($models as $model){
            if ($renameUIID && !$old2newIDs[$model->uiid]) continue;
            $set = $model->get_records();
            unset($set['id'],$set['created_on'],$set['modified_on']);
            $set['uiid'] = $old2newIDs[$model->uiid]?:$model->uiid;
            $set = array_map(function($item){
                return is_null($item) ? 'NULL' : "'{$item}'";
            }, $set);
            $set['page_id'] = $this->id;
            $set['uuid'] = 'uuid()';

            $values[] = "(".join(',', array_values($set)).")";
        }
        if ($values){
            $dba->exec($sql.join(',', $values));
        }
    }
    private function copy_uicomponent_instance($copyFromPage, $old2newIDs) {
        $dba = YZE_DBAImpl::get_instance();
        $models = Uicomponent_Instance_Model::from()->where('page_id=:pid and is_deleted=0')->select([':pid'=>$copyFromPage->id]);

        $columns = Uicomponent_Instance_Model::$columns;
        unset($columns['id'],$columns['created_on'],$columns['modified_on']);
        $columns = array_keys($columns);

        $sql = "insert into uicomponent_instance(".join(',', $columns).") values ";
        $values = [];
        foreach ($models as $model){
            if (!$old2newIDs[$model->instance_uuid]) continue;

            $set = $model->get_records();
            unset($set['id'],$set['created_on'],$set['modified_on']);
            $set['instance_uuid'] = $old2newIDs[$model->instance_uuid];
            $set = array_map(function($item){
                return is_null($item) ? 'NULL' : "'{$item}'";
            }, $set);
            $set['uuid'] = 'uuid()';
            $set['page_id'] = $this->id;

            $values[] = "(".join(',', array_values($set)).")";
        }
        if ($values){
            $dba->exec($sql.join(',', $values));
        }
    }
    // 复制的页面是组件页面时
    private function copy_uicomponent_event($copyFromPage, &$oldId2new){
        $models = Uicomponent_Event_Model::from()->where('page_id=:pid and is_deleted=0')->select([':pid'=>$copyFromPage->id]);
        foreach ($models as $model){
            $data = $model->get_records();
            unset($data['id'], $data['created_on'], $data['modified_on'], $data['uuid']);
            $data['uuid'] = Uicomponent_Event_Model::uuid();
            $data['page_id'] = $this->id;
            $newModel = new Uicomponent_Event_Model();
            $newModel->save_from_data($data);
            $oldId2new[Uicomponent_Event_Model::CLASS_NAME][$model->id] = $newModel->id;
        }
    }
    private function copy_bind_data($copyFromPage, &$oldClass2new){
        $models = Page_Bind_Data_Model::from()->where('page_id=:pid and is_deleted=0')->select([':pid'=>$copyFromPage->id]);
        foreach ($models as $model){
            $data = $model->get_records();
            unset($data['id'], $data['created_on'], $data['modified_on'], $data['uuid']);
            $data['uuid'] = Page_Bind_Data_Model::uuid();
            $data['page_id'] = $this->id;
            $newModel = new Page_Bind_Data_Model();
            $newModel->save_from_data($data);
            $oldClass2new[Page_Bind_Data_Model::CLASS_NAME][$model->uuid] = $newModel->uuid;
        }
    }
    private function copy_bind_event($copyFromPage, $old2newIDs, $oldId2new, &$oldClass2new, $renameUIID=true){
        $models = Page_Bind_Event_Model::from()->where('page_id=:pid and is_deleted=0')->select([':pid'=>$copyFromPage->id]);
        foreach ($models as $model){
            $data = $model->get_records();
            unset($data['id'], $data['created_on'], $data['modified_on'], $data['uuid']);
            $data['uuid'] = Page_Bind_Event_Model::uuid();
            $data['uiid'] = $renameUIID ? ($old2newIDs[$model->uiid]?:'') : $model->uiid;
            $data['page_id'] = $this->id;
            $data['uicomponent_event_id'] = $oldId2new[Uicomponent_Event_Model::CLASS_NAME][$model->uicomponent_event_id] ?: $model->uicomponent_event_id;
            $newModel = new Page_Bind_Event_Model();
            $newModel->save_from_data($data);
            $oldClass2new[Page_Bind_Event_Model::CLASS_NAME][$model->uuid] = $newModel->uuid;
        }
    }
    private function copy_bind_io($copyFromPage, $old2newIDs, &$oldClass2new, $renameUIID=true){
        $dba = YZE_DBAImpl::get_instance();
        $models = Page_Bind_Io_Model::from()->where('page_id=:pid and is_deleted=0')->select([':pid'=>$copyFromPage->id]);

        $columns = Page_Bind_Io_Model::$columns;
        unset($columns['id'],$columns['created_on'],$columns['modified_on']);
        $columns = array_keys($columns);

        $sql = "insert into page_bind_io(".join(',', $columns).") values ";
        $values = [];
        foreach ($models as $model){
            if ($renameUIID && !$old2newIDs[$model->uiid]) continue;
            $from_uuid = $oldClass2new[$model->from_class][$model->from_uuid];
            if (!$from_uuid) continue;

            $set = $model->get_records();
            unset($set['id'],$set['created_on'],$set['modified_on']);
            $set['from_uuid'] = $from_uuid;
            $set['data_id'] = $model->data_id == $model->from_uuid ? $from_uuid : $model->data_id;// 单纯一个数据，没有下级数据对情况
            $set['from_class'] = addslashes($model->from_class);// 对\转义成\\
            $set['uiid'] = $old2newIDs[$model->uiid] ?: $model->uiid;
            $set = array_map(function($item){
                return is_null($item) ? 'NULL' : "'{$item}'";
            }, $set);
            $set['uuid'] = 'uuid()';
            $set['page_id'] = $this->id;

            $values[] = "(".join(',', array_values($set)).")";
        }
        if ($values){
            $dba->exec($sql.join(',', $values));
        }
    }
    private function copy_bind_state($copyFromPage, $old2newIDs, &$oldClass2new, $renameUIID=true){
        $dba = YZE_DBAImpl::get_instance();
        $models = Page_Bind_State_Model::from()->where('page_id=:pid and is_deleted=0')->select([':pid'=>$copyFromPage->id]);
        $columns = Page_Bind_State_Model::$columns;
        unset($columns['id'],$columns['created_on'],$columns['modified_on']);
        $columns = array_keys($columns);
        $sql = "insert into page_bind_state(".join(',',$columns).") values ";
        $values = [];
        foreach ($models as $model){
            if ($renameUIID && !$old2newIDs[$model->uiid]) continue;

            $set = $model->get_records();
            unset($set['id'],$set['created_on'],$set['modified_on']);
            $set['uiid'] = $old2newIDs[$model->uiid] ?: $model->uiid;
            $set['style'] = html_entity_decode($model->style);
            $set['expression'] = html_entity_decode($model->expression);
            $set = array_map(function($item){
                return is_null($item) ? 'NULL' : "'{$item}'";
            }, $set);
            $set['uuid'] = 'uuid()';
            $set['page_id'] = $this->id;

            $values[] = "(".join(',', array_values($set)).")";
        }
        if ($values){
            $dba->exec($sql.join(',', $values));
        }
    }
    private function copy_bind_variable($copyFromPage, &$oldClass2new){
        $dba = YZE_DBAImpl::get_instance();
        //数据绑定有两种情况：
        //case 1 同一页面中的数据绑定，to_page_id = from_page_id, 这时copy时from和to都需要处理
        //case 2 页面向子页面绑定数据，to_sub_page_id = from_main_page_id，这时只处理from
        $models = Page_Bind_Variable_Model::from()->where('from_page_id=:pid and is_deleted=0')->select([':pid'=>$copyFromPage->id]);
        $columns = Page_Bind_Variable_Model::$columns;
        unset($columns['id'],$columns['created_on'],$columns['modified_on']);
        $columns = array_keys($columns);
        $sql = "insert into page_bind_variable(".join(',',$columns).") values ";
        $values = [];
        foreach ($models as $model){
            $from_uuid = $oldClass2new[$model->from_class][$model->from_uuid];
            $to_uuid = $oldClass2new[$model->to_class][$model->to_uuid]?:$model->to_uuid;
            if (!$from_uuid) continue;

            $set = $model->get_records();
            unset($set['id'],$set['created_on'],$set['modified_on']);
            $set['from_uuid'] = $from_uuid;
            $set['from_page_id'] = $this->id;
            $set['from_expression'] = html_entity_decode($model->from_expression);

            //case 1的情况下，to的相关信息也进行copy
            if ($model->from_page_id == $model->to_page_id){
                $set['to_uuid'] = $to_uuid;
                $set['to_page_id'] = $this->id;
                $set['to_data_id'] = $model->to_data_id == $model->to_uuid ? $to_uuid : $model->to_data_id;
            }

            $set['to_class'] = addslashes($model->to_class);
            $set['from_class'] = addslashes($model->from_class);
            $set = array_map(function($item){
                return is_null($item) ? 'NULL' : "'{$item}'";
            }, $set);
            $set['uuid'] = 'uuid()';

            $values[] = "(".join(',', $set).")";
        }
        if ($values){
            $dba->exec($sql.join(',', $values));
        }
    }
    private function copy_bind_api_action($copyFromPage, &$oldClass2new){
        $models = Page_Bind_API_Action_Model::from()->where('page_id=:pid and is_deleted=0')->select([':pid'=>$copyFromPage->id]);
        foreach ($models as $model){
            $from_uuid = $oldClass2new[$model->from_class][$model->from_uuid];
            if (!$from_uuid) continue;
            $data = $model->get_records();
            unset($data['id'], $data['created_on'], $data['modified_on'], $data['uuid']);
            $data['uuid'] = Page_Bind_API_Action_Model::uuid();
            $data['from_uuid'] = $from_uuid;
            $data['page_id'] = $this->id;
            $newModel = new Page_Bind_API_Action_Model();
            $newModel->save_from_data($data);
            $oldClass2new[Page_Bind_API_Action_Model::CLASS_NAME][$model->uuid] = $newModel->uuid;
        }
    }

    /**
     * api和api action是互相关联的关系：
     * <pre>
     * api---包含-->api action
     *  ↑           ｜
     *   ----触发-----
     * </pre>
     *
     * @param $copyFromPage
     * @param $oldClass2new
     * @param $old2newIDs
     * @return void
     * @throws YZE_DBAException
     */
    private function copy_bind_api_and_action($copyFromPage, &$oldUUID2new, &$oldID2new){
        $models = Page_Bind_Api_Model::from()
            ->where('page_id=:pid and is_deleted=0')
            ->select([':pid'=>$copyFromPage->id]);
        $unbindActionUuid = [];
        foreach ($models as $model){
            $bind_uuid = $oldUUID2new[$model->bind_class][$model->bind_uuid];
            // bind api action还没有copy，这时$oldUUID2new中没有内容，先复制，在后面再次更新
            if (!$bind_uuid && $model->bind_class != Page_Bind_Api_Action_Model::CLASS_NAME) continue;
            $data = $model->get_records();
            unset($data['id'], $data['created_on'], $data['modified_on'], $data['uuid']);
            $data['uuid'] = Page_Bind_Api_Model::uuid();
            $data['bind_uuid'] = $bind_uuid;
            $data['page_id'] = $this->id;
            $newModel = new Page_Bind_Api_Model();
            $newModel->save_from_data($data);
            $oldID2new[Page_Bind_Api_Model::CLASS_NAME][$model->id] = $newModel->id;
            $oldUUID2new[Page_Bind_Api_Model::CLASS_NAME][$model->uuid] = $newModel->uuid;
            if (!$bind_uuid) {
                $unbindActionUuid[$model->bind_uuid][] = $newModel;
            }
        }

        $models = Page_Bind_Api_Action_Model::from()
            ->where('page_id=:pid and is_deleted=0')
            ->select([':pid'=>$copyFromPage->id]);
        foreach ($models as $model){
            $from_uuid = $oldUUID2new[$model->from_class][$model->from_uuid];
            if (!$from_uuid) continue;
            $data = $model->get_records();
            unset($data['id'], $data['created_on'], $data['modified_on'], $data['uuid']);
            $data['uuid'] = Page_Bind_Api_Action_Model::uuid();
            $data['from_uuid'] = $from_uuid;
            $data['page_id'] = $this->id;
            $newModel = new Page_Bind_Api_Action_Model();
            $newModel->save_from_data($data);
            $oldID2new[Page_Bind_Api_Action_Model::CLASS_NAME][$model->id] = $newModel->id;
            $oldUUID2new[Page_Bind_Api_Action_Model::CLASS_NAME][$model->uuid] = $newModel->uuid;
        }

        foreach ($unbindActionUuid as $oldActionUuid=>$newModels) {
            foreach ($newModels as $newModel) {
                $newUuid = $oldUUID2new[Page_Bind_Api_Action_Model::CLASS_NAME][$oldActionUuid];
                if($newUuid) $newModel->set('bind_uuid', $newUuid)->save();
            }
        }
    }
    private function copy_action($copyFromPage, $oldId2new, &$oldClass2new){
        $models = Action_Model::from()->where('page_id=:pid and is_deleted=0')->select([':pid'=>$copyFromPage->id]);
        foreach ($models as $model){
            $bind_uuid = $oldClass2new[$model->bind_class][$model->bind_uuid];
            $uiComponentEventId = $oldId2new[Uicomponent_Event_Model::CLASS_NAME][$model->emit_event_id]?:$model->emit_event_id;
            $bind_api_id = $oldId2new[Page_Bind_Api_Model::CLASS_NAME][$model->bind_api_id]?:0;
            if (!$bind_uuid) continue;
            $data = $model->get_records();
            unset($data['id'], $data['created_on'], $data['modified_on'], $data['uuid']);
            $data['uuid'] = Action_Model::uuid();
            $data['bind_uuid'] = $bind_uuid;
            $data['emit_event_id'] = $uiComponentEventId;
            $data['bind_api_id'] = $bind_api_id;
            $data['page_id'] = $this->id;
            $newModel = new Action_Model();
            $newModel->save_from_data($data);

            foreach($model->get_mutations() as $mutation){
                $mutation_from_uuid = $oldClass2new[Page_Bind_Data_Model::CLASS_NAME][$mutation->mutation_from_uuid];
                if (!$mutation_from_uuid) continue;
                $connect_from_uuid = $oldClass2new[Page_Bind_Data_Model::CLASS_NAME][$mutation->connect_from_uuid];

                $mutationData = $mutation->get_records();
                unset($mutationData['id'], $mutationData['created_on'], $mutationData['modified_on'], $mutationData['uuid']);
                $mutationData['uuid'] = Mutation_Model::uuid();
                $mutationData['mutation_from_uuid'] = $mutation_from_uuid;
                $mutationData['mutation_data_id'] = $mutation->mutation_data_id==$mutation->mutation_from_uuid ? $mutation_from_uuid : $mutation->mutation_data_id;
                $mutationData['connect_data_id'] = $mutation->connect_data_id==$mutation->connect_from_uuid ? $connect_from_uuid : $mutation->connect_data_id;
                $mutationData['connect_from_uuid'] = $connect_from_uuid?:'';
                $mutationData['action_id'] = $newModel->id;
                $newMutation = new Mutation_Model();
                $newMutation->save_from_data($mutationData);
            }
        }
    }

    /**
     * 复制页面
     * @param $copyFromPage Page_Model 要复制的页面
     * @param $old2newIDs array uuid旧到新到映射
     * @param $renameUIID boolean 是否要重命名UIID
     * @return void
     * @throws YZE_DBAException
     */
    public function copy_bind_from($copyFromPage, $oldUI2new, $renameUIID=true){
        $oldUuid2new = [];
        $oldId2new = [];
        $this->copy_bind_style($copyFromPage, $oldUI2new, $renameUIID);
        $this->copy_uicomponent_instance($copyFromPage, $oldUI2new);
        $this->copy_uicomponent_event($copyFromPage, $oldId2new);
        $this->copy_bind_data($copyFromPage, $oldUuid2new);
        $this->copy_bind_io($copyFromPage, $oldUI2new, $oldUuid2new, $renameUIID);
        $this->copy_bind_state($copyFromPage, $oldUI2new, $oldUuid2new, $renameUIID);
        $this->copy_bind_event($copyFromPage, $oldUI2new, $oldId2new, $oldUuid2new, $renameUIID);

        $this->copy_bind_api_and_action($copyFromPage, $oldUuid2new, $oldId2new);

        $this->copy_bind_variable($copyFromPage, $oldUuid2new);
        $this->copy_action($copyFromPage, $oldId2new, $oldUuid2new);
    }

    /**
     * 某些数据是便于前端处理用的，在前端拉取时每次都会重新获取，所以在保存时可调用该方法去掉
     * @param $uiconfig
     * @return mixed
     */
    public static function remove_node(&$uiconfig) {
        unset($uiconfig->dataIn, $uiconfig->dataOut, $uiconfig->dataBound, $uiconfig->events);
        foreach ((array)$uiconfig->items as &$subconfig){
            self::remove_node($subconfig);
        }
        return $uiconfig;
    }
}?>
