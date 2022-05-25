<?php


namespace Positron48\CommentExtension\Service;


use Bolt\Extension\BaseExtension;
use Bolt\Extension\ExtensionRegistry;
use Positron48\CommentExtension\Extension;

class ConfigService
{
    /**
     * @var ExtensionRegistry
     */
    protected $registry;

    /**
     * @var array
     */
    protected $config;

    public function __construct(ExtensionRegistry $registry)
    {
        $this->registry = $registry;
        $this->config = $this->getConfigArray();
    }

    protected function getConfigArray() : array
    {
        if(!empty($this->config)) {
            return $this->config;
        }

        /** @var BaseExtension $extension */
        $extension = $this->registry->getExtension(Extension::class);
        if($extension === null) {
            return [];
        }

        $configCollection = $extension->getConfig();

        $config = [];
        foreach ($configCollection as $key => $item) {
            foreach ($item as $param => $value) {
                $config[$key][$param] = $this->prepareValue($value);
            }
        }
        return $config;
    }

    /**
     * todo: research best practice and replace
     *
     * @param $value
     * @return array|false|mixed|string
     */
    protected function prepareValue($value)
    {
        if(preg_match('#%env\((.*)\)%#', $value, $matches)) {
            $value = $_ENV[$matches[1]] ?: $value;
        }
        return $value;
    }

    public function isRecaptchaEnabled()
    {
        $this->config = $this->getConfigArray();
        return isset($this->config['recaptcha_enterprise']) ?
            (bool) $this->config['recaptcha_enterprise']['enabled'] :
            false;
    }

    public function getSiteKey()
    {
        $this->config = $this->getConfigArray();
        return isset($this->config['recaptcha_enterprise']) ?
            (string) $this->config['recaptcha_enterprise']['key'] :
            "";
    }

    public function getGoogleKey()
    {
        $this->config = $this->getConfigArray();
        return isset($this->config['recaptcha_enterprise']) ?
            (string) $this->config['recaptcha_enterprise']['google_api_key'] :
            "";
    }

    public function getProjectId()
    {
        $this->config = $this->getConfigArray();
        return isset($this->config['recaptcha_enterprise']) ?
            (string) $this->config['recaptcha_enterprise']['project_id'] :
            "";
    }

    public function getScoreThreshold()
    {
        $this->config = $this->getConfigArray();
        return isset($this->config['recaptcha_enterprise']) ?
            (float) $this->config['recaptcha_enterprise']['score_threshold'] :
            1;
    }

    public function isGravatarEnabled()
    {
        $this->config = $this->getConfigArray();
        return isset($this->config['gravatar']) ?
            (bool) $this->config['gravatar']['enabled'] :
            false;
    }
}