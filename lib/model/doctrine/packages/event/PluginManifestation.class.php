<?php

/**
 * PluginManifestation
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginManifestation extends BaseManifestation implements liMetaEventSecurityAccessor
{
  protected static $credentials = array(
    'contact_id' => 'event-reservations-change-contact',
  );
  
  public function duplicate($save = true)
  {
    $manif = $this->copy();
    foreach ( array('id', 'updated_at', 'created_at', 'sf_guard_user_id') as $property )
      $manif->$property = NULL;
    foreach ( array('Gauges', 'PriceManifestations', 'Organizers') as $subobjects )
    foreach ( $this->$subobjects as $subobject )
    {
      $collection = $manif->$subobjects;
      $collection[] = $subobject->copy();
    }
    
    if ( $save )
      $manif->save();
    
    return $manif;
  }
  
  public function preSave($event)
  {
    // converting duration from "1:00" to 3600 (seconds)
    if ( intval($this->duration).'' != ''.$this->duration )
      $this->duration = intval(strtotime($this->duration.'+0',0));
    
    // completing or correcting reservation fields
    if ( !$this->reservation_begins_at
      || $this->reservation_begins_at && $this->reservation_begins_at > $this->happens_at )
      $this->reservation_begins_at = $this->happens_at;
    if ( !$this->reservation_ends_at
      || $this->reservation_ends_at && $this->reservation_ends_at < date('Y-m-d H:i:s',strtotime($this->happens_at)+$this->duration) )
      $this->reservation_ends_at = date('Y-m-d H:i:s',strtotime($this->happens_at)+$this->duration);
    if ( sfContext::hasInstance() )
    {
      $sf_user = sfContext::getInstance()->getUser();
      if ( !$sf_user->hasCredential($this->credentials['contact_id']) && $sf_user->getContact() )
        $this->Applicant = $sf_user->getContact();
    }
    
    parent::preSave($event);
  }
  
  public function postInsert($event)
  {
    $add_prices = false;
    if ( sfContext::hasInstance() )
    {
      $sf_user = sfContext::getInstance()->getUser();
      if ( $sf_user->hasCredential(array('tck-transaction', 'event-admin-price',), false) )
        $add_prices = true;
    }
    else $add_prices = true;
    
    if ( $this->PriceManifestations->count() == 0 && $add_prices )
    foreach ( Doctrine::getTable('Price')->createQuery('p')->andWhere('p.hide = FALSE')->execute() as $price )
    {
      $pm = PriceManifestation::createPrice($price);
      $pm->manifestation_id = $this->id;
      //$pm->save();
      $this->PriceManifestations[] = $pm;
    }
    $this->save();
    
    parent::postInsert($event);
  }
  
  public function getDurationHR()
  {
    if ( intval($this->duration).'' != ''.$this->duration )
      return $this->duration;
    
    $hours = floor($this->duration/3600);
    $minutes = floor($this->duration%3600/60) > 9 ? floor($this->duration%3600/60) : '0'.floor($this->duration%3600/60);
    return $hours.':'.$minutes;
  }
  
  public function getMEid()
  {
    return $this->Event->getMEid();
  }
  
  public static function getCredentials()
  {
    return self::$credentials;
  }
}
