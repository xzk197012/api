<?php
/**
 * 
 */
class SmsController extends Yaf_Controller_Abstract
{
	public function indexAction()
	{

	}

	//发送短信
	public function sendAction()
	{
		$submit = $this->getRequest()->getQuery("submit",false);
		if(!$submit){
			echo json_encode(array("errno"=>4001,"errmsg"=>"请通过正确渠道提交"));
			return false;
		}

		$uid = $this->getRequest()->getPost("uid",flase);
		$contents = $this->getRequest()->getPost('contents',false);
		$templateId = $this->getRequest()->getPost('templateId',false);

		if(!$uid || !$templateId){
			echo json_encode(array('errno'=>4001,'errmsg'=>'用户ID.模板不能为空',));
			return false;
		}

		$model = new SmsModel();
		if($data = $model->send(intval($uid),intval($templateId))){
			echo json_encode(array(
					'errno'=>0,
					'errmsg'=>'',
					'data'=>$data,
			));
		}else{
			echo json_encode(array(
					'errno'=>$model->errno,
					'errmsg'=>$model->errmsg,
			));
		}
		// echo json_encode(array($uid,$contents));
		die;
		return true;
	}
}
?>