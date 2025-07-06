<?php


namespace Positron48\CommentExtension\Controller;

use Bolt\Entity\Content;
use Bolt\Extension\ExtensionController;
use Doctrine\Persistence\ManagerRegistry;
use Positron48\CommentExtension\Entity\Comment;
use Positron48\CommentExtension\Form\CommentType;
use Positron48\CommentExtension\Service\ConfigService;
use Positron48\CommentExtension\Service\GoogleRecaptchaService;
use Positron48\CommentExtension\Service\SpamFilterService;
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
        ConfigService $configService,
        SpamFilterService $spamFilterService
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
            if ($spamFilterService->isSpam($comment->getMessage(), $comment->getAuthorName())) {
                // TODO: notify admin about spam
                $flashBag->add('commentForm', 'Sorry, your comment has been identified as spam');
                return $this->redirectToRoute('record', [
                    'contentTypeSlug' => $comment->getContent()->getContentType(),
                    'slugOrId' => $comment->getContent()->getSlug(),
                ]);
            }
            if(
                $configService->isRecaptchaEnabled() &&
                $recaptchaService->getScore($request->request->get('g-recaptcha-response')) < $configService->getScoreThreshold()
            ){
                // TODO: notify admin about bot
                $flashBag->add('commentForm', 'Sorry, you have been identified as a bot');
            } else {
                $managerRegistry->getManager()->persist($comment);
                $managerRegistry->getManager()->flush();
                // TODO: notify admin about comments
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