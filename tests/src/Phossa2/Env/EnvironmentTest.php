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
        putenv('ROOT_DIR');
        putenv('BIN_DIR');
        putenv('ETC_DIR');
        putenv('CONF_DIR');
        putenv('VAR_DIR');
        putenv('MY_VAR');
        putenv('TMP_DIR');
        putenv('DOC_DIR');
        putenv('MY_DOC');
        putenv('ENV_FILE');
        putenv('SER_VAL');
    }

    /**
     * Normal env loading
     *
     * @covers Phossa2\Env\Environment::load()
     */
    public function testLoad1()
    {
        // set server variable
        $_SERVER['test'] = 'mytest';

        $this->environment->load(__DIR__ . '/test.env');

        // reference resolved
        $this->assertEquals('/user/local', getenv('ROOT_DIR'));
        $this->assertEquals('/user/local/bin', getenv('BIN_DIR'));

        // assign default :=
        $this->assertEquals('/etc', getenv('ETC_DIR'));
        $this->assertEquals('/etc', getenv('CONF_DIR'));

        // source another file
        $this->assertEquals('/new/doc', getenv('MY_DOC'));

        // :- set value
        $this->assertEquals('/var', getenv('MY_VAR'));
        $this->assertEquals(false, getenv('VAR_DIR'));

        // empty
        $this->assertEquals('', getenv('TMP_DIR'));

        // magic ${__FILE__}
        $this->assertEquals('test.env', getenv('ENV_FILE'));

        // php globals $_SERVER[...]
        $this->assertEquals('mytest', getenv('SER_VAL'));

        // clear it
        unset($_SERVER['test']);
    }

    /**
     * Not overload
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

        putenv('BIN_DIR');
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
        $this->environment->overload(__DIR__ . '/test.env');

        $this->assertEquals('/user/local/bin', getenv('BIN_DIR'));

        putenv('BIN_DIR');
    }
}
