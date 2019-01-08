<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Creates a stub for a test to be written.
     * Marks it as incomplete with a generic message.
     *
     * @param string $message - an optional message to be supplied. A default message is used if not specified.
     */
    public function stub($message = 'Stub for test that needs to be written.')
    {
        $this->markTestIncomplete($message);
    }
}
