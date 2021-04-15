<?php

class FrontEndFileHandlerTest extends \Codeception\TestCase\Test
{
	private $reservedCSS;
	private $reservedJS;
	private function _filemtime($file)
	{
		return '?' . date('YmdHis', filemtime(_XE_PATH_ . $file));
	}

	public function _before()
	{
		$this->reservedCSS = HTMLDisplayHandler::$reservedCSS;
		$this->reservedJS = HTMLDisplayHandler::$reservedJS;
		HTMLDisplayHandler::$reservedCSS = '/xxx$/';
		HTMLDisplayHandler::$reservedJS = '/xxx$/';
		FrontEndFileHandler::$minify = 'none';
		FrontEndFileHandler::$concat = 'none';
	}
	
	public function _after()
	{
		HTMLDisplayHandler::$reservedCSS = $this->reservedCSS;
		HTMLDisplayHandler::$reservedJS = $this->reservedJS;
	}
	
	public function _failed()
	{
		HTMLDisplayHandler::$reservedCSS = $this->reservedCSS;
		HTMLDisplayHandler::$reservedJS = $this->reservedJS;
	}
	
	public function testJsHead()
	{
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('./common/js/js_app.js', 'head'));
		$handler->loadFile(array('./common/js/common.js', 'body'));
		$handler->loadFile(array('./common/js/common.js', 'head'));
		$handler->loadFile(array('./common/js/xml_js_filter.js', 'body'));
		$expected[] = array('file' => '/rhymix/common/js/js_app.js' . $this->_filemtime('common/js/js_app.js'), 'targetie' => null);
		$expected[] = array('file' => '/rhymix/common/js/common.js' . $this->_filemtime('common/js/common.js'), 'targetie' => null);
		$this->assertEquals($expected, $handler->getJsFileList());
	}
	
	public function testJsBody()
	{
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('./common/js/xml_handler.js', 'body'));
		$handler->loadFile(array('./common/js/xml_js_filter.js', 'head'));
		$expected[] = array('file' => '/rhymix/common/js/xml_handler.js' . $this->_filemtime('common/js/xml_handler.js'), 'targetie' => null);
		$this->assertEquals($expected, $handler->getJsFileList('body'));
	}
	
	public function testCssLess()
	{
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('./public/common/css/rhymix.less'));
		$result = $handler->getCssFileList(true);
		$this->assertRegexp('/\.rhymix\.less\.css\?\d+$/', $result[0]['file']);
		$this->assertEquals('all', $result[0]['media']);
		$this->assertEmpty($result[0]['targetie']);
	}

	public function testDuplicateOrder()
	{
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('./common/js/js_app.js', 'head', '', -100000));
		$handler->loadFile(array('./common/js/common.js', 'head', '', -100000));
		$handler->loadFile(array('./common/js/xml_handler.js', 'head', '', -100000));
		$handler->loadFile(array('./common/js/xml_js_filter.js', 'head', '', -100000));
		$handler->loadFile(array('./common/js/js_app.js', 'head', '', -100000));
		$handler->loadFile(array('./common/js/common.js', 'head', '', -100000));
		$handler->loadFile(array('./common/js/xml_handler.js', 'head', '', -100000));
		$handler->loadFile(array('./common/js/xml_js_filter.js', 'head', '', -100000));
		$expected[] = array('file' => '/rhymix/common/js/js_app.js' . $this->_filemtime('common/js/js_app.js'), 'targetie' => null);
		$expected[] = array('file' => '/rhymix/common/js/common.js' . $this->_filemtime('common/js/common.js'), 'targetie' => null);
		$expected[] = array('file' => '/rhymix/common/js/xml_handler.js' . $this->_filemtime('common/js/xml_handler.js'), 'targetie' => null);
		$expected[] = array('file' => '/rhymix/common/js/xml_js_filter.js' . $this->_filemtime('common/js/xml_js_filter.js'), 'targetie' => null);
		$this->assertEquals($expected, $handler->getJsFileList());
	}
	
	public function testRedefineOrder()
	{
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('./common/js/xml_handler.js', 'head', '', 1));
		$handler->loadFile(array('./common/js/js_app.js', 'head', '', -100000));
		$handler->loadFile(array('./common/js/common.js', 'head', '', -100000));
		$handler->loadFile(array('./common/js/xml_js_filter.js', 'head', '', -100000));
		$expected[] = array('file' => '/rhymix/common/js/js_app.js' . $this->_filemtime('common/js/js_app.js'), 'targetie' => null);
		$expected[] = array('file' => '/rhymix/common/js/common.js' . $this->_filemtime('common/js/common.js'), 'targetie' => null);
		$expected[] = array('file' => '/rhymix/common/js/xml_js_filter.js' . $this->_filemtime('common/js/xml_js_filter.js'), 'targetie' => null);
		$expected[] = array('file' => '/rhymix/common/js/xml_handler.js' . $this->_filemtime('common/js/xml_handler.js'), 'targetie' => null);
		$this->assertEquals($expected, $handler->getJsFileList());
	}
	
	public function testUnload()
	{
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('./common/js/js_app.js', 'head', '', -100000));
		$handler->loadFile(array('./common/js/common.js', 'head', '', -100000));
		$handler->loadFile(array('./common/js/xml_handler.js', 'head', '', -100000));
		$handler->loadFile(array('./common/js/xml_js_filter.js', 'head', '', -100000));
		$handler->unloadFile('./common/js/js_app.js', '', 'all');
		$expected[] = array('file' => '/rhymix/common/js/common.js' . $this->_filemtime('common/js/common.js'), 'targetie' => null);
		$expected[] = array('file' => '/rhymix/common/js/xml_handler.js' . $this->_filemtime('common/js/xml_handler.js'), 'targetie' => null);
		$expected[] = array('file' => '/rhymix/common/js/xml_js_filter.js' . $this->_filemtime('common/js/xml_js_filter.js'), 'targetie' => null);
		$this->assertEquals($expected, $handler->getJsFileList());
	}

	public function testExternalFile1()
	{
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('http://external.host/js/script.js'));
		$handler->loadFile(array('https://external.host/js/script.js'));
		$handler->loadFile(array('//external.host/js/script1.js'));
		$handler->loadFile(array('///external.host/js/script2.js'));

		$expected[] = array('file' => 'http://external.host/js/script.js', 'targetie' => null);
		$expected[] = array('file' => 'https://external.host/js/script.js', 'targetie' => null);
		$expected[] = array('file' => '//external.host/js/script1.js', 'targetie' => null);
		$expected[] = array('file' => '//external.host/js/script2.js', 'targetie' => null);
		$this->assertEquals($expected, $handler->getJsFileList());
	}
	
	public function testExternalFile2()
	{
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('//external.host/js/script.js'));
		$handler->loadFile(array('///external.host/js/script.js'));

		$expected[] = array('file' => '//external.host/js/script.js', 'targetie' => null);
		$this->assertEquals($expected, $handler->getJsFileList());
	}
	
	public function testExternalFile3()
	{
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('http://external.host/css/style1.css'));
		$handler->loadFile(array('https://external.host/css/style2.css'));
		$handler->loadFile(array('https://external.host/css/style3.css?foo=bar&t=123'));

		$expected[] = array('file' => 'http://external.host/css/style1.css', 'media'=>'all', 'targetie' => null);
		$expected[] = array('file' => 'https://external.host/css/style2.css', 'media'=>'all', 'targetie' => null);
		$expected[] = array('file' => 'https://external.host/css/style3.css?foo=bar&t=123', 'media'=>'all', 'targetie' => null);
		$this->assertEquals($expected, $handler->getCssFileList());
	}
	
	public function testExternalFile4()
	{
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('//external.host/css/style.css'));
		$handler->loadFile(array('///external.host/css2/style2.css'));
		$handler->loadFile(array('//external.host/css/style3.css?foo=bar&t=123'));

		$expected[] = array('file' => '//external.host/css/style.css', 'media'=>'all', 'targetie' => null);
		$expected[] = array('file' => '//external.host/css2/style2.css', 'media'=>'all', 'targetie' => null);
		$expected[] = array('file' => '//external.host/css/style3.css?foo=bar&t=123', 'media'=>'all', 'targetie' => null);
		$this->assertEquals($expected, $handler->getCssFileList());
	}

	public function testPathConversion()
	{
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('./common/xeicon/xeicon.min.css'));
		$result = $handler->getCssFileList();
		$this->assertEquals('/rhymix/common/css/xeicon/xeicon.min.css' . $this->_filemtime('common/css/xeicon/xeicon.min.css'), $result[0]['file']);
		$this->assertEquals('all', $result[0]['media']);
		$this->assertEmpty($result[0]['targetie']);
	}

	public function testTargetie1()
	{
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('./common/js/js_app.js', 'head', 'ie6'));
		$handler->loadFile(array('./common/js/js_app.js', 'head', 'ie7'));
		$handler->loadFile(array('./common/js/js_app.js', 'head', 'ie8'));
		$expected[] = array('file' => '/rhymix/common/js/js_app.js' . $this->_filemtime('common/js/js_app.js'), 'targetie' => 'ie6');
		$expected[] = array('file' => '/rhymix/common/js/js_app.js' . $this->_filemtime('common/js/js_app.js'), 'targetie' => 'ie7');
		$expected[] = array('file' => '/rhymix/common/js/js_app.js' . $this->_filemtime('common/js/js_app.js'), 'targetie' => 'ie8');
		$this->assertEquals($expected, $handler->getJsFileList());
	}

	public function testTargetie2()
	{
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('./common/css/common.css', null, 'ie6'));
		$handler->loadFile(array('./common/css/common.css', null, 'ie7'));
		$handler->loadFile(array('./common/css/common.css', null, 'ie8'));

		$expected[] = array('file' => '/rhymix/common/css/common.css', 'media' => 'all', 'targetie' => 'ie6');
		$expected[] = array('file' => '/rhymix/common/css/common.css', 'media' => 'all', 'targetie' => 'ie7');
		$expected[] = array('file' => '/rhymix/common/css/common.css', 'media' => 'all', 'targetie' => 'ie8');
		$this->assertEquals($expected, $handler->getCssFileList());
	}
	
	public function testMedia()
	{
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('./common/css/common.css', 'all'));
		$handler->loadFile(array('./common/css/common.css', 'screen'));
		$handler->loadFile(array('./common/css/common.css', 'handled'));

		$expected[] = array('file' => '/rhymix/common/css/common.css', 'media'=>'all', 'targetie' => null);
		$expected[] = array('file' => '/rhymix/common/css/common.css','media'=>'screen',  'targetie' => null);
		$expected[] = array('file' => '/rhymix/common/css/common.css', 'media'=>'handled', 'targetie' => null);
		$this->assertEquals($expected, $handler->getCssFileList());
	}

	public function testMinify()
	{
		FrontEndFileHandler::$minify = 'all';
		
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('./public/common/css/rhymix.less'));
		$result = $handler->getCssFileList(true);
		$this->assertRegexp('/\.rhymix\.less\.min\.css\b/', $result[0]['file']);
		$this->assertEquals('all', $result[0]['media']);
		$this->assertEmpty($result[0]['targetie']);
		
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('./public/common/js/common.js', 'head'));
		$result = $handler->getJsFileList('head', true);
		$this->assertRegexp('/minified\/common\.js\.common\.min\.js\?\d+$/', $result[0]['file']);
		$this->assertEmpty($result[0]['targetie']);
		
		FrontEndFileHandler::$minify = 'none';
	}
	
	public function testConcat()
	{
		FrontEndFileHandler::$concat = 'css';
		
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('./public/common/css/rhymix.less'));
		$handler->loadFile(array('./public/common/css/bootstrap-responsive.css'));
		$handler->loadFile(array('http://external.host/style.css'));
		$handler->loadFile(array('.//public/common/css/bootstrap.css', null, 'IE'));
		$handler->loadFile(array('./tests/_data/formatter/concat.source1.css'));
		$handler->loadFile(array('./tests/_data/formatter/concat.source2.css'));
		$handler->loadFile(array('./tests/_data/formatter/concat.target1.css'));
		$handler->loadFile(array('./tests/_data/formatter/concat.target2.css'));
		$result = $handler->getCssFileList(true);
		$this->assertEquals(4, count($result));
		$this->assertRegexp('/combined\/[0-9a-f]+\.css\?\d+$/', $result[0]['file']);
		$this->assertEquals('/rhymix/common/css/bootstrap.css' . $this->_filemtime('common/css/bootstrap.css'), $result[1]['file']);
		$this->assertEquals('IE', $result[1]['targetie']);
		$this->assertEquals('http://external.host/style.css', $result[2]['file']);
		$this->assertRegexp('/combined\/[0-9a-f]+\.css\?\d+$/', $result[3]['file']);
		
		FrontEndFileHandler::$concat = 'js';
		
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('./common/js/common.js', 'head'));
		$handler->loadFile(array('./common/js/debug.js', 'head'));
		$handler->loadFile(array('./common/js/html5.js', 'head'));
		$handler->loadFile(array('///external.host/js/script.js'));
		$handler->loadFile(array('./tests/_data/formatter/concat.source1.js', 'head', 'lt IE 8'));
		$handler->loadFile(array('./tests/_data/formatter/concat.source2.js', 'head', 'gt IE 7'));
		$handler->loadFile(array('./tests/_data/formatter/concat.target1.js'));
		$handler->loadFile(array('./tests/_data/formatter/concat.target2.js'));
		$result = $handler->getJsFileList('head', true);
		$this->assertEquals(3, count($result));
		$this->assertRegexp('/combined\/[0-9a-f]+\.js\?\d+$/', $result[0]['file']);
		$this->assertEquals('//external.host/js/script.js', $result[1]['file']);
		$this->assertRegexp('/combined\/[0-9a-f]+\.js\?\d+$/', $result[2]['file']);
		
		FrontEndFileHandler::$concat = 'none';
	}
	
	public function testBlockedScripts()
	{
		HTMLDisplayHandler::$reservedCSS = $this->reservedCSS;
		HTMLDisplayHandler::$reservedJS = $this->reservedJS;
		
		$handler = new FrontEndFileHandler();
		$handler->loadFile(array('./common/css/xe.min.css'));
		$handler->loadFile(array('./common/js/common.js'));
		$handler->loadFile(array('./common/js/xe.js'));
		$handler->loadFile(array('./common/js/xe.min.js'));
		$handler->loadFile(array('./common/js/xml2json.js'));
		$handler->loadFile(array('./common/js/jquery.js'));
		$handler->loadFile(array('./common/js/jquery-1.x.min.js'));
		$handler->loadFile(array('./common/js/jquery-2.0.0.js'));
		$handler->loadFile(array('./common/js/jQuery.min.js'));
		$handler->loadFile(array('http://code.jquery.com/jquery-latest.js'));
		$result = $handler->getCssFileList();
		$this->assertEquals(0, count($result));
		$result = $handler->getJsFileList();
		$this->assertEquals(1, count($result));
		$this->assertEquals('/rhymix/common/js/xml2json.js' . $this->_filemtime('common/js/xml2json.js'), $result[0]['file']);
	}
}
