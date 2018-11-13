<?php
/**
 * 
 */
class ArticleController extends Yaf_Controller_Abstract
{	

	//添加文章
	public function addAction($artId = 0)
	{	
		if (!$this->_isAdmin()) {
			echo json_encode(array("errno"=>-2000,"errmsg"=>"需要管理员权限才可以操作"));
			return false;
		}
		
		$submit = $this->getRequest()->getQuery('submit',"0");
		if( $submit < 1){
			echo json_encode(array("errno"=>-2001,"errmsg"=>"请通过正确渠道提交",));
			return false;
		}

		//获取参数
		$title = $this->getRequest()->getPost("title",false);
		$contents = $this->getRequest()->getPost("contents",false);
		$author = $this->getRequest()->getPost("author",false);
		$cate = $this->getRequest()->getPost("cate",false);

		if(!$title || !$contents || !$author || !$cate){
			echo json_encode(array("errno"=>-2002,"errmsg"=>"标题.内容.作者.分类  不能为空"));
			return false;
		}

		$model = new ArticleModel();

		if($lastId = $model->add(trim($title),trim($contents),trim($author),trim($cate),$artId)){
			echo json_encode(array(
					"errno"=>0,
					"errmsg"=>"",
					"data"=>array("lastId"=>$lastId),
			));
		}else{
			echo json_encode(array(
					"errno"=>$model->errno,
					"errmsg"=>$model->errmsg,
			));
		}

		// echo json_encode(array("title"=>$title,"contents"=>$contents,"author"=>$author,"cate"=>$cate));
		return true;
	}
	
	//修改文章
	public function editAction()
	{
		if(!$this->_isAdmin()){
			echo json_encode(array("errno"=>-2000,"errmsg"=>"需要管理员权限才能操作"));
			return false;
		}
		$artId = $this->getRequest()->getQuery('artId',false);
		// echo json_encode(array("errno"=>$artId));
		// exit;
		if(is_numeric($artId) && $artId){
			return $this->addAction($artId);
		}else{
			echo json_encode(array("errno"=>-2003,"errmsg"=>"缺少必要的文章ID参数"));
		}

	}
	//删除文章 
	public function delAction()
	{
		if(!$this->_isAdmin()){
			echo json_encode(array("errno"=>-2000,"errmsg"=>"需要管理员权限才能操作"));
			return false;
		}

		$artId = $this->getRequest()->getQuery('artId',false);
		if(is_numeric($artId) && $artId){
			$model = new ArticleModel();
			if($model->del($artId)){
				echo json_encode(array(
						"errno"=>0,
						"errmsg"=>"",
				));
			}else{
				echo json_encode(array(
						"errno"=>$model->errno,
						"errmsg"=>$model->errmsg,
				));
			}
		}else{
			echo json_encode(array("errno"=>-2003,"errmsg"=>"缺少必要的文章ID参数"));
		}
		return true;
	}

	//文章状态更改
	public function statusAction()
	{
		if(!$this->_isAdmin()){
			echo json_encode(array("errno"=>-2000,"errmsg"=>"需要管理员权限才能操作"));
			return false;
		}

		$artId = $this->getRequest()->getQuery("artId",false);
		$status = $this->getRequest()->getQuery("status","offline");
		// echo json_encode(array("id"=>$artId,"status"=>$status));die;

		if(is_numeric($artId) && $artId){
			$model = new ArticleModel();
			if($model->status($artId, $status)){
				echo json_encode(array(
						"errno"=>0,
						"errmsg"=>"",
				));
			}else{
				echo json_encode(array(
						"errno"=>$model->errno,
						"errmsg"=>$model->errmsg,
				));
			}
		}else{
			echo json_encode(array(
					"errno"=>-2003,
					"errmsg"=>"缺少必要的文章参数",
			));
		}
	}

	//获取文章内容
	public function getAction()
	{	
		$artId = $this->getRequest()->getQuery("artId",false);
		if(is_numeric($artId) && $artId){
			$model = new ArticleModel();
			if($data = $model->get($artId)){
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
		}else{
			echo json_encode(array("errno"=>-2003,"errmsg"=>"缺少必要的文章ID参数"));
		}
		return true;
	}

	//文章列表分页
	public function listAction()
	{
		$pageNo = $this->getRequest()->getQuery('pageNo','0');
		$pageSize = $this->getRequest()->getQuery("pageSize","10");
		$cate = $this->getRequest()->getQuery("cate","0");
		$status = $this->getRequest()->getQuery("status","online");


		$model = new ArticleModel();
		if($data = $model->list($pageNo, $pageSize, $cate, $status)){
			echo json_encode(array(
					"errno"=>0,
					"errmsg"=>"111",
					"data"=>$data,
			));
		}else{
			echo json_encode(array(
					"errno"=>$model->errno,
					"errmsg"=>$model->errmsg,
			));
		}

		return true;
	}

	//判断是否有权限
	private function _isAdmin()
	{
		return true;
	}
}
?>