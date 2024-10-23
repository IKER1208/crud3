<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Token;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
class AuthController extends Controller
{
    public function login(Request $request)
{
    $validate = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if ($validate->fails()) {
        return response()->json([
            'msg' => "Introduzca todos los datos requeridos",
            'error' => $validate->errors()
        ], 400);
    }

    $response = Http::post('https://1826-187-190-56-49.ngrok-free.app/login', [
        'email' => $request->email,
        'password' => $request->password,
    ]);

    if ($response->status() == 401) {
        return response()->json([
            'msg' => "Credenciales incorrectas"
        ], 401);
    }

    if ($response->failed()) {
        return response()->json([
            'msg' => "Error al conectarse a la otra API",
            'error' => $response->body()
        ], 500);
    }

    $externalToken = $response->json()['token'];

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'msg' => "Credenciales incorrectas en el sistema local"
        ], 401);
    }

    $localToken = $user->createToken('token')->plainTextToken;

    $token = new Token();
        
    $token->token_1 = "Bearer {$localToken}";
    $token->token_2 = $externalToken;
    $token->save();


    return response()->json([
        'msg' => "Sesión iniciada",
        'token_iker' => $localToken 
    ], 200);
}

    public function register (Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',
        ]);

        if ($validate->fails()) 
        {
            return response()->json([
                'msg' => "Introduzca todos los datos requeridos",
                'error' => $validate->errors()
            ], 400);
        }

        $register = Http::post('https://1826-187-190-56-49.ngrok-free.app/register', [
            'full_name' => $request->name,
            'email' => $request->email,
            'password' => $request->password
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        
        return response()->json([
            'msg' => "Usuario creado",
            'hola'=>$register
        ], 200);
    }

}
