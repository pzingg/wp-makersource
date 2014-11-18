Maker Source Wordpress Project
==============================


Initial source code tarball
---------------------------
Built from https://develop.svn.wordpress.org/trunk@30350

git-svn-id: http://core.svn.wordpress.org/trunk@30349 1a063a9b-81f0-0310-95a4-ce76da25c4cd
 master
 
commit 015f786f27a934583a142ffbc7834f90453d54f6 1 parent b3e14b3
Authored on November 14, 2014

Apache and MySQL configuration and WordPress 5-minute install
-------------------------------------------------------------
1. Apache
    Install .htaccess
    Modify /etc/apache2/httpd.conf to enable php5 and rewrite modules
    Install /etc/apache2/extra/httpd-vhosts.conf

2. MySQL
    CREATE USER 'wpadmin'@'localhost' IDENTIFIED BY 'wpadmin'
    CREATE DATABASE wp\_bamem
    GRANT ALL PRIVILEGES ON wp\_bamem.* TO 'wpadmin'@'localhost'

3. WordPress
    Edit wp-config.php
    define('DB_NAME', 'wp\_bamem');
    define('DB_USER', 'wpadmin');
    define('DB_PASSWORD', 'wpadmin');
    define('DB_HOST', 'localhost:/tmp/mysql.sock');
    $table_prefix  = 'bamem\_';
    define('WP_DEBUG', true);
    Generate salts for wp-config.php keys

Pods
----
1. Created 6 new custom taxonomy pods:
    Subject Areas
    Grade Levels
    Maker Skills
    Skill Levels
    Durations
    Resource Types

2. Created 2 new custom post type pods:
    Projects ('project')
    Project Resources ('project\_resource')

3. Added a pods list items widget to the Widgets Area to show recent projects.

Custom Taxonomy Ordering
------------------------
Installed i-order-terms plugin and dragged term items into proper order.  This adds custom\_order column in term\_taxonomy table.

Custom Display of Projects
--------------------------
TBD

