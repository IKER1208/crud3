<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Sucursal;
use App\Models\Token;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Validator;

class SucursalController extends Controller
{
    public function index(Request $request)
    {
        $token_iker = $request->header('Authorization');

        // Buscar el token correspondiente
        $token_noe = Token::where('token_1', $token_iker)->first();
        $token_noe = $token_noe->token_2;

        try {
            // Obtener todos los sucursales de la base de datos local
            $sucursales = Sucursal::all();

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://1826-187-190-56-49.ngrok-free.app/generos');

            // Devolver la respuesta con las sucursales y los datos de la API externa
            return response()->json([
                'msg' => 'Listado de sucursales',
                'sucursales' => $sucursales,
                'generos' => $dataResponse->json()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa'
            ], 500);
        }
    }

    public function show($id, Request $request)
    {
        try {
            $sucursal = Sucursal::find($id);

            $token_iker = $request->header('Authorization');

            // Buscar el token correspondiente
            $token_noe = Token::where('token_1', $token_iker)->first();
            $token_noe = $token_noe->token_2;

            if (!$sucursal) {
                return response()->json([
                    'msg' => 'No se encontró la sucursal'
                ], 404);
            }

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://1826-187-190-56-49.ngrok-free.app/generos/' . $id);

            return response()->json([
                'msg' => 'Sucursal encontrada',
                'sucursal' => $sucursal,
                'data' => $dataResponse->json()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $token_iker = $request->header('Authorization');

            // Buscar el token más reciente correspondiente
            $token_noe_record = Token::where('token_1', $token_iker)
                ->orderBy('created_at', 'desc')
                ->first();

            // Verificar si se encontró el token
            if (!$token_noe_record) {
                return response()->json(['error' => 'Token no encontrado'], 401);
            }

            $token_noe = $token_noe_record->token_2;

            // Validación de los datos recibidos
            $validate = Validator::make($request->all(), [
                'nombre' => 'string|required',
                'direccion' => 'string|required',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()
                ], 400);
            }

            $faker = Faker::create();
            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->post('https://1826-187-190-56-49.ngrok-free.app/generos', [
                'nombre' => $faker->name, // Usar nombre del request
                'descripcion' => $faker->sentence, // Usar dirección del request
            ]);

            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }

            // Crear la sucursal localmente
            $sucursal = Sucursal::create([
                'nombre' => $request->input('nombre'),
                'direccion' => $request->input('direccion'),
            ]);

            return response()->json([
                'msg' => 'Sucursal creada con éxito',
                'sucursal' => $sucursal,
                'data' => $dataResponse->json()
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $token_iker = $request->header('Authorization');

            // Buscar el token más reciente correspondiente
            $token_noe_record = Token::where('token_1', $token_iker)
                ->orderBy('created_at', 'desc')
                ->first();

            // Verificar si se encontró el token
            if (!$token_noe_record) {
                return response()->json(['error' => 'Token no encontrado'], 401);
            }

            $token_noe = $token_noe_record->token_2;

            // Validación de los datos recibidos
            $validate = Validator::make($request->all(), [
                'nombre' => 'string|max:128|required',
                'direccion' => 'string|max:255|required',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()
                ], 400);
            }

            // Buscar la sucursal a actualizar
            $sucursal = Sucursal::find($id);

            if (!$sucursal) {
                return response()->json([
                    'msg' => 'Sucursal no encontrada'
                ], 404);
            }

            $faker = Faker::create();
            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->post('https://1826-187-190-56-49.ngrok-free.app/generos', [
                'nombre' => $faker->name, // Usar nombre del request
                'descripcion' => $faker->sentence, // Usar dirección del request
            ]);

            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }

            // Actualizar la sucursal localmente
            $sucursal->update([
                'nombre' => $request->input('nombre'),
                'direccion' => $request->input('direccion'),
            ]);

            return response()->json([
                'msg' => 'Sucursal actualizada con éxito',
                'sucursal' => $sucursal,
                'data' => $dataResponse->json()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id, Request $request)
    {
        try {
            $token_iker = $request->header('Authorization');

            // Buscar el token más reciente correspondiente
            $token_noe_record = Token::where('token_1', $token_iker)
                ->orderBy('created_at', 'desc')
                ->first();

            // Verificar si se encontró el token
            if (!$token_noe_record) {
                return response()->json(['error' => 'Token no encontrado'], 401);
            }

            $token_noe = $token_noe_record->token_2;

            // Buscar la sucursal a eliminar
            $sucursal = Sucursal::find($id);

            if (!$sucursal) {
                return response()->json(['msg' => "Sucursal no encontrada"], 404);
            }

            // Hacer la petición a la API externa para eliminar los datos correspondientes
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->delete('https://1826-187-190-56-49.ngrok-free.app/generos/' . $id);

            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }

            // Eliminar la sucursal localmente
            $sucursal->delete();

            return response()->json(['msg' => 'Sucursal eliminada con éxito'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500);
        }
    }
}
