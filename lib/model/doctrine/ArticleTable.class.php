<?php

/**
 * ArticleTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class ArticleTable extends Doctrine_Table {

  /**
   * Returns an instance of this class.
   *
   * @return object ArticleTable
   */
  public static function getInstance() {
    return Doctrine_Core::getTable('Article');
  }

  /**
   * 
   * Fetch the list of all articles sorted by date.
   * 
   * @param int $asso
   */
  public function getArticlesList($asso = null) {
    $q = $this->createQuery('a')
            ->select('a.*, as.id, p.couleur')
            ->leftJoin('a.Asso as')
            ->leftJoin('as.Pole p')
            ->addOrderBy('a.created_at DESC');

    if (!is_null($asso))
      /*if ($asso->isPole())
        $q = $q->leftJoin('Asso as')->where("as.pole_id = ?", $asso->getPrimaryKey());
      else*/
        $q = $q->where("a.asso_id = ?", $asso->getPrimaryKey());

    return $q;
  }

  public function getLastArticles($count = 3) {
    $q = $this->getArticlesList()
            ->limit($count);
    return $q;
  }

    public function getArticlesFollowed($user_id){
      $q = $this->createQuery('ar')
            ->select ('as.name, ar.*')
            ->where('ar.asso_id = as.id')
            ->andWhere('ab.user_id = ?', $user_id)
            ->leftJoin('ar.Asso as')
            ->leftJoin('as.Abonnement ab')
            ->orderBy('ar.updated_at desc')
            ->limit(3);            
      return $q;
    }
    
    public function getAbonnementsFollowed($user_id){
      $res = Doctrine_Manager::getInstance()
       ->getConnection('doctrine')
       ->getDbh()
       ->query('(SELECT a.id, a.name, a.summary, a.created_at, a.asso_id, asso.name AS assoName, \'article\'
                FROM article a, abonnement ab, asso asso
                WHERE (a.asso_id = ab.asso_id AND ab.user_id = '.$user_id.' AND a.asso_id = asso.id)
                limit 5)
                UNION (SELECT e.id, e.name, e.summary, e.created_at, e.asso_id, asso.name AS assoName, \'event\'
                FROM event e, abonnement ab, asso asso
                WHERE (e.asso_id = ab.asso_id AND ab.user_id = '.$user_id.' AND e.asso_id = asso.id) 
                LIMIT 5)
                ORDER BY created_at DESC')
       ->fetchAll(PDO::FETCH_ASSOC);
      return $res;
    } 
       
   
}
