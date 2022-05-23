<?php


namespace Positron48\CommentExtension\Controller;

use Bolt\Entity\Content;
use Bolt\Extension\ExtensionController;
use Doctrine\Persistence\ManagerRegistry;
use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\TokenProperties\InvalidReason;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Positron48\CommentExtension\Entity\Comment;
use Positron48\CommentExtension\Form\CommentType;
use Positron48\CommentExtension\Repository\CommentRepository;
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
    public function index(CommentRepository $commentRepository, Request $request): Response
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
            'comments' => $comments
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
        FlashBagInterface $flashBag
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
            if($this->createAssessment($request->request->get('g-recaptcha-response')) > 0.5) {
                $managerRegistry->getManager()->persist($comment);
                $managerRegistry->getManager()->flush();
            } else {
                $flashBag->add('commentForm', 'sorry, you are bot');
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

    /**
     * Create an assessment to analyze the risk of a UI action.
     * @param string $token The user's response token for which you want to receive a reCAPTCHA score. (See https://cloud.google.com/recaptcha-enterprise/docs/create-assessment#retrieve_token)
     * @param string $siteKey The key ID for the reCAPTCHA key (See https://cloud.google.com/recaptcha-enterprise/docs/create-key)
     * @param string $project Your Google Cloud project ID
     */
    protected function createAssessment(
        string $token,
        string $siteKey = '6LfsgxIgAAAAAOqVxBz0Kglg_ChOc-Fw4bdFZdQ6',
        string $project = 'positroid-tech'
    ): float {
        // TODO: To avoid memory issues, move this client generation outside
        // of this example, and cache it (recommended) or call client.close()
        // before exiting this method.
        // ¯\_(ツ)_/¯
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $_ENV['GOOGLE_APPLICATION_RECAPTHA_CREDENTIALS']);
        $client = new RecaptchaEnterpriseServiceClient();
        $projectName = $client->projectName($project);

        $event = (new Event())
            ->setSiteKey($siteKey)
            ->setToken($token);

        $assessment = (new Assessment())
            ->setEvent($event);

        try {
            $response = $client->createAssessment(
                $projectName,
                $assessment
            );

            // You can use the score only if the assessment is valid,
            // In case of failures like re-submitting the same token, getValid() will return false
            if ($response->getTokenProperties()->getValid() == false) {
                dump(
                    'The CreateAssessment() call failed because the token was invalid for the following reason: ' .
                    InvalidReason::name($response->getTokenProperties()->getInvalidReason())
                );
            } else {
                return $response->getRiskAnalysis()->getScore();

                // Optional: You can use the following methods to get more data about the token
                // Action name provided at token generation.
                // printf($response->getTokenProperties()->getAction() . PHP_EOL);
                // The timestamp corresponding to the generation of the token.
                // printf($response->getTokenProperties()->getCreateTime()->getSeconds() . PHP_EOL);
                // The hostname of the page on which the token was generated.
                // printf($response->getTokenProperties()->getHostname() . PHP_EOL);
            }
        } catch (\Exception $e) {
            dump('CreateAssessment() call failed with the following error: ' . $e->getMessage());
        }
        return 0;
    }
}