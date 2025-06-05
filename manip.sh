#!/bin/bash

# This script is used to import and manipulate data from CSV  files to the new wordpress site.
# Before running this script, make sure to follow the instructions below:
# 1. Create a new database and user in your MySQL server.
# 2. Grant the user all privileges on the new database.
# 3. Create a new WordPress site and configure it to use the new database.
# 4. Make sure to have the CSV files in the next directory : /var/lib/mysql-files/[NAME_SITE]
# 5. Make sure you imported all users from the old site to the new site, same for the uploads directory.
# END
#
# To run this script, use the following command:
# ./manip.sh [OPTIONS] [NAME_SITE] [DB_NAME] [DB_USER] [DB_PASS]
# Options:
# -h, --help: Show this help message
# -u, --user: To change the user_id of userspost table
# -p, --post: To change the post
# -a, --all: To change all the user_id and post_id


# /var/lib/mysql-files/$NAME_SITE/


# Help message
if [ "$#" -eq 0 ] || ( [ "$#" -eq 1 ] && { [ "$1" == "-h" ] || [ "$1" == "--help" ]; } ); then
    echo "Usage: $0 [OPTIONS] [NAME_SITE] [DB_NAME] [DB_USER] [DB_PASS]"
    echo "Options:"
    echo "-h, --help: Show this help message"
    echo "-u, --user: To change the user_id and add all users data"
    echo "-p, --post: To change the post and add all posts data"
    echo "-f, --forminator: To change the post_id and add all forminator's data"
    echo "-a, --all: To change and add all users, posts and forminator's data"
    exit 0
fi

if [ "$#" -ne 5 ]; then
    echo "Usage: $0 [OPTIONS] [NAME_SITE] [DB_NAME] [DB_USER] [DB_PASS]"
    exit 1
fi

OPTIONS=$1
NAME_SITE=$2
DB_NAME=$3
DB_USER=$4
DB_PASS=$5

# Create directory in mysql-files if it doesn't exist
if [ ! -d "/var/lib/mysql-files/$NAME_SITE" ]; then
    echo "Creating directory /var/lib/mysql-files/$NAME_SITE"
    mkdir -p /var/lib/mysql-files/$NAME_SITE
fi
if [ ! -d "/var/lib/mysql-files/$NAME_SITE/forminator" ]; then
    echo "Creating directory /var/lib/mysql-files/$NAME_SITE/forminator"
    mkdir -p /var/lib/mysql-files/$NAME_SITE/forminator
fi
if [ ! -d "/var/lib/mysql-files/$NAME_SITE/contest" ]; then
    echo "Creating directory /var/lib/mysql-files/$NAME_SITE/contest"
    mkdir -p /var/lib/mysql-files/$NAME_SITE/contest
fi

# Check if the CSV files exist
if [ "$OPTIONS" == "-u" ] || [ "$OPTIONS" == "-a" ]; then
    if [ ! -f "./$NAME_SITE/old_users.csv" ]; then
        echo "Error: old_users.csv file not found in /$NAME_SITE"
        exit 1
    else
        echo "old_users.csv file found in /$NAME_SITE"

        cp ./$NAME_SITE/old_users.csv /var/lib/mysql-files/$NAME_SITE
    fi
    if [ ! -f "./$NAME_SITE/usermeta.csv" ]; then
        echo "Error: old_users.csv file not found in /$NAME_SITE"
        exit 1
    else
        echo "usermeta.csv file found in /$NAME_SITE"
    fi
fi
if [ "$OPTIONS" == "-p" ] || [ "$OPTIONS" == "-a" ]; then
    if [ ! -f "./$NAME_SITE/old_posts.csv" ]; then
        echo "Error: old_posts.csv file not found in /$NAME_SITE"
        exit 1
    else
        echo "old_posts.csv file found in /$NAME_SITE"
    fi
    if [ ! -f "./$NAME_SITE/postmeta.csv" ]; then
        echo "Error: postmeta.csv file not found in /$NAME_SITE"
        exit 1
    else
        echo "postmeta.csv file found in /$NAME_SITE"
    fi
fi
if [ "$OPTIONS" == "-f" ] || [ "$OPTIONS" == "-a" ]; then
    if [ ! -f "./$NAME_SITE/forminator/old_views.csv" ]; then
        echo "Error: old_views.csv file not found in /$NAME_SITE/forminator"
        exit 1
    else
        echo "old_views.csv file found in /$NAME_SITE/forminator"
    fi
    if [ ! -f "./$NAME_SITE/forminator/old_reports.csv" ]; then
        echo "Error: old_reports.csv file not found in /$NAME_SITE/forminator"        
        exit 1
    else
        echo "old_reports.csv file found in /$NAME_SITE/forminator"

        cp ./$NAME_SITE/forminator/old_reports.csv /var/lib/mysql-files/$NAME_SITE
    fi
    if [ ! -f "./$NAME_SITE/forminator/old_entry.csv" ]; then
        echo "Error: old_entry.csv file not found in /$NAME_SITE/forminator"
        exit 1
    else
        echo "old_entry.csv file found in /$NAME_SITE/forminator"
        
        cp ./$NAME_SITE/forminator/old_entry.csv /var/lib/mysql-files/$NAME_SITE
    fi
    if [ ! -f "./$NAME_SITE/forminator/old_entry_meta.csv" ]; then
        echo "Error: old_entry_meta.csv file not found in /$NAME_SITE/forminator"
        exit 1
    else
        echo "old_entry_meta.csv file found in /$NAME_SITE/forminator"
    fi
fi

chmod -R 777 /var/lib/mysql-files

# Execute the php script
php add_bdd.php $OPTIONS $NAME_SITE $DB_NAME $DB_USER $DB_PASS

chmod -R 700 /var/lib/mysql-files