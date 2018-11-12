<?php

/**
 * 
 */
class UserModel	
{	
	public $errno = 0;
	public $errmsg = "";
	private $db = null;

	public function __construct()
	{
		$this->db = new PDO("mysql:host=127.0.0.1;dbname=api;","root","root");
	}
	
	//用户登录验证
	public function login($name, $password)
	{	

		$query = $this->db->prepare("select `password`,`id` from `user` where `name`=?");
		$query->execute(array($name));
		$ret = $query->fetchAll();

		if(!$ret || count($ret)!=1){
			$this->errno = -100;
			$this->errmsg = "查找用户失败";
			return false;
		}
		$userInfo = $ret[0];
		if($this->_password_generate($password) != $userInfo['password']){
			$this->errno = -1010;
			$this->errmsg = "密码错误";
			return false;
		}

		return array("数据库密码"=>$userInfo['password'],"表单密码"=>$userInfo['password']);

		return intval($userInfo[1]);
	}

	//用户注册验证
	public function register($name, $password,$mobile)
	{	
		$query = $this->db->prepare("select count(*) as c from `user` where `name`=?");
		$query->execute(array($name));
		$count = $query->fetchAll();
		if($count[0]['c'] != 0){
			$this->errno = -1005;
			$this->errmsg = "用户名已存在";
			return false;
		}

		// $password = "";
		if(strlen($password)<8){
			$this->errno = -1006;
			$this->errmsg = "密码太短,长度至少为8位";
			return false;
		}else{
			$password = $this->_password_generate($password);
			// $password = md5($password);
		}

		if(strlen($mobile) > 11){
			$this->errno = -1007;
			$this->errmsg = "电话号码长度过长";
			return false;
		}
	
		$query = $this->db->prepare("insert into `user` (`id`,`name`,`password`,`mobile`,`reg_time`) VALUES ( null, ?, ?, ?, ?)");
		$ret = $query->execute(array($name,$password,$mobile,date("Y-m-d H:i:s")));
		if(!$ret){
			$this->errno = -1008;
			$this->errmsg = "注册失败,写入数据失败";
			return false;
		}
	}


	private function _password_generate($password)
	{
		$password = md5("salt-xxxxxxxxx=".$password);
		return $password;
	}

	

}
?>