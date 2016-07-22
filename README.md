yii2-url-manager
=========
Useful url rules for url manager.

## Install

Either run

```
$ php composer.phar require mg-code/yii2-url-manager "@dev"
```

or add

```
"mg-code/yii2-url-manager": "@dev"
```

to the ```require``` section of your `composer.json` file.

## Usage

Once the extension is installed, you can use url rules:

#### HostUrlRule
Filters url rules by host name. 
Useful if your application works on multiple domains/subdomains.

```php
return [
    'class' => 'yii\web\UrlManager',
    ......
    'rules' => [
       ['class' => 'mgcode\urlManager\HostUrlRule', 'host' => 'payment.example.com', 'rules' => [
            ['pattern' => '/', 'route' => '/payment/default/index'],
        ]],
    ]
];
```


See [Yii Routing and URL Creation](http://www.yiiframework.com/doc-2.0/guide-runtime-routing.html) for more detail.
