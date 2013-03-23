<?php

/**
 * BudgetCategorie
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    simde
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class BudgetCategorie extends BaseBudgetCategorie
{
	public function getPostesForBudget($budget) {
		return BudgetPosteTable::getInstance()->createQuery('q')
		                                      ->where('q.budget_categorie_id=?', $this->getPrimaryKey())
		                                      ->andWhere('q.budget_id=?', $budget->getPrimaryKey())
		                                      ->andWhere('q.deleted_at IS NULL')
		                                      ->execute();
	}

	public function getTotal() {
		return $this['MontantTotal'] ? $this['MontantTotal'] : 0;
	}
}
