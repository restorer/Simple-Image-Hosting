<?php

require_once(BASE_PATH.'incl/s/functions.php');

define('LBRA', '<'.'?');
define('RBRA', '?'.'>');

class Template
{
	function Parse($in, $out)
	{
		$buf = file_get_contents($in);
		$pos = 0;
		$res = '';

		if ($buf != '')
		{
			$sz = strlen($buf);
			$lbra_sz = strlen(LBRA);
			$rbra_sz = strlen(RBRA);

			for (;;)
			{
				$lb = strpos($buf, LBRA, $pos);

				if ($lb === false)
				{
					if ($pos) $res .= substr($buf, $pos);
					else $res = $buf;

					break;
				}

				$res .= substr($buf, $pos, $lb-$pos);
				$pos = $lb + $lbra_sz;
				$cnt = '';

				while ($pos < $sz)
				{
					if ($buf{$pos}=="\"" || $buf{$pos}=='\'')
					{
						$ch = $buf{$pos};
						$cnt .= $ch;
						$pos++;

						while ($pos<$sz && $buf{$pos}!=$ch)
						{
							if ($buf{$pos} == "\\")
							{
								$cnt .= $buf{$pos};
								$pos++;

								if ($pos < $sz)
								{
									$cnt .= $buf{$pos};
									$pos++;
								}
							}
							else
							{
								$cnt .= $buf{$pos};
								$pos++;
							}
						}

						if ($pos < $sz)
						{
							$cnt .= $ch;
							$pos++;
						}
					}
					elseif (($pos + $rbra_sz <= $sz) && (substr($buf, $pos, $rbra_sz) == RBRA))
					{
						$pos += $rbra_sz;
						break;
					}
					else
					{
						$cnt .= $buf{$pos};
						$pos++;
					}
				}

				$cnt = trim($cnt);

				if ($cnt != '')
				{
					$stat = '/* internal error */';

					if (strtolower($cnt) == 'end')
					{
						$stat = '}';
					}
					elseif (strtolower($cnt) == 'else')
					{
						$stat = '} else {';
					}
					else
					{
						$op = $cnt{0};

						if ($op=='!' || $op=='=' || $op=='#' || $op=='+' || $op=='^')
						{
							$cnt = trim(substr($cnt, 1));

							if ($cnt != '')
							{
								if ($op == '!') $stat = $cnt;
								elseif ($op == '=') $stat = 'echo(' . $cnt . ')';
								elseif ($op == '#') $stat = 'echo(htmlspecialchars(' . $cnt . '))';
								elseif ($op == '+') $stat = 'echo(urlencode(' . $cnt . '))';
								elseif ($op == '^') $stat = 'echo(jsencode(' . $cnt .'))';
							}
						}
						else
						{
							$i = 1;
							$s = strlen($cnt);

							while ($i<$s && (($cnt{$i}>='A' && $cnt{$i}<='Z') || ($cnt{$i}>='a' && $cnt{$i}<='z') || ($cnt{$i}>='0' && $cnt{$i}<='9')))
							{
								$op .= $cnt{$i};
								$i++;
							}

							$expr = trim(substr($cnt, $i));

							if ($expr != '')
							{
								$op = strtolower($op);

								if ($op=='for' || $op=='foreach' || $op=='if' || $op=='while' || $op=='elseif' || $op=='elsif' || $op=='each')
								{
									if ($expr{0} != '(') $expr = '('.$expr.')';

									if ($op=='elseif' || $op=='elsif') $stat = '} elseif '.$expr.' {';
									elseif ($op=='each') $stat = 'foreach '.$expr.' {';
									else $stat = $op.' '.$expr.' {';
								}
								else $stat = $cnt;
							}
							else $stat = $cnt;
						}
					}

					$res .= '<'.'?'.'php ' . $stat . ' ?'.'>';
				}
			}
		}

		if ($fp = fopen($out, 'wb'))
		{
			fwrite($fp, $res);
			fclose($fp);
			chmod($out, 0777);
		}
	}

	function Render($__ztpl_filename, $__ztpl_vars)
	{
		foreach ($__ztpl_vars as $__ztpl_k=>$_ztpl__v) eval('$'.$__ztpl_k.'=$__ztpl_vars[$__ztpl_k];');
		ob_start();
		require($__ztpl_filename);
		$__ztpl_res = ob_get_contents();
		@ob_end_clean();
		return $__ztpl_res;
	}

	function Process($filename, $vars=array())
	{
		$dir = substr(dirname($filename), strlen(BASE_PATH));
		$rdir = BASE_PATH.'cache/'.$dir;
		if ($dir!='' && !is_dir($rdir)) MakeDirectory($rdir, 0777);

		if (substr($rdir, -1) != '/') $rdir .= '/';
		$rname = $rdir.basename($filename).'.php';
		$mt = filemtime($filename);
		$mk = true;

		if (file_exists($rname))
		{
			$nmt = filemtime($rname);
			if ($nmt >= $mt) return $this->Render($rname, $vars);
		}

		$this->Parse($filename, $rname);
		return $this->Render($rname, $vars);
	}
}

?>