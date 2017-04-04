<?php
namespace Test\PhpFlo\Builder;

use PhpFlo\Builder\ComponentDiFinder;
use PhpFlo\Common\ComponentInterface;
use PhpFlo\Exception\InvalidDefinitionException;
use Psr\Container\ContainerInterface;

class ComponentDiFinderTest extends \PHPUnit_Framework_TestCase
{

    public function testInstance()
    {
        $diFinder = new ComponentDiFinder(
            $this->stub(ContainerInterface::class)
        );

        $this->assertInstanceOf(ComponentDiFinder::class, $diFinder);
    }

    public function testFindComponentInDi()
    {
        $diFinder = new ComponentDiFinder(
            $this->stub(
                ContainerInterface::class,
                [
                    'get' => $this->stub(ComponentInterface::class, [], 'SomeComponent'),
                    'has' => false,
                ]
            )
        );

        $component = $diFinder->build('SomeComponent');
        $this->assertInstanceOf('SomeComponent', $component);
    }

    public function testInvalidComponent()
    {
        $this->expectException(InvalidDefinitionException::class);
        $this->expectExceptionMessage('Component SomeInvalidComponent doesn\'t appear to be a valid PhpFlo component');

        $diFinder = new ComponentDiFinder(
            $this->stub(
                ContainerInterface::class,
                [
                    'get' => $this->stub('SomeInvalidComponent'),
                    'has' => false,
                ]
            )
        );

        $diFinder->build('SomeInvalidComponent');
    }

    /**
     * Will create a stub with several methods and defined return values.
     * definition:
     * [
     *   'myMethod' => 'somevalue',
     *   'myOtherMethod' => $callback,
     *   'anotherMethod' => function ($x) use ($y) {},
     * ]
     *
     * @param string $class
     * @param array $methods
     * @param string $className classname for mock object
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function stub($class, array $methods = [], $className = '')
    {
        $builder = $this->getMockBuilder($class)
            ->disableOriginalConstructor();

        if (!empty($methods)) {
            $builder->setMethods(array_keys($methods));
        }

        if ('' !== $className) {
            $builder->setMockClassName($className);
        }

        $stub = $builder->getMock();
        foreach ($methods as $method => $value) {
            if (is_callable($value)) {
                $stub->expects($this->any())->method($method)->willReturnCallback($value);
            } else {
                $stub->expects($this->any())->method($method)->willReturn($value);
            }
        }

        return $stub;
    }
}
