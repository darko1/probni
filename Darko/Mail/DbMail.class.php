<?php
define('DB_NAME', 'oopispit');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8');


require_once('MyMail.class.php');
class Baza {
    protected $dbHandler;

    public function __construct() {
        try {	
                $dbHandler = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
                $dbHandler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $dbHandler->exec("SET NAMES '" . DB_CHARSET . "'");
        } catch (PDOException $e) {
                echo $e->getMessage();
        }
        $this->dbHandler = $dbHandler;
        return $dbHandler;
    }
    
    public function getHandler() {
        return $this->dbHandler;
    }
    
    public function __destruct() {
        
    }
}

class AMail {
	public $id;
	public $to;
	public $cc;
	public $bcc;
	public $from;
	public $subject;
	public $message;
	public $datum;
}

class DbMail  extends Baza  implements IOnMailSend{
	public function AfterSend($MailObj){
		
		$this->Save($MailObj->to,$MailObj->cc,$MailObj->bcc,$MailObj->from,$MailObj->subject,$MailObj->message);
	}


	public function Save($to,$cc,$bcc,$from,$subject,$message){
		$statementHandler = $this->dbHandler->prepare( "INSERT INTO maillog (`to`,`cc`,`bcc`,`from`,`subject`,`message`,`datum`) VALUES (:to, :cc,:bcc, :from, :subject, :message, NOW()) ;");					
		$statementHandler->bindParam(":to", $to);
		$statementHandler->bindParam(":cc", $cc);
		$statementHandler->bindParam(":bcc", $bcc);
		$statementHandler->bindParam(":from", $from);
		$statementHandler->bindParam(":subject", $subject);
		$statementHandler->bindParam(":message", $message);
		$statementHandler->execute();
	}
	public function getall(){
		$statementHandler = $this->dbHandler->prepare( "SELECT * FROM maillog ORDER BY datum DESC ;");	
		$statementHandler->execute();	
		return $statementHandler->fetchAll(PDO::FETCH_CLASS, 'AMail');    		
	}
}

?>