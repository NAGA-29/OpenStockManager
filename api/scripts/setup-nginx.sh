#!/bin/bash

# さくらのVPSのUbuntuにLEMP環境を構築するスクリプト
# 変数宣言
PHP_VERSION=php8.4
TIMEZONE="Asia/Tokyo"

# Ubuntu システムを更新
sudo apt update
sudo apt upgrade -y
sudo apt autoclean
sudo apt autoremove -y

# Nginx のインストールと確認
sudo apt install -y nginx
nginx -v #バージョン確認
sudo systemctl start nginx
sudo systemctl status nginx # 起動確認

# PHP-FPM のインストール
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php # Ondřej SurýのPPAは一般的に使われている
sudo apt update
sudo apt install -y $PHP_VERSION-fpm

# CHANGE: 2026-02-03 Gitは不要なためGitインストール手順を削除

# supervisorのインストール
sudo apt install -y supervisor

# Composer の準備
# sudo apt install -y curl php-cli php-mbstring unzip
sudo apt install -y curl ${PHP_VERSION}-cli ${PHP_VERSION}-mbstring unzip # 2026-02-03 PHPバージョン変数を使用するように変更
# sqliteの準備
sudo apt install -y sqlite3 $PHP_VERSION-sqlite3
# 必要な拡張モジュールをインストール
sudo apt install -y $PHP_VERSION-bcmath $PHP_VERSION-ctype $PHP_VERSION-fileinfo $PHP_VERSION-mbstring $PHP_VERSION-tokenizer $PHP_VERSION-xml $PHP_VERSION-curl $PHP_VERSION-gd $PHP_VERSION-zip $PHP_VERSION-pdo $PHP_VERSION-pgsql $PHP_VERSION-gmp

# Composer のインストール
EXPECTED_CHECKSUM="$(wget -q -O - https://composer.github.io/installer.sig)"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]
then
    >&2 echo 'ERROR: Invalid installer checksum'
    rm composer-setup.php
    exit 1
fi

php composer-setup.php --quiet
RESULT=$?
rm composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
composer --version

# # セキュリティ強化: Uncomplicated Firewall (UFW)の設定
# echo "ファイアウォールを設定中..."
# sudo apt install -y ufw
# sudo ufw allow 'Nginx Full'
# sudo ufw allow 'OpenSSH'
# sudo ufw --force enable

# Nginxの設定をPHPと連携するように変更
echo "Nginxの設定を変更中..."
# 設定ファイルのバックアップを作成
sudo cp /etc/nginx/sites-available/default /etc/nginx/sites-available/default.bak
echo "Nginxの設定ファイルをバックアップしました: /etc/nginx/sites-available/default.bak"

sudo tee /etc/nginx/sites-available/default > /dev/null <<EOL
server {
    listen 80 default_server;
    listen [::]:80 default_server;

    root /var/www/html/public;
    index index.php index.html index.htm;

    server_name _;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/${PHP_VERSION}-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOL

# Nginx の再起動
sudo systemctl restart nginx
sudo systemctl restart $PHP_VERSION-fpm
# sudo systemctl restart php8.2-fpm

# /var/www/html の所有権を変更
sudo chown -R www-data:www-data /var/www/html
sudo usermod -a -G www-data $USER # 実行したユーザーをwww-dataに追加
sudo chmod -R 775 /var/www/html # ファイルのパーミッションを変更(cloneしたいから) cloneが終わったら775を755に戻す

# SSHセキュリティ強化設定
# echo "SSHセキュリティ設定中..."
# sudo cp /etc/ssh/sshd_config /etc/ssh/sshd_config.bak
# sudo sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin no/' /etc/ssh/sshd_config
# sudo sed -i 's/#PasswordAuthentication yes/PasswordAuthentication no/' /etc/ssh/sshd_config
# sudo systemctl restart ssh

# # 自動更新の設定
# echo "自動更新を設定中..."
# sudo apt install -y unattended-upgrades
# sudo dpkg-reconfigure -plow unattended-upgrades

echo "Complete setup-nginx.sh"
exit $RESULT
