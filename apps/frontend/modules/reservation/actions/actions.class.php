<?php

/**
 * reservation actions.
 *
 * @package    simde
 * @subpackage reservation
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class reservationActions extends sfActions
{
 /*
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
	$this->userIdentified = false;
	if ($this->getUser()->isAuthenticated())
	{
		$this->userIdentified= true;	      
		$UserID = $this->getUser()->getGuardUser()->getId();
		$this->UserID=$this->getUser()->getGuardUser()->getId();
		$this->idSalle = $request->getUrlParameter("id", -1);
		$values = array('UserID'=> $this->UserID,'idSalle'=> $this->idSalle);


		$this->salle = SalleTable::getInstance()->getSalleById($this->idSalle)->execute()[0]; 

		//test si la salle appartient bien aux pole de l'utilisateur
		if($this->idSalle != -1)
		{
			//BDE toujours dans les poles de l'utilisateur
			$this->polesUser = array("1");

			$this->assosUser = AssoTable::getInstance()->getMyAssos($this->getUser()->getGuardUser()->getId())->execute();
			if($this->assosUser)
			{
				foreach($this->assosUser as $asso)
				{
					$pole = PoleTable::getInstance()->getOneById($asso->getPoleId());
					if(!in_array($pole->getId(), $this->polesUser))
						array_push($this->polesUser, $pole->getId());
				}
			}
			$this->forward404Unless(in_array($this->salle->getIdPole(), $this->polesUser));
		}	
		
		// on ne récupère que les noms d'assos correspondant aux pôles 
		// pourquoi prend le login et pas le nom ?
		$this->query = AssoTable::getInstance()->getMyAssosName($UserID,$this->idSalle);
		//var_dump($this->query);
		
		$this->form = new ReservationForm(array(),$values);

		$this->ok = false;
		$this->afficherErreur= false;
  
  		if ($request->isMethod('post'))
  		{
  			$this->form->bind($request->getParameter($this->form->getName()));
			var_dump($request->getParameter($this->form->getName()));
			if ($this->form->isValid())
			{
				var_dump("valide");
				$this->reservation=$this->form->save(); // Save into database 
				$this->ok=true;
			}
			else
			{
			      var_dump("NOvalide");
			      $this->afficherErreur= true;
			}
		}
  	      
	}
	 
  }
  
  public function executeList(sfWebRequest $request)
  {
	$this->user = $this->getUser()->getGuardUser();
	
	$idSalle = $request->getUrlParameter('id',-1);
	
	if($idSalle == -1)
	{
		//BDE toujours dans les poles de l'utilisateur
		$this->polesUser = array("1");

		$this->assosUser = AssoTable::getInstance()->getMyAssos($this->getUser()->getGuardUser()->getId())->execute();
		
		if($this->assosUser)
		{
			foreach($this->assosUser as $asso)
			{
				$pole = PoleTable::getInstance()->getOneById($asso->getPoleId());
				if(!in_array($pole->getId(), $this->polesUser))
					array_push($this->polesUser, $pole->getId());
			}
		}
		$this->sallesUser = array();
		    foreach($this->polesUser as $pole)
		    {
		   	 $salles = SalleTable::getInstance()->getSalleByPole($pole)->execute();
			 foreach($salles as $salle)
				array_push($this->sallesUser, $salle->getId());
		    }

		$this->reservations = array();	
		foreach($this->sallesUser as $salle)
		{	
			$resa = ReservationTable::getInstance()->getReservationBySalle($salle)->execute();
			foreach($resa as $res)
				array_push($this->reservations, $res);
		}
	}
	else
	{
		$this->user = $this->getUser()->getGuardUser();	
  		$this->reservations = ReservationTable::getInstance()->getReservationBySalle($idSalle)->execute();
	}
  }


  public function executeShow(sfWebRequest $request)
  {
  		$id = $request->getUrlParameter("id",-1);
  
  		if ($id == -1)
  			$this->forward404Unless(false);
  
  		$this->forward404Unless(ReservationTable::getInstance()->isReservationExist($id));
  
		$this->reservation = ReservationTable::getInstance()->getReservationById($id)->execute()[0];
  }
  
  public function executeFormNew(sfWebRequest $request)
  {
	if (!$request->isXmlHttpRequest())
	{
	  $this->forward404Unless(false);
	}
  
	$idSalle = $request->getParameter("idSalle", -1);
	$UserID = $request->getParameter("UserID", -1);
	
	
	// création du tableua à passer au constructeur du formulaire de réservation
	$values = array('UserID'=> $UserID,'idSalle'=> $idSalle);
    
	$this->form = new ReservationForm(array(),$values);
	
	// TO DO : Voir si la salle appartient au BDE ou non et en fonction donner possiblité de rentrer une asso ou non.
	$PoleId= SalleTable::getInstance()->getSalleById($idSalle)->execute()[0]->getIdPole();
	if($PoleId==1){
	    $this->form->getWidget('id_asso')->setOption('add_empty',true);
	}
	
	// valeur par défaut pour les champs cachés.
	$this->form->setDefault('id_salle', $idSalle);
	$this->form->setDefault('id_user_reserve', $UserID);
	$this->form->setDefault('estvalide', 0); // TODO si 2 semaines avant ou pas
			
	$this->ok = false;
	$this->afficherErreur= false;


	return $this->renderPartial('reservation/formNew',array('form'=>$this->form,'idSalle'=>$idSalle));
	 
  }


}
