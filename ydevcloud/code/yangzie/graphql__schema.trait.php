<?php

namespace yangzie;

/**
 * graphql schema 内省处理
 */
trait Graphql__Schema
{

    /**
     * @param GraphqlSearchNode $node
     * @return array
     */
    public function __schema(GraphqlSearchNode $node)
    {
        $schemeResult = [];
        $models = $this->find_All_Models();
        foreach ($node->sub as $schemaNode) {
            $schemaName = strtoupper($schemaNode->name);
            switch ($schemaName) {
                case '__TYPENAME':
                    $schemeResult[$schemaNode->name] = "__Schema";
                    break;
                case 'QUERYTYPE':
                    $schemeResult[$schemaNode->name] = $this->schema_Query_type($models, $schemaNode);
                    break;
                case 'SUBSCRIPTIONTYPE':
                    $schemeResult[$schemaNode->name] = $this->schema_Subscription_Type($schemaNode);
                    break;
                case 'MUTATIONTYPE':
                    $schemeResult[$schemaNode->name] = $this->schema_Mutation_Type($models, $schemaNode);
                    break;
                case 'TYPES':
                    $schemeResult[$schemaNode->name] = $this->all_schema_Types($models, $schemaNode);
                    break;
                case 'DIRECTIVES':
                    $schemeResult[$schemaNode->name] = $this->schema_Directives($schemaNode);
                    break;
                case 'DESCRIPTION':
                    $schemeResult[$schemaNode->name] = YZE_APP_NAME . " schema for GraphiQL";
                    break;
            }
        }
        return $schemeResult;
    }

    /**
     * @param array $models
     * @param GraphqlSearchNode $node
     * @return array
     */
    private function schema_Query_type($models, GraphqlSearchNode $node)
    {
        $queryFileds = [];
        $fieldSearch = GraphqlIntrospection::find_search_node_by_name($node->sub, 'fields');
        $argSearch = GraphqlIntrospection::find_search_node_by_name($fieldSearch->sub, 'args');
//        print_r($argSearch);
        $fieldNames = [];
        foreach ($models as $table => $class) {
            $modelObject = new $class;
            $fieldNames[] = $table;
            $field = new GraphqlField($table,
                new GraphqlType($table,null,  GraphqlType::KIND_OBJECT),
                $modelObject->get_description(),
                $this->get_Model_Args($argSearch));
            $queryFileds[] = $field;
        }
        $this->custom_fields($queryFileds, $fieldNames);


        $field = new GraphqlField("count", new GraphqlType('count',null,  GraphqlType::KIND_OBJECT), "分页数据");
        $queryFileds[] = $field;

        $fieldNames[] = 'count';

        if (count($fieldNames) != count(array_unique($fieldNames))){
            throw new YZE_FatalException(sprintf(__('field 定义重复了请检查: %s')), join(',', $fieldNames));
        }

        $type = new GraphqlType("YangzieQuery","Yangzie Query entry", GraphqlType::KIND_OBJECT, null, $queryFileds);
        $intro = new GraphqlIntrospection($node, $type->get_data());
        return $intro->search();
    }

    /**
     * 返回系统有哪些订阅操作
     * @param $node
     * @return string
     */
    private function schema_Subscription_Type($node)
    {
        return null;
    }

    private function schema_Mutation_Type($models, GraphqlSearchNode $node)
    {
        // 每个表的单记录更新
        // TODO controller的action映射
        $queryFileds = [];
        foreach ($this->getModelMutations($models) as $mutationInfo) {
            $queryFileds[] = $mutationInfo['field'];
        }

        $type = new GraphqlType("YangzieMutation", "Yangzie Mutation entry", GraphqlType::KIND_OBJECT, null, $queryFileds);
        $intro = new GraphqlIntrospection($node, $type->get_data());
        return $intro->search();
    }

    private function get_model_schema($models, GraphqlSearchNode $node){
        $results = [];
        // 每一个model都是types的一级节点
        foreach ($models as $table => $model) {
            $modelObject = new $model;
            // 根据scheme请求返回内容
            $fields = $this->get_Model_Fields($modelObject);
            $type = new GraphqlType($table, $modelObject->get_description(), GraphqlType::KIND_OBJECT, null, $fields);
            $intro = new GraphqlIntrospection($node, $type->get_data());
            $results[] = $intro->search();

        }
        return $results;
    }
    /**
     * 返回系统有哪些查询类型（也就是Model）,默认情况下每个Modal的cloumn都将返回，表示都可以被graphql查询
     * @param GraphqlSearchNode $node
     * @return array
     */
    private function all_schema_Types($models, GraphqlSearchNode $node)
    {
        if (!$node->has_value()) return [];
        $results = [];
        $results[] = $this->schema_Query_type($models, $node);
        $results[] = $this->schema_Mutation_Type($models, $node);
//        $results[] = $this->schema_Subscription_Type($node);
        $results = array_merge($results, $this->_schema_Basic_type($node));


        // 类型系统的基础类型
        $schema = new GraphqlIntrospection($node, $this->get__schema());
        $results[] = $schema->search();
        $type = new GraphqlIntrospection($node, $this->get__type());
        $results[] = $type->search();
        $typekind = new GraphqlIntrospection($node, $this->get__typekind());
        $results[] = $typekind->search();
        $field = new GraphqlIntrospection($node, $this->get__fields());
        $results[] = $field->search();
        $inputvalue = new GraphqlIntrospection($node, $this->get__InputValue());
        $results[] = $inputvalue->search();
        $enumValue = new GraphqlIntrospection($node, $this->get__enumValue());
        $results[] = $enumValue->search();
        $directive = new GraphqlIntrospection($node, $this->get__directive());
        $results[] = $directive->search();
        $directiveLocation = new GraphqlIntrospection($node, $this->get__directiveLocation());
        $results[] = $directiveLocation->search();

        // model中的enum 类型
        foreach ($models as $table => $model) {
            $modelObject = new $model;
            $columns = $modelObject->get_columns();
            foreach ($columns as $columnName => $columnConfig) {
                if ($columnConfig['type'] != 'enum') {
                    continue;
                }
                $enumTypeName = $model::TABLE . '_' . $columnName;
                $fieldSearch = GraphqlIntrospection::find_search_node_by_name($node->sub, 'fields');
                $enumValues = $this->get_Model_Enum($modelObject, $fieldSearch, $columnName);
                $type = new GraphqlType($enumTypeName, $modelObject->get_column_mean($columnName), GraphqlType::KIND_ENUM);
                $type->enumValues = $enumValues;
                $intro = new GraphqlIntrospection($node, $type->get_data());
                $results[] = $intro->search();
            }
        }

        $results = array_merge($results, $this->get_model_schema($models, $node));
        $this->custom_query_types($node, $results);

            // model的查询参数类型
        // 根据scheme请求返回内容
        $type = new GraphqlType("count", "查询分页数据");
        $queryFileds = [];
        foreach ($models as $table => $class) {
            $field = new GraphqlField($table,
                new GraphqlType('Int',null, GraphqlType::KIND_SCALAR)
                , sprintf(__("%s count"), $table)
            );
            $queryFileds[] = $field;
        }
        $customFields = [];
        $this->custom_fields($customFields);
        foreach ($customFields as $customField) {
            $queryFileds[] = new GraphqlField($customField->name,
                new GraphqlType('Int',null, GraphqlType::KIND_SCALAR)
                , sprintf(__("%s count"), $customField->name)
            );
        }
        $type->fields = $queryFileds;
        $intro = new GraphqlIntrospection($node, $type->get_data());
        $results[] = $intro->search();

        $type = new GraphqlType("Where", "model的查询条件", GraphqlType::KIND_INPUT_OBJECT);
        $type->inputFields = $this->get_Model_Where_Fields();
        $intro = new GraphqlIntrospection($node, $type->get_data());
        $results[] = $intro->search();

        $type = new GraphqlType("Clause", __("分页，分组和排序"), GraphqlType::KIND_INPUT_OBJECT);
        $type->inputFields = $this->get_Model_Clause_Fields();
        $intro = new GraphqlIntrospection($node, $type->get_data());
        $results[] = $intro->search();

        return $results;
    }
    private function get_non_list_type($name, $kind){
        return new GraphqlType(null, null, GraphqlType::KIND_NON_NULL,
            new GraphqlType(null, null, GraphqlType::KIND_LIST,
                new GraphqlType(null, null, GraphqlType::KIND_NON_NULL,
                    new GraphqlType($name, null, $kind))));
    }
    private function get__typekind()
    {
        $enumValues = [
            new GraphqlEnumValue("SCALAR", __("Indicates this type is a scalar.")),
            new GraphqlEnumValue("OBJECT", __("Indicates this type is an object. `fields` and `interfaces` are valid fields.")),
            new GraphqlEnumValue("INTERFACE", __("Indicates this type is an interface. `fields`, `interfaces`, and `possibleTypes` are valid fields.")),
            new GraphqlEnumValue("UNION", __("Indicates this type is a union. `possibleTypes` is a valid field.")),
            new GraphqlEnumValue("ENUM", __("Indicates this type is an enum. `enumValues` is a valid field.")),
            new GraphqlEnumValue("INPUT_OBJECT", __("Indicates this type is an input object. `inputFields` is a valid field.")),
            new GraphqlEnumValue("LIST", __("Indicates this type is a list. `ofType` is a valid field.")),
            new GraphqlEnumValue("NON_NULL", __("Indicates this type is a non-null. `ofType` is a valid field."))
        ];
        $type = new GraphqlType("__TypeKind", __("An enum describing what kind of type a given `__Type` is."), GraphqlType::KIND_ENUM);
        $type->enumValues = $enumValues;
        return $type->get_data();
    }
    private function get__EnumValue()
    {
        $stringType = new GraphqlType("String", null, GraphqlType::KIND_SCALAR);
        $booleanType = new GraphqlType("Boolean", null, GraphqlType::KIND_SCALAR);
        $nonNullStringType = new GraphqlType(null, null, GraphqlType::KIND_NON_NULL,$stringType);
        $nonNullBooleanType = new GraphqlType(null, null, GraphqlType::KIND_NON_NULL,$booleanType);

        $fields = [
            new GraphqlField("name", $nonNullStringType),
            new GraphqlField("description", $stringType),
            new GraphqlField("isDeprecated", $nonNullBooleanType),
            new GraphqlField("deprecationReason", $stringType)
        ];
        $_enumValue = new GraphqlType("__EnumValue", __("One possible value for a given Enum. Enum values are unique values, not a placeholder for a string or numeric value. However an Enum value is returned in a JSON response as a string."));
        $_enumValue->fields = $fields;
        return $_enumValue->get_data();
    }

    private function get__InputValue()
    {
        $stringType = new GraphqlType("String", null, GraphqlType::KIND_SCALAR);
        $booleanType = new GraphqlType("Boolean", null, GraphqlType::KIND_SCALAR);
        $objectType = new GraphqlType("Boolean", null, GraphqlType::KIND_OBJECT);
        $nonNullStringType = new GraphqlType(null, null, GraphqlType::KIND_NON_NULL,$stringType);
        $nonNullBooleanType = new GraphqlType(null, null, GraphqlType::KIND_NON_NULL,$booleanType);
        $nonNullObjectType = new GraphqlType(null, null, GraphqlType::KIND_NON_NULL,$objectType);

        $fields = [
            new GraphqlField("name", $nonNullStringType),
            new GraphqlField("description", $stringType),
            new GraphqlField("type", $nonNullObjectType),
            new GraphqlField("defaultValue", $stringType, __("A GraphQL-formatted string representing the default value for this input value.")),
            new GraphqlField("isDeprecated", $nonNullBooleanType),
            new GraphqlField("deprecationReason", $stringType)
        ];
        $_inputValue = new GraphqlType("__InputValue",
            __("Arguments provided to Fields or Directives and the input fields of an InputObject are represented as Input Values which describe their type and optionally a default value."));
        $_inputValue->fields = $fields;
        return $_inputValue->get_data();
    }
    private function get__DirectiveLocation()
    {
        $enumValues = [
            new GraphqlEnumValue("QUERY", __("Location adjacent to a query operation.")),
            new GraphqlEnumValue("MUTATION", __("Location adjacent to a mutation operation.")),
            new GraphqlEnumValue("SUBSCRIPTION", __("Location adjacent to a subscription operation.")),
            new GraphqlEnumValue("FIELD", __("Location adjacent to a field.")),
            new GraphqlEnumValue("FRAGMENT_DEFINITION", __("Location adjacent to a fragment definition.")),
            new GraphqlEnumValue("FRAGMENT_SPREAD", __("Location adjacent to a fragment spread.")),
            new GraphqlEnumValue("INLINE_FRAGMENT", __("Location adjacent to an inline fragment.")),
            new GraphqlEnumValue("VARIABLE_DEFINITION", __("Location adjacent to a variable definition.")),
            new GraphqlEnumValue("SCHEMA", __("Location adjacent to a schema definition.")),
            new GraphqlEnumValue("SCALAR", __("Location adjacent to a scalar definition.")),
            new GraphqlEnumValue("OBJECT", __("Location adjacent to an object type definition.")),
            new GraphqlEnumValue("FIELD_DEFINITION", __("Location adjacent to a field definition.")),
            new GraphqlEnumValue("ARGUMENT_DEFINITION", __("Location adjacent to an argument definition.")),
            new GraphqlEnumValue("INTERFACE", __("Location adjacent to an interface definition.")),
            new GraphqlEnumValue("UNION", __("Location adjacent to a union definition.")),
            new GraphqlEnumValue("ENUM", __("Location adjacent to an enum definition.")),
            new GraphqlEnumValue("ENUM_VALUE", __("Location adjacent to an enum value definition.")),
            new GraphqlEnumValue("INPUT_OBJECT", __("Location adjacent to an input object type definition.")),
            new GraphqlEnumValue("INPUT_FIELD_DEFINITION", __("Location adjacent to an input object field definition."))
        ];
        $_directive = new GraphqlType("__DirectiveLocation",
            __("A Directive can be adjacent to many parts of the GraphQL language, a __DirectiveLocation describes one such possible adjacencies."),
            GraphqlType::KIND_ENUM);
        $_directive->enumValues = $enumValues;
        return $_directive->get_data();
    }
    private function get__Directive()
    {
        $stringType = new GraphqlType("String", null, GraphqlType::KIND_SCALAR);
        $booleanType = new GraphqlType("Boolean", null, GraphqlType::KIND_SCALAR);
        $nonNullStringType = new GraphqlType(null, null, GraphqlType::KIND_NON_NULL,$stringType);
        $nonNullBooleanType = new GraphqlType(null, null, GraphqlType::KIND_NON_NULL,$booleanType);

        $args = [ new GraphqlInputValue("includeDeprecated", $booleanType,"","false")];
        $fields = [
            new GraphqlField("name", $nonNullStringType),
            new GraphqlField("description", $stringType),
            new GraphqlField("isRepeatable", $nonNullBooleanType),
            new GraphqlField("locations", $this->get_non_list_type("__DirectiveLocation", GraphqlType::KIND_ENUM)),
            new GraphqlField("args", $this->get_non_list_type("__InputValue", GraphqlType::KIND_OBJECT), "", $args)
        ];
        $_directive = new GraphqlType("__Directive",
            __("A Directive provides a way to describe alternate runtime execution and type validation behavior in a GraphQL document.\n\nIn some cases, you need to provide options to alter GraphQL's execution behavior in ways field arguments will not suffice, such as conditionally including or skipping a field. Directives provide this by describing additional information to the executor.")
        );
        $_directive->fields = $fields;
        return $_directive->get_data();
    }
    private function get__Type()
    {
        $stringType = new GraphqlType("String", null, GraphqlType::KIND_SCALAR);
        $booleanType = new GraphqlType("Boolean", null, GraphqlType::KIND_SCALAR);
        $enumType = new GraphqlType("__TypeKind", null, GraphqlType::KIND_ENUM);
        $nonNullEnumType = new GraphqlType(null, null, GraphqlType::KIND_NON_NULL,$enumType);
        $listObjectType = new GraphqlType(null, null, GraphqlType::KIND_LIST,
            new GraphqlType(null, null, GraphqlType::KIND_NON_NULL,
                new GraphqlType(null, null, GraphqlType::KIND_OBJECT)));

        $args = [new GraphqlInputValue("includeDeprecated", $booleanType, "", "false")];
        $fields = [
            new GraphqlField("kind", $nonNullEnumType),
            new GraphqlField("name", $stringType),
            new GraphqlField("description", $stringType),
            new GraphqlField("specifiedByURL", $stringType),
            new GraphqlField("fields", $listObjectType, "", $args),
            new GraphqlField("interfaces", $listObjectType),
            new GraphqlField("possibleTypes", $listObjectType),
            new GraphqlField("enumValues", $listObjectType, "", $args),
            new GraphqlField("inputFields", $listObjectType, "", $args)
        ];

        $__type = new GraphqlType("__Type"
            , __("The fundamental unit of any GraphQL Schema is the type. There are many kinds of types in GraphQL as represented by the `__TypeKind` enum.\n\nDepending on the kind of a type, certain fields describe information about that type. Scalar types provide no information beyond a name, description and optional `specifiedByURL`, while Enum types provide their values. Object and Interface types provide the fields they describe. Abstract types, Union and Interface, provide the Object types possible at runtime. List and NonNull types compose other types."));
        $__type->fields = $fields;
        return $__type->get_data();
    }
    private function get__schema()
    {
        $stringType = new GraphqlType("String", null, GraphqlType::KIND_SCALAR);
        $objectType = new GraphqlType("__Type", null, GraphqlType::KIND_OBJECT);
        $nonNullObjectType = new GraphqlType(null, null, GraphqlType::KIND_NON_NULL,$objectType);

        $fields = [
            new GraphqlField("description", $stringType),
            new GraphqlField("types", $this->get_non_list_type("__Type", GraphqlType::KIND_OBJECT), __("A list of all types supported by this server.")),
            new GraphqlField("queryType", $nonNullObjectType, __("The type that query operations will be rooted at.")),
            new GraphqlField("mutationType", $objectType, __("If this server supports mutation, the type that mutation operations will be rooted at.")),
            new GraphqlField("subscriptionType", $objectType, __("If this server support subscription, the type that subscription operations will be rooted at.")),
            new GraphqlField("directives", $this->get_non_list_type("__Directive", GraphqlType::KIND_OBJECT), __("A list of all directives supported by this server.")),
        ];

        $__schema = new GraphqlType("__Schema",
            __("A GraphQL Schema defines the capabilities of a GraphQL server. It exposes all available types and directives on the server, as well as the entry points for query, mutation, and subscription operations.")
        );
        $__schema->fields = $fields;
        return $__schema->get_data();
    }
    private function get__fields(){
        $booleanType = new GraphqlType("Boolean", null, GraphqlType::KIND_SCALAR);
        $stringType = new GraphqlType("String", null, GraphqlType::KIND_SCALAR);
        $objectType = new GraphqlType("__Type", null, GraphqlType::KIND_OBJECT);
        $nonNullBooleanType = new GraphqlType(null, null, GraphqlType::KIND_NON_NULL,$booleanType);
        $nonNullObjectType = new GraphqlType(null, null, GraphqlType::KIND_NON_NULL,$objectType);
        $nonNullStringType = new GraphqlType(null, null, GraphqlType::KIND_NON_NULL,$stringType);

        $args = new GraphqlInputValue("includeDeprecated", $booleanType, "", "false");
        $fields = [
            new GraphqlField("name", $nonNullStringType),
            new GraphqlField("description", $stringType),
            new GraphqlField("args", $this->get_non_list_type("__InputValue", GraphqlType::KIND_OBJECT), null, [$args]),
            new GraphqlField("type", $nonNullObjectType),
            new GraphqlField("isDeprecated", $nonNullBooleanType),
            new GraphqlField("deprecationReason", $stringType),
        ];
        $__field = new GraphqlType("__Field",
            __("Object and Interface types are described by a list of Fields, each of which has a name, potentially a list of arguments, and a return type."));
        $__field->fields = $fields;
        return $__field->get_data();
    }
    /**
     * 基础数据类型
     */
    private function _schema_Basic_type(GraphqlSearchNode $node)
    {
        $basicTypes = $this->basic_types();
        $rst = [];

        foreach ($basicTypes as $basicType => $info) {
            $type = new GraphqlType($basicType, $info['description'], GraphqlType::KIND_SCALAR);
            $intro = new GraphqlIntrospection($node, $type->get_data());
            $rst[] = $intro->search();
        }
        return $rst;
    }

    private function get_Model_Enum(YZE_Model $model, GraphqlSearchNode $node, $columnName)
    {
        if (!$model || !$node->has_value()) return [];
        $result = [];
        $method = "get_{$columnName}";
        if (!method_exists($model, $method)) return [];
        foreach ($model->$method() as $enum) {
            $result[] = new GraphqlEnumValue($enum);
        }
        return $result;
    }

    /**
     * 根据scheme查询返回model需要返回的字段信息，包含自定义的field
     *
     * @param YZE_Model $model
     * @param GraphqlSearchNode $node 查询结构体
     * @return []
     */
    private function get_Model_Fields(YZE_Model $model)
    {
        $result = $model->get_graphql_fields();

        if (method_exists($model, "custom_graphql_fields")){
            foreach ($model->custom_graphql_fields() as $custom_field){
                $result[] = $custom_field;
            }
        }

        // 如果有关联表，则关联表也作为field
        $unique_keys = $model->get_relation_columns();
        foreach ($unique_keys as $column => $relationInfo){
            $assoName = $relationInfo['graphql_field'];
            $modelClass = $relationInfo['target_class'];
            if (!class_exists($modelClass))continue;
            $field = new GraphqlField($assoName, new GraphqlType($modelClass::TABLE, null, GraphqlType::KIND_OBJECT), $column." field"
            );
            $result[] = $field;
        }

        return $result;
    }
    private function get_Model_Where_Fields()
    {
        $result = [];
        $typeIntro = new GraphqlType(null,null,GraphqlType::KIND_NON_NULL, new GraphqlType('String',null,GraphqlType::KIND_SCALAR));
        $nullIntro = new GraphqlType("String",null, GraphqlType::KIND_SCALAR);
        $listTypeIntro = new GraphqlType(null,null,GraphqlType::KIND_LIST, new GraphqlType('String',null,GraphqlType::KIND_SCALAR));
        $result[] = new GraphqlInputValue("column", $typeIntro, __("查询字段名"));
        $result[] = new GraphqlInputValue("op", $typeIntro, __("比较条件"));
        $result[] = new GraphqlInputValue("value", $listTypeIntro, __("查询值"));
        $result[] = new GraphqlInputValue("andor", $nullIntro, __("And / Or 拼接下一个where"));
        return $result;
    }

    private function get_Model_Clause_Fields()
    {
        $result = [];
        $typeIntro = new GraphqlType("String", null, GraphqlType::KIND_SCALAR);
        $numberTypeIntro = new GraphqlType("Int", null, GraphqlType::KIND_SCALAR);
        $result[] = new GraphqlInputValue("orderBy", $typeIntro, __("排序"));
        $result[] = new GraphqlInputValue("sort", $typeIntro, __("ASC / DESC"));
        $result[] = new GraphqlInputValue("groupBy", $typeIntro, __("分组"));
        $result[] = new GraphqlInputValue("page", $numberTypeIntro, __("当前页"), "1");
        $result[] = new GraphqlInputValue("limit", $numberTypeIntro, __("每页大小"), "10");
        return $result;
    }

    /**
     * 获取查询某个字段的查询条件
     * @param $columnName
     * @param GraphqlSearchNode $node
     */
    private function get_Model_Args(GraphqlSearchNode $node)
    {
        if (!$node->has_value()) return [];
        return [
            new GraphqlInputValue("id", new GraphqlType("ID",null, GraphqlType::KIND_SCALAR),__("主键(数字)或uuid（字符串）, 当传入时忽略 wheres 参数")),
            new GraphqlInputValue("wheres", new GraphqlType(null,null, GraphqlType::KIND_LIST, new GraphqlType('Where',null,  GraphqlType::KIND_OBJECT)), __("查询条件数组")),
            new GraphqlInputValue("clause", new GraphqlType('Clause',null, GraphqlType::KIND_OBJECT), __("分支、分页、排序")),
        ];
    }



    /**
     * 返回系统有哪些指令类型
     * @param $node
     * @return string
     */
    private function schema_Directives($node)
    {
        $booleanType = new GraphqlType("Boolean", null, GraphqlType::KIND_SCALAR);
        $stringType = new GraphqlType("String", null, GraphqlType::KIND_SCALAR);
        $intType = new GraphqlType("Int", null, GraphqlType::KIND_SCALAR);
        $nonNullBooleanType = new GraphqlType(null, null, GraphqlType::KIND_NON_NULL,$booleanType);
        $nonNullStringType = new GraphqlType(null, null, GraphqlType::KIND_NON_NULL,$stringType);
        $directives = [
            new GraphqlDirective("include",
                __("Directs the executor to include this field or fragment only when the `if` argument is true."),
                [
                    new GraphqlInputValue("if", $nonNullBooleanType, __("Included when true."))
                ],
                [
                    "FIELD",
                    "FRAGMENT_SPREAD",
                    "INLINE_FRAGMENT"
                ]
            ),
            new GraphqlDirective("skip",
                __("Directs the executor to skip this field or fragment when the `if` argument is true."),
                [
                    new GraphqlInputValue("if",$nonNullBooleanType, __("Skipped when true."))
                ],
                [
                    "FIELD",
                    "FRAGMENT_SPREAD",
                    "INLINE_FRAGMENT"
                ]
            ),

            new GraphqlDirective("defer",
                __("Directs the executor to defer this fragment when the `if` argument is true or undefined."),
                [
                    new GraphqlInputValue("if",$booleanType, __("Deferred when true or undefined.")),
                    new GraphqlInputValue("label",$stringType, __("Unique name."))
                ],
                [
                    "FRAGMENT_SPREAD",
                    "INLINE_FRAGMENT"
                ]
            ),
            new GraphqlDirective("stream",
                __("Directs the executor to stream plural fields when the `if` argument is true or undefined."),
                [
                    new GraphqlInputValue("if",$booleanType, __("Stream when true or undefined.")),
                    new GraphqlInputValue("label",$stringType, __("Unique name.")),
                    new GraphqlInputValue("initialCount",$intType, __("Number of items to return immediately."))
                ],
                [
                    "FIELD"
                ]
            ),

            new GraphqlDirective("deprecated",
                __("Marks an element of a GraphQL schema as no longer supported."),
                [
                    new GraphqlInputValue("reason",$stringType, __("Explains why this element was deprecated, usually also including a suggestion for how to access supported similar data. Formatted using the Markdown syntax, as specified by [CommonMark](https=>//commonmark.org/).",'"No longer supported"')),
                ],
                [
                    "FIELD_DEFINITION",
                    "ARGUMENT_DEFINITION",
                    "INPUT_FIELD_DEFINITION",
                    "ENUM_VALUE"
                ]
            ),
            new GraphqlDirective("specifiedBy",
                __("Exposes a URL that specifies the behaviour of this scalar."),
                [
                    new GraphqlInputValue("url",$nonNullStringType, __("The URL that specifies the behaviour of this scalar.")),
                ],
                [
                    "SCALAR"
                ]
            )
        ];
        $results = [];
        foreach ($directives as $directive){
            $intro = new GraphqlIntrospection($node, $directive->get_data());
            $results[] = $intro->search();
        }
        return $results;
    }

    private function custom_query_types($node, &$results){
        $types = [];
        YZE_Hook::do_hook(YZE_GRAPHQL_CUSTOM_QUERY_TYPE, $types);
        if (!$types) return;

        foreach ($types as $type){
            $intro = new GraphqlIntrospection($node, $type->get_data());
            $results[] = $intro->search();
        }
    }

    private function custom_fields(&$results, &$names=[]){
        $types = [];
        YZE_Hook::do_hook(YZE_GRAPHQL_CUSTOM_QUERY_TYPE, $types);
        if (!$types) return;

        foreach ($types as $type){
            $names[] = $type->name;
            $results[] = new GraphqlField($type->name, $type, $type->description, $type->args);
        }
    }
}

?>
