<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\classes\checkAddProducts;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_that_true_is_true()
    {
        $this->assertTrue(true);
    }

    public function strip_fake()
    {
        $this->exceptException();
    }
}
