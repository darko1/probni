<?php
        class FormValidator
        {
			private $_name='';
			private $owner='';
            function __construct($aname='',$aowner='')
            {
                $this->_name=$aname;
				$this->owner=$aowner;
            }

            protected $_rules=array();
			protected $_messages=array();

            
			function getRules() { return $this->_rules; }
			function setRules($value) { $this->_rules=$value; }
			function defaultRules() { return array(); }
			
			function getMessages() { return $this->_messages; }
			function setMessages($value) { $this->_messages=$value; }
			function defaultMessages() { return array(); }
            
			function addRule($key,$value) {$this->_rules[$key]=$value; }
			function addMessage($key,$value) {$this->_messages[$key]=$value; }
            

            Function multimail(){
            	foreach($this->_rules as $key=>$rule){
            		foreach($rule as $rulekey=>$rulevalue){
            			if(strtolower($rulekey)=='multiemail'){
            				return true;
            			}
            		}
            	}
            	return false;
            }

            function dumpHeaderCode()
            {
                
                ?>
				<script src="<?php echo RPCL_HTTP_PATH; ?>jquery.js"></script> 
                <script language="javascript" src="<?php echo RPCL_HTTP_PATH; ?>jquery.validate.js"></script>
                
<script language="javascript" type="text/javascript">
<!--
	$(document).ready(function() {	
	var validator = $("#<?php echo $this->owner->name; ?>").validate({
		errorClass:'error',
		validClass:'success',
		errorElement:'span',
		
		
		highlight: function(element, errorClass, validClass) {
			if (element.type === 'radio') {
				this.findByName(element.name).parent("div").parent("div").removeClass(validClass).addClass(errorClass);
			} else {
				if (element.type === 'checkbox') {
					$(element).parent("label").parent("div").parent("div").removeClass(validClass).addClass(errorClass);
				} else {
					$(element).parent("div").parent("div").removeClass(validClass).addClass(errorClass);
				}
			}
		},
		unhighlight: function(element, errorClass, validClass) {
			if (element.type === 'radio') {
				this.findByName(element.name).parent("div").parent("div").removeClass(errorClass).addClass(validClass);
			} else {
				if (element.type === 'checkbox') {
					$(element).parent("label").parent("div").parent("div").removeClass(errorClass).addClass(validClass);
				} else {
					$(element).parent("div").parent("div").removeClass(errorClass).addClass(validClass);
				}
			}
		}
<?php
	
    
	if (!empty($this->_rules)){
		$arules=json_encode($this->_rules);
		
		echo  ",rules:".$arules;
	}
	
	if (!empty($this->_messages)){
		$amessages=json_encode($this->_messages);  //,JSON_FORCE_OBJECT
		echo  ",messages:".$amessages;
	}        
?>
	    
});
});
<?php
	if ($this->multimail()){
    ?>
    	jQuery.validator.addMethod("multiemail",    function(value, element) {
         	if (this.optional(element)) // return true on optional element 
             	return true;
         	var emails = value.split(/[;,]+/); // split element by , and ;
         	valid = true;
         	for (var i in emails) {
             	value = emails[i];
             	valid = valid &&
                    jQuery.validator.methods.email.call(this, $.trim(value), element);
         	}
         	return valid;
     		},
    		jQuery.validator.messages.email
		);
    	
    <?php   	
    }
    ?>
-->
</script>
<?php
            }


        }

?>
