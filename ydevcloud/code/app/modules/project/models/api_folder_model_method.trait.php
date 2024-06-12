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
trait Api_Folder_Model_Method{
    public static function get_all_folders($project, &$folderPaths=[], $need_filter=false) {
        $items = Api_Folder_Model::from('f')->where('f.project_id=:pid and f.is_deleted=0')
            ->order_By('index','asc','f')
            ->order_By('id','asc','f')
            ->select([':pid'=>$project->id]);
        $folderTree = [];
        $folders = [];
        // 先查询出所有的目录
        foreach ($items as $item){
            $folders[$item->id] = $item;
        }
        // 先整理目录成树形结构
        foreach ($folders as $folder){
            $curr = $folder;
            // 向上遍历找到顶级
            if ($curr->api_folder_id){
                $path =[$curr->id];
                $parent = $curr;
                do{
                    $parent = $folders[$parent->api_folder_id];
                    $path[] = $parent->id;
                }while($parent->api_folder_id);
                $top = $parent;
                $folderPaths[] = $path;
            }else{
                $folderPaths[] = [$curr->id];
                $path = [];
                $top = $curr;
            }

            // 封装tree-select的结构
            self::pick_tree_data($folderTree, $top, $path, $folders);
        }
        if ($need_filter){
            return array_values(Api_Folder_Model_Method::filter_tree_data($folderTree));
        }
        return $folderTree;
    }

    public static function filter_tree_data($folderTree){
        foreach ($folderTree as &$tree){
            if (@$tree['children']){
                $tree['children'] = Api_Folder_Model_Method::filter_tree_data($tree['children']);
            }
            $tree['children'] = $tree['children'] ? array_values($tree['children']) : [];
        }
        return $folderTree;
    }
    private static function pick_tree_data(&$return, $topFolder, $sub_paths, $allFolders){
        if (!$return[$topFolder->id]){
            $return[$topFolder->id] = [
                'title'=> $topFolder->name,
                'id'=> $topFolder->uuid,
                'comment'=> $topFolder->comment,
                'children'=> []
            ];
        }
        if (!$sub_paths) return;
        array_pop($sub_paths);//弹出最顶层，开始遍历子集
        $sub_paths = array_reverse($sub_paths);
        $children = &$return[$topFolder->id]['children'];
        foreach ($sub_paths as $subid){
            $subFolder = $allFolders[$subid];
            $children[$subid] = [ 'title'=> $subFolder->name, 'parent'=>$topFolder->uuid, 'id'=> $subFolder->uuid, 'comment'=> $topFolder->comment, 'children'=> []];
            $children = &$children[$subid]['children'];
            $topFolder = $allFolders[$subid];
        }
    }

    // 这里实现model的业务方法
    public function remove(){
        try{
            parent::remove();
        }catch (\Exception $e){
            $dba = YZE_DBAImpl::get_instance();
            $sql = "update api_folder set api_folder_id = NULL where api_folder_id = ".$this->id;
            $dba->exec($sql);
            $sql = "update web_api set api_folder_id = NULL where api_folder_id = ".$this->id;
            $dba->exec($sql);
            parent::remove();
        }
    }
}?>
