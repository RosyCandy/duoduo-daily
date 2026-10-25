# duoduo.best 部署与迁移记录

## 2026-10-21 迁移结果

- 原服务器：`opc@140.245.61.181`，ARM64。
- 新服务器：`rosyhazes@35.209.43.178:22`，Debian 12 / x86_64，约 969MB 内存，沿用实际已有的 7GB swap。
- 新项目目录：`/home/rosyhazes/duoduo-daily`。
- `duoduo.best` 和 `www.duoduo.best` 的 A 记录均已指向新 IP，没有 AAAA 记录。
- 最后一次同步先停止旧 PHP 并将旧 MySQL 设为只读，再导出、导入 SQL。7 张表逐表校验值全部一致：22 篇文章、40 首音乐、1 条评论。
- 126 个网站及媒体文件共 1,552,017,334 字节，SHA-256 全部通过；最终源文件清单没有新增、修改或删除。
- 首页、www、HTTP 到 HTTPS 跳转、文章页、Markdown 导出、文章/个人资料/评论/音乐 API 及并发请求通过公网验收。40 首小程序音乐都有 m4a 链接，原始音频和 m4a 的 Range 请求返回 206。
- 旧机 PHP 已停止，数据库保持只读，Nginx 临时转发到新机。旧机两项项目定时任务已停用，供 DNS 缓存过渡和回退使用；不要再恢复旧机写入。
- 按用户最新要求取消 GitHub 数据库备份。刚迁到新机的 GitHub 部署私钥已删除，只保留本地备份任务。

## 1 核 1G 运行配置

`docker-compose.yml` 限制 Nginx 64MB、PHP 192MB、MySQL 448MB，并设置有限 swap 余量和日志滚动。验收时业务容器合计约 160MB，未发生 OOM 或重启；实际占用随负载变化。

MySQL 使用与原机相同的 `8.0.46`，InnoDB 缓冲池 128MB，关闭 performance schema，最多 20 个连接。MySQL 端口仅在 Docker 内网可用，没有发布 3307。

PHP 使用 Alpine 运行镜像，保留 pdo_mysql、zip、mbstring，按需启动，最多 2 个工作进程，每个请求内存限制 96MB。无需 JIT。PHP 镜像须在新架构构建，不能直接搬运 ARM64 镜像。上传目录归 Alpine 的 www-data（UID/GID 82）所有。

```bash
cd /home/rosyhazes/duoduo-daily
docker compose config --quiet
docker compose up -d --no-build
docker compose ps
docker stats --no-stream
```

需要重建 PHP 时：

```bash
COMPOSE_PARALLEL_LIMIT=1 docker compose build php
docker compose up -d --no-build php
```

数据库只能通过 SQL 逻辑导出迁移，不要复制正在运行的 `mysql-data/`。线上 `.env` 与 `html/includes/db.php` 必须匹配，`.env` 保持权限 600。

## 数据库备份

用户 crontab（系统时区 UTC）：

```cron
0 16 * * 0 /home/rosyhazes/duoduo-daily/scripts/db-backup.sh >> /home/rosyhazes/db-backup.log 2>&1
```

即北京时间每周一 00:00。脚本使用一致性 SQL 导出，先验证 gzip 再落盘到项目的 `.backups/`，目录权限 700、文件权限 600，并用 flock 避免重叠执行。最终迁移后已手动执行并通过 gzip 校验。

```bash
cd /home/rosyhazes/duoduo-daily
scripts/db-backup.sh
```

本机完整迁移备份在 `/Users/san/Desktop/duoduo-daily/.migration/20261021/`；`snapshot/` 是初始站点与媒体，`final/` 是停写后的最终 SQL、逐表校验值和文件清单。它们包含私有数据及证书，已排除 Git，保留在权限受限目录中。

## HTTPS 与防火墙

保留原有有效证书，当前到期日为 2026-12-31。整体迁移 `ssl/certbot/`，包括 live 指向 archive 的相对符号链接和 ACME 账户。新机与云防火墙均须放行 TCP 80/443。

root crontab（UTC）：

```cron
0 3,15 * * * /home/rosyhazes/duoduo-daily/scripts/renew-cert.sh >> /var/log/duoduo-renew-cert.log 2>&1
```

续期使用与原证书配置匹配的 Certbot v5.8.0，内存限制 192MB；保留 `/etc/letsencrypt` 与 `/var/www/html` 容器路径，成功后验证并重载 Nginx。迁移后已成功执行 `--dry-run` 演练。手动演练：

```bash
sudo /home/rosyhazes/duoduo-daily/scripts/renew-cert.sh --dry-run --no-random-sleep-on-renew
```

## 旧机过渡

旧 Nginx 转发到 `35.209.43.178`，保持上游 TLS 主机名和证书校验。两域名 DNS 均生效、旧缓存过期后即可由用户下线旧实例。

若本机代理把旧 IP 连接转到了新机，可通过新机 SSH 跳转访问旧机，并继续严格验证原来的主机指纹；不要删除 known_hosts 或关闭主机验证。
