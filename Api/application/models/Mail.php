<?php

/**
 * 
 */

include __DIR__."/../../vendor/autoload.php";
use Nette\Mail\Message;
class MailModel
{
	public $errno = 0;
	public $errmsg = "";
	private $db = null;

	public function __construct()
	{
		$this->db = new PDO("mysql:host=127.0.0.1;dbname=api;","root","root");
	}

	public function send($uid, $title, $contents)
	{
		$query = $this->db->prepare("select `email` from `user` where `id`=?");
		$query->execute(array(intval($uid)));
		$ret = $query->fetchAll();
		if(!$ret && count($ret)!=1){
			$this->errno = 3003;
			$this->errmsg = "查找用户信息失败";
			return false;
		}

		$userEmail = $ret[0]['email'];
		if(!filter_var($userEmail,FILTER_VALIDATE_EMAIL)){
			$this->errno = 3004;
			$this->errmsg = "用户邮箱不符合标准,邮箱地址为:".$userEmail;
		}

		$mail = new Message;
		$mail->setFrom('邮箱接口测试 PHP API <xzk1991526@163.com>')
			 ->addTo($userEmail)
			 ->setSubject($title)
			 ->setBody($contents);
		$mailer = new Nette\Mail\SmtpMailer([
				'host' => 'smtp.163.com',
				'username' => 'xzk1991526@163.com',
				'password' => 'xzk5201314', /*smtp独立密码*/
				'secure' =>'ssl',
		]);	 
		
		$rep = $mailer->send($mail);
		return $userEmail;

	}
}
?>