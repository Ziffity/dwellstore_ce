/**
 * GoMage.com
 * 
 * GoMage Feed Pro
 * 
 * @category Extension
 * @copyright Copyright (c) 2010-2013 GoMage.com (http://www.gomage.com)
 * @author GoMage.com
 * @license http://www.gomage.com/licensing Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version Release: 3.3
 * @since Class available since Release 3.2
**/

		
	
function gpDoChange(obj){
$('loading-mask').show();
configForm.submit();
}

function  gpGenerate(e){

		var params = {
		'code_id' : e.id
		};	

	$('loading-mask').show();
		var request = new Ajax.Request(gomage_feed_config_base_url, {
			method : 'GET',
			parameters : params,
			loaderArea : false,
			onSuccess : function(transport) {
			 eval('var response = '+transport.responseText);
			 $('loading-mask').hide();
			 if(response.error){
			 alert(response.error);
			 }else{
			 alert(response.success);
			 }
			
				
			}
		});
}


	