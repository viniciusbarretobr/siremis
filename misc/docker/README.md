# SIREMIS Docker Installation #

Guidelines to install Siremis development version (git master branch) using Docker
on Debian 10 (Buster).

## Build Docker Image ##

In this folder (`misc/docker`), inspect the **apache2** configuration file
`files/000-default.conf.debian10` and update it as needed, especially the
`ServerName` values.

Then run:

```
docker build -t siremisdev-debian10 -f Dockerfile.debian10-gitdev .
```

## Run Docker Container And Install Siremis ##

Execute:

```
docker run --name siremisdev-debian10 -p 8080:80 siremisdev-debian10
```

Prepare the access to database for **Siremis** and create **Kamailio** database
if needed -- see the instructions at:

  * http://kb.asipto.com/siremis:install51x:main#database_configuration

The use an web browser and navigate to:

  * http://127.0.0.1/siremis/

Follow up the web installation wizard steps -- more details at:

  * http://kb.asipto.com/siremis:install51x:main#web_installation_wizard

Note that the previous steps from the **Siremis Installation Tutorial** were
already done inside the Docker container during its build process.

From now on, the commands `docker stop` and `docker start` can be used to
control when to stop/start the container.

## Stop Docker Container ##

Execute:

```
docker stop siremisdev-debian10
```

## Start Docker Container ##

Execute:

```
docker start siremisdev-debian10
```

## Container Database Server Access ##

If the database server is configured to listen on an IP accessible from inside
Docker container, then it is straightforward to set it via the Siremis web
installation wizard.

If the database server is running on host system and it is configured to listen
on Unix socket file, then the socket file can be mounted as volume inside
container. When running the container first time to do the installation wizard,
use:

```
docker run --name siremisdev-debian10 run -v /host/path/to/mysqld.sock:/container/path/to/mysqld.sock -p 8080:80 siremisdev-debian10
```

For example:

```
docker run --name siremisdev-debian10 run -v /run/mysqld/mysqld.sock:/run/mysqld/mysqld.sock -p 8080:80 siremisdev-debian10
```

If does not work directly with the socket file, then share the folder:

```
docker run --name siremisdev-debian10 run -v /run/mysqld/:/run/mysqld/ -p 8080:80 siremisdev-debian10
```

The above should work on Linux. The support to share socket files between host
and container systems is work in progress for Docker. Currently, an workaround
for MacOS is to use `socat` to proxy from an IP socket to Unix socket file:

```
socat TCP4-LISTEN:3306,reuseaddr,fork,bind=__HOST_IP__ UNIX-CONNECT:/host/path/to/mysqld.sock
```

If the IP of the host system is `192.168.56.1` and MySQL Server 5.7 was installed
using `macports`, next `socat` command can be used:

```
socat TCP4-LISTEN:3306,reuseaddr,fork,bind=192.168.56.1 UNIX-CONNECT:/opt/local/var/run/mysql57/mysqld.sock
```

Then use `__HOST_IP__` in the `DB Host Name` fields, for both Siremis and SIP
databases, in the 2nd step of Siremis web installation wizard.

Note: using `socat` may affect the performance of interacting with the database
server, if any slow down is noticed, then it is recommended to configure MySQL
server to run on an IP socket accessible from inside the Docker container. Also,
if `socat` binds to a public IP (or IP reachable from untrusted systems), enable
firewall and set appropriate security rules so only the Docker container system
can connect to the `socat` socket.

## Contributions ##

Issues can be reported at:

  * https://github.com/asipto/siremis/issues

Preferred way to contribute is via pull requests at:

  * https://github.com/asipto/siremis/pulls

## Copyright ##

 2020 - asipto.com
