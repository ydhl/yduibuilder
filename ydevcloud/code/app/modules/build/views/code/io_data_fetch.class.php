<?php
namespace app\modules\build\views\code;
use app\build\Build_Model;
use app\project\Page_Bind_Api_Model;

/**
 * 输入输出数据绑定处理
 */
class Io_Data_Fetch{
    private $build;
    public function __construct(Build_Model $build)
    {
        $this->build = $build;
    }

    /**
     * 获取指定的api请求数据类型
     * @param $response_uuid 响应结果uuid
     * @param Page_Bind_Api_Model $bindApi
     * @return array
     */
    public function fetch_output_args($response_uuid, Page_Bind_Api_Model $bindApi){
        $configs = $bindApi->get_output_config($response_uuid);
        return $this->fetch_data_bound_uiid($bindApi->uuid, 'out', $configs['body'], true);// todo 目前只考虑json
    }

    /**
     * 获取绑定的输出数据项，该数据项可能是1级数据或者是数据下面的子数据，$dataName返回访问这个数据的访问路径。
     * 一个ui可以绑定多个输出类型，每个输出类型只能绑定一个数据项。
     * 返回格式：
     *
     * [
     * OUTPUTAS1: DATA1,
     * OUTPUTAS2: DATA2
     * ]
     *
     * 同时通过dataNames返回每个输出格式的数据对应的访问路径 格式[OUTPUTAS1: dataname, OUTPUTAS2: dataname]
     *
     *
     * boundDataName: 输出数据的访问路径
     * 1. 如果上层有数组，该数组在输出绑定时为（itemOfUIID，idxOfUIID）in arrName的格式，其下的所有数据在绑定时都以itemUIID.foo.bar的格式，foo.bar为数据的路径
     * 2. 上层是对象，则按foo.bar的格式
     *
     * @param $uiid string  uiid
     * @param $boundDataNames string  绑定的数据在前端绑定输出时的名称
     * @param $bound string out:输出绑定数据， bound: 关联数据绑定
     * @return array data model
     */
    public function fetch_output_data($uiid, &$boundDataNames=[], $bound='out'){
        //from_uuid, data_id
        $outputInfos = $this->build->get_uiid_Bind_Data($uiid, $bound);
        if (!$outputInfos) return [];

        $datas = [];
        foreach ($outputInfos as $outputAS => $outputInfo){
            $bind_data_model = $this->build->get_bound_datas($outputInfo['from_uuid']);
            if (!$bind_data_model) continue;
            $path = [];
            $bind_data_model->get_parent_of_data_id($outputInfo['data_id'], null, $path, $data);
            if (!$data) continue;

            if ($path){
                $iterateIndex = array_search('', $path);
                if ($iterateIndex !== false){
                    // 没有name的是数组的item项目
                    // item下面的数据路径就是iterate迭代器重新命名的，比如(item, index) in arrName中的item
                    $newPath = array_slice($path, 0, $iterateIndex + 1);
                    $newPath[count($newPath) - 1] = 'itemOf'.$path[$iterateIndex+1];
                    $path = $newPath;
                }
            }
            // 自己是数组项Item的情况
            if (!$data['name']){
                $path = ['itemOf'.reset($path)];
            }else{
                array_unshift($path, $data['name']);
            }
            $boundDataNames[$outputAS] = join('.', array_reverse($path));

            $datas[$outputAS] = $data;
        }

        return $datas;
    }

    /**
     * 获取指定的uiid绑定输入的数据, 一个ui只处理一个绑定的输入
     *
     *
     * @param $uiid string  uiid
     * @param $boundDataName string  绑定数据的名称
     * @return array data model
     */
    public function fetch_input_data($uiid, &$boundDataName){
        //from_uuid, data_id
        $inputInfo = $this->build->get_uiid_Bind_Data($uiid, 'in');
        if (!$inputInfo) return [];

        $bind_data_model = $this->build->get_bound_datas($inputInfo['from_uuid']);
        if (!$bind_data_model) return [];
        $path = [];
        $bind_data_model->get_parent_of_data_id($inputInfo['data_id'], null, $path, $data);
        if (!$data) return [];

        if ($path){
            $iterateIndex = array_search('', $path);
            if ($iterateIndex !== false){
                // 没有name的是数组的item项目
                // item下面的数据路径就是iterate迭代器重新命名的，比如(item, index) in arrName中的item
                $newPath = array_slice($path, 0, $iterateIndex + 1);
                $newPath[count($newPath) - 1] = 'itemOf'.$path[$iterateIndex+1];
                $path = $newPath;
            }
        }
        // 自己是数组项Item的情况
        if (!$data['name']){
            $path = ['itemOf'.reset($path)];
        }else{
            array_unshift($path, $data['name']);
        }
        $boundDataName = join('.', array_reverse($path));

        return $data;
    }

    /**
     * 对json结构体数据解析每个数据节点及其绑定对ui id
     *
     * @param Page_Bind_Api_Model $bindApi
     * @param $inOut string in|out
     * @param $jsonData array
     * @param $keyIsName boolean false返回的数据中用id作为key，true用name作为key
     * @return array [data key => bound uiid info]
     */
    public function fetch_data_bound_uiid($from_uuid, $inOut, $jsonData, $keyIsName=false){
        $data = [];
        $key = ($keyIsName ? $jsonData['name'] : $jsonData['uuid']);

        if ($jsonData['type'] == 'array'){
            $myBound = [
                'uiid'=>$this->build->get_uiid_of_data($from_uuid, $inOut, $jsonData['uuid']),
                'item'=>$this->fetch_data_bound_uiid($from_uuid, $inOut, $jsonData['item'], $keyIsName)
            ];
            if ($jsonData['name']) { // 一个item, 如name:[]
                $data[$key] = $myBound;
            } else { // 是最顶级的array，这时没有name，如[]
                $data = $myBound;
            }
        }else if ($jsonData['type'] == 'object') {
            $props = [];
            if ($jsonData['props']) {
                foreach ($jsonData['props'] as $prop) {
                    $props = array_merge($props, $this->fetch_data_bound_uiid($from_uuid, $inOut, $prop, $keyIsName));
                }
            }
            $myBound = [
                'uiid'=>$this->build->get_uiid_of_data($from_uuid, $inOut, $jsonData['uuid']),
                'props'=>$prop
            ];
            if ($jsonData['name']) {// 子集object，比如name:{}
                $data[$key] = $myBound;
            } else {// 顶级object，比如{}
                $data = $myBound;
            }
        }else{
            $data[$key] = ['uiid'=>$this->build->get_uiid_of_data($from_uuid, $inOut, $jsonData['uuid'])];
        }
        return $data;
    }
    /**
     * 对json结构体数据解析每个数据节点及其绑定对ui id
     *
     * @param Page_Bind_Api_Model $bindApi
     * @param $inOut string in|out
     * @param $jsonData array
     * @param $keyIsName boolean false返回的数据中用id作为key，true用name作为key
     * @return array [uiid=>bound data uuid]
     */
    public function fetch_uiid_bound_data($from_uuid, $inOut, $jsonData){
        $data = [];
        $boundInfo = $this->build->get_uiid_of_data($from_uuid, $inOut, $jsonData['uuid']);
        if ($inOut=='in' && $boundInfo){// 输入绑定时，返回的是uiid
            $data[$boundInfo][] = $jsonData['uuid'];
        }else if ($boundInfo) {
            foreach ($boundInfo as $uiid=>$outputAs){
                $data[$uiid][$jsonData['uuid']] = $outputAs;
            }
        }

        if ($jsonData['type'] == 'array'){
            $myBound = $this->fetch_uiid_bound_data($from_uuid, $inOut, $jsonData['item']);
            if ($myBound) {
                $data = array_merge_recursive($data, $myBound);
            }
        }else if ($jsonData['type'] == 'object') {
            $props = [];
            if ($jsonData['props']) {
                foreach ($jsonData['props'] as $prop) {
                    $props = array_merge_recursive($props, $this->fetch_uiid_bound_data($from_uuid, $inOut, $prop));
                }
            }
            if ($props) {
                $data = array_merge_recursive($data, $props);
            }
        }
        return $data;
    }
}
