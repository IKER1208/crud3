<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestamo;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Faker\Factory as Faker;

class PrestamoController extends Controller
{
    public function index(Request $request)
    {
        // Extraer el token desde el encabezado 'token_noe'
        $token_noe = request()->header('token_noe');

        // Validar si el token fue proporcionado
        if (!$token_noe) {
            return response()->json([
                'error' => 'Token no proporcionado'
            ], 401); // Devuelve error 401 si el token no fue proporcionado
        }

        try {
            // Obtener todos los préstamos desde la base de datos
            $prestamos = Prestamo::all();

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
            'Authorization' => $token_noe
            ])->get('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/playlists');

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400);
            }

            // Devolver la respuesta con los préstamos y las playlists de la API externa
            return response()->json([
                'msg' => 'Listado de préstamos',
                'prestamos' => $prestamos,
                'playlists' => $dataResponse->json()
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
        // Extraer el token desde el encabezado 'token_noe'
        $token_noe = request()->header('token_noe');

        // Validar si el token fue proporcionado
        if (!$token_noe) {
            return response()->json([
                'error' => 'Token no proporcionado'
            ], 401); // Devuelve error 401 si el token no fue proporcionado
        }

        // Buscar el préstamo por su ID
        $prestamo = Prestamo::find($id);
        if (!$prestamo) {
            return response()->json([
                'msg' => 'No se encontró el préstamo'
            ], 404);
        }

        try {
            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
            'Authorization' => $token_noe
            ])->get('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/playlists/' . $id);

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400);
            }

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
            // Validar la solicitud
            $request->validate([
                'cliente_id' => 'required|integer',
                'libro_id' => 'required',
                'fecha_prestamo' => 'required|date',
                'fecha_devolucion' => 'nullable|date',
            ]);

            // Crear una instancia de Faker
            $faker = Faker::create();

            // Crear un nuevo préstamo
            $prestamo = new Prestamo();
            $prestamo->cliente_id = $request->cliente_id;
            $prestamo->libro_id = $request->libro_id;
            $prestamo->fecha_prestamo = $request->fecha_prestamo;
            $prestamo->fecha_devolucion = $request->fecha_devolucion;
            $prestamo->save();

            // Hacer la petición a la API externa utilizando el token proporcionado
            $token_noe = $request->header('token_noe');
            $dataResponse = Http::withHeaders([
            'Authorization' => $token_noe
            ])->post("https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/playlists", [
                'nombre' => $faker->sentence,
                'descripcion' => $faker->sentence,
                'user_id' => 1,
            ]);

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al crear la playlist en la API externa',
                    'details' => $dataResponse->json()
                ], 400);
            }

            // Devolver la respuesta confirmando la creación
            return response()->json([
                'msg' => 'Préstamo creado con éxito',
                'prestamo' => $prestamo,
                'playlist' => $dataResponse->json()
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
            // Validar la solicitud
            $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'libro_id' => 'required|exists:libros,id',
                'fecha_prestamo' => 'required|date',
                'fecha_devolucion' => 'nullable|date',
            ]);

            // Buscar el préstamo por su ID
            $prestamo = Prestamo::find($id);
            if (!$prestamo) {
                return response()->json([
                    'msg' => 'No se encontró el préstamo'
                ], 404);
            }
            $faker = Faker::create();
            // Actualizar los datos del préstamo
            $prestamo->cliente_id = $request->input('cliente_id');
            $prestamo->libro_id = $request->input('libro_id');
            $prestamo->fecha_prestamo = $request->input('fecha_prestamo');
            $prestamo->fecha_devolucion = $request->input('fecha_devolucion');
            $prestamo->save();

            // Hacer la petición a la API externa utilizando el token proporcionado
            $token_noe = $request->header('token_noe');
            $dataResponse = Http::withHeaders([
            'Authorization' => $token_noe
            ])->put("https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/playlists/".$id, [
                'nombre' => $faker->sentence,
                'descripcion' => $faker->sentence,
                'user_id' => 1,
            ]);

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al actualizar la playlist en la API externa',
                    'details' => $dataResponse->json()
                ], 400);
            }

            // Devolver la respuesta confirmando la actualización
            return response()->json([
                'msg' => 'Préstamo y playlist actualizados con éxito',
                'prestamo' => $prestamo,
                'playlist' => $dataResponse->json()
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
            // Buscar el préstamo por su ID
            $prestamo = Prestamo::find($id);
            if (!$prestamo) {
                return response()->json([
                    'error' => 'Préstamo no encontrado'
                ], 404);
            }

            // Hacer la petición a la API externa utilizando el token proporcionado
            $token_noe = $request->header('token_noe');

            if (!$token_noe) {
                return response()->json([
                    'error' => 'Token no proporcionado'
                ], 401);
            }

            // Eliminar el préstamo localmente
            $prestamo->delete();

            // Eliminar la playlist en la API externa
            $dataResponse = Http::withHeaders([
            'Authorization' => $token_noe
            ])->delete("https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/playlists/{$id}");

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al eliminar la playlist en la API externa',
                    'details' => $dataResponse->json()
                ], 400);
            }

            // Devolver la respuesta confirmando la eliminación
            return response()->json([
                'msg' => 'Préstamo y playlist eliminados con éxito'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500);
        }
    }
}
