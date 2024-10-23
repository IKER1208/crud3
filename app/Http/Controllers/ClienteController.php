<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Models\Token;
use Faker\Factory as Faker;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $token_iker = $request->header('Authorization');

            // Buscar el token correspondiente
            $token_noe_record = Token::where('token_1', $token_iker)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$token_noe_record) {
                return response()->json(['error' => 'Token no encontrado'], 401);
            }

            $token_noe = $token_noe_record->token_2;

            // Obtener los datos de los clientes desde la base de datos
            $clientes = Cliente::all();

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/discografias');

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400);
            }

            // Devolver la respuesta con los clientes locales y las canciones de la API externa
            return response()->json([
                'msg' => 'Clientes encontrados',
                'clientes' => $clientes,
                'canciones' => $dataResponse->json()
            ], 200);

        } catch (\Exception $e) {
            // Manejo de excepciones en caso de error al comunicarse con la API externa
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id, Request $request)
    {
        try {
            $token_iker = $request->header('Authorization');

            // Buscar el token correspondiente
            $token_noe_record = Token::where('token_1', $token_iker)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$token_noe_record) {
                return response()->json(['error' => 'Token no encontrado'], 401);
            }

            $token_noe = $token_noe_record->token_2;

            // Obtener el cliente por su ID
            $cliente = Cliente::find($id);
            if (!$cliente) {
                return response()->json(['error' => 'Cliente no encontrado'], 404);
            }

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/discografias/' . $id);

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400);
            }

            // Devolver la respuesta con el cliente y los datos de la API externa
            return response()->json([
                'msg' => 'Cliente encontrado',
                'cliente' => $cliente,
                'data' => $dataResponse->json()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
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

            if (!$token_noe_record) {
                return response()->json(['error' => 'Token no encontrado'], 401);
            }

            $token_noe = $token_noe_record->token_2;

            // Validación de los datos recibidos
            $validate = Validator::make($request->all(), [
                'nombre' => 'string|required',
                'email' => 'email|required'
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
            ])->post('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/discografias', [
                'nombre' => $faker->name, // Usar nombre del request
                'telefono' => $faker->phoneNumber,
                'direccion' => $faker->address,
            ]);

            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }

            // Crear el cliente localmente
            $cliente = Cliente::create([
                'nombre' => $request->input('nombre'),
                'email' => $request->input('email'),
            ]);

            return response()->json([
                'msg' => 'Cliente creado con éxito',
                'cliente' => $cliente,
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

            if (!$token_noe_record) {
                return response()->json(['error' => 'Token no encontrado'], 401);
            }

            $token_noe = $token_noe_record->token_2;

            // Validación de los datos recibidos
            $validate = Validator::make($request->all(), [
                'nombre' => 'string|max:128|required',
                'email' => 'email|max:128|required',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()
                ], 400);
            }

            // Buscar el cliente a actualizar
            $cliente = Cliente::find($id);

            if (!$cliente) {
                return response()->json([
                    'msg' => 'Cliente no encontrado'
                ], 404);
            }

            $faker = Faker::create();
            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->post('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/discografias', [

                'nombre' => $faker->name, // Usar nombre del request
                'telefono' => $faker->phoneNumber,
                'direccion' => $faker->address,                // Usar país del request
            ]);

            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }

            // Actualizar el cliente localmente
            $cliente->update([
                'nombre' => $request->input('nombre'),
                'email' => $request->input('email'),]);

            return response()->json([
                'msg' => 'Cliente actualizado con éxito',
                'cliente' => $cliente,
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

            if (!$token_noe_record) {
                return response()->json(['error' => 'Token no encontrado'], 401);
            }

            $token_noe = $token_noe_record->token_2;

            // Buscar el cliente a eliminar
            $cliente = Cliente::find($id);
            if (!$cliente) {
                return response()->json(['error' => 'Cliente no encontrado'], 404);
            }

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->delete('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/discografias/' . $id);

            // Manejo de error de la respuesta de la API
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => $dataResponse->json() // Proporcionar detalles del error
                ], $dataResponse->status());
            }

            // Eliminar el cliente localmente
            $cliente->delete();

            return response()->json([
                'msg' => 'Cliente eliminado con éxito'
            ], 204);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500);
        }
    }
}
