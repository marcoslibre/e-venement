<?php
  if ( isset($gauge) && $gauge->getRawValue() instanceof Gauge )
    $param = 'gauge_id='.$gauge->id;
  elseif ( isset($gauges) && count($gauges) > 0 )
  {
    $ids = array();
    foreach ( $gauges as $gauge )
      $ids[] = $gauge->id;
    $param = 'gauges_list[]='.implode('&amp;gauges_list[]=', $ids);
  }
?>
          <a class="close float"
             href="#"
             onclick="javascript: $(this).closest('.seated-plan-parent').closest('.gauge').removeClass('active'); LI.controls_repeat($(this).closest('.seated-plan-parent').find('.controls-loop').prop('checked', false)); console.log('Seated plan closed'); return false;"
             title="<?php echo __('Close') ?>"
          ></a>
          <a class="full float"
             href="#"
             onclick="javascript: $(this).closest('.seated-plan-parent').toggleClass('high'); return false;"
             title="<?php echo __('Full size') ?>"
          >
            <span class="ui-icon ui-icon-arrow-2-n-s"></span>
          </a>
          <a class="print float"
             href="#"
             onclick="javascript: LI.print_seated_plan($(this).closest('.seated-plan-parent')); return false;"
             title="<?php echo __('Print', null, 'menu') ?>"
          >
            <span class="ui-icon ui-icon-print"></span>
          </a>
          <a class="occupation"
             href="<?php echo url_for('seated_plan/getSeats?id='.$seated_plan->id) ?>?<?php echo $param ?>"
             onclick="javascript: var plan = $(this).closest('.seated-plan-parent').find('.seated-plan'); LI.seatedPlanLoadData($(this).prop('href'), plan); return false;"
             title="<?php echo __('Reload') ?>"
          >
            <?php echo __('Reload') ?>
          </a>
          <a class="holds"
             href="<?php echo url_for('hold/css?manifestation_id='.$gauge->manifestation_id) ?>"
             onclick="javascript: $('<link></link>').prop('rel','stylesheet').prop('type','text/css').prop('href', $(this).prop('href')).addClass('holds').appendTo('head'); setTimeout(function(){ $('#transition .close').click(); },1000); return false;"
             title="<?php echo __('Holds') ?>"
          >
            <?php echo __('Holds') ?>
            <!-- remove Holds stylesheet as soon as an other action is clicked -->
            <script type="text/javascript"><!--
              $('.seated-plan-actions *:not(.holds)').click(function(){
                $('head link.holds').remove();
              });
            --></script>
          </a>
          <input class="controls-loop" type="checkbox" name="live-controls" value="yes" title="<?php echo __('Auto') ?>" onchange="javascript: return LI.controls_repeat(this);" />
          <a class="controls"
             href="<?php echo url_for('seated_plan/getControls?id='.$seated_plan->id) ?>?<?php echo $param ?>"
             onclick="javascript: var plan = $(this).closest('.seated-plan-parent').find('.seated-plan'); LI.seatedPlanLoadData($(this).prop('href'), plan); return false;"
             title="<?php echo __('Ticket control',null,'menu') ?>"
          >
            <?php echo __('Ticket control',null,'menu') ?>
            <script type="text/javascript"><!--
              LI.controls_repeat = function(elt){
                if ( LI.controls_interval )
                  clearInterval(LI.controls_interval);
                
                if ( $(elt).is(':checked') && $(elt).closest('.seated-plan-parent').closest('.gauge').hasClass('active') )
                  LI.controls_interval = setInterval(function(){ $(elt).siblings('a.controls').click(); },30000);
              }
            --></script>
          </a>
          <?php use_javascript('event-seated-plan-more-data') ?>
          <a class="shortnames"
             href="<?php echo url_for('seated_plan/getShortnames?id='.$seated_plan->id) ?>?<?php echo $param ?>"
             onclick="javascript: var plan = $(this).closest('.seated-plan-parent'); LI.seatedPlanMoreDataInitialization($(this).prop('href'), true, plan); return false;"
             title="<?php echo __('Spectators') ?>"
          >
            <?php echo __('Spectators') ?>
          </a>
          <a class="ranks"
             href="<?php echo url_for('seated_plan/getRanks?id='.$seated_plan->id) ?>?<?php echo $param ?>"
             onclick="javascript: var plan = $(this).closest('.seated-plan-parent'); LI.seatedPlanMoreDataInitialization($(this).prop('href'), true, plan); return false;"
             title="<?php echo __('Ranks') ?>"
          >
            <?php echo __('Ranks') ?>
          </a>
          <a class="debts"
             href="<?php echo url_for('seated_plan/getDebts?id='.$seated_plan->id) ?>?<?php echo $param ?>"
             onclick="javascript: var plan = $(this).closest('.seated-plan-parent'); LI.seatedPlanMoreDataInitialization($(this).prop('href'), true, plan); return false;"
             title="<?php echo __('Debts') ?>"
          >
            <?php echo __('Debts') ?>
          </a>
          <form class="groups"
             method="get"
             action="<?php echo url_for('seated_plan/getGroup') ?>"
             target="_blank"
             onsubmit="javascript: if ( $(this).find('[name=group_id]').val() == '0' ) { $(this).find('option').css('background-color', 'transparent'); $(this).closest('.seated-plan-parent').find('.more-data.group').remove(); return false; } var plan = $(this).closest('.seated-plan-parent'); LI.seatedPlanMoreDataInitialization($(this).prop('action')+'?'+$(this).serialize(), true, plan); return false;"
          >
            <input type="hidden" name="id" value="<?php echo $seated_plan->id ?>" />
            <?php if ( isset($ids) ): ?>
            <?php foreach ( $ids as $id ): ?>
            <input type="hidden" name="gauges_list[]" value="<?php echo $id ?>" />
            <?php endforeach ?>
            <?php else: ?>
            <input type="hidden" name="gauges_list[]" value="<?php echo $gauge->id ?>" />
            <?php endif ?>
            <select name="group_id" onchange="javascript: $(this).closest('form').submit();">
              <option value="0">--<?php echo __('Clear groups') ?>--</option>
              <?php foreach ( Doctrine::getTable('Group')->createQuery('g')->orderBy('u.id IS NULL DESC, u.username, name')->execute() as $group ): ?>
                <option value="<?php echo $group->id ?>"><?php echo $group ?></option>
              <?php endforeach ?>
            </select>
          </form>
          <form class="transactions"
             method="get"
             action="<?php echo url_for('seated_plan/getTransaction') ?>"
             target="_blank"
             onsubmit="javascript: if ( $(this).find('[name=transaction_id]').val() == '0' ) { $(this).find('option').css('background-color', 'transparent'); $(this).closest('.seated-plan-parent').find('.more-data.transaction').remove(); return false; } var plan = $(this).closest('.seated-plan-parent'); LI.seatedPlanMoreDataInitialization($(this).prop('action')+'?'+$(this).serialize(), true, plan); return false;"
          >
            <input type="hidden" name="id" value="<?php echo $seated_plan->id ?>" />
            <?php if ( isset($ids) ): ?>
            <?php foreach ( $ids as $id ): ?>
            <input type="hidden" name="gauges_list[]" value="<?php echo $id ?>" />
            <?php endforeach ?>
            <?php else: ?>
            <input type="hidden" name="gauges_list[]" value="<?php echo $gauge->id ?>" />
            <?php endif ?>
            <select name="transaction_id" onchange="javascript: $(this).closest('form').submit();">
              <option value="0">--<?php echo __('Clear transactions') ?>--</option>
              <?php foreach ( Doctrine::getTable('Transaction')->createQuery('t')->andWhere('tck.seat_id IS NOT NULL')->groupBy($group = 't.id, t.contact_id, t.professional_id')->having('count(tck.id) > ?', 1)->select($group)->orderBy('t.id DESC')->execute() as $transaction ): ?>
                <option value="<?php echo $transaction->id ?>">
                  #<?php echo $transaction->id ?>
                  <?php if ( $transaction->contact_id ): ?>
                    <?php echo $transaction->Contact ?>
                  <?php endif ?>
                </option>
              <?php endforeach ?>
            </select>
          </form>
          
          <?php use_helper('CrossAppLink') ?>
          <a href="<?php echo cross_app_url_for('tck', 'transaction/find') ?>" class="transaction" style="display: none"></a>
