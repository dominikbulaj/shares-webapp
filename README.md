Track URL shares
================

This is source code for simple webapp that tracks how many times given URL was shared on social media. It returns 
information from: Facebook, Twitter, Google+, LinkedIn, Pinterest and StumbleUpon.

**Try it on: [shares.webit.pl](http://shares.webit.pl)**

## Requirements
As it uses PHP as backend (api endpoint) you need to be running **PHP 5.4+**. Backend build on top of [Silex](http://silex.sensiolabs.org/).
Additionally it stores data in **MySQL** database, so you will need one.


## Installation:

* PHP dependencies are managed by [Composer](https://getcomposer.org/). To install everything required run following command in main application directory: ```composer install```. If you didn't use Composer before - check how to [install](https://getcomposer.org/download/) it. 

 **IMPORTANT** you must run composer in Linux/Unix shell as it created some directories using `mkdir` command.
 
 It will install Silex micro framework, Doctrine DBAL, Monolog and Symphony Yaml and [shares-counter-php](https://github.com/dominikbulaj/shares-counter-php) library that's responsible for checking social media APIs.

 
* Frontend dependencies are manages by [Bower](http://bower.io/). Installing dependencies is as easy as running ```bower install``` assuming you already have installed Bower. If not - check how to [install Bower](http://bower.io/#install-bower).

  It will install: AngularJS with additional modules, Bootstrap and jQuery. Because on inconsistency Bower package vs GitHub repository - [Material Design for Bootstrap](http://fezvrasta.github.io/bootstrap-material-design/) is bundled with application assets.


* Finally you need to create MySQL tables. Assuming you have running MySQL server and already created new database, easiest way to create tabels is to run: ```mysql -h{host} -u{username} -p {database name} < database.sql```. Check MySQL [command line tool](http://dev.mysql.com/doc/refman/5.6/en/mysql.html) for additional parameters and how-to.

## Configuration
There's one configuration file. It stores database access information (for now) and is located in ```/app/config.yml```
Just fill in informations about: host, database name, username and password.

By default after Composer during installation will create two directories in `api/`:

1. `api/cache/` for storing Silex cache
2. `api/log/` log directory where are logged request & response information as well application issues

**SECURITY NOTE** both directories have default access permissions set to 777 (full access). Please change it allowing write from web-server user only (apache, www-data, etc.)

## Todo
Unit testing

## License
Copyright (c) 2014 [Dominik Bułaj](http://www.webit.pl). See the LICENSE file for license rights and limitations (MIT)