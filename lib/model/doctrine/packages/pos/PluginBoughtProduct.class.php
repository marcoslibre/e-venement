<?php

/**
 * PluginBoughtProduct
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginBoughtProduct extends BaseBoughtProduct
{
  public function preDelete($event)
  {
    if ( $this->member_card_id )
    {
      if ( $this->MemberCard->active )
        throw new liEvenementException('You cannot remove a product linked with a member card, you should better try to remove the member card...');
      else
        $this->MemberCard->delete();
    }
  }
  public function preSave($event)
  {
    if ( !$this->isModified() )
      return parent::preSave($event);
    
    // if the item is not being bought or is bought already, modifications are not allowed
    $mods = $this->getModified();
    if ( $this->integrated_at && !isset($mods['integrated_at']) )
    {
      error_log('Trying to modify the #'.$this->id.' item which has been bought already.');
      //throw new liEvenementException('Trying to modify the #'.$this->id.' item which has been bought already.');
      return;
    }
    
    parent::preSave($event);
    
    if ( !$this->vat_id && $this->product_declination_id )
      $this->Vat = $this->Declination->Product->Vat;
    if ( is_null($this->vat) && !$this->vat_id )
      throw new liEvenementException('Trying to set VAT on a BoughtProduct w/o prerequisites...');
    if ( is_null($this->vat) )
      $this->vat = $this->Vat->value ? $this->Vat->value : 0;
    if ( !$this->price_name )
      $this->price_name = (string)$this->Price;
    
    if ( !$this->value )
      $this->value = $this->getValueFromSchema();
    
    if ( !$this->name )
      $this->name = (string)$this->Declination->Product;
    if ( !$this->declination )
      $this->declination = (string)$this->Declination;
    
    if ( !$this->code && $this->product_declination_id )
      $this->code = $this->Declination->code;
    if ( !$this->description_for_buyers
      && $this->product_declination_id && $this->Declination->description_for_buyers )
      $this->description_for_buyers = $this->Declination->description_for_buyers;
    
    // Shipping fees
    if ( !$this->Transaction->with_shipment )
    {
      $this->shipping_fees = 0;
      $this->shipping_fees_vat = 0;
    }
    elseif ( $this->product_declination_id )
    {
      $this->shipping_fees = $this->Declination->Product->shipping_fees;
      $this->shipping_fees_vat = $this->Declination->Product->ShippingFeesVat->value;
    }
    
    // VAT
    if ( $this->product_declination_id )
      $this->vat = $this->Declination->Product->Vat->value;
    
    // decrease the stock of this declination
    $mods = $this->getModified();
    if ( (isset($mods['destocked']) || isset($mods['integrated_at']))
      && $this->product_declination_id
      && $this->Declination->use_stock )
    {
      // if integrating
      if ( isset($mods['integrated_at']) )
      {
        $this->Declination->stock = $this->Declination->stock + (!$this->destocked && $this->integrated_at ? -1 : 1);
        $this->destocked = $this->integrated_at ? true : false;
      }
      // if not currently integrating, but it needs to be count in the stock's outputs
      elseif ( isset($mods['destocked']) )
      {
        if ( !$this->isNew() )
          $this->Declination->stock = $this->Declination->stock + ($this->destocked ? -1 : 1);
        elseif ( $this->destocked )
          $this->Declination->stock = $this->Declination->stock - 1;
      }
      $this->Declination->save();
    }
    
    // Member cards
    if ( $this->Declination->MemberCardTypes->count() == 1 && !$this->member_card_id && !is_null($this->integrated_at) )
    {
      $this->MemberCard = new MemberCard;
      $this->MemberCard->active = true;
      $this->MemberCard->Transaction = $this->Transaction;
      $this->MemberCard->Contact = $this->Transaction->Contact;
      $this->MemberCard->MemberCardType = $this->Declination->MemberCardTypes[0];
    }
    
    return parent::preSave($event);
  }
  
  public function postDelete($event)
  {
    // increase the stock with this declination
    if ( $this->product_declination_id
      && $this->Declination->use_stock
      && $this->destocked )
    {
      $this->Declination->stock = $this->Declination->stock + 1;
      $this->Declination->save();
    }
    return parent::postDelete($event);
  }
  
  public function getValueFromSchema()
  {
    $value = NULL;
    foreach ( $this->Declination->Product->PriceProducts as $p )
    if ( $this->price_id == $p->price_id )
      $value = $p->value ? $p->value : 0; // free price here
    
    return $value;
  }
  public function isSold()
  {
    return !is_null($this->integrated_at);
  }
  
  public function getIndexesPrefix()
  {
    return strtolower(get_class($this));
  }
}
