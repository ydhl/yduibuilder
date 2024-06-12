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
	private static $conn=[];
	private $db_name = '';

	private function connect($db_name, $force=false){
		$app_module = new App_Module();
		$db_name = $db_name ?: $app_module->get_module_config('default_db');
		$this->db_name = $db_name;

		if ($force && @self::$conn[$db_name]) self::$conn[$db_name] = null;

		// 没有数据库，或者数据库链接已经建立
		if (!$db_name || @self::$conn[$db_name]){
			$this->begin_Transaction();
			return;
		}

		$db_connection = $app_module->get_module_config('db_connections')[$db_name];

		try{
			self::$conn[$db_name] =  new PDO(
				'mysql:dbname='.$db_name
				.';port='.$db_connection['db_port']
				.';host='.$db_connection['db_host'],
				$db_connection['db_user'],
				$db_connection['db_psw'],
				$db_connection['db_params']
			);
		}catch (\PDOException $e){// 封装下，避免链接异常时暴露数据库链接信息
			throw new YZE_DBAException(join(' ', $e->errorInfo), $e->getCode());
		}
		self::$conn[$db_name]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);// 失败时抛出异常
		self::$conn[$db_name]->query('SET NAMES '.$db_connection['db_charset']);
		$this->begin_Transaction();
	}

	/**
	 * 如果是MySQL server has gone away错误，则重连数据库并执行传入的方法
	 * 其他情况抛出异常
	 * @param $errorCode
	 * @param $errorInfo
	 * @param $method
	 * @param ...$args
	 * @throws YZE_DBAException
	 * @return mixed|void
	 */
	private function check_connect($errorCode, $errorInfo, $method, ...$args){
		if(is_array($errorInfo)){
			$errorInfo = join(' ', $errorInfo);
		}
		if (preg_match("/MySQL server has gone away/", $errorInfo, $matches)){ // 2006 server has gone away
//			echo $errorInfo;
			$this->connect($this->db_name, true);
			return call_user_func([$this, $method], ...$args);
		}
		throw new YZE_DBAException($errorInfo, $errorCode);
	}
	public function __construct($db_name){
		$this->connect($db_name);
	}

	/**
	 * 返回DBA实例, 如果已经存在数据库链接则返回，不存在则创建，并开启事务
	 *
	 *  <strong>在调用get_instance得到实例后的后续操作都是对db_name数据库的，如果遇到不是同一个库的，需要再次get_instance($other_db_name)</strong>
	 *
	 * @param $db_name string 要链接的数据库名, 不指定则链接__config__中default_db指定的默认数据库
	 * @return YZE_DBAImpl
	 */
	public static function get_instance($db_name=null){
		return new static($db_name);
	}
	public function get_db_name(){
		return $this->db_name;
	}

	private function get_entity_record(YZE_Model $entity){
		$records = $entity->get_records();
		foreach ($records as $name => &$value) {
			if (in_array($name, $entity->encrypt_columns)){
				$value = $this->encrypt($value, YZE_DB_CRYPT_KEY);
			}
		}
		return $records;
	}
	private function save_update(YZE_Model $entity){
		$sql = new YZE_SQL();

		$sql->update('t', $this->get_entity_record($entity))
			->from(get_class($entity),"t", $entity->get_suffix());
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
			$alias = $table_alias."_";
			if (substr($field_name, 0, strlen($alias)) !== $alias) {
				continue;//当前字段不属于$entity
			}
			$field_name = substr($field_name, strlen($alias));

			if (!$entity->has_set_value($field_name)) {
				if (is_null($field_value)) {
					$entity->set( $field_name , NULL);
					continue;
				}
				$value = self::filter_var($field_value);
				if (in_array($field_name, $entity->encrypt_columns)){
					$value = $this->decrypt($value, YZE_DB_CRYPT_KEY);
				}
				$entity->set( $field_name , $value);#数据库取出来编码
			}
		}
	}

	/**
	 * 返回PDO对象
	 * @return PDO
	 */
	public function get_Conn($db_name=null){

		$app_module = new App_Module();
		$db_name = $db_name ?: $app_module->get_module_config('default_db');

		return self::$conn[$db_name];
	}
	public function get_all_Conn(){
		return self::$conn;
	}

	/**
	 * 重制数据库连接
	 * @return YZE_DBAImpl
	 */
	public function reset(){
		self::$conn  = [];
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
	 * 对value对值进行转义
	 * @param $value
	 * @return string
	 */
	public function quote($value){
	    return self::$conn[$this->db_name]->quote($value);
	}

	/**
	 * 批量查找class的指定id的对象
	 * @param array $ids 主键
	 * @param string $class 类名
	 * @return array model数组
	 * @throws YZE_DBAException
	 */
	public function find_by(array $ids, $class, $suffix){
		if(!($class instanceof YZE_Model) && !class_exists($class)){
			throw new YZE_DBAException("Model Class $class not found");
		}

		$entity = $class instanceof YZE_Model ? $class : new $class;
		$ids = array_map(function ($item) {
			return intval($item);
		}, $ids);

		$sql = new YZE_SQL();
		$sql->from(get_class($entity),"a", $suffix)
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
	public function find($key,$class, $suffix=null){
		if(!($class instanceof YZE_Model) && !class_exists($class)){
			throw new YZE_DBAException("Model Class $class not found");
		}

		$entity = $class instanceof YZE_Model ? $class : new $class;

		$sql = new YZE_SQL();
		$sql->from(get_class($entity),"a", $suffix)->limit(1);

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
		try {
			$stm = self::$conn[$this->db_name]->query($sql);
			return new YZE_PDOStatementWrapper($stm);
		}catch (\PDOException $e){
			return $this->check_connect($e->getCode(), $e->errorInfo, 'native_Query', $sql);
		}
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

		try{
			if($params){
				$statement = self::$conn[$this->db_name]->prepare($sql->__toString());
				$statement->execute($params);
			}else{
				$statement = self::$conn[$this->db_name]->query($sql->__toString());
			}
		}catch(\PDOException $e){
			return $this->check_connect($e->getCode(), $e->errorInfo, 'select', $sql, $params, $index_field);
		}

		$raw_result = $statement->fetchAll(PDO::FETCH_ASSOC);

		$num_rows = $statement->rowCount();
		$entity_objects = array();

		//多表查询, 对每一行数据中的每一个entity, 构建好entity
		for($i=0;$i<$num_rows;$i++){#所有的对象
			foreach($classes as $alias => $cls){
				$e = new $cls();
				$entity_objects[$i][ $alias ] = $e;
			}
		}
		$row=0;
		while ($raw_result){
			$raw_row_data = array_shift($raw_result);#每行
			foreach($entity_objects[$row] as $alias => &$entity){
				$this->build_entity($entity,
					$raw_row_data,
					$alias);
			}
			$row++;
		}
		//把没有数据的对象设置为null
		$objects = array();
		$has_more_class = count($classes)>1;
		foreach ($entity_objects as $index => $o) {
			foreach ($o as $n => $v) {
				$key = $index_field ? $v->get($index_field) : $index;
				if($has_more_class){
					$objects[$key][$n] = $v && array_filter($v->get_records()) ? $v : null;
				}else{
					$objects[$key] = $v && array_filter($v->get_records()) ? $v : null;
				}
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
		$sql->delete()->from(get_class($entity),"t",$entity->get_suffix());
		$sql->where("t",$entity->get_key_name(),YZE_SQL::EQ,$entity->get_key());
		$affected_row = $this->execute($sql);
		if($affected_row){
			$entity->delete_key();
		}
		\yangzie\YZE_Hook::do_hook(YZE_HOOK_MODEL_DELETE, $entity);
		return $entity;
	}

	/**
	 * 当前是否处于事务内
	 * @return mixed
	 */
	public function in_transaction(){
		return self::$conn[$this->db_name]->inTransaction();
	}
	/**
	 * 执行YZE_SQL 语句，返回影响的记录数
	 *
	 * 注意如果执行了DDL，mysql会隐式的提交事务，但PDO是无感的，在PDO看来事务还未提交，这个时候如果rollback则不会起作用；
	 * 隐式提交后，如果数据库被设置成自动提交模式，将恢复自动提交模式。
	 *
	 * @param YZE_SQL $sql
	 * @throws YZE_DBAException
	 * @return integer
	 */
	public function execute(YZE_SQL $sql){
		return $this->exec($sql->__toString());
	}

	/**
	 * 执行原生SQL语句，返回影响的记录数
	 *
	 * 注意如果执行了DDL，mysql会隐式的提交事务，但PDO是无感的，在PDO看来事务还未提交，这个时候如果rollback则不会起作用；
	 * 隐式提交后，如果数据库被设置成自动提交模式，将恢复自动提交模式。
	 *
	 * @param string $sql
	 * @throws YZE_DBAException
	 * @return integer
	 */
	public function exec($sql){
		if(empty($sql))return false;
		try{
			return self::$conn[$this->db_name]->exec($sql);
		}catch (\PDOException $e){
			return $this->check_connect($e->getCode(), $e->errorInfo, 'exec', $sql);
		}
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
		->from(get_class($entity),"t", $entity->get_suffix());

		$rowCount = $this->execute($sql);
		$insert_id = self::$conn[$this->db_name]->lastInsertId();

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
	    if(self::$conn[$this->db_name]){
	       self::$conn[$this->db_name]->setAttribute(PDO::ATTR_AUTOCOMMIT, $boolean ? 1 : 0);
	    }
	}

	/**
	 * 开启指定数据库的事务，在数据库创建链接时，事物会自动开启
	 * @param boolean $commit true，会提交之前开启的事务， false 继续使用之前的事务
	 * @return void
	 */
	public function begin_Transaction($commit=true){
		if ($commit && $this->in_transaction()){
			try{
				if(self::$conn[$this->db_name]){
					self::$conn[$this->db_name]->commit();
				}
			}catch(\Exception $e){}
		}
		try{
			if(self::$conn[$this->db_name])self::$conn[$this->db_name]->beginTransaction();
		}catch(\Exception $e){}
	}

	/**
	 * 提交所有链接数据库的事务
	 *
	 * <strong>如果数据库被设置成自动提交模式，此函数（方法）之后将恢复自动提交模式。如果要开启新事务，重新get_instance或者调用start_transaction</strong>
	 *
	 * @return void
	 */
	public static function commit_all(){
		foreach (self::$conn as $db_name => $conn){
			try{
				if($conn) {
					$conn->commit();
				}
			}catch(\Exception $e){}
		}
	}

	/**
	 * 提交事务，请求正常响应后，框架会提交事务
	 *
	 * <strong>如果数据库被设置成自动提交模式，此函数（方法）之后将恢复自动提交模式。如果要开启新事务，重新get_instance或者调用start_transaction</strong>
	 *
	 * @return void
	 */
	public function commit(){
		try{
			if(self::$conn[$this->db_name]){
				self::$conn[$this->db_name]->commit();
			}
		}catch(\Exception $e){}
	}

	/**
	 * 回滚所有链接数据库的事务
	 *
	 * 包括 MySQL 在内的一些数据库， 当在一个事务内有类似删除或创建数据表等 DLL 语句时，会自动导致一个隐式地提交。隐式地提交将无法回滚此事务范围内的任何更改。
	 *
	 * <strong>如果数据库被设置成自动提交模式，此函数（方法）之后将恢复自动提交模式。如果要开启新事务，重新get_instance或者调用start_transaction</strong>
	 *
	 * @return void
	 */
	public static function rollBack_all(){
		foreach (self::$conn as $db_name => $conn){
			try{
				if($conn) {
					$conn->rollBack();
				}
			}catch(\Exception $e){}
		}
	}
	/**
	 * 事务回滚，请求处理出现了任何未处理的异常，即进入了Controller的exception方法，框架会自动回滚
	 *
	 * 包括 MySQL 在内的一些数据库， 当在一个事务内有类似删除或创建数据表等 DLL 语句时，会自动导致一个隐式地提交。隐式地提交将无法回滚此事务范围内的任何更改。
	 *
	 * <strong>如果数据库被设置成自动提交模式，此函数（方法）之后将恢复自动提交模式。如果要开启新事务，重新get_instance或者调用start_transaction</strong>
	 *
	 * @return void
	 */
	public function rollBack(){
		try{
			if(self::$conn[$this->db_name]) self::$conn[$this->db_name]->rollBack();
		}catch(\Exception $e){
		}
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
		try{
			$stm = self::$conn[$this->db_name]->prepare($sql);
			$stm->execute($values);
			$row = $stm->fetch(PDO::FETCH_ASSOC);
			return @$row['f'];
		}catch (\PDOException $e){
			return $this->check_connect($e->getCode(), $e->errorInfo, 'lookup', $field, $table, $where, $values);
		}
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
		try{
			$stm = self::$conn[$this->db_name]->prepare($sql);
			$stm->execute($values);
			return $stm->fetch(PDO::FETCH_ASSOC)?:[];
		}catch (\PDOException $e){
			return $this->check_connect($e->getCode(), $e->errorInfo, 'lookup_record', $fields, $table, $where, $values);
		}
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
		try{
			$stm = self::$conn[$this->db_name]->prepare($sql);
			$stm->execute($values);
			return $stm->fetchAll(PDO::FETCH_ASSOC)?:[];
		}catch (\PDOException $e){
			return $this->check_connect($e->getCode(), $e->errorInfo, 'lookup_records', $fields, $table, $where, $values);
		}
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

		try {
			$stm = self::$conn[$this->db_name]->prepare($sql);
			return $stm->execute($values);
		}catch(\PDOException $e){
			return $this->check_connect($e->getCode(), $e->errorInfo, 'update', $table, $fields, $where, $values);
		}
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

		try{
			$stm = self::$conn[$this->db_name]->prepare($sql);
			return $stm->execute($values);
		}catch (\PDOException $e){
			return $this->check_connect($e->getCode(), $e->errorInfo, 'deletefrom', $table, $where, $values);
		}
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

        try{
			$stm = self::$conn[$this->db_name]->prepare($sql);
			$stm->execute($values);
		}catch (\PDOException $e){
			return $this->check_connect($e->getCode(), $e->errorInfo, "check_Insert", $table, $info, $checkSql, $checkInfo, $exist, $update, $key);
		}

        if ( !  $stm->rowCount()) {
            if( ! $update){
                throw new YZE_DBAException("not insert, check record exist");
            }
            $where = preg_replace("/^.+where/", "", $checkSql);
            $sql = "UPDATE `{$table}` SET {$set} WHERE {$where}";
            $stm = self::$conn[$this->db_name]->prepare($sql);
			$stm->execute($values);

            preg_match_all("/(?P<words>:[^\s]+)/", $where, $matchWheres);
            $lookupValues = [];
            foreach($matchWheres['words'] as $word){
                $lookupValues[$word] = $values[$word];
            }

            return $this->lookup($key, $table, $where, $lookupValues);
        }

        return self::$conn[$this->db_name]->lastInsertId();
    }
    /**
     * 插入记录; 返回新记录的主键；如果要做有条件的插入请使用check_insert方法。
	 * <br/>
	 * 如果插入的表有唯一字段，可通过$duplicate_key指定这些唯一字段，存在唯一字段冲突后，则会更新对应的记录
     *
     * @param string $table 要插入的表名
     * @param array $info array("field"=>"value"); 要插入的字段及其值
     * @param array $duplicate_key array("field0","field1"); 指定$info中的唯一字段(主键或唯一索引)，如果指定，
	 * 				则会生成 INSERT_ON_DUPLICATE_KEY_UPDATE 语句，再指定的字段有唯一健冲突时执行更新
	 * 				info中的字段数量如果少于$duplicate_key中的字段数量，会有sql语法错误
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

		try{
			$stm = self::$conn[$this->db_name]->prepare($sql);
			$stm->execute($values);
			return self::$conn[$this->db_name]->lastInsertId();
		}catch (\PDOException $e){
			return $this->check_connect($e->getCode(), $e->errorInfo, "insert", $table, $info, $duplicate_key, $keyname);
		}

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

		try{
			$stm = self::$conn[$this->db_name]->prepare($sql);
			$stm->execute($values);
			return self::$conn[$this->db_name]->lastInsertId();
		}catch (\PDOException $e){
			return $this->check_connect($e->getCode(), $e->errorInfo, "insert_Or_Ignore", $table, $info);
		}

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

		try{
			$stm = self::$conn[$this->db_name]->prepare($sql);
			$stm->execute($values);
			return self::$conn[$this->db_name]->lastInsertId();
		}catch (\PDOException $e){
			return $this->check_connect($e->getCode(), $e->errorInfo, "replace", $table, $info);
		}
    }

	/**
	 * 返回指定表的字段列表
	 * @param $table
	 * @return array
	 */
	public function table_fields($table) {
	    $sql="show columns from $table";

		try{
			$stm = self::$conn[$this->db_name]->query($sql);
		}catch (\PDOException $e){
			return $this->check_connect($e->getCode(), $e->errorInfo, "table_fields", $table);
		}

	    $fileds = array();
		while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
			$fileds[] = $row['Field'];
		}
	    return $fileds;
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
		return self::filter_var(@$this->result[$this->index][$table_alias ? "{$table_alias}_{$name}" : $name]);#数据库取出来编码
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
