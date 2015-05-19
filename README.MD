This library contains some useful helpers for routing in Nette framework.

Zax\Application\Routers\MetadataBuilder
=======================================

This class is a builder for metadata, it add the ability to use aliases and a workaround for UI\Multiplier.

It takes two params in constructor - presenter and action. Alternatively, you can pass array of metadata.

Aliases
-------

```php
$default = ['presenter' => 'Default', 'action' => 'default'];
$meta = (new MetadataBuilder($default))
	->addAlias('bar', 'some-deep-control-param')
	->build();
$router[] = new Route('/test-alias[/<bar>]', $meta);
```

Calling URL `/test-alias/foo` will result in value "foo" being assigned to persistent parameter
`some-deep-control-param`.

Signal aliases
--------------

Signals need a different treatment, because it's only a single parameter `do`.

```php
$meta = (new MetadataBuilder($default))
	->addSignalAlias('add', 'some-deep-control-add')
	->build();
$router[] = new Route('/test-do[/action-<do>]', $meta);
```

Calling URL `/test-do/action-add` will call signal `add` in `some-deep-control`.

Boolean parameters
------------------

Boolean parameters will only display some text in URL to represent `TRUE`.

```php
$meta = (new MetadataBuilder($default))
	->addAlias('details', 'some-deep-control-showDetails')
	->addBooleanParam('details')
	->build();
$router[] = new Route('/test-boolean[/<details>]', $meta);
```

Calling URL `/test-boolean/details` will set parameter `some-deep-control-showDetails` to `TRUE`.

Array parameters
----------------

By default, Nette works with array persistent params by making a special param in URL *for every value*.
This behavior can quickly clutter the URL, especially when using components. Fortunately, this library can turn

```
/test-array?some-deep-control-selected[]=1&some-deep-control-selected[]=5&some-deep-control-selected[]=10
```

into

```
/test-array?selected=1+5+10
```

like this

```php
$meta = (new MetadataBuilder($default))
	->addAlias('selected', 'some-deep-control-selected')
	->addArrayParam('selected', '+') // second parameter (delimiter) is optional, ',' is default
	->build();
$router[] = new Route('/test-array', $meta);
```

Working with Multiplier
-----------------------

Multipliers can be tricky to handle in routers. Fortunately, there's the ability to use aliases inside other
aliases!

```php
$meta = (new MetadataBuilder($default))
	->addAlias('item', 'list-selectedItemId')
	->addAlias('view', 'list-selectedItem-<item>-view')
	->build();
$router[] = new Route('/test-multiplier[/item-<item>[/view-<view>]]', $meta);
```

Control `list-selectedItem` is a Multiplier, `<item>` will get replaced by value inside `list-selectedItemId`.

Therefore, calling URL `/test-multiplier/item-34/view-edit` will set `list-selectedItem-34-view` to
`edit`.