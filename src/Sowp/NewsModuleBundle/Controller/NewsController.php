<?php

namespace Sowp\NewsModuleBundle\Controller;

use Sowp\NewsModuleBundle\Entity\News;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * News controller.
 *
 * @Route("/admin/wiadomosci")
 */
class NewsController extends Controller
{
    /** constant int pagerfanta*/
    const NEWS_PER_PAGE = 4;

    /**
     * Lists all news entities.
     *
     * @Route("/", name="sowp_newsmodule_news_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NewsModuleBundle:News');
        $page = $request->query->get('page', 1);
        $pagerAdapter = new DoctrineORMAdapter($repo->getQueryBuilderAll(), false);
        $news = new Pagerfanta($pagerAdapter);
        $news->setMaxPerPage(self::NEWS_PER_PAGE);
        $news->setCurrentPage($page);

        return $this->render('NewsModuleBundle:news:index.html.twig', array(
            'news' => $news,
        ));
    }

    /**
     * Creates a new news entity.
     *
     * @Route("/dodaj", name="sowp_newsmodule_news_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $news = new News();
        $form = $this->createForm('Sowp\NewsModuleBundle\Form\NewsType', $news);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($news);
                $em->flush();
                $this->addFlash('notice', 'Message added');

                return $this->redirectToRoute('sowp_newsmodule_news_show', ['slug' => $news->getSlug()]);
            }
        }

        return $this->render('NewsModuleBundle:news:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a news entity.
     *
     * @Route("/{slug}/dupa", name="sowp_newsmodule_news_show")
     * @Method("GET")
     */
    public function showAction(News $news)
    {
        if ($news->getDeletedAt() instanceof \DateTime) {
            $restoreForm = $this->createRestoreForm($news);
        } else {
            $deleteForm = $this->createDeleteForm($news);
        }

        return $this->render('NewsModuleBundle:news:show.html.twig', array(
            'news' => $news,
            'delete_form' => isset($deleteForm) ? $deleteForm->createView() : null,
            'restore_form' => isset($restoreForm) ? $restoreForm->createView() : null,
        ));
    }

    /**
     * Displays a form to edit an existing news entity.
     *
     * @Route("/edytuj/{slug}", name="sowp_newsmodule_news_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, News $news)
    {
        $deleteForm = $this->createDeleteForm($news);
        $editForm = $this->createForm('Sowp\NewsModuleBundle\Form\NewsType', $news);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted()) {
            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                //$attachments = $news->getAttachments();
                $em->persist($news);
                $em->flush();
                $this->addFlash('notice', 'Changes saved');

                return $this->redirectToRoute('sowp_newsmodule_news_show', ['slug' => $news->getSlug()]);
            }
        }

        return $this->render('NewsModuleBundle:news:edit.html.twig', array(
            'news' => $news,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a news entity.
     *
     * @Route("/wykasuj/{slug}", name="sowp_newsmodule_news_delete")
     * @Method({"DELETE"})
     */
    public function deleteAction(Request $request, News $news)
    {
        $form = $this->createDeleteForm($news);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($news);
                $em->flush($news);
                $this->addFlash('notice', 'Article deleted');
            }
        }

        return $this->redirectToRoute('sowp_newsmodule_news_index');
    }

    /**
     * Restores a soft deleted entity.
     *
     * @Route("/przywroc/{slug}", name="sowp_newsmodule_news_restore")
     * @Method({"POST"})
     */
    public function restoreAction(Request $request, News $news)
    {
        $form = $this->createRestoreForm($news);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $attachments = $news->getAttachments();
                $news->setAttachments([]);
                $news->setDeletedAt(null);
                $em->persist($news);
                $em->flush();
                $news->setAttachments($attachments);
                $em->persist($news);
                $em->flush();
                $this->addFlash('notice', 'Article restored');

                return $this->redirectToRoute('sowp_newsmodule_news_show', ['slug' => $news->getSlug()]);
            }
        }

        return $this->redirectToRoute('sowp_newsmodule_news_index');
    }

    /**
     * Shows list of revisions for selected news.
     *
     * @Route("/lista-zmian/{slug}", name="sowp_newsmodule_news_revisions_list")
     * @Method({"GET"})
     */
    public function revisionlistAction(News $news)
    {
        $newsId = $news->getId();
        $auditReader = $this->container->get('simplethings_entityaudit.reader');
        $revisions = $auditReader->findRevisions(
            News::class,
            $newsId
        );

        return $this->render('NewsModuleBundle:news:revlist.html.twig', [
            'news' => $news,
            'revisions' => $revisions,
        ]);
    }

    /**
     * Shows detail of selected revision for selected news.
     *
     * @Route("/rewizja/{newsId},{revId}", name="sowp_newsmodule_news_revisions_detail")
     * @Method({"GET"})
     */
    public function revisiondetailAction($newsId, $revId)
    {
        $auditReader = $this->container->get('simplethings_entityaudit.reader');
        $newsRevision = $auditReader->find(
            News::class,
            $newsId,
            $revId
        );

        return $this->render('NewsModuleBundle:news:show.html.twig', [
            'news' => $newsRevision,
            'revision' => $revId,
        ]);
    }

    /**
     * Creates a form to delete a news entity.
     *
     * @param News $news The news entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(News $news)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('sowp_newsmodule_news_delete', array('slug' => $news->getSlug())))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * Creates a form to restore softdeleted news entity.
     *
     * @param News $news The news entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createRestoreForm(News $news)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('sowp_newsmodule_news_restore', array('slug' => $news->getSlug())))
            ->setMethod('POST')
            ->getForm();
    }
}
