$(document).ready(function() {
  
  // Mobile menu toggle click handler.
  $('.menu-icon.mobile').click(function(e) {
    e.preventDefault();
    
    var $menu = $('.menu-main');
    if ($menu.is(':hidden')) {
      $menu.slideDown();
    }
    else {
      $menu.slideUp();
    }
  });
  
});