<?php

/**
 * PluginOrganismTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PluginOrganismTable extends AddressableTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object PluginOrganismTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('PluginOrganism');
    }
}