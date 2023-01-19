<?php

namespace Wame\LaravelAuth\Http\Controllers\Traits;

use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Wame\ApiResponse\Helpers\ApiResponse;
use Wame\LaravelAuth\Http\Resources\V1\BaseUserResource;
use Wame\LaravelAuth\Notifications\UserRegisteredNotification;

trait HasRegistration
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function register(Request $request): JsonResponse
    {
        // Checks if users can log in
        if (!config('wame-auth.register.enabled')) {
            return ApiResponse::code('3.1.1', $this->codePrefix)->response(403);
        }

        // Validate request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => config('wame-auth.register.password_rules')
        ]);

        if ($validator->fails()) {
            return ApiResponse::errors($validator->messages()->toArray())
                ->code('1.1.1', $this->codePrefix)
                ->response(400);
        }

        // Create new user
        /** @var User $user */
        $user = $this->newUser($request);

        // If email verification is enabled
        if (config('wame-auth.register.email_verification')) {
            $user->notify(new UserRegisteredNotification());
        }

        // Try to authenticate user with OAuth2
        $passport = $this->authUserWithOAuth2($request->email, $request->password);
        $passportValidation = $this->checkIfPassportHasError($passport);
        if (!empty($passportValidation)) return ApiResponse::code(...$passportValidation[0])->response($passportValidation[1]);

        $data['user'] = new BaseUserResource($user);
        $data['auth'] = $passport;

        return ApiResponse::data($data)->code('1.1.2', $this->codePrefix)->response(201);
    }


    /**
     * @param Request $request
     * @return mixed
     */
    private function newUser(Request $request): mixed
    {
        return User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => null
        ]);
    }
}
