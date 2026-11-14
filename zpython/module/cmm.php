<?php

	define('CONSTANT_PREFIX', 'const_prefix');
	define('FUNCTION_PREFIX', 'func_prefix');
	define('TYPE_FLOAT', 'float');
	//define('TYPE_INTEGER', 'dword');
	define('NEW_LINE', "\n");
	class GenerateCode
	{
		function initString($string)
		{
			$data = [];
			$string = pack("La*", strlen($string), $string);
			for($i = 0; $i < strlen($string); $i++)
			{
				$symbol = str_replace('\'','\\\'',$string[$i]);
				$data[] = ord($symbol);
			}
			$data[] = 0;
			return '{'.implode(',',$data).'}';
		}
		function structureCode()
		{
			return [$this->headInclude,$this->headConstant,$this->headVariable,$this->headFunction,$this->code];
		}
		function firstStartCode()
		{
			$this->code[] = 'int main(void) {';
		}
		function lastStartCode()
		{
			$this->code[] = '}';
		}
		function initConstant()
		{
			$this->headConstant = [];
			foreach ($this->constants as $i => $value)
			{
				$name = CONSTANT_PREFIX.$i;
				$data = 0;
				$type = 'dword';
				switch ($value[0])
				{
					case T_STR:
						$data = $this->initString($value[1]);
						$name = CONSTANT_PREFIX.$i.'[]';
						break;
					case T_FLT:
						$type = TYPE_FLOAT;
						$data = $value[1];
						break;
				}
				$this->headConstant[] = "{$type} {$name} = {$data};";
			}
		}
		function initFunction()
		{
			$this->headFunction = [];
			foreach ($this->functions as $i => $function)
			{
				$name = FUNCTION_PREFIX.$i;
				$args = implode(',', $function['args']);
				$this->headFunction[] = "dword {$name}(dword $args){";
				$this->headFunction[] = $function['variables'];
				$this->headFunction[] = $function['code'];
				$this->headFunction[] = '}';
			}
		}
		function initVariable()
		{
			$this->headVariable = [];
			foreach ($this->variables as $name => $val)
			{
				if (!$val) continue;
				$this->headVariable[] = "dword {$name} = 0;";
			}
			//$this->headVariable
		}
		function implodeAll($arr)
		{
			$return = '';
			foreach ($arr as $value)
			{
				if (gettype($value) == 'array') $return .= $this->implodeAll($value);
				else $return .= $value.NEW_LINE;
			}
			return $return;
		}
		function getCode()
		{
			return $this->prettyCode($this->implodeAll($this->code));
		}
		private function prettyCode($code)
		{
			$code = explode(NEW_LINE, $code);
			$newCode = [];
			$count = 0;
			foreach ($code as $line) {
				$line = trim($line);
				if (!$line) continue;
				$l = substr_count($line, '{');
				$r = substr_count($line, '}');

				if ($l == $r) {
					$newCode[] = str_repeat("\t", $count).trim($line);
					$count -= $r;
				}
				else {
					$count -= $r;
					$newCode[] = str_repeat("\t", $count).trim($line);
				}
				$count += $l;
			}
			return implode(NEW_LINE, $newCode);
		}
		function freeAlloc($name, &$value)
		{
			switch ($value[0])
			{
				case T_STR:
					$this->write("freeString({$name});");
					break;
			}
		}
		function convertString($value)
		{
			switch ($value[0])
			{
				case T_STR:
					return $value[1];
				case T_INT:
					return "IntToStr({$value[1]})";
				case T_FLT:
					return "FltToStr({$value[1]})";
			}
			return '';
		}
		function convertInteger($value)
		{
			switch ($value[0])
			{
				case T_STR:
					return "StrToInt({$value[1]})";
				case T_INT:
					return $value[1];
			}
			return 0;
		}
		function convertFloat($value)
		{
			switch ($value[0])
			{
				case T_STR:
					return "StrToFlt({$value[1]})";
				case T_INT:
					return "IntToFlt({$value[1]})";
				case T_FLT:
					return $value[1];
			}
			return 0;
		}
		function concatString($a, $b)
		{
			$a = $this->convertString($a);
			$b = $this->convertString($b);
			return "concatString({$a}, {$b})";
		}
		function integerAdd($a, $b)
		{
			$a = $this->convertInteger($a);
			$b = $this->convertInteger($b);
			return "{$a} + {$b}";
		}
		function floatAdd($a, $b)
		{
			$a = $this->convertFloat($a);
			$b = $this->convertFloat($b);
			return "addFloat({$a}, {$b})";
		}
		function integerMul($a, $b)
		{
			$a = $this->convertInteger($a);
			$b = $this->convertInteger($b);
			return "{$a} * {$b}";
		}
		function integerDiv($a, $b)
		{
			$a = $this->convertInteger($a);
			$b = $this->convertInteger($b);
			return "{$a} / {$b}";
		}
		function integerSub($a, $b)
		{
			$a = $this->convertInteger($a);
			$b = $this->convertInteger($b);
			return "{$a} - {$b}";
		}
	}

	class NodeProcessor
	{
		protected $zpython;
		protected $zpythonData;
		function init($context, $data)
		{
			$this->zpython = $context;
			$this->zpythonData = $data;
		}
		function genNameFunction()
		{
			return FUNCTION_PREFIX.(count($this->zpython->functions));
		}
		function pre($element, $context, $data)
		{
			echo "Function `{$element['name']}` no exists!";
			exit();
		}
		function post($element, $context, $data)
		{

		}
		function error($message)
		{
			echo $message;
			exit;
		}
		public $ASM_REG = [
			'EAX','EBX','ECX','EDX','ESI','EDI','ESP','EBP'
		];
		public $ASM_CMD = [
			'INT'
		];
	}
	class ReturnValue extends NodeProcessor
	{
		function pre($element, $context, $data)
		{
			$value = $this->zpython->pop();
			$this->zpython->write("return {$value[1]};");
		}
	}
	class CallFunction extends NodeProcessor
	{
		function pre($element, $context, $data)
		{
			$count = $element['value'];
			$args = [];
			for ($i=0;$i<$count;$i++)
			{
				$arg = $this->zpython->pop();
				$args[] = $arg[1];
			}
			$args[] = $count;
			$args = implode(',', array_reverse($args));
			$function = $this->zpython->pop();
			$this->zpython->push([$function[1][3],"{$function[1][0]}({$args})",1]);
		}
	}
	class PopTop extends NodeProcessor
	{
		function pre($element, $context, $data)
		{
			$value = $this->zpython->pop();
			$this->zpython->write("$value[1];");
		}
	}
	class BuildConstKeyMap extends NodeProcessor
	{
		function pre($element, $context, $data)
		{
			$x=$this->zpython->pop();
			print_r($x);
			print_r($element);exit;
		}
	}
	class MakeFunction extends NodeProcessor
	{
		function pre($element, $context, $data)
		{
			$code = $this->zpython->pop()[1];
			$name = $this->genNameFunction();
			$local = $code[0][6];
			$argc = $code[0][7];
			$zpObject = new ZPython($code, false);
			$code = $zpObject->getCode();
			$args = ['argc'];
			for ($i = 0; $i < $argc; $i++)
			{
				$args[] = $local[$i];
				unset($zpObject->variables[$local[$i]]);
			}
			$zpObject->initVariable();
			//print_r($function->headVariable);exit;
			$this->zpython->addFunction(['code'=>$code,'args'=>$args,'variables'=>$zpObject->headVariable]);
			$this->zpython->push([T_FNC, ['#'.$name, $argc, $local, T_INT], 1]);
			//exit;
		}
	}
	class BinaryAdd extends NodeProcessor
	{
		function pre($element, $context, $data)
		{
			$b = $context->pop();
			$a = $context->pop();
			$a[2]--;
			$b[2]--;
			if ($a[0] === T_STR || $b[0] === T_STR)
			{
				$context->push([T_STR, $context->concatString($a, $b), 1]);
			}
			elseif ($a[0] === T_FLT || $b[0] === T_FLT)
			{
				$context->push([T_FLT, $context->floatAdd($a, $b), 1]);
			}
			else switch($a[0])
			{
				case T_INT:
					$context->push([$a[0], $context->integerAdd($a, $b), 1]);
					break;
			}
		}
	}
	class LoadName extends NodeProcessor
	{
		function pre($element, $context, $data)
		{
			$value = $this->zpython->getVariable($element['value']);
			if (!$value)
			{
				$this->error("Is `{$element['value']}` no exists!");
			}
			$vars = &$this->zpython->variables[$element['value']];
			switch ($value[0])
			{
				case T_FNC:
					$value[1][0] = $element['value'];
					break;
				default:
					$value[1] = $element['value'];
			}
			$value[2] = &$vars[count($vars)-1][2];
			$this->zpython->incVariable($element['value']);
			$this->zpython->push($value);
		}
	}
	class LoadConst extends NodeProcessor
	{
		function pre($element, $context, $data)
		{
			switch (gettype($element['value']))
			{
				case "integer":
					$context->push([T_INT, $element['value'], 1]);
					break;
				case "string":
					$count = $context->addConstant([T_STR, $element['value']]);
					$context->push([T_STR, '#'.CONSTANT_PREFIX.$count, 1]);
					break;
				case "double":
					$count = $context->addConstant([T_FLT, $element['value']]);
					$context->push([T_FLT, '#'.CONSTANT_PREFIX.$count, 1]);
					break;
				case "array":
				case "object":
					$count = $context->addConstant([T_FLT, $element['value']]);
					$context->push([T_MKF, $element['value'], 1]);
					break;
				default:
					$context->push([T_NUL, '0', 1]);
			}
		}
	}

	class StoreVariable extends NodeProcessor
	{
		function pre($element, $context, $data)
		{
			$name = trim($element['value']);
			$value = $this->zpython->pop();
			switch ($value[0])
			{
				case T_FNC:
					$data = $value[1][0];
					//$value[1][0] = $element['value'];
					break;
				default:
					$data = $value[1];
			}
			if (array_search($name, $this->ASM_REG) === false) $this->zpython->setVariable($name, $value);
			$this->zpython->write("{$name} = {$data};");
		}
	}