# dz_markdown
> 因为购买的markdown插件实在太垃圾了，不得不再自己重写一个，RNM，退钱！

油猴中文网用markdown插件

## 安装

需要使用yarn和composer安装依赖包

```
cd src
yarn
composer install
```

然后将src下文件粘贴到`/plugin/codfrm_markdown`中

## 开发配置

为了方便开发调试，可以使用docker启动

```bash
docker-compose up -d
```

启动docker，安装好discuz后，
在runtime/config/config_global.php文件后加入下面内容可开启开发者模式
```php
$_config['plugindeveloper'] = 2;
```

## 感谢以下项目

* tui.editor
* github-markdown-css
* prismjs
* erusev/parsedown
