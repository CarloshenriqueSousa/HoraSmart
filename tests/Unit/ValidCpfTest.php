<?php

namespace Tests\Unit;

use App\Rules\ValidCpf;
use Tests\TestCase;

class ValidCpfTest extends TestCase
{
    private ValidCpf $rule;
    private array $errors;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule   = new ValidCpf();
        $this->errors = [];
    }

    private function validate(string $cpf): bool
    {
        $this->errors = [];
        $this->rule->validate('cpf', $cpf, function ($message) {
            $this->errors[] = $message;
        });
        return empty($this->errors);
    }

    public function test_valid_cpf_passes()
    {
        // CPF válido gerado pelo algoritmo
        $this->assertTrue($this->validate('529.982.247-25'));
    }

    public function test_invalid_cpf_fails()
    {
        $this->assertFalse($this->validate('529.982.247-00')); // dígitos errados
    }

    public function test_repeated_digits_fail()
    {
        $this->assertFalse($this->validate('111.111.111-11'));
        $this->assertFalse($this->validate('000.000.000-00'));
        $this->assertFalse($this->validate('999.999.999-99'));
    }

    public function test_wrong_length_fails()
    {
        $this->assertFalse($this->validate('123'));
        $this->assertFalse($this->validate('123456789012345'));
    }

    public function test_unformatted_valid_cpf_passes()
    {
        $this->assertTrue($this->validate('52998224725'));
    }
}
