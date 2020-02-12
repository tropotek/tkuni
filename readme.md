# tkuni  

__Project:__ tkuni  
__Published:__ 16 May 2016  
__Web:__ <http://www.tropotek.com/>  
__Authors:__ Michael Mifsud <http://www.tropotek.com/>  


## Contents

- [Installation](#installation)
- [Introduction](#introduction)
- [Upgrade](#upgrade)
- [Documentation](docs/index.md)
- [Changelog](changelog.md)


## Introduction

A base site using the Tk framework, use this as a starting point for your 
own education sites.


## Installation

Start by getting the dependant libs:

~~~bash
# git clone https://github.com/tropotek/tkuni.git
# cd tkuni
# composer install
~~~

This should prompt you to answer a few questions and create the `src/config/config.php` and .htaccess files.

If this fails you need to create your own `.htaccess` (copy the `.htaccess.in`) and config.php (copy the `config.php.in`)
Also you will have to run the command `bin/cmd migrate` to install the DB.

## Upgrade

Call the command `bin/cmd upgrade` and if all is well you will get the newest version installed




