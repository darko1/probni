<?php

interface IOnMailSend {
	public function AfterSend($MailObj);	
}


class MyMail{
	const smtpServer= 'mail.t-com.hr';
	private $ToArray=array();	
	private $CCArray=array();	
	private $BCCArray=array();	
	private $from;
	private $subject;
	private $message;
	private $mime_boundary;
	private	$attachment=array();
	private $AfterSend = array();	
	
	public function __construct($to='',$from='',$subject='',$message='',$attachment='',$OnSend=null){
		$this->mime_boundary=$this->getBoundary();
		$this->AddTo($to);
		$this->AddAttachment($attachment);
		$this->subject=$subject;
		$this->setFrom($from);
		$this->setMessage($message);
		$this->RegAfterSend($OnSend);
	}
	
	public function __set($name, $value)
    {        
        $this->$name = $value;
    }
	
	
	public function __get($name)
    {   		
		switch (strtolower($name)){
			case 'to':
				return implode(",", $this->ToArray);
			break;
			case 'cc':
				return implode(",", $this->CCArray);
			break;
			case 'bcc':
				return implode(",", $this->BCCArray);
			break;
			default:
				return $this->$name;
			break;
		}        
    }
	
	/*
		registrira objekte koji implementiraju IOnMailSend interface
	*/
	public function RegAfterSend($obj){
		if(isset($obj)){
			if ($obj instanceof IOnMailSend){
				$this->AfterSend[]=$obj;
			} else {
				throw new Exception('Objekt '.get_class($obj).' ne implementira IOnMailSend');
			}
		}
	}
	/*
		prolazi registrirane objekte i zove AfterSend() funkciju
		kao parametar šalje sam sebe tak da MyMail klasa ne vodi brigu
		kaj kojem objektu treba
	*/
	private function DoAfterSend(){		
		if(!empty($this->AfterSend)){
			foreach($this->AfterSend as $obj){
				try{
					$obj->AfterSend($this);
				} catch (Exception $e){
					$this->error[]=$e->getMessage();
				}							
			}
		}
	}
	
	private function isValidAddress($Address){
		if(strpos($Address,',')){
			$arr=explode(',',$Address);
		}elseif (strpos($Address,';')){
			$arr=explode(';',$Address);
		}else $arr[]=$Address;
		foreach($arr as $key=>$adresa){
			$arr[$key]= filter_var($adresa, FILTER_VALIDATE_EMAIL);
		}
		return implode(',',$arr);
	}
	private function getBoundary(){
		$rnd = md5(microtime()); 
		return $rnd; 
	}
	private function getHeaders(){
		$headers=array();
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-type: multipart/mixed; boundary=\"$this->mime_boundary\"; charset=\"utf-8\"";		
		$headers[] = "From: $this->from";		
		$headers[] = "Reply-To: $this->from";	
		if (!empty($this->CCArray)){
			$headers[] = 'Cc: '.$this->cc ."\r\n";
		}
		if (!empty($this->BCCArray)){
			$headers[] = 'Bcc: '.$this->bcc ."\r\n";
		}
		$headers[] = "Content-Transfer-Encoding: 7bit";
		//$headers[] = "X-Mailer: PHP/".phpversion();
		return implode("\r\n", $headers);
	}
	
	private function getAttachment(){
		$message='';
		for($i=0;$i<count($this->attachment);$i++){
			$data='';
			$message .= "--$this->mime_boundary\n";
			if((!is_array($this->attachment[$i])) && (is_file($this->attachment[$i]))){				
				$fp =    @fopen($this->attachment[$i],"rb");
				$data =    @fread($fp,filesize($this->attachment[$i]));
                @fclose($fp);
				$name=$this->attachment[$i];
				$len=filesize($name);
			} else{
				
				$name=$this->attachment[$i]['name'];
				$data=$this->attachment[$i]['data'];
				$len=strlen($data);
			}
			if(!empty($data)){
				$data = chunk_split(base64_encode($data));
				$message .= "Content-Type: application/octet-stream; name=\"".basename($name)."\"\n" .
				"Content-Description: ".basename($name)."\n" .
				"Content-Disposition: attachment;\n" . " filename=\"".basename($name)."\"\n" .
				"Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
            }
        }
		
		return $message;
	}
	public function send(){
		
		$to=$this->to;
		$message=$this->getMessage();
		if (empty($to)){throw new Exception("Nije zadano kome se šlje");}
		if (empty($this->from)){throw new Exception("Nije zadano tko šlje");}
		if (empty($this->subject)){throw new Exception("Nije zadan subject ");}
		if (empty($message)){throw new Exception("Poruka je prazna ");}
		
		ini_set('SMTP',self::smtpServer);
		$ok=mail($this->to,$this->setSubject($this->subject),$message,$this->getHeaders());
		
		if($ok ){			
			$this->DoAfterSend();
		}
		return $ok;
	}
	Private function msgToHtmml($message){
		$result='<!DOCTYPE html>
					<html lang="hr">
					<head>
					<meta charset="utf-8">
					</head>
					<body>';
		$result.=$this->message . "\n\n";	
		$result.='</body>
		</html>'. "\n\n";
		return $result;
	}
	private function getMessage(){	
		
		$message="--$this->mime_boundary\r\n";		
		$message.= "Content-Type: text/html; charset=\"utf-8\";\n" .	"Content-Transfer-Encoding: 7bit\n\n";
		$message.=$this->msgToHtmml($this->message);
		
		$message.=$this->getAttachment();
		$message.="--$this->mime_boundary--"; 
		return $message;
		
	}
	
	public function AddTo($to){
		$to=$this->isValidAddress($to);
		if(!empty($to)){
			$this->ToArray[]=$to;
		}
	}
	public function AddCC($cc){
		$cc=$this->isValidAddress($cc);
		if(!empty($cc)){
			$this->CCArray[]=$cc;
		}
	}
	public function AddBCC($bcc){
		$bcc=$this->isValidAddress($bcc);
		if(!empty($bcc)){
			$this->BCCArray[]=$bcc;
		}
	}
	
	public function AddAttachment($attachment,$data=null){
		if(!empty($attachment)){
			if(is_array($attachment)){
				$this->attachment=$attachment;
			} elseif($data==null){
				$this->attachment[]=$attachment;
			}else{
				$this->attachment[]=array('name'=>$attachment,'data'=>$data);				
			}
		}
	}
	
	public function setSubject($subject){
		return '=?UTF-8?B?'.base64_encode($subject).'?=';
	}
	public function setFrom($from){
		$this->from=$from;
	}
	
	public function setMessage($message){
		$this->message=$message;
	}
	public function setOnSend($OnSend){
		$this->OnSend=$OnSend;
	}
	
}
?>