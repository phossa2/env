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
    public function testLoad()
    {
        $this->environment->load(__DIR__ . '/test.env');

        $this->assertEquals('/user/local/bin', getenv('BIN_DIR'));
        $this->assertEquals('/user/local', getenv('ROOT_DIR'));
        $this->assertEquals('/etc', getenv('ETC_DIR'));
        $this->assertEquals('', getenv('VAR_DIR'));
        $this->assertEquals('/var', getenv('MY_VAR'));
        $this->assertEquals(false, getenv('DOC_DIR'));
        $this->assertEquals('test.env', getenv('ENV_FILE'));
    }
}
