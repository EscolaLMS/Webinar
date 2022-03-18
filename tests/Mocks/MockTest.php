<?php

namespace EscolaLms\Webinar\Tests\Mocks;

use Illuminate\Foundation\Testing\WithFaker;

abstract class MockTest
{
    use WithFaker;

    public function __construct()
    {
        $this->faker = $this->makeFaker();
    }
}
