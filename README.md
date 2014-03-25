Animhist
========

Animated History project - A project built with Laravel, Google Maps API, Google Fusion Table API and Google Drive API.

How to set up the project
-------------------------
- Make sure that you have installed Apache, MySQL and PHP >= 5.5 (or XAMPP full package if lazy) in your PC.
- Make sure that you have installed Composer, Git. Google it if you don't know the link, it should be at the top seach result.
- Configure Apache/PHP in your PC to allow Laravel properly working (referring to http://www.youtube.com/watch?v=u_OZA27Batc, http://www.youtube.com/watch?v=JNn_tS2KpcY), including: Enable OpenSSL (php_openssl) and File Info (php_fileinfo) in php.ini, Enable Mcrypt PHP Module, Config Virtual Host. Also need to enable Apache mod_rewrite as well:
```sh
sudo a2enmod rewrite
sudo service apache2 restart
```
or refer to http://www.leonardaustin.com/blog/technical/enable-mod_rewrite-in-xampp/ if you are using XAMPP.
- Clone this repo to your local disk, using Terminal or Git Bash, or any other Git GUI program. If you are using Windows and XAMPP, clone to htdocs of your XAMPP folder; otherwise, just clone it to your preferred folder.
```sh
git clone https://github.com/zuyetawarmatik/CS3283-animhist
```
- In your terminal, go to folder CS3283-animhist/animhist, type:
``` sh
composer update --no-scripts
composer install
```
to install the project.
- In MySQL (phpmyadmin), create a new database name "animhist", collation = utf8_unicode_ci.
- Go to animhist/app/config/database.php to config MySQL database souce by providing your database host, username and password.
- Using Laravel Migration to install project database schema by Terminal (you should be in animhist folder as well to type this command):
``` sh
php artisan migrate
```
- You can setup a virtual host directing to the project folder by following this video http://www.youtube.com/watch?v=JNn_tS2KpcY
or just using terminal and cd to animhist folder, type:
```sh
php artisan serve
```
- And then you can go to your setup virtual host link or just http://localhost:8000 to play with the application!

Contributing to the project (For beginners)
-------------------------------------------
- Remember to turn on Apache and MySQL service beforehand.
- Suggested IDE: Eclipse with PDT Plugin. Setting up Workspace as CS3283-animhist folder, and import CS3283-animhist/animhist folder to the workspace as the project.
- Remember to do this if you finish a robust feature: Git Commit Changes and Git Push.
- Remember to do this if you are lacking of updating the project: Git Pull, Git Merge and Resolve Merging Conflicts. If you encounter any new migrations in app/database/migrations folder, please run
``` sh
php artisan migrate
```
to keep in track of database consistency.
Otherwise, if you encounter error when migrating schema into your own localhost DB, please delete all tables in project database in SQL (or use PHPMyAdmin) and then run this in terminal:
``` sh
php artisan migrate:refresh
```
to reinstalling the whole project database.
