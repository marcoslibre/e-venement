<?php use_helper('Number') ?>
<?php echo format_currency($transaction->getPrice(true,true),$sf_context->getConfiguration()->getCurrency()) ?>
