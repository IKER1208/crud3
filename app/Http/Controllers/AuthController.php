<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
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

    $response = Http::post('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/login', [
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

    return response()->json([
        'msg' => "Sesión iniciada",
        'token_externo' => $externalToken, 
        'token' => $localToken 
    ], 200);
}

    public function register (Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validate->fails()) 
        {
            return response()->json([
                'msg' => "Introduzca todos los datos requeridos",
                'error' => $validate->errors()
            ], 400);
        }

        $register = Http::post('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/register', [
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
            'msg' => "Usuario creado"
        ], 200);
    }

}