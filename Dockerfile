FROM php:8.2-apache

# 开启 Apache 的 mod_rewrite 重写模块（解决 RewriteEngine 报错）
RUN a2enmod rewrite

# 修改 Apache 配置，允许 .htaccess 文件覆盖默认规则
RUN sed -i '/<Directory \/var\/www\/html>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# 将你的代码复制到网站根目录
COPY . /var/www/html/

# 暴露 80 端口
EXPOSE 80