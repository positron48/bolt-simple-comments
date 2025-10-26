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
use Positron48\CommentExtension\Service\CommentLoggingService;
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
        SpamFilterService $spamFilterService,
        CommentLoggingService $commentLoggingService
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
            // Устанавливаем сервис логирования в SpamFilterService
            $spamFilterService->setCommentLoggingService($commentLoggingService);
            
            // Проверка на спам с логированием
            if ($spamFilterService->isSpamWithLogging($comment)) {
                $flashBag->add('commentForm', 'Sorry, your comment has been identified as spam');
                return $this->redirectToRoute('record', [
                    'contentTypeSlug' => $comment->getContent()->getContentType(),
                    'slugOrId' => $comment->getContent()->getSlug(),
                ]);
            }

            // Проверка reCAPTCHA с логированием
            if ($configService->isRecaptchaEnabled()) {
                $recaptchaToken = $request->request->get('g-recaptcha-response');
                $score = $recaptchaService->getScore($recaptchaToken);
                $threshold = $configService->getScoreThreshold();
                
                // Логируем получение рейтинга
                $commentLoggingService->logRecaptchaScore($score, $threshold, $score >= $threshold);
                
                if ($score < $threshold) {
                    // Логируем детектирование бота
                    $commentLoggingService->logBotDetection($comment, $score, $threshold);
                    $flashBag->add('commentForm', 'Sorry, you have been identified as a bot');
                } else {
                    // Сохраняем комментарий и логируем успешное создание
                    $managerRegistry->getManager()->persist($comment);
                    $managerRegistry->getManager()->flush();
                    $commentLoggingService->logCommentCreation($comment, true);
                }
            } else {
                // Сохраняем комментарий без reCAPTCHA и логируем
                $managerRegistry->getManager()->persist($comment);
                $managerRegistry->getManager()->flush();
                $commentLoggingService->logCommentCreation($comment, true);
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