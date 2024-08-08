# 使用官方的 PHP 基礎映像
FROM php:8.3-cli

# 更新包列表並安裝依賴，然後清理緩存以減少映像大小
RUN apt-get update && apt-get install -y --no-install-recommends \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_mysql gd zip pcntl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# 從官方 Composer 映像中復制 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 創建一個非root用戶
RUN useradd -m symfony
USER symfony

# 設置工作目錄
WORKDIR /var/www

# 複製應用文件
COPY --chown=symfony:symfony . .

# 安裝應用依賴
RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist --optimize-autoloader

# 切換回 root 用戶以便安裝系統級別的軟件包
USER root

# 暴露端口 9000 並啟動 PHP-FPM 服務
EXPOSE 9000
CMD ["php-fpm"]
