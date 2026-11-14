<?php
	namespace csphinx;
	class generate
	{
		private $code = [];
		private $head = [];
		private $init = 'main';
		private $fullCode = '';
		private $globalData = [];
		private $cDebug = [];
		private $compiler = 'src/cmm';
		private $newLine = "\n";
		public $include = [];
		public $initName = [
			'consoleInit' => ['consoleInit', T_FNC, 0, T_BOL]
			,'int' => ['int', T_FNC, 0, T_INT,[T_STR]]
			,'str' => ['str', T_FNC, 0, T_STR,[T_INT]]
			,'print' => ['PY_print', T_FNC, 0, T_BOL,[T_STR]]
			,'input' => ['PY_input', T_FNC, 0, T_STR]
			,'trim' => ['PY_trim', T_FNC, 0, T_STR]
			,'len' => ['PY_len', T_FNC, 0, T_INT]
			,'stdout' => ['stdout', T_FNC, 0, T_NUL,[T_STR]]
			,'malloc' => ['PY_malloc', T_FNC, 0, T_ADR]
			,'free' => ['PY_free', T_FNC, 0, T_ADR]
			,'include' => ['include', T_FNC, 0, T_NUL]
			,'id' => ['id', T_FNC, 0, T_ADR]
			,'__name__' => ['PY__NAME__', T_FNC, 0, T_STR]
		];
		public function setVariable($name, $data)
		{
			$this->code[] = "{$name} = {$data};";
		}
		public function addCode($code)
		{
			$this->fullCode .= $code;
		}
		public function startCode($localStore = [])
		{
			$cmmData = [];
			$cmmData[] = "globalDataSectionSTART:\n";
			foreach ($this->globalData as $iData => $data)
			{
				$name = PREFIX_DATA.$iData;
				switch ($data[1])
				{
					case T_STR:
						$cmmData[] = "char {$name}[] = {$data[0]};\n";
						break;
					case T_FLT:
						$cmmData[] = "float $name = {$data[0]};\n";
						break;
				}
			}
			$cmmData[] = "globalDataSectionEND:\n";
			$includeFiles = $this->getIncludes();
			$initVars = $this->getInitVar($localStore);
			$bodyFunction = "inline dword {$this->init}(void){{$this->newLine}".implode($this->newLine, $this->code)."{$this->newLine}}";
			$resultCode = [$includeFiles,implode($this->newLine,$cmmData),$initVars,implode($this->newLine, $this->head),$bodyFunction];
			$this->code = [];
			return $this->prettyCode(implode($this->newLine, $resultCode));
		}
		public function functionReturn($r)
		{
			if (!$r[0]) $this->code[] = 'return 0;';
			else $this->code[] = "return {$r[0]};";
		}
		public function mathAdd($a, $b)
		{
			if ($a[1] == T_INT && $b[1] == T_STR){
				if ($a[0]) $a = $a[0].'+';
				else $a = '';
				return ["{$a}atoi({$b[0]})", T_INT, 0];
			}
			if ($a[1] == T_FLT && $b[1] == T_STR){
				return ["{$a[0]}+atof({$b[0]})", T_FLT, 0];
			}
			if ($a[1] == T_STR && $b[1] == T_INT){
				return ["additionString({$a[0]}, itoa({$b[0]}))", T_STR, 0];
			}
			if ($a[1] == T_STR && $b[1] == T_FLT){
				return ["additionString({$a[0]}, ftoa({$b[0]}))", T_STR, 0];
			}
			if ($a[1] == T_STR && $b[1] == T_BOL){
				return ["additionString({$a[0]}, btoa({$b[0]}))", T_STR, 0];
			}
			return null;
		}
		public function mathSub($a, $b)
		{
			if ($a[1] == T_INT && $b[1] == T_INT){
				return ["{$a[0]}-{$b[0]}", T_INT, 0];
			}
			if ($a[1] == T_FLT && $b[1] == T_FLT){
				return ["{$a[0]}-{$b[0]}", T_INT, 0];
			}
			if ($a[1] == T_INT && $b[1] == T_FLT){
				return ["Float({$a[0]})-{$b[0]}", T_FLT, 0];
			}
			if ($a[1] == T_FLT && $b[1] == T_INT){
				return ["{$a[0]}-Float({$b[0]})", T_FLT, 0];
			}
			return null;
		}
		public function build()
		{
			$path = sys_get_temp_dir();
			$name = md5(rand());
			logs($this->fullCode);
			file_put_contents("{$path}/{$name}.c", $this->fullCode);
			exec("{$this->compiler} {$path}/{$name}.c 2>&1", $this->cDebug);
			$this->logger($this->cDebug);
			unlink("{$path}/{$name}.c");
			if (file_exists("{$path}/{$name}.kex"))
			{
				$data = file_get_contents("{$path}/{$name}.kex");
				unlink("{$path}/{$name}.kex");
				return $data;
			}
			return null;
		}
		private function getIncludes()
		{
			$r = '';
			foreach ($this->include as $name) $r .= "#include \"{$name}\"".$this->newLine;
			return $r;
		}
		public function logger($data)
		{
			logs($data);return;
			$error = [];
			foreach ($data as $line)
			{
				if (preg_match('#(.*?)\((\d+)\).*?\'(.*?)\'.*undefined\.$#ui', $line, $data))
				{
					$error[] = [$data[1],$data[2],$data[3]];
				}
			}
			if (count($error))
			{
				echo "\nОшибка компиляции:\n";
				foreach ($error as $line)
				{
					echo "В файле {$line[0]}\n";
					echo "На линии {$line[1]}\n";
					echo "Не определена {$line[2]}\n";
				}
			}
		}
		private function getInitVar($data) {
			$r = [];
			foreach ($data as $name => $line) {
				$r[] = ":dword {$name} = 0;";
			}
			return implode($this->newLine, $r);
		}
		private function prettyCode($code)
		{
			$code = explode($this->newLine, $code);
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
			return implode($this->newLine, $newCode);
		}
		public function initString($string)
		{
			$data = [];
			$string = pack("La*", strlen($string), $string);
			for($i = 0; $i < strlen($string); $i++)
			{
				$symbol = str_replace('\'','\\\'',$string[$i]);
				$data[] = ord($symbol);
			}
			$data[] = 0;
			$name = PREFIX_DATA.count($this->globalData);
			$this->globalData[] = ['{'.implode(',',$data).'}', T_STR];
			return '#'.$name;
		}
		public function initFloat($float) // or double
		{
			$name = PREFIX_DATA.count($this->globalData);
			$this->globalData[] = [$float, T_FLT];
			return '#'.$name;
		}
	}
	?>