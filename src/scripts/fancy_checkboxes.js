$(document).ready(function() {
  
  var check_label = function(checkbox) {
    var $label = $(checkbox).parent('.fancy-cb-label');
    
    if ($(checkbox).is(':checked')) {
      $label.addClass('fancy-cb-on');
    }
    else {
      $label.removeClass('fancy-cb-on');
    }
  };
  
  // Setup listener and check existing checkboxes.
  $('.fancy-cb-label input[type=checkbox]').change(function() {
    check_label(this);
  }).each(function(i, obj){
    check_label(obj);
  });
  
  // Click listener for the label;
  $('.fancy-cb-label').not('.cb-master-label').click(function() {
    // Check the checkbox.
    // The label is checked by the trigger.
    var $checkbox = $(this).find('input[type=checkbox]');
    //$checkbox.click();
    $checkbox.prop('checked', !$checkbox.prop('checked'));
    $checkbox.trigger('change');
  });
  
  // Master and slaves.
  // Clicking the master selects the slaves.
  $('.cb-master-label').click(function() {
    var $cb_master_label = $(this);
    var $cb_master = $cb_master_label.find('.cb-master');
    var $cb_group = $cb_master_label.parents('.fancy-cb-group');
    var cb_slaves_labels = $cb_group.find('.cb-slave-label');
    
    if (typeof $cb_master_label.data('status') == 'undefined') {
      // -1 - all unchecked
      // 0 - some checked
      // 1 - all on this page
      // 2 - all
      $cb_master_label.data('status', -1);
    }
    var master_status = $cb_master_label.data('status');
    var master_status_new = null;
    
    // Next status.
    switch (master_status) {
      case -1:
        master_status_new = 1;
        // Select all the slaves.
        cb_slaves_labels.find('.cb-slave').prop('checked', true).trigger('change');
        
        $cb_master_label.removeClass('cb-master-none cb-master-some cb-master-all').addClass('cb-master-page');
        // We only want the master checkbox selected when all is selected.
        $cb_master.prop('checked', false);
      break;
      case 0:
        master_status_new = -1;
        // Unselect all the slaves
        cb_slaves_labels.find('.cb-slave').prop('checked', false).trigger('change');
        
        $cb_master_label.removeClass('cb-master-some cb-master-page cb-master-all').addClass('cb-master-none');
        // We only want the master checkbox selected when all is selected.
        $cb_master.prop('checked', false);
      break;
      case 1:
        master_status_new = 2;
        // Select all the slaves.
        cb_slaves_labels.find('.cb-slave').prop('checked', true).trigger('change');
        
        $cb_master_label.removeClass('cb-master-none cb-master-some cb-master-page').addClass('cb-master-all');
        // We only want the master checkbox selected when all is selected.
        $cb_master.prop('checked', true);
      break;
      case 2:
        master_status_new = -1;
        cb_slaves_labels.find('.cb-slave').prop('checked', false).trigger('change');
        
        $cb_master_label.removeClass('cb-master-some cb-master-page cb-master-all').addClass('cb-master-none');
        // We only want the master checkbox selected when all is selected.
        $cb_master.prop('checked', false);
      break;
    }
    
    $cb_master_label.data('status', master_status_new);

  });
  
  // Master and slaves.
  // Clicking the slaves checks the master.
  $('.cb-slave-label').click(function() {
    var $self = $(this);
    //The slave's master
    $cb_group = $self.parents('.fancy-cb-group');
    $cb_master_label = $cb_group.find('.cb-master-label');
    
    // From the moment a slave is clicked the master should be set to an
    // intermediate state.
    $cb_master_label
    .removeClass('cb-master-none cb-master-page cb-master-all')
    .addClass('cb-master-some')
    .data('status', 0);
    // On the intermediate status the checkbox is not selected.
    $cb_master_label.find('.cb-master').prop('checked', false);
    
    // Being checked.
    if ($self.hasClass('fancy-cb-on')) {
      // If all the other slaves are selected, re-select the master.
      var all_selected_slaves = $cb_group.find('.cb-slave-label.fancy-cb-on').length;
      var all_slaves = $cb_group.find('.cb-slave-label').length;
      
      if (all_slaves == all_selected_slaves) {
        $cb_master_label
          .removeClass('cb-master-none cb-master-some cb-master-all')
          .addClass('cb-master-page')
          .data('status', 1);
      }
    }
  });
});