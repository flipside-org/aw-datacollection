/**
 * RespondentQueue
 * Holds the respondents used by enketo.
 * The respondents will be appended to the queue and remain in it until
 * there's a page refresh.
 * It has an internal counter to keep track of the current respondent.
 * 
 * This should not be initialized directly. Use prepareQueue instead.
 * 
 * IMPORTANT: The SubmissionQueue is directly dependent on Connection
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
        // ctid is the call task id.
        if (n.ctid == stored_data[i].ctid) { return false; }
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
    var filtered = $.grep(new_respondents, function(n, index) {
      for (var i in current_queue) {
        // ctid is the call task id.
        if (n.ctid == current_queue[i].ctid) { return false; }
      }
      return true;
    });
    
    console.log('Current queue NOT null');
    console.log('Respondents in current queue:');
    console.log(current_queue);
    console.log('New respondents after filter:');
    console.log(filtered);
    console.log('**********************');

    return current_respondent_queue.appendResp(filtered);
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