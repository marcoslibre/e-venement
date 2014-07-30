<?php use_helper('Slug') ?>
<ol class="sf_admin_form_row sf_admin_form_field_show_queries">
<?php foreach ( $form->getObject()->Queries as $query ): ?>
  <li>
    <?php $widget = $query->getWidget() ?>
    <label for="<?php slugify($widget->getLabel()) ?>">
      <?php echo link_to($query, 'query/edit?id='.$query->id) ?>
    </label>
    <span class="query-rank"><?php echo __('Rank') ?>: <?php echo $query->rank ?></span>
    <span class="query-weight"><?php echo __('Weight') ?>: <?php echo $query->weight ?></span>
    <div class="widget">
      <?php echo $query->getWidget()->render(slugify($widget->getLabel())); ?>
    </div>
    <?php foreach ( $widget->getStylesheets() as $css ) use_stylesheet($css) ?>
    <?php foreach ( $widget->getJavascripts() as $js  ) use_javascript($js) ?>
  </li>
<?php endforeach ?>
</ol>