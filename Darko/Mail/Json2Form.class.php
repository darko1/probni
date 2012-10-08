<?php
include_once('Forma.class.php');
class Json2Form{
	private $jsn;
	private $decoded;	
	private $name;
	public $forms=array();
	
	
	public function __construct($jsn,$name){
		$this->name=$name;
		$this->jsn=file_get_contents($jsn);
		$this->decoded = json_decode($this->jsn,true);		
		$this->build();
	}
	/*private function buildValidator(){
		$this->validator= new FormValidator('validator',$this->name);
		foreach($this->decoded['grupa'] as $key=>$value){			
			if (isset($value['rule'])){
				$this->validator->addRule($key,$value['rule']);
			}
			if (isset($value['message'])){
				$this->validator->addMessage($key,$value['message']);
			}
		}
	}*/
	private function CreateControl($aclass,$name,$data){
		return new $aclass($name,$data);
	}
	
	private function build(){		
		if(!empty($this->decoded)){
			foreach ($this->decoded as $key=>$value){
				$this->forms[$key]= new Forma($key);
				foreach($value as $itemname=>$item){					
					if(isset($item['type'])){
						if (in_array($item['type'],get_declared_classes())){
							
							$this->forms[$key]->addControl($this->CreateControl($item['type'],$itemname,$item));
						}else throw new Exception('Klasa '.$item['type'].' nije deklarirana !!!');						
					}else throw new Exception($itemname.'  nema zadan type !!!');						
				}
			}
		}
						
	}	
	public function dumpMyForm(){
	
		if(!empty($this->forms)){		
			foreach($this->forms as $form){$form->dumpForm();}
		}
	}
}

?>