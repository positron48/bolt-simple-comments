<?php


namespace Positron48\CommentExtension\Controller;

use Bolt\Entity\Content;
use Bolt\Extension\ExtensionController;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Positron48\CommentExtension\Entity\Comment;
use Positron48\CommentExtension\Form\CommentType;
use Positron48\CommentExtension\Repository\CommentRepository;
use Positron48\CommentExtension\Service\ConfigService;
use Positron48\CommentExtension\Service\GoogleRecaptchaService;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;

class Controller extends ExtensionController
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
        ]);
    }

    /**
     * @Route("/content/{id}/comment", name="extension_comment_create", methods={"POST"})
     */
    public function create(
        Request $request,
        Content $content,
        ManagerRegistry $managerRegistry,
        FlashBagInterface $flashBag,
        GoogleRecaptchaService $recaptchaService,
        ConfigService $configService
    ): Response
    {
        $comment = new Comment();
        $comment->setContent($content);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if (
            isset($request->request->get('comment')['field']) &&
            empty($request->request->get('comment')['field']) &&
            $form->isSubmitted() &&
            $form->isValid()
        ) {
            if(
                $configService->isRecaptchaEnabled() &&
                $recaptchaService->getScore($request->request->get('g-recaptcha-response')) < $configService->getScoreThreshold()
            ){
                $flashBag->add('commentForm', 'sorry, you are bot');
            } else {
                $managerRegistry->getManager()->persist($comment);
                $managerRegistry->getManager()->flush();
            }

            return $this->redirectToRoute('record', [
                'contentTypeSlug' => $comment->getContent()->getContentType(),
                'slugOrId' => $comment->getContent()->getSlug(),
            ]);
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                /** @var FormError $error */
                foreach ($child->getErrors() as $error) {
                    $flashBag->add('commentForm', $child->getName() . ': ' . $error->getMessage());
                }
            }
        }

        // пока нет никакой обработки ошибок
        return $this->redirectToRoute('record', [
            'contentTypeSlug' => $comment->getContent()->getContentType(),
            'slugOrId' => $comment->getContent()->getSlug(),
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