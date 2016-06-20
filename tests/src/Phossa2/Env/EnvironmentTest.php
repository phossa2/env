<?php
namespace Phossa2\Env;

/**
 * Environment test case.
 */
class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var Environment
     */
    private $environment;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->environment = new Environment();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->environment = null;
        $this->clearEnv();
        parent::tearDown();
    }

    public function clearEnv()
    {
        putenv('BIN_DIR');
        putenv('ROOT_DIR');
        putenv('ETC_DIR');
        putenv('VAR_DIR');
        putenv('MY_VAR');
        putenv('DOC_DIR');
        putenv('ENV_FILE');
    }

    /**
     * Normal env loading
     *
     * @covers Phossa2\Env\Environment::load()
     */
    public function testLoad1()
    {
        // server variable
        $_SERVER['test'] = 'mytest';

        $this->environment->load(__DIR__ . '/test.env');

        // reference resolved
        $this->assertEquals('/user/local/bin', getenv('BIN_DIR'));
        $this->assertEquals('/user/local', getenv('ROOT_DIR'));

        $this->assertEquals('/etc', getenv('ETC_DIR'));

        // empty value
        $this->assertEquals('', getenv('VAR_DIR'));
        $this->assertEquals('/var', getenv('MY_VAR'));

        // unresolved
        $this->assertEquals(false, getenv('DOC_DIR'));

        // magic ${__FILE__}
        $this->assertEquals('test.env', getenv('ENV_FILE'));

        // try $_SERVER[...]
        $this->assertEquals('mytest', getenv('SER_VAL'));

        // clear it
        unset($_SERVER['test']);
    }

    /**
     * Use existing env variables
     *
     * @covers Phossa2\Env\Environment::load()
     */
    public function testLoad2()
    {
        // preset env
        putenv('BIN_DIR=/bin');

        // load env, but BIN_DIR in file will be ignored
        $this->environment->load(__DIR__ . '/test.env');

        $this->assertEquals('/bin', getenv('BIN_DIR'));
    }

    /**
     * Test overload
     *
     * @covers Phossa2\Env\Environment::load()
     */
    public function testLoad3()
    {
        // preset env
        putenv('BIN_DIR=/bin');

        // load env, BIN_DIR will NOT be ignored
        $this->environment->setOverload(true);
        $this->environment->load(__DIR__ . '/test.env');

        $this->assertEquals('/user/local/bin', getenv('BIN_DIR'));
    }

    /**
     * Load multiple env files, resolve cross references
     *
     * @covers Phossa2\Env\Environment::load()
     */
    public function testLoad4()
    {
        // load multiple files
        $this->environment->load(__DIR__ . '/test.env');
        $this->environment->load(__DIR__ . '/test2.env');

        // reference resolved across files
        $this->assertEquals('/home/doc', getenv('DOC_DIR'));

        // clear env
        putenv('MY_DOC');
    }
}
