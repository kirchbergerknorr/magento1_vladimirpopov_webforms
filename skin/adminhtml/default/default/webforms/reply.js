 Event.observe(window, 'load', function() {
				$('templates_button').onclick = function(){
					if($('quick_template').selectedIndex){
				  new Ajax.Request('{$this->getUrl('*/ticket/ajaxgettplcontent')}id/'+$('quick_template').getValue(),
				  {
					method:'get',
					onSuccess: function(transport){
					  var response = transport.responseText || '';
						insertAtCursor($('content_value'), response)
						
					}
				  });
				  }else{
				alert('".$this->__('Please select template')."')
					}
				}
			
			
				
				function insertAtCursor(myField, myValue) {
				
				if (document.selection) {
					myField.focus();
					sel = document.selection.createRange();
					sel.text = myValue;
				}else if (myField.selectionStart || myField.selectionStart == '0') {
				
				var startPos = myField.selectionStart;
				var endPos = myField.selectionEnd;
				myField.value = myField.value.substring(0, startPos)+ myValue+ myField.value.substring(endPos, myField.value.length);
				} else {
				myField.value += myValue;
				}
} 
 });