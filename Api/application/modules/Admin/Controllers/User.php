<?php
/**
 * 
 */


class UserController extends Yaf_Controller_Abstract
{
	
	public function indexAction()
	{
		echo $_SERVER['REQUEST_URI'];
		// echo Yaf_Application::environ('oid');
		exit;
		// $this->getView()->assign('name','张三丰');
	}

	public function userAction()
	{
		$name = $this->getRequest()->getPost('name',false);
		$password = $this->getRequest()->getPost('password',false);
		$mobile = $this->getRequest()->getPost('mobile',false);

		if(!$name || !$password){
			echo json_encode(array("errno"=>-1002,"errmsg"=>"用户名与密码必须传递"));
			return false;
		}
		if(!$mobile){
			echo json_encode(array("errno"=>-1002,"errmsg"=>"电话号码不能为空"));
			return false;
		}

		$model = new UserModel();
		if($model->register(trim($name),trim($password),trim($mobile))){
			echo json_encode(array(
							"errno"=>0,
							"errmsg"=>"",
							"data"=>array("name"=>$name)
			));
		}else{
			echo json_encode(array(
				"errno"=>$model->errno,
				"errmsg"=>$model->errmsg,
			));
		}

		return true;
	}


	public function loginAction()
	{	
		$submit = $this->getRequest()->getQuery('submit',"0");
		if( $submit < 1){
			echo json_encode(array("errno"=>-1001,"errmsg"=>"请通过正确渠道提交",));
			return false;
		}

		$name = $this->getRequest()->getPost('name',false);
		$password = $this->getRequest()->getPost('password',false);
		
		if(!$name || !$password){
			echo json_encode(array("errno"=>-1002,"errmsg"=>"用户名与密码必须传递"));
			return false;
		}

		$model = new UserModel();

		$uid = $model->login(trim($name),trim($password));
		// echo json_encode(array("用户"=>$uid));
		if($uid){
			//种session
			session_start();
			$_SESSION['user_token'] = md5("salt".$_SERVER['REQUEST_TIME'].$uid);
			$_SESSION['user_token_time'] = $_SERVER['REQUEST_TIME'];
			$_SESSION['uid'] = $uid;
			echo json_encode(array(
				"errno"=>0,
				"errmsg"=>"",
				"data"=>array("data"=>$name,"密码"=>$uid)
			));
		}else{
			echo json_encode(array(
				"errno"=>$model->errno,
				"errmsg"=>$model->errmsg,
			));
		}
		
		return true;
	}
}
?>