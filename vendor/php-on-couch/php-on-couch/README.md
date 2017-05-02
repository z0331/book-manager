[![Latest Stable Version](https://poser.pugx.org/php-on-couch/php-on-couch/version)](https://packagist.org/packages/php-on-couch/php-on-couch)[![Latest Unstable Version](https://poser.pugx.org/php-on-couch/php-on-couch/v/unstable)](//packagist.org/packages/php-on-couch/php-on-couch)[![Build Status](https://travis-ci.org/PHP-on-Couch/PHP-on-Couch.svg?branch=master)](https://travis-ci.org/PHP-on-Couch/PHP-on-Couch)[![Coverage Status](https://coveralls.io/repos/github/PHP-on-Couch/PHP-on-Couch/badge.svg?branch=master)](https://coveralls.io/github/PHP-on-Couch/PHP-on-Couch?branch=master)[![License](https://poser.pugx.org/php-on-couch/php-on-couch/license)](https://packagist.org/packages/php-on-couch/php-on-couch)


[![Stories in Ready](https://badge.waffle.io/PHP-on-Couch/PHP-on-Couch.png?label=ready&title=Ready)](https://waffle.io/PHP-on-Couch/PHP-on-Couch)
## Table of content
- [Introduction](#introduction)
- [What's new](#whats-new)
- [Changes](#changes)
- [Installation and testing](#installation-and-testing)
- [Components and documentation](#components-and-documentation)
    + [Couch class](#couch-class)
    + [CouchClient class](#couchclient-class)
        * [Database functionnalities](#database-functionnalities)
        * [Documents functionnalities](#documents-functionnalities)
        * [Views functionnalities](#views-functionnalities)
        * [Mango Query](#mango-query)
    + [CouchDocument class](#couchdocument-class)
    + [CouchReplicator class](#couchreplicator-class)
    + [CouchAdmin class](#couchadmin-class)
- [Quick-start guide](#quick-start-guide)
- [Example](#example)
- [Community](#community)
    + [Contributions](#contributions)
    + [Feedback](#feedback)

## Introduction

[PHP On Couch](http://github.com/PHP-on-Couch/PHP-on-Couch/) tries to provide an easy way to work with your [CouchDB](http://couchdb.apache.org) [documents](http://docs.couchdb.org/) with [PHP](http://php.net). 

## What's new

Due to the lack of support on the last repository, I forked it and I will make sure it's kept active. Feel free to post any issue or feature request. I'm open for further developments but I don't have a lot of time. 

With the new release of 2.0, the master branch will support only this version and the next one.

To access PHP-on-Couch for CouchDB 1.6.1, please visit [this link](https://github.com/PHP-on-Couch/PHP-on-Couch/tree/1.6.1).


## Recent changes

For the complete change list, head over [here](changelist.md)

## Installation and testing

Install the library using composer : `composer require php-on-couch/php-on-couch`.
You can find more detailed informations about installation [here](INSTALL.md)

Test instructions to be determined.

## Components and documentation

This library has four main classes and a custom [Exception](http://php.net/manual/en/language.exceptions.php) class.

### Couch class
This is the most basic of the three classes, and is responsible for the low level dialog between PHP and the CouchDB server. There should be no need of using it directly.

From version **2.0.2**, you are able to change the HTTP adapter used by the Couch class. For more details, click [here](doc/couch.md).


### CouchClient class

This class maps all the actions the application can do on the CouchDB server. Documentation is split in three main topics :

#### [Database functionnalities](doc/couch_client-database.md)

 - List databases
 - Create and delete a database
 - Retrieve database informations
 - Test whether a database exists
 - Get uuids
 - Get databases changes

#### [Documents functionnalities](doc/couch_client-document.md)

- Fetching documents
- Storing documents
- Copy a document
- Store attachments
- Delete document attachments
- Get all documents

#### [Views functionnalities](doc/couch_client-view.md)

- Calling a view with query options : key, startkey, endkey, limit, stale, ...

#### [Mango Query](doc/couch_client_mango.md)

- Create and manage indexes
- Make complex query with Mango Query

### CouchDocument class

Easing the manipulation of documents, the CouchDocument class uses PHP magic getters and setters. Documentation available [here](doc/couch_document.md).

### CouchReplicator class

A dedicated class to manage replications over different instances of CouchDB databases. Documentation available [here](doc/couch_replicator.md).

### CouchAdmin class

A class to manage users and database/users associations. Documentation available [here](doc/couch_admin.md).

## Quick-start guide
  
1. Import those classes whenever you need to access CouchDB server :

```
use PHPOnCouch\Couch, 
    PHPOnCouch\CouchAdmin, 
    PHPOnCouch\CouchClient; 
```

If you need to use replication features, also use the couchReplicator definition :

        use PHPOnCouch\CouchReplicator;

2. Create a client object. You have to tell it the _Data source name_ (dsn) of your CouchDB server, as well as the name of the database you want to work on. The DSN is the URL of your CouchDB server, for example _http://localhost:5984_.
        
        $client = new CouchClient($couchdb_server_dsn, $couchdb_database_name);

3. Use it !
        
        try {
            $client->createDatabase();
        } catch (Exception $e) {
            echo "Unable to create database : ".$e->getMessage();
        }
        
        $doc = new CouchDocument($client);
        $doc->set( array('_id'=>'some_doc_id', 'type'=>'story','title'=>"First story") );
        
        $view = $client->limit(10)->descending(true)->getView('some_design_doc','viewname');


## Example

Some code first :

At first, you need to import the main components through their namespace. If you use composer, I suggest you to use their autoload wich is easy to setup. Otherwise, you can use your own autoload function or a basic require with some namespace escaping.

```
use PHPOnCouch\Couch, //The core of PHP-on-Couch
    PHPOnCouch\CouchAdmin, //The object to handle admins
    PHPOnCouch\CouchClient; //The CouchDB client object

```

Here's an example for basic operations

```
// Set a new connector to the CouchDB server
$client = new CouchClient('http://my.couch.server.com:5984', 'my_database');

// document fetching by ID
$doc = $client->getDoc('some_doc_id');
// updating document
$doc->newproperty = array("hello !", "world");
try {
    $client->storeDoc($doc);
} catch (Exception $e) {
    echo "Document storage failed : " . $e->getMessage() . "<BR>\n";
}
```

Here's a quick example of how to fetch a view

```
// view fetching, using the view option limit
try {
    $view = $client->limit(100)->getView('orders', 'by-date');
} catch (Exception $e) {
    echo "something weird happened: " . $e->getMessage() . "<BR>\n";
}
```

Finally, how to use the CouchDocument class.

```
//using couch_document class :
$doc = new CouchDocument($client);
$doc->set(array('_id' => 'JohnSmith', 'name' => 'Smith', 'firstname' => 'John')); //create a document and store it in the database
echo $doc->name; // should echo "Smith"
$doc->name = "Brown"; // set document property "name" to "Brown" and store the updated document in the database
```



       
## Community

### Contributions

Feel free to make any contributions. All contributions must follow the [code style](codestyle.md) and must also comes with valid and complete tests. 

Help is really appreciated to complete add more tests.

### Feedback

[![Gitter chat](https://badges.gitter.im/gitterHQ/gitter.png)](https://gitter.im/PHP-on-Couch/PHP-on-Couch)

Don't hesitate to submit feedback, bugs and feature requests ! My contact address is [alexiscote19@hotmail.com](mailto:alexiscote19@hotmail.com?subject=Feedback)


