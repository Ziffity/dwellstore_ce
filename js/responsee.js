/*
 * Responsee JS - v2.2 - 2015-03-08
 * http://www.myresponsee.com
 * Copyright 2015, Vision Design - graphic zoo
 * Free to use under the MIT license.
*/
var temp=null;
window.onresize = function(event) {
windowhandle();
}
function windowhandle(){

    if (jQuery(window).width() <= 800){
	if(temp==null)
		temp=1;
	else
		return;
jQuery(document).ready(function($) {

	jQuery("#search-form").hide();  	
			jQuery(".form-search-inner img").click(function(){
		   jQuery("#search-form").slideToggle( 'slow');
			 });
			 
  //Responsee nav 
    
  $('.top-nav > ul li:has(ul)').addClass('submenu');
  $('.top-nav > ul ul li:has(ul)').addClass('sub-submenu');
  $('.top-nav > ul ul li:has(ul)').removeClass('submenu');
  $('.top-nav > ul li.submenu .droparrow').click(function() { 
	if($(this).parent().parent().hasClass("expanded")){
	$('.submenu').removeClass( "expanded");
	$('ul.show-ul').hide();
	return;
	}
	
	
	
	$('.submenu').removeClass( "expanded");
	$('ul.show-ul').hide();
    //$('.top-nav > ul li.submenu:hover > ul').toggleClass('show-ul', 'slow'); 
	//if(temp!=null && pre!=null && pre!=this)
	//	temp.hide();
	//if(pre!=null && pre!=this)
	//	$('ul li.submenu').removeClass( "expanded");
	//temp=$('.top-nav > ul li.submenu:hover > ul.show-ul');
    //temp.toggle('slow');
	$(this).parent().siblings(0).show();
	$(this).parent().parent().toggleClass("expanded");
	 
		$('html,body').animate({
        scrollTop: $(this).parent().parent().offset().top},
        'slow');
		
  }); 
  $('.top-nav > ul ul > li.sub-submenu .droparrow').click(function() { 
    $('.top-nav ul ul li:hover > ul.show-ul').toggle('slow');   
  });
});  
}
}
