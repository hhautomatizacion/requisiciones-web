sudo apt-get install apache2 php5 libapache2-mod-php5 php5-mysql php5-gd libssh2-php
sudo apt-get install mysql-server-5.5
sudo apt-get install php-fpdf php-mbstring

sudo usermod -a -G www-data mantenimientocl

Cambiar configuracion de mysql (tamaño maximo de paquete y permitir conexiones desde cualquier host)

::

   sudo nano /etc/mysql/my.cnf
   
----

| bind-address=0.0.0.0

----

| max_allowed_packet=100M

----
   
Cambiar configuracion PHP (tamaño maximo para subir archivos y tiempo maximo de ejecucion del script)

::

   sudo nano /etc/php5/apache2/php.ini
   
----

| upload_max_filesize = 100M

----

| post_max_size = 100M

----

| max_execution_time = 300

----

Conceder permisos de red al usuario root y crear la base de datos en el servidor

::

   mysql -u root -p
   
----

| GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'manttocl' WITH GRANT OPTION;   
| CREATE DATABASE requisiciones;
| EXIT

----

sudo cd /var/www/html
sudo mkdir uploads
sudo chown -R mantenimientocl:www-data *
sudo chmod -R 775 *



