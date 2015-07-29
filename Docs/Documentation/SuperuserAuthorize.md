SuperuserAuthorize
=============

Setup
---------------

SuperuserAuthorize is here to provide full permissions to specific "SUPER" users in your app.

```php
$config['Auth']['authorize']['Users.Superuser'] = [
        //superuser field in the Users table
        'superuser_field' => 'is_superuser',
    ];
```

If the current user 'superuser_field' is true, he'll get full permissions in your app.

Note if you don't have superusers, you can disable the SuperuserAuthorize in AuthComponent initialization