东合公共会员服务端接口
========
安装
--------

使用 composer
```shell
composer require linshunwei/danhar-yun
```

在配置文件中添加服务提供者（Laravel5.5 有自动添加）
```php
'providers' => [
    //...
    Linshunwei\DanharYun\DanharYunServiceProvider::class,
    //...
],
```

复制配置文件到配置目录，配置文件内容不多，而且可以在 `.env` 文件中设置。手动复制或者使用命令复制：
```shell
php artisan vendor:publish --provider="Linshunwei\DanharYun\DanharYunServiceProvider"
```

修改配置文件 `config/danhar-yun.php`
```php
'host' => env('DANHAR_YUN_HOST',''), //会员服务器
'admin_host' => env('DANHAR_YUN_ADMIN_HOST',''), //管理后台服务器
'token_url' => env('DANHAR_YUN_OAUTH_TOKEN_URL',''),
'authorization_url' => env('DANHAR_YUN_OAUTH_AUTHORIZATIION_URL',''),
'callback_url' => env('DANHAR_YUN_CALLBACK_URL',''), //回调服务器
'client_id' => env('DANHAR_YUN_CLIENT_ID',''),
'client_secret' =>  env('DANHAR_YUN_CLIENT_SECRET',''),
'log_debug' =>  env('DANHAR_YUN_LOG_DEBUG',false), //是否开启日志 /logs/danhar_yun目录
```

或者直接在 `.env` 文件中设置需要修改的内容，没有特殊情况默认即可
```
DANHAR_YUN_HOST=127.0.0.1
DANHAR_YUN_CLIENT_ID=xxxx
DANHAR_YUN_CLIENT_SECRET=xxx
......
```
代码调用 消息列表+
```php
    $yun = new DanharYun();
    $res = $yun->getParameterItem('xxx');
```

