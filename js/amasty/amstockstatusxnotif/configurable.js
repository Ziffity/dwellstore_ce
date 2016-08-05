/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2012 Amasty (http://www.amasty.com)
* @package Amasty_Stockstatus Amasty_Xnotif
*/

currentProductId = 0;
defaultProductId = 0;

StockStatus = Class.create();

StockStatus.prototype = 
{
    options : null,
    configurableStatus : null,
    
    initialize : function(options)
    {
        this.options = options;
    },
    
    showStockAlert: function(code)
    {
        //var beforeNode = $('ocb').childElements()[$('ocb').childElements().length-1];
        var div = document.createElement('div');
        div.id  = 'amstockstatus-stockalert'; 
        div.innerHTML = code;

        // DWELL - moving subscribe block to after options
        jQuery('#atc-pob').prepend(div);
        $$('.product-options p.required').each(function(required) {
                    required.style.position = 'relative';
                    required.style.top = '0px';
        }.bind(this));
    },
    
    hideStockAlert: function()
    {
      if ($('amstockstatus-stockalert'))
        {
          $('amstockstatus-stockalert').remove();
        }
    },
    
    onConfigure : function(key, settings)
    {   
        this.hideStockAlert();
        this._removeStockStatus();
        if (null == this.configurableStatus && $$('p.availability span')[0])
        {
            this.configurableStatus = $$('p.availability span')[0].innerHTML;
        }
        if ('undefined' != typeof(this.options[key]))
        {
            if ('undefined' != typeof(changeConfigurableStatus) && changeConfigurableStatus && $$('p.availability span')[0])
            {
                if (this.options[key]['custom_status'])
                {
                  if(this.options[key]['custom_status_icon_only'] == 1){
                    $$('p.availability span')[0].innerHTML = this.options[key]['custom_status_icon'];
                  }
                  else{
                    $$('p.availability span')[0].innerHTML =  this.options[key]['custom_status'] + this.options[key]['custom_status_icon'];
                  }
                
                }
                else
                {
                    $$('p.availability span')[0].innerHTML = this.configurableStatus;
                }
            }
            if (this.options[key]['custom_status'])
            {
                $$('.product-options-bottom .price-box').each(function(pricebox) {
                    span = document.createElement('span');
                    span.id = 'amstockstatus-status';
                    span.style.paddingLeft = '10px';
                    span.innerHTML = this.options[key]['custom_status'];
                    pricebox.appendChild(span);
                }.bind(this));
            }
            if (0 == this.options[key]['is_in_stock'])
            {
                $$('p.availability, .add-to-cart label, .add-to-cart input, .add-to-cart #add_to_cart').each(function(elem) {
                    elem.hide();
                });
                if ($('registry_button')) {
                  Element.addClassName('registry_button','wishlist-atc');
                } else { 
                  Element.addClassName('wish_list_button','wishlist-atc');
                }
                if (this.options[key]['stockalert'])
                {
                  this.showStockAlert(this.options[key]['stockalert']);
                }
            } else 
            {
                $$('p.availability, .add-to-cart label, .add-to-cart input, .add-to-cart #add_to_cart').each(function(elem) {
                    elem.show();
                });
                if ($('registry_button')) {
	                Element.removeClassName('registry_button','wishlist-atc');
	            } else { 
	            	Element.removeClassName('wish_list_button','wishlist-atc');
	            }
            }
            if (this.options[key]['product_id'])
            {
                currentProductId = this.options[key]['product_id'];
            } else
            {
                currentProductId = 0;
            }
        } else 
        {
	    if($$('p.availability span')[0])
            	$$('p.availability span')[0].innerHTML = this.configurableStatus;
            $$('.add-to-cart label, .add-to-cart input, .add-to-cart #add_to_cart').each(function(elem) {
                elem.show();
            });
             if ($('registry_button')) {
               Element.removeClassName('registry_button','wishlist-atc');
             } else { 
               Element.removeClassName('wish_list_button','wishlist-atc');
            }
            currentProductId = 0;
        }

        keyParts = explode(',', key);
        if ("" == keyParts[keyParts.length-1]) // this means we have something like "28," - the last element is empty - config is not finished
        {
            needConcat  = true;
            selectIndex = keyParts.length-1;
        } else 
        {
            needConcat  = false;
            selectIndex = keyParts.length;
        }
        // now searching if we have any option to which we should add custom status
        for (i = 0; i < settings.length; i++)
        {
            if (i == keyParts.length-1)
            {
                for (x = 0; x < settings[i].options.length; x++)
                {
                    if (needConcat)
                    {
                        keyCheck = key + settings[i].options[x].value;
                    } else 
                    {
                        keyCheckParts = explode(',', key);
                        keyCheckParts[keyCheckParts.length-1] = settings[i].options[x].value;
                        keyCheck = implode(',', keyCheckParts);
                    }
                    if ('undefined' != typeof(this.options[keyCheck]))
                    {
                        if (0 == this.options[keyCheck]['is_in_stock'])
                        {
                            if ('undefined' != typeof(confData))
                            {
                                var imageId = 'amconf-image-' + settings[i].options[x].value;
                                if ($(imageId))
                                {
                                    $(imageId).addClassName('amconf-image-outofstock');
                                }
                            }
                        }
                        
                        if (this.options[keyCheck]['custom_status'])
                        {
                            if (!strpos(settings[i].options[x].text, this.options[keyCheck]['custom_status']))
                            {
                                settings[i].options[x].text = settings[i].options[x].text + ' (' + this.options[keyCheck]['custom_status'] + ')';
                            }
                        }
                    }
                }
            }
        }
        
        if ($('registry_button')) {
          this.getRegistryUrl();
        }
    },
    
    _removeStockStatus : function()
    {
        if ($('amstockstatus-status'))
        {
            $('amstockstatus-status').remove();
        }
    },
    
    getRegistryUrl: function() {
       path = document.location.href + "?";
       count = 0;
       jQuery('.super-attribute-select').each(function() {
         if (count > 0) {
           path += '&';
          }
          path += 'a_' + parseInt(jQuery(this).attr('id').substring(9)) + '=' + jQuery(this).val();
          count++;
        });
        $target = jQuery('#registry-url');
        $target.html(path);
      }
};

Product.Config.prototype.configure = function(event){
    var element = Event.element(event);
    this.configureElement(element);
    var key = '';
    this.settings.each(function(select){
        // will check if we need to reload product information when the first attribute selected
        if (!select.value && 'undefined' != typeof(confData) && confData.oneAttributeReload && "undefined" != select.options[1] && !confData.isResetButton)
        {
            // if option is not selected, and setting is set to "Yes", will consider it as if the first attribute was selected (0 - is "Choose ...")
            key += select.options[1].value + ',';
        } else 
        {
            key += select.value + ',';
        }
    });
    key = key.substr(0, key.length - 1);
    stStatus.onConfigure(key, this.settings);
};

Product.Config.prototype.loadStatus = function()
{
    var key = '';
    stStatus.onConfigure(key, this.settings);
}

 Product.Config.prototype.configureElement = function(element) {
        this.reloadOptionLabels(element);
        if(element.value){
            this.state[element.config.id] = element.value;
            if(element.nextSetting){
                element.nextSetting.disabled = false;
                this.fillSelect(element.nextSetting);
                this.resetChildren(element.nextSetting);
            }
        }
        else {
            this.resetChildren(element);
        }
        this.reloadPrice();
        
        //Amasty code for Automatically select attributes that have one single value
        if(('undefined' != typeof(amConfAutoSelectAttribute) && amConfAutoSelectAttribute) ||('undefined' != typeof(amStAutoSelectAttribute) && amStAutoSelectAttribute)){
            var nextSet = element.nextSetting;
            if(nextSet && nextSet.options.length == 2 && !nextSet.options[1].selected && element && !element.options[0].selected){
                nextSet.options[1].selected = true;
                this.configureElement(nextSet);
            }     
        }
 }

Product.Config.prototype.configureForValues =  function () {
        if (this.values) {
            this.settings.each(function(element){
                var attributeId = element.attributeId;
                element.value = (typeof(this.values[attributeId]) == 'undefined')? '' : this.values[attributeId];
                this.configureElement(element);
            }.bind(this));
        }
        //Amasty code for Automatically select attributes that have one single value
         if(('undefined' != typeof(amConfAutoSelectAttribute) && amConfAutoSelectAttribute) ||('undefined' != typeof(amStAutoSelectAttribute) && amStAutoSelectAttribute)){
            var select  = this.settings[0];
            if(select && select.options.length == 2 && !select.options[1].selected){
                select.options[1].selected = true;
                this.configureElement(select);
            }
         }
}

function explode (delimiter, string, limit) 
{
    var emptyArray = { 0: '' };
    
    // third argument is not required
    if ( arguments.length < 2 ||
        typeof arguments[0] == 'undefined' ||
        typeof arguments[1] == 'undefined' )
    {
        return null;
    }
 
    if ( delimiter === '' ||
        delimiter === false ||
        delimiter === null )
    {
        return false;
    }
 
    if ( typeof delimiter == 'function' ||
        typeof delimiter == 'object' ||
        typeof string == 'function' ||
        typeof string == 'object' )
    {
        return emptyArray;
    }
 
    if ( delimiter === true ) {
        delimiter = '1';
    }
    
    if (!limit) {
        return string.toString().split(delimiter.toString());
    } else {
        // support for limit argument
        var splitted = string.toString().split(delimiter.toString());
        var partA = splitted.splice(0, limit - 1);
        var partB = splitted.join(delimiter.toString());
        partA.push(partB);
        return partA;
    }
}

function implode (glue, pieces) {
    var i = '', retVal='', tGlue='';
    if (arguments.length === 1) {
        pieces = glue;
        glue = '';
    }
    if (typeof(pieces) === 'object') {
        if (pieces instanceof Array) {
            return pieces.join(glue);
        }
        else {
            for (i in pieces) {
                retVal += tGlue + pieces[i];
                tGlue = glue;
            }
            return retVal;
        }
    }
    else {
        return pieces;
    }
}

function strpos (haystack, needle, offset) 
{
    var i = (haystack+'').indexOf(needle, (offset ? offset : 0));
    return i === -1 ? false : i;
}

Event.observe(window, 'load', function(){
    defaultProductId = document.getElementsByName('product')[0].value;
});
    function send_alert_email(url, button)
    {
        var f = $('product_addtocart_form');
		var productId = button.id.replace(/\D+/g,"");
		if($('amxnotif_guest_email-' + productId)){
			$('amxnotif_guest_email-' + productId).addClassName("validate-email required-entry");
		}
        var validator = new Validation(f);
        if (validator.validate()) {
            f.action = url;
            f.id = 'am_product_addtocart_form';
            f.submit();
            button.disable();
            return true;  
        }
        button.style.position = 'relative';
        button.style.top = '-50px';
        button.style.left = '180px';
		if($('amxnotif_guest_email-' + productId)){
			$('amxnotif_guest_email-' + productId).removeClassName("validate-email required-entry");
		}
        return false;
    }
    
    function checkIt(evt,url, button) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;  
        if (charCode == 13) {
               return send_alert_email(url, button);
        }
        return true;
    }
    
    function send_email(formId) { 
		var form = $(formId);
		var validator = new Validation(form);
		if (validator.validate()) {
			$(formId).submit();
			$('button-alert-email').remove();
			return true;
		}
		$$('.product-options p.required').each(function(required) {
			required.style.position = 'relative';
			required.style.top = '0px';
		}.bind(this));
		/*$('button-alert-email').style.position = 'relative';
		$('button-alert-email').style.top = '-50px';
		$('button-alert-email').style.left = '200px';*/
		return false;
	}