<?php

namespace Sowp\ArticleBundle\SearchProvider;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Sowp\ArticleBundle\Entity\Article;
use Sowp\SearchModuleBundle\Search\SearchProviderAbstract;

class SearchProvider extends SearchProviderAbstract
{
    protected $typeName = 'Article';
    protected $queryAlias = 'article';

    public function __construct(EntityManager $em)
    {
        parent::__construct($em);
        $this->eRepo = $this->em->getRepository('Sowp\ArticleBundle\Entity\Article');
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
            $this->injectLink(function (array &$a) { $a['link'] = "/admin/article/" . $a['link']; });

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