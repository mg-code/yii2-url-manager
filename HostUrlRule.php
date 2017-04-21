<?php

namespace mgcode\urlManager;

use Yii;
use yii\base\InvalidConfigException;
use yii\web\CompositeUrlRule;
use yii\web\Request;
use yii\web\UrlRule;
use yii\web\UrlRuleInterface;

/**
 * HostUrlRule filters url rules by host name.
 * Useful if your application works on multiple domains/subdomains.
 * @link https://github.com/mg-code/yii2-url-manager
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
     * @var string|null Holds protocol name
     */
    private $_createUrlProtocol;

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
        $compiledRules = [];
        $verbs = 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS';
        foreach ($this->rules as $key => $rule) {
            if (is_string($rule)) {
                $rule = ['route' => $rule];
                if (preg_match("/^((?:($verbs),)*($verbs))\\s+(.*)$/", $key, $matches)) {
                    $rule['verb'] = explode(',', $matches[1]);
                    // rules that do not apply for GET requests should not be use to create urls
                    if (!in_array('GET', $rule['verb'])) {
                        $rule['mode'] = UrlRule::PARSING_ONLY;
                    }
                    $key = $matches[4];
                }
                $rule['pattern'] = $key;
            }
            if (is_array($rule)) {
                $rule = Yii::createObject(array_merge($this->ruleConfig, $rule));
            }
            if (!$rule instanceof UrlRuleInterface) {
                throw new InvalidConfigException('URL rule class must implement UrlRuleInterface.');
            }
            $compiledRules[] = $rule;
        }
        return $compiledRules;
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
                $protocol = $this->getCreateUrlProtocol();
                if (substr($url, 0, 1) !== '/') {
                    $url = '/'.$url;
                }
                return $protocol.'://'.$this->host.$url;
            }
        }
        return false;
    }

    /**
     * Returns protocol for create url. If protocol not defined will detect from current request or set as http.
     * @return null|string
     */
    public function getCreateUrlProtocol()
    {
        if ($this->_createUrlProtocol !== null) {
            return $this->_createUrlProtocol;
        }

        $this->_createUrlProtocol = \Yii::$app->has('request') && \Yii::$app->request instanceof Request && Yii::$app->request->isSecureConnection ? 'https' : 'http';
        return $this->_createUrlProtocol;
    }

    /**
     * Setter for create url protocol
     * @param $value
     */
    public function setCreateUrlProtocol($value)
    {
        $this->_createUrlProtocol = $value;
    }
}
