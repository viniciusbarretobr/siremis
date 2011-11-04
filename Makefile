# Makefile for SIREMIS

URLBASE ?= siremis
BASEDIR=`pwd`
SIREMISDIR=$(BASEDIR)/siremis
VERSION=3.2.0

# cooltext (dn:com/st:glowingsteel/fg:brandname/fn:hotp)

all: prepare

# prepare SIREMIS dirs for web install
prepare:
	@echo "updating htaccess file..."
	@sed -e "s#/%%URLBASE%%/#/$(URLBASE)/#g" \
				< ./misc/templates/htaccess > ./siremis/.htaccess
	@echo "updating app.inc file..."
	@sed -e "s#/%%URLBASE%%#/$(URLBASE)#g" \
				< ./misc/templates/app.inc > ./siremis/bin/app.inc
	@echo "creating folders..."
	@mkdir -p siremis/files/cache
	@mkdir -p siremis/files/cache/data
	@mkdir -p siremis/files/cache/metadata
	@mkdir -p siremis/themes/default/template/cpl
	@echo "done"

apache-conf:
	@echo "# siremis apache conf snippet ..."
	@echo
	@sed -e "s#%%URLBASE%%#$(URLBASE)#g" \
				-e "s#%%SIREMISDIR%%#$(SIREMISDIR)#g" \
				< ./misc/templates/apache2.conf

clean: cleancache

distclean: cleancache cleansessions

cleancache:
	@echo "cleaning caching files..."
	rm -f siremis/themes/default/template/cpl/*.php
	rm -f siremis/log/*.log
	rm -f siremis/files/cache/metadata/*.cmp
	rm -rf `find ./siremis/files/cache/data/* -maxdepth 0 -type d`

cleansessions:
	@echo "cleaning session files..."
	rm -f siremis/session/sess_*

cleansvn:
	@echo "removing .svn directories..."
	rm -rf `find . -type d -name .svn`

cleansiremis:
	@echo "removing temporary files..."
	rm -f siremis/*.txt
	rm -f siremis/*.lock

cleanbin:
	@echo "removing unsafe files..."
	rm -rf siremis/bin/cronjob
	rm -rf siremis/bin/filebrowser
	rm -rf siremis/bin/phing
	rm -rf siremis/bin/tools
	rm -rf siremis/bin/toolsx
	rm -f siremis/bin/empty.php
	rm -f siremis/bin/install_mod.php
	rm -f siremis/bin/metaedit.php

cleanweb: cleansvn cleansiremis cleanbin

cleanwiz:
	@echo "removing web installation wizard files..."
	rm -rf siremis/install

cleantopkg: distclean cleansvn cleansiremis cleanbin

