<?php namespace Champion;

use Champion\Exceptions\RuntimeException;

class ClassObject
{
    /** @var string */
    private $className;

    /** @var \ReflectionClass */
    private $reflection;

    /** @var string */
    private $classMethod;

    /**
     * @param string $controllerName
     * @param string $controllerMethod
     */
    public function __construct($controllerName, $controllerMethod = null)
    {
        $this->className = $controllerName;
        $this->reflection = new \ReflectionClass($controllerName);
        $this->classMethod = $controllerMethod;
    }

    /**
     * @return \ReflectionMethod
     */
    private function getConstructor()
    {
        return $this->reflection->getConstructor();
    }

    /**
     * @return array
     */
    private function getConstructorParameters()
    {
        if (!$this->getConstructor()) {
            return array();
        }

        $out = array();
        $parameters = $this->getConstructor()->getParameters();

        /** @var \ReflectionParameter $parameter */
        foreach ($parameters as $parameter) {
            $out[] = $parameter->getClass()->getName();
        }

        return $out;
    }

    /**
     * Run initiated class method.
     */
    public function run()
    {
        $methodToRun = $this->classMethod;

        if (is_null($methodToRun)) {
            throw new RuntimeException("Can not execute object method. Method not provided.");
        }

        $controller = $this->getInstanceWithDependencies();
        $controller->$methodToRun();
    }

    /**
     * @return object
     */
    public function getInstanceWithDependencies()
    {
        $constructParams = array();
        $parameters = $this->getConstructorParameters();

        foreach ($parameters as $className) {
            $class = new self($className);
            $constructParams[] = $class->getInstanceWithDependencies();
        }

        return $this->reflection->newInstanceArgs($constructParams);
    }
}
