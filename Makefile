# Makefile for SIREMIS

# make variables
NAME ?= siremis
URLBASE ?= $(NAME)
BASEDIR=`pwd`
SIREMISDIR=$(BASEDIR)/$(NAME)
VERSION=4.3.0

owner ?= www-data:www-data

# tools
TAR ?= tar

# cooltext (dn:com/st:glowingsteel/fg:brandname/fn:hopt)

all: prepare

prepare-htaccess:
	@echo "updating htaccess file for apache 2.2 ..."
	@sed -e "s#/%%URLBASE%%/#/$(URLBASE)/#g" \
				< ./misc/templates/htaccess > ./siremis/.htaccess


prepare-htaccess24:
	@echo "updating htaccess file for apache 2.4 ..."
	@sed -e "s#/%%URLBASE%%/#/$(URLBASE)/#g" \
				< ./misc/templates/htaccess24 > ./siremis/.htaccess
	@echo "deploying htaccess files for apache 2.4 in subdirs ..."
	@cp ./misc/templates/htaccess24-deny ./siremis/log/.htaccess
	@cp ./misc/templates/htaccess24-deny ./openbiz/metadata/.htaccess
	@cp ./misc/templates/htaccess24-deny ./openbiz/languages/.htaccess


prepare-common:
	@echo "updating app.inc file..."
	@sed -e "s#/%%URLBASE%%#/$(URLBASE)#g" \
				< ./misc/templates/app.inc > ./siremis/bin/app.inc
	@echo "creating folders..."
	@mkdir -p siremis/files/cache
	@mkdir -p siremis/files/cache/data
	@mkdir -p siremis/files/cache/metadata
	@mkdir -p siremis/themes/default/template/cpl
	@echo "done"


# prepare SIREMIS dirs for web install with apache 2.2
prepare: prepare-htaccess prepare-common


# prepare SIREMIS dirs for web install with apache 2.4
prepare24: prepare-htaccess24 prepare-common


apache-conf:
	@echo "# siremis apache 2.2 conf snippet ..."
	@echo
	@sed -e "s#%%URLBASE%%#$(URLBASE)#g" \
				-e "s#%%SIREMISDIR%%#$(SIREMISDIR)#g" \
				< ./misc/templates/apache2.conf

apache24-conf:
	@echo "# siremis apache 2.4 conf snippet ..."
	@echo
	@sed -e "s#%%URLBASE%%#$(URLBASE)#g" \
				-e "s#%%SIREMISDIR%%#$(SIREMISDIR)#g" \
				< ./misc/templates/apache24.conf

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

toolsx:
	@echo "preparing toolsx meta bin directory..."
	rm -rf siremis/bin/toolsx
	cp -a misc/bin/toolsx siremis/bin/

cleantoolsx:
	@echo "removing toolsx meta bin directory..."
	rm -rf siremis/bin/toolsx

resetchmod:
	@echo "reseting file permisions..."
	find . ! -type d -exec chmod 644 {} \;
	find . -type d -exec chmod 755 {} \;

locks:
	@echo "preparing install lock..."
	touch siremis/install.lock

cleanlocks:
	@echo "removing install lock..."
	rm -rf siremis/install.lock

.PHONY: chown
chown:
	@echo "changing onwner to $(owner) ..."
	chown -R $(owner) .

cleantopkg: distclean cleansvn cleansiremis cleanbin cleantoolsx

.PHONY: tar
tar:
	rm -rf tmp
	mkdir -p tmp/$(NAME)-$(VERSION)
	$(TAR) --exclude=tmp/* --exclude=tmp -cf - . \
		| $(TAR) -x --directory=tmp/$(NAME)-$(VERSION)
	make -C tmp/$(NAME)-$(VERSION) cleantopkg
	$(TAR) -C tmp/ \
		--exclude=.git* \
		--exclude=.svn* \
		--exclude=.cvs* \
		--exclude=CVS* \
		--exclude=*.gz \
		--exclude=*.tgz \
		--exclude=*.bz2 \
		--exclude=*.tar \
		--exclude=*.patch \
		--exclude=.\#* \
		--exclude=*.swp \
		--exclude=*.swo \
		-czvf "$(NAME)-$(VERSION).tgz" "$(NAME)-$(VERSION)"
	mv "$(NAME)-$(VERSION).tgz" ../
	rm -rf tmp

