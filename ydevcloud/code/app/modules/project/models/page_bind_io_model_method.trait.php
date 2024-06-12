<?php
namespace app\project;
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
trait Page_Bind_Io_Model_Method{
    public static function get_io($page, $bind_class){
        $ios = Page_Bind_Io_Model::from()->where('page_id=:pid and from_class=:cls')
            ->select([':pid'=>$page->id,':cls'=>$bind_class]);
        $data = [];
        foreach ($ios as $io){
            if ($io->type =='in'){
                $data[$io->data_id]['in'] = $io->uiid;
            }else{
                $data[$io->data_id]['out'][$io->uiid] = $io->output_as?:'';
                if($io->bound_as) $data[$io->data_id]['bound'][$io->uiid] = $io->bound_as?:'';
            }
        }
        return $data;
    }

    /**
     * 给data的每个数据项设置好in out 绑定关系
     * @param array $in_out
     * @param array $data
     * @return array
     */
    public static function get_bind_io(array $in_out, array $data){
        $rst = $data;
        if ($in_out[$data['uuid']]) {
            $rst['in'] = $in_out[$data['uuid']]['in']?:null;
            $rst['out'] = $in_out[$data['uuid']]['out']?:null;
            $rst['bound'] = $in_out[$data['uuid']]['bound']?:null;
        }else{
            $rst['in'] = null;
            $rst['out'] = null;
        }
        if ($rst['item']){ // 数组
            $rst['item'] = self::get_bind_io($in_out, $data['item']);
        }else if ($rst['props']){ // 对象
            foreach ($rst['props'] as $index => $prop){
                $rst['props'][$index] = self::get_bind_io($in_out, $prop);
            }
        }
        return $rst;
    }

    /**
     * 删除那些在页面上已经删除掉的ui绑定
     * @param $page
     * @return void
     */
    public static function remove_gone_uiid(Page_Model $page) {
        $ios = Page_Bind_Io_Model::from()->where('page_id=:pid')->select([':pid'=>$page->id]);
        foreach ($ios as $io){
            $item = $page->find_ui_item($io->uiid);
            if (!$item){
                $io->remove();
            }
        }
    }
}?>
