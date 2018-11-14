<?php
/**
 * 
 */
class SmsModel
{
	
	public $errno = 0;
	public $errmsg = '';
	private $db = null;

	public function __construct()
	{
		$this->db = new PDO("mysql:host=127.0.0.1;dbname=api","root","root");
	}

	public function send($uid, $templateId)
	{
		$query = $this->db->prepare("select `mobile` from `user` where `id`=?");
		$query->execute(array(intval($uid)));
		$ret = $query->fetchAll();
		if(!$ret || count($ret)!=1){
			$this->errno = 4003;
			$this->errmsg = '查询手机信息失败';
			return false;
		}

		$userMobile = $ret[0]['mobile'];
		if(!$userMobile || !is_numeric($userMobile) || strlen($userMobile)!=11){
			$this->errno = 4004;
			$this->errmsg = '用户手机号码不符合要求 手机号为:'.(!$userMobile ? "空" : $userMobile);
			return false;
		}
		
		$uid = "xzk197012";
		$pwd = "df4a624ae5a31a58c354dd46a7ed0ffe";
		$sms = new ThirdParty_Sms($uid, $pwd);

		$contentParam = array('code'=>rand(1000,9999));
		$template = $templateId;
		$res = $sms->send($userMobile, $contentParam, $template);
		if($res['stat'] == '100'){
			return true;
		}else{
			$this->errno = 4005;
			$this->errmsg = "发送失败:".$res['stat'].'('.$res['message'].')';
			return false;
		}

		return $userMobile;
	}
}
?>
