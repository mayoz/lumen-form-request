<?php

namespace Illuminate\Tests\Foundation;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Container\Container;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\Factory as ValidationFactoryContract;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Factory as ValidationFactory;
use Illuminate\Validation\ValidationException;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class FoundationFormRequestTest extends TestCase
{
    protected $mocks = [];

    protected function tearDown(): void
    {
        m::close();

        $this->mocks = [];
    }

    public function testValidatedMethodReturnsTheValidatedData()
    {
        $request = $this->createRequest(['name' => 'specified', 'with' => 'extras']);

        $request->validateResolved();

        $this->assertEquals(['name' => 'specified'], $request->validated());
    }

    public function testValidatedMethodReturnsTheValidatedDataNestedRules()
    {
        $payload = ['nested' => ['foo' => 'bar', 'baz' => ''], 'array' => [1, 2]];

        $request = $this->createRequest($payload, TestFormRequestNestedStub::class);

        $request->validateResolved();

        $this->assertEquals(['nested' => ['foo' => 'bar'], 'array' => [1, 2]], $request->validated());
    }

    public function testValidatedMethodReturnsTheValidatedDataNestedChildRules()
    {
        $payload = ['nested' => ['foo' => 'bar', 'with' => 'extras']];

        $request = $this->createRequest($payload, TestFormRequestNestedChildStub::class);

        $request->validateResolved();

        $this->assertEquals(['nested' => ['foo' => 'bar']], $request->validated());
    }

    public function testValidatedMethodReturnsTheValidatedDataNestedArrayRules()
    {
        $payload = ['nested' => [['bar' => 'baz', 'with' => 'extras'], ['bar' => 'baz2', 'with' => 'extras']]];

        $request = $this->createRequest($payload, TestFormRequestNestedArrayStub::class);

        $request->validateResolved();

        $this->assertEquals(['nested' => [['bar' => 'baz'], ['bar' => 'baz2']]], $request->validated());
    }

    public function testValidatedMethodNotValidateTwice()
    {
        $payload = ['name' => 'specified', 'with' => 'extras'];

        $request = $this->createRequest($payload, TestFormRequestTwiceStub::class);

        $request->validateResolved();
        $request->validated();

        $this->assertEquals(1, TestFormRequestTwiceStub::$count);
    }

    public function testValidateThrowsWhenValidationFails()
    {
        $this->expectException(ValidationException::class);

        $request = $this->createRequest(['no' => 'name']);

        $request->validateResolved();
    }

    public function testValidateMethodThrowsWhenAuthorizationFails()
    {
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('This action is unauthorized.');

        $this->createRequest([], TestFormRequestForbiddenStub::class)->validateResolved();
    }

    public function testPrepareForValidationRunsBeforeValidation()
    {
        $this->createRequest([], TestFormRequestHooks::class)->validateResolved();
    }

    public function test_after_validation_runs_after_validation()
    {
        $request = $this->createRequest([], TestFormRequestHooks::class);

        $request->validateResolved();

        $this->assertEquals(['name' => 'Adam'], $request->all());
    }

    /**
     * Create a new request of the given type.
     *
     * @param  array  $payload
     * @param  string  $class
     * @return \Illuminate\Foundation\Http\FormRequest
     */
    protected function createRequest($payload = [], $class = TestFormRequestStub::class)
    {
        $container = tap(new Container, function (Container $container) {
            $container->instance(
                ValidationFactoryContract::class,
                $this->createValidationFactory($container)
            );
        });

        $request = $class::create('/', 'GET', $payload);

        return $request->setContainer($container);
    }

    /**
     * Create a new validation factory.
     *
     * @param  \Illuminate\Container\Container  $container
     * @return \Illuminate\Validation\Factory
     */
    protected function createValidationFactory($container)
    {
        $translator = m::mock(Translator::class)->shouldReceive('get')
                       ->zeroOrMoreTimes()->andReturn('error')->getMock();

        return new ValidationFactory($translator, $container);
    }
}

class TestFormRequestStub extends FormRequest
{
    public function rules()
    {
        return ['name' => 'required'];
    }

    public function authorize()
    {
        return true;
    }
}

class TestFormRequestNestedStub extends FormRequest
{
    public function rules()
    {
        return ['nested.foo' => 'required', 'array.*' => 'integer'];
    }

    public function authorize()
    {
        return true;
    }
}

class TestFormRequestNestedChildStub extends FormRequest
{
    public function rules()
    {
        return ['nested.foo' => 'required'];
    }

    public function authorize()
    {
        return true;
    }
}

class TestFormRequestNestedArrayStub extends FormRequest
{
    public function rules()
    {
        return ['nested.*.bar' => 'required'];
    }

    public function authorize()
    {
        return true;
    }
}

class TestFormRequestTwiceStub extends FormRequest
{
    public static $count = 0;

    public function rules()
    {
        return ['name' => 'required'];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            self::$count++;
        });
    }

    public function authorize()
    {
        return true;
    }
}

class TestFormRequestForbiddenStub extends FormRequest
{
    public function authorize()
    {
        return false;
    }
}

class TestFormRequestHooks extends FormRequest
{
    public function rules()
    {
        return ['name' => 'required'];
    }

    public function authorize()
    {
        return true;
    }

    public function prepareForValidation()
    {
        $this->replace(['name' => 'Taylor']);
    }

    public function passedValidation()
    {
        $this->replace(['name' => 'Adam']);
    }
}
