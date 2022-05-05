<?php


namespace Positron48\CommentExtension\Controller;

use Bolt\Extension\ExtensionController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends ExtensionController
{
    /**
     * @Route("/comments", name="extension_comment_admin")
     */
    public function index(): Response
    {
        $context = [
            'title' => 'Positron48 Comment Extension'
        ];

        return $this->render('@bolt-simple-comments/comment_admin.html.twig', $context);
    }
}