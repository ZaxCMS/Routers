<?php

namespace Zax\Application\Routers;

use Nette\Application\Routers\Route;
use Nette\Object;
use Nette\Utils\Strings;

class MetadataBuilder extends Object {

	/** @var array */
	private $metadata = [];

	/** @var array */
	private $aliases = [];

	/** @var array */
	private $doAliases = [];

	/** @var array */
	private $booleanParams = [];

	/**
	 * @param string|array $presenter or $metadata
	 * @param string|NULL $action or NULL if using metadata
	 */
	public function __construct($presenter, $action = NULL) {
		if(is_array($presenter)) {
			$this->metadata = $presenter;
		} else {
			$this->metadata = [
				'presenter' => $presenter
			];

			if(strlen($action) > 0) {
				$this->metadata['action'] = $action;
			}
		}
	}

	/**
	 * @param string $alias
	 * @param string $fullName
	 * @return $this
	 */
	public function addAlias($alias, $fullName) {
		$this->aliases[$alias] = $fullName;
		return $this;
	}

	/**
	 * @param string $alias
	 * @param string $fullName
	 * @return $this
	 */
	public function addSignalAlias($alias, $fullName) {
		$this->doAliases[$alias] = $fullName;
		return $this;
	}

	/**
	 * @param string $param
	 * @return $this
	 */
	public function addBooleanParam($param) {
		$this->booleanParams[] = $param;
		return $this;
	}

	/**
	 * @param string $name
	 * @return array|NULL
	 */
	private function findTokens($name) {
		return Strings::match($name, '~(\<[a-z-]+\>)+~i');
	}

	/**
	 * @param array $tokens
	 * @param array $params
	 * @param array $aliases
	 * @param string $fullName
	 * @return string
	 */
	private function createFullName(array $tokens, array $params, array $aliases, $fullName) {
		foreach($tokens as $token) {
			$token2 = substr($token, 1, -1);
			if(isset($params[$token2])) {
				$fullName = str_replace($token, $params[$token2], $fullName);
			} else if(isset($aliases[$token2]) && isset($params[$aliases[$token2]])) {
				$fullName = str_replace($token, $params[$aliases[$token2]], $fullName);
			}
		}
		return $fullName;
	}

	/**
	 * @param array $aliases
	 * @param array $doAliases
	 * @return array
	 */
	private function createAliases($aliases = [], $doAliases = []) {
		return [
			Route::FILTER_IN => function($params) use ($aliases, $doAliases) {
				if(isset($params['do'])) {
					foreach($doAliases as $doAlias => $doFullName) {
						$doTokens = $this->findTokens($doFullName);
						if($doTokens) {
							$doFullName = $this->createFullName($doTokens, $params, $aliases, $doFullName);
						}
						if($params['do'] == $doAlias)
							$params['do'] = $doFullName;
					}
				}
				foreach($aliases as $alias => $fullName) {
					if(isset($params[$alias])) {;
						$tokens = $this->findTokens($fullName);
						if($tokens) {
							$fullName = $this->createFullName($tokens, $params, $aliases, $fullName);
						}
						$params[$fullName] = $params[$alias];
						unset($params[$alias]);
					}
				}

				return $params;
			},
			Route::FILTER_OUT => function($params) use ($aliases, $doAliases) {
				if(isset($params['do'])) {
					foreach($doAliases as $doAlias => $doFullName) {
						$doTokens = $this->findTokens($doFullName);
						if($doTokens) {
							$doFullName = $this->createFullName($doTokens, $params, $aliases, $doFullName);
						}
						if($params['do'] == $doFullName)
							$params['do'] = $doAlias;
					}
				}
				foreach($aliases as $alias => $fullName) {
					$tokens = $this->findTokens($fullName);
					if($tokens) {
						$fullName = $this->createFullName($tokens, $params, $aliases, $fullName);
					}
					if(isset($params[$fullName])) {
						$params[$alias] = $params[$fullName];
						unset($params[$fullName]);
					}
				}

				return $params;
			}
		];
	}

	/**
	 * @param array $metadata
	 * @param array $aliases
	 * @param array $doAliases
	 * @param array $boolParams
	 * @return array
	 */
	private function createMetadata(array $metadata, $aliases = [], $doAliases = [], $boolParams = []) {
		if(count($aliases) + count($doAliases) > 0) {
			$metadata[NULL] = $this->createAliases($aliases, $doAliases);
		}

		if(count($boolParams) > 0) {
			foreach($boolParams as $param) {
				$metadata[$param] = [
					Route::FILTER_IN => function() {
						return TRUE;
					},
					Route::FILTER_OUT => function() use ($param) {
						return $param;
					}
				];
			}
		}

		return $metadata;
	}

	/**
	 * @return array
	 */
	public function build() {
		return $this->createMetadata($this->metadata, $this->aliases, $this->doAliases, $this->booleanParams);
	}

}
