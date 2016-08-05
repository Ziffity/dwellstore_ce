$jsq=jQuery.noConflict();

var EsNewsSubscribers = {

    cookieLifeTime: 100,

    cookieName: 'es_newssubscriber',

    baseUrl: '',

    setCookieLifeTime: function(value)
    {
        this.cookieLifeTime = value;
    },

    setCookieName: function(value)
    {
        this.cookieName = value;
    },

    setBaseUrl: function(url)
    {
	
        this.baseUrl = url;
    },

    getBaseUrl: function(url)
    {

        return this.baseUrl;
    },
    createCookie: function() {
        var days = this.cookieLifeTime;
        var value = 1;
        var name = this.cookieName;
        if (days) {
            var date = new Date();
            date.setTime(date.getTime()+(days*24*60*60*1000));
            var expires = "; expires="+date.toGMTString();
        }
        else var expires = "";
        document.cookie = escape(name)+"="+escape(value)+expires+"; path=/";
    },

    readCookie: function(name) {
        var name = this.cookieName;
        var nameEQ = escape(name) + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return unescape(c.substring(nameEQ.length,c.length));
        }
        return null;
    },

    boxClose: function()
    {
        $jsq('#esns_background_layer').fadeOut();
    },

    boxOpen: function()
    {
        $jsq('#esns_background_layer').fadeIn();
    }
}

var DwellNewsSubscribers = {
 	context: $jsq('#esns_background_layer'),
 	successMsg: 'Your 15% off promo code will arrive in your inbox very soon. <br /> <br />Hotmail users: add mail@dwellmedia.com to your address book for white listing.',
 	subscribedMsg: 'You\'re already on our list, so you\'ll continue to hear first about our new products and special offers.',
 	updatedCss: {
 	  'font-size': '20px',
 	  'line-height': '22px'
 	},
	displayError: function() {
	 $jsq('.dwell_header').html('Error').css({'color':'#353434'});
	  $jsq('.dwell_body').html("Invalid email address").css(this.updatedCss);

	},
	displaySuccess: function() {
	  $jsq('.dwell_header').css({'color':'#353434'}).html('Thank you.');
	  $jsq('.dwell_body').html(this.successMsg).css({
 	    'font-size': '17px',
 	    'line-height': '22px'
 	  });
	  $jsq('#esns_box_subscribe, .dwell_addtl').remove();
	  $jsq('.dwell_shop').show();
	},
	displaySubscribed: function() {
	  $jsq('.dwell_header').html('Hello again.').css({'color':'#353434'});
	  $jsq('.dwell_body').html(this.subscribedMsg).css(this.updatedCss);
	  $jsq('#esns_box_subscribe, .dwell_addtl').remove();
	  $jsq('.dwell_shop').show();
	},
	validPage: function() {
	  if ($jsq('body').hasClass('catalog-category-view') || $jsq('body').hasClass('cms-home')) {
	    return true;
	  }
	  return false;
	}
}

$jsq(function() {
    if (!DwellNewsSubscribers.validPage()) {
    	return;
    }
    if (EsNewsSubscribers.readCookie() != 1) {
        EsNewsSubscribers.createCookie();
        EsNewsSubscribers.boxOpen();
    }
    $jsq('#esns_background_layer').css('height', $jsq(document).height()+'px');
    $jsq('#esns_box_layer').css('margin-top', (($jsq(window).height()-$jsq('#esns_box_layer').height()) /2)+'px');
    $jsq('#esns_submit').click(function(){
        var email = $jsq('#esns_email').val();
        if (email == '') {
            $jsq('#esns_box_subscribe_response_error').html('This is a required field.');
            return false;
        }
        $jsq.post(EsNewsSubscribers.getBaseUrl()+'newsletter/subscriber/newajax/', {'email':email}, function(resp) {
            var response = $jsq.parseJSON(resp);
            if (response['errorMsg']) {
                if (response['errorMsg'] == 'There was a problem with the subscription: This address is already subscribed.' || response['errorMsg'] == 'This address is already subscribed.' || 
                    response['errorMsg'] == 'There was a problem with the subscription: This email address is already assigned to another user.') {
                  DwellNewsSubscribers.displaySubscribed();
                 setTimeout('EsNewsSubscribers.boxClose()', 5000);
                } else {
					if(response['errorMsg'] =='There was a problem with the subscription: Please enter a valid email address.')
					DwellNewsSubscribers.displayError();
                }
            } else {
            	DwellNewsSubscribers.displaySuccess();
                /*$jsq('#esns_box_subscribe_response_error').html('');
                $jsq('#esns_box_subscribe_response_success').html(response['successMsg']);
                $jsq('#esns_box_subscribe_form').css('display','none');
                $jsq('#esns_box_subscribe_response_success').css('display','block');
                 */
				 setTimeout('EsNewsSubscribers.boxClose()', 5000);
            }
			
        });
    });
    $jsq('#esns_box_close').click(function(){
        EsNewsSubscribers.boxClose();
    });

});
