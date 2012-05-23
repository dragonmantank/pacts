<?php

class PactTest extends PHPUnit_Framework_Testcase
{
    protected $tester;

    public function setUp()
    {
        $this->tester = new PactTester();
    }

    /**
     * Tests to see if basic Preconditions are being extracted properly
     */
    public function testExtractBasicPreconditions()
    {
        $this->assertTrue($this->tester->hasPrecondition('add'));
        $this->assertEquals(2, count($this->tester->getConditions('pre', 'add')));
    }

    /**
     * Tests to make sure that @pre annotations are properly extracted
     */
    public function testExtractCustomPreconditions()
    {
        $this->assertTrue($this->tester->hasPrecondition('divide'));
        $this->assertEquals(2, count($this->tester->getConditions('pre', 'divide')));
    }

    /**
     * Tests to make sure that basic @param annotations are tested properly
     */
    public function testPassBasicCheck()
    {
        $condition = array(
            'check' => 'basic',
            'type' => 'int',
            'param' => 1,
        );
        $args = array(5, 'test');

        $this->assertTrue($this->tester->basicCheck($condition, $args));
    }

    /**
     * Tests to make sure that basic @param annotations are tested properly when failing
     */
    public function testFailBasicCheck()
    {
        $condition = array(
            'check' => 'basic',
            'type' => 'int',
            'param' => 2,
        );
        $args = array(5, 'test');

        $this->assertFalse($this->tester->basicCheck($condition, $args));
    }
}