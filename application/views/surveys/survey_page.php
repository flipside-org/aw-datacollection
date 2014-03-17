<div class="row">
  
  <?= $messages ?>  
  
  <span class="label"><strong>Status:</strong> <?= $survey->status ?></span>
  <h1><?= $survey->title ?></h1>
  
  <div>
    <?= nl2br_except_pre($survey->introduction) ?>
  </div>
  
  <?php if (has_permission('download survey files')) : ?>
    <?php if ($survey->files['xls'] !== NULL) : ?>
      <div>xls: <a href="<?= base_url(sprintf('survey/%d/files/xls', $survey->sid)); ?>">xls</a></div>
    <?php endif; ?>
    
    <?php if ($survey->files['xml'] !== NULL) : ?>
      <div>xml: <a href="<?= base_url(sprintf('survey/%d/files/xml', $survey->sid)); ?>">xml</a></div>
    <?php endif; ?>
  <?php endif; ?>
  
</div>




<select data-placeholder="Assign operators" style="width:350px;" class="chosen-select" multiple>
  <option value=""></option>
  <option value="2">John</option>
  <option value="3">Kevin</option>
  <option value="4">Sarah</option>
  <option value="5">Wallace</option>
  <option value="6">Marta</option>
</select>

<script type="text/javascript" charset="utf-8">
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
</script>