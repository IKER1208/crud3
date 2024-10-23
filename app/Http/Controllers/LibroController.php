<?php

namespace App\Http\Controllers;

use App\Models\Libro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Faker\Factory as Faker;

class LibroController extends Controller
{
    public function index(Request $request)
    {
        $token_iker = $request->header('Authorization');

        // Buscar el token correspondiente
        $token_noe = Token::where('token_1', $token_iker)->first();
        $token_noe = $token_noe ? $token_noe->token_2 : null;

        if (!$token_noe) {
            return response()->json(['error' => 'Token no encontrado'], 401);
        }

        try {
            // Obtener los datos de los libros desde la base de datos
            $libros = Libro::all();

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/albums');

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400);
            }

            // Devolver la respuesta con los libros locales y los albums de la API externa
            return response()->json([
                'msg' => 'Libros encontrados',
                'libros' => $libros,
                'albums' => $dataResponse->json()
            ], 200);

        } catch (\Exception $e) {
            // Manejo de excepciones en caso de error al comunicarse con la API externa
            return response()->json([
                'error' => 'Error al comunicarse con la API externa'
            ], 500);
        }
    }

    public function show($id, Request $request)
    {
        $token_iker = $request->header('Authorization');

        // Buscar el token correspondiente
        $token_noe = Token::where('token_1', $token_iker)->first();
        $token_noe = $token_noe ? $token_noe->token_2 : null;

        if (!$token_noe) {
            return response()->json(['error' => 'Token no encontrado'], 401);
        }

        try {
            // Obtener el libro por ID desde la base de datos
            $libro = Libro::find($id);

            // Verificar si el libro fue encontrado
            if (!$libro) {
                return response()->json(['error' => 'Libro no encontrado'], 404);
            }

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/albums/' . $id);

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400);
            }

            // Devolver la respuesta con el libro encontrado y los datos de la API externa
            return response()->json([
                'msg' => 'Libro encontrado',
                'libro' => $libro,
                'data' => $dataResponse->json()
            ], 200);

        } catch (\Exception $e) {
            // Manejo de excepciones en caso de error al comunicarse con la API externa
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
                'titulo' => 'string|required',
                'autor' => 'string|required',
            ]);

            if ($validate->fails()) {
                return response()->json(['error' => $validate->errors()], 400);
            }

            $faker = Faker::create();
            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->post('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/albums', [
                'nombre' => $faker->sentence,
                'fecha_lanzamiento' => $faker->date,
                'artista_id' => 1,
            ]);

            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json(['error' => $dataResponse->json()], $dataResponse->status());
            }

            // Crear el libro localmente
            $libro = Libro::create([
                'titulo' => $request->input('titulo'),
                'autor' => $request->input('autor'),
            ]);

            return response()->json([
                'msg' => 'Libro creado con éxito',
                'libro' => $libro,
                'data' => $dataResponse->json()
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()], 500);
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
                'titulo' => 'string|max:128|required',
                'autor' => 'string|max:64|required',
            ]);

            if ($validate->fails()) {
                return response()->json(['error' => $validate->errors()], 400);
            }

            // Buscar el libro a actualizar
            $libro = Libro::find($id);

            if (!$libro) {
                return response()->json(['error' => 'Libro no encontrado'], 404);
            }

            $faker = Faker::create();
            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->put('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/albums/' . $id, [

                'nombre' => $faker->sentence,
                'fecha_lanzamiento' => $faker->date,
                'artista_id' => 1,
            ]);

            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json(['error' => $dataResponse->json()], $dataResponse->status());
            }

            // Actualizar el libro localmente
            $libro->update([
                'titulo' => $request->input('titulo'),
                'autor' => $request->input('autor'),
            ]);

            return response()->json([
                'msg' => 'Libro actualizado con éxito',
                'libro' => $libro,
                'data' => $dataResponse->json()
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()], 500);
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

            // Buscar el libro a eliminar
            $libro = Libro::find($id);

            if (!$libro) {
                return response()->json(['error' => 'Libro no encontrado'], 404);
            }

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->delete('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/albums/' . $id);

            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json(['error' => $dataResponse->json()], $dataResponse->status());
            }

            // Eliminar el libro localmente
            $libro->delete();

            return response()->json(['msg' => 'Libro eliminado con éxito'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()], 500);
        }
    }
}
