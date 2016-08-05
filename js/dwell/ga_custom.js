jQuery.noConflict();
jQuery(document).ready(function() {
	/* 
		Quick view link handler
	*/
	if (jQuery('.md_quickview_handler').length >0) {
		jQuery('.md_quickview_handler').click(function(){
			product_name=jQuery(this).parent('div').parent('div').parent('li').children('h2.product-name').children('a').html();
			dwell_ga.event('Product Category','Quick Look Click',product_name);
			dwell_ga.page(this.href);
		});
	}
	
	/* 
		Home page banner handler
	*/
	if (jQuery(".slider1-1").length > 0) {
		jQuery(".slider1-1").bind("sliderChange", function(event, curSlide) {
   			s = jQuery(curSlide);
   			dwell_ga.event('Homepage Slider','Slide',s.children('a').children('img').attr('src'));
   			dwell_ga.page(s.children('a').attr('href'));
  		}).click(function() {
  			slider_pth = jQuery(this).children('.jquery-slider-slide-current').children('a').attr('href');
  			dwell_ga.event('Homepage Slider','Click',slider_pth);
  		});
  	} 
  	
  	/* 
  		Category featured product handler - adding element id with pos for easy targeting
  	*/
  	if (jQuery('#cat_fp').length > 0) {
  		jQuery('.products-grid li .product-image a:not(.md_quickview_handler)').click(function() {
  			// add closest
  			pos = jQuery(this).parent('div').parent('li').attr('id').substring(3);
  			loc = document.location.href.split('/');
  			cat = loc[loc.length-1].substring(0,loc[loc.length-1].indexOf('.'));
			dwell_ga.event('Product Category Featured Product','Click ',cat+':'+pos);
  		});
  		jQuery('.products-grid li .product-name a').click(function() {
  			// add closest 
  			pos = jQuery(this).parent('h2').parent('li').attr('id').substring(3);
  			loc = document.location.href.split('/');
  			cat = loc[loc.length-1].substring(0,loc[loc.length-1].indexOf('.'));
			dwell_ga.event('Product Category Featured Product','Click',cat+':'+pos);  		
		});
  	}
  	
  	/*
  		PDP event handlers - swatches, attribute selects, image carousel, related products
  	*/
  	
  	if (jQuery('body').hasClass('catalog-product-view')) {
  		if (jQuery('.amconf-image').length > 0) {
  			jQuery('.amconf-image').click(function() {
  				dwell_ga.event('Product Details Page','Swatch Click','Color:'+this.title);
  				dwell_ga.page(document.location.href);
  			});
  		};
  		if (jQuery('.super-attribute-select').length > 0) {
  			jQuery('.super-attribute-select').change(function() {
  				dwell_ga.event('Product Details Page','Attribute Change',spConfig.config.attributes[this.id.substring(9)].label+':'+this.options[this.selectedIndex].label);
  				dwell_ga.page(document.location.href);
  			});
  		}
  		if (jQuery('#amasty_gallery').length>0) {
  			jQuery('#amasty_gallery a img').click(function() {
  				dwell_ga.event('Product Details Page','Thumbnail Click',this.src);
  				dwell_ga.page(document.location.href);
  			});
  		}
  		if (jQuery('.box-up-sell').length > 0) {
  			jQuery('.box-up-sell ul.products-grid li .item-info').click(function() {
  				// add closest
  				pos = jQuery(this).parent('li').attr('id').substring(3);
				dwell_ga.event('Product Details Page','Related Product Click',pos);
			});
		}
		
		jQuery('#wrapper').mouseenter(function() {
			dwell_ga.event('Product Details Page','Image Zoom',document.location.href);
		});
  		
  		
  	}
});


var dwell_ga = {
	event: function(cat,action,val) {
		_gaq.push(['_trackEvent',cat,action,val]);
	},
	page: function(path) {
		if (path.substring(0,4) == 'http') {
			s = path.split("/");
			rel_path ='';
			for(p=3;p<s.length;p++) {
				rel_path += s[p]+'/';
			}
			path = rel_path.substring(0,rel_path.length-1);
		}
		_gaq.push(['_trackPageview',path]);
	},
	rebindQuickView: function() {
		if (jQuery('.amconf-image').length > 0) {
  			jQuery('.amconf-image').click(function() {
  				dwell_ga.event('Quick Look','Swatch Click','Color:'+this.title);
  				dwell_ga.page(document.location.href);
  			});
  		};
  		if (jQuery('.super-attribute-select').length > 0) {
  			jQuery('.super-attribute-select').change(function() {
  				dwell_ga.event('Quick Look','Attribute Change',spConfig.config.attributes[this.id.substring(9)].label+':'+this.options[this.selectedIndex].label);
  				dwell_ga.page(document.location.href);
  			});
  		}
  		if (jQuery('ul.pagination').length>0) {
  			jQuery('ul.pagination li a img').click(function() {
  				dwell_ga.event('Quick Look','Thumbnail Click',this.alt);
  				dwell_ga.page(document.location.href);
  			});
  		}
  		jQuery('.slides_container').mouseenter(function() {
			dwell_ga.event('Quick Look','Image Zoom',jQuery('.product-name h1').html());
		});
	
	}
}