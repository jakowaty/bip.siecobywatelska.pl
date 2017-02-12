<?php

namespace Sowp\NewsModuleBundle\SearchProvider;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Sowp\NewsModuleBundle\Entity\News;
use Sowp\SearchModuleBundle\Search\SearchProviderAbstract;
use Sowp\SearchModuleBundle\Search\SearchProviderInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
class SearchProvider extends SearchProviderAbstract
{
    protected $typeName = 'News';
    protected $queryAlias = 'news';

    public function __construct(EntityManager $em)
    {
        parent::__construct($em);
        $this->eRepo = $this->em->getRepository('Sowp\NewsModuleBundle\Entity\News');
    }

    public function search($query)
    {
        parent::search($query);

        try {

            $this->qbSingle = $this->eRepo->createQueryBuilder($this->queryAlias)
                ->addSelect("$this->queryAlias.slug as link")
                ->andWhere("MATCH_AGAINST ($this->queryAlias.title, $this->queryAlias.content, :phrase) > 0.01")
                ->setParameter('phrase', $query);

            $this->extractResults();
            $this->injectLink(function (array &$a) { $a['link'] = "/admin/wiadomosci/" . $a['link']; });

        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function getLink()
    {
        // TODO: Implement getLink() method.
    }
}