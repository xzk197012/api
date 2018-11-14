<?php

/**
 * 
 */
class MailController extends Yaf_Controller_Abstract
{
	
	public function sendAction()
	{	

		$submit = $this->getRequest()->getQuery("submit",false);
		if(!$submit){
			echo json_encode(array("errno"=>3000,"errmsg"=>"请通过正确的渠道提交"));
			return false;
		}

		//获取参数
		$uid = $this->getRequest()->getPost("uid",false);
		$title = $this->getRequest()->getPost("title",false);
		$contents = $this->getRequest()->getPost("contents",false);
		if(!$uid || !$title || !$contents){
			echo json_encode(array(
					"errno"=>3001,
					"errmsg"=>"用户ID.邮件标题.邮件内容,均不能为空",
			));
			return false;
		}

		//调用model 发邮件
		$model = new MailModel();
		if($data = $model->send(intval($uid), trim($title), trim($contents))){
			echo json_encode(array(
					"errno"=>0,
					"errmsg"=>"",
					"data"=>$data,
			));
		}else{
			echo json_encode(array(
					"errno"=>$model->errno,
					"errmsg"=>$model->errmsg,
			));
		}
		die;
	}
}
?>