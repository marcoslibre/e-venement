<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
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
*    Foundation, Inc., 5'.$rank.' Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    $this->location_id = $request->getParameter('location_id');
    $this->event_id = $request->getParameter('event_id');
    
    $from = date('Y-m-d H:i', $request->getParameter('start',$time = time()));
    $to = date('Y-m-d H:i', $request->getParameter('end',strtotime('+ 1 month', $time)));
    $this->month_view = strtotime($to) - strtotime($from) >= strtotime('+ 1 month',$time) - $time;
    
    $no_ids = $request->getParameter('no_ids',array());
    if ( !is_array($no_ids) ) $no_ids = array();
    foreach ( $no_ids as $key => $value )
    if ( !$value )
      unset($no_ids[$key]);
    
    $q = Doctrine::getTable('Manifestation')->createQuery('m')
      ->select('m.*, l.*, c.*, e.*, g.*')
      ->leftJoin('m.Color c')
      ->andWhere('m.happens_at >= ?',$from)
      ->andWhere('m.happens_at <  ?',$to)
      ->orderBy('m.happens_at DESC');
    if ( $this->location_id )
      $q->andWhere('(TRUE')
        ->andWhere('m.location_id = ?',$this->location_id)
        ->leftJoin('m.Booking b')
        ->orWhere('b.id = ?',$this->location_id)
        ->andWhere('TRUE)');
    if ( $this->event_id )
      $q->andWhere('m.event_id = ?', $this->event_id);
    elseif ( $this->month_view )
      // if the manifestation's duration > 1 day or the manifestation's reservation starts one day and stops another and duration > 18h
      $q->andWhere("(me.hide_in_month_calendars = FALSE OR m.duration > ? OR DATE_TRUNC('day', m.reservation_begins_at) + '1 day'::interval < DATE_TRUNC('day', m.reservation_ends_at) AND m.duration > ?)", array(24*60*60, 18*60*60, ));
    if ( $no_ids )
      $q->andWhereNotIn('m.id',$no_ids);
    
    EventFormFilter::addCredentialsQueryPart($q);
    $this->manifestations = $q->execute();
    $this->forward404Unless($this->manifestations);
    
    $this->debug = false;
    if ( $request->hasParameter('debug') )
    {
      $this->getResponse()->setContentType('text/html');
      $this->setLayout('layout');
      $this->debug = true;
    }
