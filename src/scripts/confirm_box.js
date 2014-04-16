/* The HTML for the confirm box:
<div class="confirm-box-wrapper">
  <section class="confirm-box">
    <header class="confirm-box-head">
      <h1 class="hd-s confirm-title">Confirmation required</h1>
      <a href="#" class="confirm-close confirm-icon-close"><span class="visually-hidden">Close</span></a>
    </header>
    <div class="confirm-box-body">
      <p class="confirm_message">Are you sure?</p>
    </div>
    <footer class="confirm-box-foot">
      <ul class="bttn-toolbar">
        <li><a href="#" class="bttn bttn-danger bttn-medium confirm-cancel">Cancel</a></li>
        <li><a href="#" class="bttn bttn-success bttn-medium confirm-accept">Confirm</a></li>
      </ul>
    </footer>
  </section>
</div>
// Located in: components/confirm_box.php */
var confirmBox = function(msg, options) {
  var settings = $.extend({
    title: 'Are you sure?',
    confirm: 'Confirm',
    cancel: 'Cancel',
    
    onCancel: function(){},
    onConfirm: function(){}
  }, options );
    
  // Reference to needed objects.
  var $confirm_box = $('.confirm-box-wrapper');
  var $accept_bttn = $confirm_box.find('.confirm-accept');
  var $cancel_bttn = $confirm_box.find('.confirm-cancel');
  var $close_bttn = $confirm_box.find('.confirm-close');
  
  // Set message.
  $confirm_box.find('.confirm-message').text(msg);

  // Other properties if set.
  if (settings.title) {
    $confirm_box.find('.confirm-title').text(settings.title);
  }
  if (settings.confirm) {
    $accept_bttn.text(settings.confirm);
  }
  if (settings.cancel) {
    $cancel_bttn.text(settings.cancel);
  }

  // Show box.
  $confirm_box.addClass('revealed');
  
  // Click listeners for different buttons.
  $confirm_box.find('.confirm-cancel, .confirm-close').click(function(e) {
    e.preventDefault();
    e.stopPropagation();
    // Hide box.
    $confirm_box.removeClass('revealed');
    // Unbind all click events.
    // The box code is always the same. It is not destroyed and added every time.
    // To avoid event accumulation the event is removed.
    $accept_bttn.unbind('click');
    $cancel_bttn.unbind('click');
    $close_bttn.unbind('click');
    
    // Custom callback.
    settings.onCancel.apply($confirm_box);
  });

  $accept_bttn.click(function(e) {
    e.preventDefault();
    e.stopPropagation();
    // Hide box.
    $confirm_box.removeClass('revealed');
    // Unbind all click events.
    // The box code is always the same. It is not destroyed and added every time.
    // To avoid event accumulation the event is removed.
    $accept_bttn.unbind('click');
    $cancel_bttn.unbind('click');
    $close_bttn.unbind('click');
    
    // Custom callback.
    settings.onConfirm.apply($confirm_box);
  });
};

$(document).ready(function() {
  // ================================================================
  // Fake form submits and confirmation buttons.
  //
  // To create a confirmation button add ( data-confirm-action="message" )
  // to the button. This will trigger a confirmation box. If the answer
  // is affirmative the button action will fire.
  //
  // ================================================================  
  var confirm_action_block = false;
  $('[data-confirm-action]').click(function(e) {
    // Theoretically the confirmation box can be added to any element
    // meaning that if the user confirms, the element's default action
    // should be triggered and, by consequence, this click event.
    // To prevent the confirmation from being shown a second time, a blocker
    // variable is used.
    if (!confirm_action_block) {
      // Disable default actions
      e.preventDefault();
      e.stopImmediatePropagation();

      var $self = $(this);
      // To show a confirm action only the data-confirm-action with a message is
      // required. However everything can be customized with data attributes.
      var txt_message = $self.attr('data-confirm-action');
      var txt_title = $self.attr('data-confirm-title');
      var txt_accept_bttn = $self.attr('data-confirm-accept');
      var txt_cancel_bttn = $self.attr('data-confirm-cancel');
      
      confirmBox(txt_message, {
        title: txt_title,
        confirm: txt_accept_bttn,
        cancel: txt_cancel_bttn,
        
        onConfirm: function(){
          // Block next action.
          confirm_action_block = true;
          // Trigger click on element.
          $self[0].click();
        }
      });
    }
    // Unblock.
    confirm_action_block = false;
  });
  
  // For fake submit buttons add ( data-trigger-submit="id" ) and replace
  // id with the id of the submit button.
  // Triggers a click event on the element with the given id.
  $('[data-trigger-submit]').click(function(e) {
    e.preventDefault();
    var id = $(this).attr('data-trigger-submit');
    document.getElementById(id).click();
  });
}); 