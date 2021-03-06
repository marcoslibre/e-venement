<td class="event"><?php echo str_replace('__', '', $pdt['id']) === ''.$pdt['id']
  ? cross_app_link_to($pdtname, 'pos', 'product/show?id='.$pdt['id'])
  : $pdtname
?></td>
<td class="see-more"><a href="#product-<?php echo slugify($pdtname) ?>">-</a></td>
<td class="id-qty"><?php echo $pdt['qty'] ?></td>
<td class="value"><?php echo format_currency($pdt['value'],$sf_context->getConfiguration()->getCurrency()) ?></td>
<td class="extra-taxes"><?php echo format_currency($pdt['taxes'],$sf_context->getConfiguration()->getCurrency()) ?></td>
<?php $local_vat = 0 ?>
<?php foreach ( $vat as $t => $v ): ?>
  <?php $local_vat += round($v[$pdtname]['__total__'],2); ?>
  <td class="vat"><?php echo format_currency($v[$pdtname]['__total__'],$sf_context->getConfiguration()->getCurrency()) ?></td>
<?php endforeach ?>
<td class="vat total"><?php echo format_currency($local_vat,$sf_context->getConfiguration()->getCurrency()); ?></td>
<td class="tep"><?php echo format_currency($pdt['value']+$pdt['taxes'] - $local_vat,$sf_context->getConfiguration()->getCurrency()) ?></td>
