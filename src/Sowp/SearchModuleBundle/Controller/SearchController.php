<?php

namespace Sowp\SearchModuleBundle\Controller;

use Sowp\SearchModuleBundle\SearchModuleBundle;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class SearchController extends Controller
{
    /**
     * @Route("/search", name="sowp_searchbundle_search")
     */
    public function searchAction(Request $request)
    {
        $q = $request->query->get("q", false);
        $results = [];

        if (!$q || empty($q)) {
            throw new NotFoundHttpException();
        }

        $sm = $this->get("sowp.bip.search_manager");

        /**
         * @var $provider SearchModuleBundle\Search\SearchResultInterface
         */
        foreach ($sm->getProviders() as $provider) {
            $provider->search($q);
            $results[$provider->getTypeName()] = $provider->getResultsSingle();
            // Whata data need a put into template
            // to use |render_search_entry()
            // assume that each module can provide
            // diffrent link types like:
            // - custom/{slug}
            // - /{slug}-{id}
            // so on;
        }

        return $this->render('SearchModuleBundle::search.html.twig',[
            'query' => $q,
            'results' => $results
        ]);
    }
}