var always_fail = true;
$(document).ready(function(){
  $('.allow-submission').click(function(e) {
    e.preventDefault();
    always_fail = false;
  });
});


// Little object to keep track of what's happening
var whatsGoingOn = {
  bootstrapping : true,
  block_submission : false,
  
  numbers : {
    exhausted_bootstrapping : false,
    complete_confirm : false,
    offline : false
  }
};


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

// Connection needs to be global.
var con;
requirejs(['jquery', 'Modernizr', 'enketo-js/Form'], function($, Modernizr, Form) {
  var loadErrors, form;
  // Respondent queue.
  var resp_queue;
  var current_respondent;
  var submission_queue;
  // Perform request for form and data.
  $.get(Connection.URL_XSLT_TRANSFORM, function(xml_form) {
    RespondentQueue.requestNumbers(function(respondents) {
      con = new Connection();
      // Set number to call.
      submission_queue = new SubmissionQueue();
      
      resp_queue = RespondentQueue.prepareQueue(respondents, null);
      
      // Enketo form stuff.
      var $data = $(xml_form);
      formStr = (new XMLSerializer() ).serializeToString($data.find( 'form:eq(0)' )[0]);
      modelStr = (new XMLSerializer() ).serializeToString($data.find( 'model:eq(0)' )[0]);
      
      // Insert form.
      $('#validate-form').before(formStr);
      
      // Initialize form.
      initializeForm();
  
      //validate handler for validate button
      $('#validate-form').on('click', function() {
        form.validate();
        if (!form.isValid()) {
          alert('Form contains errors. Please see fields marked in red.');
        } else {
          alert('Form is correctly filled. However, since this is a test run, no data will be collected.');
          //alert('Form is valid! (see XML record in the console)');
          if (whatsGoingOn.block_submission) {
            alert('There are no numbers available. The form can not be submitted.');
          }
          else {
            console.log('validate-form');
            console.log(current_respondent);
            current_respondent.form_data = form.getDataStr();
            submission_queue.add(current_respondent);
            
            form.resetView();
            initializeForm();
          }
        }
      });
      
      whatsGoingOn.bootstrapping = false;
      
    });
  }, 'xml');
  
  
  /**
   * Initialize the form.
   */
  function initializeForm() {
    whatsGoingOn.block_submission = false;
    // TODO: Setting data. Temporary. Remove.
    $('#respondent_number').text('');
    
    // Check if there are more respondents.
    if (!resp_queue.hasNextResp()) {
      whatsGoingOn.block_submission = true;
      // TODO: Do something when there are no more numbers.
      alert('Numbers exhausted.');
      
      // Find out why
      if (whatsGoingOn.bootstrapping) {
        alert('BOOTSTRAPPING: Your queue is full. Wait for it to submit.');
        whatsGoingOn.numbers.exhausted_bootstrapping = true;
      }
      else if (!con.isOnline()) {
        alert("You are offline. It was not possible to fetch more numbers.\nWait");
        whatsGoingOn.numbers.offline = true;
      }
      else if (con.isOnline()) {
        // Probably numbers are over?
        whatsGoingOn.numbers.complete_confirm = true;
        $('#totals').before('<h1>WAIT!</h1>');
      }
      else {
        alert('This is embarrassing. An error occurred. Refresh the page.');
      }
      return;
    }
    
    // Get the next respondent.
    current_respondent = resp_queue.getNextResp();
    
    // TODO: Setting data. Temporary. Remove.
    $('#respondent_number').text(current_respondent.number);
    
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
  }



  //******************************************/
  // Event listeners
  // EVENT submission_queue_submit_success
  $(window).on('submission_queue_submit_success', function() {
    console.log('EVENT: submission_queue_submit_success');
    
    RespondentQueue.requestNumbers(function(respondents) { 
      // Set number to call.
      resp_queue = RespondentQueue.prepareQueue(respondents, resp_queue);
      
      if (whatsGoingOn.numbers.exhausted_bootstrapping) {
        whatsGoingOn.numbers.exhausted_bootstrapping = false;
        if (resp_queue.hasNextResp()){
          alert("Turns out there are more respondents.\n Initialize the form again!");
          initializeForm();
        }
        else {
          alert("There are no more respondents.\nData collection is over.");
        }
      }
      else if (whatsGoingOn.numbers.complete_confirm) {
        whatsGoingOn.numbers.complete_confirm = false;
        if (resp_queue.hasNextResp()){
          alert("Turns out there are more respondents.\n Initialize the form again!");
          initializeForm();
        }
        else {
          alert("There are no more respondents.\nData collection is over.");
        }
      }
      else if (whatsGoingOn.numbers.offline) {
        whatsGoingOn.numbers.offline = false;
        if (resp_queue.hasNextResp()){
          alert("Turns out there are more respondents.\n Initialize the form again!");
          initializeForm();
        }
        else {
          alert("There are no more respondents.\nData collection is over.");
        }
      }
      
    });
  });
  // End Event
  
  // EVENT submission_queue_change
  $(window).on('submission_queue_change', function(event, sub_queue) {
    console.log('EVENT: submission_queue_change');
    
    var $container = $('#queue_submit');
    $container.html('');
    var queue = sub_queue.getQueue();
    for (var i in queue) {
      $container.append($('<div>').text(queue[i].number));
    }
  });
  // End Event
  
  // EVENT respondent_queue_change
  $(window).on('respondent_queue_change', function(event, resp_queue) {
    console.log('EVENT: respondent_queue_change');
    
    var $container = $('#queue_resp');
    $container.html('');
    var all_respondents = resp_queue.getQueue();
    for (var i in all_respondents) {
      $container.append($('<div>').text(all_respondents[i].number));
    }
  });
  // End Event
  
  // EVENT connection_status_change
  $(window).on('connection_status_change', function() {
    console.log('EVENT: connection_status_change');
    // TEMP
    var $indicator = $('.connection-status');
    if (con.online) { $indicator.text('Online').removeClass('alert'); }
    else { $indicator.text('Offline').addClass('alert'); }
    // /TEMP
    
    
    if (con.online){
      submission_queue.submit();
    }
  });
  // End Event

});






































// Objects
var RespondentQueue = function() {
  this.respondents = [];
  this.current = 0;
};

RespondentQueue.prepareQueue = function(new_respondents, current_respondent_queue) {
  if (SubmissionQueue.supportsLocalStorage() == false) {
    alert('You need a modern browser.');
    return null;
  }
  
  var stored_data;
  // Get stored data and convert it to JSON.
  try {
    stored_data = JSON.parse(localStorage.getItem('aw_submission_queue'));
  }
  catch(e) {
    stored_data = [];
  }
  
  // Remove from new_respondents the respondents that are already
  // scheduled for submission.
  if ($.isArray(stored_data) && stored_data.length > 0) {

    var filtered = $.grep(new_respondents, function(n, index) {
      for (var i in stored_data) {
        if (n.number == stored_data[i].number) { return false; }
      }
      return true;
    });
    
    console.log('**********************');
    console.log('store');
    console.log(stored_data);
    console.log('filter');
    console.log(filtered);
    console.log('resp queue');
    console.log(new_respondents);
    console.log('**********************');
    
    // Assign filtered.
    new_respondents = filtered;
  }
  
  if (current_respondent_queue == null) {
    // First setup.
    var queue = new RespondentQueue();
    queue.appendResp(new_respondents);
    return queue;
  }
  else {
    // Appending numbers.
    // After filtering the numbers from the server against the ones
    // on localStorage, we need to filter them against the ones in the
    // previous queue. In the end we will be left only with the new numbers.
    var current_queue = current_respondent_queue.getQueue();
    var new_respondents = $.grep(filtered, function(n, index) {
      for (var i in current_queue) {
        if (n.number == current_queue[i].number) { return false; }
      }
      return true;
    });
    
    return current_respondent_queue.appendResp(new_respondents);
  }
};

RespondentQueue.requestNumbers = function(callback) {
  $.get(Connection.URL_REQUEST_RESPONDENTS, function(response) {
    console.log('Respondents from server: (RespondentQueue.requestNumbers)');
    console.log(response.respondents);
    callback(response.respondents);
  }, 'json');
};
 
RespondentQueue.prototype.getNextResp = function() {
  console.log('RespondentQueue.prototype.getNextResp');
  if (typeof this.respondents[this.current] == 'undefined') {
    return false;
  }
  else {
    var resp = this.respondents[this.current];
    this.current++;
    return resp;
  }
};
 
RespondentQueue.prototype.hasNextResp = function() {
  return typeof this.respondents[this.current] != 'undefined';
};

RespondentQueue.prototype.getTotal = function() {
  return this.respondents.length;
};

RespondentQueue.prototype.getCurrentCount = function() {
  return this.current;
};

RespondentQueue.prototype.getQueue = function() {
  return this.respondents;
};

RespondentQueue.prototype.appendResp = function(respondents) {
  $.merge(this.respondents, respondents);
  $(window).trigger('respondent_queue_change', this);
  return this;
};







Connection = function() {
  this.connection_check_interval = 15 * 1000;
  this.csrf_token = null;
  this.online = false;
  
  this.init();
};

// Add some static variables.
$.extend(Connection, {
  URL_CHECK_CONNECTION     : Aw.settings.check_connection,
  URL_REQUEST_CSRF         : Aw.settings.base_url + 'survey/survey_request_csrf_token/',
  URL_REQUEST_RESPONDENTS  : Aw.settings.base_url + 'survey/survey_request_numbers/1',
  URL_XSLT_TRANSFORM       : Aw.settings.xslt_transform_path,
  URL_FORM_SUBMIT          : Aw.settings.base_url + 'survey/survey_submit_enketo_form',
});

Connection.prototype.init = function() {
  var self = this;
  
  // Check for the first time.
  self.checkConnection();
  
  // Interval to check for connection.
  window.setInterval( function() {
    self.checkConnection();
  }, self.connection_check_interval );
};

Connection.prototype.checkConnection = function() {
  var self = this;
  // navigator.onLine is totally unreliable (returns incorrect trues) on Firefox, Chrome, Safari (on OS X 10.8),
  // but I assume falses are correct
  if ( navigator.onLine ) {
      $.ajax({
        type: 'GET',
        url: Connection.URL_CHECK_CONNECTION,
        cache: false,
        dataType: 'json',
        timeout: 3000,
        complete: function( response ) {
          // Important to check for the content of the no-cache response as it will
          // start receiving the fallback page specified in the manifest!
          var online = typeof response.responseText !== 'undefined' && response.responseText === 'connected';
          self.setOnlineStatus( online );
        }
      });
  } else {
    self.setOnlineStatus( false );
  }
};

Connection.prototype.setOnlineStatus = function(newStatus) {
  var self = this;
  console.log('Is online: ' + newStatus);
  if (newStatus != self.online) {
    self.online = newStatus;
    $(window).trigger('connection_status_change');
  }
};

Connection.prototype.isOnline = function() {
  return this.online;
};

Connection.prototype.requestCSRF = function(callback) {
  var self = this;
  console.log('Requesting CSRF token. Current: ' + self.csrf_token);
  $.get(Connection.URL_REQUEST_CSRF, function(response) {
    self.csrf_token = response.csrf;
    console.log('New CSRF Token: ' + self.csrf_token);
    callback();
  }, 'json');
};

Connection.prototype.invalidateCSRF = function() {
  this.csrf_token = null;
  return this;
};

Connection.prototype.getCSRF = function() {
  return this.csrf_token;
};









var SubmissionQueue = function() {
  this.queue = [];
  this.is_uploading = false;
  
  if (SubmissionQueue.supportsLocalStorage() == false) {
    alert('You need a modern browser.');
    return null;
  }
  
  this.init();
  $(window).trigger('submission_queue_change', this);
  this.submit();
};

SubmissionQueue.supportsLocalStorage = function() {
  try {
    return 'localStorage' in window && window['localStorage'] !== null;
  } catch (e) {
    return false;
  }
};

SubmissionQueue.prototype.init = function() {
  var self = this;
  console.log('Initializing Submission queue');
  
  var data = localStorage.getItem('aw_submission_queue');
  if (data != null) {
    try{
      var parsed = JSON.parse(data);
      if ($.isArray(parsed) && parsed.length > 0){
        this.queue = parsed;
      }
    }
    catch(e) {
      // Something went wrong with the parsing.
      // Probably the data is not correctly stored.
      // Init as empty.
      this.queue = [];
    }
  }
};

SubmissionQueue.prototype.add = function(value) {
  console.log('Adding value to submission queue.');
  this.queue.push(value);
  this.store();
  $(window).trigger('submission_queue_change', this);
  this.submit();
};

SubmissionQueue.prototype.shift = function() {
  console.log('Shifting value from submission queue.');
  this.queue.shift();
  this.store();
  $(window).trigger('submission_queue_change', this);
};

SubmissionQueue.prototype.getQueue = function() {
  return this.queue;
};

SubmissionQueue.prototype.store = function(value) {
  console.log('Storing on localStorage.');
  var to_store = JSON.stringify(this.queue);
  localStorage.setItem('aw_submission_queue', to_store);
};

SubmissionQueue.prototype.submit = function() {
  var self = this;
  console.log('Pre submission checks..');
  console.log('Online: ' + con.isOnline());
  console.log('Uploading:' + self.is_uploading);
  console.log('Queue: ' + self.queue.length);
  
  if (con.isOnline() && !self.is_uploading && self.queue.length > 0) {
    console.log('Submitting.');
    
    // Check if there is a CSRF token.
    if (con.getCSRF() == null) {
      // Request csrf.
      con.requestCSRF(function() {
        self.submit();
      });
      return;
    }
    
    ///////////////////////MOCK
    // Simulate errors
    if (always_fail && Math.random() < 1) {
      console.warn('error');
      self.is_uploading = true;
      $.get(Aw.settings.base_url + 'survey/delay/4', function() {
        // yeah. do nothing.
      }).fail(function(r){
        if (r.status == 404) {
          // Not found means no connection.
          con.setOnlineStatus( false );
        }
      }).always(function() {
        self.is_uploading = false;
        self.submit();
      });
      return;
    }
    always_fail = true;
    ///////////////////////MOCK
    
    var to_submit = self.queue[0];
    self.is_uploading = true;
    
    // Submit data
    $.post(Connection.URL_FORM_SUBMIT, {
      csrf_aw_datacollection : con.getCSRF(),
      respondent: to_submit
      
    }, function(r) {
      console.log('Success.');
      console.log(r);
      // The operation succeeded.
      // Remove the respondent and trigger change.
      self.shift();
      $(window).trigger('submission_queue_submit_success');
            
    }).fail(function(res) {
      // If the request failed because of the CSRF token, invalidate it and try again.
      if (res.status == 500 && res.responseText.match('The action you have requested is not allowed.')) {
        console.log('Fail.');
        console.log('Invalid token.');
        // CSRF token error.
        // Invalidate token.
        con.invalidateCSRF();
      }
      
    }).always(function() {
      self.is_uploading = false;
      // Submit again. If there are no more items in the queue
      // the submission is not going to run.
      // If a new token is needed, it will be requested
      self.submit();
    });
  }
};