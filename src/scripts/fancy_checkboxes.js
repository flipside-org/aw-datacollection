$(document).ready(function() {
  
  var check_label = function(checkbox) {
    var $label = $(checkbox).parent('.label-check');
    
    if ($(checkbox).is(':checked')) {
      $label.addClass('cb-on');
    }
    else {
      $label.removeClass('cb-on');
    }
  };
  
  // Setup listener and check existing checkboxes.
  $('.label-check input[type=checkbox]').change(function() {
    check_label(this);
  }).each(function(i, obj){
    check_label(obj);
  });
  
  // Click listener for the label;
  $('.label-check').click(function() {
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
    var $cb_group = $cb_master_label.parents('.cb-group');
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
    $cb_group = $self.parents('.cb-group');
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
    if ($self.hasClass('cb-on')) {
      // If all the other slaves are selected, re-select the master.
        var all_selected_slaves = $cb_group.find('.cb-slave-label.cb-on').length;
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

/*

var d = document;
var safari = (navigator.userAgent.toLowerCase().indexOf('safari') !== -1) ? true : false;
var gebtn = function(parEl, child) {
  return parEl.getElementsByTagName(child);
};
onload = function() {

  var body = gebtn(d,'body')[0];
  body.className = body.className && body.className !== '' ? body.className + ' has-js' : 'has-js';

  if (!d.getElementById || !d.createTextNode)
    return;
  var ls = gebtn(d, 'label');
  for (var i = 0; i < ls.length; i++) {
    var l = ls[i];
    if (l.className.indexOf('label_') == -1)
      continue;
    var inp = gebtn(l,'input')[0];
    if (l.className == 'label_check') {
      l.className = (safari && inp.checked === true || inp.checked) ? 'label_check cb-on' : 'label_check c_off';
      l.onclick = check_it;
    }
    if (l.className == 'label_radio') {
      l.className = (safari && inp.checked === true || inp.checked) ? 'label_radio r_on' : 'label_radio r_off';
      l.onclick = turn_radio;
    }
  }
};
var check_it = function() {
  var inp = gebtn(this,'input')[0];
  if (this.className == 'label_check c_off' || (!safari && inp.checked)) {
    this.className = 'label_check cb-on';
    if (safari)
      inp.click();
  } else {
    this.className = 'label_check c_off';
    if (safari)
      inp.click();
  }
};
var turn_radio = function() {
  var inp = gebtn(this,'input')[0];
  if (this.className == 'label_radio r_off' || inp.checked) {
    var ls = gebtn(this.parentNode, 'label');
    for (var i = 0; i < ls.length; i++) {
      var l = ls[i];
      if (l.className.indexOf('label_radio') == -1)
        continue;
      l.className = 'label_radio r_off';
    }
    this.className = 'label_radio r_on';
    if (safari)
      inp.click();
  } else {
    this.className = 'label_radio r_off';
    if (safari)
      inp.click();
  }
};
*/