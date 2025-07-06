<?php

namespace Positron48\CommentExtension\Service;

class SpamFilterService
{
    private ?string $spamRegex;

    public function __construct(?string $spamRegex = null)
    {
        $this->spamRegex = getenv('COMMENT_SPAM_REGEX') ?: null;
    }

    public function isSpam(string $message, string $authorName): bool
    {
        if (empty($this->spamRegex)) {
            return false;
        }

        return (bool) preg_match($this->spamRegex, $message) ||
            (bool) preg_match($this->spamRegex, $authorName);
    }

    public function getSpamRegex(): ?string
    {
        return $this->spamRegex;
    }
} 