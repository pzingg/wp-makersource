Maker Source Wordpress Project
==============================

Core and plugin versions
------------------------
 * WordPress v4.1, from github master commit 015f786f27a934583a142ffbc7834f90453d54f6
 * userpro v2.13 ($28 from codecanyon.net) - user registration w social
 * userpro-bookmarks v1.4.1 - save posts and projects to collections
 * pods v2.4.3  - Custom post types and taxonomies, relationships
 * i-order-terms v1.3.1 - Custom taxonomony ordering
 * facetious v1.1.4 - Faceted search
 * codepress-admin-columns v2.2.9 - Display field columns in Admin UI edit table
 * nav-menu-roles v1.6.3 - Show menu items based on logged in or not
 * redirection v2.3.11 - Redirect home page URL / to /project
 * rewrite-rules-inspector v1.2.1 - Debug rewrite rules
 * jetpack v3.2.1 - Other niceties from the Wordpress.com core team

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

Custom Taxonomy Ordering
------------------------
Installed i-order-terms plugin and dragged term items into proper order.  This adds custom\_order column in term\_taxonomy table.

Faceted Search
--------------
Added Facetious search widget to main widget area.

Display of Projects in Gallery
------------------------------
Use twentythirteen "gallery" post format for all projects
Enable post formats for project pod in Pods Admin.

Added "Recent Projects" Pods - List Items widget to main widget area.

Save Projects to Per-User Collections
-------------------------------------
Use UserPro User Bookmarks Add-On ('userpro-bookmarks').  Changed "Default Collection" to "Favorites".

Enabled display of user collections on profile pages, by "subclassing" 
/userpro/templates/view.php to /twentythirteen/userpro/view.php.  Note that code in userpro loads subclassed templates using get\_template\_directory(), which specifies the parent theme, not the child theme.

TODO
----
Review other plugin code to see if there's a better way
to manage and add new related project resources than the Pods multi-select
input. Should be able to search for project resources or add new,
and explicitly remove resources.

