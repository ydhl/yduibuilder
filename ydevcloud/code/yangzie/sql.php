<?php
namespace yangzie;
/**
 * 构建查询语句类。
 *
 * @author liizii
 * @since 20100620
 */
class YZE_SQL extends YZE_Object{
	const EQ 		= "=";
	const NE 		= "!=";
	const GT 		= ">";
	const LT 		= "<";
	const GEQ 		= ">=";
	const LEQ 		= "<=";
	const ISNULL	= "is null";
	const ISNOTNULL	= "is not null";
	const NOTIN 	= "not in";
	const IN 		= "in";
	const FIND_IN_SET   = "FIND_IN_SET";
	const BETWEEN 	= "between";
	const LIKE 		= "like";
	const BEFORE_LIKE 	= "like before";
	const END_LIKE 		= "like end";
	const DESC		= "desc";
	const ASC		= "asc";
	/**
	 * [["alias"	=> $table_alias,"field"	=> $column,"op"	=> $op,"value"	=> $value,"andor"=>"and"]]
	 * @var array
	 */
	private $where = array();
	private $and_where_group = array();
	private $or_where_group = array();
	/**
	 * like:array('alias1'=>array('select column'))
	 *
	 * @var array
	 */
	private $select = array();
	/**
	 * array('alias'=>$alias,'field'=>$field);
	 * @var array
	 */
	private $distinct = null;
	/**
	 * array('table name'=>array(array('alias'=>$alias,'field'=>$field)));
	 * @var array
	 */
	private $count = array();
	/**
	 * array('table name'=>array(array('alias'=>$alias,'field'=>$field,'distinct'=>boolean)));
	 * @var array
	 */
	private $sum = array();
	/**
	 * array('table name'=>array(array('alias'=>$alias,'field'=>$field)));
	 * @var array
	 */
	private $min = array();
	/**
	 * array('table name'=>array(array('alias'=>$alias,'field'=>$field)));
	 * @var array
	 */
	private $max = array();
	/**
	 * like:array('alias1'=>array('column name'=>'column value'))
	 *
	 * @var array
	 */
	private $update = array();
	/**
	 * like:array('alias1'=>array('column name'=>'column value'))
	 *
	 * @var array
	 */
	private $insert = array();
	/**
	 * like:array('alias'=>array('table'=>,'join'=>array()))
	 * @var array
	 */
	private $from = array();
	private $limit_start;
	private $limit_end;
	private $has_join = false;
	private $has_from = false;

	/**
	 * array('alias'		=> $table_alias,
	 * 'order_by'	=> $order_by,
	 * 'sort'		=> $sort)
	 * @var unknown_type
	 */
	private $order_by = array();
	/**
	 *
	 * array('alias'		=> $table_alias,
	 * 'group_by'	=> $order_by)
	 * @var unknown_type
	 */
	private $group_by = array();
	/**
	 * 当前构建的sql是做什么查询
	 * @var string
	 */
	private $action = "select";
	/**
	 * 构建的查询中涉及到的类
	 * @var unknown_type
	 */
	private $classes = array();
	/**
	 * 如果在INSERT插入行后会导致在一个UNIQUE索引或PRIMARY KEY中出现重复值，
	 * 则在出现重复值的行执行UPDATE；并用unique_key 配置的字段作为update的条件
	 * 如果不会导致唯一值列重复的问题，则插入新行. 用法：
	 *
	 *
	 * $unique_key = ["Key_name_A"=>A,"Key_name_B"=>B,"Key_name_C"=>C,"Key_name_D"=>array(D,E)];
	 *
	 * A,B,C三个是独立唯一的字段，D,E是联合起来唯一的字段
	 * @var array
	 */
	private $unique_key = array();
	/**
	 * INSERT_NOT_EXIST;INSERT_EXIST;INSERT_NOT_EXIST_OR_UPDATE时传入的检查某条记录的完整原生sql
	 * @var unknown
	 */
	private $check_sql  = "";
	private $insert_type = self::INSERT_NORMAL;
	/**
	 * 普通插入sql
	 * @var unknown
	 */
    const INSERT_NORMAL    = "insert_normal";
    /**
     * 指定的条件不存在时插入, <strong>注意,如果数据已经存在了，这种情况下对象没有主键返回</strong>
     * @var unknown
     */
    const INSERT_NOT_EXIST = "insert_not_exist";
    /**
     * 指定的条件不存在时插入； 存在则更新;这是传入的check sql必须同插入的sql是同一个表，yze会用check sql的where去更新插入的值
     * @var unknown
     */
    const INSERT_NOT_EXIST_OR_UPDATE = "insert_not_exist_or_update";
    /**
     * 指定的条件存在是插入
     * @var unknown
     */
    const INSERT_EXIST     = "insert_exist";
    /**
     * 有唯一键冲突时进行更新
     * @var unknown
     */
    const INSERT_ON_DUPLICATE_KEY_UPDATE = "insert_on_duplicate_key_update";
    /**
     * 有唯一健冲突时先删除原来的，再插入
     * @var unknown
     */
    const INSERT_ON_DUPLICATE_KEY_REPLACE = "insert_on_duplicate_key_replace";
    /**
     * 忽略唯一健冲突;数据将不写入数据库
     * @var unknown
     */
    const INSERT_ON_DUPLICATE_KEY_IGNORE  = "insert_on_duplicate_key_ignore";
	/**
	 * 构建and where条件段，e.g. where('item','part_no',YZE_SQL::LIKE,'%Good%');
	 * 该where与上一个where条件是and关系
	 *
	 * @param string $table_alias
	 * @param string $column
	 * @param string $op
	 * @param string|bool $value
	 * @param boolean $is_column 默认情况下value是变量值，某些情况下需要用字段作为值，这时通过传入is_column=true来指定value是一个字段名；
	 * @return YZE_SQL
	 */
	public function where($table_alias,$column,$op,$value=null, $is_column = false){
		$this->where[] = array(
			"alias"	=> $table_alias,
			"field"	=> $column,
			"op"	=> $op,
	        "is_column"	=> $is_column,
			"value"	=> $value,
			"andor"	=> "AND",
		);
		return $this;
	}

	/**
	 * 构建原生where条件，native_Where跟where一样根据调用的顺序构建最终的where语句；
	 * 但native_Where需要考虑如何和上一个where是and还是or，如native_Where("and ....")或native_Where("or ....")
	 * @param $where
	 * @return YZE_SQL
	 */
	public function native_Where($where){
			$this->where[] = array(
			"native"	=> $where
		);
		return $this;
	}
	/**
	 * 构建OR where条件段，e.g. or_where('item','part_no',YZE_SQL::LIKE,'%Good%')，与上一个条件是or关系
	 * @param string $table_alias
	 * @param string $column
	 * @param string $op
	 * @param string $value
	 * @param boolean $is_column 默认情况下value是变量值，某些情况下需要用字段作为值，这时通过传入is_column=true来指定value是一个字段名；
	 * @return YZE_SQL
	 */
	public function or_where($table_alias,$column,$op,$value=null,$is_column=false){
		$this->where[] = array(
			"alias"	=> $table_alias,
			"field"	=> $column,
			"op"	=> $op,
	        "is_column"	=> $is_column,
			"value"	=> $value,
			"andor"	=> "OR",
		);
		return $this;
	}
	/**
	 * eg.构建分组查询，如( ... AND ...)，分组由()包含
	 * e.g where_group([new YZE_Where('o','status',YZE_SQL::EQ,'completed'),new  YZE_Where('o','order_time',SQL::EQ,'2010-9-6')])
	 * @param array $where 里面的值是Where实例
	 * @return YZE_SQL
	 */
	public function where_group(array $where){
		$this->and_where_group[] = $where;
		return $this;
	}
	/**
	 * eg.构建分组查询，如( ... and ...) OR (...)，分组由()包含
	 * e.g or_where_group([new  YZE_Where('o','status',YZE_SQL::EQ,'completed','or'),new  YZE_Where('o','order_time',SQL::EQ,'2010-9-6','or')])
	 * @param array $where
	 * @return YZE_SQL
	 */
	public function or_where_group(array $where){
		$this->or_where_group[] = $where;
		return $this;
	}
	/**
	 * 查询字段，e.g. select('item',array('part_no','quote_id'))->select('o',array('order_time'))
	 * @param string $alias 查询的表别名
	 * @param array $select 查询的字段
	 * @return YZE_SQL
	 */
	public function select($alias,array $select=array("*")){
		$this->action = "select";
		if($alias!="*"){
			$this->select[$alias] = $select;
		}
		return $this;
	}
	/**
	 *
	 * 查询时distinct那个字段.e.g distinct('item','qo_item_id')
	 * @param string $alias 表别名
	 * @param string $field 查询时要distinct的字段名
	 * @return YZE_SQL
	 */
	public function distinct($alias,$field){
		$this->action = "select";
		$this->distinct = array('alias'=>$alias,'field'=>$field);
		return $this;
	}
	/**
	 * 查询count($alias.$field)，e.g. count('item','*')
	 *
	 * @param string $table_alias 查询的表别名
	 * @param string $field 要count的字段
	 * @param string $count_alias 要count的字段取值别名
	 * @param boolean $distinct 要count的字段
	 *
	 * @return YZE_SQL
	 */
	public function count($table_alias, $field, $count_alias, $distinct=false){
		$this->action = "select";
		$this->count[][$table_alias] = array('field' => $field,
		        'alias'=> $count_alias,
		        'distinct'=> $distinct);

		return $this;
	}
	/**
	 * 查询sum($alias.$field)，e.g. sum('item','qo_item_id')
	 * @param string $table_alias 查询的表别名
	 * @param string $field 要count的字段
	 * @param string $sum_alias 要sum的字段取值别名
	 * @return YZE_SQL
	 */
	public function sum($table_alias,$field,$sum_alias){
		$this->action = "select";
		$this->sum[][$table_alias] = array('field' => $field,'alias' => $sum_alias);
		return $this;
	}
	/**
	 * 查询max($alias.$field)，e.g. max('item','qo_item_id')
	 * @param string $table_alias 查询的表别名
	 * @param string $field 要max的字段
	 * @param string $max_alias 要max的字段取值别名
	 * @return YZE_SQL
	 */
	public function max($table_alias,$field,$max_alias){
		$this->action = "select";
		$this->max[][$table_alias] = array('field' => $field,'alias' => $max_alias);
		return $this;
	}
	/**
	 * 查询min($alias.$field)，e.g. min('item','qo_item_id')
	 * @param string $table_alias 查询的表别名
	 * @param string $field 要min的字段
	 * @param string $min_alias 要min的字段取值别名
	 * @return YZE_SQL
	 */
	public function min($table_alias,$field,$min_alias){
		$this->action = "select";
		$this->min[][$table_alias] = array('field' => $field,'alias' => $min_alias);
		return $this;
	}
	/**
	 * 构建删除sql,e.g. delete()
	 * @return YZE_SQL
	 */
	public function delete(){
		$this->action = "delete";
		return $this;
	}
	/**
	 * 构建更新sql,e.g. update('item',array('part_no'=>'value','quote_id'=>'34343'));
	 * @param string $alias 更新的表别名
	 * @param array $datas 要更新的字段（键）与值
	 * @return YZE_SQL
	 */
	public function update($alias,array $datas){
		$this->action = "update";
		$this->update[$alias] = $datas;
		return $this;
	}
	/**
	 * 构建插入sql,e.g. insert('item',array('part_no'=>'value','quote_id'=>'34343'));
	 *
	 * @param string $alias 插入的表别名
	 * @param array $datas 要插入的字段（键）与值
	 * @param string $insert_type <ol>
	 * <li>INSERT_NORMAL：普通插入语句, 默认情况</li>
	 * <li>INSERT_NOT_EXIST： 指定的$checkSql条件查询不出数据时才插入，如果插入、更新成功，会返回主键值，如果插入失败会返回0，这时的entity->get_key()返回0</li>
	 * <li>INSERT_NOT_EXIST_OR_UPDATE： 指定的$checkSql条件查询不出数据时才插入, 查询出数据则更新这条数据；如果插入、更新成功，会返回主键值，如果插入失败会返回0，这时的entity->get_key()返回0 </li>
	 * <li>INSERT_EXIST： 指定的$checkSql条件查询出数据时才插入，如果插入、更新成功，会返回主键值，如果插入失败会返回0，这时的entity->get_key()返回0</li>
	 * <li>INSERT_ON_DUPLICATE_KEY_UPDATE： 有唯一健冲突时更新其它字段</li>
	 * <li>INSERT_ON_DUPLICATE_KEY_REPLACE： 有唯一健冲突时先删除原来的，然后在插入</li>
	 * <li>INSERT_ON_DUPLICATE_KEY_IGNORE： 有唯一健冲突时忽略，不抛异常</li>
	 * </ol>
	 * @param unkonw $extra_info $insert_type==self::INSERT_ON_DUPLICATE_KEY_UPDATE传入唯一键字段数组
	 * $insert_type==self::INSERT_EXIST,INSERT_NOT_EXIST_OR_UPDATE,INSERT_NOT_EXIST时传入完整的sql；不传入sql则使用自己的where条件
	 * 其它insert_type设置无意义
	 * @return YZE_SQL
	 */
	public function insert($alias,array $datas, $insert_type=self::INSERT_NORMAL, $extra_info=null){
		$this->action = "insert";
		$this->insert[$alias] = $datas;
		$this->insert_type = $insert_type;
		if($insert_type==self::INSERT_ON_DUPLICATE_KEY_UPDATE){
		  $this->unique_key = (array)$extra_info;
		}else if($insert_type==self::INSERT_EXIST || $insert_type==self::INSERT_NOT_EXIST || $insert_type==self::INSERT_NOT_EXIST_OR_UPDATE){
		    $this->check_sql = $extra_info;
		}
		return $this;
	}
	/**
	 * 构建查询的from段,e.g. from('LineItem','item')->from('Order','o')
	 * @param string $class_name 对象名
	 * @param string $alias 别名
	 * @return YZE_SQL
	 */
	public function from($class_name,$alias=null){
		$entity = new $class_name();
		$this->from[($alias ?: 'm')] = array(
			'table'=>$entity->get_table(),
			'join'=>array()
		);
		$this->has_from = true;
		$this->classes[($alias ?: 'm')] = $class_name;
		return $this;
	}
	/**
	 * left_join查询段,e.g. left_join('item','item.order_id = o.order_id')
	 * @param string $class_name 对象名
	 * @param string $alias 别名
	 * @param string $join_on
	 * @return YZE_SQL
	 */
	public function left_join($class_name,$alias,$join_on){
		$entity = new $class_name();
		$this->from[($alias ?: $class_name)]= array(
			'table' => $entity->get_table(),
			'join' =>array(
				'type'=>'left',
				'on'=>$join_on
			)
		);

		$this->classes[($alias ?: $class_name)] = $class_name;
		$this->has_join = true;
		return $this;
	}
	/**
	 * right_join查询段,e.g. right_join('item','item.order_id = o.order_id')
	 * @param string $class_name 对象名
	 * @param string $alias 别名
	 * @param string $join_on
	 * @return YZE_SQL
	 */
	public function right_join($class_name,$alias,$join_on){
		$entity = new $class_name();
		$this->from[($alias ?: $class_name)]= array(
			'table' => $entity->get_table(),
			'join' =>array(
				'type'=>'right',
				'on'=>$join_on
			)
		);
		$this->classes[($alias ?: $class_name)] = $class_name;
		$this->has_join = true;
		return $this;
	}
	/**
	 * inner join查询段,e.g. left_join('item','item.order_id = o.order_id')
	 * @param string $class_name 对象名
	 * @param string $alias 别名
	 * @param string $join_on
	 * @return YZE_SQL
	 */
	public function join($class_name,$alias,$join_on){
		$entity = new $class_name();
		$this->from[($alias ?: $class_name)]= array(
			'table' => $entity->get_table(),
			'join'=>array(
				'type'=>'inner',
				'on'=>$join_on
			)
		);
		$this->classes[($alias ?: $class_name)] = $class_name;
		$this->has_join = true;
		return $this;
	}
	/**
	 * 返回给定的表在构建的查询中的别名,如果没有找到返回false
	 *
     * <strong>如果同一个table在sql中出现了两次,得到的alias则是第一个</strong>
     *
	 * @param string $table_name
	 * @return string
	 */
	public function get_alias($table_name){
		foreach($this->from as $alias => $from_table){
			if($from_table['table']==$table_name){
				return $alias;
			}
		}
		return false;
	}
	/**
	 * sql 的limit限制，e.g. limit(0,10),limit(20)
	 * @param int $start
	 * @param int $end
	 * @return YZE_SQL
	 */
	public function limit($start,$end=null){
		$this->limit_start = $start;
		$this->limit_end = $end;
		return $this;
	}

	/**
	 * 构建查询的order_by。e.g. order_by('item','order_id','desc')
	 *
	 * @param string $table_alias
	 * @param string $order_by
	 * @param string $sort
	 * @param bool $use_alias true 拼的sql是order by {$table_alias}_$order_by. false拼的sql是order by {$table_alias}.{$order_by}
	 *
	 * @return YZE_SQL
	 */
	public function order_by($table_alias,$order_by,$sort, $use_alias=false){
		$this->order_by[] = array(
			'alias'		=> $table_alias,
			'order_by'	=> $order_by,
			'sort'		=> $sort,
            'use_alias' => $use_alias
		);
		return $this;
	}
	/**
	 * 构建查询的group_by。e.g. group_by('item','order_id')
	 * @param string $table_alias
	 * @param string $group_by
	 * @param bool $use_alias true 拼的sql是group by {$table_alias}_$group_by. false拼的sql是group by {$table_alias}.{$group_by}
	 * @return YZE_SQL
	 */
	public function group_by($table_alias, $group_by, $use_alias=false){
		$this->group_by[] = array(
			'alias'		=> $table_alias,
			'group_by'	=> $group_by,
            'use_alias' => $use_alias
		);
		return $this;
	}

	/**
	 * 用方法分组
	 * @param $group_by
	 * @return $this
	 */
	public function group_by_function($group_by){
	    $this->group_by[] = array(
	            'group_by'	=> $group_by,
	            'function'  => true
	    );
	    return $this;
	}

	/**
	 *
	 *
	 * @author leeboo
	 * @return YZE_SQL
	 */
	public static function new_SQL(){
		return new YZE_SQL();
	}

	/**
     * 清空where条件
     * <pre>
     * 1) 如果指定了alias和column则只清空指定的字段条件
     * 2) 如果指定了alias和没有指定column则清空指定表的所有字段条件
     * 3) 如果没有指定任何参数,则删除所有的where条件
     * </pre>
	 *
     * @param string $alias 要删除的表别名
     * @param string $column 要删除的字段
     * @return YZE_SQL
     */
    public function clean_where($alias = null, $column = null) {
        if (!$alias && !$column) {
            $this->where = array();
            $this->and_where_group = array();
            $this->or_where_group = array();
        }
        if ($alias && ! $column) {//remove all table where
            $willDeleteItem = [];
            foreach ($this->where as $index => $item) {
                if ($item['alias'] == $alias)
                    $willDeleteItem[] = $index;
            }

            foreach ($this->and_where_group as $index => $item) {
                if ($item->get_Alias() == $alias)
                    $willDeleteItem[] = $index;
            }

            foreach ($this->or_where_group as $index => $item) {
                if ($item->get_Alias() == $alias)
                    $willDeleteItem[] = $index;
            }

            foreach ($willDeleteItem as $index) {
                unset($this->where[$index]);
                unset($this->and_where_group[$index]);
                unset($this->or_where_group[$index]);
            }

        }
        if ($alias && $column) {//remove specify column
            $willDeleteItem = [];
            foreach ($this->where as $index => $item) {
                if ($item['alias'] == $alias && $item['field']==$column)
                    $willDeleteItem[] = $index;
            }

            foreach ($this->and_where_group as $index => $item) {
                if ($item->get_Alias() == $alias && $item->get_Field()==$column)
                    $willDeleteItem[] = $index;
            }

            foreach ($this->or_where_group as $index => $item) {
                if ($item->get_Alias() == $alias && $item->get_Field()==$column)
                    $willDeleteItem[] = $index;
            }

            foreach ($willDeleteItem as $index) {
                unset($this->where[$index]);
                unset($this->and_where_group[$index]);
                unset($this->or_where_group[$index]);
            }
        }
        return $this;
    }

	/**
	 * 清除之前构造的查询
	 * @return YZE_SQL
	 */
	public function clean(){
		$this->where	= array();

		$this->from 	= array();
		$this->limit_end= null;
		$this->limit_start= null;
		$this->order_by 	= array();

		$this->and_where_group = array();
		$this->or_where_group = array();
		$this->update = array();
		$this->insert = array();
		$this->group_by = array();
		$this->has_join = false;
		$this->has_from = false;

		$this->entities = array();
		$this->classes = array();
		$this->clean_select();

		return $this;
	}

	/**
	 * @return YZE_SQL
	 */
	public function clean_groupby(){
		$this->group_by = array();
		return $this;
	}

	/**
	 * @return YZE_SQL
	 */
	public function clean_limit(){
		$this->limit_end= null;
		$this->limit_start= null;
		return $this;
	}

	/**
	 * @return YZE_SQL
	 */
	public function clean_select(){
		$this->select     = array();
		$this->distinct = null;
		$this->action = "select";
        $this->count = array();
        $this->max = array();
        $this->min = array();
        $this->sum = array();
        $this->limit_start = 0;
        $this->limit_end = 0;
        return $this;
	}
	/**
	 * 返回要查询的对象类名(包含join的对象)，键为查询的别名
	 * @param boolean $just_select 如果为true，则只返回from中的类，false返回所有涉及到的类（如join的表）
	 * @return array
	 */
	public function get_select_classes($just_select=false){
		if(empty($this->select) || !$just_select){
			return $this->classes;//所有的类
		}
		foreach($this->select as $alias=>$other){
			$classes[$alias] = $this->classes[$alias];
		}
		return $classes;
	}
	public function __toString(){
		switch($this->action){
			case "select":return $this->_select();
			case "delete":return $this->_delete();
			case "update":return $this->_update();
			case "insert":return $this->_insert();
			default:return '';
		}
	}
	/**
	 * 是否有join连接
	 * @return bool
	 */
	public function has_join()
	{
		return $this->has_join;
	}
	/**
	 * 是否有from
	 * @return bool
	 */
	public function has_from(){
		return $this->has_from;
	}

	/**
	 * 返回sql中的所有表名.e.g array(alias=>table)
	 * @return array
	 */
	public function get_select_table(){
		foreach($this->from as $alias => $from_table){
			$from[$alias] = $from_table['table'];
		}
		return $from;
	}


	private function _where_group($groupWhere, $group_and_or="and"){
		#分组查询
		$where='';
		foreach((array)$groupWhere as $w){
			if(is_array($w)){
				$where .= " ".$group_and_or." (".$this->_where_group($w).") ";
			}else{
				if(@$not_first){#第一个where前不需要and or
					$where .= " ".$w->get_AndOr()." ".$this->_buildWhere($w->get_where_array());
				}else{
					$where .= " ".$this->_buildWhere($w->get_where_array());
					$not_first = true;
				}
			}
		}
		return @$where;
	}


	private function _where(){
		$where = "";
		foreach((array)$this->where as $wheres){
			if( @$wheres['native']){
				$where .= $wheres['native'];
				continue;
			}
			if(@$not_first){#第一个where前不需要and or
				$where .= " ".$wheres['andor']." ".$this->_buildWhere($wheres);
			}else{
				@$where .= $this->_buildWhere($wheres);
				$not_first = true;
			}
		}
		foreach (($this->and_where_group) as $and_where) {
			$groupWhere = $this->_where_group($and_where, "and");
	        $where = ($where ? $where." AND " : "")."(".$groupWhere.")";
		}
		foreach ($this->or_where_group as $or_where) {
			$orGroupWhere = $this->_where_group($or_where, "or");
			$where = ($where ? $where." OR " : "")."(".$orGroupWhere.")";
		}
		return @$where;
	}

	private function _from(){
		$no_alias = $this->isinsert() || $this->isdelete();//不要别名
		$from = array();
		foreach($this->from as $alias => $from_table){
			array_walk($from_table,function(&$item, $key){
				if($key == "table"){
					$item = "`{$item}`";
				}
			});
			if($from_table['join']){
				switch(strtoupper($from_table['join']['type'])){
					case 'LEFT':
						$from[] = "LEFT JOIN ".(
							$no_alias ?
							$from_table['table'] :
							$from_table['table']." AS ".$alias)
							." ON ".$from_table['join']['on'];
						break;
					case 'RIGHT':
						$from[] = "RIGHT JOIN ".(
							$no_alias ?
							$from_table['table'] :
							$from_table['table']." AS ".$alias)
							." ON ".$from_table['join']['on'];
						break;
					default:
					case 'INNER':
						$from[] = "INNER JOIN ".(
							$no_alias ?
							$from_table['table'] :
							$from_table['table']." AS ".$alias)
							." ON ".$from_table['join']['on'];
						break;
				}
			}else{
				//先处理from，在按顺序处理其他join
				array_unshift($from, $no_alias ? $from_table['table'] : $from_table['table']." AS ".$alias);
			}
		}

		return join(" ",$from);
	}

	private function _select(){
		$select = [];
		#处理distinct查询字段
		if($this->distinct){
			$alias = $this->distinct['alias'];
			$column = $this->distinct['field'];
			$select[] = "distinct {$alias}.{$column} AS {$alias}_{$column}";
		}

		if($this->select){#指定了要查询什么
			foreach($this->select as $alias => $columns){
				foreach((array)$columns as $column){
					if($column=="*"){
						//查询指定表的所有
					    $cls = $this->classes[$alias];
						$entities = new $cls;
						foreach($entities->get_columns() as $column => $define){
							$select[] = "{$alias}.{$column} AS {$alias}_{$column}";
						}
						unset($entities);
					}else{
						$select[] = "{$alias}.{$column} AS {$alias}_{$column}";
					}
				}
			}
		}else if(
			!$this->count &&
			!$this->max &&
			!$this->min &&
			!$this->sum &&
			!$this->distinct
		){#没有指定查询，并且没有聚合函数则查询所有
			foreach($this->classes as $alias => $cls){
				$entities = new $cls;
				foreach($entities->get_columns() as $column => $define){
					$select[] = "{$alias}.{$column} AS {$alias}_{$column}";
				}
				unset($entities);
			}
		}
		#处理count，sum，max，min这些函数查询
		foreach($this->count as $counts){
			foreach($counts as $alias => $count){
				$select[] = $count['field']=="*"
					? "count(".($count['distinct'] ? "distinct" : "")." *) AS {$alias}_".$count['alias']
					: "count(".($count['distinct'] ? "distinct" : "")." {$alias}.".$count['field'].") AS {$alias}_".$count['alias'];
			}
		}
		foreach($this->max as $maxs){
			foreach($maxs as $alias => $max){
				$select[] = "max({$alias}.".$max['field'].") AS {$alias}_".$max['alias'];
			}
		}
		foreach($this->min as $mins){
			foreach($mins as $alias => $min){
				$select[] = "min({$alias}.".$min['field'].") AS {$alias}_".$min['alias'];
			}
		}
		foreach ($this->sum as $sums){
			foreach($sums as $alias => $sum){
				$select[] = "sum({$alias}.".$sum['field'].") AS {$alias}_".$sum['alias'];
			}
		}

		$where = $this->_where();
		return "SELECT "
				.($select ? join(",",$select) : "*")." FROM ".$this->_from()
				.($where  ? " WHERE ".$where : "")
				.$this->_group_by()
				.$this->_order_by()
				.$this->_limit();
	}
	private function _delete(){
		$where = $this->_where();
		return "DELETE FROM ".$this->_from()
				.($where  ? " \r\nWHERE ".$where : "");
	}
	private function _insert(){
	    $update = array();
		$insert_column = [];
		$insert_value = [];
		foreach($this->insert as $alias => $insertDatas){
			foreach((array)$insertDatas as $field => $value){
			    $val = $this->_quoteValue($value);
			    if(($this->insert_type==YZE_SQL::INSERT_ON_DUPLICATE_KEY_UPDATE && ! in_array($field, $this->unique_key))){
			        $update[] = "`{$field}`=VALUES(`{$field}`)";
			    }else if($this->insert_type==YZE_SQL::INSERT_ON_DUPLICATE_KEY_REPLACE){
			        $update[] = "`{$field}`={$val}";
			    }
				$insert_column[] = "`{$field}`";
				$insert_value[]  = $val;
			}
		}
		$class = $this->get_select_classes(true);
		$class = $class[$alias];
		$obj = new $class();

		switch ($this->insert_type){
		    case self::INSERT_EXIST:
		        $where = $this->check_sql ? $this->check_sql->__toString() : "SELECT ".$class::KEY_NAME." FROM ".$this->_from()." WHERE ".$this->_where();
		        return "INSERT INTO ".$this->_from()." (".join(",",$insert_column).") SELECT ".join(",",$insert_value)." FROM dual WHERE EXISTS ({$where})";

	        case self::INSERT_NOT_EXIST:
            case self::INSERT_NOT_EXIST_OR_UPDATE:
                $where = $this->check_sql ? $this->check_sql->__toString() : "SELECT ".$class::KEY_NAME." FROM ".$this->_from()." WHERE ".$this->_where();
	            return "INSERT INTO ".$this->_from()." (".join(",",$insert_column).") SELECT ".join(",",$insert_value)." FROM dual WHERE NOT EXISTS ({$where})";

		    case self::INSERT_ON_DUPLICATE_KEY_IGNORE:
		        return  "INSERT IGNORE INTO ".$this->_from()." (".join(",",$insert_column).") VALUES(".join(",",$insert_value).")";

		    case self::INSERT_ON_DUPLICATE_KEY_UPDATE:

		        return  "INSERT INTO ".$this->_from()
		        ." (".join(",",$insert_column).") VALUES("
		                .join(",",$insert_value).")  ON DUPLICATE KEY UPDATE ".$class::KEY_NAME." = LAST_INSERT_ID(".$class::KEY_NAME."), ".join(",", $update);

		    case self::INSERT_ON_DUPLICATE_KEY_REPLACE:
		        return  "REPLACE INTO ".$this->_from()." SET ".join(",", $update);

	        case self::INSERT_NORMAL:
	        default :
		        return  "INSERT INTO ".$this->_from()." (".join(",",$insert_column).") VALUES(".join(",",$insert_value).")";
		}

	}
	private function _update(){
		foreach($this->update as $alias => $updateDatas){
			foreach((array)$updateDatas as $field => $value){
				$update[] = $alias.".".$field."=".$this->_quoteValue($value);
			}
		}
		$where = $this->_where();
		return "UPDATE ".$this->_from()." \r\nSET "
				.join(",",$update)
				.($where  ? " \r\nWHERE ".$where : "");
	}

	private function _group_by(){
		foreach ($this->group_by as $group_by){
		    if(@$group_by['function']){
		        $by[] = $group_by['group_by'];
		    }else{
			    $by[] = $group_by['use_alias'] ? $group_by['alias']."_".$group_by['group_by'] : $group_by['alias'].".".$group_by['group_by'];
		    }
		}
		return @$by ? " GROUP BY ".join(',',$by) : "";
	}
	private function _order_by(){
		foreach ($this->order_by as $order_by){
			$by[] = ($order_by['use_alias'] ? $order_by['alias']."_".$order_by['order_by'] : $order_by['alias'].".".$order_by['order_by'])." ".strtoupper($order_by['sort']);
		}
		return @$by ? " ORDER BY ".join(',',$by) : "";
	}
	private function _limit(){
		if($this->limit_start && $this->limit_end){
			return " LIMIT ".(int)$this->limit_start." , ".(int)$this->limit_end;
		}elseif($this->limit_start){
			return " LIMIT ".(int)$this->limit_start;
		}if($this->limit_end){
			return " LIMIT 0 , ".(int)$this->limit_end;
		}else{
			return "";
		}
	}

	public function isinsert(){
		return strcasecmp($this->action,"insert")==0;
	}
	public function isdelete(){
		return strcasecmp($this->action,"delete")==0;
	}
	/**
	 * 构建格式正确的sql,转义。
	 * @param unknown_type $value
	 */
	private function _quoteValue($value){
	    ///mysql_escape_string 把\转换成\\
	    //处理null
	    $_ = function ($v, $defilter_var) {
	        if(is_null($v)){
	            return "null";
	        }
	        if (is_string($v)){
	            return YZE_DBAImpl::get_instance()->quote($defilter_var);
	        }

	        if (is_numeric($v)){
	            return $v;
	        }

	        return  YZE_DBAImpl::get_instance()->quote($defilter_var) ;#数据库中的操作要特殊字符解码
	    };

	    if(is_array($value)){
	        foreach($value as $index => $v){
	            $return[$index] = $_($v, self::defilter_var($v));
	        }
	        return @$return;
	    }else{
	        return $_($value, self::defilter_var($value));
	    }
	}

	private function _buildWhere($wheres){
	    if($this->isinsert() || $this->isdelete()){
	        $column = "`".$wheres['field']."`";
	    }else{
	        $column = $wheres['alias'].".".$wheres['field'];
	        if(@$wheres['field_func']){
	            $column = $wheres['field_func']."( ".$column." )";
	        }
	    }

	    #处理where中有子查询的情况
	    if(is_a($wheres['value'],'\yangzie\YZE_SQL')){
	        switch($wheres['op']){
	            case self::NOTIN:
	                $cond = " NOT IN (".$wheres['value']->__toString().")";break;
	            case self::IN:
	                $cond = " IN (".$wheres['value']->__toString().")";break;
	            case self::EQ:
	                $cond = " = (".$wheres['value']->__toString().")";break;
	            case self::BETWEEN:
	            default:				$cond = " IS NOT NULL";break;
	        }
	        return $column.$cond;
	    }
	    $quoted_value = $wheres['is_column'] ? "`".$wheres['value']."`" : $this->_quoteValue($wheres['value']);
	    switch($wheres['op']){
	        case self::LIKE:		$cond = " LIKE ".YZE_DBAImpl::get_instance()->quote("%".self::defilter_var($wheres['value'])."%");break;
	        case self::BEFORE_LIKE:	$cond = " LIKE ".YZE_DBAImpl::get_instance()->quote("%".self::defilter_var($wheres['value']));break;
	        case self::END_LIKE:	$cond = " LIKE ".YZE_DBAImpl::get_instance()->quote(self::defilter_var($wheres['value'])."%");break;
	        case self::FIND_IN_SET: return $cond = " FIND_IN_SET (".$quoted_value.", $column)";
	        case self::EQ:			$cond = " = ".$quoted_value;break;
	        case self::NOTIN:		$cond = " NOT IN (".($quoted_value ? join(",",(array)$quoted_value) : 'NULL').")";break;
	        case self::IN:			$cond = " IN (".($quoted_value ? join(",",(array)$quoted_value) : 'NULL').")";break;
	        case self::BETWEEN:		$cond = " BETWEEN ".array_shift($quoted_value)." AND ".array_shift($quoted_value);break;
	        case self::NE:			$cond = " != ".$quoted_value;break;
	        case self::GT:			$cond = " > ".$quoted_value;break;
	        case self::LT:			$cond = " < ".$quoted_value;break;
	        case self::GEQ:			$cond = " >= ".$quoted_value;break;
	        case self::LEQ:			$cond = " <= ".$quoted_value;break;
	        case self::ISNULL:		$cond = " IS NULL";break;
	        case self::ISNOTNULL:
	        default:				$cond = " IS NOT NULL";break;
	    }
	    return $column.$cond;
	}
}
/**
 * YZE_Where构建对象，用户构建复杂的where组合
 * @author liizii
 *
 */
class YZE_Where extends YZE_Object{
	private $alias;
	private $field;
	private $op;
	private $value;
	private $andor;
	private $field_func;
	private $value_func;

	/**
	 *
	 * @param unknown_type $alias 表别名
	 * @param unknown_type $field 表字段
	 * @param unknown_type $op 操作符
	 * @param unknown_type $value 值
	 * @param unknown_type $andor 该Where条件与前面的where如何拼接，是and还是or
	 * @param string $value_func 运用在值上的函数
	 * @param string $field_func 运用在字段的函数
	 */
	public function __construct($alias,$field,$op,$value,$andor="AND", $field_func="", $value_func=""){
		$this->alias 	= $alias;
		$this->field 	= $field;
		$this->op 		= $op;
		$this->value 	= $value;
		$this->andor 	= $andor;
		$this->field_func 	= $field_func;
		$this->value_func 	= $value_func;
	}
	public function get_where_array(){
		return array(
			"alias"	=> $this->alias,
			"field"	=> $this->field,
			"op"	=> $this->op,
			"value"	=> $this->value,
			"andor"	=> $this->andor,
			"field_func" => $this->field_func,
			"value_func" => $this->value_func,
		);
	}
	public function get_field_func(){
		return $this->field_func;
	}
	public function get_value_func(){
		return $this->value_func;
	}
	public function get_alias(){
		return $this->alias;
	}
	public function get_Field(){
		return $this->field;
	}
	public function get_Op(){
		return $this->op;
	}
	public function get_Value(){
		return $this->value;
	}
	public function get_AndOr(){
		return $this->andor;
	}
}
?>
