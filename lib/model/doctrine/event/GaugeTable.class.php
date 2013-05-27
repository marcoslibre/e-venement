<?php

/**
 * GaugeTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class GaugeTable extends PluginGaugeTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object GaugeTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Gauge');
    }
  
  public function createQuery($alias = 'g',$full = true)
  {
    $ws = $alias != 'ws' ? 'ws' : 'ws1';
    
    $q = parent::createQuery($alias);
    $where = "     duplicating IS NULL
               AND cancelling IS NULL
               AND gauge_id = $alias.id";
    
    if ( $full )
    $q->select("$alias.*")
      ->leftJoin("$alias.Workspace ws")
      ->addSelect("(SELECT count(*) AS nb
                    FROM Ticket tck1
                    WHERE (printed_at IS NOT NULL OR integrated_at IS NOT NULL)
                      AND $where
                      AND id NOT IN (SELECT tck11.cancelling FROM Ticket tck11 WHERE tck11.cancelling IS NOT NULL)
                   ) AS printed")
      ->addSelect("(SELECT count(*) AS nb
                    FROM Ticket tck2
                    WHERE printed_at IS NULL AND integrated_at IS NULL
                      AND transaction_id IN (SELECT o2.transaction_id FROM Order o2)
                      AND $where
                      AND id NOT IN (SELECT tck22.cancelling FROM Ticket tck22 WHERE tck22.cancelling IS NOT NULL)
                   ) AS ordered")
      ->addSelect("(SELECT count(*) AS nb
                    FROM Ticket tck3
                    WHERE printed_at IS NULL AND integrated_at IS NULL
                      AND transaction_id NOT IN (SELECT o3.transaction_id FROM Order o3)
                      AND $where
                      AND id NOT IN (SELECT tck33.cancelling FROM Ticket tck33 WHERE tck33.cancelling IS NOT NULL)
                   ) AS asked");
    return $q;
  }
  
  public function retrieveList()
  {
    return $this->createQuery('g')
      ->leftJoin('g.Manifestation m')
      ->leftJoin('m.Event e')
      ->addSelect('ws.*, m.*, e.*')
      ->addSelect('e.name AS event_name')
      ->addSelect('m.happens_at AS happens_at')
      ->addSelect('ws.name AS workspace_name');
  }
}
