<?php

declare(strict_types=1);

namespace Positron48\CommentExtension;

use Bolt\Entity\Content;
use Positron48\CommentExtension\Entity\Comment;
use Positron48\CommentExtension\Form\CommentType;
use Positron48\CommentExtension\Repository\CommentRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;
    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    public function __construct(
        CommentRepository $commentRepository,
        Environment $environment,
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->commentRepository = $commentRepository;
        $this->environment = $environment;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('commentList', [$this, 'getComments']),
        ];
    }

    public function getComments(Content $content, int $page=1, int $count=1000): Markup
    {
        $template = '@bolt-simple-comments/comment_list.html.twig';
        if($this->environment->getLoader()->exists('comment_list.html.twig')) {
            $template = 'comment_list.html.twig';
        }

        $comments = $this->commentRepository->getByContentIdQuery($content->getId());

        $comment = new Comment();
        $comment->setContent($content);

        $form = $this->formFactory->createBuilder(CommentType::class, $comment)
            ->setAction($this->urlGenerator->generate('extension_comment_create',  ['id' => $content->getId()]))
            ->setMethod('POST')
            ->getForm()
        ;

        $html = $this->environment->render($template, [
            'comments' => $comments->getArrayResult(),
            'form' => $form->createView(),
        ]);

        return new Markup($html, 'UTF-8');
    }
}