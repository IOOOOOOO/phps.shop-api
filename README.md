

API端配置

1、config/setting.php

2、extend/Wxpay/WxPayConfig.php

3、微信证书默认安装位置- extend/Wxpay/cert

4、图片访问路径前缀- extend/Uedit/config下imageUrlPrefix

5、数据表sys_config的参数--可通过CMS端配置

**下个版本一定要弄成后台配置所有参数**


CMS端配置

开发版：src/common/config.js 下的API网址

生产版：js/app.xxxx.js 下搜索wx.phps.shop，替换成你的API网址即可。

	   该生产版一定要放在根目录，不然访问是白屏。或者自定义配置文件

小程序端配置

config.js下API网址


如花拼团属个人开发，本项目基于 MIT 协议，可自由地享受和参与开源

QQ群:728615087   文档：http://www.phps.shop/
