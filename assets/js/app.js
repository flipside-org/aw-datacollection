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
  /**
   * Controls whether the for is bootstrapping.
   * The system finished bootstrapping after the form is initialized
   * for the first time.
   */
  bootstrapping : true,
  /**
   * Controls the submission of the form. If the user is waiting for
   * new numbers, the form submission must be blocked. However the user can
   * validate it and play around.
   */
  block_submission : false,
  /**
   * Sub object to control what's happening to numbers.
   * The user can exhaust numbers for several reasons.
   */
  numbers : {
    /**
     * Numbers can be exhausted when the user loads the form and all the
     * numbers assigned are already in the submit queue.
     * The user has to wait for a submission.
     * Upon a successful submission the event submission_queue_submit_success
     * will be triggered, requiring new numbers and re-initializing the form.
     * If there are no more numbers, means that the data collection is over.
     */
    exhausted_bootstrapping : false,
    /**
     * This is a somewhat complex reason.
     * When a user finishes all the numbers and is online, it should mean
     * that data collection is over. There is a chance, however, that the user
     * just came online and the system didn't have time to fetch a new number. 
     * This would be a false positive.
     * Let's imagine the user receives respondents [1,2,3], and the connection
     * drops. The user submits [1,2] and immediately before submitting [3] the
     * connection comes back on. The system has no time to request new numbers
     * and since the local queue is empty, the system assumes that the data
     * collection is over.
     * To prevent this from happening, a confirmation is asked. Since the 
     * connection was reestablished, a respondent will be submitted and a new
     * number will be requested.
     * If there are new numbers the form will be reset otherwise
     * we know for sure that the data collection is over.
     */
    complete_confirm : false,
    /**
     * If the user submits the last number in the local queue and the
     * connection is offline, the system can not fetch more numbers.
     * The user has to wait for the connection to come online again.
     * When it comes online a connection_status_change event will be
     * triggered, a respondent will be submitted, a new number will
     * be requested and the form re-initialized.
     * If there are no more numbers data collection is over.
     */
    offline : false
  }
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
  // Respondent queue. Instance of RespondentQueue.
  var resp_queue;
  // Submission queue. Instance of SubmissionQueue.
  var submission_queue;
  // Current respondent.
  var current_respondent;
  
  // Perform request for form. Form will be returned in xml.
  $.get(Connection.URL_XSLT_TRANSFORM, function(xml_form) {
    // Request numbers.
    RespondentQueue.requestNumbers(function(respondents) {
      // Initialize connection. Although this could be initialized before
      // It's only needed it the form and the first batch of numbers
      // are successfully requested.
      con = new Connection();
      // Initialize the submission queue
      submission_queue = new SubmissionQueue();
      // Initialize respondent queue using the numbers received.
      resp_queue = RespondentQueue.prepareQueue(respondents);
      
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
        console.log('validate-form click event');
        form.validate();
        if (!form.isValid()) {
          alert('Form contains errors. Please see fields marked in red.');
        } else {
          if (whatsGoingOn.block_submission) {
            alert('There are no numbers available. The form can not be submitted.');
          }
          else {
            // Form is valid. Get the data.
            current_respondent.form_data = form.getDataStr();
            // Add the respondent to the submission queue.
            submission_queue.add(current_respondent);
            // Reset the form.
            form.resetView();
            // Initialize again.
            initializeForm();
          }
        }
      });
      
      // Bootstrap finished.
      whatsGoingOn.bootstrapping = false;
      
    });
  }, 'xml');
  
  
  /**
   * Initialize the form.
   */
  // TODO: initializeForm() - Replace alerts with proper messages.
  function initializeForm() {
    // Unblock for submission. It could be blocked if the numbers
    // were exhausted at some point.
    whatsGoingOn.block_submission = false;
    
    // TODO: Setting data. Temporary. Remove.
    $('#respondent_number').text('');
    
    // Check if there are more respondents.
    if (!resp_queue.hasNextResp()) {
      // Numbers are exhausted. Block the form until further action is taken.
      whatsGoingOn.block_submission = true;
      
      alert('Numbers exhausted.');
      
      // Find out why
      if (whatsGoingOn.bootstrapping) {
        // Brief explanation. Check the whatsGoingOn Object for more info.
        // The page has just been loaded and this is the first time the form
        // is initialized. The numbers fetched from the server are all in
        // the submission queue.
        whatsGoingOn.numbers.exhausted_bootstrapping = true;
        alert('BOOTSTRAPPING: Your queue is full. Wait for it to submit.');
      }
      else if (!con.isOnline()) {
        // Brief explanation. Check the whatsGoingOn Object for more info.
        // The user is offline and it's not possible to fetch numbers.
        whatsGoingOn.numbers.offline = true;
        alert("You are offline. It was not possible to fetch more numbers.\nWait");
      }
      else if (con.isOnline()) {
        // Brief explanation. Check the whatsGoingOn Object for more info.
        // Probably numbers are over and data collection is over, however it
        // could be a false positive.
        whatsGoingOn.numbers.complete_confirm = true;
        alert("There are no more numbers in your queue. Please wait while your system fetches more.");
      }
      else {
        // This should never happens, but if it does, we got it covered.
        alert("This is embarrassing.\nAn error occurred. Please refresh the page.");
      }
      // Return
      return false;
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
  // To ease usage of enketo, the objects involved in making queues work
  // trigger events. These need to be captured inside the requirejs 
  // function die to variable scope, and because they are only needed
  // if the form is correctly initialized.
  
  // EVENT submission_queue_submit_success
  // This event is triggered by the SubmissionQueue object when there's a
  // successful submission.
  $(window).on('submission_queue_submit_success', function() {
    console.log('EVENT: submission_queue_submit_success');
    
    // After a successful submission request new numbers.
    RespondentQueue.requestNumbers(function(respondents) { 
      // Update the respondent queue using the numbers received from the server.
      resp_queue = RespondentQueue.prepareQueue(respondents, resp_queue);
      
      // TODO: submission_queue_submit_success - Handle equal ifs.
      // Right now the instructions are the same for every condition. Check if
      // this is correct and improve code.
      // TODO: submission_queue_submit_success - Use proper messages instead of alert().
      
      // When the numbers are exhausted the user has to wait for a submission.
      // Upon submission, new numbers are requested and the user can be notified.
      // The user will either be able to continue data collection or be done
      // with collection.
      if (whatsGoingOn.numbers.exhausted_bootstrapping) {
        // Brief explanation. Check the whatsGoingOn Object for more info.
        // The page has just been loaded and this is the first time the form
        // is initialized. The numbers fetched from the server are all in
        // the submission queue.
        whatsGoingOn.numbers.exhausted_bootstrapping = false;
        if (resp_queue.hasNextResp()){
          alert("Turns out there are more respondents.\nInitialize the form again!");
          initializeForm();
        }
        else {
          alert("There are no more respondents.\nData collection is over.");
        }
      }
      else if (whatsGoingOn.numbers.complete_confirm) {
        // Brief explanation. Check the whatsGoingOn Object for more info.
        // Probably numbers are over and data collection is over, however it
        // could be a false positive.
        whatsGoingOn.numbers.complete_confirm = false;
        if (resp_queue.hasNextResp()){
          alert("Turns out there are more respondents.\nInitialize the form again!");
          initializeForm();
        }
        else {
          alert("There are no more respondents.\nData collection is over.");
        }
      }
      else if (whatsGoingOn.numbers.offline) {
        // Brief explanation. Check the whatsGoingOn Object for more info.
        // The user is offline and it's not possible to fetch numbers.
        whatsGoingOn.numbers.offline = false;
        if (resp_queue.hasNextResp()){
          alert("Turns out there are more respondents.\nInitialize the form again!");
          initializeForm();
        }
        else {
          alert("There are no more respondents.\nData collection is over.");
        }
      }
      
    });
  });
  // End Event submission_queue_submit_success
  
  // EVENT submission_queue_change
  // This event is triggered every time the submission queue changes.
  // Adding and/or removing respondents form the queue will trigger the event.
  // This event is also triggered after the submission queue initialization
  // even if the submission queue is empty.
  $(window).on('submission_queue_change', function(event, sub_queue) {
    console.log('EVENT: submission_queue_change');
    
    var $container = $('#queue_submit');
    $container.html('');
    var queue = sub_queue.getQueue();
    for (var i in queue) {
      $container.append($('<div>').text(queue[i].number));
    }
  });
  // End Event submission_queue_change
  
  // EVENT respondent_queue_change
  // This event is triggered every time the respondent queue changes, however
  // right now it only possible to append elements to the respondent queue
  // since it will keep history of respondents (until page refresh)
  // This event is also triggered after the respondent queue initialization
  // even if the respondent queue is empty.
  $(window).on('respondent_queue_change', function(event, resp_queue) {
    console.log('EVENT: respondent_queue_change');
    
    var $container = $('#queue_resp');
    $container.html('');
    var all_respondents = resp_queue.getQueue();
    for (var i in all_respondents) {
      $container.append($('<div>').text(all_respondents[i].number));
    }
  });
  // End Event respondent_queue_change
  
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





































/**
 * RespondentQueue
 * Holds the respondents used by enketo.
 * The respondents will be appended to the queue and remain in it until
 * there's a page refresh.
 * It has an internal counter to keep track of the current respondent.
 * 
 * This should not be initialized directly. Use prepareQueue instead.
 */
RespondentQueue = function() {
  this.respondents = [];
  this.current = 0;
};

/**
 * Static function to initialize the RespondentQueue.
 * The server has no way to know which numbers are in the submission queue
 * so it sends all the numbers currently reserved.
 * During bootstrap, after receiving the numbers, only the ones not in
 * the submission queue should be added to the respondent queue.
 * Example:
 * Numbers in submission queue: [1,2]
 * Numbers from server: [1,2,3,4]
 * Numbers added to respondent queue [3,4]
 * 
 * When requesting additional numbers, only the new numbers are added to the
 * respondents queue, so the numbers are matched against the submission queue
 * and the current respondent queue.
 * Example:
 * Number 1 is submitted.
 * Numbers in submission queue: [2]
 * Numbers from server: [2,3,4,5]
 * Numbers in respondent queue: [3,4]
 * Numbers added to respondent queue [5]
 * Final respondent queue: [3,4,5] 
 * 
 * @static
 * @param {array} new_respondents
 *   New respondents received from the server.
 * @param {RespondentQueue} current_respondent_queue
 *   If null, a new RespondentQueue is returned, otherwise the numbers are
 *   appended to the existing queue.
 */
RespondentQueue.prepareQueue = function(new_respondents, current_respondent_queue) {
  if (Connection.supportsLocalStorage() == false) {
    alert("Your browser is outdated.\nYou will be redirected to a page to upgrade your browser.");
    window.location = 'http://browsehappy.com/';
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
    console.log('Respondents from the server:');
    console.log(new_respondents);
    console.log('Respondents in localStorage:');
    console.log(stored_data);
    console.log('Respondents after filter:');
    console.log(filtered);
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
    var current_queue = current_respondent_queue.getQueue();
    // After filtering the numbers from the server against the ones
    // on localStorage, we need to filter them against the ones in the
    // previous queue. In the end we will be left only with the new numbers.
    new_respondents = $.grep(filtered, function(n, index) {
      for (var i in current_queue) {
        if (n.number == current_queue[i].number) { return false; }
      }
      return true;
    });
    
    console.log('Current queue NOT null');
    console.log('Respondents in current queue:');
    console.log(current_queue);
    console.log('Respondents after filter:');
    console.log(new_respondents);
    console.log('**********************');
    
    return current_respondent_queue.appendResp(new_respondents);
  }
};

/**
 * Static function to request new numbers.
 * 
 * @static
 * @param {function} callback
 *   Callback function executed when the request for new numbers is successful.
 */
RespondentQueue.requestNumbers = function(callback) {
  $.get(Connection.URL_REQUEST_RESPONDENTS, function(response) {
    console.log('Respondents from server: (RespondentQueue.requestNumbers)');
    console.log(response.respondents);
    callback(response.respondents);
  }, 'json');
};

/**
 * Returns the next respondent and moves the counter forward.
 * 
 * @return {mixed}
 *   Returns the next respondent in queue or false if not available.
 */
RespondentQueue.prototype.getNextResp = function() {
  if (typeof this.respondents[this.current] == 'undefined') {
    return false;
  }
  else {
    var resp = this.respondents[this.current];
    this.current++;
    return resp;
  }
};

/**
 * Checks whether there's a next respondent in queue.
 * 
 * @return {boolean} 
 */
RespondentQueue.prototype.hasNextResp = function() {
  return typeof this.respondents[this.current] != 'undefined';
};

/**
 * Returns the total length of the respondent queue.
 * 
 * @return {int}
 */
RespondentQueue.prototype.getTotal = function() {
  return this.respondents.length;
};

/**
 * Returns the current counter.
 * Since the counter is moved by getNextResp() after returning the respondent
 * the current respondent index is counter-1
 * 
 * @return {int}
 */
RespondentQueue.prototype.getCurrentCount = function() {
  return this.current;
};

/**
 * Returns all the respondents in queue.
 * 
 * @return {array}
 */
RespondentQueue.prototype.getQueue = function() {
  return this.respondents;
};

/**
 * Appends given respondents to the queue.
 * Even if it's only one respondent it must be inside an array.
 * 
 * Triggers Event respondent_queue_change
 * 
 * @param {array} respondents
 * @return {RespondentQueue} this
 *   To allow chaining.
 */
RespondentQueue.prototype.appendResp = function(respondents) {
  $.merge(this.respondents, respondents);
  $(window).trigger('respondent_queue_change', this);
  return this;
};









/**
 * Connection
 * Responsible to asses the status of the connection. After initialization
 * it will periodically check for connectivity and trigger a 
 * connection_status_change event every time there's a change.
 */
Connection = function() {
  this.connection_check_interval = 15 * 1000;
  this.csrf_token = null;
  this.online = false;
  
  this.initialized = false;
  
  this.init();
};

// Add some static variables.
// TODO: Urls should be in Aw.settings.url.
$.extend(Connection, {
  /**
   * Url to periodically query to check for connectivity.
   */
  URL_CHECK_CONNECTION     : Aw.settings.check_connection,
  /**
   * Url to request a CSRF token if the current one is void.
   */
  URL_REQUEST_CSRF         : Aw.settings.base_url + 'survey/survey_request_csrf_token/',
  /**
   * Url to get the survey form.
   */
  URL_XSLT_TRANSFORM       : Aw.settings.xslt_transform_path,
  /**
   * Url to request respondents.
   */
  URL_REQUEST_RESPONDENTS  : Aw.settings.base_url + 'survey/survey_request_numbers/1',
  /**
   * Url to where the form must be submitted.
   */
  URL_FORM_SUBMIT          : Aw.settings.base_url + 'survey/survey_submit_enketo_form',
});

/**
 * Initializes the connection setting up the checking interval.
 */
Connection.prototype.init = function() {
  var self = this;
  // Only initialize once.
  if (self.initialized) { return; }
  self.initialized = true;
  
  // Check for the first time.
  self.checkConnection();
  
  // Interval to check for connection.
  window.setInterval( function() {
    self.checkConnection();
  }, self.connection_check_interval );
};

/**
 * Checks if there's connectivity with the server. 
 */
Connection.prototype.checkConnection = function() {
  var self = this;
  // As found in enketo.
  // navigator.onLine is totally unreliable (returns incorrect trues)
  // on Firefox, Chrome, Safari (on OS X 10.8), but we assume
  // falses are correct.
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

/**
 * Sets the status of the connection if it changed.
 * 
 * Trigger Event connection_status_change
 *  When the status change.
 * 
 * @param {boolean} newStatus
 */
Connection.prototype.setOnlineStatus = function(newStatus) {
  var self = this;
  console.log('Is online: ' + newStatus);
  if (newStatus != self.online) {
    self.online = newStatus;
    $(window).trigger('connection_status_change', self);
  }
};

/**
 * Returns whether there's connectivity or not.
 * 
 * @returns {boolean}
 */
Connection.prototype.isOnline = function() {
  return this.online;
};

/**
 * Requests new CSRF token
 * Executes callback function if the request is successful.
 * 
 * @param {function} callback
 */
Connection.prototype.requestCSRF = function(callback) {
  var self = this;
  console.log('Requesting CSRF token. Current: ' + self.csrf_token);
  $.get(Connection.URL_REQUEST_CSRF, function(response) {
    self.csrf_token = response.csrf;
    console.log('New CSRF Token: ' + self.csrf_token);
    callback();
  }, 'json');
};

/**
 * Invalidates the current CSRF token.
 * 
 * @return {Connection} this
 *   To allow chaining.
 */
Connection.prototype.invalidateCSRF = function() {
  this.csrf_token = null;
  return this;
};

/**
 * Returns the current CSRF token.
 * 
 * @return {string}
 */
Connection.prototype.getCSRF = function() {
  return this.csrf_token;
};

/**
 * Checks whether the localStorage is available.
 * @static
 * @return {boolean}
 */
Connection.supportsLocalStorage = function() {
  try {
    return 'localStorage' in window && window['localStorage'] !== null;
  } catch (e) {
    return false;
  }
};






/**
 * SubmissionQueue
 * Holds the respondents scheduled for submission.
 * Is kept in sync with the localStorage, adding data to it and removing
 * it when is submitted.
 * 
 * On initialization build the queue from the localStorage and tries to
 * submit data.
 * 
 * Trigger Event submission_queue_change
 *   Event is triggered after syncing with the local storage, even if
 *   the queue is empty.
 */
SubmissionQueue = function() {
  this.queue = [];
  this.is_uploading = false;
  
  if (Connection.supportsLocalStorage() == false) {
    alert("Your browser is outdated.\nYou will be redirected to a page to upgrade your browser.");
    window.location = 'http://browsehappy.com/';
    return null;
  }
  
  this.init();
  $(window).trigger('submission_queue_change', this);
  this.submit();
};

/**
 * Initializes the submission queue syncing with the local storage.
 */
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
      // Initialize as empty.
      this.queue = [];
    }
  }
};

/**
 * Adds respondent to the submission queue.
 * The added respondent will be stored in the localStorage and
 * a submission will be tried.
 * 
 * Triggers Event submission_queue_change
 *   After storing respondent on localStorage.
 * 
 * @param {array} value
 */
SubmissionQueue.prototype.add = function(value) {
  console.log('Adding value to submission queue.');
  this.queue.push(value);
  this.store();
  $(window).trigger('submission_queue_change', this);
  this.submit();
};

/**
 * Removes the first respondent from the submission queue.
 * The changes will be written to the localStorage.
 * This method is called after a successful submission. It's always the first
 * element in the queue to be submitted, and after the submission it can be
 * removed.
 * 
 * Triggers Event submission_queue_change
 *   After removing respondent from localStorage.
 * 
 * @param {array} value
 */
SubmissionQueue.prototype.shift = function() {
  console.log('Shifting value from submission queue.');
  this.queue.shift();
  this.store();
  $(window).trigger('submission_queue_change', this);
};

/**
 * Returns all the respondents in queue.
 * 
 * @return {array}
 */
SubmissionQueue.prototype.getQueue = function() {
  return this.queue;
};

/**
 * Stores the queue on localStorage overriding the existing one. Since it is
 * synced when the SubmissionQueue is initialized the data is safe.
 */
SubmissionQueue.prototype.store = function() {
  console.log('Storing on localStorage.');
  var to_store = JSON.stringify(this.queue);
  localStorage.setItem('aw_submission_queue', to_store);
};

/**
 * Submits the first respondent in the queue.
 * When successful will remove the submitted respondent from the queue.
 * 
 * Trigger Event submission_queue_change
 * Trigger Event submission_queue_submit_success
 *   Events are triggered after a successful submission.
 * 
 */
SubmissionQueue.prototype.submit = function() {
  var self = this;
  console.log('Pre submission checks..');
  console.log('Online: ' + con.isOnline());
  console.log('Uploading:' + self.is_uploading);
  console.log('Queue: ' + self.queue.length);
  
  // Only try to submit if the following condition are met:
  // There's an active connection.
  // There's no concurrent submission.
  // There's something to submit.
  if (con.isOnline() && !self.is_uploading && self.queue.length > 0) {
    console.log('Submitting.');
    
    // Check if there is a CSRF token.
    if (con.getCSRF() == null) {
      // Request CSRF token.
      con.requestCSRF(function() {
        // Now that we have a token, submit again.
        self.submit();
      });
      return null;
    }
    
    // TODO: SubmissionQueue.prototype.submit - Remove MOCK
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
    ///////////////////////--MOCK
    
    // Submit only the first respondent on queue.
    var respondent_to_submit = self.queue[0];
    // Marks as uploading to prevent concurrent uploads.
    self.is_uploading = true;
    
    // Submit data
    $.post(Connection.URL_FORM_SUBMIT, {
      // TODO: Survey id must always accompany the post
      csrf_aw_datacollection : con.getCSRF(),
      respondent: respondent_to_submit
      
    }, function(res) {
      console.log('Submission successful.');
      console.log(res);
      // The operation succeeded.
      // Remove the respondent and trigger change.
      self.shift();
      $(window).trigger('submission_queue_submit_success');
            
    }).fail(function(res) {
      console.log('Submission failed.');
      // If the request failed because of the CSRF token, invalidate it and try again.
      if (res.status == 500 && res.responseText.match('The action you have requested is not allowed.')) {
        console.log('Invalid token.');
        // CSRF token error.
        // Invalidate token.
        con.invalidateCSRF();
      }
      else if (res.status == 404) {
        console.log('404 - Offline');
        // 404 - Not found means no connection.
        // Just set the connection status to offline. If this turns out to
        // be a false positive, the next time the connection check runs
        // the status is changed and the submission will be tried again.
        con.setOnlineStatus( false );
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