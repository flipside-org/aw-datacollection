$(document).foundation();

$('a.disabled').click(function(e) {
  e.preventDefault();
  e.stopPropagation();
  e.stopImmediatePropagation();
});


// Show shadow when content slides under the page header
$(document).ready(function() {
  var $content = $('#site-body .content');
  var $header = $('#page-head .inner');

  if ($header.length > 0) {
    $content.css('margin-top', $header.outerHeight());
    
    $(document).resize(function() {
      $content.css('margin-top', $header.outerHeight());
    });
    
    $(window).scroll(function() {
      if($(this).scrollTop() > 0) {
        $header.addClass('overlay');
      }
      else {
        $header.removeClass('overlay');
      }
    });
  }
  
  // ================================================================
  // Fake form submits and confirmation buttons.
  //
  // To create a confirmation button add ( data-confirm-action="message" )
  // to the button. This will trigger a confirmation box. If the answer
  // is affirmative the button action will fire.
  //
  // For fake submit buttons add ( data-trigger-submit="id" ) and replace
  // id with the id of the submit button.
  // ================================================================

  $('[data-confirm-action]').click(function(e) {
    var message = $(this).attr('data-confirm-action');
    var confirm = window.confirm(message);
    if (!confirm) {
      e.preventDefault();
      e.stopImmediatePropagation();
    }
  });
  
  $('[data-trigger-submit]').click(function(e) {
    e.preventDefault();
    var id = $(this).attr('data-trigger-submit');
    $('#' + id).click();
  });
});