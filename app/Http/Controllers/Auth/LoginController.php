<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoginResource;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Http\Requests\LoginRequest;

class LoginController extends AuthenticatedSessionController
{
    public function store(LoginRequest $request)
    {
        return $this->loginPipeline($request)->then(function ($request) {
            $user = Auth::user();
            $token = $user->createToken('auth_token', ['role:'.$user->role])->plainTextToken;

            return new LoginResource([
                'token' => $token,
                'user' => $user,
            ]);
        });
    }
}
