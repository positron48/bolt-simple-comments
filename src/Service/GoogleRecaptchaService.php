<?php


namespace Positron48\CommentExtension\Service;

use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\TokenProperties\InvalidReason;
use Psr\Log\LoggerInterface;
use Positron48\CommentExtension\Service\CommentLoggingService;

class GoogleRecaptchaService
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var ConfigService
     */
    protected $configService;
    protected $commentLoggingService;

    public function __construct(
        LoggerInterface $logger,
        ConfigService $configService,
        CommentLoggingService $commentLoggingService
    ){
        $this->logger = $logger;
        $this->configService = $configService;
        $this->commentLoggingService = $commentLoggingService;
    }

    /**
     * Create an assessment to analyze the risk of a UI action.
     * @param string $token The user's response token for which you want to receive a reCAPTCHA score. (See https://cloud.google.com/recaptcha-enterprise/docs/create-assessment#retrieve_token)
     */
    public function getScore(string $token): float
    {
        // todo: research best practice and replace
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $_ENV['GOOGLE_APPLICATION_RECAPTHA_CREDENTIALS']);

        $client = new RecaptchaEnterpriseServiceClient();
        $projectName = $client->projectName($this->configService->getProjectId());

        $event = (new Event())
            ->setSiteKey($this->configService->getSiteKey())
            ->setToken($token);

        $assessment = (new Assessment())
            ->setEvent($event);

        try {
            $response = $client->createAssessment(
                $projectName,
                $assessment
            );

            if ($response->getTokenProperties()->getValid() == false) {
                $invalidReason = InvalidReason::name($response->getTokenProperties()->getInvalidReason());
                $this->logger->error(
                    'The CreateAssessment() call failed because the token was invalid for the following reason: ' . $invalidReason
                );
                
                // Логируем неудачный запрос к reCAPTCHA
                $this->commentLoggingService->logRecaptchaRequest(
                    $token, 
                    0, 
                    false, 
                    'Invalid token: ' . $invalidReason
                );
            } else {
                $score = $response->getRiskAnalysis()->getScore();
                
                // Логируем успешный запрос к reCAPTCHA
                $this->commentLoggingService->logRecaptchaRequest($token, $score, true);
                
                return $score;
            }
        } catch (\Exception $e) {
            $this->logger->error(
                'CreateAssessment() call failed with the following error: ' . $e->getMessage()
            );
            
            // Логируем ошибку запроса к reCAPTCHA
            $this->commentLoggingService->logRecaptchaRequest(
                $token, 
                0, 
                false, 
                $e->getMessage()
            );
        }
        return 0;
    }
}