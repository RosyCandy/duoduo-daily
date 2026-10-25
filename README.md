# DuoDuo’s Daily

个人博客源码，线上站点：https://duoduo.best/ 。

仓库收录从生产服务器同步的森林配色新版，包括首页大图、分类与归档、标签漂移和鼠标连线、音乐播放器与滚动歌单、账户与管理后台、正文图片上传，以及 1 核 1G 的 Docker 运行配置。

## 源码与数据

- `html/`：PHP 页面、API、样式、脚本与站点基础图片。
- `nginx/`、`php/`、`mysql/`：服务镜像与低内存配置。
- `scripts/`：数据库备份与证书续期脚本。
- `LOW_MEMORY.md`：1GB 内存的运行方式、配置和采样数据。
- `MIGRATION.md`：已有生产环境的迁移与运维记录。

音乐音频、数据库数据、上传的新图片、环境密钥、TLS 证书、运行备份和验证截图不入库；音乐页、播放器、上传功能的代码完整保留。仓库中早期已经跟踪的示例图片仍保留。

克隆得到源码，不会自动获得线上文章、账户、评论、歌单或音频。完整恢复现有博客需要单独恢复 SQL 备份、上传图片和音乐目录。

## 配置与部署

```bash
git clone https://github.com/RosyCandy/duoduo-daily.git
cd duoduo-daily
cp .env.example .env
chmod 600 .env
```

编辑 `.env`：为 MySQL 设置独立的应用用户密码和 root 密码；首次部署空数据库时填写 `BLOG_ADMIN_USERNAME` 与 `BLOG_ADMIN_PASSWORD`。这些值仅用于首次创建管理员，不会改动已有管理员密码。AI 功能可选填写 `DASHSCOPE_API_KEY`。

当前 Nginx 配置用于 `duoduo.best` 的 HTTPS 生产环境。启动前，把有效证书及其 archive/live 目录放在 `ssl/certbot/` 下，使证书路径与 `nginx/default.conf` 一致；其他域名需要相应修改 Nginx 和证书配置。

```bash
mkdir -p html/assets/music html/assets/music_m4a html/assets/images/posts html/assets/images/avatars
COMPOSE_PARALLEL_LIMIT=1 docker compose build php
docker compose config --quiet
docker compose up -d --no-build
```

确认 MySQL 健康检查通过后访问站点；PHP 会创建缺失的数据表。按部署用户权限使用 `sudo chown -R 82:82 html/assets/images/posts html/assets/images/avatars html/assets/music`，让 Alpine PHP 的 www-data 能保存上传文件。既有站点应先备份，再恢复数据库与媒体文件。

## 入库时的配置处理

本次以 2026-10-25 从生产服务器下载的源码为准。为了让仓库不再写死凭据，`html/includes/db.php`、`html/api/chat.php` 改为读取环境变量，Compose 补充相应变量传递；同时取消默认的固定管理员密码。其余业务源码与生产快照保持一致。此次整理没有修改正在运行的服务器配置；以后部署此版本时必须先准备 `.env`，不要直接覆盖线上配置文件。

旧版本历史曾包含明文凭据。本次已从当前源码移除，历史提交并不会因此消失；此前使用过的凭据应另行轮换。
