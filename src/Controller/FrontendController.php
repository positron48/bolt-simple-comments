<?php


namespace Positron48\CommentExtension\Controller;

use Bolt\Entity\Content;
use Bolt\Extension\ExtensionController;
use Doctrine\Persistence\ManagerRegistry;
use Positron48\CommentExtension\Entity\Comment;
use Positron48\CommentExtension\Form\CommentType;
use Positron48\CommentExtension\Service\ConfigService;
use Positron48\CommentExtension\Service\GoogleRecaptchaService;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;

class FrontendController extends ExtensionController
{
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
        $comment->setCreatedAt(new \DateTime());
        $comment->setStatus(Comment::STATUS_NEW);

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
}