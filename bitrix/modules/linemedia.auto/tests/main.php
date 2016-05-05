<?php

/**
* @link http://phpunit.de/manual/current/en/selenium.html
*/
class TestMain extends PHPUnit_Extensions_Selenium2TestCase
{
	/*
	protected $captureScreenshotOnFailure = TRUE;
    protected $screenshotPath = '/var/www/localhost/htdocs/screenshots';
    protected $screenshotUrl = 'http://localhost/screenshots';
    
    * http://80.240.129.250:4444/
    */
    
    public static $browsers = array(
      array(
        'name'    => 'Firefox on Linux',
        'browser' => '*firefox',
        'host'    => '80.240.129.250',
        'port'    => 4444,
        'timeout' => 30000,
      ),
    );
    
    public function setUp()
    {
        $this->setBrowserUrl('http://test.auto-expert.info/');
    }
    
    public function testMain()
    {   
        $this->open('http://test.auto-expert.info/');
        $this->assertEquals('Example WWW Page', $this->title());
    }
}
