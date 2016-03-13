# Discovery
This is the code base for the discovery tool, it provides a web front end for adding, updating and deleting item types as well as an indexing agent which connects to Google adn OMDB APIs for the information which is then put into Apache Solr for searching.

###  Server
A pre-built server with all the required components is available from [here.](http://enquirer.cityofglasgowcollege.ac.uk/files/DiscoveryTool.zip)  Download and extract the zip file and follow the installation instructions contained within the README file.


### Installation
Clone the respoitory into the web root
```sh
$ git clone https://github.com/GregDoak/DiscoveryTool.git .
```
Install dependancies with composer
```sh
$ composer install
```
Build the database
```sh
$ php app/console doctrine:schema:update --force
```
Load the default user 
```sh
$ php app/console doctrine:fixtures:load
```

### Default User Credentials
**Username:** admin
**Password:** admin

### Dependencies

Discovery Tool uses a number of open source projects to work properly:
* [AngularJS](http://www.angularjs.org/)
* [Bootstrap](http://getbootstrap.com/)
* [FontAwesome](https://fortawesome.github.io/Font-Awesome/)
* [jQuery](https://jquery.com/)
* [Symfony](https://symfony.com/)

