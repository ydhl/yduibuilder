<?php
namespace app\project;
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
trait Page_Bind_Api_Model_Method{
    private $web_api;
    public function set_web_api($api){
        $this->web_api = $api;
        return $this;
    }
    public function get_web_api(){
        if (!$this->web_api && $this->web_api_id){
            $this->web_api = Web_Api_Model::find_by_id($this->web_api_id);
        }
        return $this->web_api;
    }

    public static function filter($item) {
        if (!$item['name'] && $item['type']!=='object'){
            return null;
        }
        if ($item['item'] && $item['item']['type']=='object'){// 数组项是对象
            $props = [];
            foreach($item['item']['props'] as $prop){
                $props[] = self::filter($prop);
            }
            $item['item']['props'] = array_filter($props);
        }

        if ($item['props']){
            $props = [];
            foreach($item['props'] as $prop){
                $props[] = self::filter($prop);
            }
            $item['props'] = array_filter($props);
        }
        return $item;
    }

    public static function save_from_web_api(Page_Model $page, Web_Api_Model $web_api, $bindClass, $bindUuid){

        $bind_api = Page_Bind_Api_Model::from()
            ->where('page_id=:pid and web_api_id=:id and bind_class=:cls and bind_uuid=:uid')
            ->get_Single([':pid'=>$page->id, ':id'=>$web_api->id,':cls'=>$bindClass,':uid'=>$bindUuid]);
        if (!$bind_api){
            $bind_api = new Page_Bind_Api_Model();
            $bind_api->set('uuid', Page_Bind_Api_Model::uuid())
                ->set('bind_class', $bindClass)
                ->set('bind_uuid', $bindUuid);
        }

        $api_content = json_decode(html_entity_decode($web_api->content), true);
        $input = $api_content['request'];

        if ($api_content['request']['body']){
            // api中保存时会保存body的各种情况，但实际使用时只有requestBodyType指定的一种情况
            if ($web_api->requestBodyType=='json'){
                $input['body'] = [$api_content['request']['body'][$web_api->requestBodyType]];
            }else{
                $input['body'] = $api_content['request']['body'][$web_api->requestBodyType];
            }
        }
        $input = array_filter($input);

        $filter_output = [];
        $filter_input = [];
        foreach (array_filter($api_content['response']) as $out){
            $filter = self::filter($out);
            if ($filter){
                $filter_output[] = $filter;
            }
        }
        foreach ($input as $type => $ins){
            foreach (array_filter($ins) as $in){
                $filter = self::filter($in);
                if ($filter){
                    $filter_input[$type][] = $in;
                }
            }
        }
        $bind_api->set('page_id', $page->id)
            ->set('web_api_id', $web_api->id)
            ->set('name', $web_api->name)
            ->set('requestBodyType', $web_api->requestBodyType)
            ->set('method', $web_api->method)
            ->set('path', $web_api->path)
            ->set('major', $web_api->major)
            ->set('minor', $web_api->minor)
            ->set('revision', $web_api->revision)
            ->set('version', $web_api->version)
            ->set('input', json_encode($filter_input, JSON_UNESCAPED_UNICODE))
            ->set('output', json_encode($filter_output, JSON_UNESCAPED_UNICODE))
            ->save();
        return $bind_api;
    }

    /**
     * 返回指定的输入类型数据配置
     *
     * 返回格式：{"uuid":"","name":"","type":""}...
     *
     * @param $inType string path param cookie header body
     * @return array|mixed
     */
    public function get_input_configs($inType){
        $input = json_decode(html_entity_decode($this->input), true);
        return $input[$inType] ?: [];
    }
    /**
     * 返回指定的返回数据内容
     * @param $response_uuid number 响应的uuid
     * @return array|mixed
     */
    public function get_output_config($response_uuid){
        $response = json_decode(html_entity_decode($this->output), true);
        foreach ($response as $res){
            if ($res['uuid']==$response_uuid) return $res;
        }
        return [];
    }

    /**
     * 返回接口的（正常）响应体格式
     * @return string 小写的字符串
     */
    public function get_response_type(){
        $outputs = json_decode(html_entity_decode($this->output), true);
        foreach ($outputs as $output){
            if ($output['code']==200) return strtolower($output['contentType']);
        }
        return '';
    }
    public function get_basic_info(){
        $records = $this->get_records();
        unset($records['id'],$records['created_on'],$records['modified_on'],$records['is_deleted']
            ,$records['input'],$records['output'],$records['page_id'],$records['web_api_id']);

        $records['apiUuid'] = $this->get_web_api()->uuid;
        return $records;
    }

    /**
     * 触发该api的信息
     * @return string
     */
    public function get_trigger_info(){
        switch ($this->bind_class){
            case Page_Bind_Event_Model::CLASS_NAME:
                $info = [];
                $event = Page_Bind_Event_Model::find_by_uuid($this->bind_uuid);
                if (!$event) return '';
                $customEvent = $event->get_uicomponent_event();
                if ($customEvent) {
                    $page = $customEvent->get_page();
                    $info[] = $page->name;
                    $info[] = $customEvent->name;
                }else{
                    $page = $event->get_page();
                    $uiconfig = $page->find_ui_item($event->uiid);
                    $info[] = $uiconfig->meta->title ? '「'.$uiconfig->meta->title.'」' :  '';
                    $info[] = $event->event;
                }
                return sprintf(__('The API is called when %s trigger event 「%s」 '), $info);
            case Page_Bind_API_Action_Model::CLASS_NAME:
                $bind_action = Page_Bind_API_Action_Model::find_by_uuid($this->bind_uuid);
                return $bind_action->get_condition_info();
            default:
                if ($this->bind_uuid && !$this->bind_class) {// 看着ui中触发
                   $uiItem = $this->get_page()->find_ui_item($this->bind_uuid);
                   return $uiItem ? sprintf(__('trigger in ui %s'), $uiItem->meta->value ?: $uiItem->meta->title) : '';
                }
        }
        return '';
    }

    /**
     * 返回调用该api时能使用的局部变量：
     * 1。如果是组件触发的事件调用api，那么该事件的参数
     * 2。如果是bind action调用的api，那么就是该bind action关联的数据：目前有bind api的响应
     * @return []
     */
    public function get_local_Variables(){
        if ($this->bind_class == Page_Bind_Event_Model::CLASS_NAME){
            $bindEvent = Page_Bind_Event_Model::find_by_uuid($this->bind_uuid);
            if (!$bindEvent){
                return null;
            }
            $customEvent = $bindEvent->get_uicomponent_event();
            if ($customEvent){
                return json_decode(html_entity_decode($customEvent->args));
            }
            return strtolower($bindEvent->event) == 'onchange' ?
            [
                ['uuid'=>'oldValue','type'=>'string','name'=>'oldValue'],
                ['uuid'=>'value','type'=>'string','name'=>'value'],
            ]:
            [
                ['uuid'=>'boundData','type'=>'any','name'=>'boundData','title'=>__('the bound output data')],
                ['uuid'=>'value','type'=>'string','name'=>'value','title'=>__('the bound output value')],
            ];
        }
        if ($this->bind_class == Page_Bind_API_Action_Model::CLASS_NAME){
            $bindAction = Page_Bind_API_Action_Model::find_by_uuid($this->bind_uuid);
            return $bindAction->get_local_variables();
        }
        // 组件中触发的api调用，
        if (!$this->bind_class && $this->bind_uuid){
            $uiItem = $this->get_page()->find_ui_item($this->bind_uuid);
            if (!$uiItem) return null;
            if (!strcasecmp($uiItem->type,'file')){// 文件组件立即上传
                $arg = new \stdClass();
                $arg->name = 'file';
                $arg->uuid = 'file';
                $arg->type = 'file';
                return [$arg];
            }
        }
        return null;
    }

    /**
     * 调用之前需要先调用Page_Bind_Event_Model和Page_Bind_Api_Action_Model的remove_gone_uiid方法
     *
     * @param Page_Model $page
     * @return void
     * @throws YZE_DBAException
     */
    public static function remove_gone_uiid(Page_Model $page) {
        $objects = Page_Bind_Api_Model::from('api')
            ->left_join(Page_Bind_Event_Model::CLASS_NAME, 'ent', 'ent.uuid=api.bind_uuid and api.bind_class=:event')
            ->left_join(Page_Bind_Api_Action_Model::CLASS_NAME, 'act', 'act.uuid=api.bind_uuid and api.bind_class=:action')
            ->where('api.page_id=:pid')
            ->select([':pid'=>$page->id, ':event'=>Page_Bind_Event_Model::CLASS_NAME, ':action'=>Page_Bind_Api_Action_Model::CLASS_NAME]);

        foreach ($objects as $object){
            $api = $object['api'];
            $event = $object['ent'];
            $action = $object['act'];
            if (!$event && !$action){
                if ($api->bind_uuid){
                    $uiItem = $page->find_ui_item($api->bind_uuid);
                    // 绑定到ui上到api
                    if (!$uiItem || $uiItem->meta->custom->apiUuid!= $api->uuid){
                        $api->remove();
                    }
                }else{
                    $api->remove();
                }
            }
        }
    }
    public function remove(){
        Page_Bind_Variable_Model::from()
            ->where("from_class=:cls and from_uuid=:uuid")
            ->delete([':cls'=>self::CLASS_NAME, ':uuid'=>$this->uuid]);
        Page_Bind_Variable_Model::from()
            ->where("to_class=:cls and to_uuid=:uuid")
            ->delete([':cls'=>self::CLASS_NAME, ':uuid'=>$this->uuid]);

        foreach (Page_Bind_Api_Action_Model::from()->where("page_id=:pid and from_class=:cls and from_uuid=:uuid")
                     ->select([':pid'=>$this->page_id, ':cls'=>self::CLASS_NAME, ':uuid'=>$this->uuid]) as $action) {
            $action->remove();
        }
        parent::remove();
    }
}?>
