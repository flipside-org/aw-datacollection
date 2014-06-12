// Enketo uses its own jquery, but for the toast
// we need the one used across the website.
// In this way we avoid conflicts.
$jQuery = $;
/**
 * Show a toast with defined message.
 */
function showToast(msg, type, sticky) {
  $jQuery().toastmessage('showToast', {
    sticky   : sticky,
    text     : msg,
    type     : type,
    inEffectDuration : 100,
    position : 'bottom-right',
  });
}
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
  // GUI controller.
  var GUI;
  
  // Perform request for form. Form will be returned in xml.
  $.get(Connection.URL_XSLT_TRANSFORM, function(response) {
    var xml_form = response['xml_form'];
    
    
    // If the computer is shared by multiple users it may happen that a user
    // submits data left by another user. That data should not be lost so
    // when a user starts data collection the system tries to submit it.
    // If the system replies with a "Submitting another user's data" we store
    // the data in another queue.
    // 
    // Before initialising queues, merge the localstorage. When the submit
    // routing runs the values will be separated again. But doing this we ensure
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
    
    
    // Initialise connection. Although this could be initialised before
    // It's only needed it the form and the first batch of numbers
    // are successfully requested.
    con = new Connection();
    // Initialise the submission queue
    submission_queue = new SubmissionQueue();
    
    for(index in submission_queue.queue) {
      if (submission_queue.queue[index].ctid == current_respondent.ctid) {
        whatsGoingOn.already_done = true;
        whatsGoingOn.finished_collection = true;
        // Moved to after initialisation.
        //alert('Respondent already in submission queue');
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
    $('#enketo-form').append(formStr);
    
    
    // Define gui controller.
    // A GUI controller is only needed if the file was successfully loaded.
    GUI = {
      // Whether to show debug data.
      debug : false,
      
      // Original text on the #respondent-number
      respondent_number_text : '',
      
      // Intit function.
      // Sets click listeners and prepares the page.
      init : function() {
        if (GUI.debug) {
          $('#debug-data').show();
        }
        
        GUI.respondent_number_text = $('.respondent-number').text();
        
        ///////////////////////////////////////////////////////////
        //  START Modal controls                                 //
        ///////////////////////////////////////////////////////////
        var $modal = $('#modal');
        // Extend modal object with a reset function.
        $.extend($modal, {
          resetModal : function() {
            // Reset values.
            $('[name=call_task_status_code]', this).val('--');
            $('[name=call_task_status_msg]', this).val('');
            // Hide error
            $('.error', this).hide();
            // Hide modal.
            $(this).removeClass('revealed');
          }
        });
        // Close button.
        $('.confirm-close', $modal).click(function(e) {
          e.preventDefault();
          $modal.resetModal();
        });
        // Cancel button
        $('.confirm-cancel', $modal).click(function(e) {
          e.preventDefault();
          $modal.resetModal();
        });
        // Accept button.
        $('.confirm-accept', $modal).click(function(e) {
          e.preventDefault();
          // Safety check.
          if (whatsGoingOn.finished_collection) {
            alert('Data collection is finished.');
            return false;
          }
          
          // Get values.
          var call_task_status_code = $('[name=call_task_status_code]', $modal).val();
          var call_task_status_msg = $('[name=call_task_status_msg]', $modal).val();
          console.log('Call task status CODE: ' + call_task_status_code);
          console.log('Call task status MSG: ' + call_task_status_msg);
          
          // Check if values are valid.
          if (call_task_status_code == '--') {
            // Show error
            $('.error', $modal).show();
            return false;
          }
          
          // Add data to respondent object.
          current_respondent.new_status = {
            code : call_task_status_code,
            msg : call_task_status_msg,
          };
          
          // Add the respondent to the submission queue.
          submission_queue.add(current_respondent);
          // Store status.
          whatsGoingOn.finished_collection = true;
          // Show message to user.
          showToast("Data submitted. Please check if all data was processed before leaving the page.", 'success', true);
          // Reset the form.
          form.resetView();
          // Clean modal fields and hide it.
          $modal.resetModal();
          // Reset and change text on the number.
          GUI.respondent_number_text = 'Collection finished';
          GUI.reset();
        });
        ///////////////////////////////////////////////////////////
        //  END Modal controls                                   //
        ///////////////////////////////////////////////////////////
        
        ///////////////////////////////////////////////////////////
        //  START Navigation bar controls                        //
        ///////////////////////////////////////////////////////////
        // Halt button to show modal.
        $('#enketo-halt').click(function(e) {
          e.preventDefault();
          if (whatsGoingOn.already_done) {
            return false;
          }
            
          $modal.resetModal();
          $modal.addClass('revealed');
        });
        
        // Proceed with data collection and show the enketo form.
        $('#enketo-proceed').click(function(e) {
          e.preventDefault();
          GUI.step2();
        });
        ///////////////////////////////////////////////////////////
        //  END Navigation bar controls                          //
        ///////////////////////////////////////////////////////////
        
        // Submit enketo form.
        $('#enketo-save').on('click', function(e) {
          e.preventDefault();
          console.log('enketo-save click event');
          
          form.validate();
          if (!form.isValid()) {
            showToast("Form contains errors. Please see fields marked in red.", 'error', true);
          } else {
            if (whatsGoingOn.finished_collection) {
              alert('Data collection is finished.');
            }
            else {
              // Form is valid. Get the data.
              current_respondent.form_data = form.getDataStr();
              // Add the respondent to the submission queue.
              submission_queue.add(current_respondent);
              // Store status
              whatsGoingOn.finished_collection = true;
              // Show message to user.
              showToast("Data submitted. Please check if all data was processed before leaving the page.", 'success', true);
              // Reset the form.
              form.resetView();
              // Reset and change text on the number.
              GUI.respondent_number_text = 'Collection finished';
              GUI.reset();
            }
          }
        });
        
        // Show connection indicator.
        $('#connection-status').show();
      },
      
      // Reset everything to the default. A.K.A step0
      // Hide all the steps, disable alt button and show loading text.
      reset : function() {
        // Reset number.
        $('.respondent-number').text(GUI.respondent_number_text);
        // Hide step1 which consists of the proceed button and metadata.
        $('.step1').removeClass('revealed');
        // Show step2 which is the save button and the enketo form.
        $('.step2').removeClass('revealed');
        // Set the halt to its disabled status. It will be enabled in step1..
        $('#enketo-halt').addClass('disabled');
      },
      
      // Prepares the enketo form and gets the next number.
      // If the number is not available shows a message.
      prepareForm : function() {
        // Initialise the form.
        form = new Form('form.or:eq(0)', modelStr);
        // For debugging.
        //window.form = form;
        // Initialise form and check for load errors.
        loadErrors = form.init();
        if (loadErrors.length > 0) {
          // TODO: Find out what kind of errors.
          console.log('loadErrors: ' + loadErrors.join(', '));
        }
        
        return true;
      },
      
      // Step1 of data collection. Shows call activity and survey introduction.
      step1 : function() {
        GUI.reset();
        // GUI.prepareForm() will return true and prepare the form if the
        // following is true.
        // !whatsGoingOn.numbers.exhausted_bootstrapping &&
        // !whatsGoingOn.numbers.complete_confirm &&
        // !whatsGoingOn.numbers.offline)
        if (GUI.prepareForm()) {            
          // Enable Halt button
          $('#enketo-halt').removeClass('disabled');
          // Set respondent number in place.
          $('.respondent-number').text(current_respondent.number);
          
          // Prepare call activity table.
          var call_activity_header = '<header class="contained-head"><h1 class="hd-s">Call activity</h1></header>';
          var call_activity_empty = '<div class="widget-empty"><p>There is no call activity for this respondent.</p></div>';
          
          var call_activity_table_open = '<div class="contained-body"><table><thead><tr><th>Status</th><th>Date</th></tr></thead><tbody>';
          var call_activity_table_close = '</tbody></table></div>';
          
          var call_activity_table_data = '';
          
          /*
          <tr>
            <td>
              <strong>No reply</strong>
              <p><em>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum viverra ornare orci malesuada.</em></p>
            </td>
            <td>18 Mar, 2014 at 14:00</td>
          </tr>
          */
       
          if (current_respondent.activity.length === 0) {
            $('#call-activity').html(call_activity_header + call_activity_empty);
          }
          else {
            // Loop over activity and populate table.
            for (index in current_respondent.activity) {
              var status = current_respondent.activity[index];
              
              // Compute date.
              var months = ['Jan', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
              var d = new Date(status.created.sec * 1000);
              var hour = d.getHours() < 10 ? '0' + d.getHours() : d.getHours();
              var min = d.getMinutes() < 10 ? '0' + d.getMinutes() : d.getMinutes();
              // Format 18 Mar, 2014 at 14:00
              var status_date = d.getDate() + ' ' + months[d.getMonth()] + ', ' + d.getFullYear() + ' at ' + hour + ':' + min;
              
              call_activity_table_data += '<tr>' + 
                '<td>' + 
                  '<strong>' + Aw.settings.call_task_status[status.code] + '</strong>' + 
                  '<p><em>' + status.message + '</em></p>' +
                '</td>' +
                '<td>' + status_date + '</td>' +
              '</tr>';
            }
            
            // Set the generated code.
            $('#call-activity').html(call_activity_header + call_activity_table_open + call_activity_table_data + call_activity_table_close);
            
          }
          
          // Show step1.
          $('.step1').addClass('revealed');          
        }
      },
      
      // Step2 of data collection. Shows the enketo form.
      step2 : function() {
        // Hide step1 which consists of the proceed button and metadata.
        $('.step1').removeClass('revealed');
        // Show step2 which is the save button and the enketo form.
        $('.step2').addClass('revealed');
      }
    };
    
    // Prepare click listeners, modal, etc.
    GUI.init();
    if (whatsGoingOn.already_done) {
      showToast('Data already collected. Survey is being processed.', 'warning', true);
    }
    else {
      // Start.
      GUI.step1();
    }
    // Bootstrap finished.
    whatsGoingOn.bootstrapping = false;
    
    // On before load action.
    window.onbeforeunload = function() {
      // Right now the fastest way to know if data is being submitted
      // is by checking the beacon status.
      var $beacon = $('#connection-status .beacon');
      if ($beacon.hasClass('working')) {
        return 'Not all survey data was submitted. Are you sure you want to leave the page?';
      }
    };
    
    // Show connection indicator.
    $('#connection-status').show();

  }, 'json');




  //******************************************/
  // Event listeners
  // To ease usage of enketo, the objects involved in making queues work
  // trigger events. These need to be captured inside the requirejs 
  // function die to variable scope, and because they are only needed
  // if the form is correctly initialised.
  
  // EVENT submission_queue_change
  // This event is triggered every time the submission queue changes.
  // Adding and/or removing respondents form the queue will trigger the event.
  // This event is also triggered after the submission queue initialisation
  // even if the submission queue is empty.
  $(window).on('submission_queue_change', function(event, sub_queue) {
    console.log('EVENT: submission_queue_change');
    
    var $container = $('#debug-data .queue-submit');
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
    
    var $beacon = $('#connection-status .beacon');
    if (connection.isOnline()) {
      $beacon.addClass('online');
      $beacon.find('span').text('Offline');
    }
    else {
      $beacon.removeClass('online');
      $beacon.find('span').text('Online');
    }    
    
    // When the system is back online try to submit.
    if (connection.isOnline()){
      submission_queue.submit();
    }
  });
  // End Event connection_status_change
  
  // EVENT submission_queue_submit_start
  // Triggered by the Submission queue when data is being submitted
  $(window).on('submission_queue_submit_start', function(event, status) {
    var $beacon = $('#connection-status .beacon');
    $beacon.addClass('working');
    $beacon.find('span').text('Uploading data.');
  });
  // End EVENT submission_queue_submit_start
  
  // EVENT submission_queue_submit_finish
  // Triggered by the Submission queue when data was submitted
  $(window).on('submission_queue_submit_finish', function(event, status) {
    var $beacon = $('#connection-status .beacon');
    $beacon.removeClass('working');
    
    // Global variable con
    if (con.isOnline()) {
      $beacon.find('span').text('Offline');
    }
    else {
      $beacon.find('span').text('Online');
    }
  });
  // End EVENT submission_queue_submit_finish

});