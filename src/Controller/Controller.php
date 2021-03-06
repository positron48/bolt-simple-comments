<?php


namespace Positron48\CommentExtension\Controller;

use Bolt\Controller\Backend\BackendZoneInterface;
use Bolt\Extension\ExtensionController;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Positron48\CommentExtension\Entity\Comment;
use Positron48\CommentExtension\Form\CommentType;
use Positron48\CommentExtension\Repository\CommentRepository;
use Positron48\CommentExtension\Service\ConfigService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Controller extends ExtensionController implements BackendZoneInterface
{
    /**
     * @Route("/comments", name="extension_comment_admin")
     */
    public function index(CommentRepository $commentRepository, Request $request, ConfigService $configService): Response
    {
        $adapter = new QueryAdapter($commentRepository->getAllQuery(), false);
        $comments = new Pagerfanta($adapter);
        $comments->setMaxPerPage(10);

        $page = (int) $request->get('page');
        if($page > 0 && $page <= $comments->getNbPages()) {
            $comments->setCurrentPage($page);
        }

        $context = [
            'title' => 'Comments',
            'comments' => $comments,
            'gravatarEnabled' => $configService->isGravatarEnabled() ?: false,
        ];

        return $this->render('@bolt-simple-comments/comment_admin.html.twig', $context);
    }

    /**
     * @Route("/comments/{id}/edit", name="extension_comment_admin_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Comment $comment, ManagerRegistry $managerRegistry): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManager()->flush();

            return $this->redirectToRoute('extension_comment_admin');
        }

        return $this->render('@bolt-simple-comments/comment_admin_edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
            'recaptchaEnabled' => false,
        ]);
    }

    /**
     * @Route("/{id}", name="comment_admin_delete", methods={"POST"})
     */
    public function delete(Request $request, Comment $comment, ManagerRegistry $managerRegistry): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager = $managerRegistry->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('extension_comment_admin');
    }
}