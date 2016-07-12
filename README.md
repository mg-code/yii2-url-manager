yii2-host-url-rule
=========
Filters url rules by host name.
Useful if your application works on multiple domains/subdomains.

### Install

Either run

```
$ php composer.phar require mg-code/yii2-host-url-rule "@dev"
```

or add

```
"mg-code/yii2-host-url-rule": "@dev"
```

to the ```require``` section of your `composer.json` file.

Usage
-----

Once the extension is installed, you can use HostUrlRule as any other url:

```php
return [
    'class' => 'yii\web\UrlManager',
    ......
    'rules' => [
       ['class' => 'mgcode\hostUrl\HostUrlRule', 'host' => 'payment.example.com', 'rules' => [
            ['pattern' => '/', 'route' => '/payment/default/index'],
        ]],
    ]
];
```
See [Yii Routing and URL Creation](http://www.yiiframework.com/doc-2.0/guide-runtime-routing.html) for more detail.
