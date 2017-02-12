<?php

namespace Sowp\SearchModuleBundle\Search;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Sowp\SearchModuleBundle\Search\SearchProviderInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class SearchProviderAbstract implements SearchProviderInterface
{
    protected $typeName;
    protected $queryAlias;

    /** @var EntityManager  */
    protected $em;

    /** @var EntityRepository*/
    protected $eRepo;

    /** @var \Doctrine\ORM\QueryBuilder for results in single mode */
    protected $qbSingle;

    protected $resultsSingle = [];

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    abstract function getLink();

    /**
     * This function must query all important
     * repositories in its module and puts
     * query builders into properties of $this.
     * It calls function that retrieve results or does it itself.
     * Also on each call it clears currently stored results&query builders.
     *
     * @param string $query
     * @return bool
     */
    public function search($query)
    {
        if (!$query) {
            throw new NotFoundHttpException();
        }

//        $this->resultsMulti = [];
        $this->resultsSingle = [];
//        $this->qbMulti = null;
        $this->qbSingle = null;

//        try {
//
//            $this->qbSingle = $this->nRepo->createQueryBuilder($this->queryAlias)
//                ->addSelect("MATCH_AGAINST ($this->queryAlias.title, $this->queryAlias.content, :phrase) as score")
//                ->andWhere('score > 0.01')
//                ->setParameter('phrase', $query);
//
//            $this->injectLink('%s/%s', '/admin/article', 'slug');
////            $this->extractResults();
//
//        } catch (\Exception $e) {
//            return false;
//        }


        return true;
    }

    /**
     * @param QueryBuilder $qb
     * @param $format
     * @param array ...$params
     */
    final protected function injectLink(\Closure $closure)
    {

        foreach ($this->resultsSingle as &$item) {
            $closure($item);
        }
//        foreach ($params as &$param) {
//            $param = $this->queryAlias . '.' . $param;
//        }
//
//        $value = sprintf($format, $path, ...$params);
//        $this->getQbSingle()->addSelect("$value as link");
    }

    final protected function extractResults()
    {
        try {
            $this->resultsSingle =
                $this->qbSingle
                    ->getQuery()
                    ->getResult();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Munction getResults*() must be called after $this->search($query)
     * Its role is to take results from search and return them as array
     * eith one value -  array with results under $this->getTypeName()
     *
     *  return [
     *      $typeName => [
     *          QueryBuilder->getResult(),
     *          QueryBuilder->getResult(),
     *          (...)
     *      ]
     * ];
     *
     * At \Sowp\SearchModuleBundle\Search\SearchManager
     * all these arrays will be merged to provide something like:
     * [
     *  typename => [Result,Result],
     *  typename => [Result],(Positive number of Results)
     *  (...)
     *
     * @return array
     */
    public function getResultsSingle()
    {
        return [$this->getTypeName() => $this->resultsSingle];
    }

//    /**
//     * @return array
//     */
//    public function getResultsMulti()
//    {
//        return [$this->getTypeName() => $this->resultsMulti];
//    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        return $this->typeName;
    }

//    /**
//     * @return \Doctrine\ORM\QueryBuilder
//     */
//    public function getQbMulti(): \Doctrine\ORM\QueryBuilder
//    {
//        return $this->qbMulti;
//    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQbSingle(): \Doctrine\ORM\QueryBuilder
    {
        return $this->qbSingle;
    }

}