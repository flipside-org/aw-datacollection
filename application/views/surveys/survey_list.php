<div class="row">

  <table width='100%' class="survey_list">
    <thead>
      <tr>
        <th width='40%'>Title</th>
        <th width='5%'>Status</th>
        <th width='35%'>Actions</th>
      </tr>
    </thead>

    <tbody>
    <?php foreach ($surveys as $survey_entity):?>
      <tr>
        <td><a href="<?= $survey_entity->get_url_view() ?>"><?= $survey_entity->title ?></a></td>
        <td><?= $survey_entity->status; ?></td>
        <td>
          <ul class="button-group">
            <?php if (has_permission('edit any survey')) :?>
            <li><a href="<?= $survey_entity->get_url_edit() ?>" class="button tiny">Edit</a></li>
            <?php endif; ?>

            <?php if (has_permission('delete any survey')) :?>
            <li>
              <?php
              print form_open('survey/delete');
              print form_hidden('survey_sid', $survey_entity->sid);
              print form_submit(array(
                'name' => 'survey_delete',
                'value' => 'Delete',
                'class' => 'button tiny'
              ));
              print form_close();
            ?>
            </li>
            <?php endif; ?>

            <?php // @todo add correct permission here ?>
            <?php if (1) :?>
            <li><a href="<?= $survey_entity->get_url_respondents() ?>" class="button tiny secondary">Respondents</a></li>
            <?php endif; ?>

            <?php if ($survey_entity->has_xml()) : ?>
              <li><a href="<?= $survey_entity->get_url_survey_enketo('testrun') ?>" class="button tiny secondary">Test Run</a></li>
              <li><a href="<?= $survey_entity->get_url_survey_enketo('collection') ?>" class="button tiny success">Collect Data</a></li>
              <li><a href="<?= $survey_entity->get_url_call_activity() ?>" class="button tiny">Call activity</a></li>
            <?php endif; ?>
          </ul>
        </td>
      </tr>
    <?php endforeach; ?>

    </tbody>
  </table>
</div>
