<?php

namespace app\modules\build\views\code;
/**
 *  生产monaco 编辑器的代码提示
 */
class Code_Suggestion
{
    /**
     * 输出web api的输出数据代码提示; suggestion 代码提示适合用于接口，方法等独立等没有上下文等个体
     * @param $jsonData
     * @return array
     */
    public static function get_json_data_suggestions($jsonData){
        $suggestions = [];
        if ($jsonData['name']){
            $suggestions[] = [
                "label"=>$jsonData['name'],
                "kind"=>"monaco.languages.CompletionItemKind['Field']",
                "insertText"=>$jsonData['name'],
                "detail"=>$jsonData['title'].$jsonData['comment'],
                "tags"=>$jsonData['deprecated']?['Deprecated']:[],
                "documentation"=>$jsonData['title'].$jsonData['comment'],
            ];
        }
        if ($jsonData['type']=='object'){
            foreach ((array)$jsonData['props'] as $prop){
                $suggestions = array_merge($suggestions, self::get_json_data_suggestions($prop));
            }
        }else if ($jsonData['type']=='array'){
            $suggestions = array_merge($suggestions, self::get_json_data_suggestions($jsonData['item']));
        }
        return $suggestions;
    }
}
