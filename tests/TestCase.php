<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use JetBrains\PhpStorm\ArrayShape;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    #[ArrayShape(['Authorization' => "string"])] public function getAuthHeader(User $user): array
    {
        $token = $user->createToken('api-test')->plainTextToken;
        return ['Authorization' => sprintf('Bearer %s', $token)];
    }

    public function getDefaultUser(): User
    {
        return User::where('email', 'john@example.com')->first();
    }
}
