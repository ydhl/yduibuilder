<?php
namespace yangzie;

/**
 * model基类，封装了基本的表与model的映射、操作。
 * yangzie约定表都必需包含以下的字段内容：
 * 	自增的主键
 *
 * 建议有：
 *
 * 一个标识一条记录的版本的字段,
 * 一个uuid字段，同自增的主键，提供给前端使用，因为不建议给前端暴露主键字段
 *
 * 其他：
 *
 * 不支持复合主键
 *
 * @author liizii
 *
 */
abstract class YZE_Model extends YZE_Object{
	use Graphql_Query;
	/**
	 * @var YZE_SQL
	 */
	private $sql;
	protected $records = array();
	/**
	 * 映射：
	 * <pre>
	 * [
	 * "attr1"=>["from"=>"id","to"=>"id","class"=>"","type"=>"one-one"],
	 * "attr2"=>["from"=>"id","to"=>"id","class"=>"","type"=>"one-many"]
	 * ]
	 * </pre>
	 * 获取：$this->attr1;
	 */
	protected $objects = array();
	/**
	 * 需要进行加密的字段名，
	 *
	 * 加密是对称的，对于这类指定的加密字段，通过yangzie Api进行读取和写入时（get,set）是自动进行加密解密的，对开发者是无感的.
	 *
	 * 如果不配置，开发者也可通过YZE_DBAImpl->encrypt,YZE_DBAImpl->decrypt加解密设置，
	 * 通过yangzie接口对数据进行读写的都支持加解密处理, 但如果是开发者自己写原生sql，则由开发者自行处理
	 * <br/><br/>
	 * 加解密不同的数据库实现会不同，Mysql是通过AES_ENCRYPT、AES_DECRYPT实现的；加解密的秘钥在__config__.php中YZE_DB_CRYPT_KEY
	 * 但要注意加密的内容是二进制格式（blob），或者自行通过bin2hex等转换成字符串存储，所以需要设置合适的字段类型
	 *
	 *
	 * @var array
	 */
	public $encrypt_columns = array();
	private $cache = array();
	/**
	 * 如果在INSERT插入行后会导致在一个UNIQUE索引或PRIMARY KEY中出现重复值，
	 * 则在出现重复值的行执行UPDATE；并用unique_key 配置的字段作为update的条件
	 * 如果不会导致唯一值列重复的问题，则插入新行. 用法：
	 * <pre>
	 * $unique_key = ["A"=>"Key_name_A","B"=>"Key_name_B","C"=>"Key_name_B","D"=>"Key_name_D","E"=>"Key_name_D"];
	 * </pre>
	 * A,B,C三个是独立唯一的字段，D,E是联合起来唯一的字段
	 * @var array
	 */
	protected $unique_key = array();

	/**
	 * 该model与其他model的关联关系
	 * 格式 [column=>[关联的表类名=>关联的目标表字段]]
	 * @var array
	 */
	protected $relation_column = array();


	public function get_unique_key(){
		return $this->unique_key;
	}

	/**
	 * 格式 [column=>[target_class=>关联的表类名,target_id=>关联的目标表字段, graphql_field=>]]
	 * @return array
	 */
	public function get_relation_columns(){
		return $this->relation_column;
	}
	/**
	 * 返回表名
	 */
	public function get_table(){
		return $this::TABLE;
	}
	/**
	 * 返回主键字段名,
	 */
	public function get_key_name(){
		return $this::KEY_NAME;
	}
	public function get_uuid_name(){
		return $this::UUID_NAME;
	}
	/**
	 * 返回实体对应的字段名,格式是：array('column'=>array(type,nullable))
	 * @return array
	 */
	public function get_columns(){
		return static::$columns;
	}

	/**
	 * 获取前端能看到的所有column，默认是返回所有的column；
	 * Model如果需要根据登录用户来控制能查询什么字段，则需要重载该方法
	 *
	 * @return array 需要返回self::$column一样的结构体
	 */
	public function get_graphql_column(){
		return $this->get_columns();
	}
	/**
	 * 把model的字段封装成GraphqlField 返回
	 * @return array<GraphqlField>
	 */
	public function get_graphql_fields(){
		$result = [];
		foreach ($this->get_graphql_column() as $columnName => $columnConfig) {
			$field = new GraphqlField($columnName,
				$this->get_Model_Field_Type($columnConfig, $columnName),
				$this->get_column_mean($columnName)
			);
			$result[] = $field;
		}
		return $result;
	}

	/**
	 * 获取Graphql字段的类型
	 *
	 * @param $columnConfig
	 * @param $columnName
	 * @return GraphqlType
	 */
	public function get_Model_Field_Type($columnConfig, $columnName)
	{
		$map = ['integer' => 'Int', 'date' => 'Date', 'string' => 'String', 'float' => 'Float'];
		return new GraphqlType(
			$columnConfig['type'] == 'enum' ? $this->get_table() . '_' . $columnName : $map[$columnConfig['type']],
			null,
			$columnConfig['type'] == 'enum' ? 'ENUM' : 'SCALAR');
	}

	public function get_module_name(){
		return $this::MODULE_NAME;
	}

	/**
	 * 是否给column字段设置来值
	 * @param $column
	 * @return bool
	 */
	public function has_set_value($column){
		return array_key_exists($column,$this->records);
	}

	/**
	 * 是否存在column字段
	 * @param $column
	 * @return bool
	 */
	public function has_column($column){
		return array_key_exists($column,static::$columns);
	}

	/**
	 * 对model转换成json对象字符串
	 *
	 * @author leeboo
	 * @return string json string
	 */
	public function to_json(){
		return json_encode($this->get_records());
	}

	/**
	 * 根据jsonString创建对象, 如果json不是有效的json，返回null
	 * @author leeboo
	 * @param string $json
	 * @return YZE_Model
	 */
	public static function from_Json($json){
		$array = json_decode($json, true);
		if(is_null($array))return null;

		return self::from_Array($array);
	}

	/**
	 * 根据jsonString创建对象, 如果json不是有效的json，返回null
	 * @param array $array
	 * @return YZE_Model
	 */
	public static function from_Array(array $array){
		$class = get_called_class();
		$obj = new $class();

		foreach($array as $name => $value){
			$obj->set($name, $value);
		}
		return $obj;
	}

	/**
	 * 返回主键值
	 * @return id
	 */
	public function get_key(){
		return $this->get($this->get_key_name());
	}

	/**
	 * 返回uuid字段
	 * @return mixed
	 */
	public function get_uuid(){
		return $this->get($this->get_uuid_name());
	}
	/**
	 * 对于时间字段，去掉datetime后面的时间部分，只留日期部分
	 * @param unknown_type $name
	 */
	public function get_date_val($name, $format="y年m月d日"){
		if (!$this->get($name) || $this->get($name)=="0000-00-00 00:00:00"){
			return "";
		}
		return date($format,strtotime($this->get($name)));
	}

	/**
	 * 返回model的记录数组
	 * @return array|mixed
	 */
	public function get_records(){
		return $this->records;
	}
	/**
	 * 获取指定字段的值
	 * @param unknown $name
	 */
	public function get($name){
		return @$this->records[$name];
	}
	/**
	 * 设值的时候会根据字段的类型对值进行相应的处理：
	 * <ul>
	 * <li>1. 如果是integer型，把值转型为int后再设值</li>
	 * <li>2. 如果是float型，把值转型为float后再设值</li>
	 * <li>3. 其他不变</li>
	 * <li>4. 对于有长度限制的字符串类型，会按照长度进行截取，超过部分忽略</li>
	 * </ul>
	 * @param unknown_type $name
	 * @param unknown_type $value
	 */
	public function set($name, $value){
		$props = $this->get_Field_props($name);
		switch ($this->get_Field_Type($name)) {
			case "integer":
				$value = is_null($value) ? null : intval($value);
				break;
			case "float":
				$value = is_null($value) ? null : floatval($value);
                break;
			case "string":
				$value = $props['length'] && $value ? mb_substr($value, 0, $props['length']) : $value;
            default:
                if (is_null($value)) {
                    $value = null;
                }
                break;
		}
		$this->records[$name] = $value;
		return $this;
	}

	/**
	 * 根据主键查询model
	 *
	 * @param int $id
	 * @return YZE_Model
	 * @throws YZE_DBAException
	 */
	public static function find_by_id($id){
		return YZE_DBAImpl::get_instance()->find($id,get_called_class());
	}

	/**
	 * 查询指定主键的model，并以id作为键返回数组
	 *
	 * @param string|array $ids
	 * @throws YZE_DBAException
	 * @return array<YZE_Model>
	 */
	public static function find_by_ids($ids){
	    $arr = [];
	    if (is_string($ids)){
	        $ids = explode(",", $ids);
	    }
	    $ids = array_filter($ids);
	    if( ! $ids)return $arr;
	    foreach (YZE_DBAImpl::get_instance()->find_by($ids, get_called_class()) as $obj){
	        $arr[ $obj->id ] = $obj;
	    }
	    return $arr;
	}
	/**
	 * 删除数据库中指定id的记录
	 * <br/><br/>
	 * 该方法触发Hook YZE_HOOK_MODEL_DELETE，传入已经清空主键的model
	 *
	 * @author leeboo
	 * @param int $id 主键
	 * @throws YZE_DBAException
	 * @return YZE_Model 返回被删除的model
	 */
	public static function remove_by_id($id){
		$class = get_called_class();

		if(!($class instanceof YZE_Model) && !class_exists($class)){
			throw new YZE_DBAException("Model Class $class not found");
		}

		$entity = $class instanceof YZE_Model ? $class : new $class;

		YZE_DBAImpl::get_instance()->delete($entity);
        return $entity;
	}

	/**
	 * 通过uuid查找model
	 *
	 * @param $uuid
	 * @return YZE_Model
	 */
	public static function find_by_uuid($uuid){
		return self::from()->where("uuid=:uuid")->get_Single([":uuid"=>$uuid]);
	}
	/**
	 * 删除数据库中指定uuid的记录
	 * <br/><br/>
	 * 该方法触发Hook YZE_HOOK_MODEL_DELETE，传入已经清空主键的model
	 *
	 * @author leeboo
	 * @param string $uuid
	 * @throws YZE_DBAException
	 */
	public static function remove_by_uuid($uuid){
		$dba = YZE_DBAImpl::get_instance();
		$entity = self::find_by_uuid($uuid);
		if(!$entity){
			throw new YZE_DBAException("Model ({$uuid}) not found");
		}
		$dba->delete($entity);
	}

	/**
	 * 判断$dateValue是否是空值，假值，0000-00-00，0000-00-00 00:00:00都看作空日期
	 *
	 * @param $dateValue
	 * @return bool
	 */
	public function is_Empty_Date($dateValue){
		return !$this->get($dateValue) || $this->get($dateValue)=="0000-00-00" || $this->get($dateValue)=="0000-00-00 00:00:00";;
	}


	/**
	 * 用attrs更新指定主键的model
	 *
	 * @author leeboo
	 * @param int | array $id 可以是一个主键，也可以是主键数组
	 * @param array $attrs
	 * @throws YZE_DBAException
	 * @return boolean
	 */
	public static function update_by_id($id, $attrs){
		$class = get_called_class();

		if(!($class instanceof YZE_Model) && !class_exists($class)){
			throw new YZE_DBAException("Model Class $class not found");
		}

		$entity = $class instanceof YZE_Model ? $class : new $class;

		$sql = new YZE_SQL();
		$sql->update("t", $attrs)->from(get_called_class(), "t");
		if (is_array($id)) {
		    $sql->where("t", $entity->get_key_name(), YZE_SQL::IN, $id);
		} else {
		    $sql->where("t", $entity->get_key_name(), YZE_SQL::EQ, $id);
		}

		YZE_DBAImpl::get_instance()->execute($sql);
	}


	/**
	 * 然后所有数据
	 *
	 * @return array
	 * @throws YZE_DBAException
	 */
	public static function find_all(){
		return YZE_DBAImpl::get_instance()->find_All(get_called_class());
	}

	/**
	 * 对当前model save时之前调用，可以进行验证等工作，抛出异常则会终止保存
	 * @return void
	 */
	public function before_Save(){
		// 在save前做些检查等工作
	}

	/**
	 * 保存(update,insert)记录；如果有主键，则更新；没有则插入；并返回插入或更新的记录的主键
	 * 插入情况，根据$type进行不同的插入策略:
	 * <ol>
	 * <li>INSERT_NORMAL：普通插入语句, 默认情况</li>
	 * <li>INSERT_NOT_EXIST： 指定的$checkSql条件查询不出数据时才插入，如果插入、更新成功，会返回主键值，如果插入失败会返回0，这时的entity->get_key()返回0</li>
	 * <li>INSERT_NOT_EXIST_OR_UPDATE： 指定的$checkSql条件查询不出数据时才插入, 查询出数据则更新这条数据；如果插入、更新成功，会返回主键值，如果插入失败会返回0，这时的entity->get_key()返回0 </li>
	 * <li>INSERT_EXIST： 指定的$checkSql条件查询出数据时才插入，如果插入、更新成功，会返回主键值，如果插入失败会返回0，这时的entity->get_key()返回0</li>
	 * <li>INSERT_ON_DUPLICATE_KEY_UPDATE： 有唯一健冲突时更新其它字段</li>
	 * <li>INSERT_ON_DUPLICATE_KEY_REPLACE： 有唯一健冲突时先删除原来的，然后在插入</li>
	 * <li>INSERT_ON_DUPLICATE_KEY_IGNORE： 有唯一健冲突时忽略，不抛异常</li>
	 * </ol>
	 * <br/>
	 * 更新时该方法触发Hook YZE_HOOK_MODEL_UPDATE，传入更新的model
	 *
	 * 插入时该方法触发Hook YZE_HOOK_MODEL_INSERT，传入插入后的model
	 *
	 * @param string $type YZE_SQL::INSERT_XX常量
	 * @param YZE_SQL|null $checkSql 完整的判断查询sql
	 * @throws YZE_DBAException
	 * @return YZE_Model
	 */
	public function save($type=YZE_SQL::INSERT_NORMAL, YZE_SQL $checkSql=null){
		$this->before_Save();
	    YZE_DBAImpl::get_instance()->save($this, $type, $checkSql);
		return $this;
	}

	/**
	 * 对当前model进行保存，判断传入的字段值，如果这些值已存在，则更新，否则插入
	 *
	 * @param array $checkFields
	 * @return YZE_Model
	 * @throws YZE_DBAException
	 * @author liizii
	 */
	public function insert_Or_Update( $checkFields ){
	    if ( ! $checkFields){
	        $this->save();
	        return $this;
	    }

	    $sql = new YZE_SQL();
	    $sql->from($this::CLASS_NAME, "__mine__");
	    foreach ($checkFields as $field){
	       $sql->where("__mine__", $field, YZE_SQL::EQ, $this->get($field));
	    }
	    $this->save(YZE_SQL::INSERT_NOT_EXIST_OR_UPDATE, $sql);
	    return $this;
	}

	/**
	 * 从数据库删除对象数据，并清除model对主键，
	 * <strong>对象所包含的数据还存在，只是主键不存在了</strong>
	 */
	public function remove(){
		YZE_DBAImpl::get_instance()->delete($this);
		return $this;
	}

	/**
	 * 从数据库中刷新
	 *
	 * @return YZE_Model
	 * @throws YZE_DBAException
	 * @author leeboo
	 */
	public function refresh(){
		$new = YZE_DBAImpl::get_instance()->find($this->get_key(), get_class($this));
		if($new){
			foreach ($new->get_records() as $name => $value){
				$this->set($name, $value);
			}
		}
		return $this;
	}

	/**
	 * 删除所有记录
	 * @return bool
	 * @throws YZE_DBAException
	 */
	public static function remove_all(){
		$sql = new YZE_SQL();
		$sql->delete()->from(get_called_class(),'a');
		return YZE_DBAImpl::get_instance()->execute($sql);
	}

	/**
	 * 把当前对象的主键值清空
	 * @return YZE_Model
	 */
	public function delete_key(){
		unset($this->records[$this->get_key_name()]);
		return $this;
	}

	/**
	 * 清空指定的字段
	 *
	 * @param $key
	 * @return YZE_Model
	 */
    public function delete_field($key){
        unset($this->records[$key]);
        return $this;
    }

	/**
	 * 用指定的data保存(update,insert)记录；如果有主键，则更新；没有则插入；并返回插入或更新的记录的主键
	 * 插入情况，根据$type进行不同的插入策略:
	 * <ol>
	 * <li>INSERT_NORMAL：普通插入语句, 默认情况</li>
	 * <li>INSERT_NOT_EXIST： 指定的$checkSql条件查询不出数据时才插入，如果插入、更新成功，会返回主键值，如果插入失败会返回0，这时的entity->get_key()返回0</li>
	 * <li>INSERT_NOT_EXIST_OR_UPDATE： 指定的$checkSql条件查询不出数据时才插入, 查询出数据则更新这条数据；如果插入、更新成功，会返回主键值，如果插入失败会返回0，这时的entity->get_key()返回0 </li>
	 * <li>INSERT_EXIST： 指定的$checkSql条件查询出数据时才插入，如果插入、更新成功，会返回主键值，如果插入失败会返回0，这时的entity->get_key()返回0</li>
	 * <li>INSERT_ON_DUPLICATE_KEY_UPDATE： 有唯一健冲突时更新其它字段</li>
	 * <li>INSERT_ON_DUPLICATE_KEY_REPLACE： 有唯一健冲突时先删除原来的，然后在插入</li>
	 * <li>INSERT_ON_DUPLICATE_KEY_IGNORE： 有唯一健冲突时忽略，不抛异常</li>
	 * </ol>
	 * <br/>
	 * 更新时该方法触发Hook YZE_HOOK_MODEL_UPDATE，传入更新的model
	 *
	 * 插入时该方法触发Hook YZE_HOOK_MODEL_INSERT，传入插入后的model
	 *
	 * @param array $posts
	 * @param string $prefix posts中每个字段的前缀；用于解决posts中同名字段的冲突，当设置后，则该model的每个字段数据都需要以prefix作为前缀打头
	 * @param string $type YZE_SQL::INSERT_XX常量
	 * @param YZE_SQL|null $checkSql 完整的判断查询sql
	 * @throws YZE_DBAException
	 * @return YZE_Model
	 */
	public function save_from_data($posts, $prefix="", $type=YZE_SQL::INSERT_NORMAL, YZE_SQL $checkSql=null)
	{
		foreach ( $this->get_columns() as $name => $define) {
			if (array_key_exists($prefix.$name, $posts)) {
				$this->set($name, $posts[$prefix.$name]);
			}
		}
		return $this->save($type, $checkSql);
	}

	public function __get($name){
	    $value = $this->get($name);
	    if (in_array($name, $this->encrypt_columns)){
	    	$value = YZE_DBAImpl::get_instance()->decrypt($value, YZE_DB_CRYPT_KEY);
		}
	    return $value;
	}

	public function __set($name, $value){
		if (in_array($name, $this->encrypt_columns)){
			$value = YZE_DBAImpl::get_instance()->encrypt($value, YZE_DB_CRYPT_KEY);
		}
	    return $this->set($name, $value);
	}

	/**
	 * 开启 Model Query的链式调用；Model Query的用法是以::from()开始，然后是连表或者where语句，最后以执行的动作结束：
	 * <ul>
	 * <li>select 查询多条数据</li>
	 * <li>get_single 查询一条数据</li>
	 * <li>delete 删除数据</li>
	 * <li>count</li>
	 * <li>sum</li>
	 * <li>max</li>
	 * <li>min</li>
	 * </ul>
	 * 调用from开始Model Query后就会缓存相应查询条件等信息，如果要开始新对查询，需要调用clean方法
	 *
	 * @param string $myAlias
	 * @return YZE_Model
	 */
	public static function from($myAlias=null){
		$obj = new static;
		$obj->init_Sql ($myAlias?:'m');
		return $obj;
	}
	/**
	 * 条件语句，变量用命名占位符: ("CHAR_LENGTH(title)=:title and (id>10 or id<20)")
	 *
	 * @param $where 条件字符串
	 * @return YZE_Model
	 */
	public function where($where){
		$this->init_Sql();
		$this->sql->native_Where($where);
		return $this;
	}

	/**
	 * 排序
	 *
	 * @param string $column
	 * @param string $sort
	 * @param string $alias
	 * @return YZE_Model
	 */
	public function order_By($column, $sort, $alias=null){
		$this->init_Sql();
		$this->sql->order_by($alias ?: "m", $column, $sort);
		return $this;
	}

	/**
	 * 分组
	 * @param string $column
	 * @param string $alias
	 * @return YZE_Model
	 */
	public function group_By($column, $alias=null){
		$this->init_Sql();
		$this->sql->group_by($alias ?: "m", $column);
		return $this;
	}

	/**
	 * 分页
	 * @param int $start 开始索引
	 * @param int $limit 返回条数
	 * @return YZE_Model
	 */
	public function limit($start, $limit){
		$this->init_Sql();
		$this->sql->limit($start, $limit);
		return $this;
	}

	/**
	 * 左连接
	 * @param string $joinClass 连接的model class
	 * @param string $joinAlias 连接的model的别名
	 * @param string $join_on 连接条件
	 * @return YZE_Model
	 */
	public function left_join($joinClass, $joinAlias, $join_on){
		$this->init_Sql ();

		$this->sql->left_join($joinClass, $joinAlias, $join_on);
		return $this;
	}
	/**
	 * 右连接
	 * @param string $joinClass 连接的model class
	 * @param string $joinAlias 连接的model的别名
	 * @param string $join_on 连接条件
	 * @return YZE_Model
	 */
	public function right_join( $joinClass, $joinAlias, $join_on){
		$this->init_Sql ();

		$this->sql->right_join($joinClass, $joinAlias, $join_on);
		return $this;
	}
	/**
	 * 内连接
	 * @param string $joinClass 连接的model class
	 * @param string $joinAlias 连接的model的别名
	 * @param string $join_on 连接条件
	 * @return YZE_Model
	 */
	public function join($joinClass, $joinAlias, $join_on){
		$this->init_Sql ();

		$this->sql->join($joinClass, $joinAlias, $join_on);
		return $this;
	}

	/**
	 * 该方法需要在最后调用，该方法直接返回查询结果数组, 该方法调用后sql中where部分会保留
	 *
	 * @param array $params [:field=>value] 命名占位符及其值
	 * @param unknown $alias 要选择的对象的别名，如果有联合查询，没有指定alias则返回所有的数据
	 * @return array<YZE_Model>
	 * @throws YZE_DBAException
	 */
	public function select(array $params=array(), $alias=null){
		$this->init_Sql ();
		if ( ! $this->sql->has_from()){
			$this->sql->from(static::CLASS_NAME, $alias ?: "m");
		}
		if ($alias){
			$this->sql->select($alias);
		}

		$obj = YZE_DBAImpl::get_instance()->select($this->sql, $params);

		$this->sql->clean_select();
		return $obj;
	}

	/**
	 * 该方法需要在最后调用，该方法直接返回查询结果对象, 该方法调用后sql中where部分会保留
	 *
	 * @param array $params [:field=>value] 命名占位符及其值
	 * @param unknown $alias 要选择的对象的别名，如果有联合查询，没有指定alias则返回所有的数据
	 * @return YZE_Model
	 * @throws YZE_DBAException
	 */
	public function get_Single(array $params=array(), $alias=null){
		$this->init_Sql ();

		if ( ! $this->sql->has_from()){
			$this->sql->from(static::CLASS_NAME, $alias ?: "m");
		}
		if ($alias){
			$this->sql->select($alias);
		}
		$this->sql->limit(1);
		$obj = YZE_DBAImpl::get_instance()->get_Single($this->sql, $params);
		$this->sql->clean_select();
		return $obj;
	}
	/**
	 * 返回count结果, 该方法调用后sql中where部分会保留
	 *
	 * @param string $field count的字段
	 * @param array $params [:field=>value]格式的数组
	 * @param unknown $alias alias要选择的对象的别名，如果有联合查询；没有指定alias，则默认是直接类，也就是第一个调用的静态类，如TestModel::where()->Left_jion()->count()中的TestModel
	 * @param bool $distinct 是否distinct
	 * @return int
	 */
	public function count($field, array $params=array(), $alias=null, $distinct=false){
		$this->init_Sql();
		if ( ! $alias){
			$alias = "m";
		}
		if ( ! $this->sql->has_from()){
			$this->sql->from(static::CLASS_NAME, $alias ?: "m");
		}
		$this->sql->count($alias , $field, "COUNT_ALIAS", $distinct);

		$obj = YZE_DBAImpl::get_instance()->get_Single($this->sql, $params);
		$this->sql->clean_select();
		if ( ! $obj)return 0;
		$obj = is_array($obj) ? $obj[$alias] : $obj;
		return intval($obj ? $obj->Get("COUNT_ALIAS") : 0);
	}
	/**
	 * 返回sum结果, 该方法调用后sql中where部分会保留
	 * @param unknown $field sum的 字段
	 * @param array $params [:field=>value]格式的数组
	 * @param unknown $alias alias要选择的对象的别名，如果有联合查询；没有指定alias，则默认是直接类，也就是第一个调用的静态类，如TestModel::where()->Left_jion()->sum()中的TestModel
	 * @return int
	 */
	public function sum($field, array $params=array(), $alias=null){
		$this->init_Sql();
		if ( ! $alias){
			$alias = "m";
		}
		if ( ! $this->sql->has_from()){
			$this->sql->from(static::CLASS_NAME, $alias ?: "m");
		}
		$this->sql->sum($alias, $field, "SUM_ALIAS");

		$obj = YZE_DBAImpl::get_instance()->get_Single($this->sql, $params);
		$this->sql->clean_select();
		if ( ! $obj)return 0;
		$obj = is_array($obj) ? $obj[$alias] : $obj;
		return $obj ? $obj->Get("SUM_ALIAS") : 0;
	}
	/**
	 * 返回max结果, 该方法调用后sql中where部分会保留
	 * @param unknown $field max 字段
	 * @param array $params [:field=>value]格式的数组
	 * @param unknown $alias alias要选择的对象的别名，如果有联合查询；没有指定alias，则默认是直接类，也就是第一个调用的静态类，如TestModel::where()->Left_jion()->sum()中的TestModel
	 * @return int
	 */
	public function max($field, array $params=array(), $alias=null){
		$this->init_Sql();
		if ( ! $alias){
			$alias = "m";
		}
		if ( ! $this->sql->has_from()){
			$this->sql->from(static::CLASS_NAME, $alias ?: "m");
		}
		$this->sql->max($alias, $field, "MAX_ALIAS");

		$obj = YZE_DBAImpl::get_instance()->get_Single($this->sql, $params);
		$this->sql->clean_select();
		if ( ! $obj)return 0;
		$obj = is_array($obj) ? $obj[$alias] : $obj;
		return $obj ? $obj->Get("MAX_ALIAS") : 0;
	}
	/**
	 * 返回max结果, 该方法调用后sql中where部分会保留
	 * @param unknown $field min 字段
	 * @param array $params [:field=>value]格式的数组
	 * @param unknown $alias alias要选择的对象的别名，如果有联合查询；没有指定alias，则默认是直接类，也就是第一个调用的静态类，如TestModel::where()->Left_jion()->sum()中的TestModel
	 * @return int
	 */
	public function min($field, array $params=array(), $alias=null){
		$this->init_Sql();
		if ( ! $alias){
			$alias = "m";
		}
		if ( ! $this->sql->has_from()){
			$this->sql->from(static::CLASS_NAME, $alias ?: "m");
		}
		$this->sql->min($alias, $field, "MIN_ALIAS");

		$obj = YZE_DBAImpl::get_instance()->get_Single($this->sql, $params);
		$this->sql->clean_select();
		if ( ! $obj)return 0;
		$obj = is_array($obj) ? $obj[$alias] : $obj;
		return $obj ? $obj->Get("MIN_ALIAS") : 0;

	}

	/**
	 * 删除满足条件对记录
	 *
	 * @param array $params
	 * @param string $alias
	 * @return YZE_Model
	 * @throws YZE_FatalException
	 */
	public function delete(array $params=array(), $alias=null){
		$this->init_Sql();
		if ( ! $alias){
			$alias = "m";
		}
		if ( ! $this->sql->has_from()){
			$this->sql->from(static::CLASS_NAME, $alias ?: "m");
		}
		$statement = YZE_DBAImpl::get_instance()->get_Conn()->prepare($this->sql->delete()->__toString());
		if(! $statement->execute($params) ){
		    throw new YZE_FatalException(join(",", $statement->errorInfo()));
		}
		$this->sql->clean_select();
		return $this;
	}

	/**
	 * 清空from开始的查询，开始新查询
	 * @return YZE_Model
	 */
	public function clean(){
		$this->sql->clean();
		return $this;
	}

	/**
	 * 清空from开始的Model Query中的where条件
	 * @return $this
	 */
	public function clean_where(){
		$this->sql->clean_where();
		return $this;
	}
	/**
	 * 清空表中所有数据
	 */
	public static function truncate(){
		$sql = "TRUNCATE `".YZE_DB_DATABASE."`.`".static::TABLE."`;";
		YZE_DBAImpl::get_instance()->exec($sql);
	}

	private function init_Sql($alias=null) {
		if ($this->sql == null){
			$this->sql = new YZE_SQL();
		}

		if ( ! $this->sql->has_from() && $alias){
		    $this->sql->from(static::CLASS_NAME, $alias);
		}
		return $this->sql;
	}

	private function get_Field_Type($field_name){
	    $columns = $this->get_columns();
	    return @$columns[$field_name]['type'];
	}
	private function get_Field_props($field_name){
	    $columns = $this->get_columns();
	    return @$columns[$field_name];
	}

	/**
	 * 返回uuid值
	 * @return mixed
	 * @throws YZE_DBAException
	 */
    public static function uuid() {
        $sql = "select uuid() as uuid";
        $rst = YZE_DBAImpl::get_instance()->native_Query($sql);
        $rst->next();
        return $rst->f("uuid");
    }

	/**
	 * 返回每个字段的具体的面向用户可读的含义，比如login_name表示登录名，
	 * 由子类实现
	 * @param $column
	 * @return mixed
	 */
    public function get_column_mean($column){
		return $column;
	}
}
?>
