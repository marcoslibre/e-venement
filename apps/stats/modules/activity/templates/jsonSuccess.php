<?php
/**********************************************************************************
*
*       This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
  $json = $sf_data->getRaw('lines');
  switch ( $denomination ) {
  case 'hour':
    $json['csvHeaders'] = array(
      __('Hour of the day'),
      __('Processed tickets'),
    );
    break;
  default:
    $json['csvHeaders'] = array(
      __('Date'),
      __('Passing'),
      __('Printed'),
      __('Ordered'),
      __('Asked')
    );
    $json['legends'] = array(
      'ordered' => __('Ordered'),
      'printed' => __('Printed'),
      'passing' => __('Passing'),
    );
    break;
  }

  echo json_encode($json);
?>
