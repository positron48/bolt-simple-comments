<?php


namespace Positron48\CommentExtension;

use Bolt\Menu\ExtensionBackendMenuInterface;
use Knp\Menu\MenuItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdminMenu implements ExtensionBackendMenuInterface
{
    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function addItems(MenuItem $menu): void
    {
        // This adds a new heading
//        $menu->addChild('User Content Extension', [
//            'extras' => [
//                'name' => 'User Content Extension',
//                'type' => 'separator',
//            ]
//        ]);

        // This adds the link
        $menu->addChild('Comments', [
            'uri' => $this->urlGenerator->generate('extension_comment_admin'),
            'extras' => [
                'icon' => 'fa-user-circle'
            ]
        ]);
    }
}