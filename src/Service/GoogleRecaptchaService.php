<?php


namespace Positron48\CommentExtension\Service;

use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\TokenProperties\InvalidReason;
use Psr\Log\LoggerInterface;

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

    public function __construct(
        LoggerInterface $logger,
        ConfigService $configService
    ){
        $this->logger = $logger;
        $this->configService = $configService;
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
                $this->logger->error(
                    'The CreateAssessment() call failed because the token was invalid for the following reason: ' .
                    InvalidReason::name($response->getTokenProperties()->getInvalidReason())
                );
            } else {
                return $response->getRiskAnalysis()->getScore();
            }
        } catch (\Exception $e) {
            $this->logger->error(
                'CreateAssessment() call failed with the following error: ' . $e->getMessage()
            );
        }
        return 0;
    }
}