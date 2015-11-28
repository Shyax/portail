<?php

/**
 * ReservationTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class ReservationTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object ReservationTable
     */
  public static function getInstance()
  {
    return Doctrine_Core::getTable('Reservation');
  }
    
  public function getAllReservation()
  {
    $q = $this->createQuery('q');
            
    return $q;
  }
    
  public function getReservationById($id)
  {  
    $q = $this->createQuery('q')
              ->where('q.id = ?', $id)
              ->addOrderBy('date');
          
    return $q;
  }
    
  public function getReservationBySalle($salle)
  {
    $q = $this->createQuery('a')
              ->where('a.id_salle = ?', $salle)
              ->addOrderBy('a.date');
            
    return $q;
  }
    
  public function isReservationExist($id)
  {
    $q = $this->createQuery()
              ->where('id = ?', $id)->execute();
            
    return (count($q) > 0);
  }
    
  public function isReservationNoValidExist($id)
  {
    $q = $this->createQuery()
              ->where('id = ?', $id)
              ->andWhere('estValide=?', 0)->execute();
            
    return (count($q) > 0);
  }
  
  public function getReservationNoValide()
  {
    $q = $this->createQuery('a')
              ->where('a.estValide=?', 0)
              ->addOrderBy('a.date','ASC');
          
    return $q;
  }

  public function getReservationByPole($pole)
  {
    $q = $this->createQuery('a')
              ->leftJoin('a.Salle s')
              ->leftJoin('s.Pole p')
              ->where('p.id=?',$pole)
              ->addOrderBy('a.date','ASC');
        
    return $q;  
  }


  public function getStatJours()
  {
    $q = Doctrine_Manager::getInstance()->getCurrentConnection();
    $result = $q->execute("select r.*, count(r.id) as count_resa, weekday(r.date) as dow from reservation r group by dow order by dow");

    return $result;
  }

  public function getStatMois()
  {
    $q = Doctrine_Manager::getInstance()->getCurrentConnection();
    $result = $q->execute("select r.*, count(r.id) as count_resa, month(r.date) as month from reservation r group by month order by month");

    return $result;
  }
    
  // Fonctions utilisées dans la formulaire de réservation partie frontend.
    
  public function isChevauchementFin($date,$id_salle,$heuredebut,$heurefin)
  {
    $q = $this->createQuery()
              ->select('count(*)')
               ->from('Reservation r')
              ->where('r.date = ?', $date)
              ->andWhere('r.id_salle = ?', $id_salle)
              ->andWhere('r.heurefin > ?', $heuredebut)
              ->andWhere('r.heurefin <= ?', $heurefin);
        
      return $q;
  }
    
  public function isChevauchementDebut($date,$id_salle,$heuredebut,$heurefin)
  {
    $q = $this->createQuery()
              ->select('count(*)')
              ->from('Reservation r')
              ->where('r.date = ?', $date)
              ->andWhere('r.id_salle = ?', $id_salle)
              ->andWhere('r.heuredebut >= ?', $heuredebut)
              ->andWhere('r.heuredebut < ?', $heurefin);
        
    return $q;
  }
    
  public function isChevauchementInterne($date,$id_salle,$heuredebut,$heurefin)
  {
    $q = $this->createQuery()
              ->select('count(*)')
              ->from('Reservation r')
              ->where('r.date = ?', $date)
              ->andWhere('r.id_salle = ?', $id_salle)
              ->andWhere('r.heuredebut < ?', $heuredebut)
              ->andWhere('r.heurefin > ?', $heurefin);
        
    return $q;
  }
    
  public function isChevauchementFinUpdate($date,$id_salle,$heuredebut,$heurefin,$id)
  {
    $q = $this->createQuery()
              ->select('count(*)')
              ->from('Reservation r')
              ->where('r.date = ?', $date)
              ->andWhere('r.id_salle = ?', $id_salle)
              ->andWhere('r.heurefin > ?', $heuredebut)
              ->andWhere('r.heurefin <= ?', $heurefin)
              ->andWhere('r.id != ?',$id); 
        
    return $q;
  }
    
  public function isChevauchementDebutUpdate($date,$id_salle,$heuredebut,$heurefin,$id)
  {
    $q = $this->createQuery()
              ->select('count(*)')
              ->from('Reservation r')
              ->where('r.date = ?', $date)
              ->andWhere('r.id_salle = ?', $id_salle)
              ->andWhere('r.heuredebut >= ?', $heuredebut)
              ->andWhere('r.heuredebut < ?', $heurefin)
              ->andWhere('r.id != ?',$id); 
        
    return $q;
  }
    
  public function isChevauchementInterneUpdate($date,$id_salle,$heuredebut,$heurefin,$id)
  {
    $q = $this->createQuery()
              ->select('count(*)')
              ->from('Reservation r')
              ->where('r.date = ?', $date)
              ->andWhere('r.id_salle = ?', $id_salle)
              ->andWhere('r.heuredebut < ?', $heuredebut)
              ->andWhere('r.heurefin > ?', $heurefin)
              ->andWhere('r.id != ?',$id); 
        
    return $q;
  }
    
  public function getReservationPourAssoPourDateUpdate($id_asso,$date,$id)
  {
    $q = $this->createQuery()
              ->from('Reservation r')
              ->where('r.id_asso = ?', $id_asso)
              ->andWhere('r.date = ?', $date)
              ->andWhere('r.id != ?',$id);
        
    return $q;
  }
    
  public function getReservationPourAssoPourDate($id_asso,$date)
  {
    $q = $this->createQuery()
              ->from('Reservation r')
              ->where('r.id_asso = ?', $id_asso)
              ->andWhere('r.date = ?', $date);
        
    return $q;
  }
    
  public function isJourLibre($date,$id_salle)
  {
    $q = $this->createQuery()
              ->select('count(*)')
              ->from('Reservation r')
              ->where('r.date = ?', $date)
              ->andWhere('r.id_salle = ?', $id_salle)
              ->andWhere('r.allday = ?', 1);
        
    return $q;
  }
    
  public function isJourLibreUpdate($date,$id_salle,$id)
  {
    $q = $this->createQuery()
              ->select('count(*)')
              ->from('Reservation r')
              ->where('r.date = ?', $date)
              ->andWhere('r.id_salle = ?', $id_salle)
              ->andWhere('r.id != ?', $id)
              ->andWhere('r.allday = ?', 1);
        
    return $q;
  }
  
  public function getReservationsBySalleBetweenDates($salle, $start, $end)
  {
    $q = $this->createQuery('a')
              ->where('a.id_salle = ?', $salle)
              ->andWhere('a.date >= FROM_UNIXTIME(?)', $start)
              ->andWhere('a.date <= FROM_UNIXTIME(?)', $end)
              ->addOrderBy('a.date');
            
    return $q;
  }
}
