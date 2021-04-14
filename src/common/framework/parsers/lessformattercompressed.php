<?php

namespace Rhymix\Framework\Parsers;

class LessFormatterCompressed extends LessFormatterClassic {
	public $disableSingle = true;
	public $open = "{";
	public $selectorSeparator = ",";
	public $assignSeparator = ":";
	public $break = "";
	public $compressColors = true;

	public function indentStr($n = 0) {
		return "";
	}
}
