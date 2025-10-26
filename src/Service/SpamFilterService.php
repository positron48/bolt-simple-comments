<?php

namespace Positron48\CommentExtension\Service;

use Positron48\CommentExtension\Entity\Comment;
use Psr\Log\LoggerInterface;

class SpamFilterService
{
    protected $spamRegex;
    protected $commentLoggingService;

    public function __construct(
        ?string $spamRegex = null
    ) {
        $this->spamRegex = getenv('COMMENT_SPAM_REGEX') ?: $spamRegex;
    }

    /**
     * Устанавливает сервис логирования (для избежания циклических зависимостей)
     */
    public function setCommentLoggingService(CommentLoggingService $commentLoggingService): void
    {
        $this->commentLoggingService = $commentLoggingService;
    }

    public function isSpam(string $message, string $authorName): bool
    {
        if (empty($this->spamRegex)) {
            return false;
        }

        $isMessageSpam = (bool) preg_match($this->spamRegex, $message);
        $isAuthorSpam = (bool) preg_match($this->spamRegex, $authorName);
        $isSpam = $isMessageSpam || $isAuthorSpam;

        return $isSpam;
    }

    /**
     * Проверка на спам с логированием
     */
    public function isSpamWithLogging(Comment $comment): bool
    {
        $isSpam = $this->isSpam($comment->getMessage(), $comment->getAuthorName());
        
        $reason = null;
        if ($isSpam) {
            if (preg_match($this->spamRegex, $comment->getMessage())) {
                $reason = 'Message matched spam regex';
            } elseif (preg_match($this->spamRegex, $comment->getAuthorName())) {
                $reason = 'Author name matched spam regex';
            }
        }

        if ($this->commentLoggingService) {
            $this->commentLoggingService->logSpamDetection($comment, $isSpam, $reason);
        }

        return $isSpam;
    }

    public function getSpamRegex(): ?string
    {
        return $this->spamRegex;
    }
} 