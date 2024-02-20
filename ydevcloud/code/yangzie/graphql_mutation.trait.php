<?php

namespace yangzie;

/**
 * graphql mutation
 */
trait Graphql_Mutation
{

    /**
     * @param array $models key是table，value是对应的class
     * @return array key是mutation 名称，value是GraphqlField
     */
    public function getModelMutations( array $models){
        $_ = [];
        foreach ($models as $table => $model) {
            $modelObject = new $model;
            $args = [];
            $columnConfigs = $modelObject->get_columns();
            foreach ($columnConfigs as $column => $columnInfo){
                $args[] = new GraphqlInputValue($column, $modelObject->get_Model_Field_Type($columnInfo, $column));
            }

            $saveMutation = 'save' . ucfirst(strtolower($table));
            $saveField = new GraphqlField(
                $saveMutation,
                new GraphqlType($table, null, GraphqlType::KIND_OBJECT),
                'save the ' . $table . ' record, if has primary key column update other else create',$args
            );
            $_[$saveMutation] = ["field"=>$saveField,"class"=>$model];

            // removeMutation
            $keyName = $modelObject->get_key_name();
            $removeMutation = 'remove' . ucfirst(strtolower($table));
            $removeField = new GraphqlField(
                $removeMutation,
                new GraphqlType($table, null, GraphqlType::KIND_OBJECT),
                'remove the ' . $table . ' record and unset the primary key'
                ,[
                    new GraphqlInputValue($keyName, $modelObject->get_Model_Field_Type($columnConfigs[$keyName], $keyName)),
                    new GraphqlInputValue("uuid", new GraphqlType("String", null, 'SCALAR'))
                ]
            );
            $_[$removeMutation] = ["field"=>$removeField,"class"=>$model];
        }
        return $_;
    }
    /**
     * @param array<GraphqlSearchNode> $nodes
     * @return array
     * @throws YZE_FatalException
     */
    public function mutation(array $nodes)
    {
        $mutations = $this->getModelMutations($this->find_All_Models());
        $results = [];
        // 顶层node就是具体的mutation，其子节点是mutation后返回的内容
        foreach ($nodes as $node) {
            if (@!$mutations[$node->name]) throw new YZE_FatalException("mutation ".$node->name." not found");
            $class = $mutations[$node->name]['class'];
            $model = new $class;
            foreach ($node->args as $arg){
                if (!$model->has_column($arg->name)){
                    throw new YZE_FatalException("column ".$arg->name." not exist");
                }
                $model->set($arg->name, $arg->value);
            }
            if (preg_match("/^remove/", $node->name)){
                if ($model->get_key()){
                    $class::remove_by_id($model->get_key());
                }else if($model->get_uuid()){
                    $class::remove_by_uuid($model->get_uuid());
                }else{
                    throw new YZE_FatalException($model->get_key_name()." or ".$model->get_uuid_name()." must specify one");
                }
            }else{
                $model->save();
            }

            $result = [];
            foreach ($node->sub as $subNode){
                $result[$subNode->alias?:$subNode->name] = $model->get($subNode->name);
            }
            $results[$node->alias?:$node->name] = $result;
        }
        return GraphqlResult::success($this, $results);
    }

}

?>
