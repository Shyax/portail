<?php

/**
 * GaleriePhotoTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class GaleriePhotoTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object GaleriePhotoTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('GaleriePhoto');
    }
    public function getEventGaleries($event){
        $q = $this->createQuery('gal')
            ->select('gal.*')
            ->where('gal.event_id = ?', $event->getPrimaryKey());
        return $q;
    }
}