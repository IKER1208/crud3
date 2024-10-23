<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestamo;
use App\Models\Token;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Faker\Factory as Faker;

class PrestamoController extends Controller
{
    public function index(Request $request)
    {
        // Extraer el token desde el encabezado 'token_noe'
        $token_iker = request()->header('Authorization');

        // Buscar el token correspondiente
        $token_noe = Token::where('token_1', $token_iker)->first();
        $token_noe = $token_noe->token_2;

        try {
            // Obtener todos los préstamos desde la base de datos
            $prestamos = Prestamo::all();

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://1826-187-190-56-49.ngrok-free.app/singles');

            // Devolver la respuesta con los préstamos y las playlists de la API externa
            return response()->json([
                'msg' => 'Listado de préstamos',
                'prestamos' => $prestamos,
                'singles' => $dataResponse->json()
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
        try {

            // Buscar el préstamo por su ID
            $prestamo = Prestamo::find($id);
            // Extraer el token desde el encabezado 'token_noe'
            $token_iker = $request->header('Authorization');

            // Buscar el token correspondiente
            $token_noe = Token::where('token_1', $token_iker)->first();
            $token_noe = $token_noe->token_2;

            if (!$prestamo) {
                return response()->json([
                    'msg' => 'No se encontró el prestamo'
                ], 404);
            }

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://1826-187-190-56-49.ngrok-free.app/singles/' . $id);

            // Devolver la respuesta con el préstamo y los datos de la API externa
            return response()->json([
                'msg' => 'Préstamo encontrado',
                'prestamo' => $prestamo,
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

            $validate = Validator::make($request->all(), [
                'cliente_id' => 'required|integer',
                'libro_id' => 'required|integer',
                'fecha_prestamo' => 'required|date',
                'fecha_devolucion' => 'nullable|date',
            ]);

            if ($validate->fails()) {
                return response()->json(['error' => $validate->errors()], 400);
            }

            // Crear una instancia de Faker
            $faker = Faker::create();

            $formatos = ["CD", "Vinilo", "Cassette", "Digital"];

            $formato = $formatos[array_rand($formatos)];

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->post('https://1826-187-190-56-49.ngrok-free.app/singles', [
                        'nombre' => $faker->name, // Usar nombre del request
                        'formato' => $formato, // Usar formato del request
                    ]);

            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al crear elSingleNode en la API externa',
                    'details' => $dataResponse->json()
                ], 400);
            }

            $prestamo = Prestamo::create([
                'cliente_id' => $request->input('cliente_id'),
                'libro_id' => $request->input('cliente_id'),
                'fecha_prestamo' => $request->input('fecha_prestamo'),
                'fecha_devolucion' => $request->input('fecha_devolucion'),
            ]);

            // Devolver la respuesta confirmando la creación
            return response()->json([
                'msg' => 'Préstamo creado con éxito',
                'prestamo' => $prestamo,
                'single' => $dataResponse->json()
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


            $validate = Validator::make($request->all(), [
                'cliente_id' => 'required|integer',
                'libro_id' => 'required|integer',
                'fecha_prestamo' => 'required|date',
                'fecha_devolucion' => 'nullable|date',
            ]);

            if ($validate->fails()) {
                return response()->json(['error' => $validate->errors()], 400);
            }

            $prestamo = Prestamo::find($id);

            if (!$prestamo) {
                return response()->json([
                    'msg' => 'No se encontró el prestamo'
                ], 404);
            }

            // Buscar el préstamo por su ID
            $prestamo = Prestamo::find($id);

            if (!$prestamo) {
                return response()->json([
                    'msg' => 'No se encontró el préstamo'
                ], 404);
            }

            $faker = Faker::create();

            $formatos = ["CD", "Vinilo", "Cassette", "Digital"];

            $formato = $formatos[array_rand($formatos)];

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->put("https://1826-187-190-56-49.ngrok-free.app/singles/" . $id, [
                        'nombre' => $faker->sentence,
                        'formato' => $formato,
                    ]);

            $prestamo->update([
                'cliente_id' => $request->input('cliente_id'),
                'libro_id' => $request->input('cliente_id'),
                'fecha_prestamo' => $request->input('fecha_prestamo'),
                'fecha_devolucion' => $request->input('fecha_devolucion'),
            ]);

            // Devolver la respuesta confirmando la actualización
            return response()->json([
                'msg' => 'Préstamo y single actualizados con éxito',
                'prestamo' => $prestamo,
                'singles' => $dataResponse->json()
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

            // Buscar el préstamo por su ID
            $prestamo = Prestamo::find($id);

            if (!$prestamo) {
                return response()->json([
                    'error' => 'Préstamo no encontrado'
                ], 404);
            }

            // Eliminar la playlist en la API externa
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->delete("https://1826-187-190-56-49.ngrok-free.app/singles/{$id}");

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'details' => $dataResponse->json()
                ], $dataResponse->status());
            }

            // Eliminar el registro de la base de datos
            $prestamo->delete();

            // Devolver la respuesta confirmando la eliminación
            return response()->json([
                'msg' => 'Préstamo y single eliminados con éxito'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500);
        }
    }
}
