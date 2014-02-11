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
 * 
 * IMPORTANT: The SubmissionQueue is directly dependent on the Connection
 * and assumes a var con exists.
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
        console.log('CSRF token requested. Calling submit().');
        self.submit();
      });
      return null;
    }
    
    // TODO: SubmissionQueue.prototype.submit - Remove MOCK
    ///////////////////////MOCK
    // Simulate errors
    if (always_fail) {
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
    //*/////////////////////--MOCK
    
    // Submit only the first respondent on queue.
    var respondent_to_submit = self.queue[0];
    // Marks as uploading to prevent concurrent uploads.
    self.is_uploading = true;
    
    // Submit data
    $.post(Connection.URL_FORM_SUBMIT, {
      sid : Aw.settings.current_survey.sid,
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
      console.log('Finished submitting. Calling submit().');
      self.submit();
    });
  }
};