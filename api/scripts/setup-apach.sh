#!/bin/bash

# さくらのVPSのUbuntuにLAMP環境を構築するスクリプト

# Ubuntu システムを更新
sudo apt update
sudo apt upgrade
sudo apt autoclean
sudo apt autoremove

# Apache のインストールと確認
sudo apt install -y apache2
# apache2 -v #バージョン確認
sudo service apache2 start
# sudo systemctl status apache2 # 起動確認
sudo a2enmod rewrite # mod_rewriteを有効化
sudo service apache2 restart

# PHP のインストール
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php # Ondřej SurýのPPAは一般的に使われている
sudo apt update
sudo apt install -y php8.4
# php -v # バージョン確認

# Composer の準備
sudo apt install -y curl php-cli php-mbstring unzip
# sqliteの準備
sudo apt install -y sqlite3 php8.4-sqlite3
# 必要な拡張モジュールをインストール
sudo apt install -y php8.4-bcmath php8.4-ctype php8.4-fileinfo php8.4-mbstring php8.4-tokenizer php8.4-xml php8.4-curl php8.4-gd php8.4-zip php8.4-pdo

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

# Apache の再起動
sudo service apache2 restart

# /var/www/html の所有権を変更
sudo chown -R www-data:www-data /var/www/html
sudo usermod -a -G www-data $USER # 実行したユーザーをwww-dataに追加
sudo chmod -R 775 /var/www/html # ファイルのパーミッションを変更(cloneしたいから) cloneが終わったら775を755に戻す

# key:generate
# ssh-keygen -t rsa

exit $RESULT
