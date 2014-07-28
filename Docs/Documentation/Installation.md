Installation
============

To install the plugin, place the files in a directory labelled "Users/" in your "app/Plugin/" directory.

Git Submodule
-------------

If you're using git for version control, you may want to add the **Users** plugin as a submodule on your repository. To do so, run the following command from the base of your repository:

```
git submodule add git@github.com:CakeDC/users.git app/Plugin/Users
```

After doing so, you will see the submodule in your changes pending, plus the file ".gitmodules". Simply commit and push to your repository.

To initialize the submodule(s) run the following command:

```
git submodule update --init --recursive
```

To retreive the latest updates to the plugin, assuming you're using the "master" branch, go to "app/Plugin/Users" and run the following command:

```
git pull origin master
```

If you're using another branch, just change "master" for the branch you are currently using.

If any updates are added, go back to the base of your own repository, commit and push your changes. This will update your repository to point to the latest updates to the plugin.

Composer
--------

The plugin also provides a "composer.json" file, to easily use the plugin through the Composer dependency manager.

```
"cakedc/users": "~2.1.0"
```
