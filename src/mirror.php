<?php

namespace divengine;

/**
 * [[]] Div PHP Mirror
 * 
 * A PHP library for exposing and calling functions and classes remotely.
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * 
 * @package divengine/mirror
 * @author Rafa Rodriguez @rafageist [https://rafageist.com]
 * @link   https://divengine.org
 */

class mirror
{
	/**
	 * The version of the library.
	 * 
	 * @var string
	 */
	private static string $__version = '1.0.0';

	/**
	 * The server to call.
	 * 
	 * @var string
	 */
	private static ?string $server = null;

	/**
	 * The classes to expose.
	 * 
	 * @var array
	 */
	private static array $classesToExpose = [];

	/**
	 * The functions to expose.
	 * 
	 * @var array
	 */
	private static array $functionsToExpose = [];

	/**
	 * The classes to generate.
	 * 
	 * @var array
	 */
	private static array $classesToGenerate = [];

	/**
	 * The functions to generate.
	 * 
	 * @var array
	 */
	private static array $functionsToGenerate = [];

	/**
	 * Get the version of the library.
	 * 
	 * @return string
	 */
	public static function getVersion(): string
	{
		return self::$__version;
	}

	/**
	 * Set the server to call.
	 * 
	 * @param mixed $server
	 * @return void
	 */
	public static function setServer(string $server): void
	{
		self::$server = $server;
	}

	/**
	 * Get the server to call.
	 * 
	 * @return string
	 */
	public static function getServer(): string
	{
		return self::$server;
	}

	/**
	 * Get the classes to expose.
	 * 
	 * @return array
	 */
	public static function getClassesToExpose(): array
	{
		return self::$classesToExpose;
	}

	/**
	 * Get the functions to expose.
	 * 
	 * @return array
	 */
	public static function getFunctionsToExpose(): array
	{
		return self::$functionsToExpose;
	}

	/**
	 * Get the classes to generate.
	 * 
	 * @return array
	 */	
	public static function getClassesToGenerate(): array
	{
		return self::$classesToGenerate;
	}

	/**
	 * Get the functions to generate.
	 * 
	 * @return array
	 */
	public static function getFunctionsToGenerate(): array
	{
		return self::$functionsToGenerate;
	}

	/**
	 * Clear the classes to expose.
	 * 
	 * @return void
	 */
	public static function clearClassesToExpose(): void
	{
		self::$classesToExpose = [];
	}

	public static function clearFunctionsToExpose(): void
	{
		self::$functionsToExpose = [];
	}

	/**
	 * Clear the classes to generate.
	 * 
	 * @return void
	 */
	public static function clearClassesToGenerate(): void
	{
		self::$classesToGenerate = [];
	}

	/**
	 * Clear the functions to generate.
	 * 
	 * @return void
	 */
	public static function clearFunctionsToGenerate(): void
	{
		self::$functionsToGenerate = [];
	}

	/**
	 * Prepare a class or function to be exposed.
	 * 
	 * @param mixed $name
	 * @return void
	 */
	public static function prepare($name): void
	{
		if (class_exists($name)) {

			$className = $name;
			$reflection = new \ReflectionClass($className);
			$properties = $reflection->getProperties();
			$methods = $reflection->getMethods();

			$classInfo = [
				'class' => $reflection->getShortName(),
				'properties' => array_map(function ($prop) {
					// each property is {name, type, default, modifiers}
					return [
						'name' => $prop->getName(),
						'type' => $prop->hasType() ? $prop->getType()->getName() : null,
						'default' => serialize($prop->isDefault() ? $prop->getDefaultValue() : null),
						'modifiers' => \Reflection::getModifierNames($prop->getModifiers())
					];
				}, $properties),
				'methods' => array_map(function ($method) {
					$parameters = $method->getParameters();

					return [
						'name' => $method->getName(),
						'returnType' => $method->hasReturnType() ? $method->getReturnType()->getName() : null,
						'modifiers' => \Reflection::getModifierNames($method->getModifiers()),
						'parameters' => array_map(function ($param) {
							return [
								'name' => $param->getName(),
								'type' => $param->hasType() ? $param->getType()->getName() : null,
								'default' => serialize($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null),
								'byReference' => $param->isPassedByReference()
							];
						}, $parameters)
					];
				}, $methods)
			];

			self::$classesToExpose[] = $classInfo;
		}

		if (function_exists($name)) {
			$reflection = new \ReflectionFunction($name);
			$parameters = $reflection->getParameters();

			$functionInfo = [
				'name' => $reflection->getName(),
				'parameters' => array_map(function ($param) {
					return [
						'name' => $param->getName(),
						'type' => $param->hasType() ? $param->getType()->getName() : null,
						'default' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
						'byReference' => $param->isPassedByReference()
					];
				}, $parameters)
			];

			self::$functionsToExpose[] = $functionInfo;
		}
	}

	/**
	 * Expose a class or function by page.
	 * 
	 * @param int $page
	 * @return string
	 */
	public static function expose(int $page = 1): string
	{
		$kind = null;
		$totalFunctions = count(self::$functionsToExpose);
		$totalClasses = count(self::$classesToExpose);
		$totalPages = $totalFunctions + $totalClasses;

		if ($page <= $totalFunctions) {
			$kind = 'function';
			$element = self::$functionsToExpose[$page - 1];
		}
		elseif ($page <= $totalPages) {
			$kind = 'class';
			$element = self::$classesToExpose[$page - $totalFunctions - 1];
		}
		else {
			return ''; // json_decode('') == NULL
		}

		return json_encode([
			'page' => $page,
			'totalPages' => $totalPages,
			'elements' => [
				(object) [
					'kind' => $kind,
					'info' => $element
				]
			]
		]);
	}

	/**
	 * Call a remote method or function.
	 * 
	 * @param string $methodName
	 * @param array $args
	 * @param mixed $instance
	 * @throws \Exception
	 * @return mixed
	 */
	public static function call(string $methodName, array $args, $instance = null): mixed
	{
		if (self::$server === null) {
			throw new \Exception('Server not set');
		}

		$serializedArgs = array_map('serialize', $args);

		$exploded = explode('::', $methodName);

		$className = null;
		if (count($exploded) == 2) {
			$className = $exploded[0];
			$methodName = $exploded[1];
		}

		$payload = json_encode([
			'class' => $className,
			'instance' => serialize($instance),
			'method' => $methodName,
			'args' => $serializedArgs
		]);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, self::$server);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

		$response = curl_exec($ch);
		curl_close($ch);

		$result = json_decode($response);
		$result->result = unserialize($result->result);

		return $result;
	}

	/**
	 * Receive a call from a remote method or function.
	 * 
	 * @return object|null
	 */
	public static function listen(string $json = null): object|null
	{
		if ($json == null) {
			$json = file_get_contents('php://input');
		}

		$payload = @json_decode($json);

		if (empty($payload)) {
			return null;
		}

		$class = $payload->class;
		$method = $payload->method;
		$args = array_map('unserialize', $payload->args);
		$instance = unserialize($payload->instance);

		$time_start = microtime(true);
		$memory_start = memory_get_usage();
		$result = call_user_func_array([$instance ?? $class, $method], $args);
		$time_end = microtime(true);
		$memory_end = memory_get_usage();

		return (object) [
			'time' => date('Y-m-d H:i:s'),
			'class' => $class,
			'method' => $method,
			'result' => serialize($result),
			'executionTime' => $time_end - $time_start,
			'memoryUsage' => $memory_end - $memory_start
		];
	}

	/**
	 * Discover classes and functions from a remote server.
	 * 
	 * @param string $url
	 * @param int $page
	 * 
	 * @return bool
	 */
	public static function discover(string $url, string $payload = ''): bool
	{	
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

		$response = curl_exec($ch);

		$discover = json_decode($response, true);

		if ($discover === null) {
			return false;
		}

		self::$classesToGenerate = array_merge(self::$classesToGenerate, array_filter($discover['elements'], function ($element) {
			return $element['kind'] == 'class';
		}));

		self::$functionsToGenerate = array_merge(self::$functionsToGenerate, array_filter($discover['elements'], function ($element) {
			return $element['kind'] == 'function';
		}));

		return true;
	}

	/**
	 * Generate a PHP file from a model.
	 * 
	 * @param mixed $namespace
	 * @return string
	 */
	public static function generate(?string $namespace = null): string
	{
		$namespaceDeclaration = $namespace ? "namespace $namespace {\n" : '';
		$code = "<?php\n\n";
		$code .= "/**\n";
		$code .= " * This file was generated by divengine/mirror\n";
		$code .= " * \n";
		$code .= " * Do not modify this file, changes will be lost.\n";
		$code .= " */\n\n";
		$code .= $namespaceDeclaration;
		$code .= "\tuse divengine\mirror;\n\n";

		// Generate classes
		foreach (self::$classesToGenerate as $class) {
			$classInfo = $class['info'];
			$classCode = "\n\tclass {$classInfo['class']} {\n";

			foreach ($classInfo['properties'] as $property) {
				$modifiers = $property['modifiers'] ? implode(' ', $property['modifiers']) . ' ' : '';
				$classCode .= "\t\t{$modifiers} \${$property['name']} = " . var_export(unserialize($property['default']), true) . ";\n";
			}

			foreach ($classInfo['methods'] as $method) {
				$returnType = $method['returnType'] ? ": {$method['returnType']}" : '';
				$modifiers = $method['modifiers'] ? implode(' ', $method['modifiers']) . ' ' : '';

				$classCode .= "\t\t{$modifiers} function {$method['name']} (";

				$paramStrings = [];
				foreach ($method['parameters'] as $param) {
					$paramString = '$' . $param['name'];
					if (isset($param['default'])) {
						$paramString .= ' = ' . var_export($param['default'], true);
					}
					$paramStrings[] = $paramString;
				}

				$classCode .= implode(', ', $paramStrings) . ") $returnType {\n";

				if ($method['name'] == "__construct") {
					$classCode .= "\t\t\t\$result = mirror::call('{$classInfo['class']}::{$method['name']}', func_get_args(), \$this);\n";
					foreach ($classInfo['properties'] as $property) {
						$classCode .= "\t\t\t\$this->{$property['name']} = \$result->{$property['name']};\n";
					}
				} else {
					$isStatic = in_array('static', $method['modifiers']);
					$instance = $isStatic ? '' : ', $this';
					$classCode .= "\t\t\treturn mirror::call('{$classInfo['class']}::{$method['name']}', func_get_args()$instance);\n";
				}

				$classCode .= "\t\t}\n";
			}

			$classCode .= "\t}\n";
			$code .= $classCode;
		}

		// Generate functions
		foreach (self::$functionsToGenerate as $function) {
			$functionInfo = $function['info'];

			$functionCode = "\n\tfunction {$functionInfo['name']} (";
			$paramStrings = [];
			foreach ($functionInfo['parameters'] as $param) {
				$paramString = '$' . $param['name'];
				if (isset($param['default'])) {
					$paramString .= ' = ' . var_export($param['default'], true);
				}
				$paramStrings[] = $paramString;
			}

			$functionCode .= implode(', ', $paramStrings) . ") {\n";
			$functionCode .= "\t\treturn mirror::call('" . $functionInfo['name'] . "', func_get_args());\n";
			$functionCode .= "\t}\n";
			$code .= $functionCode;
		}

		$code .= "}\n";

		return $code;
	}
}
