$(document).foundation();

$('a.disabled').click(function(e) {
  e.preventDefault();
  // Workaround for when links loads with disabled and later is removed.
  if ($(this).hasClass('disabled')) {
    e.stopPropagation();
    e.stopImmediatePropagation();
  }
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
      if ($(this).scrollTop() > 0) {
        $header.addClass('overlay');
      } else {
        $header.removeClass('overlay');
      }
    });
  }
  
  // Expand element.
  $('[data-expand]').click(function(e) {
    e.preventDefault();
    var id = $(this).attr('data-expand');
    $('#' + id).toggleClass('revealed');
  });
});