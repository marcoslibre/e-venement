<?php use_stylesheet('tck-touchscreen-simplified?'.date('Ymd')) ?>
<?php use_javascript('tck-touchscreen-simplified?'.date('Ymd')) ?>
<?php use_javascript('jquery.nicescroll.min.js') ?>
<?php use_helper('Number') ?>

<form action="#" method="get">
  <div class="header simplified-top-block">
    <ul class="products-types ui-widget-content ui-corner-all">
       <li data-bunch-id="manifestations"><?php echo __('Manifestations') ?></li
      ><li data-bunch-id="museum"><?php echo __('Museum') ?></li
      ><li data-bunch-id="store"><?php echo __('Store', null, 'menu') ?></li>
    </ul>
    <div class="contact ui-widget-content ui-corner-all"></div>
  </div>
  <div class="content simplified-top-block">
    <ul class="bunch manifestations ui-widget-content ui-corner-all" data-bunch-id="manifestations">
    </ul>
    <ul class="prices ui-widget-content ui-corner-all">
    </ul>
    <ul class="payments ui-widget-content ui-corner-all">
      <li class="value"><input type="number" name="simplified[payment_value]" value="" placeholder="<?php echo __('Value') ?>" /></li>
    </ul>
    <ul class="cart ui-widget-content ui-corner-all">
      <li class="print end">
        <button name="s" class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button fg-button-icon-left">
          <?php echo __('Print and deliver') ?>
          <span class="ui-icon ui-icon-print"></span>
        </button>
      </li>
      <li class="topay end">
        <div class="left">
          <span class="category"><?php echo __('Still missing') ?></span>
        </div>
        <div class="right">
          <span class="value"><?php echo format_currency(0,$sf_context->getConfiguration()->getCurrency()) ?></span>
        </div>
      </li>
      <li class="paid end">
        <div class="left">
          <span class="category"><?php echo __('Total') ?></span>
        </div>
        <div class="right">
          <span class="value" data-value="0"><?php echo format_currency(0,$sf_context->getConfiguration()->getCurrency()) ?></span>
        </div>
      </li>
      <li class="total end">
        <div class="left">
          <span class="category"><?php echo __('To pay') ?></span>
          <span class="qty" data-qty="0">0</span>
        </div>
        <div class="right">
          <span class="value" data-value="0"><?php echo format_currency(0,$sf_context->getConfiguration()->getCurrency()) ?></span>
        </div>
      </li>
    </ul>
  </div>
</form>
