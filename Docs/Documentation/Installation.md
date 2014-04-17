Installation
============

The plugin is pretty easy to set up, all you need to do is to copy it to you application plugins folder and load the needed tables. You can create database tables using either the schema shell or the [CakeDC Migrations plugin](http://github.com/CakeDC/migrations):

```
./Console/cake schema create users --plugin Users
```

or

```
./Console/cake Migrations.migration run all --plugin Users
```

You will also need the [CakeDC Search plugin](http://github.com/CakeDC/search), just grab it and put it into your application's plugin folder.