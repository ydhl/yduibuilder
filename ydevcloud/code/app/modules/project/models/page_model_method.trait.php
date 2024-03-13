<?php
namespace app\project;
use app\user\User_Model;
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
        Page_Bind_Style_Model::remove_gone_uiid($this);
        Uicomponent_Instance_Model::remove_gone_uiid($this);
    }
    public function remove() {
        Page_User_Model::from()->where('page_id=:pid')->delete([':pid'=>$this->id]);
        Page_Version_Model::from()->where('page_id=:pid')->delete([':pid'=>$this->id]);
        Page_Bind_Style_Model::from()->where('page_id=:pid')->delete([':pid'=>$this->id]);
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
        $config = json_decode(html_entity_decode($this->config));// 不能转化成关联数组，这回导致空对象的{}json_encode被转化成[]
        $subPageIds = [];
        $this->fetchSubPageIds($config, $subPageIds);
        $subPageIds = array_unique($subPageIds);
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
     *
     * @return string
     */
    public function get_save_path($target){
        if (strtolower($target) == "html"){
            $module = $this->get_module();
            $folder = $module->folder ?: $module->name;
            $page_file = $folder . '/' . $this->get_export_file_name($target).".html";
            return $page_file;
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
     * 找出config中所有ui的id数组
     * @param $config uiconfig 对象
     * @return array [id=>type]
     */
    public static function all_sub_item_uiid($config, &$uiids = []){
        $uiids[$config->meta->id] = $config->type;
        if ($config->items){
            foreach ($config->items as $item){
                self::all_sub_item_uiid($item, $uiids);
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
     * 从根节点查找uuid的父级
     *
     * @param $uiid
     * @param $index
     * @param $parent
     * @return mixed|null
     */
    public function find_parent($uiid, &$index=-1, $config=null) {
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
    public function is_parent_contain_sub_page($target_id, $subPageId){
        $config = $this->get_config();
        $targetUi = self::findUiItem($config, $target_id);
        if($targetUi->meta->id == $subPageId) return true;

        $parent = $this->find_parent($target_id, $index, $config);
        while($parent){
            if($parent->meta->id == $subPageId) return true;
            $parent = $this->find_parent($parent->meta->id, $index, $config);
        }
        return false;
    }
    public function copy_bind_from($copyFromPage, $old2newIDs){
        $dba = YZE_DBAImpl::get_instance();

        //3 uicomponent_instance
        $models = Uicomponent_Instance_Model::from()->where('page_id=:pid and is_deleted=0')->select([':pid'=>$copyFromPage->id]);
        $sql = "insert into uicomponent_instance(uuid, page_id, uicomponent_page_id, instance_uuid) values ";
        $values = [];
        foreach ($models as $model){
            if (!$old2newIDs[$model->instance_uuid]) continue;
            $values[] = "(uuid(), {$this->id}, {$model->uicomponent_page_id}, '".$old2newIDs[$model->instance_uuid]."')";
        }
        if ($values){
            $dba->exec($sql.join(',', $values));
        }

        //4 page_bind_style
        $models = Page_Bind_Style_Model::from()->where('page_id=:pid and is_deleted=0')->select([':pid'=>$copyFromPage->id]);
        $sql = "insert into page_bind_style(uuid, page_id, style_id, uiid) values ";
        $values = [];
        foreach ($models as $model){
            if (!$old2newIDs[$model->uiid]) continue;
            $values[] = "(uuid(), {$this->id}, {$model->style_id}, '".$old2newIDs[$model->uiid]."')";
        }
        if ($values){
            $dba->exec($sql.join(',', $values));
        }

    }
}?>
