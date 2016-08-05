var AmZoomer  = Class.create();
AmZoomer.prototype = ({
    zoomSettings: [],
    generalSettings: [],
    carouselSettings: [],
    lightboxSettings: [],
    
    initialize: function (settings) {
            if(settings['zoom'] || settings['general']) {
                this.zoomSettings = settings['zoom'];
                this.generalSettings = settings['general'];
                this.carouselSettings = settings['carousel'];
                
                settings['lightbox'].afterShow=function () {
                var pageTitle = jQuery(document).find("title").text();
                var customContent =  '<a class="pin-it" target="_blank" href="http://pinterest.com/pin/create/button/?url=' +
                  encodeURIComponent(document.location.href) +'&media=' +encodeURIComponent(this.href) +
                  '&description='+pageTitle+'" class="pin-it-button" count-layout="horizontal">' +
                  '<img border="0" src="http://assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>';
                 jQuery('.fancybox-inner').append(customContent);
                    jQuery(".fancybox-wrap").hover(function() {
                        jQuery(".pin-it").show();
                    }, function() {
                        jQuery(".pin-it").hide();
                    });
                };                
                this.lightboxSettings = settings['lightbox']; 
            }
    },
    
    loadZoom: function() {
        if(this.generalSettings['zoom_enable'] === "1" || this.generalSettings['lightbox_enable'] === "1") {
            jQuery("#amasty_zoom").elevateZoom(this.zoomSettings);
        }        
        //jQuery("#amasty_zoom").data('elevateZoom', this.zoomSettings);/*
        if(this.generalSettings['change_image'] != "0" && $("amasty_zoom") && $("amasty_gallery")) {
            var self = this;
            jQuery("#amasty_gallery a").bind(self.generalSettings['change_image'], function(e) {  
                 // Example of using Active Gallery
                 jQuery('#amasty_gallery a').removeClass('active');
                 jQuery(this).addClass('active'); 
                 var ez =   jQuery('#amasty_zoom').data('elevateZoom');
                 ez.swaptheimage(jQuery(this).attr("data-image"), jQuery(this).attr("data-zoom-image"));
                 if(!self.generalSettings['thumbnail_lignhtbox'] === "1") {
                    return false;   
                 } 
            });
        }
        
        if(this.generalSettings['lightbox_enable'] === "1"  && $("amasty_zoom")) {
            jQuery("#amasty_zoom").bind("click", function(e) {  
                var ez =  jQuery("#amasty_zoom").data('elevateZoom'); 
                jQuery.fancybox(ez.getGalleryList(), AmZoomerObj.lightboxSettings);
                return false;
            });    
        } 
        if(this.generalSettings['thumbnail_lignhtbox'] === "1") {
            jQuery('.fancybox').fancybox(AmZoomerObj.lightboxSettings);    
        }
        this.loadCarousel();
    },
    
    loadCarousel: function() {
        if(this.generalSettings['carousel_enable'] === "1"  && $("amasty_zoom")  && $("amasty_gallery")) {
            jQuery("#amasty_gallery").carouFredSel(this.carouselSettings);    
        }        
    }
    
});

Event.observe(window, 'load', function(){
    if('undefined' != typeof(AmZoomerObj)) {
        AmZoomerObj.loadZoom();
    }
});