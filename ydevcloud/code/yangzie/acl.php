<?php
namespace yangzie;

use app\yze_get_ignore_acos;
class YZE_ACL extends YZE_Object{
	private $acos_aros;
	private $permission_cache = array();
	private static $instance;

	private function __construct(){
		$this->acos_aros = \app\yze_get_acos_aros();
		krsort($this->acos_aros);
		$newarr = array();
		foreach ($this->acos_aros as $aco=>$aros){
		    if(is_array($aros['deny'])) {
		    	krsort($aros['deny']);
		    }
		    if(is_array($aros['allow'])){
		    	krsort($aros['allow']);
		    }
		    $newarr[$aco] = $aros;
		}
		$this->acos_aros = $newarr;
	}
	private function check_user_permission($aconame){
		$perm = get_user_permissions();

		if(!$perm)return -1;
		if (is_array(@$perm["deny"])){//配置了拒绝项
			$denys = $this->in_array($aconame, $perm["deny"]);//拒绝当前ACO
			if ($denys){//拒绝当前ACO的所有action
				return false;
			}
		}

		if (is_array(@$perm["allow"])){//允许当前ACO
			$allow = $this->in_array($aconame, $perm["allow"]);//允许当前ACO

			if ($allow){//允许当前ACO的所有action
				return true;
			}
		}

		if (@$perm["deny"]=="*")return false;//拒绝优先
		if (@$perm["allow"]=="*")return true;//允许所有
		return -1;
	}
	private function check_role_permission($aroname, $aconame){
		if (!trim($aroname)) {
			return false;
		}

		if(function_exists("get_permissions")){
			$perm = get_permissions($aroname);

			if (is_array(@$perm["deny"])){//配置了拒绝项
				$denys = $this->in_array($aconame, $perm["deny"]);//拒绝当前ARO
				if ($denys){//拒绝当前ACO的所有action
					return false;
				}
			}

			if (is_array(@$perm["allow"])){//允许当前ACO
				$allow = $this->in_array($aconame, $perm["allow"]);//允许当前ARO
				if ($allow){//允许当前ACO的所有action
					return true;
				}
			}

			if(@$perm["deny"]=="*")return false;//拒绝优先

			if (@$perm["allow"]=="*")return true;//允许所有

			if($aconame=="/") return false;//都没找到

			$aconames = explode("/", $aconame);
			array_pop($aconames);
			$aconame= count($aconames)==1 ? "/" : join("/", $aconames);
			return $this->check_role_permission($aroname, $aconame);
		}


		$perm = @$this->acos_aros[$aconame];


		if (is_array(@$perm["deny"])){//配置了拒绝项
			$denys = $this->in_array($aroname, $perm["deny"]);//拒绝当前ARO
			if ($denys){//拒绝当前ACO的所有action
				return false;
			}
		}

		if (is_array(@$perm["allow"])){//允许当前ACO
			$allow = $this->in_array($aroname, $perm["allow"]);//允许当前ARO
			if ($allow){//允许当前ACO的所有action
				return true;
			}
		}

		if(@$perm["deny"]=="*")return false;//拒绝优先

		if (@$perm["allow"]=="*")return true;//允许所有

		if($aroname=="/") return false;//都没找到

		$aronames = explode("/", $aroname);
		array_pop($aronames);
		$aroname = count($aronames)==1 ? "/" : join("/", $aronames);
		return $this->check_role_permission($aroname, $aconame);
	}
	private function need_controll($aconame){
		$array = \app\yze_get_ignore_acos();

		foreach ((array)$array as $aco) {
			$newaco = strtr($aco, array("*"=>".*"));
			if (preg_match("{^".$newaco."}i", $aconame)){
				return null;
			}
		}
		foreach ((array)$this->acos_aros as $aco=>$ignore) {
			$newaco = strtr($aco, array("*"=>".*"));
			if (preg_match("{^".$newaco."}i", $aconame)){
				return $aco;
			}
		}
		return null;
	}
	private function in_array($check, array $arrays){
		foreach ($arrays as $k) {
			if ($k==$check) return true;
			if(substr($k, -1) != "*"){
				$k .= "/*";
			}
			$k = strtr($k, array("*"=>".*"));
			if (preg_match("{^".$k."$}i", $check)) {
				return true;
			}
		}
		return false;
	}

	/**
	 *
	 * @return YZE_ACL
	 */
	public static function get_instance()
	{
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}

	/**
	 *
	 * 开始检查权限，在begin_check_permission和end_check_permission之间的内容将在$aroname具有$aconame访问权限时输出
	 *
	 * @author leeboo
	 * @param string $aroname
	 * @param string $aconame
	 * @return void
	 */
	public function begin_check_permission($aroname, $aconame){
		ob_start();
		if(isset($this->permission_cache[$aroname][$aconame])) return;
		$this->permission_cache[$aroname][$aconame] = $this->check_byname($aroname, $aconame);
	}

	/**
	 * 在begin_check_permission和end_check_permission之间的内容将在$aroname具有$aconame访问权限时输出
	 * @param $id
	 * @param $aroname
	 * @param $aconame
	 * @return void
	 */
	public  function end_check_permission($aroname, $aconame){
		if(@$this->permission_cache[$aroname][$aconame]){
			ob_end_flush();
			return;
		}
		ob_end_clean();
	}

	/**
	 * 检查$aroname是否有对$aconame对访问权限
	 *
	 * @param string $aroname
	 * @param string $aconame
	 * @return bool
	 */
	public function check_byname($aroname, $aconame){
	    $aconame = $this->need_controll($aconame);
		if ( ! $aconame) {
			return true;
		}

		if(function_exists("get_user_permissions")){
			$check_rst = $this->check_user_permission($aconame);
			if($check_rst!==-1)return $check_rst;
		}
		if(is_array($aroname)){//当前用户有多个角色
			foreach ($aroname as $value) {
				$check_rst = $this->check_role_permission($value, $aconame);
				if($check_rst)return true;
			}
			return false;
		}else{
			return $this->check_role_permission($aroname, $aconame);
		}
	}

}

?>
