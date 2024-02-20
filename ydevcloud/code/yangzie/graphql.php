<?php

namespace yangzie;

/**
 * Graphql处理控制器
 *
 * @category Framework
 * @package Yangzie
 * @author liizii
 * @link yangzie.yidianhulian.com
 */
class Graphql_Controller extends YZE_Resource_Controller {
    use Graphql__Schema, Graphql__Type, Graphql__Typename, Graphql_Query, Graphql_Mutation;
    private $operationType = 'query';
    private $operationName;
    /**
     * 变量
     * @var
     */
    private $vars;
    /**
     * 变量的默认值
     * @var array
     */
    private $varDefault;

    private $fetchActRegx = "/:|\{|\}|\(.+?\)|\w+|\.{1,3}|\\$|\#[^\\n]*/miu";
    private $allModelTypes;
    public function response_headers(){
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
            ];
    }
    public function post_index() {
        return $this->index();
    }
    public function index() {
        $this->layout = '';
        try{
            // 1. 线解析graphql成语法结构体
            list($query, $vars) = $this->fetch_Request();
            $this->vars = $vars;
            $nodes = $this->parse($query);
            if ($this->operationType == "mutation"){
                return $this->mutation($nodes);
            }
            $result = [];
            $count = [];
            // 2. 对每个结构进行数据查询
            foreach ($nodes as $node) {
                //2.1 内省特殊的查询：如__SCHEMA 向服务端询问有哪些可查询端内容 https://graphql.cn/learn/introspection/
                if (preg_match("/^__/", $node->name, $matches)){
                    $method = $node->name;
                    if(!method_exists($this, $method)){
                        throw new YZE_FatalException('can not query for '.$method.', method not found');
                    }
                    $result[$node->name] = $this->$method($node);
                    continue;
                }

                // 2.2 具体数据查询
                if ($node->name != "count"){
                    $total = 0;
                    $result[$node->alias?:$node->name] = $this->query($node->name, $node, $total);
                    $count[$node->alias?:$node->name] = $total;
                }else{
                    $result[$node->alias?:$node->name] = [];
                }
            }
            if (isset($result['count'])){
                $result['count'] = $count;
            }
            // 3. 返回结构
            return GraphqlResult::success($this, $result);
        }catch (\Exception $e){
            return GraphqlResult::error($this, $e->getMessage());
        }
    }

    /**
     * 根据请求端方法（post/get）已经传参端方式，获取请求中端数据
     * @return array [0=>查询字符串, 1=>变量字符串, 2=>操作名称字符串]
     */
    private function fetch_Request() {
        $request = $this->request;

        if (strcmp(@$_SERVER['CONTENT_TYPE'], 'application/json') === 0 ){
            $content = json_decode(trim(file_get_contents("php://input")), true);
            return [@$content['query'],@$content['variables'],@$content['operationName']];
        }

        return[
            trim($request->get_from_request('query')),
            trim($request->get_from_request('variables')),
            trim($request->get_from_request('operationName'))
        ];
    }

    /**
     * 解析请求并对field做验证，如果有错误抛出异常。
     * query IntrospectionQuery { __schema { queryType { name } } }返回的结构体格式如下：
     * <pre>
     * [
     *  0=>[
     *   'name'=>'__schema',
     *   'sub'=>[
     *      ... 下面的结构体
     *   ]
     *  ]
     * ]
     * </pre>
     * 每个结构体的格式如下：[name=>名称, sub=>[下面的结构体], args=>[参数结构体]]；
     * 参数结构体的格式如下：[name=>参数名, default=>默认值]
     * @throws YZE_FatalException
     * @return array<GraphqlSearchNode>
     */
    private function parse($query){
        //用正则来分离query里面的结构
        preg_match_all($this->fetchActRegx, $query, $matches);
        //处理query 或者 mutation name

        $acts = $matches[0];
        if (!$acts){
            throw new YZE_FatalException('query is missing');
        }

        //{
        //query {
        //query operationName {
        //query operationName(arg) {
        //mutation {
        //mutation operationName {
        //mutation operationName(arg) {的情况
        if (!strcasecmp('query', $acts[0]) || !strcasecmp('mutation', $acts[0])){
            $this->operationType = strtolower($acts[0]);
            if ($acts[1]!="{"){
                $this->operationName = $acts[1];
                if ($acts[2]!='{'){
                    $this->parse_var_default($acts[2]);
                    return $this->fetch_Node(array_slice($acts, 4));
                }
                return $this->fetch_Node(array_slice($acts, 3));
            }
            return $this->fetch_Node(array_slice($acts, 2));
        }
        // 直接{开头的情况
        return $this->fetch_Node(array_slice($acts, 1));
    }

    private function parse_var_default($args){
        // 处理默认值
    }

    /**
     * 提取指定的fragment
     * @param $acts
     * @return array<GraphqlSearchNode>
     */
    private function fetch_Fragment($acts, $fragmentName) {
        $fragmentIndex = -1;
        foreach ($acts as $index => $act) {
            if (!strcasecmp('fragment', $acts[$index]) && !strcasecmp($acts[$index+1], $fragmentName)){
                $fragmentIndex = $index;
                break;
            }
        }
        if ($fragmentIndex==-1) return [];

        while (true) {
            if (!strcasecmp('{', $acts[$fragmentIndex])){
                break;
            }
            $fragmentIndex++;
        }
        return $this->fetch_Node(array_slice($acts, $fragmentIndex + 1));
    }

    /**
     * 遍历提取的关键字，然后解析出节点，传入的数据中不需要头的{
     * @param $acts
     * @param int $fetchedLength
     * @return array<GraphqlSearchNode>
     */
    private function fetch_Node ($acts, &$fetchedLength=0) {
        $nodes = [];
        $currNode = new GraphqlSearchNode();
        $index = 0;
        if (!$acts) return $nodes;
        while (true){
            // 解析完了
            if ($index==count($acts)-1) {
                $fetchedLength = $index;
                if ($currNode->name) $nodes[] = $currNode;
                return $nodes;
            }
            $act = $acts[$index++];

            // 遇到}表示当前节点节点解析完了
            if ($act=="}") {
                $fetchedLength = $index;
                if ($currNode->name) $nodes[] = $currNode;
                return $nodes;
            }

            // 开始解析新节点
            if ($act == "{"){
                $subLength = 0;
                $currNode->sub = $this->fetch_Node(array_slice($acts, $index), $subLength);
                $index += $subLength;
                $nodes[] = $currNode;
                $currNode = new GraphqlSearchNode();
                continue;
            }
            //参数处理
            if (substr($act, 0, 1) == "("){
                $arg_acts = $this->parse_args($act);
                $currNode->args = $this->fetch_Args($arg_acts);
                continue;
            }
            // ：别名处理,:后面是别名，index往后移动一位
            if ($act == ":"){
                $alias = $acts[$index++];
                $currNode->alias = $currNode->name;
                $currNode->name = $alias;
                continue;
            }
            // fragment 处理，后面是fragment，index移动一位
            if ($act == "..."){
                $nodes = array_merge($nodes, $this->fetch_Fragment($acts, $acts[$index++]));
                $currNode = new GraphqlSearchNode();
                continue;
            }
            // 正常节点名称
            if ($currNode->name){
                $nodes[] = $currNode;
                $currNode = new GraphqlSearchNode();
            }
            $currNode->name = $act;
        }
        return $nodes;
    }

    /**
     * 逐个字符遍历，提取参数名和参数值和参数列表的元字符[]{}
     *
     * 测试字符串
     * '(id: "\"{(1000),", a:"(2\\\')", c:1)';
     *
     * '(wheres: [{column: "id:,{()}[]\",", op: "=", value: "\"】28中文\"}"}], id: 2, c: "c", a:$a)'
     *
     * (a:1,b:2)
     *
     * (wheres:[{column:"name",op: "like", value:[$name]}])
     *
     * @param $argString
     * @return array
     */
    private function parse_args($argString){
        $acts = [];
        $index = 0;
        $words = [];
        $isHandleValue = false;
        $isInQuote = null;
        while (true){
            if ($index >= mb_strlen($argString)) {
                // 剩余的内容
                if ($words)$acts[] = join('', $words);
                break;
            }
            $c = mb_substr($argString, $index++, 1);

            // case 0 在没有值内容时，遇到的元字符
            if (in_array($c, ['{', '}', '[', ']']) && !$isHandleValue){
                if($c =="]"){
                    if ($words) $acts[] = join('', $words);//[$name]的情况
                    $words = [];
                }
                $acts[] = $c;
                continue;
            }

            // case 1 在没有处理值时遇到: 表示参数名结束，如果当前在处理值，那么:是值内容，比如name: "value:"
            if ($c == ":" && !$isHandleValue){//名
                $words[] = $c;
                $acts[] = join('', $words);
                $words = [];

                // 名后面就是值开始
                $isHandleValue = true;

                // 往下推直到值的第一个字符不是空格的字符
                while(true){
                    $c = mb_substr($argString, $index++, 1);
                    if($c!=" ") break;
                }
                if (in_array($c, ["{","["])){
                    $isHandleValue = false; // 下级参数
                    $acts[] = $c;
                }else{
                    if (in_array($c, ["'",'"'])){ // 引号值
                        $isInQuote = $c;
                    }
                    $words[] = $c;
                }
                continue;
            }

            // 检测是否值结束，值的结束以,})
            if ($isHandleValue && in_array($c, [',','}',')'])){
                $isFinishedValue = false;
                if (!$isInQuote){// 非字符串值是没有引号的，那么遇到这些字符就表示结束
                    $isFinishedValue = true;
                }else{// 字符串则判断前一个字符是"，前2个字符不是\则是结束,比如"", "\",",
                    // $prev_c = mb_substr($argString, $index-2, 1);
                    // $prev_2nd_c = mb_substr($argString, $index-3, 1);
                    $prev_c = end($words);
                    $prev_2nd_c = prev($words);
                    reset($words);

                    if (strlen($prev_c) && strlen($prev_2nd_c) && $prev_c == $isInQuote && $prev_2nd_c != '\\'){
                        $isFinishedValue = true;
                    }
                }
                if ($isFinishedValue){
                    $acts[] = join('', $words);
                    $isHandleValue = false;
                    $isInQuote = null;
                    $words = [];
                    if ($c == "}"){// 由于这时由于isInQuote的影响，没有进入case 0
                        $acts[] = "}";
                    }

                    continue;
                }
            }
            if (($c == "(" || $c == ")") && !$isInQuote){// 忽略(),
                continue;
            }
            if (($c == "," || $c == " ") && !$isHandleValue){// 忽略参数名分隔符，和对应的空格,
                continue;
            }

            $words[] = $c;
        }
        return $acts;
    }

    private function array_key_last($array){
        $keys = array_keys($array);
        return end($keys);
    }

    private function fetch_Args_array ($end, $acts, &$fetchedLength=0) {
        $args = [];
        $index = 0;
        while (true) {
            $act = @$acts[$index++];
            // 解析完了
            if ($act == $end || $index > count($acts)) {
                $fetchedLength = $index;
                break;
            }

            // 开始解析下级数组
            if ($act == "{" || $act == "[") {
                $subLength = 0;
                $jsonValue = $this->fetch_Args_array($act == "[" ? "]" : "}",array_slice($acts, $index), $subLength);
                $index += $subLength;
                $key_last = $this->array_key_last($args);
                if ($key_last){
                    $args[$key_last] = $jsonValue;
                }else{
                    $args[] = $jsonValue;
                }
                continue;
            }
            if (substr($act,-1) == ":"){
                $args[rtrim($act, ":")] = null;
            }else{
                if (substr($act,0,1) == '$'){
                    $args[$this->array_key_last($args)] = $this->vars[substr($act,1)];
                }else{
                    $args[$this->array_key_last($args)] = trim($act, '"');
                }
            }
        }
        return $args;
    }
    /**
     * 提取查询字符串中的参数部分
     * @param $argString
     * @return array
     */
    private function fetch_Args ($acts) {
        $args = [];
        $currArg = new GraphqlSearchArg();
        $index = 0;
        while (true) {
            // 解析完了
            if ($index >= count($acts)) {
                if ($currArg->name){
                    $args[] = $currArg;
                }
                break;
            }
            $act = $acts[$index++];

            // 开始解析下级数组
            if ($act == "[" || $act == "{") {
                $subLength = 0;
                $jsonValue = $this->fetch_Args_array($act == "[" ? "]" : "}",array_slice($acts, $index), $subLength);
                $index += $subLength;

                $currArg->value = $jsonValue;
                $args[] = $currArg;
                $currArg = new GraphqlSearchArg();
                continue;
            }
            if (!$currArg->name){
                $currArg->name = rtrim($act, ":");
            }else{
                if (substr($act,0,1)=="\$"){
                    // 参数值是变量值，则通过变量获取
                    $currArg->value = $this->vars[substr($act, 1)];
                }else{
                    $currArg->value = trim($act, '"');
                }
                $args[] = $currArg;
                $currArg = new GraphqlSearchArg();
            }
        }

        return $args;
    }

    /**
     * 解析并返回查询结果，对field做验证，如果有错误抛出异常
     * @param $table
     * @param GraphqlSearchNode $node
     * @param $total
     * @return array|array[]|mixed|void|null
     * @throws YZE_DBAException
     * @throws YZE_FatalException
     */
    private function query($table, GraphqlSearchNode $node, &$total=0) {
        $models = $this->find_All_Models();
        if (!$node->sub){ // 没有查询具体的字段
            return null;
        }

        $class = @$models[$table];
        if ($class) return $this->model_query($class, $node, $total);
        // 自定义字段的查询
        $data = ['search'=>$node, 'rsts'=>[], 'total'=>0];
        YZE_Hook::do_hook(YZE_GRAPHQL_CUSTOM_SEARCH, $data);
        $total = $data["total"];
        return $data["rsts"];
    }

    private function basic_types() {
        return [
            'Int'=>['description'=>''],
            'Date'=>['description'=>'timestamp,date,datetime,time,year'],
            'String'=>['description'=>''],
            'Float'=>['description'=>''],
            'Boolean'=>['description'=>''],
            'ID'=>['description'=>'']
        ];
    }

    /**
     * 返回格式[tableName=>[__Field]]
     * @return mixed
     * @throws YZE_FatalException
     */
    private function get_all_model_types(){
        if ($this->allModelTypes) return $this->allModelTypes;
        $models = $this->find_All_Models();
        $searchNode = $this->parse("{
    __schema {
      types {
        ...FullType
      }
    }
}

fragment FullType on __Type {
    name
    fields(includeDeprecated: true) {
        name
        type{
            name
        }
    }
}
");
        $searchNode = reset($searchNode);
        $searchNode = GraphqlIntrospection::find_search_node_by_name($searchNode->sub, 'types');
        $types = $this->get_model_schema($models, $searchNode);
        foreach ($types as $type){
            $this->allModelTypes[$type['name']] = $type['fields'];
        }
        return $this->allModelTypes;
    }
}

?>
