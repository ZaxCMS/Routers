<?php

namespace Zax\Tests;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

class HelpersTest extends Tester\TestCase {

	/** @var Nette\Application\IRouter */
	protected $router;

	public function __construct(Nette\DI\Container $container) {
		$this->router = $container->getByType('Zax\Tests\RouterFactory')->create();
	}

	/**
	 * @param string $url
	 * @return array
	 */
	protected function urlToParams($url) {
		$request = $this->router->match(new Nette\Http\Request(new Nette\Http\UrlScript($url)));
		return $request->parameters;
	}

	public function testMetadata() {

		// test aliases
		$p = $this->urlToParams('/test-alias/abc');
		Assert::same('abc', $p['some-deep-control-param']);

		// test boolean params
		$p = $this->urlToParams('/test-alias-boolean/details');
		Assert::true($p['some-deep-control-showDetails']);
		$p = $this->urlToParams('/test-alias-boolean');
		Assert::false(isset($p['some-deep-control-showDetails']));

		// test multiplier
		$p = $this->urlToParams('/test-multiplier/page-10/item-8/view-edit');
		Assert::equal('10', $p['list-paginator-page']);
		Assert::equal('8', $p['list-selectedItemId']);
		Assert::equal('edit', $p['list-selectedItem-8-view']);

		// test signal
		$p = $this->urlToParams('/test-do/action-add');
		Assert::equal('some-deep-control-add', $p['do']);
		$p = $this->urlToParams('/test-do/action-submit');
		Assert::equal('some-form-submit', $p['do']);

		// test complex
		$p = $this->urlToParams('/admin/users/filter-banned/page-42/user-43/view-edit/submit');
		Assert::true($p['list-filters-banned']);
		Assert::equal('42', $p['list-paginator-page']);
		Assert::equal('43', $p['list-selectedUserId']);
		Assert::equal('edit', $p['list-selectedUser-43-view']);
		Assert::equal('list-selectedUser-43-editForm-formSubmitted', $p['do']);
		$p = $this->urlToParams('/admin/users/page-42/user-43/ban');
		Assert::false(isset($p['list-filters-banned']));
		Assert::equal('42', $p['list-paginator-page']);
		Assert::equal('43', $p['list-selectedUserId']);
		Assert::equal('Default', $p['list-selectedUser-43-view']);
		Assert::equal('list-selectedUser-43-ban', $p['do']);
	}

}

$test = new HelpersTest($container);
$test->run();
