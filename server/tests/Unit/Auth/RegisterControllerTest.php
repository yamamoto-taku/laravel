<?php

namespace Tests\Unit\Auth;

use App\Http\Controllers\Auth\RegisterController;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    protected $target;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->target = new RegisterController();
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function 正常系()
    {
        $method = $this->changeAccessible('validator');

        $input = [
            'name' => 'yamamoto-taku',
            'email' => 'hoge@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        $validator = $method->invoke($this->target, $input);
        $this->assertTrue($validator->passes());
    }

    /**
     * @param array $input
     * @dataProvider validatorProviderForFailure
     * @test
     * @throws \ReflectionException
     */
    public function 異常系(array $input)
    {
        $method = $this->changeAccessible('validator');
        $validator = $method->invoke($this->target, $input);
        $this->assertFalse($validator->passes());
    }

    /**
     * @return array
     */
    public function validatorProviderForFailure()
    {
        $input = [
            'name' => 'あいうえお',
            'email' => 'hoge@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        $null = [
            'name' => null,
            'email' => null,
            'password' => null,
            'password_confirmation' => null,
        ];

        return [
            '必須入力チェック' => [$null],
            '日本語入力ひらがな' => [$input],
        ];
    }


    /**
     * @param $object
     * @return mixed
     * @throws \ReflectionException
     */
    public function changeAccessible($methodName)
    {
        $reflection = new \ReflectionClass($this->target);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }
}
