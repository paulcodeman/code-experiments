<?php

	require_once ('module/c--.php');

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

	define('PYTHON_NULL', [0, T_NUL, 0]);

	define('NEW_LINE', "\n");
	define('PREFIX_DATA', "constantDATA");

	define('PREFIX_NAME', '');

	$GLOBALS['functions'] = Array();

	function logs($x) { echo print_r($x,true); }

	$generator = new csphinx\generate();

	class zpython {
		public $pyByteCode = [];
		private $generator = null;
		public $start = true;
		public $type = T_NUL;
		public $args = [];
		public $cDebug = '';
		private $line = 0;
		public $include = [];
		public $localStore = [];
		public $noInitVar = ['EAX','EBX','ECX','EDX','EDI','ESI','AX','BX','CX','DX','AH','BH','ASM', 'INT'];
		public $allocTypes = [T_STR];
		private function includes($names)
		{
			$list = [];
			foreach ($names as $name)
			{
				if ($name[0] != '"')
				{
					echo 'только строка include';
					exit;
				}
				else
				{
					$name = trim(substr($name, 1, strlen($name)-2));
				}
				$nameArr = explode('.', $name);
				$exp = $nameArr[count($nameArr) - 1];
				if ($exp == 'py')
				{
					$code = file_get_contents($name);
					$o = new zpython($code);
					$o->init = 'include_'.$nameArr[0];
					logs($o->pyByteCode);
					$list[] = $c = $o->generate();
				}
				elseif ($exp == 'c')
				{
					$list[] = '#include "'.$name.'"';
				}
			}
			return implode(NEW_LINE, $list);
		}

		private function getInitFast($data, $o) {
			if (!count($data)) return '';
			$types = $o[3];
			$r = [];
			$offset = 0;
			foreach ($data as $i => $name) {
				$offset += 4;
				if ($o[0][0][7] > $i ) $r[] = "dword {$name} = DSDWORD[#ARGC+{$offset}];";
				else $r[] = "dword {$name} = 0;";
			}
			//$begin = $data[0];
			//$limit = $o[0][0][7];
			//$r[] = "__loadargs__(ARGC, {$limit}, #$begin, #ARGC);";

			return implode(NEW_LINE, $r);
		}
		private function chkType($a,$b, $def = null) {
			if (gettype($b) === 'integer')
			{
				if ($a[1] !== $b)
				{
					echo "Тип должен быть `{$def}` на линии {$this->line}!";
					exit;
				}
				else return $b;
			}

			if ($a[1] == T_FNC) $type1 = $a[3];
			else $type1 = $a[1];
			if ($b[1] == T_FNC) $type2 = $b[3];
			else $type2 = $b[1];

			if ($type1 != $type2) {
				if ($type1 != T_ADR && $type2 != T_ADR)
				{
					$line = $this->line;
					echo "Типы не совпадают на линии {$line}!";
					exit;
				}
				if ($def !== null && ($type1 !== $def && $type2 !== $def))
				{
					$line = $this->line;
					echo "Тип должен быть `{$def}` на линии {$line}!";
					exit;
				}
				if ($type1 == T_ADR) return $type2;
				if ($type2 == T_ADR) return $type1;
			}
			if ($def !== null && $type1 != $def)
			{
				$line = $this->line;
				echo "Тип должен быть `{$def}` на линии {$line}!";
				exit;
			}
			return $type1;
		}
		function __construct($python_code = '', &$generate = null)
		{
			switch (gettype($python_code))
			{
				case 'string':
					$path = getcwd().'/src';
					file_put_contents("{$path}/__pycode.py", $python_code);
					$json = exec("python3 {$path}/code.py {$path}/__pycode.py json");
					$this->pyByteCode = json_decode($json, true);
					unlink("{$path}/__pycode.py");
					break;
				case 'array':
					$this->pyByteCode = $python_code;
					break;
			}
			if ($generate != null) $this->generator = $generate;
		}

		private function getData($val)
		{
			$type = T_NUL; // default type
			$link = 0; // count links variables
			switch (gettype($val)) {
				case 'string':
					$type = T_STR;
					$val = $this->generator->initString($val);
					break;
				case 'integer':
					$type = T_INT;
					break;
				case 'double':
					$type = T_FLT;
					$val = $this->generator->initFloat($val);
					break;
				case 'array':
					$type = T_ARR;
					break;
				case 'boolean':
					if (!$val) $val = 0;
					$type = T_BOL;
					break;
				case 'NULL':
					$type = T_NUL;
					$val = 0;
					break;
			}
			return [$val, $type, $link];
		}
		private function store($name, $data)
		{
			if (!array_key_exists($name,$this->localStore)) $this->localStore[$name] = [];
			else $this->localStore[$name][count($this->localStore[$name])-1][2]--;
			$data[2]++;
			$this->localStore[$name][] = $data;
		}
		private function load($name)
		{
			if (!array_key_exists($name,$this->localStore)) return PYTHON_NULL;
			return $this->localStore[$name][count($this->localStore[$name])-1];
		}
		public function generate($tree = null){
			$dbPush = [];
			$code = [];
			$goto = [];
			$head = [];
			$lineGOTO = [];
			foreach ($this->pyByteCode as $countline => $line) {
				if ($line[2]) $this->line = $line[2];
				if (in_array($countline, $lineGOTO)) $code[] = "__LOOP_".$countline.':';
				_start:
				$gotoLabel = "__LOOP_{$line[3]}";
				if (!isset($goto[$gotoLabel])) $goto[$gotoLabel] = count($code);
				$code[] = "{$gotoLabel}:";
				$commandPython = $line[0];
				switch ($commandPython)
				{
					case 'LOAD_CONST':
						$dbPush[] = $this->getData($line[1]);
						break;
					case 'UNPACK_SEQUENCE':
						$val = $line[1];
						$x = array_pop($dbPush);
						while ($val--)
						{
							$dbPush[] = $this->getData($x[0][$val]);
						}
						break;
					case 'GET_ITER':
						//$val = array_pop($dbPush);
						$name = 'GET_ITER_'.$line[3];
						//$dbStore[$name] = $val;
						$val = $val[0];
						//$code[] = "{$name} = {$val};";
						break;
					case 'STORE_NAME':
					case 'STORE_FAST':
						$name = $line[1];
						$preview = $this->pyByteCode[$countline-1];
						$val = array_pop($dbPush);
						if ($name == 'INT')
						{
							$code[] = '$int '.$val[0].';';
							break;
						}
						//------------------------
						if ($preview[0] == 'LOAD_NAME')
						{
							if (isset($this->localStore[$preview[1]]))
							{
								foreach ($this->localStore[$preview[1]] as $i => $data)
								{
									/*if (!$this->localStore[$preview[1]][$i][2])
									{
										array_splice($this->localStore[$preview[1]], $i, 1);
										continue;
									}*/
									if ($this->localStore[$preview[1]][$i][1] == T_STR) $this->localStore[$preview[1]][$i][2]++;
								}
							}
						}
						if (isset($this->localStore[$name]))
						{
							if ($this->load($name)[2] == 1) switch ($this->load($name)[1])
							{
								case T_STR:
									$code[] = "freeMemoryString({$name});";
									break;
							}
							//$this->localStore[$name][2]--;
						}

						switch ($val[1])
						{
							case T_STR:
							case T_FLT:
							case T_INT:
								$this->generator->setVariable($name, $val[0]);
								break;
						}
						$this->store($name, $val);
						break;

						if ($val[1] == T_MKF)
						{
							$body = $val[0];
							$fastList = $val[0][0][6];
							$newFast = [];
							$old = T_NUL;
							$store = [];
							foreach ($fastList as $i => $item)
							{
								if (!in_array($item, $this->noInitVar))
								{
									$newFast[] = $item;
									if (isset($val[3][$i])) $old = $val[3][$i];
									$store[$item] = [$item, $old];
								}
							}
							$o = new pyto($body);
							$o->localStore = $store;
							$o->args = $val[0][0][6];
							$body = $o->cmm();
							$head[] = "inline dword cdecl {$name}(dword ARGC,...){".NEW_LINE.$this->getInitFast($newFast, $val)."\n$body\n}";
							$dbDEF[$name] = [$name, T_FNC, 0, $o->type, $val[3], null];
						}
						elseif (0&&$val[1] == T_ARR)
						{
							$val = $val[0];
							$val2 = [];
							foreach ($val as $item)
							{
								if (gettype($item) == 'string')
								{
									$val2[] = $this->initString($item);
								}
								else
								{
									$val2[] = $item;
								}
							}
							$code[] = "{$name} = {".implode(',', $val2)."};";
						}
						elseif ($val[1] == T_FNC)
						{
							$code[] = "{$name} = #{$val[0]};";
						}
						elseif ($val[1] == T_STR)
						{
							$code[] = "{$name} = {$val[0]};";
						}
						elseif ($val[1] == T_INT)
						{
							if (isset($dbStore[$name]) && $dbStore[$name][1] == T_STR)
							{
								$code[] = "freeVariableString({$name});";
							}
							$code[] = "{$name} = {$val[0]};";
						}
						else
						{
							$data = $val[0];
							if ($data === "{$name}+1")
							{
								$code[] = "{$name}++;";
							}
							elseif ($data === "{$name}-1")
							{
								$code[] = "{$name}--;";
							}
							else
							{
								if (isset($dbStore[$name]) && in_array($dbStore[$name][1],$this->allocTypes))
								{
									$code[] = "freeAllocVariable({$name});";
								}
								$code[] = "{$name} = {$data};";
							}
							$dbStore[$name] = $val;
						}
						break;
					case 'BUILD_TUPLE':
					case 'BUILD_LIST':
						//$val = array_pop($dbPush);
						$count = $line[1];
						$old = 0;
						$args = [];

						while ($count--)
						{
							$val = array_pop($dbPush);
							if (!$old) $old = $val;
							else $this->chkType($old, $val);
							$args[] = $val[0];
						}
						if (count($args)) $args = ','.implode(',', $args);
						else $args = '';
						if ($old[1] == T_FLT) $dbPush[] = ["listInitFloat({$line[1]}.0{$args})", T_ARR, $old[1]];
						else $dbPush[] = ["listInit({$line[1]}{$args})", T_ARR, $old[1]];
						break;
					case 'BUILD_MAP':
						//$val = array_pop($dbPush);
						$count = $line[1];
						$old = 0;
						$args = [];

						while ($count--)
						{
							$val = array_pop($dbPush);
							$name = array_pop($dbPush);
							$this->chkType($name, T_STR, 'String');
							if (!$old) $old = $val;
							else $this->chkType($old, $val);
							$args[] = $val[0];
							$args[] = $name[0];
						}
						if (count($args)) $args = ','.implode(',', array_reverse($args));
						else $args = '';
						$dbPush[] = ["dictInit({$line[1]}{$args})", T_DCT, $old[1]];
						break;
					case 'BUILD_CONST_KEY_MAP':
						//$val = array_pop($dbPush);
						$count = $line[1];
						$old = 0;
						$args = [];
						$keys = array_pop($dbPush);
						$keys = $keys[0];
						while ($count--)
						{
							$val = array_pop($dbPush);
							$name = ['"'.array_pop($keys).'"',T_STR];
							$this->chkType($name, T_STR, 'String');
							if (!$old) $old = $val;
							else ;//$this->chkType($old, $val); FIX!!!
							$args[] = $val[0];
							$args[] = $name[0];
						}
						if (count($args)) $argsCmm = ','.implode(',', array_reverse($args));
						else $argsCmm = '';
						$dbPush[] = ["dictInit({$line[1]}{$argsCmm})", T_DCT, $old[1], $args];
						break;
					case 'LOAD_ATTR':
						$attr = $line[1];
						$val = array_pop($dbPush);
						$dbPush[] = ['_TYPE'.$val[1].'_'.$attr, T_FNC, 0, $val[1], null, $val];
						break;
					case 'LOAD_NAME':
					case 'LOAD_GLOBAL':
						$name = $line[1];
						if (isset($dbDEF[$name]))
						{
							$dbPush[] = $dbDEF[$name];
						}
						if (!isset($this->localStore[$name]))
						{
							echo "`{$name}` не определено!";
							exit;
						}
						$variable = $this->load($name);
						$variable[0] = $name;
						$dbPush[] = $variable;
						break;
					case 'LOAD_FAST':
						$name = $line[1];
						if (in_array($name, $this->noInitVar))
						{
							$dbPush[] = [$name, T_ADR];
							break;
						}
						if (!isset($this->localStore[$name]))
						{
							$dbPush[] = [$name, T_NUL];
							break;
						}
						$list = $this->localStore[$name];
						$list[0] = $name;
						$dbPush[] = $list;
						break;
					case 'COMPARE_OP':
						$b = array_pop($dbPush);
						$a = array_pop($dbPush);

						$x = $a[0];
						$y = $b[0];
						if ($a[1] == T_NUL || $b[1] == T_NUL)
						{
							$dbPush[] = [0, T_NUL];
						}
						else switch ($line[1])
						{
							case 'is':
								$this->chkType($a, $b);
								$dbPush[] = ["intcmp(#$x,#$y)", T_BOL];
								break;
							case '==':
								$type = $this->chkType($a, $b);
								if ($type == T_STR)
								{
									$dbPush[] = ["streq($x,$y)", T_BOL];
								}
								else
								{
									$dbPush[] = ["inteq($x,$y)", T_BOL];
								}
								break;
							case '!=':
								$type = $this->chkType($a, $b);
								if ($type == T_STR)
								{
									$dbPush[] = ["strneq($x,$y)", T_BOL];
								}
								else
								{
									$dbPush[] = ["intneq($x,$y)", T_BOL];
								}
								break;
							case '>':
								$this->chkType($a, $b, T_INT);
								$dbPush[] = ["intb($x,$y)", T_BOL];
								break;
							case '<':
								$this->chkType($a, $b, T_INT);
								$dbPush[] = ["intb($y,$x)", T_BOL];
								break;
							case '>=':
								$this->chkType($a, $b, T_INT);
								$dbPush[] = ["intbc($x,$y)", T_BOL];
								break;
							case '<=':
								$this->chkType($a, $b, T_INT);
								$dbPush[] = ["intmc($x,$y)", T_BOL];
								break;
						}
						break;
					case 'SETUP_LOOP':
						$gotoLabel = '__LOOP_'.$line[1];
						$code[] = $gotoLabel.':';
						$goto[$gotoLabel] = false;
						$loopData = $line[1];
						//$lineGOTO[] = $line[1];
						break;
					case 'JUMP_ABSOLUTE':
						$gotoLabel = '__LOOP_'.$line[1];
						$code[] = "goto {$gotoLabel};";
						$goto[$gotoLabel] = false;
						break;
					case 'POP_JUMP_IF_FALSE':
						$a = array_pop($dbPush);
						$l = (int)$line[1];
						$val = $a[0];
						$gotoLabel = '__LOOP_'.$l;
						$code[] = "IF (!$val) goto {$gotoLabel};";
						//$lineGOTO[] = $l;
						$goto[$gotoLabel] = false;
						break;
					case 'POP_JUMP_IF_TRUE':
						$a = array_pop($dbPush);
						$l = (int)$line[1];
						$val = $a[0];
						$gotoLabel = '__LOOP_'.$l;
						$code[] = "IF ($val) goto {$gotoLabel};";
						$goto[$gotoLabel] = false;
						//$lineGOTO[] = $l;
						break;
					case 'BREAK_LOOP':
						$gotoLabel = '__LOOP_'.$loopData;
						$code[] = 'goto '.$gotoLabel.';';
						$goto[$gotoLabel] = false;
						break;
					case 'INPLACE_ADD':
					case 'BINARY_ADD':
						$b = array_pop($dbPush);
						$a = array_pop($dbPush);

						$return = $this->generator->mathAdd($a, $b);
						if ($return) $dbPush[] = $return;
						else $dbPush[] = [0, T_NUL, 0];
						break;
					case 'INPLACE_MULTIPLY':
					case 'BINARY_MULTIPLY':
						$b = array_pop($dbPush);
						$a = array_pop($dbPush);
						$type = $this->chkType($a, $b);
						$x = $a[0];
						$y = $b[0];
						if ($a[1] == T_STR && $b[1] == T_INT) {
							$dbPush[] = ["strrep($x,$y)", $a[1]];
						}
						elseif ($type == T_NUL) {
							$dbPush[] = [0, T_NUL];
						}
						else {
							$type = $this->chkType($a, $b, T_INT);
							$dbPush[] = ["calc($x*$y)", $type];
						}
						break;
					case 'BINARY_LSHIFT':
						$b = array_pop($dbPush);
						$a = array_pop($dbPush);
						//$type = $this->chkType($a, $b);
						$x = $a[0];
						$y = $b[0];
						if ($a[1] == T_NUL || $b[1] == T_NUL) {
							$dbPush[] = [0, T_NUL];
							break;
						}
						$dbPush[] = ["$x<<$y", T_INT];
						break;
					case 'BINARY_RSHIFT':
						$b = array_pop($dbPush);
						$a = array_pop($dbPush);
						//$type = $this->chkType($a, $b);
						$x = $a[0];
						$y = $b[0];
						if ($a[1] == T_NUL || $b[1] == T_NUL) {
							$dbPush[] = [0, T_NUL];
							break;
						}
						$dbPush[] = ["$x>>$y", T_INT];
						break;
					case 'BINARY_OR':
						$b = array_pop($dbPush);
						$a = array_pop($dbPush);
						//$type = $this->chkType($a, $b);
						$x = $a[0];
						$y = $b[0];
						if ($a[1] == T_NUL || $b[1] == T_NUL) {
							$dbPush[] = [0, T_NUL];
							break;
						}
						$dbPush[] = ["$x|$y", T_INT];
						break;
					case 'INPLACE_POWER':
					case 'BINARY_POWER':
						$b = array_pop($dbPush);
						$a = array_pop($dbPush);
						if ($b[1] == T_INT)
						{
							if ($a[1] == T_STR) $dbPush[] = ["powstr({$a[0]},{$b[0]})", $a[1]];
							elseif ($a[1] == T_INT) $dbPush[] = ["powint({$a[0]},{$b[0]})", $a[1]];
							break;
						}
						$dbPush[] = [0, T_NUL];
						break;
					case 'INPLACE_TRUE_DIVIDE':
					case 'BINARY_TRUE_DIVIDE':
						$b = array_pop($dbPush);
						$a = array_pop($dbPush);
						$type = $this->chkType($a, $b);
						$x = $a[0];
						$y = $b[0];
						if ($type == T_NUL) {
							$dbPush[] = [0, T_NUL];
						}
						else {
							$type = $this->chkType($a, $b, T_INT);
							$dbPush[] = ["($x)/($y)", $type];
						}
						break;
					case 'INPLACE_SUBTRACT':
					case 'BINARY_SUBTRACT':
						$b = array_pop($dbPush);
						$a = array_pop($dbPush);
						$type = $this->chkType($a, $b, T_INT);
						$x = $a[0];
						$y = $b[0];
						if ($type == T_NUL) {
							$dbPush[] = [0, T_NUL];
						}
						else {
							$type = $this->chkType($a, $b, T_INT);
							$dbPush[] = ["$x-$y", $type];
						}
						break;
					case 'BUILD_SLICE':
						if ($line[1] == 2)
						{
							$dbPush[] = [[]];
						}
						break;
					case 'BINARY_SUBSCR':
						$key = array_pop($dbPush);
						$data = array_pop($dbPush);

						if ($data[1] == T_STR)
						{
							$val = $data[0];
							$k = $key[0];
							$dbPush[] = ["PY_getChar($val, $k)", T_STR];
						}
					break;
					case 'RETURN_VALUE':
						$this->generator->functionReturn(array_pop($dbPush));
						break;
					case 'ROT_TWO':
						$val1 = array_pop($dbPush);
						$val2 = array_pop($dbPush);
						$dbPush[] = $val1;
						$dbPush[] = $val2;
						break;
					case 'ROT_THREE':
						$val1 = array_pop($dbPush);
						$val2 = array_pop($dbPush);
						$val3 = array_pop($dbPush);
						$dbPush[] = $val1;
						$dbPush[] = $val3;
						$dbPush[] = $val2;
						break;
					case 'CALL_FUNCTION':
						$arg = [];
						$countArguments = $line[1];
						for ($i = 0; $i < $countArguments; $i++) {
							array_unshift($arg, array_pop($dbPush));
						}
						$val = array_pop($dbPush);
						$name = $val[0];
						if ($val[1] != T_FNC)
						{
							echo "{$name} не функция!";
							exit;
						}
						if ($name == 'include')
						{

							$head[] = $this->includes($arg);
							$dbPush[] = [0, T_NUL];
							break;
						}
						if ($name == 'id')
						{
							$dbPush[] = ['#'.$arg[0][0], T_ADR];
							break;
						}
						$type = T_NUL;
						if ($val[1] == T_FNC) $type = $val[3];
						$args = '';
						$oldType = -1;
						foreach($arg as $index=>$valArg)
						{
							if (isset($val[4][$index])) $typeArg = $val[4][$index];
							else $typeArg = $oldType;
							$oldType = $typeArg;
							switch ($typeArg)
							{
								case T_STR:
									if ($valArg[1] == T_INT) $args .= ',itoa('.$valArg[0].')';
									elseif ($valArg[1] == T_FLT) $args .= ',ftoa('.$valArg[0].')';
									elseif ($valArg[1] == T_BOL) $args .= ',btoa('.$valArg[0].')';
									else $args .= ','.$valArg[0];
									break;
								case T_INT:
									if ($valArg[1] == T_STR) $args .= ',atoi('.$valArg[0].')';
									else $args .= ','.$valArg[0];
									break;
								case T_FLT:
									if ($valArg[1] == T_STR) $args .= ',atof('.$valArg[0].')';
									else $args .= ','.$valArg[0];
									break;
								default:
									$args .= ','.$valArg[0];
									break;
							}
						}
						$dbPush[] = ["{$name}({$countArguments}$args)", $type];
						break;
					case 'POP_TOP':
						$val = array_pop($dbPush);
						$code[] = $val[0].';';
						break;
					case 'MAKE_FUNCTION':
						$val = array_pop($dbPush);
						$types = [];
						if ($line[4] == 'annotations')
						{
							$annotations = array_reverse(array_pop($dbPush)[3]);
							foreach ($annotations as $valI => $valArg)
							{
								if ($valI&1)
								{
									switch ($valArg)
									{
										case 'int':
											$types[] = T_INT;
											break;
										case 'str':
											$types[] = T_STR;
											break;
										default:
											echo "Тип `{$valArg}` не существует!";
											exit;
											break;
									}
								}
							}
						}
						elseif(count($line[6]))
						{
							echo "Аргументы должны быть с типами!";
							exit;
						}
						if ($line[1] == 1) $dbPush[] = [$val[0], T_MKF, array_pop($dbPush)];
						else $dbPush[] = [$val[0], T_MKF, null, $types];
						break;
				}
			}
			// очистка лишних goto
			foreach ($goto as $line) if ($line !== false) $code[$line] = '';
			if ($this->start) {
				$this->generator->addCode($this->generator->startCode($this->localStore));
			}
			//return $head.implode(NEW_LINE, $code);
		}
	}

	function makeLibs($nameLib = "./include/py.h", $path = './libpy', $prefix = 'PY_')
	{
		$listCode = [];
		$list = scandir($path);
		foreach ($list as $name)
		{
			if ($name == '.' || $name == '..') continue;
			$code = file_get_contents($path.'/'.$name);
			$name = explode('.',$name);
			$name = $prefix.$name[0];
			$listCode[] = "inline cdecl dword {$name}(dword ARGC,...){";
			$listCode[] = "\t".str_replace(NEW_LINE,"\n\t", $code);
			$listCode[] = "}";
		}
		$code = implode(NEW_LINE, $listCode);
		file_put_contents($nameLib, $code);
	}

	if (isset($argv[1]))
	{
		if (!file_exists($argv[1]))
		{
			echo "File `{$argv[1]}` no exists!";
			exit;
		}
		$generator = new csphinx\generate();
		$generator->include = ['include/kolibri.h','include/mem.h','include/date.h','include/strings.h','include/fs.h','include/py.h','include/console.h'];

		$python = new zpython(file_get_contents($argv[1]), $generator);

		logs($python->pyByteCode);

		$python->generate();

		$binary = $generator->build();

		if ($binary)
		{
			$name = explode('.', $argv[1]);
			if (!isset($argv[2])) $argv[2] = $name[0].'.kex';
			file_put_contents($argv[2], $binary);
		}
	}
	else
	{
		echo "Help:....";
	}

	//exec("sudo -u pavel sh -c \"export DISPLAY=:0; nautilus\"");
	//file_put_contents('data.c', $c);
	//$path = getcwd();
	//exec("/usr/kex {$path}/data.kex 2>&1", $out);
	//logs($out);

