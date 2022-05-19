<?php

declare(strict_types=1);

namespace Positron48\CommentExtension;

use Positron48\CommentExtension\Repository\CommentRepository;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    /**
     * @var CommentRepository
     */
    protected $commentRepository;
    /**
     * @var Environment
     */
    protected $environment;

    public function __construct(CommentRepository $commentRepository, Environment $environment)
    {
        $this->commentRepository = $commentRepository;
        $this->environment = $environment;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('commentList', [$this, 'getComments']),
        ];
    }

    public function getComments(int $contentId, int $page=1, int $count=1000): Markup
    {
        $template = '@bolt-simple-comments/comment_list.html.twig';
        if($this->environment->getLoader()->exists('comment_list.html.twig')) {
            $template = 'comments_list.html.twig';
        }

        $comments = $this->commentRepository->getByContentIdQuery($contentId);
        $html = $this->environment->render($template, [
            'comments' => $comments->getArrayResult()
        ]);

        return new Markup($html, 'UTF-8');
    }
}