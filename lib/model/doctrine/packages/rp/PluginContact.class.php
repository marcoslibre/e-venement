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
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

/**
 * PluginContact
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginContact extends BaseContact
{
  public function preSave($event)
  {
    foreach ( $this->Relationships as $key => $rel )
    if ( !$rel['to_contact_id'] )
      unset($this->Relationships[$key]);
    
    return parent::preSave($event);
  }
  
  public function postInsert($event)
  {
    foreach ( $this->Professionals as $pro )
      $pro->contact_id = $this->id;
    
    foreach ( $this->Phonenumbers as $pn )
      $pn->contact_id = $this->id;
    
    parent::postInsert($event);
  }

  public function getIndexesPrefix()
  {
    return strtolower(get_class($this));
  }
  
/**
 * functions getVcard() setVcard()
 * generates a vCard from Contact $this
 * It is optimized for the Zimbra data structure but fits to the vCard standard.
 *
 * Reversible fields:
 *  * n:LastName / Contact::name
 *  * n:;FirstName / Contact::firstname
 *  * n:;;;Prefixes / Contact::title
 *  * adr:;;StreetAddress / Contact::address
 *  * adr:;;;Locality / Contact::address
 *  * adr:;;;;;PostalCode / Contact::postalcode
 *  * adr:;;;;;;Country / Contact::country
 *  * email: / Contact::email -- with smart/random updates from CardDAV to e-venement (under the condition that orders have not changed or changes are understandable)
 *  * rev: / Contact::updated_at
 *  * note: / Contact::description
 *  * uid: / Contact::vcard_uid
 *
 * Non-reversible fields (will be resetted on every change in the e-venement datas)
 *  * org:
 *  * adr:;;;;Region
 *  * adr:TYPE=WORK
 *  * fn:
 *  * tel: / Contact::Phonenumbers -- with smart/random updates from CardDAV to e-venement
 *
 */
  
  /**
   * function setVcard
   * @param $vcard liVCard
   * @return PluginContact $this
   **/
  public function setVcard($vcard, $dummy = NULL)
  {
    if (!( $vcard instanceof liVCard ))
      $vcard = new liVCard($vcard);
    
    parent::setVcard($vcard);
    
    // reset name to add firstname
    $this->firstname = $vcard['n']['FirstName'];
    $this->title = $vcard['n']['Prefixes'];
    
    // HAZARDOUS TREATMENT !!
    /*
    // add pro emails
    foreach ( $this->Professionals as $pro )
    {
      if ( trim($pro->contact_email) )
      $vCard['email'] = array(
        'Value' => $pro->contact_email,
        'Type'  => array('work','internet'),
      );
      if ( trim($pro->Organism->url) )
      $vCard['url'] = $pro->Organism->url;
    }
    */
    
    // description
    $this->description = $vcard['note'];
    
    return $this;
  }

  /**
   * function getVcard()
   * @return liVCard matching $this
   **/
  public function getVcard($dummy = NULL)
  {
    $vCard = parent::getVcard();
    
    // reset name to add firstname
    unset($vCard['n']);
    $vCard['n']  = array(
      'LastName'  => $this->name,
      'FirstName' => $this->firstname,
      'Prefixes'  => $this->title,
    );
    
    // pro orgs
    foreach ( $this->Professionals as $pro )
      $vCard['org'] = array(
        'Name' => (string)$pro->Organism,
        'Unit1' => $pro->department,
        'Unit2' => $pro->name_type,
      );
    if ( $this->Professionals->count() > 0 )
      $vCard['title'] = $this->Professionals[0]->name_type;
    
    // pro addr
    foreach ( $this->Professionals as $pro )
    if ( $pro->Organism->address || $pro->Organism->postalcode || $pro->Organism->city || $pro->Organism->country )
    $vCard['adr'] = $arr = array(
      'ExtendedAddress' => '',
      'POBox'         => '',
      'StreetAddress' => liVCard::realNLToVcfNL(implode("\n", array($pro->Organism->name, $pro->Organism->address,))),
      'Locality'      => $pro->Organism->city,
      'PostalCode'    => $pro->Organism->postalcode,
      'Country'       => $pro->Organism->country,
      'Type'          => array('work', 'postal', 'parcel'),
    );
    
    // tel perso
    foreach ( $this->Phonenumbers as $pn )
    if ( trim($pn->number) )
    $vCard['tel'] = array(
      'Value' => $pn->number,
      'Type' => array(
        'home',
        stripos($pn->name, 'fax') !== false ? 'fax' : 'voice',
      ),
    );
    // tel pro
    foreach ( $this->Professionals as $pro )
    if ( trim($pro->contact_number) )
    $vCard['tel'] = array(
      'Value' => $pro->contact_number,
      'Type' => array(
        'work',
        'voice',
      ),
    );
    
    // add pro emails
    foreach ( $this->Professionals as $pro )
    {
      if ( trim($pro->contact_email) )
      $vCard['email'] = array(
        'Value' => $pro->contact_email,
        'Type'  => array('work','internet'),
      );
      if ( trim($pro->Organism->url) )
      $vCard['url'] = $pro->Organism->url;
    }
    
    // description
    $arr = array();
    if ( sfConfig::has('app_cards_enable') )
    {
      if ( $this->MemberCards->count() > 0 )
      {
        foreach ( $this->MemberCards as $mc )
        if ( strtotime($mc->expire_at) > strtotime('now') && $mc->active )
          $arr[] = (string)$mc;
        if ( $this->description )
        {
          $arr[] = '';
          $arr[] = '- -- ---';
        }
      }
    }
    if ( $this->description )
      $arr[] = str_replace("\r\n", '\n', $this->description);
    $vCard['note'] = implode('\n',$arr);
    
    // END
    return $vCard;
  }
}
