<?php

namespace mgcode\urlManager;

use Yii;
use yii\web\UrlRule;

/**
 * Redirects user to destination route if current request matched.
 * @link https://github.com/mg-code/yii2-url-manager
 * @author Maris Graudins <maris@mg-interactive.lv>
 */
class RedirectUrlRule extends UrlRule
{
    /**
     * @var int The HTTP status code.
     */
    public $statusCode = 301;

    /**
     * @inheritdoc
     */
    public function init(){
        parent::init();
        
        // This url is used only for parsing.
        $this->mode = static::PARSING_ONLY;
    }

    /**
     * Redirects to destination, if current request matched.
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        if($parse = parent::parseRequest($manager, $request)) {
            list($route, $params) = $parse;

            $rule = array_merge([$route], $params);
            \Yii::$app->response->redirect($rule, $this->statusCode)->send();
            \Yii::$app->end();
        }
        return false;
    }
}
