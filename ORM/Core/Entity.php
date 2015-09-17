<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 数据实体层基类(ORM核心)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.0
 +------------------------------------------------------------------------------
 */
abstract class Entity extends Object implements IEntity
{
	/**
	 * 数据源组件标识id(全局唯一)
	 */
	public $dsId	    = 'mysql';

	/**
	 * 数据库对象
	 */
	public $db          = null;

	/**
	 * 数据表标识(全局唯一)
	 */
	public $tableKey    = null;

	/**
	 * 数据表主键
	 */
	public $pk          = 'id';

	/**
	 * 调试开关
	 */
	public $debug       = false;

	/**
	 * 表行记录
	 */
	private $row        = array();

	/**
	 * 表数据是否发生改变(只针对更新、删除)
	 */
	private $change     = false;


	/**
	 * 创建数据库对象
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function createDbObject()
	{
		if ( $this->db )
		{
			return '';
		}

		$this->db = $this->com($this->dsId);
		if ( $this->debug === true )
		{
			$log = $this->com('dbLog');
			if ( get_class($log) != 'stdClass' )
			{
				$this->db->dbLog = $log;
			}
		}
	}

	/**
	 * 设置表切片(分表标识)
	 *
	 * @access	public
	 * @param	int		$slice	切片(分表表识)
	 */
	public function slice($slice)
	{
		$this->slice = $slice;
	}

	/**
	 * 得到结构信息
	 *
	 * @access	public
	 * @return	array
	 */
	public function struct()
	{
		$this->createDbObject();

		return $this->db->struct($this->tableKey);
	}

	/**
	 * 得到指定ID的数据(主键查询)
	 *
	 * @access	public
	 * @param	int		$id		主键id
	 * @param	string	$col	字段名称[只支持一个字段]
	 * @return	mixed(array|string|int|float)
	 */
	public function findOne($id = 0, $col = '')
	{
		if ( empty($id) )
		{
			return array();
		}

		if ( isset($this->row[$id]) && !$this->change )
		{
			if ( $col )
			{
				return isset($this->row[$id][$col]) ? $this->row[$id][$col] : '';
			}
			else
			{
				return $this->row[$id];
			}
		}

		$rule['eq']    = array( $this->pk => $id );
		$rule['slice'] = $this->slice;
		$this->createDbObject();
		$data = $this->db->findOne($this->tableKey, $rule);
		
		if ( $col )
		{
			return isset($data[$col]) ? $data[$col] : '';
		}
		
		return $data;
	}

	/**
	 * 获取多条数据
	 *
	 * @access	public
	 * @param	array	$rule  数据查询规则
	 * @return	array
	 */
	public function find($rule)
	{
		!isset($rule['limit']) && $rule['limit'] = 1;
		$this->createDbObject();
		$rule['slice'] = $this->slice;
		$method        = $rule['limit'] == 1 ? 'findOne' : 'find';
		
		return $this->db->$method($this->tableKey, $rule);
	}

	/**
	 * 获取多条数据(数据分页时用)
	 *
	 * @access	public
	 * @param	array	$rule	数据查询规则
	 * @return	array
	 */
	public function findAll($rule)
	{
		$page          = isset($rule['page']) ? intval($rule['page']) : 1;
		$rule['page']  = $page >= 1 ? $page : 1;
		$this->createDbObject();
		$rule['slice'] = $this->slice;
		
		return $this->db->findAll($this->tableKey, $rule);
	}

	/**
	 * 按条件统计数据
	 *
	 * @access	public
	 * @param	array	$rule	数据查询规则
	 * @return	int
	 */
	public function count($rule)
	{
		$this->createDbObject();
		$rule['slice'] = $this->slice;

		return $this->db->count($this->tableKey, $rule);
	}

	/**
	 * 创建一条数据
	 *
	 * @access	public
	 * @param	array	$data	数据信息[键值对]
	 * @return	int				0失败、大于0成功
	 */
	public function create($data)
	{
		if ( !is_array($data) || empty($data) )
		{
			return 0;
		}

		$this->createDbObject();
		$rule['slice'] = $this->slice;
		
		return $this->db->create($this->tableKey, $data, $rule);
	}
	
	/**
	 * 修改数据
	 *
	 * @access	public
	 * @param	array	$data	被修改的数据[键值对]
	 * @param	array	$rule	数据修改规则
	 * @return	bool
	 */
	public function modify($data, $rule)
	{
		if ( !is_array($data) || empty($data) || !is_array($rule) || empty($rule) ) 
		{
			return false;
		}

		$this->change  = true;
		$this->createDbObject();
		$rule['slice'] = $this->slice;
		
		return $this->db->modify($this->tableKey, $data, $rule);
	}

	/**
	 * 删除数据 
	 *
	 * @access	public
	 * @param	array	$rule	数据删除规则
	 * @return	bool
	 */
	public function remove($rule)
	{
		if ( !is_array($rule) || empty($rule) )
		{
			return false;
		}

		$this->change  = true;
		$this->createDbObject();
		$rule['slice'] = $this->slice;
		
		return $this->db->remove($this->tableKey, $rule);
	}

	/**
	 * 开始事务
	 *
	 * @access	public
	 * @return	bool
	 */
	public function begin()
	{
		$this->createDbObject();
		
		return $this->db->begin($this->tableKey);
	}

	/**
	 * 提交事务
	 *
	 * @access	public
	 * @return	bool
	 */
	public function commit()
	{
		$this->createDbObject();
		
		return $this->db->commit();
	}

	/**
	 * 回滚事务
	 *
	 * @access	public
	 * @return	bool
	 */
	public function rollBack()
	{
		$this->createDbObject();
		
		return $this->db->rollBack();
	}

	/**
	 * 释放资源
	 *
	 * @access	public
	 * @return	void
	 */
	public function __destruct()
	{
		$this->dsId     = null;
		$this->db       = null;
		$this->tableKey = null;
		$this->pk       = null;
		$this->debug    = null;
		$this->row      = null;
		$this->change   = null;
	}
}
?>