jQuery(document).ready(function($) {

  $('#newsletter-validate-detail').bind('submit.dwellNewsletter', function(event) {
    event.preventDefault();
    
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

    context = $('.dwell-footer');
    $('.newsletter_success, .newsletter_error', context).hide();
    email = $(this, context).find('input[name="email"]').val();
    email_address = $(this, context).find('input[name="email_address"]').val();
    if (email_address != '') {
      return false;
    }
    if (email == '') {
      return false;
    }
    if(!email.match(re)) {
     return false;
   }
    $.post($(this, context).attr('action'), {
      'email':email,
      'source': 'Footer'
      }, 
      function(data) {
        if (data.errorMsg != '') {
          $('.newsletter_error', context).show();
        } else {
          $('.newsletter_success', context).show();
        }
      }, 
      'json'
    );
  });
});