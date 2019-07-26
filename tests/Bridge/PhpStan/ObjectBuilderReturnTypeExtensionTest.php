<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\RequestHandlers\Bridge\PhpStan;

use LoyaltyCorp\RequestHandlers\Bridge\PhpStan\ObjectBuilderReturnTypeExtension;
use LoyaltyCorp\RequestHandlers\Builder\Interfaces\ObjectBuilderInterface;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Reflection\Dummy\DummyMethodReflection;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use Tests\LoyaltyCorp\RequestHandlers\Stubs\Vendor\PhpStan\ScopeStub;
use Tests\LoyaltyCorp\RequestHandlers\TestCase;

/**
 * @covers \LoyaltyCorp\RequestHandlers\Bridge\PhpStan\ObjectBuilderReturnTypeExtension
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) required to test
 */
class ObjectBuilderReturnTypeExtensionTest extends TestCase
{
    /**
     * Tests what class the extension supports.
     *
     * @return void
     */
    public function testSupportedClass(): void
    {
        $extension = new ObjectBuilderReturnTypeExtension();

        static::assertSame(ObjectBuilderInterface::class, $extension->getClass());
    }

    /**
     * Tests extension supports build method
     *
     * @return void
     */
    public function testSupportedMethodBuild(): void
    {
        $extension = new ObjectBuilderReturnTypeExtension();

        $method = new DummyMethodReflection('build');

        static::assertTrue($extension->isMethodSupported($method));
    }

    /**
     * Tests extension supports buildWithContext method
     *
     * @return void
     */
    public function testSupportedMethodBuildWithContext(): void
    {
        $extension = new ObjectBuilderReturnTypeExtension();

        $method = new DummyMethodReflection('buildWithContext');

        static::assertTrue($extension->isMethodSupported($method));
    }

    /**
     * Tests that the getTypeFromMethodCall method returns the expected ObjectType
     * from the first argument.
     *
     * @return void
     *
     * @throws \PHPStan\Analyser\UndefinedVariableException
     * @throws \PHPStan\Broker\ClassAutoloadingException
     * @throws \PHPStan\Broker\ClassNotFoundException
     * @throws \PHPStan\Broker\FunctionNotFoundException
     * @throws \PHPStan\Reflection\MissingConstantFromReflectionException
     * @throws \PHPStan\Reflection\MissingMethodFromReflectionException
     * @throws \PHPStan\Reflection\MissingPropertyFromReflectionException
     * @throws \PHPStan\ShouldNotHappenException
     */
    public function testGetTypeFromMethodCall(): void
    {
        $extension = new ObjectBuilderReturnTypeExtension();

        $method = new DummyMethodReflection('build');
        $call = new MethodCall(
            new MethodCall(
                new Variable('this'),
                'getObjectBuilder'
            ),
            'buildWithContext',
            [
                new Arg(
                    new ClassConstFetch(
                        new FullyQualified('stdClass'),
                        'name'
                    )
                )
            ]
        );
        $type = new ConstantStringType('stdClass');
        $scope = new ScopeStub($type);

        $expected = new ObjectType('stdClass');

        $result = $extension->getTypeFromMethodCall(
            $method,
            $call,
            $scope
        );

        static::assertEquals($expected, $result);
    }

    /**
     * Tests that the getTypeFromMethodCall method returns mixed when the first
     * argument is untyped.
     *
     * @return void
     *
     * @throws \PHPStan\Analyser\UndefinedVariableException
     * @throws \PHPStan\Broker\ClassAutoloadingException
     * @throws \PHPStan\Broker\ClassNotFoundException
     * @throws \PHPStan\Broker\FunctionNotFoundException
     * @throws \PHPStan\Reflection\MissingConstantFromReflectionException
     * @throws \PHPStan\Reflection\MissingMethodFromReflectionException
     * @throws \PHPStan\Reflection\MissingPropertyFromReflectionException
     * @throws \PHPStan\ShouldNotHappenException
     */
    public function testGetTypeFromMethodCallUntypedParam(): void
    {
        $extension = new ObjectBuilderReturnTypeExtension();

        $method = new DummyMethodReflection('build');
        $call = new MethodCall(
            new MethodCall(
                new Variable('this'),
                'getObjectBuilder'
            ),
            'buildWithContext',
            [
                new Arg(
                    new Variable('variable')
                )
            ]
        );
        $type = new ConstantIntegerType(5);
        $scope = new ScopeStub($type);

        $expected = new MixedType();

        $result = $extension->getTypeFromMethodCall(
            $method,
            $call,
            $scope
        );

        static::assertEquals($expected, $result);
    }
}