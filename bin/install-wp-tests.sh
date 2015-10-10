#!/usr/bin/env bash

if [ $# -lt 3 ]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version]"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}

WP_CORE_DIR=/tmp/wordpress/
WP_DEVELOP_DIR=${WP_DEVELOP_DIR-/tmp/wordpress-develop}

download() {
    if [ `which curl` ]; then
        curl -s "$1" > "$2";
    elif [ `which wget` ]; then
        wget -nv -O "$2" "$1"
    fi
}

# Solution from https://github.com/wp-cli/wp-cli/blob/master/templates/install-wp-tests.sh#L25
if [[ $WP_VERSION =~ [0-9]+\.[0-9]+(\.[0-9]+)? ]]; then
	WP_BRANCH="$WP_VERSION"
elif [[ $WP_VERSION == 'nightly' ]]; then
	WP_BRANCH="master"
else
	# http serves a single offer, whereas https serves multiple. we only want one
	download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
	grep '[0-9]+\.[0-9]+(\.[0-9]+)?' /tmp/wp-latest.json
	LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//')
	if [[ -z "$LATEST_VERSION" ]]; then
		echo "Latest WordPress version could not be found"
		exit 1
	fi
	WP_BRANCH="$LATEST_VERSION"
fi

echo $WP_BRANCH

set -ex

install_wp() {
	mkdir -p $WP_CORE_DIR

	if [ $WP_VERSION == 'latest' ]; then
		local ARCHIVE_NAME='latest'
	elif [ $WP_VERSION == 'nightly' ]; then
		local ARCHIVE_NAME='nightly-builds/wordpress-latest'
	else
		local ARCHIVE_NAME="wordpress-$WP_VERSION"
	fi

	TMP_EXTRACT=$(mktemp -d /tmp/wp-XXXXX)

	wget -nv -O /tmp/wordpress.zip https://wordpress.org/${ARCHIVE_NAME}.zip
	unzip /tmp/wordpress.zip -d $TMP_EXTRACT
	DIRS=($TMP_EXTRACT/*)
	mv ${DIRS[@]:0:1}/* $WP_CORE_DIR
	rm -r $TMP_EXTRACT

	wget -nv -O $WP_CORE_DIR/wp-content/db.php https://raw.github.com/markoheijnen/wp-mysqli/master/db.php
}

install_test_suite() {
	# portable in-place argument for both GNU sed and Mac OSX sed
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i .bak'
	else
		local ioption='-i'
	fi

	# set up testing suite
	git clone https://github.com/frozzare/wordpress-develop.git $WP_DEVELOP_DIR
	cd $WP_DEVELOP_DIR
	git fetch
	git checkout ${WP_BRANCH}

	cp $WP_DEVELOP_DIR/wp-tests-config-sample.php $WP_DEVELOP_DIR/wp-tests-config.php
	sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR':" $WP_DEVELOP_DIR/wp-tests-config.php
	sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" $WP_DEVELOP_DIR/wp-tests-config.php
	sed $ioption "s/yourusernamehere/$DB_USER/" $WP_DEVELOP_DIR/wp-tests-config.php
	sed $ioption "s/yourpasswordhere/$DB_PASS/" $WP_DEVELOP_DIR/wp-tests-config.php
	sed $ioption "s|localhost|${DB_HOST}|" $WP_DEVELOP_DIR/wp-tests-config.php
}

install_db() {
	# parse DB_HOST for port or socket references
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ] ; then
		if [[ "$DB_SOCK_OR_PORT" =~ ^[0-9]+$ ]] ; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	# create database
	mysql --execute="CREATE DATABASE IF NOT EXISTS $DB_NAME" --user="$DB_USER" --password="$DB_PASS"$EXTRA
}

install_wp
install_test_suite
install_db
