<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\Token;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Validator;

class EmpleadoController extends Controller
{
    public function index(Request $request)
    {
        $token_iker = $request->header('Authorization');

        // Buscar el token correspondiente
        $token_noe = Token::where('token_1', $token_iker)->first();
        $token_noe = $token_noe->token_2;

        try {
            // Obtener todos los autores de la base de datos local
            $empleadoes = Autor::all();

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://1826-187-190-56-49.ngrok-free.app

/playlistsCanciones');

            // Devolver la respuesta con los empleadoes y los datos de la API externa
            return response()->json([
                'msg' => 'Autores y albums encontrados',
                'autores' => $empleadoes,
                'playcan' => $dataResponse->json()
            ], 200);

        } catch (\Exception $e) {
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
        $token_noe = $token_noe->token_2;

        try {
            // Buscar al autor por ID
            $empleado = Autor::find($id);

            // Verificar si el autor existe
            if (!$empleado) {
                return response()->json([
                    'msg' => 'No se encontró el autor'
                ], 404); // Código 404 para "No encontrado"
            }

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get("https://1826-187-190-56-49.ngrok-free.app

/playlistsCanciones/{$id}");

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400);
            }

            return response()->json([
                'msg' => 'Autor encontrado',
                'autor' => $empleado,
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
                'sucursal_id' => 'integer|required',
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
            ])->post('https://1826-187-190-56-49.ngrok-free.app

/playlistsCanciones', [
                  'playlist_id' => 1,
                  'cancion_id' => 1,
                               // Usar país del request
            ]);

            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }

            // Crear el autor localmente
            $empleado = Autor::create([
                'nombre' => $request->input('nombre'),
                'sucursal_id' => $request->input('sucursal_id'),
            ]);

            return response()->json([
                'msg' => 'Autor creado con éxito',
                'autor' => $empleado,
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
                'sucursal_id' => 'integer|required',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()
                ], 400);
            }

            // Buscar el autor a actualizar
            $empleado = Autor::find($id);

            if (!$empleado) {
                return response()->json([
                    'msg' => 'Empleado no encontrado'
                ], 404);
            }

            $faker = Faker::create();
            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->put('https://1826-187-190-56-49.ngrok-free.app

/playlistsCanciones/' . $id, [
                'playlist_id' => 1,
                'cancion_id' => 1,
            ]);

            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }

            // Actualizar el autor localmente
            $empleado->update([
                'nombre' => $request->input('nombre'),
                'sucursal_id' => $request->input('sucursal_id'),
            ]);

            return response()->json([
                'msg' => 'Autor actualizado con éxito',
                'emplado' => $empleado,
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

            // Buscar el autor a eliminar
            $empleado = Autor::find($id);

            if (!$empleado) {
                return response()->json(['msg' => "Autor no encontrado"], 404);
            }

            // Hacer la petición a la API externa para eliminar los datos correspondientes
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->delete('https://1826-187-190-56-49.ngrok-free.app

/playlistsCanciones/' . $id);

            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }

            // Eliminar el autor localmente
            $empleado->delete();

            return response()->json([
                'msg' => "Empleado y datos de la API externa eliminados con éxito"
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500);
        }
    }
}
