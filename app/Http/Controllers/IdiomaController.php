<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Models\Idioma;
use App\Models\Token;
use Illuminate\Support\Facades\Validator;
use Faker\Factory as Faker;

class IdiomaController extends Controller
{
    public function index(Request $request)
    {
        $token_iker = $request->header('Authorization');

        // Buscar el token correspondiente
        $token_noe = Token::where('token_1', $token_iker)->first();
        $token_noe = $token_noe->token_2;

        try {
            // Obtener todos los idiomaes de la base de datos local
            $idiomas = Idioma::all();

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://1826-187-190-56-49.ngrok-free.app/resenas');

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400);
            }

            // Devolver la respuesta con los idiomaes y los datos de la API externa
            return response()->json([
                'msg' => 'Idiomas encontrados',
                'idiomas' => $idiomas,
                'data' => $dataResponse->json()
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
            $idioma = Idioma::find($id);

            $token_iker = $request->header('Authorization');

            // Buscar el token correspondiente
            $token_noe = Token::where('token_1', $token_iker)->first();
            $token_noe = $token_noe->token_2;

            if (!$idioma) {
                return response()->json([
                    'msg' => 'No se encontró el idioma'
                ], 404);
            }

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://1826-187-190-56-49.ngrok-free.app/resenas/' . $id);

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400);
            }

            return response()->json([
                'msg' => 'Idioma encontrado',
                'idioma' => $idioma,
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

            $faker = Faker::create();

            // Validación de los datos recibidos
            $validate = Validator::make($request->all(), [
                'nombre' => 'string|required',
            ]);

            $resenaData = [
                'resena' => $faker->text(200),
                'fecha' => $faker->date,
                'calificacion' => $faker->numberBetween(1, 10),
                'user_id' => $request->input('id'),
                'cancion_id' => $request->input('id'),
            ];
            $faker = Faker::create();
            // Crear la playlist en la API externa
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->post("https://1826-187-190-56-49.ngrok-free.app/resenas/", $resenaData);

            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()
                ], 400);
            }
            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }

            // Crear la idioma localmente
            $idioma = idioma::create([
                'nombre' => $request->input('nombre'),
            ]);

            return response()->json([
                'msg' => 'Idioma creado con éxito',
                'idioma' => $idioma,
                'data' => $dataResponse->json(),
                'resenaData' => $resenaData
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
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()
                ], 400);
            }

            // Buscar la idioma a actualizar
            $idioma = idioma::find($id);

            if (!$idioma) {
                return response()->json([
                    'msg' => 'Idioma no encontrado'
                ], 404);
            }

            $faker = Faker::create();
            $resenaData = [
                'resena' => $faker->text(200),
                'fecha' => $faker->date,
                'calificacion' => $faker->numberBetween(1, 10),
                'user_id' => $request->input('id'),
                'cancion_id' => $request->input('id'),
            ];

            // Actualizar la playlist en la API externa
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->put("https://1826-187-190-56-49.ngrok-free.app/resenas/" . $id, $resenaData);

            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }

            // Actualizar la idioma localmente
            $idioma->update([
                'nombre' => $request->input('nombre'),
            ]);

            return response()->json([
                'msg' => 'Idioma actualizado con éxito',
                'idioma' => $idioma,
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

            // Buscar la idioma a eliminar
            $idioma = Idioma::find($id);

            if (!$idioma) {
                return response()->json(['msg' => "idioma no encontrado"], 404);
            }

            // Hacer la petición a la API externa para eliminar los datos correspondientes
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->delete("https://1826-187-190-56-49.ngrok-free.app/resenas/{$id}");

            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }

            // Eliminar la idioma localmente
            $idioma->delete();

            return response()->json([
                'msg' => "Idioma y datos de la API externa eliminados con éxito"
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500);
        }
    }
}
