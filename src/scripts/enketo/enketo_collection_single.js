var always_fail = true;
$(document).ready(function(){
  $('.allow-submission').click(function(e) {
    e.preventDefault();
    always_fail = false;
  });
});

/**
 * Require Js configuration for enketo.
 * Coming from enketo core.
 * TODO: baseUrl should come from server.
 */
requirejs.config({
  baseUrl : Aw.settings.base_url + "assets/libs/enketo-core/lib",
  paths : {
    "enketo-js" : "../src/js",
    "enketo-widget" : "../src/widget",
    "enketo-config" : "../config.json",
    "text" : "text/text",
    "xpath" : "xpath/build/xpathjs_javarosa",
    "file-manager" : "file-manager/src/file-manager",
    "jquery.xpath" : "jquery-xpath/jquery.xpath",
    "jquery.touchswipe" : "jquery-touchswipe/jquery.touchSwipe"
  },
  shim : {
    "xpath" : {
      exports : "XPathJS"
    },
    "bootstrap" : {
      deps : ["jquery"],
      exports : "jQuery.fn.popover"
    },
    "widget/date/bootstrap3-datepicker/js/bootstrap-datepicker" : {
      deps : ["jquery"],
      exports : "jQuery.fn.datepicker"
    },
    "widget/time/bootstrap3-timepicker/js/bootstrap-timepicker" : {
      deps : ["jquery"],
      exports : "jQuery.fn.timepicker"
    },
    "Modernizr" : {
      exports : "Modernizr"
    }
  }
});
/**
 * Little object to track the status of things.
 */
var whatsGoingOn = {
  // Since we are only collection for one number, block after submission.
  finished_collection : false,
  
  // A user submits data and the respondent is added to the end of the
  // submission queue. If the user refreshes the page before the data
  // is submitted we should prevent additional collection.
  already_done : false,
};

/**
 * Connection
 * Variable to hold the connection object.
 */
var con;

// Require needed scripts and start everything.
requirejs(['jquery', 'Modernizr', 'enketo-js/Form'], function($, Modernizr, Form) {
  // Errors when loading the form.
  var loadErrors;
  // Enketo form.
  var form;
  // Submission queue. Instance of SubmissionQueue.
  var submission_queue;
  // Current respondent.
  var current_respondent = Aw.settings.single_call_task;
  
  // Perform request for form. Form will be returned in xml.
  $.get(Connection.URL_XSLT_TRANSFORM, function(response) {
    var xml_form = response['xml_form'];
    
    
    // If the computer is shared by multiple users it may happen that a user
    // submits data left by another user. That data should not be lost so
    // when a user starts data collection the system tries to submit it.
    // If the system replies with a "Submitting another user's data" we store
    // the data in another queue.
    // 
    // Before initializing queues, merge the localstorage. When the submit
    // routing runs the values will be separated again. Bu doing this we ensure
    // a default status to start.
    // Scenario:
    // User A receives numbers [1,2,3,4,5] connection drops, the user collects
    // data for [1,2,3] and goes away.
    // User B starts data collection making the data collected for [1,2,3]
    // shift to the aw_submission_queue_skipped.
    // User A comes back, and since no data was submitted the server sends the
    // same numbers again [1,2,3,4,5]. We need to take the skipped values
    // into account so that the user starts data collection with [4].
    var stored_data = [];
    // Get stored data and convert it to JSON.
    try {
      stored_data = JSON.parse(localStorage.getItem('aw_submission_queue'));
      if (stored_data == null) {
        stored_data = [];
      }
    } catch(e) {}
    
    var stored_data_skipped = [];
    try {
      stored_data_skipped = JSON.parse(localStorage.getItem('aw_submission_queue_skipped'));
      if (stored_data_skipped == null) {
        stored_data_skipped = [];
      }
    } catch(e) {}
    
    stored_data = stored_data.concat(stored_data_skipped);
    localStorage.setItem('aw_submission_queue', JSON.stringify(stored_data));
    // Clean skipped queue.
    localStorage.removeItem('aw_submission_queue_skipped');
    
    
    // Initialize connection. Although this could be initialized before
    // It's only needed it the form and the first batch of numbers
    // are successfully requested.
    con = new Connection();
    // Initialize the submission queue
    submission_queue = new SubmissionQueue();
    
    for(index in submission_queue.queue) {
      if (submission_queue.queue[index].ctid == current_respondent.ctid) {
        whatsGoingOn.already_done = true;
        whatsGoingOn.finished_collection = true;
        alert('Respondent already in submission queue');
        break;
      }
    }
    
    // Enketo form stuff.
    // XML Parser.
    var parser = new DOMParser();
    var $data = parser.parseFromString(xml_form, 'text/xml');
    // Convert to jQuery object to allow find.
    $data = $($data);
    
    formStr = (new XMLSerializer() ).serializeToString($data.find( 'form:eq(0)' )[0]);
    modelStr = (new XMLSerializer() ).serializeToString($data.find( 'model:eq(0)' )[0]);
    
    // Insert form.
    $('#submit-form').before(formStr);
    
    // Initialize form.
    init();
    
    // Proceed with data collection and show the enketo form.
    $('#proceed-collection').click(function(e) {
      e.preventDefault();
      $('.call-actions').addClass('hide');
      $('.enketo-container').removeClass('hide');
    });
    
    // Show the form to set the call status.
    $('#halt-collection').click(function(e) {
      e.preventDefault();
      $('.call-actions').addClass('hide');
      $('.call-status').removeClass('hide');
    });

    // Submit enketo form.
    $('#submit-form').on('click', function() {      
      
      if (whatsGoingOn.finished_collection) {
        alert('Data collection is finished.');
        return false;
      }
      
      console.log('validate-form click event');
      form.validate();
      if (!form.isValid()) {
        alert('Form contains errors. Please see fields marked in red.');
      } else {
          // Form is valid. Get the data.
          current_respondent.form_data = form.getDataStr();
          // Add the respondent to the submission queue.
          submission_queue.add(current_respondent);
          
          whatsGoingOn.finished_collection = true;
          
          form.resetView();
          alert('Done. You can leave. Or wait for data to be submitted.');
      }
    });
    
    // Submit call status form.
    $('#call-task-status-submit').click(function(e) {
      e.preventDefault();
      
      if (whatsGoingOn.finished_collection) {
        alert('Data collection is finished.');
        return false;
      }

      var call_task_status_code = $('.call-status [name=call_task_status_code]').val();
      var call_task_status_msg = $('.call-status [name=call_task_status_msg]').val();
      
      if (call_task_status_code == '--') {
        alert('Please select a valid status.');
        return false;
      }
      
      // Do something with the data.
      current_respondent.new_status = {
        code : call_task_status_code,
        msg : call_task_status_msg,
      };
      
      // Add the respondent to the submission queue.
      submission_queue.add(current_respondent);
          
      whatsGoingOn.finished_collection = true;
      
      form.resetView();
      alert('Done. You can leave. Or wait for data to be submitted.');
    });
    
    $('#call-task-status-cancel').click(function(e) {
      e.preventDefault();
      $('.call-actions').removeClass('hide');
      $('.call-status').addClass('hide');
    });

  }, 'json');
  
  function init() {
    initializeForm();
    initializeGUI();
  }
  
  /**
   * Always after initializeForm();
   */
  function initializeGUI() {
    $('.call-actions').removeClass('hide');
    // Clean call task
    $('.call-status').addClass('hide');
    $('.call-status [name=call_task_status_code]').val('--');
    $('.call-status [name=call_task_status_msg]').val('');
    
    $('.enketo-container').addClass('hide');
    
    $('#respondent_number').text(current_respondent.number);
  }
  
  /**
   * Initialize the form.
   */
  function initializeForm() {
    
    // Initialize the form.
    form = new Form('form.or:eq(0)', modelStr);
    // For debugging.
    window.form = form;
    // Initialize form and check for load errors.
    loadErrors = form.init();
    if (loadErrors.length > 0) {
      // TODO: Find out what kind of errors.
      alert('loadErrors: ' + loadErrors.join(', '));
    }
    
    return true;
  }



  //******************************************/
  // Event listeners
  // To ease usage of enketo, the objects involved in making queues work
  // trigger events. These need to be captured inside the requirejs 
  // function die to variable scope, and because they are only needed
  // if the form is correctly initialized.
  
  // EVENT submission_queue_change
  // This event is triggered every time the submission queue changes.
  // Adding and/or removing respondents form the queue will trigger the event.
  // This event is also triggered after the submission queue initialization
  // even if the submission queue is empty.
  $(window).on('submission_queue_change', function(event, sub_queue) {
    console.log('EVENT: submission_queue_change');
    
    var $container = $('#debug_data .queue-submit');
    $container.html('');
    var queue = sub_queue.getQueue();
    for (var i in queue) {
      $container.append($('<div>').text(queue[i].number));
    }
  });
  // End Event submission_queue_change

  
  // EVENT connection_status_change
  // Triggered by the Connection object every time there's a status change.
  // Status can be online of offline and can be checked using
  // connection.isOnline()
  $(window).on('connection_status_change', function(event, connection) {
    console.log('EVENT: connection_status_change');
    
    // TEMP
    var $indicator = $('.connection-status');
    if (connection.isOnline()) { $indicator.text('Online').removeClass('alert'); }
    else { $indicator.text('Offline').addClass('alert'); }
    // /TEMP
    
    
    // When the system is back online try to submit.
    if (connection.isOnline()){
      submission_queue.submit();
    }
  });
  // End Event connection_status_change

});