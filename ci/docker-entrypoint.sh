#!/bin/bash
_bootstrap() {
    cd /var/www/html/docroot;
    drush si ezcontent --db-url='mysql://ezcontent:ezcontent@ezcontent_db/ezcontent' --site-name='EZ Content BLT' --account-name='admin' --account-pass='admin12345' -y
    drush en ezcontent_demo -y
    drush en ezcontent_api -y
    drush cr
}

_bootstrap
