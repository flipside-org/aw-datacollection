$(document).ready(function() {
  $(".chosen-select").chosen();
  
  $(".chosen-select").on('change', function(evt, params) {
    if (typeof params.selected != 'undefined') {
      var action = 'assign';
      var uid = params.selected;
    }
    else if (typeof params.deselected != 'undefined') {
      var action = 'unassign';
      var uid = params.deselected;
    }
    else {
      // Nothing to do here.
      return false;
    }
    
    
    console.log(action);
    console.log(uid);
    // To unselect
    /*
      console.log('fire!');
      $(".chosen-select option[value=" + uid + "]").prop("selected", false);
      $(".chosen-select").trigger('chosen:updated');
    // To unselect */
    
  });
});
