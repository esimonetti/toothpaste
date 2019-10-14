# Toothpaste

*This tool is provided AS-IS and might have bugs. Please help create and provide bug fixes!*

## Description
Utility to keep your Sugar system clean via CLI. As it is a CLI only tool, it cannot execute from within Sugar Cloud.<br />
This tool allows the execution of various CLI actions including repair, useful ongoing maintenance, identification of possible problems and extracting data from a Sugar installation.

## Requirements
* PHP >= 7.1
* Composer

## Installation
Within your installation director (eg: `~/toothpaste`), run the following:
```
composer require esimonetti/toothpaste
```
Composer will download toothpaste and all its dependencies, so that you are ready to go.<br />
To be able to execute local commands, toothpaste has to be able to access a local Sugar installation, be on the same server, and with php CLI available.

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
Toothpaste v0.0.5
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
Toothpaste v0.0.5
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
Toothpaste v0.0.5
Setting maintenance mode off...
Entering /var/www/html/sugar...
Setting up instance...
The configuration setting maintenanceMode is now set to: off
The system is accessible via the UI by all users
Execution completed in 0.17 seconds.
```
