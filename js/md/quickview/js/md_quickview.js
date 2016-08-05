	/*
	*
	*
	*/
jQuery.noConflict();
jQuery(function($) {
	var myhref,qsbtt;

	// base function
	
	//get IE version
	function ieVersion(){
		var rv = -1; // Return value assumes failure.
		if (navigator.appName == 'Microsoft Internet Explorer'){
			var ua = navigator.userAgent;
			var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
			if (re.exec(ua) != null)
				rv = parseFloat( RegExp.$1 );
		}
		return rv;
	}

	//read href attr in a tag
	function readHref(){
		var mypath = arguments[0];
		var patt = /\/[^\/]{0,}$/ig;
		if(mypath[mypath.length-1]=="/"){
			mypath = mypath.substring(0,mypath.length-1);
			return (mypath.match(patt)+"/");
		}
		return mypath.match(patt);
	}


	//string trim
	function strTrim(){
		return arguments[0].replace(/^\s+|\s+$/g,"");
	}

	function _qsJnit(){
	

		
		var selectorObj = arguments[0];
			//selector chon tat ca cac li chua san pham tren luoi
		var listprod = $(selectorObj.itemClass);

		var qsImg;
		var mypath = 'quickview/index/view';
		var baseUrl = EM.Quickview.BASE_URL + mypath;

		var _qsHref = "<a id=\"md_quickview_handler\" class=\"md_qv\" href=\"#\" style=\"visibility:hidden;position:absolute;top:0;left:0\">Quick view</a>";
		$(document.body).append(_qsHref);
		
		$.each(listprod, function(index, value) { 
			var reloadurl = baseUrl;
			
			//get reload url
			myhref = $(value).children(selectorObj.aClass );

			var prodHref = readHref(myhref.attr('href'))[0];
		//	prodHref[0] == "\/" ? prodHref = prodHref.substring(1,prodHref.length) : prodHref;
		//	prodHref=strTrim(prodHref);
			
			reloadurl = baseUrl+"/path"+prodHref;	
			version = ieVersion();	
			if(version < 8.0 && version > -1){
				reloadurl = baseUrl+"/path"+prodHref;
			}
			//end reload url

			
			$(selectorObj.imgClass, this).bind('mouseover', function() {
				var o = $(this).offset();
				$('#md_quickview_handler').attr('href',reloadurl).show()
					.css({
						'top': o.top+($(this).height()/2)-20+'px',
						'left': o.left+($(this).width()/2)-60+'px',
						'visibility': 'visible'
					});
			});
			$(value).bind('mouseout', function() {
				$('#md_quickview_handler').hide();
			});
		});

		//fix bug image disapper when hover
		$('#md_quickview_handler')
			.bind('mouseover', function() {
				$(this).show();
			})
			.bind('click', function() {
				$(this).hide();
			});
		//insert quickview popup

		$('#md_quickview_handler').fancybox({
				'titleShow'			: false,
				'width'				: EM.Quickview.QS_FRM_WIDTH,
				'height'			: 'auto',//EM.Quickview.QS_FRM_HEIGHT,
				'autoScale'			: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'autoDimensions'	: false,
				'scrolling'     	: 'no',
				'padding' 			:0,
  				'margin'			:0,
				'type'				: 'ajax',
				'overlayColor'		: EM.Quickview.OVERLAYCOLOR
				
		});


	
	
	}

	//end base function

	if (!$('body').hasClass('catalog-product-view')) {
		_qsJnit({
			itemClass : '.products-grid li.item', //selector for each items in catalog product list,use to insert quickview image
			aClass : 'a.product-image', //selector for each a tag in product items,give us href for one product
			imgClass: '.product-image img' //class for quickview href product-collateral
		});
	}
});


