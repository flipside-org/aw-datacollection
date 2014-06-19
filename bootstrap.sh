#!/usr/bin/env bash
VERSION=2

if [ -e runonce.vagrant ]; then
  CURRENT_VERSION=$(tail -n 1 runonce.vagrant)
  # Just an early check to kill the script.
  if (( $CURRENT_VERSION >= $VERSION )); then
    echo "Provisioning at latest version. VERSION: $CURRENT_VERSION"
    exit;
  fi
else
  CURRENT_VERSION=0
fi

if (( $CURRENT_VERSION < 1 )); then
  CURRENT_VERSION=1
  echo "Installing version $CURRENT_VERSION"
  #######################################
  ##        START PROVISIONING         ##
  #######################################
  sudo apt-get update

  # General Program
  sudo apt-get -y install vim
  sudo apt-get -y install unzip
  sudo apt-get -y install make
  sudo apt-get -y install curl

  # Apache
  sudo apt-get -y install apache2
  sudo a2enmod rewrite
  echo 'ServerName localhost' | sudo tee -a /etc/apache2/httpd.conf
  sudo service apache2 restart

  # mysql
  sudo echo "mysql-server-5.5 mysql-server/root_password password root" | debconf-set-selections
  sudo echo "mysql-server-5.5 mysql-server/root_password_again password root" | debconf-set-selections
  sudo apt-get -y install mysql-server-5.5

  # php
  sudo apt-get -y install php5 php5-dev php5-cli php-pear
  sudo apt-get -y install php5-mysql php5-suhosin php-pear php5-curl php5-gd php5-imagick php5-mcrypt php5-memcache php5-xdebug php-apc
  sudo apt-get -y install libapache2-mod-php5
  sudo service apache2 restart

  # Setup link
  sudo ln -s /vagrant /var/www/airwolf

  # Delete index.html to have directory listing
  sudo rm /var/www/index.html

  # Install Mongo
  sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 7F0CEB10
  echo 'deb http://downloads-distro.mongodb.org/repo/ubuntu-upstart dist 10gen' | sudo tee /etc/apt/sources.list.d/mongodb.list
  sudo apt-get update
  sudo apt-get -y install mongodb-org

  # Mongo extension for php
  no | sudo pecl install mongo
  echo 'extension=mongo.so' | sudo tee -a /etc/php5/apache2/php.ini
  echo 'extension=mongo.so' | sudo tee -a /etc/php5/cli/php.ini
  sudo service apache2 restart

  # Install phpunit
  sudo pear config-set auto_discover 1
  sudo pear install pear.phpunit.de/PHPUnit
  
  # Install php5-xsl extension
  sudo apt-get -y install php5-xsl
  echo 'extension=php_xsl.so' | sudo tee -a /etc/php5/apache2/php.ini
  echo 'extension=php_xsl.so' | sudo tee -a /etc/php5/cli/php.ini
  sudo service apache2 restart

  # Python xlrd
  sudo apt-get -y install python-pip
  sudo pip install xlrd
  
  # Admin Apps

  # Phpmyadmin
  wget https://github.com/phpmyadmin/phpmyadmin/archive/RELEASE_4_1_3.tar.gz
  tar -xzf RELEASE_4_1_3.tar.gz
  rm RELEASE_4_1_3.tar.gz
  sudo mv phpmyadmin-RELEASE_4_1_3/ /var/www/phpmyadmin

  # Genghis App
  wget https://github.com/bobthecow/genghis/archive/v2.3.10.zip
  unzip v2.3.10.zip
  rm v2.3.10.zip
  sudo mv genghis-2.3.10 /var/www/genghis

  # Some php configurations 

  # Set display errors to On
  # Author: olaf@flipside.org
  PHP_FILE="/etc/php5/apache2/php.ini"
  sudo sed -i "s/display_errors = Off/display_errors = On/" $PHP_FILE

  # Change AllowOverride None to AllowOverride All on line 11 for default site vhost.
  # Multiline regex matched don't work with sed so I resorted to php.
  # Better solution is welcome! 
  php -r '$f="/etc/apache2/sites-available/default";$d=file_get_contents($f);$d=preg_replace("/(<Directory \/var\/www\/>.*?AllowOverride )None(.*?<\/Directory>)/s","$1All$2",$d);file_put_contents($f,$d);' 

  sudo service apache2 restart


  #######################################
  ##         END PROVISIONING          ##
  #######################################
fi

if (( $CURRENT_VERSION < 2 )); then
  CURRENT_VERSION=2
  echo "Installing version $CURRENT_VERSION"
  #######################################
  ##        START PROVISIONING         ##
  #######################################
  
  sudo apt-get update
  sudo apt-get install -y python-software-properties python g++ make
  sudo add-apt-repository -y ppa:chris-lea/node.js
  sudo apt-get update
  sudo apt-get install -y nodejs

  sudo npm install -g casperjs

  sudo wget https://bitbucket.org/ariya/phantomjs/downloads/phantomjs-1.9.7-linux-i686.tar.bz2
  sudo tar -xf phantomjs-1.9.7-linux-i686.tar.bz2
  sudo rm phantomjs-1.9.7-linux-i686.tar.bz2
  sudo mv phantomjs-1.9.7-linux-i686/ /opt/phantomjs
  sudo ln -s /opt/phantomjs/bin/phantomjs /usr/bin/phantomjs
  
  #######################################
  ##         END PROVISIONING          ##
  #######################################
fi

# Create file to make it run according to versions.
echo 'File to make Vagrant provisioning run according to versions. Do not delete.' > runonce.vagrant
echo 'Current version:' >> runonce.vagrant
echo $CURRENT_VERSION >> runonce.vagrant
exit;

# NEW VERSION CODE
# Remove comments and change version number.
# Do not forget to change the version number at the top of the file.
<<COMMENT1
if (( $CURRENT_VERSION < 3 )); then
  CURRENT_VERSION=3
  echo "Installing version $CURRENT_VERSION"
  #######################################
  ##        START PROVISIONING         ##
  #######################################
  
  # Code here
  
  #######################################
  ##         END PROVISIONING          ##
  #######################################
fi
COMMENT1
