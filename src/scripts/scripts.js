$(document).foundation();

$(document).ready(function(){
  
  // Dropdown for the main sidebar.
  var hide_timeout = null;
  $('a[data-dropdown="action-primary"]').mouseenter(function() {
    clearTimeout(hide_timeout);
    var $self = $(this);
    $self.siblings('.action-dropdown-primary').show();
  })
  .mouseleave(function() {
    var $self = $(this);
    
    hide_timeout = setTimeout(function() {
      $self.siblings('.action-dropdown-primary').hide();
    }, 150);
  });
  
  $('.action-dropdown-primary').mouseenter(function() {
    clearTimeout(hide_timeout);
    var $self = $(this);
    $self.show();
  })
  .mouseleave(function() {
    var $self = $(this);
    hide_timeout = setTimeout(function() {
      $self.hide(); 
    }, 150);
  });
  
  
  
  $('a[data-dropdown="action-bttn"]').click(function(event) {
    event.stopPropagation();
    event.preventDefault();
    
    var $self = $(this);
    var $dropdown = $self.siblings('.action-dropdown');
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
