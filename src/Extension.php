<?php

declare(strict_types=1);

namespace Positron48\CommentExtension;

use Bolt\Extension\BaseExtension;
use Symfony\Component\Filesystem\Filesystem;

class Extension extends BaseExtension
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Simple comments';
    }

    public function install(): void
    {
        $projectDir = $this->getContainer()->getParameter('kernel.project_dir');
        //$public = $this->getContainer()->getParameter('bolt.public_folder');

        $source = dirname(__DIR__) . '/migrations/';
        $destination = $projectDir . '/migrations/';

        $filesystem = new Filesystem();
        $filesystem->mirror($source, $destination);
    }

    public function initialize($cli = false): void
    {
        $this->addTwigNamespace('bolt-simple-comments');
    }
}