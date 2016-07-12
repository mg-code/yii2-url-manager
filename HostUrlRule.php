<?php

namespace mgcode\hostUrl;

use Yii;
use yii\base\InvalidConfigException;
use yii\web\CompositeUrlRule;
use yii\web\UrlRuleInterface;

/**
 * HostUrlRule filters url rules by host name.
 * Useful if your application works on multiple domains/subdomains.
 * @link https://github.com/mg-code/yii2-host-url-rule
 * @author Maris Graudins <maris@mg-interactive.lv>
 */
class HostUrlRule extends CompositeUrlRule
{
    /**
     * @var string
     */
    public $host;

    /**
     * @var array the rules contained within this composite rule. Please refer to [[UrlManager::rules]]
     * for the format of this property.
     * @see prefix
     * @see routePrefix
     */
    public $rules = [];

    /**
     * @var array the default configuration of URL rules. Individual rule configurations
     * specified via [[rules]] will take precedence when the same property of the rule is configured.
     */
    public $ruleConfig = ['class' => 'yii\web\UrlRule'];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->host)) {
            throw new InvalidConfigException('`host` property must be set.');
        }
        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function createRules()
    {
        $rules = [];
        foreach ($this->rules as $key => $rule) {
            if (!is_array($rule)) {
                $rule = [
                    'pattern' => $key,
                    'route' => $rule,
                ];
            }
            $rule = Yii::createObject(array_merge($this->ruleConfig, $rule));
            if (!$rule instanceof UrlRuleInterface) {
                throw new InvalidConfigException('URL rule class must implement UrlRuleInterface.');
            }
            $rules[] = $rule;
        }
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        if (!isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] != $this->host) {
            return false;
        }
        foreach ($this->rules as $rule) {
            /* @var $rule \yii\web\UrlRule */
            if (($result = $rule->parseRequest($manager, $request)) !== false) {
                return $result;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        foreach ($this->rules as $rule) {
            /* @var $rule \yii\web\UrlRule */
            if (($url = $rule->createUrl($manager, $route, $params)) !== false) {
                $hostInfo = Yii::$app->request->isSecureConnection ? 'https' : 'http';
                if (substr($url, 0, 1) !== '/') {
                    $url = '/'.$url;
                }
                return $hostInfo.'://'.$this->host.$url;
            }
        }
        return false;
    }
}