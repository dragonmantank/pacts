<?php
/**
 * PHP Pacts
 *
 * This file contains the code for the Pact trait, which can be added to classes
 * that want to integrate programming contracts into a class.
 *
 * @author Chris Tankersley <chris@ctankersley.com>
 * @version 0.1
 * @package Pacts
 */

namespace Pacts;

/**
 * Provides detecting and calling programming contracts on methods
 * This trait injects functions into a class that will allow it to easily set up
 * and start using contracts. The end-user class will just need to implement the
 * custom condition functions as needed.
 */
trait Pact
{
    /**
     * Conditions that are discovered on the object
     * @var array
     */
    protected $conditions = array('pre' => array(), 'post' => array());

    /**
     * Whether or not the class has been parsed for conditions
     * @var bool
     */
    protected $parsed = false;

    /**
     * Basic types that can be automatically checked for preconditions
     * @var array
     */
    protected $base_types = array('int', 'float', 'bool', 'string', 'mixed', 'array', 'long');

    /**
     * Current counter for parameters when detecting preconditions
     * @var int
     */
    protected $param_count = 1;

    /**
     * Magic Method to control workflow for contract programming
     * Instead of calling public functions, this magic method will intercept the
     * calls to methods, check and run preconditions, run the actual method,
     * and then check the postconditions.
     *
     * @param string $method Method to call
     * @param array $args Arguments for the method we're calling
     */
    public function __call($method, $args)
    {
        if($this->hasPreconditions($method)) {
            foreach($this->conditions['pre'] as $condition) {
                if('basic' == $condition['check']) {
                    $this->basicCheck($condition, $method, $args);
                }
            }
        }
    }

    /**
     * Performs a basic is_* check against a parameter
     * @param annotations generate basic contracts, and this will check those
     * contracts against PHP's built-in scalar checking
     *
     * @param array $condition Condition information
     * @param array $args Arguments passed to functions
     * @return bool
     */
    public function basicCheck($condition, $args)
    {
        $functionName = 'is_'.$condition['type'];
        return $functionName($args[$condition['param'] - 1]);
    }

    /**
     * Returns the conditions for a method name
     *
     * @param string $type Type of condition, pre or post
     * @param string $methodName Method to check
     * @return array
     */
    public function getConditions($type, $methodName)
    {
        return $this->conditions[$type][$methodName];
    }

    /**
     * Provides a quick check to see if a method has any preconditions
     *
     * @param string $methodName Method to check against
     * @return bool
     */
    public function hasPrecondition($methodName)
    {
        $this->param_count = 1;

        $class = new \ReflectionClass(get_class($this));
        $method = $class->getMethod($methodName);
        $docblock = $method->getDocComment();

        foreach(explode("\n", $docblock) as $line) {
            $this->extractPrecondition($line, $methodName);
        }

        return (bool)count($this->conditions['pre'][$methodName]);
    }

    /**
     * Checks a docblock line to see if it contains any preconditions
     *
     * @param string $line Line of text to check
     * @param string $methodName Method this line came from
     */
    public function extractPrecondition($line, $methodName)
    {
        if(preg_match('|\* \@param ([a-zA-Z]*) ([\$a-zA-Z0-9_]*)|', $line, $matches)) {
            if(in_array($matches[1], $this->base_types)) {
                $check = 'basic';
            } else {
                $check = 'class';
            }

            $this->conditions['pre'][$methodName][] = array(
                'check' => $check,
                'type' => $matches[1],
                'param' => $this->param_count,
            );
            $this->param_count++;
        }

        if(preg_match('|\* \@pre ([a-zA-Z_]*) ([0-9]*)|', $line, $matches)) {
            $this->conditions['pre'][$methodName][] = array(
                'check' => 'custom',
                'type' => $matches[1],
                'param' => $matches[2],
            );
        }
    }
}