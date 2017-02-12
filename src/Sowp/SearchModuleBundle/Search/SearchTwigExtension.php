<?php

namespace Sowp\SearchModuleBundle\Search;

use Sowp\SearchModuleBundle\Search\SearchResultInterface;

class SearchTwigExtension extends \Twig_Extension
{
    private $twig;

    public function __construct(\Twig_Environment $te)
    {
        $this->twig = $te;
    }

//    public function getFunctions()
//    {
//        return [
//            new \Twig_SimpleFunction(
//                'render_search_provider',
//                [
//                    $this,
//                    'renderSearchProvider'
//                ]
//            )
//        ];
//    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'render_search_entry',
                [
                    $this,
                    'renderSearchProvider'
                ]
            )
        ];
    }

    public function renderSearchProvider(array $a)
    {
        //list($entity, $link) = each($a);

//        return "<h3><a href=\"$link\">  </a></h3>";
//            #{#<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Cumque repellat dolore iusto quos similique vel. Quia accusantium incidunt at dolorum, ipsum quo. Temporibus ad officia magnam nesciunt mollitia, quod porro?</p>#}

    }

//    public function titleSearchProvider(SearchResultInterface $provider)
//    {
//
//    }
}