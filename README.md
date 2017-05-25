# Yaf Init

## 简介

用于初始化基于Yaf的项目

[Yaf手册](http://www.laruence.com/manual/index.html)

## Yaf基础配置

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

## Nginx配置

### 一个域名一个项目配置示例

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

### 同域名下多项目部署配置示例

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
