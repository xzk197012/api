<?php

/**
 * 
 */
class ArticleModel
{	
	public $errno = 0;
	public $errmsg = "";
	private $db = null;

	public function __construct()
	{
		$this->db = new PDO("mysql:host=127.0.0.1;dbname=api;","root","root");
		// $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
		$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
	}
	// $this->db->setAttribute(PDO::ATTR_EMULATE_PAERARES,false);
	//文章添加与删除
	public function add($title, $contents, $author, $cate, $artId=0)
	{
		$isEdit = false;
		if($artId != 0 && is_numeric($artId)){
			//文章修改
			$query = $this->db->prepare("select count(*) from `article` where `id`=?");
			$query->execute(array($artId));
			$ret = $query->fetchAll();
			if( !$ret || count($ret)!=1){
				$this->errno = "-2004";
				$this->errmsg = "找不到你要编辑的文章内容,ErrInfo:".end($query->errorInfo());
				return false;
			}
			$isEdit = true;
		}else{
			//检查分类是否存在
			$query = $this->db->prepare("select count(*) from `art_cate` where `id`=?");
			$query->execute(array($cate));
			$ret = $query->fetchAll();
			if(!$ret || $ret[0][0]==0){
				$this->errno = "-2005";
				$this->errmsg = "找不到此分类的信息,请先去创建分类,ErrInfo:".end($query->errorInfo());
				return false;
			}
		}

		/*插入或者更新文章内容*/

		$data = array($title, $contents, $author, intval($cate));
		if( !$isEdit ){
			$query = $this->db->prepare("insert into `article` (`title`,`contents`,`author`,`cate`) VALUES (?,?,?,?)");
		}else{
			$query = $this->db->prepare("update `article` set `title`=?,`contents`=?,`author`=?,`cate`=? where `id`=?");
			$data[] = $artId; 
		}

		$ret = $query->execute($data);
		if( !$ret ){
			$this->errno = "-2006";
			$this->errmsg = "操作数据库失败,ErrInfo:".end($query->errorInfo());
			return false;
		}

		/*
		返回文章最后的ID
		*/
		if( !$isEdit ){
			return intval($this->db->lastInsertId());
		}else{
			return intval($artId);
		}

	}

	//文章删除
	public function del($artId)
	{
		$query = $this->db->prepare("delete from `article` where `id`=?");
		$ret = $query->execute(array(intval($artId)));
		if(!$ret){
			$this->errno = -2007;
			$this->errmsg = "删除失败,ErrInfo:".end($query->errorInfo());
			return false;
		}
		return true;
	}

	//文章状态修改
	public function status($artId, $status="offline")
	{	
		$data = array($status,intval($artId));
		$query = $this->db->prepare("update `article` set `status`=? where `id`=?");
		$ret = $query->execute($data);
		if(!$ret){
			$this->errno = -2008;
			$this->errmsg = "更改文章状态失败,ErrInfo:".end($query->errorInfo());
			return false;
		}

		return true;
	}

	//获取文章
	public function get($artId)
	{
		$query = $this->db->prepare("select * from `article` where `id`=?");
		$query->execute(array(intval($artId)));
		$ret = $query->fetchAll();
		if(!$ret){
			$this->errno = -2009;
			$this->errmsg = "查询文章信息失败,ErrInfo:".end($query->errorInfo());
			return false;
		}

		//获取分类信息
		$artInfo = $ret[0];
		// return $artInfo['cate'];
		$query = $this->db->prepare("select `name` from `art_cate` where `id`=? ");
		$query->execute(array($artInfo['cate']));
		$ret = $query->fetchAll();
		if(!$ret){
			$this->errno = -2010;
			$this->errmsg = "获取文章分类信息失败,ErrInfo:".end($query->errorInfo());
			return false;
		}
		$artInfo['cateName'] = $ret[0]['name'];

		$data = array(
				"id"=>intval($artId),
				"title"=>$artInfo['title'],
				"contents"=>$artInfo['contents'],
				"author"=>$artInfo['author'],
				"cateName"=>$artInfo['cateName'],
				"cateId"=>intval($artInfo['cate']),
				"ctime"=>$artInfo['ctime'],
				"mtime"=>$artInfo['mtime'],
				"status"=>$artInfo['status'],
		);

		return $data;
	}

	//分页信息
	public function list($pageNo=0, $pageSize=10, $cate=0, $status="online")
	{	
		// return $cate;
		// return $pageNo.$pageSize.$cate.$status;
		$start = $pageNo * $pageSize + ($pageNo==0?0:1);
		// return intval($start).intval($pageSize).$cate;
		// return;
		if($cate == 0){
			$filter = array($status, intval($start), intval($pageSize));
			$query = $this->db->prepare(" select `id`,`title`,`contents`,`author`,`cate`,`ctime`,`mtime`,`status` from `article` where `status`=? order by `id` desc limit ?,? " );
		}else{
			$filter = array(intval($cate), $status, intval($start), intval($pageSize));
			$query = $this->db->prepare(" select `id`,`title`,`contents`,`author`,`cate`,`ctime`,`mtime`,`status` from `article` where `cate`=? and `status`=? order by `id` desc limit ?,? ");
		}
		// return $filter;
		$query->execute($filter);
		$ret = $query->fetchAll();
		if(!$ret){
			$this->errno = -2012;
			$this->errmsg = "获取文章列表失败,ErrInfo:".end($query->errorInfo());
			return false;
		}

		// return $cate;
		$data = array();
		$cateInfo = array();

		foreach ($ret as $item) {
			/**
			 *获取分类信息
			 */
			if(isset($cateInfo[$item['cate']])){
				$cateName = $cateInfo[$item['cate']];
			}else{
				$query = $this->db->prepare("select `name` from `art_cate` where `id`=?");
				$query->execute(array($item['cate']));
				$retCate = $query->fetchAll();
				if(!$retCate){
					$this->errno = -2010;
					$this->errmsg = "获取文章分类信息失败 ErrInfo:".end($query->errorInfo());
					return false;
				}
				$cateName = $cateInfo[$item['cate']] = $retCate[0]['name'];
			}

			//正文太长则剪切
			$contents = mb_strlen($item['contents'])>30 ? mb_substr($item['contents'],0,30)."......" : $item['contents'];
			$data[] = array(
				"id"=>intval($item['id']),
				"title"=>$item['title'],
				"contents"=>$contents,
				"author"=>$item['author'],
				"cateName"=>$cateName,
				"cateId"=>intval($item['cate']),
				"ctime"=>$item['ctime'],
				"mtime"=>$item['mtime'],
				"status"=>$item['status'],
			);

		}
		return $data;

	}
	
}
?>