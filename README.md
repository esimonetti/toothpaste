# Toothpaste

*This tool is provided AS-IS and might have bugs. Please help create and provide bug fixes!*

## Donations

If you find this sofware useful, please consider supporting the work that went into it, with a monthly amount. Thank you!

[<img src="https://www.paypalobjects.com/en_AU/i/btn/btn_donate_LG.gif">](https://www.paypal.com/donate/?business=35FG9B3LQ3WPA&no_recurring=0&item_name=If+you+find+this+software+useful%2C+please+consider+supporting+the+work+that+went+into+it%2C+with+a+monthly+amount.+Thank+you%21&currency_code=AUD)

## Description
CLI utility to analyse, optimise and provide additional functionality to your Sugar system. As it is a CLI only tool, it cannot execute from within Sugar Cloud.<br />
This tool allows the execution of various CLI actions including repair, useful ongoing maintenance, identification of possible problems and extracting data from a Sugar installation.

## Requirements
* Linux or Macintosh
* PHP >= 7.1
* Composer
* Some commands only run with MySQL at this stage

## Installation
Within your installation directory (eg: `~/toothpaste`), run the following:
```
composer require esimonetti/toothpaste dev-master
```
Composer will download toothpaste and all its dependencies, so that you are ready to go.<br />
To be able to execute local commands, toothpaste has to be able to access a local Sugar installation, be on the same server, and with php CLI available.

### Installation on SugarDockerized
As there were some problems running the installation commands from outside the containers, the following installation steps will enter the `sugar-cron` container to perform the installation.
```
docker exec -it sugar-cron bash
mkdir ../toothpaste
cd ../toothpaste
composer require esimonetti/toothpaste dev-master
exit
```

### Running toothpaste on SugarDockerized
To execute toothpaste, leverage the bash scripts provided with SugarDockerized
```
./utilities/runcli.sh "cd ../toothpaste && ./vendor/bin/toothpaste list"
```

## Sample uses
### List
To show the list of commands available, run the following:
```
./vendor/bin/toothpaste list
```
### Repair
To repair a system (located in `/var/www/html/sugar`), run the following:
```
./vendor/bin/toothpaste local:system:repair --instance /var/www/html/sugar
```
```
Toothpaste vX.X.X
Executing Repair command...
Entering /var/www/html/sugar...
Setting up instance...
Executing simple repair...
Clearing cache...
Executing basic instance warm-up...
Execution completed in 6.01 seconds.
```
### Maintenance on/off
To set maintenance mode on/off for a system (located in `/var/www/html/sugar`), run the following:
```
./vendor/bin/toothpaste local:maintenance:on --instance /var/www/html/sugar
```
```
Toothpaste vX.X.X
Setting maintenance mode on...
Entering /var/www/html/sugar...
Setting up instance...
The configuration setting maintenanceMode is now set to: on
The system is ONLY accessible via the UI by ADMINISTRATOR users
Execution completed in 0.19 seconds.
```
```
./vendor/bin/toothpaste local:maintenance:off --instance /var/www/html/sugar
```
```
Toothpaste vX.X.X
Setting maintenance mode off...
Entering /var/www/html/sugar...
Setting up instance...
The configuration setting maintenanceMode is now set to: off
The system is accessible via the UI by all users
Execution completed in 0.17 seconds.
```
