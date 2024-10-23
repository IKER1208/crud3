<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Editorial;
use App\Models\Token;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Validator;

class EditorialController extends Controller
{
    public function index(Request $request)
    {
        $token_iker = $request->header('Authorization');

        // Buscar el token correspondiente
        $token_noe = Token::where('token_1', $token_iker)->first();
        $token_noe = $token_noe->token_2;

        try {
            // Obtener todos los editoriales de la base de datos local
            $editoriales = Editorial::all();

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://1826-187-190-56-49.ngrok-free.app/artistas');

            // Devolver la respuesta con los editoriales y los datos de la API externa
            return response()->json([
                'msg' => 'Editoriales encontrados',
                'editoriales' => $editoriales,
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
            $editorial = Editorial::find($id);

            $token_iker = $request->header('Authorization');

            // Buscar el token correspondiente
            $token_noe = Token::where('token_1', $token_iker)->first();
            $token_noe = $token_noe->token_2;

            if (!$editorial) {
                return response()->json([
                    'msg' => 'No se encontró la editorial'
                ], 404);
            }

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://1826-187-190-56-49.ngrok-free.app/artistas/' . $id);

            return response()->json([
                'msg' => 'Editorial encontrada',
                'editorial' => $editorial,
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
                'pais' => 'string|required',
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
            ])->post('https://1826-187-190-56-49.ngrok-free.app/artistas', [
                'nombre' => $faker->name, // Usar nombre del request
                'nacionalidad' => $faker->country, // Usar país del request
            ]);
    
            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }
    
            // Crear la editorial localmente
            $editorial = Editorial::create([
                'nombre' => $request->input('nombre'),
                'pais' => $request->input('pais'),
            ]);
    
            return response()->json([
                'msg' => 'Editorial creada con éxito',
                'editorial' => $editorial,
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
                'pais' => 'string|max:64|required',
            ]);
    
            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()
                ], 400);
            }
    
            // Buscar la editorial a actualizar
            $editorial = Editorial::find($id);
    
            if (!$editorial) {
                return response()->json([
                    'msg' => 'Editorial no encontrada'
                ], 404);
            }

            $faker = Faker::create();
            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->post('https://1826-187-190-56-49.ngrok-free.app/artistas', [
                'nombre' => $faker->name, // Usar nombre del request
                'nacionalidad' => $faker->country, // Usar país del request
            ]);
    
            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }
    
            // Actualizar la editorial localmente
            $editorial->update([
                'nombre' => $request->input('nombre'),
                'pais' => $request->input('pais'),
            ]);
    
            return response()->json([
                'msg' => 'Editorial actualizada con éxito',
                'editorial' => $editorial,
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
    
            // Buscar la editorial a eliminar
            $editorial = Editorial::find($id);
    
            if (!$editorial) {
                return response()->json(['msg' => "Editorial no encontrada"], 404);
            }
    
            // Hacer la petición a la API externa para eliminar los datos correspondientes
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->delete('https://1826-187-190-56-49.ngrok-free.app/artistas/' . $id);
    
            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }
    
            // Eliminar la editorial localmente
            $editorial->delete();
    
            return response()->json([
                'msg' => "Editorial y datos de la API externa eliminados con éxito"
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500);
        }
    }
    
}
