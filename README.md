# Yaf Init

## 一、简介

用于初始化基于Yaf的项目

[Yaf手册](http://www.laruence.com/manual/index.html)

## 二、Yaf配置

### 1、PHP INI配置

名称|值|备注
-|-|-
yaf.environ|debug|线上产品设置为product
yaf.library|/your-path/yaf-init/lib|your-path可根据您的实际情况设置，yaf-init也可设置
yaf.cache_config|0|N/A
yaf.name_suffix|0|N/A
yaf.name_separator|_|N/A
yaf.action_prefer|1|N/A
yaf.forward_limit|5|N/A
yaf.lowcase_path|1|N/A
yaf.use_namespace|0|N/A
yaf.use_spl_autoload|0|N/A

[详细配置说明](http://php.net/manual/zh/yaf.configuration.php)

### 2、项目配置

名称|是否必须|类型|默认值|说明
-|-|-|-|-
application.directory|Yes|String|N/A|应用绝对目录路径
application.ext|No|String|php|PHP脚本的扩展名
application.bootstrap|No|String|Bootstrapplication.php|Bootstrap路径(绝对路径)
application.library|No|String|application.directory + "/library"|本地(自身)类库的绝对目录地址
application.baseUri|No|String|null|在路由中, 需要忽略的路径前缀, 一般不需要设置, Yaf会自动判断
application.dispatcher.defaultModule|No|String|Index|默认的模块
application.dispatcher.defaultController|No|String|Index|默认控制器
application.dispatcher.defaultAction|No|String|index|默认动作
application.dispatcher.throwException|No|Bool|true|在出错的时候, 是否抛出异常
application.dispatcher.catchException|No|Bool|false|是否使用默认的异常捕获Controller, 如果开启, 在有未捕获的异常的时候, 控制权会交给ErrorController的errorAction方法, 可以通过$request->getException()获得此异常对象
application.view.ext|No|String|phtml|视图模板扩展名
application.modules|No|String|Index|声明存在的模块名, 请注意, 如果你要定义这个值, 一定要定义Index Module
application.system.*|No|String|*|通过这个属性, 可以修改yaf的runtime configure, 比如application.system.lowcase_path, 但是请注意只有PHP_INI_ALL的配置项才可以在这里被修改, 此选项从2.2.0开始引入


## 三、Nginx配置

### 1、单域名单项目配置示例

```
server {
    listen       xxx.xxx.xxx.xxx;
    server_name  hello.xxx.com ;
    index index.php index.html index.htm;
    root /your-path/yaf-init/public/hello/;
    
    location / {   
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ .*\.(php|php5)?$ {   
        fastcgi_pass  127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi.conf;
    }   
    
    location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$ {   
        #expires 30d;
    }   
    
    location ~ .*\.(js|css)?$ {   
        #expires 1h; 
    }   
    
    access_log  /your-path/nginx/logs/yaf-init.log;
}
```

**注意：在上面配置的情况下，index.php中初始化项目需要传入项目名称**

```
<?php

require_once __DIR__."/../../lib/fx/Init.php";
$app = Fx_Init::init('hello');//这里传入项目名称
$app->bootstrap()->run();
```

### 2、单域名多项目配置示例

```
server {
    listen              8080;
    server_name         xxxx.xxx.com;
    set $php_upstream '127.0.0.1:9000;';
    
    #防盗链
    if ($host !~ "^((.*\.)?(xxxx\.(com|com\.cn|cn)|xx\.com)|localhost|\d{1,3}(\.\d{1,3}){3})(:\d+)?$") {
        #return 403
    }
    location ~* /(\.svn|CVS|Entries) {
        deny all;
    }

    location ~* /((.*)\.(.*)\/(.*)\.php) {
        deny all;
    }

    location ~* /\.(sql|bak|inc|old)$ {
        deny all;
    }

    location ~ ^/(favicon.ico|static) {
        root /your-path/yaf-init/public;
    }
    
    if (!-e $request_filename) {
        rewrite ^/hello/(.*)$ /hello/index.php/$1 break;
    }
    
    location ~ \.php$ {
        root            /your-path/yaf-init/public;
        fastcgi_pass    $php_upstream;
        #fastcgi_index   index.php;
        include         fastcgi.conf;
    }
    
    location /hello {
        root /your-path/yaf-init/public;
        index index.php;
        fastcgi_pass    $php_upstream;
        #fastcgi_index   index.php;#会给以/结尾的url加上index.php，并存到$fastcgi_script_name中
        include         fastcgi.conf;
    }
    
    location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$ {   
        #expires 30d;
    }   
        
    location ~ .*\.(js|css)?$ {   
        #expires 1h; 
    }   
    
    access_log  /your-path/nginx/logs/yaf-init.log;
}
```
