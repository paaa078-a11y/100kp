# 100kp.com（快聘网）

`100kp.com` 是使用 PHPYun 人才招聘系统搭建的同城招聘网站，包含 PC 前台、手机端、会员中心、平台后台、分站后台、支付与第三方登录接口。

本文档用于项目交接、本地启动、部署维护和故障排查。文档基于当前本地目录 `F:\Codex\100kp.com` 的实际结构编写。

## 1. 项目信息

| 项目 | 内容 |
| --- | --- |
| 站点名称 | 快聘网 |
| 正式域名 | `https://www.100kp.com` |
| 本地目录 | `F:\Codex\100kp.com` |
| 系统 | PHPYun |
| 版本 | `v7.0.1 VIP (20240122)` |
| 主要语言 | PHP 7.4 |
| 数据库 | MySQL 5.7 |
| Web 服务 | Nginx（生产环境）；开发环境也可使用 Apache |
| 模板引擎 | Smarty |
| 前端 | HTML、CSS、JavaScript、jQuery、Vue 2、Element UI、Layui |
| 后台入口 | `/admin/` |
| 手机后台入口 | `/wapadmin/` |
| 分站后台入口 | `/siteadmin/` |

> 当前项目没有 `composer.json`、`package.json` 或统一的前后端构建流程，第三方库和前端资源直接存放在源码目录中。

## 2. 主要功能

- 企业招聘、职位发布与职位搜索
- 个人求职、简历创建与简历投递
- 企业会员、个人会员及其他会员类型管理
- 招聘会、店铺招聘、兼职、培训、校园招聘
- 职场问答、资讯文章、专题招聘、职业测评
- PC 端、手机端及微信相关页面
- 平台后台、手机后台和地区分站后台
- 积分、套餐、订单及支付宝、微信支付等接口
- 微信、QQ 等第三方登录与绑定
- 地图职位、附近职位及地区分站跳转

## 3. 代码结构

```text
100kp.com/
├─ index.php                 # 主站入口
├─ global.php                # 公共初始化：常量、数据库、Smarty、通用函数
├─ admin/                    # PC 平台后台入口
├─ wapadmin/                 # 手机后台入口
├─ siteadmin/                # 地区分站后台入口
├─ app/
│  ├─ controller/            # 前台、后台、API 控制器
│  ├─ model/                 # 业务模型和数据库操作
│  ├─ public/                # 公共控制器、模型基类
│  ├─ include/               # Smarty、数据库类、上传类及第三方 SDK
│  └─ template/              # PC、手机、后台模板和静态资源
├─ config/
│  ├─ db.config.php          # 数据库及站点密钥配置，禁止公开或提交
│  ├─ db.safety.php          # 数据库安全初始化
│  ├─ db.data.php            # 通用字典数据
│  └─ initDeal.php           # 请求初始化与参数处理
├─ data/
│  ├─ plus/                  # 运行时配置和缓存数据
│  ├─ templates_c/           # Smarty 编译缓存
│  ├─ cache/                 # 业务缓存
│  ├─ upload/                # 站内上传文件
│  ├─ logo/                  # Logo、头像等资源
│  ├─ backup/                # 数据备份
│  └─ api/                   # 支付、微信等 API 配置
├─ uploads/                  # 上传目录，禁止放置或执行 PHP 文件
├─ job/                      # 职位模块入口
├─ company/                  # 企业模块入口
├─ resume/                   # 简历模块入口
├─ article/                  # 资讯模块入口
├─ ask/                      # 问答模块入口
├─ train/                    # 培训模块入口
├─ zph/                      # 招聘会模块入口
├─ m/                        # 手机端入口
├─ wap/                      # WAP 页面入口
├─ member/                   # 会员中心
├─ api/                      # 支付、OAuth、微信、小程序等接口
├─ install222/               # 安装/初始化资源，生产环境禁止公开
├─ update/                   # 升级相关页面
└─ version.php               # PHPYun 版本标识
```

业务目录通常只负责设置当前模块并加载 `global.php`，真正的控制器位于 `app/controller/`，模板位于 `app/template/`。

## 4. 运行要求

### 4.1 推荐环境

- CentOS 7 或兼容的 Linux 发行版
- Nginx 1.22+
- PHP-FPM 7.4
- MySQL 5.7
- 内存建议至少 4 GB
- HTTPS 证书，正式域名必须全站使用 HTTPS

服务器近期实际运行环境为：

- CentOS 7
- Nginx 1.22.1
- PHP 7.4.33
- MySQL 5.7.44

> PHP 7.4、MySQL 5.7 和 CentOS 7 均已停止官方维护。当前代码不能直接无验证升级到 PHP 8，升级前必须进行完整兼容性测试。

### 4.2 PHP 扩展

至少应安装并启用：

- `mysqli`
- `pdo_mysql`
- `mbstring`
- `curl`
- `gd`
- `fileinfo`
- `openssl`
- `json`
- `xml`
- `zip`

根据实际启用的功能，还可能需要：

- `memcache` / `memcached`
- `redis`
- `bcmath`
- `intl`
- `imagick`

## 5. 本地启动

### 5.1 准备 Web 服务

推荐使用 PHP 7.4 + Nginx 或 PHP 7.4 + Apache，并将网站根目录指向：

```text
F:\Codex\100kp.com
```

### 5.2 配置数据库

编辑：

```text
config/db.config.php
```

配置结构参考如下，禁止把真实密码写入文档、工单或公开仓库：

```php
<?php
$db_config = array(
    'dbtype'   => 'mysql',
    'dbhost'   => '127.0.0.1',
    'dbuser'   => 'CHANGE_ME',
    'dbpass'   => 'CHANGE_ME',
    'dbname'   => '100kp_com',
    'def'      => 'phpyun_',
    'charset'  => 'utf8',
    'timezone' => 'PRC',
    'coding'   => 'CHANGE_TO_A_RANDOM_SECRET',
);
```

注意：

- `def` 是数据表前缀，必须与数据库实际表名前缀一致。
- `coding` 用于站点 Cookie 等加密，属于敏感配置。
- `config/db.config.php` 必须禁止 Web 直接访问。
- 不要用本地数据库备份覆盖正式数据库，除非已确认目标环境。

### 5.3 导入数据库

正式环境应使用经过确认的数据库备份恢复。

本地全新测试环境可以使用 PHPYun 安装程序或安装 SQL，但 `install222/` 不得在正式站点公开保留。安装前必须备份数据库，安装后应封禁安装目录。

### 5.4 调整本地路径

当前 `.user.ini` 指向生产路径：

```ini
open_basedir=/www/wwwroot/100kp.com/:/tmp/
```

在 Windows 本地环境中通常不能直接使用该路径。可根据本地 Web 服务是否支持 `.user.ini` 调整或忽略，但正式 Linux 环境必须使用正确的站点目录。

### 5.5 设置目录权限

Linux 正式环境建议：

```text
目录：755
普通文件：644
需要上传或生成缓存的目录：775，所有者为 Web 用户（通常为 www）
```

需要可写的目录通常包括：

```text
data/cache
data/templates_c
data/upload
data/logo
data/sharelogo
data/wxpubtpl
data/xml
uploads
```

上传、缓存、日志和用户资料目录中禁止执行 PHP。

### 5.6 检查站点 URL

首次启动后，在后台或数据库配置中确认：

- PC 地址为本地或正式域名
- 手机端地址正确
- HTTPS 开关正确
- 伪静态规则已启用
- Cookie 域和主域名一致

配置变化后，重新生成系统缓存。

## 6. Nginx 配置要点

正式环境应使用宝塔或项目现成的 PHPYun 伪静态规则。基础配置至少应包含：

```nginx
server {
    listen 80;
    server_name www.100kp.com 100kp.com;
    root /www/wwwroot/100kp.com;
    index index.php index.html;

    # PHP 处理配置
    include enable-php-74.conf;

    # 禁止访问敏感目录
    location ^~ /config/ {
        return 404;
    }

    location ^~ /install222/ {
        return 404;
    }

    # 禁止上传、缓存目录直接执行 PHP
    location ~* ^/(data|uploads)/.*\.(php|phtml|phar)$ {
        return 404;
    }

    location ~* ^/data/(plus|templates_c|backup)/ {
        return 404;
    }

    # 禁止下载备份和数据库文件
    location ~* ^/.*\.(sql|bak|old|swp|tar|tar\.gz|tgz|zip|rar|7z)$ {
        return 404;
    }
}
```

不要把现有完整伪静态规则替换成简单的 `try_files`，否则职位、企业、资讯、手机端等路由可能失效。

## 7. 路由说明

### 7.1 PC 与前后台

- 前台首页：`/`
- 平台后台：`/admin/`
- 手机后台：`/wapadmin/`
- 分站后台：`/siteadmin/`

后台常见路由形式：

```text
/admin/index.php?m=模块&c=控制器&a=方法
```

例如：

```text
/admin/index.php?m=user&c=users_member
```

### 7.2 业务模块

常用页面：

```text
/job/                 职位
/company/             企业
/resume/              简历
/article/             资讯
/ask/                 问答
/train/               培训
/zph/                 招聘会
/m/                   手机端
/wap/                 WAP 页面
```

伪静态规则主要保存在宝塔站点的 rewrite 配置中。修改模块入口或 URL 规则后，应同时检查：

- Nginx 伪静态
- `config/db.data.php`
- `data/plus/seo.cache.php`
- 后台站点 URL 与 SEO 设置

## 8. 配置与缓存

### 8.1 重要配置

| 文件/目录 | 说明 |
| --- | --- |
| `config/db.config.php` | 数据库连接、表前缀、站点加密密钥 |
| `config/db.safety.php` | 数据库安全配置 |
| `config/db.data.php` | 招聘、会员等字典数据 |
| `data/plus/config.php` | 系统生成的站点配置缓存 |
| `data/plus/*.cache.php` | 分站、SEO、行业、职位等缓存 |
| `data/api/` | 支付、微信等接口配置 |

`data/plus/config.php` 为系统生成文件，通常不应直接手工修改。修改后可能被后台重新生成覆盖。

### 8.2 清理缓存

优先通过后台的“生成缓存”或“清除缓存”功能处理。

手工清理时应只操作生成型缓存：

```text
data/templates_c/
data/cache/
```

清理前先备份，不要误删：

```text
data/plus/config.php
data/plus/domain_cache.php
data/api/
data/upload/
data/logo/
```

### 8.3 地图服务切换

系统支持高德地图和天地图两套接口，后台入口为“系统 -> 网站设置 -> 地图设置”：

| `map_type` | 地图服务 | 相关配置 |
| --- | --- | --- |
| `1` | 高德地图 | `map_key`、`map_secret`、`bmap_webak` |
| `2` | 天地图 | `tdt_map_key`、`tdt_server_key` |

配置要求：

- 天地图浏览器端密钥必须申请为“浏览器端”应用类型，用于加载地图 SDK。
- 天地图服务端密钥只用于地址解析、逆地址解析等后端请求，不会输出到前台页面。
- 企业地址输入使用“精确地址解析 + 附近 POI 检索”，自动回填坐标并提供相关地址建议。
- 不要在 README、Git 提交或公开日志中写入真实密钥。
- 保存地图设置后，系统会生成本站点使用的 `mapurl`，前台模板通过 `map_enabled` 判断地图功能是否可用，不再依赖高德 `map_key` 是否存在。

天地图兼容层文件：

```text
js/tianditu-map-loader.js
js/tianditu-amap-compat.js
```

兼容层负责将现有 `AMap` 调用映射到天地图 `T` 接口，并在站内 GCJ-02 坐标与天地图 WGS-84 坐标之间转换。修改兼容层后应同步更新加载器版本参数，避免浏览器继续使用旧缓存。

上线后至少检查：

```text
PC 地图：/map/
移动地图：/m/index.php?c=map&a=maplist
职位详情地图：/job/<职位ID>.html
企业详情地图：/company/c_show-id_<企业ID>.html
后台系统设置 -> 网站设置 -> 地图设置
```

## 9. 数据库与备份

### 9.1 备份范围

一次完整备份至少包含：

```text
数据库
config/db.config.php
data/plus/config.php
data/api/
data/upload/
data/logo/
uploads/
```

安装包、临时压缩包、日志和缓存不必长期存放在 Web 根目录。

### 9.2 数据库备份示例

```bash
mysqldump \
  --single-transaction \
  --routines \
  --triggers \
  --default-character-set=utf8mb4 \
  -u数据库用户 -p 数据库名 \
  > 100kp_backup_$(date +%F_%H%M%S).sql
```

备份完成后应检查：

- 文件大小是否异常
- 压缩包或 SQL 文件是否位于 Web 根目录之外
- 权限是否为仅管理员可读
- 是否能成功解压/恢复

## 10. 部署流程

1. 在本地确认修改内容和受影响文件。
2. 备份正式数据库、站点目录、上传目录和当前 Web 配置。
3. 上传本次变更文件，不要整站覆盖正式配置和用户上传内容。
4. 检查 `config/db.config.php`、`data/plus/config.php` 等环境相关配置。
5. 设置目录权限。
6. 清理 `data/templates_c/` 和 `data/cache/`。
7. 重新生成系统缓存。
8. 验证以下核心页面：

```text
首页
职位列表与详情
企业列表与详情
简历与会员中心
资讯详情
招聘会
手机端
后台登录
后台会员列表
后台企业列表
```

9. 若启用支付、短信、邮件、微信登录，再分别验证对应流程。
10. 保留本次备份和回滚说明。

## 11. 日志与常见故障

### 11.1 常用日志

生产服务器默认路径通常为：

```text
/www/wwwlogs/100kp.com.log
/www/wwwlogs/100kp.com.error.log
/www/server/php/74/var/log/php-fpm.log
/www/server/php/74/var/log/slow.log
```

同时检查：

```bash
systemctl status nginx
systemctl status php-fpm-74
systemctl status mysqld
dmesg -T | grep -i "out of memory"
```

### 11.2 页面空白或 500

检查：

- PHP-FPM 是否运行
- PHP 错误日志
- 数据库能否连接
- 文件权限是否正确
- 模板缓存是否损坏
- 是否超出 PHP 内存限制

### 11.3 502 Bad Gateway

重点检查：

- PHP-FPM 进程是否存活
- `pm.max_children` 是否过大并触发 OOM
- Unix Socket 路径是否一致
- PHP-FPM 是否达到并发上限
- 是否有慢查询或异常请求

不要用“每 10 分钟重启一次 MySQL”的方式掩盖 OOM 或查询问题。

### 11.4 后台列表一直显示“数据加载中”

打开浏览器开发者工具的 Network 面板，检查对应列表接口是否返回：

- `{"error":0,...}`：正常
- `FAILED: Sql Error!`：SQL 参数或数据库错误
- `401`：后台额外认证或登录状态失效
- `403`：Web 安全规则拦截
- `500`：PHP 运行错误

后台排序参数中的 `t`、`order` 可能以空字符串提交。空排序值必须保持为空，不能直接转换为 `desc`，否则会生成残缺的 `ORDER BY`。

### 11.5 伪静态页面 404

检查：

- Nginx 是否加载 PHPYun rewrite 配置
- 请求是否进入正确的业务目录
- 模块入口文件是否存在
- URL 是否与后台 SEO/伪静态配置一致

### 11.6 上传失败

检查：

- `data/upload/`、`uploads/` 是否可写
- PHP `upload_max_filesize`、`post_max_size`
- 上传文件类型是否在后台允许范围内
- 上传目录是否禁止 PHP 执行
- 磁盘空间是否充足

### 11.7 中文乱码

检查：

- 页面响应是否为 `Content-Type: text/html; charset=UTF-8`
- 数据库、数据表和连接字符集
- PHP 源文件编码
- Smarty 编译缓存是否由旧编码生成
- 是否经过错误的 ISO-8859/GBK 转换

## 12. 安全要求

本项目属于直接暴露在公网的 PHP Web 应用，部署时必须遵守以下规则：

- `config/`、`data/plus/`、`data/backup/`、`install222/` 禁止公开访问。
- `uploads/`、`data/upload/` 等可写目录禁止执行 PHP。
- 不得将整站压缩包、数据库 SQL、源码备份放在 Web 根目录。
- 不得在 `uploads/` 中保留旧版源码副本。
- 后台应同时使用 Web 层认证和 PHPYun 管理账号认证。
- 后台写操作必须保留 CSRF Token 校验。
- 数据库账号只授予当前数据库所需权限，不使用 MySQL `root` 运行站点。
- 关闭 `display_errors`，错误写入日志而不是显示给访客。
- 使用 `open_basedir` 限制 PHP 可访问目录。
- 全站启用 HTTPS，禁止从 HTTPS 跳转到 HTTP。
- 禁用 TLS 1.0/1.1。
- 安装目录使用完成后立即封禁。
- SSH 使用密钥登录，限制 root 密码登录，并启用 Fail2Ban。
- 定期检查 PHP-FPM、MySQL、Nginx 和系统 OOM 日志。

如果发现以下内容，应立即隔离并排查：

- 无类型限制的文件上传脚本
- 不可解释的 `eval`、`assert`、命令执行代码
- Web 根目录中的 `.zip`、`.tar.gz`、`.sql`
- 上传目录中的 `.php` 文件
- 伪造的维修/文件管理面板
- 被追加到正常文件末尾的未知上传表单

## 13. 开发约定

### 13.1 控制器与模型

- 前台控制器：`app/controller/<模块>/`
- 业务模型：`app/model/`
- 平台后台：`admin/` 与 `app/controller/siteadmin/`
- 手机后台：`app/controller/wapadmin/`
- 公共基类：`app/public/`

修改时尽量遵循现有命名、加载方式和返回结构，避免引入与旧系统不兼容的自动加载器。

### 13.2 模板

- 前台模板：`app/template/default/`
- 手机模板：`app/template/wap/`
- 后台模板：`app/template/admin/`
- 手机后台模板：`app/template/wapadmin/`

Smarty 模板使用：

- 左分隔符：`{yun:}`
- 右分隔符：`{/yun}`

修改模板后必须清理 `data/templates_c/`，并确认 PC 和手机页面都正常。

### 13.3 数据库查询

- 新代码应使用参数化查询或现有的 `escape_string` 机制。
- 动态排序字段必须使用白名单。
- 动态表名必须限制为字母、数字和下划线。
- 分页参数必须转换为整数并限制最大值。
- 不要在错误响应中输出数据库账号、SQL 或堆栈。

### 13.4 上传

- 同时校验扩展名、文件大小、MIME 和目录。
- 服务端生成文件名，避免直接使用用户文件名。
- 上传目录禁止 PHP 执行。
- 上传成功后仅返回站内 URL。

## 14. 发布前检查清单

- [ ] 数据库和文件已备份
- [ ] `config/db.config.php` 未写入错误环境
- [ ] HTTPS 地址正确
- [ ] 伪静态配置正确
- [ ] `install222/`、`config/`、`data/plus/` 不可公开访问
- [ ] 上传目录中的 PHP 无法执行
- [ ] 备份压缩包不在 Web 根目录
- [ ] PHP 文件通过 `php -l`
- [ ] Nginx 配置通过 `nginx -t`
- [ ] PHP-FPM 配置通过语法检查
- [ ] 首页、职位、企业、资讯、手机端均为 200
- [ ] 后台登录和后台列表接口正常
- [ ] 上传、登录、注册、投递等写操作已使用测试数据验证
- [ ] 支付、短信、邮件和微信接口已使用测试环境验证
- [ ] 日志中无新增 500、502、SQL 错误和 OOM

## 15. 当前注意事项

- 本地目录不是现代 Composer 项目，第三方依赖直接位于 `app/include/`、`api/` 和前端静态目录中。
- 本地副本中的 `install222/`、`uploads/app/` 等旧安装或源码副本不应原样部署到正式环境。
- 服务器已针对 OOM、后台列表排序、SQL 注入、公开备份、上传后门和 HTTPS 降级做过修复；本地副本修改后部署前应与正式环境进行文件差异核对，避免覆盖正式修复。
- PHP 7.4、MySQL 5.7 和 CentOS 7 已停止维护，应安排独立测试环境进行升级评估。
- PHPYun 为商业软件，源码的使用、修改和分发应遵守已购买的授权条款。

## 16. 快速检查命令

```bash
# PHP 语法检查
find . -type f -name '*.php' -print0 | xargs -0 -n1 php -l

# Nginx 配置检查
nginx -t

# PHP-FPM 配置检查
/www/server/php/74/sbin/php-fpm -t

# 最近 200 个 500/502 请求
grep -E '" (500|502) ' /www/wwwlogs/100kp.com.log | tail -n 200

# 最近 OOM 记录
dmesg -T | grep -i "out of memory" | tail -n 50
```

---

文档路径：`F:\Codex\100kp.com\README.md`

