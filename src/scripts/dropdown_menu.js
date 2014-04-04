$(document).ready(function(){
  // Dropdown for the main sidebar.
  $('a[data-dropdown="action-bttn-primary"]').mouseenter(function() {
    var $self = $(this);
    var $dropdown = $self.siblings('.action-dropdown-primary');
    clearTimeout($dropdown.data('hide_timeout'));
    // Hide others.
    $('.action-dropdown-primary').not($dropdown).removeClass('revealed');
    
    $dropdown.addClass('revealed');
  })
  .mouseleave(function() {
    var $self = $(this);
    var $dropdown = $self.siblings('.action-dropdown-primary');
    
    var hide_timeout = setTimeout(function() {
      $dropdown.removeClass('revealed');
    }, 150);
    
    $dropdown.data('hide_timeout', hide_timeout);
  })
  .click(function() {
    window.location = $(this).attr('href');
  });
  
  $('.action-dropdown-primary').mouseenter(function() {
    var $self = $(this);
    clearTimeout($self.data('hide_timeout'));
    $self.addClass('revealed');
  })
  .mouseleave(function() {
    var $self = $(this);
    var hide_timeout = setTimeout(function() {
      $self.removeClass('revealed');
    }, 150);
    $self.data('hide_timeout', hide_timeout);
  });
  
  // Other dropdowns triggered by buttons.
  $('a[data-dropdown="action-bttn"]').click(function(event) {
    event.stopPropagation();
    event.preventDefault();
    
    var $self = $(this);
    var $dropdown = $self.siblings('.action-dropdown');
    
    // Hide others.
    $('.action-dropdown').not($dropdown).removeClass('revealed');
    $('a[data-dropdown="action-bttn"]').not($self).removeClass('current');
    
    $self.toggleClass('current');
    $dropdown.toggleClass('revealed');
  });

  $(document).click(function() {
    $('.action-dropdown').removeClass('revealed');
    $('a[data-dropdown="action-bttn"]').removeClass('current');
  });

});