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
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
  require('conf.inc.php');
  
  $type = $_GET['type'] == 'bdc' ? 'bdc' : 'facture';
  
  $title = $type == 'bdc' ? 'BdC' : 'Facture';
  $css[] = 'evt/styles/bdc-facture.css';
  
  if ( $user->evtlevel < $config["evt"]["right"]["mod"] )
  {
    $user->addAlert($msg = "Vous n'avez pas un niveau de droits suffisant pour accéder à cette fonctionnalité");
    $nav->redirect($config["website"]["base"]."evt/bill/",$msg);
  }
  
  // vérifs
  if ( ($transac = intval($_GET['transac'])) <= 0 )
  {
    $user->addAlert("Problème dans le numéro d'opération transmis au bon de commande.");
    $user->closeNext();
    $nav->redirect('.');
  }
  
  // passage en base du bon de commande / de la facture
  $data = array(
    'accountid'   => $user->getId(),
    'transaction' => $transac,
    'date'        => '\DEFAULT'
  );
  $correction = false;
  if ( $bd->updateRecordsSimple($type,array('transaction' => $transac),$data) === false )
  {
    if ( $bd->addRecord($type,$data) )
    {
      $user->addAlert("Impossible d'enregistrer le bon de commande / la facture en base.");
      $user->closeNext();
      $nav->redirect('.');
    }
  }
  else $correction = true;
  $request = new bdRequest($bd,'SELECT id FROM '.$type.' WHERE transaction = '.$transac);
  $id = $request->getRecord('id');
  $request->free();
  
  // récup des infos sur la personne
  $query  = ' SELECT p.id, p.prenom, p.nom, p.adresse, p.cp, p.ville, p.pays, p.email, f.date
              FROM transaction AS t
              LEFT JOIN personne_properso p
                     ON p.id = t.personneid
                    AND ( p.fctorgid = t.fctorgid OR t.fctorgid IS NULL AND p.fctorgid IS NULL )
              LEFT JOIN facture f
                     ON f.transaction = t.id
              WHERE t.id = '.$transac;
  $request = new bdRequest($bd,$query);
  $personne = $request->getRecord();
  $request->free();
  if ( intval($personne['id']) <= 0 )
  {
    $user->addAlert('Impossible de faire un bon de commande pour une personne inconnue.');
    $user->closeNext();
    $nav->redirect('.');
  }
  
  // récup des infos sur les billets
  $query  = ' SELECT e.nom, m.date, m.txtva,
                     s.nom AS sitenom, s.cp, s.ville, s.pays,
                     tm.description AS tarif, tm.prix, tm.prixspec,
                     count(*) AS nb
              FROM reservation_pre AS r, manifestation AS m, tarif_manif AS tm, evenement AS e, site AS s
              WHERE m.id = r.manifid
                AND e.id = m.evtid
                AND s.id = m.siteid
                AND tm.id = r.tarifid
                AND r.transaction = '.$transac.'
                AND tm.manifid = m.id
                AND tm.id = r.tarifid
                AND NOT annul
              GROUP BY e.nom, m.date, m.txtva, s.nom, s.cp, s.ville, s.pays, tm.description, tm.prix, tm.prixspec
              ORDER BY e.nom, m.date, s.ville, s.nom, nb DESC, tm.prix';
  $request = new bdRequest($bd,$query);
  
  includeLib('headers');
?>
    <script type="text/javascript" language="javascript">
      function load()
      {
        print();
        <?php if ( !$config['ticket']['let_open_after_print'] ): ?>
        close();
        <?php endif; ?>
      }
    </script>
    <p id="date">le <?php echo date('d/m/Y') ?></p>
<?php
    echo '<div id="seller">';
    $seller = $config['ticket']['seller'];
    $seller[] = $config["mail"]["mailfrom"];
    if ( is_array($seller) )
    {
      if ( $seller['logo'] )
      echo '<p class="logo"><img alt="logo" src="'.htmlsecure($seller['logo']).'" /></p>';
      unset($seller['logo']);
      
      foreach ( $seller as $key => $value )
      if ( $key != 'legal' )
        echo '<p class="'.htmlsecure($key).'">'.htmlsecure($value).'</p>';
    }
    echo '</div>';
    
    // les données client
    echo '<div id="customer">';
    foreach ( $personne as $key => $value )
    if ( $key != 'date' )
      echo '<p class="'.$key.'">'.htmlspecialchars($value).'</p>';
    echo '</div>';
    
    if ( $correction )
    echo '<p id="correction">Cette facture corrige la précédente datée du <span class="date">'.date('d/m/Y',strtotime($personne['date'])).'</span></p>';
    echo '<p id="ids"><span class="num">'.($type == 'bdc' ? 'Bon de commande #' : 'Facture '.$config['ticket']['facture_prefix'] ).'<span class="id">'.htmlsecure($id).'</span></span> <span class="transac">(pour l\'opération <span class="id">#'.$transac.'</span>)</span></p>';
    
    // les lignes du bdc
    $totaux = array('ht' => 0, 'tva' => array(), 'ttc' => 0);
    
    echo '<table id="lines">';
    while ( $rec = $request->getRecordNext() )
    {
      // formatage des infos
      $rec['pu']  = number_format(round($pu = $rec['prixspec'] ? $rec['prixspec'] : $rec['prix'],2),2);
      $rec['ttc'] = number_format(round($ttc = $pu * $rec['nb'],2),2);
      $totaux['ttc'] += $ttc;
      $rec['tva'] = $rec['txtva'];
      $rec['ht']  = number_format(round($ht = $ttc / (1+$rec['txtva']/100),2),2);
      $totaux['ht'] += $ht;
      $totaux['tva'][$rec['tva']] += $ttc - $ht;
      
      $rec['heure'] = date('H:i',strtotime($rec['date']));
      $rec['date']  = date('d/m/Y',strtotime($rec['date']));
      
      echo '<tr>';
      $order = array('nom','date','heure','sitenom','cp','ville','tarif','pu','nb','ttc','tva','ht');
      foreach ( $order as $value )
        echo '<td class="'.$value.'">'.htmlspecialchars($rec[$value]).'</td>';
      echo '</tr>';
    }
    echo '
      <thead><tr>
        <th class="evt">Evènement</th>
        <th class="date">Date</th>
        <th class="heure">Heure</th>
        <th class="salle">Salle</th>
        <th class="cp">CP</th>
        <th class="ville">Ville</th>
        <th class="tarif">Tarif</th>
        <th class="pu">PU TTC</th>
        <th class="nb">Qté</th>
        <th class="ttc">TTC</th>
        <th class="tva">TVA</th>
        <th class="ht">HT</th>
      </tr></thead>';
    echo '</table>';
    
    echo '<div id="totaux">';
      echo '<p class="total"><span>Total HT:</span><span class="float">'.number_format(round($totaux['ht'],2),2).'</span></p>';
      foreach ( $totaux['tva'] as $key => $value )
      echo '<p class="tva"><span>TVA '.$key.'%:</span><span class="float">'.number_format(round($value,2),2).'</span></p>';
      echo '<p class="ttc"><span>Total TTC:</span><span class="float">'.number_format(round($totaux['ttc'],2),2).'</span></p>';
    echo '</div>';
    
    echo '<p id="legal">'.nl2br(htmlsecure($seller['legal'])).'</p>';
    
    $request->free();
    includeLib('footer');
?>
