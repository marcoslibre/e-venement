<?php echo use_helper('Date'); ?>
<?php
  echo format_datetime($manifestation->happens_at < $manifestation->reservation_begins_at
    ? $manifestation->happens_at
    : $manifestation->reservation_begins_at, 'EEE d MMM yyyy HH:mm');
?>
