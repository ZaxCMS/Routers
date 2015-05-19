<?php

namespace Zax\Tests;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Zax\Application\Routers\MetadataBuilder;

class RouterFactory {

	static protected function meta($presenter, $action = NULL) {
		return new MetadataBuilder($presenter, $action);
	}

	public function create() {

		$router = new RouteList;

		// test alias
		$meta = self::meta('Default', 'default')
			->addAlias('bar', 'some-deep-control-param')
			->build();
		$router[] = new Route('/test-alias[/<bar>]', $meta);

		// test boolean
		$meta = self::meta('Default', 'default')
			->addAlias('details', 'some-deep-control-showDetails')
			->addBooleanParam('details')
			->build();
		$router[] = new Route('/test-alias-boolean[/<details>]', $meta);

		// test array
		$meta = self::meta('Default', 'default')
			->addAlias('brands', 'some-deep-control-filterBrands')
			->addArrayParam('brands')
			->build();
		$router[] = new Route('/test-alias-array[/<brands>]', $meta);

		// test array delimiter
		$meta = self::meta('Default', 'default')
			->addAlias('brands', 'some-deep-control-filterBrands')
			->addArrayParam('brands', '+')
			->build();
		$router[] = new Route('/test-alias-array-delimiter[/<brands>]', $meta);

		// test multiplier
		$meta = self::meta('Default', 'default')
			->addAlias('page', 'list-paginator-page')
			->addAlias('item', 'list-selectedItemId')
			->addAlias('view', 'list-selectedItem-<item>-view')
			->build();
		$router[] = new Route('/test-multiplier[/page-<page>][/item-<item>[/view-<view>]]', $meta);

		// test signal
		$meta = self::meta('Default', 'default')
			->addSignalAlias('add', 'some-deep-control-add')
			->addSignalAlias('submit', 'some-form-submit')
			->build();
		$router[] = new Route('/test-do[/action-<do>]', $meta);

		// test complex
		$meta = self::meta('Admin:Users', 'list')
			->addAlias('banned', 'list-filters-banned')
			->addAlias('page', 'list-paginator-page')
			->addAlias('user', 'list-selectedUserId')
			->addAlias('view', 'list-selectedUser-<user>-view')
			->addSignalAlias('submit', 'list-selectedUser-<user>-editForm-formSubmitted')
			->addSignalAlias('ban', 'list-selectedUser-<user>-ban')
			->addBooleanParam('banned')
			->build();
		$router[] = new Route('/admin/users[/filter-<banned>][/page-<page>][/user-<user>[/view-<view=Default>][/<do>]]', $meta);

		return $router;
	}

}
