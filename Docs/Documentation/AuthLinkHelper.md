AuthLinkHelper
=============

The AuthLink Helper has some methods that may be needed if you want to improve your templates and add features to your app in an easy way.
This helper provide two methods that allow you to hide or display links and postLinks based on the permissions file.
No more permissions check in your views ! If the permissions file is update, you do not have to replicate the permissions logic in the views.

Setup
---------------

Enable the Helper in `src/view/AppView.php`:
```php
class AppView extends View
{
    public function initialize()
    {
        parent::initialize();
        $this->loadHelper('CakeDC/Users.AuthLink');
    }
}
```

Link
-----------------

You can use this helper like the initial [cakePhp HtmlHelper link method](https://book.cakephp.org/4/en/views/helpers/html.html#creating-links) :

In templates
```diff
- echo $this->Html->link(__d('cake_d_c/users', 'List Users'), ['action' => 'index'])
+ echo $this->AuthLink->link(__d('cake_d_c/users', 'List Users'), ['action' => 'index'])
```

PostLink
-----------------

You can use this helper like the initial [cakePhp FormHelper postLink method](https://book.cakephp.org/4/en/views/helpers/form.html#creating-post-links) :

In templates
```diff
- echo $this->Form->postLink(__d('cake_d_c/users', 'Delete'), ['action' => 'delete', $user->id], ['confirm' => __d('cake_d_c/users', 'Are you sure you want to delete # {0}?', $user->id)])
+ echo $this->AuthLink->postLink(__d('cake_d_c/users', 'Delete'), ['action' => 'delete', $user->id], ['confirm' => __d('cake_d_c/users', 'Are you sure you want to delete # {0}?', $user->id)])
```

Before and After
-----------------

The link method allow you to add two additional parameters in the options array.
Those two parameters are `before` and `after` to quickly inject some html code in the link, like icons etc

```php
echo $this->AuthLink->link(__d('cake_d_c/users', 'List Users'), ['action' => 'index', 'before' => '<i class="fas fa-list"></i>']);
```

Before and After are only implemented for the link method.
