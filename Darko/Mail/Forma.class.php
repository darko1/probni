<?php

/*  21.05.2012 izmjenjen Tedit da podr�ava password,
 *		dadani property i visible i enabled,
 *		dodane klase:	TCheckBox, TCheckBoxGroup i TRadioGroup.
 *		TRadioGroup za default koristi "itemindex":jedna od vrijednosti iz "value",
 *		TCheckBoxGroup za default koristi array  "checked":[1,1,1], 
 *		TCheckBoxGroup i TRadioGroup ako postoji property "inline":"bilo�ta"  
 *  23.05.2012/02:23
 *      Ispravljen Tcheckbox da radi value kako treba
 *		TradioGroup JSON value promjenjen u items, itemindex izba�en, value radi ispravno
 * TcheckBoxGroup nevalja, nemogu�e dodjeliti vrijednosti osim defaultnih 
 *  	neplanirano: zapravo na formi je n checkboxeva a samo jedan objekt u formi.
 *		mo�da je bolje TcheckBox u dodat property group:integer za grupiranje i inline:boolean za prikaz u liniji
 *		a sam TcheckBoxGroup izbaciti
 *
 *	24.05.2012
 *		izba�en TcheckBoxGroup, TcheckBox ima property group, pa ako ih je vi�e za redom i imaju istu vrijednost pona�aju se kao jedna komponenta,
 *		isto radi i za TButton
 *		za kreiranje kontrola koristim "new $aclass($name,$data)" pa type u JSON u mora biti klasa kontrole.
 *		dodan TComboBox, prvi item je "placeholder" bez vrijednosti tak da form validator radi.
*/

include_once('formvalidator.inc.php');
class Forma{
	public $klasa;
	public $name;
	public $action;
	public $controls=array();
	public $validator;
	Public $enctype='';
	
	public function  __get($name) {
        return $this->$name;
    }
	
	public function __construct($name='fofm1',$klasa='well form-horizontal',$action='#'){
		$this->name=$name;
		$this->klasa=$klasa;
		$this->action = $action;		
		$this->validator= new FormValidator('valid1',$this);
	}
	public function addControl($acontrol){
		$this->controls[$acontrol->name]=$acontrol;
		$acontrol->owner=$this;
		if ($acontrol instanceof Tfile){$this->enctype="multipart/form-data";}
		if(isset($acontrol->rule)){$this->validator->addRule($acontrol->name,$acontrol->rule);}
		if(isset($acontrol->message)){$this->validator->addMessage($acontrol->name,$acontrol->message);}
	}
	/////////////////////////////////////////////////////////////ovo mo�e bolje
	private function findControl($acontrol){
		reset($this->controls);
		if(in_array($acontrol,$this->controls)){						
			while(current($this->controls)!==$acontrol){
				next($this->controls);
			}			
		}
		if(current($this->controls)===$acontrol){ 
			return true;
		}else	return false;
	}
	////////////////////////////////////////////////////////
	public function NextControl($acontrol){
		if($this->findControl($acontrol)){
			return next($this->controls);
		}
		return false;
	}
	public function PrevControl($acontrol){
		if($this->findControl($acontrol)){
			return prev($this->controls);
		}
		return false;
	}
	public function dumpForm(){
		$enctype='';
		if(!empty($this->enctype)){$enctype="enctype=\"{$this->enctype}\"";}
		echo "<form class=\"{$this->klasa}\" method=\"post\" name=\"{$this->name}\" id=\"{$this->name}\"  action=\"{$this->action}\" $enctype>";
		foreach($this->controls as $control){$control->dumpControl();}
		echo "</form>";
		$this->validator->dumpHeaderCode();
	}
}

class TControl{
	public    $owner= null;
	protected $klasa;
	protected $name;
	protected $label;
	protected $placeholder;
	protected $max_size;
	public 	  $value;	
	protected $enabled = true;
	protected $visible = true;
	public	  $group   = false;		
	
	public function  __get($name) {
        return $this->$name;
    }
	
	public function __construct($name='',$data=array()){
		$this->name=$name;
		if (!empty($data)){
			foreach($data as $key=>$value){				
				$this->$key=$value;				
			}			
		}
	}
	protected function getKlasa(){
		if ($this->enabled){
			return $this->klasa;
		} else return $this->klasa." disabled";
	}
	protected function getDisabled(){
		if ($this->enabled){
			return "";
		} else return " disabled";
	}
	protected function isFirstInGroup(){	
		if ($this->group){
			$prev=$this->owner->PrevControl($this);			
			return !($prev && $prev->visible && ($prev->group==$this->group));
		} else return false;
	}
	protected function isLastInGroup(){
		if ($this->group){
			$next=$this->owner->NextControl($this);
			return !($next && $next->visible && ($next->group==$this->group));
		} else return false;
	}
	protected function margina(){
		$prev=$this->owner->PrevControl($this);	
		if($prev){
			echo 'style="margin-top:-15px;"';
		}
	}
}

class TEdit extends TControl{
	
	protected $password=false;
	
	public function __construct($name='',$data=array()){
		parent::__construct($name,$data);
			if ($this->password){
				$this->type="password";
			} else $this->type="text";
	}
	public function dumpControl(){  
		
	?>
		<div <?php $this->margina()?> class="control-group " <?php if(!$this->visible){echo 'style="display: none;"';}?>>	
			<label class="control-label" for="<?php echo $this->name;?>"><?php echo $this->label;?></label>  
			<div class="controls">
				<input class="<?php echo $this->getKlasa();?>" name="<?php echo $this->name;?>" id="<?php echo $this->name;?>" type="<?php echo $this->type;?>" maxlength="<?php echo $this->max_size;?>" placeholder="<?php echo $this->placeholder;?>"  value="<?php echo $this->value;?>" <?php echo $this->getDisabled();?>>		
			</div>					
  		</div>
	<?php
		
	}
	public function getInline(){  			
		return	"<input class=\"{$this->getKlasa()}\" name=\"$this->name\" id=\"$this->name\" type=\"$this->type\" maxlength=\"$this->max_size\" placeholder=\"$this->placeholder\"  value=\"$this->value\" {$this->getDisabled()}>";									  				
	}
}

class TButton extends TControl{
	protected $kind;
	protected $icon;
	
	public function dumpControl(){
		if($this->visible){	
			if (($this->group && $this->isFirstInGroup())or(!$this->group)){
		 ?>
			<div class="controls">	
			<?php } ?>
				<?php echo"<button type=\"{$this->kind}\" name=\"{$this->name}\" id=\"{$this->name}\"  class=\"{$this->getKlasa()}\"><i class=\"{$this->icon}\"></i>$this->label</button>"?>
			<?php if (($this->group && $this->isLastInGroup()) or (!$this->group)){ ?>	
			</div>	
		<?php 
			}
		}
	}
}



class TCheckBox extends TControl{
	protected $opis='';
	public function dumpControl(){
		
			if (($this->group && $this->isFirstInGroup())or(!$this->group)){
			?>
			<div <?php $this->margina()?> class="control-group " <?php if(!$this->visible){echo 'style="display: none;"';}?>>	
				<label class="control-label" for="<?php echo $this->name;?>"><?php echo $this->label;?></label>  
				<div class="controls">
			<?php } ?>
					<label class="checkbox <?php if(isset($this->inline)){echo "inline";}?> ">
					<input class="<?php echo $this->getKlasa();?>" name="<?php echo $this->name;?>" id="<?php echo $this->name;?>" type="checkbox"  <?php if($this->value){echo 'checked="checked"';}?> <?php echo $this->getDisabled();?>>		
					<?php echo $this->opis; ?>
					</label>
			<?php if (($this->group && $this->isLastInGroup()) or (!$this->group)){ ?>	
				</div>					
			</div>
	<?php
				}		
	}
}



class TRadioGroup extends TControl{
	
	public function dumpControl(){
		
		  ?>
			<div <?php $this->margina()?> class="control-group " <?php if(!$this->visible){echo 'style="display: none;"';}?>>	
				<label class="control-label" for="<?php echo $this->name;?>"><?php echo $this->label;?></label>  
				<div class="controls">
				<?php
				if(isset($this->items)&& is_array($this->items)&& (count($this->items)>0)){
					$i=0;
					foreach($this->items as $key=>$avalue){
					$i++;
				
				?>
					<label class="radio <?php if(isset($this->inline)){echo "inline";}?> ">				
					<input <?php if($this->value==$avalue){echo 'checked="checked"';}?> class="<?php echo $this->getKlasa();?>" name="<?php echo $this->name;?>" id="<?php echo $this->name.$i;?>" type="radio"  value="<?php echo $avalue;?>"   <?php echo $this->getDisabled();?>>		
				<?php echo $key; ?>
					</label>
				<?php }}?>
				</div>					
			</div>
	<?php		
	}
}

class TComboBox extends TControl{
	public $items= array();
	private function MakeOptions(){	
		if(!empty($this->placeholder)){	
		$result="<option value=\"\">$this->placeholder</option>";
		} else $result='';
		foreach($this->items as $key=>$avalue){
			if ($this->value==$key){
				$result.="<option selected=selected value=\"$key\">$avalue</option>";
			} else	$result.="<option value=\"$key\">$avalue</option>";
		}
		return $result;
	}
	
	public function dumpControl(){  		
	?>
		<div <?php $this->margina()?> class="control-group " <?php if(!$this->visible){echo 'style="display: none;"';}?>>	
			<label class="control-label" for="<?php echo $this->name;?>"><?php echo $this->label;?></label>  
			<div class="controls <?php echo $this->getKlasa();?>">
				<select name="<?php echo $this->name;?>" id="<?php echo $this->name;?>" <?php echo $this->getDisabled();?>  >	
					<?php echo $this->MakeOptions(); ?>
				</select>
			</div>					
  		</div>
	<?php		
	}
	public function getInline(){  			
		$result="<select class=\"{$this->getKlasa()}\" name=\"$this->name\" id=\"$this->name\" {$this->getDisabled()}  >";	
		$result.=$this->MakeOptions();
		$result.="</select>";
		return $result;
	}
}

class TMemo extends TControl{
	protected $rows=2;	
	protected $cols=20;	
	public function dumpControl(){  
		
	?>
		<div <?php $this->margina()?> class="control-group " <?php if(!$this->visible){echo 'style="display: none;"';}?>>	
			<label class="control-label" for="<?php echo $this->name;?>"><?php echo $this->label;?></label>  
			<div class="controls">
				<textarea class="<?php echo $this->getKlasa();?>" name="<?php echo $this->name;?>" id="<?php echo $this->name;?>" rows="<?php echo $this->rows;?>" cols="<?php echo $this->cols;?>"   <?php echo $this->getDisabled();?>><?php echo htmlspecialchars($this->value);?></textarea>
			</div>					
  		</div>
	<?php
		
	}
}

class TFile extends TControl{	
	public function __construct($name='',$data=array()){
		parent::__construct($name,$data);			
		$this->type="file";		
	}
	public function dumpControl(){  
		
	?>
		<div <?php $this->margina()?> class="control-group " <?php if(!$this->visible){echo 'style="display: none;"';}?>>	
			<label class="control-label" for="<?php echo $this->name;?>"><?php echo $this->label;?></label>  
			<div class="controls">
				<input class="<?php echo $this->getKlasa();?>" name="<?php echo $this->name;?>" id="<?php echo $this->name;?>" type="<?php echo $this->type;?>" maxlength="<?php echo $this->max_size;?>" placeholder="<?php echo $this->placeholder;?>"  value="<?php echo $this->value;?>" <?php echo $this->getDisabled();?>>		
			</div>					
  		</div>
	<?php
		
	}
}

class TImage extends TControl{	
	
	public function dumpControl(){  
		if($this->visible && !empty($this->value)){
	?>
		<div <?php $this->margina()?> class="control-group " <?php if(!$this->visible){echo 'style="display: none;"';}?>>	
			<label class="control-label" for="<?php echo $this->name;?>"><?php echo $this->label;?></label>  
			<div class="controls">
				<img src="<?php echo $this->value; ?>">
			</div>					
  		</div>
	<?php
		}
	}
}
?>