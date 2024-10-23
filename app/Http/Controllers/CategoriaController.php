<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Models\Token;
use Illuminate\Support\Facades\Validator;
use Faker\Factory as Faker;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        $token_iker = $request->header('Authorization');

        // Buscar el token correspondiente
        $token_noe = Token::where('token_1', $token_iker)->first();
        $token_noe = $token_noe->token_2;

        try {
            // Obtener todos los categorias de la base de datos local
            $categorias = Categoria::all();

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://1826-187-190-56-49.ngrok-free.app/canciones');

            // Devolver la respuesta con los categorias y los datos de la API externa
            return response()->json([
                'msg' => 'categorias encontradas',
                'categorias' => $categorias,
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
            $categoria = Categoria::find($id);

            $token_iker = $request->header('Authorization');

            // Buscar el token correspondiente
            $token_noe = Token::where('token_1', $token_iker)->first();
            $token_noe = $token_noe->token_2;

            if (!$categoria) {
                return response()->json([
                    'msg' => 'No se encontró la categoría'
                ], 404);
            }

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://1826-187-190-56-49.ngrok-free.app/canciones/' . $id);

            return response()->json([
                'msg' => 'Categoria encontrada',
                'categoria' => $categoria,
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
            ])->post('https://1826-187-190-56-49.ngrok-free.app/canciones', [
                        'nombre' => $faker->name, // Usar nombre del request
                        'duracion' => $faker->randomNumber(1, 10), // Usar número aleatorio del request
                        'artista_id' => $request->input('id'), // Usar número aleatorio del request
                        'genero_id' => $request->input('id'), // Usar número aleatorio del request
                        'album_id' => $request->input('id'), // Usar número aleatorio del request
                    ]);

            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }

            // Crear la categoria localmente
            $categoria = Categoria::create([
                'nombre' => $request->input('nombre'),
            ]);

            return response()->json([
                'msg' => 'Categoria creada con éxito',
                'categoria' => $categoria,
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
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()
                ], 400);
            }

            // Buscar la editorial a actualizar
            $categoria = Categoria::find($id);

            if (!$categoria) {
                return response()->json([
                    'msg' => 'Categoria no encontrada'
                ], 404);
            }

            $faker = Faker::create();
            // Hacer la petición a la API externa utilizando el token proporcionado

            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->put('https://1826-187-190-56-49.ngrok-free.app/canciones/'.$id , [
                    'nombre' => $faker->name, // Usar nombre del request
                    'duracion' => $faker->randomNumber(1, 10), // Usar número aleatorio del request
                    'artista_id' => $request->input('id'), // Usar número aleatorio del request
                    'genero_id' => $request->input('id'), // Usar número aleatorio del request
                    'album_id' => $request->input('id'),  // Usar número aleatorio del request
                    ]);

            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }

            // Actualizar la categoria localmente
            $categoria->update([
                'nombre' => $request->input('nombre'),
            ]);

            return response()->json([
                'msg' => 'Categoria actualizada con éxito',
                'categoria' => $categoria,
                'data' => $dataResponse->json()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
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

            // Buscar la categoria a eliminar
            $categoria = Categoria::find($id);

            if (!$categoria) {
                return response()->json(['msg' => "Categoria no encontrada"], 404);
            }

            // Hacer la petición a la API externa para eliminar los datos correspondientes
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->delete('https://1826-187-190-56-49.ngrok-free.app/canciones/' . $id);

            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }

            // Eliminar la editorial localmente
            $categoria->delete();

            return response()->json([
                'msg' => "Categoria y datos de la API externa eliminados con éxito"
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500);
        }
    }
}
