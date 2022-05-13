<?php


namespace Positron48\CommentExtension\Controller;

use Bolt\Entity\Content;
use Bolt\Extension\ExtensionController;
use Bolt\Storage\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Positron48\CommentExtension\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends ExtensionController
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
}