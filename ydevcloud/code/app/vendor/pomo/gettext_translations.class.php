<?php

class Gettext_Translations extends Translations {
	/**
	 * The gettext implmentation of select_plural_form.
	 *
	 * It lives in this class, because there are more than one descendand, which will use it and
	 * they can't share it effectively.
	 *
	 */
	public function gettext_select_plural_form($count) {
		if (!isset($this->_gettext_select_plural_form) || is_null($this->_gettext_select_plural_form)) {
			$plural_header = $this->get_header('Plural-Forms');
			$this->_gettext_select_plural_form = $this->_make_gettext_select_plural_form($plural_header);
		}
		return call_user_func($this->_gettext_select_plural_form, $count);
	}

	/**
	 * Makes a function, which will return the right translation index, according to the
	 * plural forms header
	 */
	public function _make_gettext_select_plural_form($plural_header) {
		$res = create_function('$count', 'return 1 == $count? 0 : 1;');
		if ($plural_header && (preg_match('/^\s*nplurals\s*=\s*(\d+)\s*;\s+plural\s*=\s*(.+)$/', $plural_header, $matches))) {
			$nplurals = (int)$matches[1];
			$this->_nplurals = $nplurals;
			$plural_expr = trim($this->_parenthesize_plural_exression($matches[2]));
			$plural_expr = str_replace('n', '$n', $plural_expr);
			$func_body = "
				\$index = (int)($plural_expr);
				return (\$index < $nplurals)? \$index : $nplurals - 1;";
			$res = create_function('$n', $func_body);
		}
		return $res;
	}

	/**
	 * Adds parantheses to the inner parts of ternary operators in
	 * plural expressions, because PHP evaluates ternary oerators from left to right
	 * 
	 * @param string $expression the expression without parentheses
	 * @return string the expression with parentheses added
	 */
	private function _parenthesize_plural_exression($expression) {
		$expression .= ';';
		$res = '';
		$depth = 0;
		for ($i = 0; $i < strlen($expression); ++$i) {
			$char = $expression[$i];
			switch ($char) {
				case '?':
					$res .= ' ? (';
					$depth++;
					break;
				case ':':
					$res .= ') : (';
					break;
				case ';':
					$res .= str_repeat(')', $depth) . ';';
					$depth= 0;
					break;
				default:
					$res .= $char;
			}
		}
		return rtrim($res, ';');
	}
	
	public function make_headers($translation) {
		$headers = array();
		// sometimes \ns are used instead of real new lines
		$translation = str_replace('\n', "\n", $translation);
		$lines = explode("\n", $translation);
		foreach($lines as $line) {
			$parts = explode(':', $line, 2);
			if (!isset($parts[1])) continue;
			$headers[trim($parts[0])] = trim($parts[1]);
		}
		return $headers;
	}

	public function set_header($header, $value) {
		parent::set_header($header, $value);
		if ('Plural-Forms' == $header)
			$this->_gettext_select_plural_form = $this->_make_gettext_select_plural_form($value);
	}

	
}

?>
