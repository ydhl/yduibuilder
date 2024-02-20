<?php
namespace yangzie;
use \PDO;
use \PDOStatement;
use \app\App_Module;

/**
 * 与数据库进行交互DatabaseAdvisor接口，负责对数据库的crud操作，并且返回model
 * @author liizii
 *
 */
class YZE_DBAImpl extends YZE_Object
{
	private $conn;
	private static $me;

	private function __construct(){
		if($this->conn)return;
		$app_module = new App_Module();

		if(!$app_module->db_name)return;

		$this->conn =  new PDO(
			'mysql:dbname='.$app_module->get_module_config('db_name')
			.';port='.$app_module->get_module_config('db_port')
			.';host='.$app_module->get_module_config('db_host'),
			$app_module->get_module_config('db_user'),
			$app_module->get_module_config('db_psw')
		);
		$this->conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
		$this->conn->query('SET NAMES '.$app_module->get_module_config('db_charset'));
	}
	private function get_entity_record(YZE_Model $entity){
		$records = $entity->get_records();
		foreach ($records as $name => &$value) {
			if (in_array($name, $entity->encrypt_columns)){
				$value = YZE_DBAImpl::get_instance()->encrypt($value, YZE_DB_CRYPT_KEY);
			}
		}
		return $records;
	}
	private function save_update(YZE_Model $entity){
		$sql = new YZE_SQL();

		$sql->update('t', $this->get_entity_record($entity))
			->from(get_class($entity),"t");
		$sql->where("t",$entity->get_key_name(),YZE_SQL::EQ,$entity->get_key());
		$this->execute($sql);
		\yangzie\YZE_Hook::do_hook(YZE_HOOK_MODEL_UPDATE, $entity);
		return $entity->get_key();
	}
	private function valid_entity(YZE_Model  $entity) {
		$records = $entity->get_records();
		foreach ($entity->get_columns() as $column => $columnInfo) {
			if ($entity->get_key_name() == $column) continue;
			// null的情况
			if (!$columnInfo['null']){
				// 不为null有默认值
				if (key_exists($column, $records) && is_null($records[$column])) {
					throw new YZE_DBAException(sprintf(__("Field '%s' cannot be null"), $entity->get_column_mean($column)));
				}
				// 不为null并且没有默认值时验证是否设置了指
				if (!isset($columnInfo['default']) && !key_exists($column, $records)) {
					throw new YZE_DBAException(sprintf(__("Field '%s' doesn't have a default value"), $entity->get_column_mean($column)));
				}
			}
			if (!key_exists($column, $records)) continue;

			// 长度验证
			if ($columnInfo['length'] && mb_strlen($records[$column], 'utf8') > $columnInfo['length'])
				throw new YZE_DBAException(sprintf(__("Field '%s' length exceeds limit %s"), $entity->get_column_mean($column), $columnInfo['length']));
			// 枚举类型验证
			if ($columnInfo['type'] == 'enum' && !in_array($records[$column], call_user_func_array([$entity, "get_{$column}"], [])))
				throw new YZE_DBAException(sprintf(__("Field '%s' value %s is not in the accepted enum list"), $entity->get_column_mean($column), $records[$column]));
			// date类型验证
			if ($columnInfo['type'] == 'date' && !strtotime($records[$column])) {
				if (is_null($records[$column]) && $columnInfo['null']) continue;
				throw new YZE_DBAException(sprintf(__("Field '%s' value %s is not the date value"), $entity->get_column_mean($column), $records[$column]));
			}
		}
	}
	private function build_entity(YZE_Model $entity,$raw_datas,$table_alias){
		foreach ($raw_datas as $field_name => $field_value) {
			//如果从数据库中取出来的值为null，则不用对相应的对象属性赋值，因为默认他们就是null。
			//而赋值后再同步到数据库的时候，这些null值会被处理成''，0，如果字段是外键就会出错误，看_quoteValue
			if (is_null($field_value)) {
				continue ;
			}
			$alias = $table_alias."_";
			if (substr($field_name, 0, strlen($alias)) !== $alias) {
				continue;//当前字段不属于$entity
			}
			$field_name = substr($field_name, strlen($alias));
			if (!$entity->has_set_value($field_name)) {
				$value = self::filter_var($field_value);
				if (in_array($field_name, $entity->encrypt_columns)){
					$value = YZE_DBAImpl::get_instance()->decrypt($value, YZE_DB_CRYPT_KEY);
				}
				$entity->set( $field_name , $value);#数据库取出来编码
			}
		}
	}

	/**
	 * 返回PDO对象
	 * @return PDO
	 */
	public function get_Conn(){
		return $this->conn;
	}

	/**
	 * 重制数据库连接
	 * @return YZE_DBAImpl
	 */
	public function reset(){
		self::$me = null;
		$this->conn = null;
		return $this;
	}

	/**
	 * 通过指定的秘钥解密，
	 * @param string $hexString 加密支付串，通过encrypt加密后对内容
	 * @param string $key 密钥
	 * @return mixed
	 * @throws YZE_DBAException
	 */
	public function decrypt($hexString, $key){
		$rst = $this->native_Query("select AES_DECRYPT(".$this->quote(hex2bin($hexString)).",".$this->quote($key).") as var");
		$rst->next();
		return $rst->f('var');
	}

	/**
	 * 通过指定的秘钥加密, 返回hex格式的字符串
	 * @param string $value 要加密对内容
	 * @param string $key 加密密钥
	 * @return mixed|string
	 * @throws YZE_DBAException
	 */
	public function encrypt($value, $key){
		$rst = $this->native_Query("select AES_ENCRYPT(".$this->quote($value).",".$this->quote($key).") as var");
		$rst->next();
		$value = $rst->f('var');;
		return $value ? bin2hex($value) : $value;
	}

	/**
	 * 返回DBA实例，一次请求处理中就一个DBA实例，包含一个PDO连接
	 * @return YZE_DBAImpl
	 */
	public static function get_instance(){
		if(!isset(self::$me)){
			$c = __CLASS__;
			self::$me = new $c;
		}
		return self::$me;
	}

	/**
	 * 对value对值进行转义
	 * @param $value
	 * @return string
	 */
	public function quote($value){
	    return $this->conn->quote($value);
	}

	/**
	 * 批量查找class的指定id的对象
	 * @param array $ids 主键
	 * @param string $class 类名
	 * @return array model数组
	 * @throws YZE_DBAException
	 */
	public function find_by(array $ids, $class){
		if(!($class instanceof YZE_Model) && !class_exists($class)){
			throw new YZE_DBAException("Model Class $class not found");
		}

		$entity = $class instanceof YZE_Model ? $class : new $class;
		$ids = array_map(function ($item) {
			return intval($item);
		}, $ids);

		$sql = new YZE_SQL();
		$sql->from(get_class($entity),"a")
		->where("a",$entity->get_key_name(),YZE_SQL::IN,$ids);

		return $this->select($sql);
	}

	/**
	 * 根据主键查询记录，返回实体
	 * $key为查询表的主键
	 *
	 * @param int $key
	 * @param string|Model $class
	 * @throws YZE_DBAException
	 * @return YZE_Model
	 */
	public function find($key,$class){
		if(!($class instanceof YZE_Model) && !class_exists($class)){
			throw new YZE_DBAException("Model Class $class not found");
		}

		$entity = $class instanceof YZE_Model ? $class : new $class;

		$sql = new YZE_SQL();
		$sql->from(get_class($entity),"a")->limit(1);

		$sql->where("a",$entity->get_key_name(),YZE_SQL::EQ,$key);
		return $this->get_Single($sql);
	}

	/**
	 * 查询所有的记录，返回实体数组,键为主键值
	 * @param string|Model $class
	 * @throws YZE_DBAException
	 * @return array
	 */
	public function find_All($class){
		if(!($class instanceof YZE_Model) && !class_exists($class)){
			throw new YZE_DBAException("Model Class $class not found");
		}
		$entity = $class instanceof YZE_Model ? $class : new $class;
		$sql = new YZE_SQL();
		$sql->from(get_class($entity),"t");
		return $this->select($sql,[], $entity->get_key_name());
	}

	/**
	 * 原生查询，不返回对象，返回结果数组，如果是ddl，返回影响的行数
	 * 返回的结果数据由YZE_PDOStatementWrapper封装
	 *
	 * @see YZE_PDOStatementWrapper
	 * @throws YZE_DBAException
	 * @param string $sql
	 * @return YZE_PDOStatementWrapper
	 */
	public function native_Query($sql){
	    $pdo = $this->conn->query($sql);
	    if( ! $pdo){
	        throw new YZE_DBAException("sql error " . $sql . ":" . join(",",$this->conn->errorInfo()));
	    }
		return new YZE_PDOStatementWrapper($pdo);
	}

	/**
	 * 同select，区别是只返回一条数据：<br/>
	 * case1：如果sql没有联合查询，那么就返回所查询的model对象：<br/>
	 * <pre>
	 * $sql = new YZE_SQL();
	 * $sql->from(Model::class);
	 * $model = YZE_DBAImpl::get_instance()->get_Single($sql);
	 * </pre>
	 * case 2：如果sql有联合查询，那么返回的是一个数组，数组的索引就是联合查询时指定的别名，值就是对应的Model对象<br/>
	 * <pre>
	 * $sql = new YZE_SQL();
	 * $sql->from(ModelA::class,'a')->left_join(ModelB::class, 'b', 'a.id = b.aid');
	 * $models = YZE_DBAImpl::get_instance()->get_Single($sql);
	 * $modelA = $models['a'];
	 * $modelB = $models['b'];
	 * </pre>
	 * case 3：如果联合查询中也明确指定了只查询某个model，则只返回指定的model对象<br/>
	 * <pre>
	 * $sql = new YZE_SQL();
	 * $sql->from(Model::class,'a')->left_join(Model::class, 'b', 'a.id = b.aid')->select(a,'*');
	 * $modelA = YZE_DBAImpl::get_instance()->get_Single($sql);
	 * </pre>
	 *
	 * <br/><br/>
	 * 该方法会触发Hook YZE_HOOK_MODEL_SELECT，并传入查询的结果
	 * @param YZE_SQL $sql
	 * @param array $params 一个元素个数和将被执行的 SQL 语句中绑定的参数一样多的数组
	 * @throws YZE_DBAException
	 * @return YZE_Model
	 */
	public function get_Single(YZE_SQL $sql, $params=array()){
		$sql->limit(1);
		$result = $this->select($sql, $params);
		return @$result[0];
	}

	/**
	 * 根据条件查询所有的记录，返回实体数组,
	 * <strong>如果是联合查询，没有数据的对象返回null，比如下例中，如果b的记录并不存在，那么$models['b']则是null</strong>
	 * <pre>
	 * $sql = new YZE_SQL();
	 * $sql->from(ModelA::class,'a')->left_join(ModelB::class, 'b', 'a.id = b.aid');
	 * $models = YZE_DBAImpl::get_instance()->select($sql);
	 * $modelA = $models['a'];
	 * $modelB = $models['b'];
	 * </pre>
	 *
	 * <br/><br/>
	 * 该方法会触发Hook YZE_HOOK_MODEL_SELECT，并传入查询的结果
	 *
	 * @param YZE_SQL $sql
	 * @param string $index_field 返回的数组的索引，没有指定则是数字自增，指定指定名，则以该字段的值作为索引
	 * @param array $params 一个元素个数和将被执行的 SQL 语句中绑定的参数一样多的数组
	 * @throws YZE_DBAException
	 * @return array
	 */
	public function select(YZE_SQL $sql, $params=array(), $index_field=null){
		$classes = $sql->get_select_classes(true);

		if($params){
			$statement = $this->conn->prepare($sql->__toString());
			$statement->execute($params);
		}else{
			$statement = $this->conn->query($sql->__toString());
		}
		if(empty($statement)){
			throw new YZE_DBAException(join(",", $this->conn->errorInfo()));
		}

		if($statement->errorCode()!='00000'){
			throw new YZE_DBAException(join(",", $statement->errorInfo()));
		}

		$raw_result = $statement->fetchAll(PDO::FETCH_ASSOC);
		$num_rows = $statement->rowCount();
		$more_entity = count($classes) > 1;
		$entity_objects = array();

		//多表查询, 对每一行数据中的每一个entity, 构建好entity
		for($i=0;$i<$num_rows;$i++){#所有的对象
			foreach($classes as $alias => $cls){
				if($more_entity){
					$e = new $cls();
					$entity_objects[$i][ $alias ] = $e;
				}else{
					$entity_objects[$i] = new $cls();
				}
			}
		}
		$select_tables = $sql->get_select_table();

		$row=0;
		while ($raw_result){
			$raw_row_data = array_shift($raw_result);#每行
			if($more_entity){
				foreach($entity_objects[$row] as $alias => &$entity){
					$this->build_entity($entity,
							$raw_row_data,
							$alias);
				}
			}else{
				$this->build_entity($entity_objects[$row],
						$raw_row_data,
						array_search($entity_objects[$row]->get_table(), $select_tables));

			}
			$row++;
		}
		//把没有数据的对象设置为null
		$objects = array();
		foreach ($entity_objects as $index => $o) {
			if (is_array($o)) {
				foreach ($o as $n => $v) {
					$key = $index_field ? $v->get($index_field) : $index;
					$objects[$key][$n] = $v && $v->get_records() ? $v : null;
				}
			}else{
				$key = $index_field ? $o->get($index_field) : $index;
				$objects[$key] = $o && $o->get_records() ? $o : null;
			}
		}

		\yangzie\YZE_Hook::do_hook(YZE_HOOK_MODEL_SELECT, $objects);
		return $objects;
	}

	/**
	 * 删除传入的model，从数据库中删除对应主键的记录，并清空model的主键<br/>
	 * <strong>注意这时该model对应的记录在数据库中不存在了，但model对象还是存在的，只是没有了主键</strong>
	 * <br/><br/>
	 * 该方法触发Hook YZE_HOOK_MODEL_DELETE，传入已经清空主键的model
	 * @param YZE_Model $entity
	 * @throws YZE_DBAException
	 * @return YZE_Model 返回已经清空主键的model
	 */
	public function delete(YZE_Model $entity){
		$sql = new YZE_SQL();
		$sql->delete()->from(get_class($entity),"t");
		$sql->where("t",$entity->get_key_name(),YZE_SQL::EQ,$entity->get_key());
		$affected_row = $this->execute($sql);
		if($affected_row){
			$entity->delete_key();
		}
		\yangzie\YZE_Hook::do_hook(YZE_HOOK_MODEL_DELETE, $entity);
		return $entity;
	}

	/**
	 * 执行YZE_SQL 语句，返回影响的记录数
	 *
	 * @param YZE_SQL $sql
	 * @throws YZE_DBAException
	 * @return integer
	 */
	public function execute(YZE_SQL $sql){
		return $this->exec($sql->__toString());
	}

	/**
	 * 执行YZE_SQL 语句，返回影响的记录数
	 *
	 * @param string $sql
	 * @throws YZE_DBAException
	 * @return integer
	 */
	public function exec($sql){
		if(empty($sql))return false;
		$affected = $this->conn->exec($sql);
		if ($affected===false) {
			throw new YZE_DBAException(join(", ", $this->conn->errorInfo()));
		}
		return $affected;
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
	 * @param YZE_Model $entity
	 * @param string $type YZE_SQL::INSERT_XX常量
	 * @param YZE_SQL $checkSql 完整的判断查询sql
	 * @throws YZE_DBAException
	 * @return int 插入或更新的记录的主键
	 */
	public function save(YZE_Model $entity, $type=YZE_SQL::INSERT_NORMAL, YZE_SQL $checkSql=null){
		if(empty($entity)){
			throw new YZE_DBAException("save YZE_Model is empty");
		}
		$this->valid_entity($entity);
		if($entity->get_key()){//update
			return $this->save_update($entity);
		}
		$sql = new YZE_SQL();
		$extra_info = $type==YZE_SQL::INSERT_ON_DUPLICATE_KEY_UPDATE ? array_keys($entity->get_unique_key()) : $checkSql;
		//insert
		$sql->insert('t',$this->get_entity_record($entity), $type, $extra_info)
		->from(get_class($entity),"t");

		$rowCount = $this->execute($sql);
		$insert_id = $this->conn->lastInsertId();

		if($type == YZE_SQL::INSERT_EXIST || $type == YZE_SQL::INSERT_NOT_EXIST){
		    if( !$rowCount ){
		      	//这种情况下last insert id 得不到?
				// 这种情况下只会查一个表
				$check_table = $checkSql->get_select_table();
				$checkSql->clean_select()->select(array_key_first($check_table), [$entity->get_key_name()]);
				$checkRst = $this->get_Single($checkSql);
				$insert_id = $checkRst->get($entity->get_key_name());
			}
		}elseif($type == YZE_SQL::INSERT_NOT_EXIST_OR_UPDATE){
		    if( ! $rowCount ){
		        $alias = $checkSql->get_alias($entity->get_table());
		        $checkSql->update($alias, $this->get_entity_record($entity));
		        $this->execute($checkSql);
		        $checkSql->select($alias, array($entity->get_key_name()));
		        $obj = $this->get_Single($checkSql);
		        $insert_id = $obj->get_key();
		    }
		}else if($type==YZE_SQL::INSERT_ON_DUPLICATE_KEY_UPDATE){
		    //0 not modified, 1 insert, 2 update
		    if($rowCount==2 && count($entity->get_unique_key())>1){
		        $records = $entity->get_records();
		        $entity->refresh();
		        //当$update_on_duplicate_key是考虑有多个唯一健的更新情况；可能会由于某个唯一值冲突，导致其它唯一值没有更新的情况
		        //所以这里在update一下
		        foreach ($entity->get_unique_key() as $field){
		            $entity->set($field, $records[$field]);
		        }
		        $entity->save();
				$insert_id = $entity->get_key();
		    }
		}else if($type==YZE_SQL::INSERT_ON_DUPLICATE_KEY_IGNORE){
		    $insert_id = 0;
		}

		$entity->set($entity->get_key_name(), $insert_id);
		\yangzie\YZE_Hook::do_hook(YZE_HOOK_MODEL_INSERT, $entity);
		return $insert_id;
	}

	/**
	 * 是否开启自动提交
	 * @param boolean $boolean
	 */
	public function auto_Commit($boolean){
	    if($this->conn){
	       $this->conn->setAttribute(PDO::ATTR_AUTOCOMMIT, $boolean ? 1 : 0);
	    }
	}

	/**
	 * 开启事务，请求开始处理前，框架会自动开启事务
	 * @return void
	 */
	public function begin_Transaction(){
		try{
			if($this->conn)$this->conn->beginTransaction();
		}catch(\Exception $e){}
	}

	/**
	 * 提交事务，请求正常响应后，框架会提交事务
	 * @return void
	 */
	public function commit(){
		try{
			if($this->conn)$this->conn->commit();
		}catch(\Exception $e){}
	}

	/**
	 * 事务回滚，请求处理出现了任何未处理的异常，即进入了Controller的exception方法，框架会自动回滚
	 * @return void
	 */
	public function rollBack(){
		try{
		if($this->conn)$this->conn->rollBack();
		}catch(\Exception $e){}
	}

    /**
     * 查单个字段值，如果要一次查询出多个字段值用lookup_record
	 *
     * @param string $field 字段名或则能返回一个结果的sql函数及表达式 <strong style='color:red'>不能有as语句</strong>
     * @param string $table 表，可以多表联合
     * @param string $where "a=:b and c=:d" where条件，其中参数部分用命名占位符，比如这里:b, :d
     * @param array $values array(":b"=>"",":d"=>) 指定where占位符的的值，个数必须和where中的占位符数量一样
	 * @throws YZE_DBAException
     * @return string
     */
    public function lookup($field, $table, $where, array $values=array()) {
        $sql = "SELECT $field as f FROM `{$table}` WHERE {$where}";
        $stm = $this->conn->prepare($sql);

        if( ! $stm){
            throw new YZE_DBAException("can not prepare sql");
        }
        if($stm->execute($values)){
            $row = $stm->fetch(PDO::FETCH_ASSOC);
            return @$row['f'];
        }
        throw new YZE_DBAException(join(",", $stm->errorInfo()));
    }

    /**
     * 查询多个字段值，返回满足条件的一条结果数组，如果要返回多条结果，请用lookup_records
     *
     * @param string $fields 要查询的字段，可以有as语句
     * @param string $table 表，可以多表联合
     * @param string $where "a=:b and c=:d" where条件，其中参数部分用命名占位符，比如这里:b, :d
     * @param array $values array(":b"=>"",":d"=>) 指定where占位符的的值，个数必须和where中的占位符数量一样
	 * @throws YZE_DBAException
     * @return array key为查询的字段名或者as别名
     */
    public function lookup_record($fields, $table, $where="", array $values=array()) {
        $sql = "SELECT {$fields} FROM {$table}".($where ? " WHERE {$where}" :"");
        $stm = $this->conn->prepare($sql);

        if( ! $stm){
            throw new YZE_DBAException("can not prepare sql");
        }
        if($stm->execute($values)){
            return $stm->fetch(PDO::FETCH_ASSOC)?:[];
        }

        throw new YZE_DBAException(join(",", $stm->errorInfo()));
    }

    /**
     * 查询指定的一个或者多个字段，并返回满足条件的多条数据
     *
     * @param string $fields 要查询的字段，可以有as语句
     * @param string $table 表，可以多表联合
     * @param string $where "a=:b and c=:d" where条件，其中参数部分用命名占位符，比如这里:b, :d
     * @param array $values array(":b"=>"",":d"=>) 指定where占位符的的值，个数必须和where中的占位符数量一样
	 * @throws YZE_DBAException
     * @return array
     */
    public function lookup_records($fields, $table, $where="", array $values=array()) {
        $sql = "SELECT $fields FROM $table";
        if ($where) $sql .= " WHERE $where";
        $stm = $this->conn->prepare($sql);
        if( ! $stm){
            throw new YZE_DBAException("can not prepare sql");
        }
        if($stm->execute($values)){
            return $stm->fetchAll(PDO::FETCH_ASSOC)?:[];
        }
        throw new YZE_DBAException(join(",", $stm->errorInfo()));
    }

    /**
     * 更新记录，返回受影响的行数
	 *
     * @param string $table
     * @param string $fields 需要更新的字段及其值，如"foo=1,bar=:bar"，如果值是变量，也建议采用命名占位符，如果要直接传入变量，请确保已经用quote方法进行了转义，避免SQL注入攻击
	 * @param string $where "a=:b and c=:d" where条件，其中参数部分用命名占位符，比如这里:b, :d
	 * @param array $values array(":b"=>"",":d"=>) 指定where占位符的的值，个数必须和where中的占位符数量一样
	 * @throws YZE_DBAException
     * @return boolean
     */
    public function update($table, $fields, $where, array $values=array()) {
        $sql = "UPDATE $table SET $fields";
        if ($where) $sql .= " WHERE $where";

        $stm = $this->conn->prepare($sql);

        if( ! $stm){
            throw new YZE_DBAException("can not prepare sql");
        }
        if( $stm->execute($values) ===false){
            throw new YZE_DBAException(join(",", $stm->errorInfo()));
        }
        return true;
    }

    /**
     * 删除记录, 删除成功则返回true
	 *
     * @param string $table
     * @param string $where "a=:b and c=:d" where条件，其中参数部分用命名占位符，比如这里:b, :d
	 * @param array $values array(":b"=>"",":d"=>) 指定where占位符的的值，个数必须和where中的占位符数量一样
	 * @throws YZE_DBAException
     * @return boolean
     */
    public function deletefrom($table, $where, array $values=array()) {
        $sql = "DELETE FROM $table";
        if ($where) $sql .= " WHERE $where";
        $stm = $this->conn->prepare($sql);

        if( ! $stm){
            throw new YZE_DBAException("can not prepare sql");
        }
        if( $stm->execute($values) ===false){
            throw new YZE_DBAException(join(",", $stm->errorInfo()));
        }
        return true;
    }
    /**
     * 根据给定的checkSql判断如何插入记录; 成功返回新记录的主键.
	 * <br/><strong>该方法主要用来解决插入A表同时检查A表中是否存在给定记录的情况</strong>
	 * <br/><br/>
	 * 如果exist=true，则checkSql能查询记录时插入，查询不出记录则不插入；<br/>
	 * 如果exist=false，则checkSql不能查询出记录时插入，能查询出记录则不插入；
	 * <br/><br/>
	 * 如果没有插入，则可以根据update来设置该如何处理<br/>
	 * 如果update=false，这时抛异常<br/>
	 * 如果update=true，这时则把info中的记录做更新操作更新到table中去，更新的条件就是checkSQL中的where条件<br/>
     *
     * @param string $table 要插入的表
     * @param array $info array("field"=>"value") 要插入的字段及其值
     * @param string $checkSql 检查的子查询
     * @param array $checkInfo array(":field"=>"value");检查表的条件
     * @param boolean $exist true，表示存在是插入；false，表示不存在时插入
     * @param boolean $update 是否在存在是更新
     * @param string $key table表的主键名称
	 * @throws YZE_DBAException
     * @return int
     */
    public function check_Insert($table, $info, $checkSql, $checkInfo, $exist=false, $update=false, $key="id") {
        $sql_fields     = "";
        $sql_values     = "";
        $set            = "";
        $values         = $checkInfo;
        foreach ($info as $f => $v) {
            $sql_fields  .= "`" . $f . "`,";
            $sql_values  .= ":" . $f . ",";
            $set  .= "`{$f}`=:{$f},";
            $values[":" . $f] = $v;
        }

        $sql_fields  = rtrim($sql_fields, ",");
        $sql_values  = rtrim($sql_values, ",");
        $set         = rtrim($set, ",");

        $sql = "INSERT INTO `{$table}` ({$sql_fields}) SELECT {$sql_values} FROM dual WHERE ".($exist?"":"NOT")." EXISTS ({$checkSql})";
        $stm = $this->conn->prepare($sql);

        if( ! $stm){
            throw new YZE_DBAException("can not prepare sql");
        }
        if($stm->execute($values)===false){
            throw new YZE_DBAException(join(",", $stm->errorInfo()));
        }
        if ( !  $stm->rowCount()) {
            if( ! $update){
                throw new YZE_DBAException("not insert");
            }
            $where = preg_replace("/^.+where/", "", $checkSql);
            $sql = "UPDATE `{$table}` SET {$set} WHERE {$where}";
            $stm = $this->conn->prepare($sql);

            if( ! $stm){
                throw new YZE_DBAException("can not prepare sql");
            }
            if (! $stm->execute($values) ){
                throw new YZE_DBAException(join(",", $stm->errorInfo()));
            }

            preg_match_all("/(?P<words>:[^\s]+)/", $where, $matchWheres);
            $lookupValues = [];
            foreach($matchWheres['words'] as $word){
                $lookupValues[$word] = $values[$word];
            }

            return $this->lookup($key, $table, $where, $lookupValues);
        }

        return $this->conn->lastInsertId();
    }
    /**
     * 插入记录; 返回新记录的主键；如果要做有条件的插入请使用check_insert方法。
	 * <br/>
	 * 如果插入的表有唯一字段，可通过$duplicate_key指定这些唯一字段，存在唯一字段冲突后，则会更新对应的记录
     *
     * @param string $table 要插入的表名
     * @param array $info array("field"=>"value"); 要插入的字段及其值
     * @param array $duplicate_key array("field0","field1"); 指定表的唯一字段，如果指定，则会生成 INSERT_ON_DUPLICATE_KEY_UPDATE 语句，再指定的字段有唯一健冲突时执行更新
     * @param string $keyname 表主键名称 指定了$duplicate_key一定要设置
	 * @throws YZE_DBAException
     * @return int
     */
    public function insert($table, $info, $duplicate_key=array(), $keyname="") {
        if ( ! is_array($info) || empty($info) || empty($table))
            return false;

        $sql_fields     = "";
        $sql_values     = "";
        $values         = array();
        $update         = array();
        foreach ($info as $f => $v) {
            $sql_fields  .= "`" . $f . "`,";
            $sql_values  .= ":" . $f . ",";

            $values[":" . $f] = $v;
            if(array_search($f, $duplicate_key) === false){
                $update[] = "`{$f}`=VALUES(`{$f}`)";
            }
        }
        $sql_fields  = rtrim($sql_fields, ",");
        $sql_values  = rtrim($sql_values, ",");

        $sql = "INSERT INTO {$table} ({$sql_fields}) VALUES ({$sql_values})";
        if($duplicate_key){
            $sql .= " ON DUPLICATE KEY UPDATE {$keyname} = LAST_INSERT_ID({$keyname}),
            ".join(",", $update);
        }

        $stm = $this->conn->prepare($sql);

        if( ! $stm){
            throw new YZE_DBAException("can not prepare sql");
        }
        if ( ! $stm->execute($values) ) {
            throw new YZE_DBAException(join(",", $stm->errorInfo()));
        }

        return $this->conn->lastInsertId();
    }
    /**
     * 插入记录, 在有唯一键冲突时忽略插入，成功返回新记录的主键
     *
	 * @param string $table 要插入的表名
	 * @param array $info array("field"=>"value"); 要插入的字段及其值
	 * @throws YZE_DBAException
	 * @return int
	 */
    public function insert_Or_Ignore($table, $info) {
        $sql_fields     = "";
        $sql_values     = "";
        $values         = array();
        foreach ($info as $f => $v) {
            $sql_fields  .= "`" . $f . "`,";
            $sql_values  .= ":" . $f . ",";

            $values[":" . $f] = $v;
        }
        $sql_fields  = rtrim($sql_fields, ",");
        $sql_values  = rtrim($sql_values, ",");

        $sql = "INSERT IGNORE INTO {$table} ({$sql_fields}) VALUES ({$sql_values})";

        $stm = $this->conn->prepare($sql);

        if( ! $stm){
            throw new YZE_DBAException("can not prepare sql");
        }
        if ( ! $stm->execute($values) ) {
            throw new YZE_DBAException(join(",", $stm->errorInfo()));
        }

        return $this->conn->lastInsertId();
    }

    /**
     * 插入记录, 在有唯一键冲突时替换掉原来的记录，成功返回新记录的主键；
	 * <br/>
	 * 该方法适用于表中存储唯一字段的情况
     *
     * @param string $table
     * @param array $info array("field"=>"value");
	 * @throws YZE_DBAException
     * @return int
     */
    public function replace($table, $info) {
        $sql_fields     = array();
        $values         = array();
        foreach ($info as $f => $v) {
            $sql_fields[] = "`{$f}`=:{$f}";

            $values[":" . $f] = $v;
        }
        $sql_fields  = join(",", $sql_fields);

        $sql = "REPLACE INTO {$table} SET {$sql_fields}";

        $stm = $this->conn->prepare($sql);
        if( ! $stm){
            throw new YZE_DBAException("can not prepare sql");
        }
        if ( ! $stm->execute($values) ) {
            throw new YZE_DBAException(join(",", $stm->errorInfo()));
        }

        return $this->conn->lastInsertId();
    }

	/**
	 * 返回指定表的字段列表
	 * @param $table
	 * @return array
	 */
	public function table_fields($table) {
	    $sql="show columns from $table";
	    $stm=$this->conn->query($sql);

	    $fileds = array();
	    if($stm){
	        while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
	            $fileds[] = $row['Field'];
	        }
	    }

	    return $fileds;
	}

	/**
	 * CODE TO DB同步，把指定的model同步字段到数据库，如果表没有建立则建立表，建立就同步字段
	 * 这是初步实现了功能，不稳定，请勿使用在产品环境
	 *
	 * @param string $class
	 * @param boolean $reCreate 是否删除原来的重新创建
	 */
	public static function migration($class, $reCreate=false){
		$columns = $class::$columns;
		$table = $class::TABLE;
		$column_segments = array();

		$uniqueKey = "";

		foreach ($columns as $column => $defines){
			switch (strtoupper($defines['type'])){
				case "INTEGER": $type = "INT";break;
				case "TIMESTAMP": $type = "TIMESTAMP";break;
				case "DATE": $type = "date";break;
				case "FLOAT": $type = "FLOAT";break;
				case "ENUM": $type = "ENUM";break;
				case "STRING":
				default : $type = "VARCHAR(".(@$defines['length'] ? $defines['length'] : 45).")";break;
			}

			$uniqueKey .= $defines["unique"] ? ",UNIQUE INDEX `{$column}_UNIQUE` (`{$column}` ASC)" : "";
			$nullable = $defines["null"] ? "NULL" : "NOT NULL";
			$primaryID = $column==$class::KEY_NAME ? "AUTO_INCREMENT" : "";
			$default  = $defines["default"] != '' ? "DEFAULT ".$defines["default"] : "";

			$column_segments[] = "`{$column}` {$type} {$nullable} {$primaryID} {$default}";
		}
		$primary = "";
		if ($class::KEY_NAME){
			$primary = " , PRIMARY KEY (`".$class::KEY_NAME."`)";
		}
		if ($reCreate){
			$drop = "DROP TABLE `".YZE_DB_DATABASE."`.`{$table}`";
		}


		$sql = "CREATE TABLE IF NOT EXISTS `".YZE_DB_DATABASE."`.`{$table}` (".join(",", $column_segments)."{$primary}{$uniqueKey})
		ENGINE = InnoDB;";

		if ($drop){
			self::get_instance()->exec($drop);
		}

		self::get_instance()->exec($sql);
	}
}
class YZE_PDOStatementWrapper extends YZE_Object{
	/**
	 * @var PDOStatement
	 */
	private $db;
	private $result;
	private $index = -1;
	public function __construct(PDOStatement $db_mysql){
		$this->db = $db_mysql;
		$this->result = $this->db->fetchAll(PDO::FETCH_ASSOC);
	}
	public function reset(){
		$this->index = -1;
	}
	public function next(){
		$this->index +=1;
		return @$this->result[$this->index];
	}
	public function get_results(){
		return $this->result;
	}
	/**
	 * 如果提供了alias,则会已{$table_alias}_{$name}为字段名查找
	 * @param unknown $name
	 * @param unknown $table_alias
	 */
	public function f($name,$table_alias=null){
		return self::filter_var($this->result[$this->index][$table_alias ? "{$table_alias}_{$name}" : $name]);#数据库取出来编码
	}


	public function getEntity(YZE_Model $entity, $alias=""){
	   foreach (array_keys($entity->get_columns()) as $field_name) {
            $field_value = $this->f($field_name, $alias);
            if (is_null($field_value)) {
                continue ;
            }
            $entity->set( $field_name , $field_value);#数据库取出来编码
        }
	    return $entity;
	}
}
?>
