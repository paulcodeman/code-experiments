<?php

	define('T_NUL', 0);
	define('T_STR', 1);
	define('T_INT', 2);
	define('T_FLT', 9);
	define('T_FNC', 3);
	define('T_MKF', 4);
	define('T_ARR', 5);
	define('T_DCT', 10);
	define('T_ADR', 6);
	define('T_BOL', 7);
	define('T_ATR', 8);

	if (isset($argv[1]))
	{
		if (!file_exists($argv[1]))
		{
			echo "File `{$argv[1]}` no exists!";
			exit;
		}

	}
	else
	{
		echo "Help:....";
		die();
	}

	$path = getcwd().'/src';
	$name = md5(rand()).'.py';
	file_put_contents("{$path}/{$name}", file_get_contents($argv[1]));
	$json = exec("python3 {$path}/ast.py {$path}/{$name} json");
	$tree = json_decode($json, true);
	unlink("{$path}/{$name}");

	require_once ('./module/cmm.php');

	class ZPython extends GenerateCode
	{
		public $start;
		private $tree;
		private $dispatcher;
		protected $code;
		private $defaultProcess;
		public $headInclude;
		public $headConstant;
		public $headVariable;
		public $headFunction;
		public $variables;
		public $constants;
		public $functions;
		private $stack;
		function push($data)
		{
			$this->stack[] = $data;
		}
		function pop()
		{
			return array_pop($this->stack);
		}
		function write($data)
		{
			if (!count($this->code) && $this->start) $this->firstStartCode();
			$this->code[] = $data;
		}
		function process($i = 0)
		{
			if (isset($this->tree[$i]))
			{
				$this->processGenerate($this->tree[$i]);
				$this->process(++$i);
			}
		}
		function processGenerate($element, $contextData = null)
		{
			if (isset($this->dispatcher[$element[0]])) $process = $this->dispatcher[$element[0]];
			else $process = $this->defaultProcess;
			$object = [
				'value' => $element[1],
				'name' => $element[0],
				'local' => $element[6]
			];
			$process->init($this, $contextData);
			$process->pre($object, $this, $contextData);
			//$process->post($object, $this, $contextData);
		}
		function addConstant($data)
		{
			$this->constants[] = $data;
			return count($this->constants)-1;
		}
		function addFunction($function)
		{
			$this->functions[] = $function;
			return count($this->constants)-1;
		}
		function incVariable($name)
		{
			if (array_key_exists($name, $this->variables))
			{
				$list = &$this->variables[$name];
				$list[count($list)-1][2]++;
			}
		}
		function getVariable($name)
		{
			if (!array_key_exists($name, $this->variables)) return null;
			$list = &$this->variables[$name];
			return $list[count($list)-1];
		}
		function setVariable($name, $data)
		{
			if (!array_key_exists($name, $this->variables)) $this->variables[$name] = [];
			else
			{
				$list = &$this->variables[$name];
				if (!--$list[count($list)-1][2])
				{
					$this->freeAlloc($name, $list[count($list)-1]);
					//unset($list);
				}
			}
			//$data[2]++;
			$this->variables[$name][] = $data;
		}
		function __construct($tree, $start = true, &$constants = [], &$vars = [], &$function = [])
		{
			$this->tree = $tree;
			$this->start = $start;
			$this->code = [];
			$this->variables = $vars;
			$this->constants = $constants;
			$this->headVariable = [];
			$this->headInclude = [];
			$this->functions = [];
			$this->headConstant = $constants;
			$this->headFunction = $function;
			$this->defaultProcess = new NodeProcessor();
			$this->dispatcher = [
				'LOAD_CONST' => new LoadConst(),
				'LOAD_NAME' => new LoadName(),
				'LOAD_FAST' => new LoadName(),
				'BINARY_ADD' => new BinaryAdd(),
				'STORE_NAME' => new StoreVariable(),
				'STORE_FAST' => new StoreVariable(),
				'BUILD_CONST_KEY_MAP' => new BuildConstKeyMap(),
				'MAKE_FUNCTION' => new MakeFunction(),
				'CALL_FUNCTION' => new CallFunction(),
				'POP_TOP' => new PopTop(),
				'RETURN_VALUE' => new ReturnValue()
			];
			$this->process(0);
			if ($this->start) $this->lastStartCode();
			$this->initConstant();
			$this->initVariable();
			$this->initFunction();
			if ($this->start) $this->code = $this->structureCode();
		}
	}

	//print_r($tree);
	$zp = new ZPython($tree);
	print_r($zp->getCode());