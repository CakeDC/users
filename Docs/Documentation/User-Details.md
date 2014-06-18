User Details (Legacy)
=====================

The plugin contains an ```user_details``` table. This table is a key-value store and is not used by the plugin any more but kept for legacy apps.

If you want to use it you'll have to add the associations by extending the plugin or add your own profiles table which is recommend to use instead of a key-value store. But be aware that this model is very like removed in future versions of the plugin.

```php
class AppUser extends User {
	public $hasMany = array(
		'UserDetail' => array(
			'className' => 'Users.UserDetail'
		)
	);
}
```

Or using your custom profiles table.

```php
class AppUser extends User {
	public $hasOne = array(
		'Profile' => array(
			'className' => 'Profile'
		)
	);
}
```
