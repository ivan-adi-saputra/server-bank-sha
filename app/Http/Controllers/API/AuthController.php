<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Melihovv\Base64ImageDecoder\Base64ImageDecoder;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
            'pin' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        $user = User::where('email', $request->email)->exists();
        if ($user) {
            return response()->json(['message' => 'Email already exists'], 400);
        }

        DB::beginTransaction();
        try {
            $profilePicture = null;
            $ktp = null;

            if ($request->profile_picture) {
                $profilePicture = $this->uploadBase64Image($request->profile_picture);
            }

            if ($request->ktp) {
                $ktp = $this->uploadBase64Image($request->ktp);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' =>  bcrypt($request->password),
                'profile_picture' => $profilePicture,
                'ktp' => $ktp,
                'verified' => ($ktp) ? true : false,
            ]);

            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'pin' => $request->pin,
                'card_number' => $this->generateCardNumber(16),
            ]);
            DB::commit();
            $token = JWTAuth::attempt(['email' => $request->email, 'password' => $request->password]);

            $res = getUser($request->email);
            $res->token = $token;
            $res->token_expires_in = 60 * 60;
            $res->token_type = 'bearer';

            return response()->json($res);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        $validator = Validator::make($credentials, [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        try {
            $token = JWTAuth::attempt($credentials);
            if (!$token) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $res = getUser($request->email);
            $res->token = $token;
            $res->token_expires_in = 60 * 60;
            $res->token_type = 'bearer';

            return response()->json($res);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    private function generateCardNumber($length)
    {
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }

        $cardNumber = Wallet::where('card_number', $result)->exists();

        if ($cardNumber) {
            return $this->generateCardNumber($length);
        }

        return $result;
    }

    private function uploadBase64Image($base64Image)
    {
        $decoder = new Base64ImageDecoder($base64Image, $allowedFormats = ['jpeg', 'png', 'jpg']);

        $decodedContent = $decoder->getDecodedContent();
        $format = $decoder->getFormat();
        $image = Str::random(10) . '.' . $format;
        Storage::disk('public')->put($image, $decodedContent);

        return $image;
    }

    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Log out success']);
    }
}
