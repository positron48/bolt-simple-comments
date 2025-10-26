<?php

namespace Positron48\CommentExtension\Service;

use Psr\Log\LoggerInterface;
use Positron48\CommentExtension\Entity\Comment;

class CommentLoggingService
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Логирование запроса к Google reCAPTCHA
     */
    public function logRecaptchaRequest(string $token, float $score, bool $isValid, ?string $error = null): void
    {
        $context = [
            'token' => substr($token, 0, 20) . '...', // Обрезаем токен для безопасности
            'score' => $score,
            'is_valid' => $isValid,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
        ];

        if ($error) {
            $context['error'] = $error;
            $this->logger->error('reCAPTCHA request failed', $context);
        } else {
            $this->logger->info('reCAPTCHA request processed', $context);
        }
    }

    /**
     * Логирование создания комментария
     */
    public function logCommentCreation(Comment $comment, bool $success, ?string $error = null): void
    {
        $context = [
            'comment_id' => $comment->getId(),
            'author_name' => $comment->getAuthorName(),
            'author_email' => $comment->getAuthorEmail(),
            'content_id' => $comment->getContent()->getId(),
            'content_type' => $comment->getContent()->getContentType(),
            'status' => $comment->getStatus(),
            'message_length' => strlen($comment->getMessage()),
            'timestamp' => $comment->getCreatedAt()->format('Y-m-d H:i:s')
        ];

        if ($error) {
            $context['error'] = $error;
            $this->logger->error('Comment creation failed', $context);
        } else {
            $this->logger->info('Comment created successfully', $context);
        }
    }

    /**
     * Логирование получения рейтинга от reCAPTCHA
     */
    public function logRecaptchaScore(float $score, float $threshold, bool $passed): void
    {
        $context = [
            'score' => $score,
            'threshold' => $threshold,
            'passed' => $passed,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
        ];

        $level = $passed ? 'info' : 'warning';
        $message = $passed ? 'reCAPTCHA score passed threshold' : 'reCAPTCHA score below threshold';
        
        $this->logger->log($level, $message, $context);
    }

    /**
     * Логирование детектирования спама
     */
    public function logSpamDetection(Comment $comment, bool $isSpam, ?string $reason = null): void
    {
        $context = [
            'comment_id' => $comment->getId(),
            'author_name' => $comment->getAuthorName(),
            'author_email' => $comment->getAuthorEmail(),
            'content_id' => $comment->getContent()->getId(),
            'content_type' => $comment->getContent()->getContentType(),
            'message_preview' => substr($comment->getMessage(), 0, 100) . '...',
            'is_spam' => $isSpam,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
        ];

        if ($reason) {
            $context['reason'] = $reason;
        }

        $level = $isSpam ? 'warning' : 'info';
        $message = $isSpam ? 'Comment detected as spam' : 'Comment passed spam filter';
        
        $this->logger->log($level, $message, $context);
    }

    /**
     * Логирование бота (низкий рейтинг reCAPTCHA)
     */
    public function logBotDetection(Comment $comment, float $score, float $threshold): void
    {
        $context = [
            'comment_id' => $comment->getId(),
            'author_name' => $comment->getAuthorName(),
            'author_email' => $comment->getAuthorEmail(),
            'content_id' => $comment->getContent()->getId(),
            'content_type' => $comment->getContent()->getContentType(),
            'recaptcha_score' => $score,
            'threshold' => $threshold,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
        ];

        $this->logger->warning('Bot detected by reCAPTCHA', $context);
    }
}
