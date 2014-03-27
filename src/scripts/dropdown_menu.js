$(document).ready(function(){  
  // Dropdown for the main sidebar.
  $('a[data-dropdown="action-bttn-primary"]').mouseenter(function() {
    var $self = $(this);
    var $dropdown = $self.siblings('.action-dropdown-primary');
    clearTimeout($dropdown.data('hide_timeout'));
    // Hide others.
    $('.action-dropdown-primary').not($dropdown).hide();
    
    $dropdown.show();
  })
  .mouseleave(function() {
    var $self = $(this);
    var $dropdown = $self.siblings('.action-dropdown-primary');
    
    var hide_timeout = setTimeout(function() {
      $dropdown.hide();
    }, 150);
    
    $dropdown.data('hide_timeout', hide_timeout);
  });
  
  $('.action-dropdown-primary').mouseenter(function() {
    var $self = $(this);
    clearTimeout($self.data('hide_timeout'));
    $self.show();
  })
  .mouseleave(function() {
    var $self = $(this);
    var hide_timeout = setTimeout(function() {
      $self.hide(); 
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
    $('.action-dropdown').not($dropdown).hide();
    $('a[data-dropdown="action-bttn"]').not($self).removeClass('current');
    
    if ($dropdown.is(':hidden')) {
      $self.addClass('current');
      $dropdown.show();
    }
    else {
      $self.removeClass('current');
      $dropdown.hide();
    }
  });
  
  $(document).click(function() {
    $('.action-dropdown').hide();
    $('a[data-dropdown="action-bttn"]').removeClass('current');
  });
  
});