sudo apt-get install apache2 php5 libapache2-mod-php5 php5-mysql php5-gd libssh2-php
sudo apt-get install mysql-server-5.5
sudo apt-get install php-fpdf

sudo usermod -a -G www-data mantenimientocl

sudo cd /var/www/html
sudo chown -R mantenimientocl:www-data *

sudo nano /etc/php5/apache/php.ini

| displayerrors=Off
