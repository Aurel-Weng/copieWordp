#!/bin/bash

# This script is used to import and manipulate data from CSV  files to the new wordpress site.
# Before running this script, make sure to follow the instructions below:
# 1. Create a new database and user in your MySQL server.
# 2. Grant the user all privileges on the new database.
# 3. Create a new WordPress site and configure it to use the new database.
# 4. Make sure you imported uploads directory.
# 5. Place the CSV files in the appropriate directory structure : scripts -> directory for wp -> files.csv.
# END
#
# To run this script, use the following command:
# ./manip.sh [OPTIONS] [NAME_SITE] [DB_NAME] [DB_USER] [DB_PASS]
# Options:
# -h : Show this help message
# -p : To change the post
# -u : To change the user_id of userspost table
# -a : To change all the user_id and post_id
# -f : To change the post_id and add all forminator's data
# -c : To change the post_id and add all contest's data
# -s : To change the post_id and add all slide's data


# /var/lib/mysql-files/$NAME_SITE/

# Check if a specific character is present exactly once in $OPTIONS
contains_char_once() {
    local char="$1"
    local count
    count=$(echo -n "$OPTIONS" | awk -v c="$char" 'BEGIN{n=0} {for(i=1;i<=length;i++) if(substr($0,i,1)==c) n++} END{print n}')
    [ "$count" -eq 1 ]
}

# Help message
if [ "$#" -eq 0 ] || ( [ "$#" -eq 1 ] && [ "$1" == "-h" ] ); then
    echo "Usage: $0 [OPTIONS] [NAME_SITE] [DB_NAME] [DB_USER] [DB_PASS]"
    echo "Options:"
    echo "-h : Show this help message"
    echo "-u : To change the user_id and add all users data"
    echo "-p : To change the post and add all posts data"
    echo "-f : To change the post_id and add all forminator's data"
    echo "-c : To change the post_id and add all contest's data"
    echo "-s : To change the post_id and add all slide's data"
    echo "-a : To change and add all users, posts and forminator's data"
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

# Check if the CSV files exist
if contains_char_once "u" || [ "$OPTIONS" == "-a" ]; then
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
if contains_char_once "p" || [ "$OPTIONS" == "-a" ]; then
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
if contains_char_once "f" || [ "$OPTIONS" == "-a" ]; then
    # Check if the forminator directory exists
    if [ ! -d "/var/lib/mysql-files/$NAME_SITE/forminator" ]; then
        echo "Creating directory /var/lib/mysql-files/$NAME_SITE/forminator"
        mkdir -p /var/lib/mysql-files/$NAME_SITE/forminator
    fi

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
if contains_char_once "c" || [ "$OPTIONS" == "-a" ]; then
    # Check if the contest directory exists
    if [ ! -d "/var/lib/mysql-files/$NAME_SITE/contest" ]; then
        echo "Creating directory /var/lib/mysql-files/$NAME_SITE/contest"
        mkdir -p /var/lib/mysql-files/$NAME_SITE/contest
    fi

    if [ ! -f "./$NAME_SITE/contest/old_posts.csv" ]; then
        echo "Error: old_posts.csv file not found in /$NAME_SITE/contest"
        exit 1
    else
        echo "old_contest.csv file found in /$NAME_SITE/contest"
    fi

    if [ ! -f "./$NAME_SITE/contest/old_fca_cc_activity_tbl.csv" ]; then
        echo "Error: old_fca_cc_activity_tbla.csv file not found in /$NAME_SITE/contest"
        exit 1
    else
        echo "old_fca_cc_activity_tbl.csv file found in /$NAME_SITE/contest"
    fi
fi
if contains_char_once "s" || [ "$OPTIONS" == "-a"]; then
    # Check if the slide directory exists
    if [ ! -d "/var/lib/mysql-files/$NAME_SITE/slide" ]; then
        echo "Creating directory /var/lib/mysql-files/$NAME_SITE/slide"
        mkdir -p /var/lib/mysql-files/$NAME_SITE/slide
    fi

    if [ ! -f "./$NAME_SITE/old_posts.csv" ]; then
        echo "Error: old_posts.csv file not found in /$NAME_SITE"
        exit 1
    else
        echo "old_posts.csv file found in /$NAME_SITE"
    fi
    if [ ! -f "./$NAME_SITE/slide/wgl_slides.csv" ]; then
        echo "Error: wgl_slides.csv file not found in /$NAME_SITE/slide"
        exit 1
    else
        echo "wgl_slides.csv file found in /$NAME_SITE/slide"
    fi
fi

chmod -R 777 /var/lib/mysql-files
chown -R www-data:www-data /var/lib/mysql-files/$NAME_SITE

# Execute the php script
php add_bdd.php $OPTIONS $NAME_SITE $DB_NAME $DB_USER $DB_PASS

chmod -R 700 /var/lib/mysql-files