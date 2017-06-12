<?php

namespace Sowp\ApiBundle\Service;

use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Pagerfanta\Pagerfanta;
use Sowp\ApiBundle\Response\ApiResponse;
use Sowp\ApiBundle\Response\ErrorResponse;
use Sowp\ApiBundle\Response\Link;
use Sowp\ArticleBundle\Entity\Article;
use Sowp\CollectionBundle\Entity\Collection;
use Sowp\NewsModuleBundle\Entity\News;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class ApiHelper
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Packages
     */
    private $templateHelper;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        Serializer $serializer,
        RouterInterface $router,
        EntityManager $entityManager,
        Packages $templateHelper,
        RequestStack $stack
    )
    {
        $this->setSerializer($serializer);
        $this->setRouter($router);
        $this->setEm($entityManager);
        $this->setTemplateHelper($templateHelper);
        $this->setRequestStack($stack);
    }


    /**
     * @return RequestStack
     */
    public function getRequestStack(): RequestStack
    {
        return $this->requestStack;
    }

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return Packages
     */
    public function getTemplateHelper(): Packages
    {
        return $this->templateHelper;
    }

    /**
     * @param Packages $templateHelper
     */
    public function setTemplateHelper(Packages $templateHelper)
    {
        $this->templateHelper = $templateHelper;
    }

    /**
     * @return EntityManager
     */
    public function getEm(): EntityManager
    {
        return $this->em;
    }

    /**
     * @param EntityManager $em
     */
    public function setEm(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return Serializer
     */
    public function getSerializer(): Serializer
    {
        return $this->serializer;
    }

    /**
     * @param Serializer $serializer
     */
    public function setSerializer(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param $code
     * @param $data
     * @param array $links
     * @return Response
     */
    public function createApiResponse($code, $data, array $links)
    {
        $obj = new ApiResponse();
        $obj->setResponseCode($code);
        $obj->setData($data);
        $obj->setLinks($this->convertLinksArray($links));

        $data_serialized = $this->getSerializer()->serialize($obj, 'json');

        return new Response($data_serialized, $code, [
            'content-type' => 'application/json'
        ]);
    }

    /**
     * @param $code
     * @param $message
     * @param array $links
     * @return Response
     */
    public function createErrorResponse($code, $message, array $links)
    {
        $obj = new ErrorResponse();
        $obj->setResponseCode($code);
        $obj->setMsg($message);
        $obj->setLinks($this->convertLinksArray($links));

        $data_serialized = $this->getSerializer()->serialize($obj, 'json');

        return new Response($data_serialized, $code, [
            'content-type' => 'application/json'
        ]);
    }

    /**
     * @param Pagerfanta $pag
     * @param $key
     * @param $route
     * @return array
     */
    public function generateNavLinks(Pagerfanta $pag, $key, $route)
    {
        $a = [];

        if ($pag->hasNextPage()) {
            $a['next'] = $this->getRouter()->generate($route, [
                    $key => $pag->getNextPage(),
                ],
            Router::ABSOLUTE_URL
            );
        }

        if ($pag->hasPreviousPage()) {
            $a['previous'] = $this->getRouter()->generate($route, [
                    $key => $pag->getPreviousPage(),
                ],
                Router::ABSOLUTE_URL
            );
        }

        $a['current'] = $this->getRouter()->generate($route, [
                $key => $pag->getCurrentPage(),
            ],
            Router::ABSOLUTE_URL
        );

        return $a;
    }

    /**
     * @param array $links
     * @return array
     */
    private function convertLinksArray(array $links)
    {
        $a = [];

        foreach ($links as $rel => $href) {
            $a[] = new Link($rel, $href);
        }

        return $a;
    }

    public function getShowLinkForEntity($entity, $absolute = true)
    {
        if ($entity instanceof Collection) {
            return $this->getRouter()->generate('api_collections_show',[
                'id' => $entity->getId()
            ],$absolute ? Router::ABSOLUTE_URL : Router::RELATIVE_PATH);
        } elseif ($entity instanceof Article) {
            return $this->getRouter()->generate('api_article_show', [
                'id' => $entity->getId()
            ],$absolute ? Router::ABSOLUTE_URL : Router::RELATIVE_PATH);
        } elseif ($entity instanceof News) {
            return $this->getRouter()->generate('api_news_show', [
                'id' => $entity->getId()
            ], $absolute ? Router::ABSOLUTE_URL : Router::RELATIVE_PATH);
        }
    }

    public function createAttachmentsLinks($json)
    {
        /** @var Request $request */
        $request = $this->getRequestStack()->getCurrentRequest();

        if (!$request) {
            return [];
        }

        return \array_map(function ($a) use ($request) {
            $a['file'] =
                $request->getSchemeAndHttpHost() .
                $this->templateHelper->getUrl('uploads/attachments/'.$a['file']['filename']);
            return $a;
        }, $json);
    }
}