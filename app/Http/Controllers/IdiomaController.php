<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Models\Idioma;
use Faker\Factory as Faker;

class IdiomaController extends Controller
{
    public function index(Request $request)
    {
        // Extraer el token desde el encabezado 'token_noe'
        $token_noe = $request->header('token_noe');

        // Validar si el token fue proporcionado
        if (!$token_noe) {
            return response()->json([
                'error' => 'Token no proporcionado'
            ], 401);
        }

        try {
            // Obtener los idiomas desde la base de datos
            $idiomas = Idioma::all();

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/playlistsCanciones');

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400);
            }

            return response()->json([
                'msg' => 'Listado de idiomas',
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
        // Extraer el token desde el encabezado 'token_noe'
        $token_noe = $request->header('token_noe');

        // Validar si el token fue proporcionado
        if (!$token_noe) {
            return response()->json([
                'error' => 'Token no proporcionado'
            ], 401);
        }

        try {
            $idioma = Idioma::find($id);
            if (!$idioma) {
                return response()->json([
                    'msg' => 'No se encontró el idioma'
                ], 404);
            }

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/playlistsCanciones/' . $id);

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
            $request->validate([
                'nombre' => 'required|string',
            ]);

            $faker = Faker::create();
            $idioma = new Idioma();
            $idioma->nombre = $request->input('nombre');
            $idioma->save();

            // Obtener el token desde el header 'token_noe'
            $token_noe = $request->header('token_noe');

            if (!$token_noe) {
                return response()->json([
                    'error' => 'Token no proporcionado'
                ], 401);
            }

            $playlistcData = [
                'playlist_id' =>1,
                'cancion_id' => 1,
            ];

            // Crear la playlist en la API externa
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->post("https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/playlistsCanciones/", $playlistcData);

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al crear la playlistData en la API externa',
                    'details' => $dataResponse->json()
                ], 400);
            }

            $resena = $dataResponse->json();
            \Log::info('Respuesta de la API externa:', $resena); // Registra la respuesta

            return response()->json([
                'msg' => 'Idioma creado',
                'idioma' => $idioma,
                'data' => $resena,
                'playlistcData' => $playlistcData
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
            $request->validate([
                'nombre' => 'required|string',
            ]);

            $idioma = Idioma::find($id);
            if (!$idioma) {
                return response()->json([
                    'msg' => 'No se encontró el idioma'
                ], 404);
            }

            // Obtener el token desde el header 'token_noe'
            $token_noe = $request->header('token_noe');

            if (!$token_noe) {
                return response()->json([
                    'error' => 'Token no proporcionado'
                ], 401);
            }

            $idioma->nombre = $request->input('nombre');
            $idioma->save();

            $faker = Faker::create();
            $playlistcData = [
                'playlist_id' => 1,
                'cancion_id' => 1,
            ];

            // Actualizar la playlist en la API externa
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->put("https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/playlistsCanciones/" . $id, $playlistcData);

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al actualizar la playlistData en la API externa'
                ], 400);
            }

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

    public function destroy(Request $request, $id)
    {
        try {
            $idioma = Idioma::find($id);
            if (!$idioma) {
                return response()->json([
                    'error' => 'Idioma no encontrado'
                ], 404);
            }

            // Obtener el token desde el header 'token_noe'
            $token_noe = $request->header('token_noe');

            if (!$token_noe) {
                return response()->json([
                    'error' => 'Token no proporcionado'
                ], 401);
            }

            // Eliminar el idioma localmente
            $idioma->delete();

            // Eliminar la playlist en la API externa
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->delete("https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/playlistsCanciones/{$id}");

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al eliminar la playlist en la API externa',
                    'details' => $dataResponse->json()
                ], 400);
            }

            return response()->json([
                'msg' => 'Idioma eliminado con éxito'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500);
        }
    }
}
