<?php

namespace Rhymix\Framework\Parsers;

class LessFormatterLessJs extends LessFormatterClassic {
	public $disableSingle = true;
	public $breakSelectors = true;
	public $assignSeparator = ": ";
	public $selectorSeparator = ",";
}
