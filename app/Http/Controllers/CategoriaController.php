<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Faker\Factory as Faker;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        $token_iker = $request->header('Authorization');

        $token_noe = Token::where('token_1', $token_iker)->first();
        $token_noe = $token_noe->token_2;

        return response()->json([
            $token_noe
        ]);


        if (!$token_noe) {
            return response()->json([
                'error' => 'Token no proporcionado'
            ], 401);
        }

        try {
            $categorias = Categoria::all();

            $dataResponse = Http::withHeaders([
            'Authorization' => $token_noe
            ])->get('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/discografias');

            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400);
            }

            return response()->json([
                'msg' => 'Categorías encontradas',
                'categorias' => $categorias,
                'discografias' => $dataResponse->json()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id, Request $request)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json([
                'error' => 'No se encontró la categoría'
            ], 404);
        }

        $token_noe = $request->header('token_noe');

        if (!$token_noe) {
            return response()->json([
                'error' => 'Token no proporcionado'
            ], 401);
        }

        try {
            $dataResponse = Http::withHeaders([
            'Authorization' => $token_noe
            ])->get('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/discografias/' . $id);

            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400);
            }

            return response()->json([
                'msg' => 'Categoría encontrada',
                'categoria' => $categoria,
                'discografia' => $dataResponse->json()
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
            $request->validate([
                'nombre' => 'required|string|max:255',
            ]);

            // Crear una instancia de Faker
            $faker = Faker::create();

            // Generar datos ficticios
            $discoData = [
                'nombre' => $faker->name,
                'telefono' => $faker->phoneNumber,
                'direccion' => $faker->address
            ];

            $categoria = new Categoria();
            $categoria->nombre = $request->input('nombre'); 
            $categoria->save();

            $token_noe = $request->header('token_noe');

            if (!$token_noe) {
                return response()->json([
                    'error' => 'Token no proporcionado'
                ], 401);
            }

            $dataResponse = Http::withHeaders([
            'Authorization' => $token_noe
            ])->post('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/discografias', $discoData);

            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al crear la categoría en la API externa',
                    'details' => $dataResponse->json()
                ], 400);
            }

            return response()->json([
                'msg' => 'Categoría creada con éxito',
                'categoria' => $categoria,
                'discografia' => $dataResponse->json()
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
                'nombre' => 'required|string|max:255',
            ]);

            $categoria = Categoria::find($id);

            if (!$categoria) {
                return response()->json([
                    'error' => 'Categoría no encontrada'
                ], 404);
            }

            // Crear una instancia de Faker
            $faker = Faker::create();

            // Generar datos ficticios
            $discoData = [
                'nombre' =>  $faker->name,
                'telefono' => $faker->phoneNumber,
                'direccion' => $faker->address
            ];

            $categoria->nombre = $discoData['nombre'];
            $categoria->save();

            $token_noe = $request->header('token_noe');

            if (!$token_noe) {
                return response()->json([
                    'error' => 'Token no proporcionado'
                ], 401);
            }

            $dataResponse = Http::withHeaders([
            'Authorization' => $token_noe
            ])->put('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/discografias/' . $id, $discoData);

            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al actualizar la categoría en la API externa',
                    'details' => $dataResponse->json()
                ], 400);
            }

            return response()->json([
                'msg' => 'Categoría actualizada con éxito',
                'categoria' => $categoria,
                'discografia' => $dataResponse->json()
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
            $categoria = Categoria::find($id);

            if (!$categoria) {
                return response()->json([
                    'error' => 'Categoría no encontrada'
                ], 404);
            }

            $token_noe = $request->header('token_noe');

            if (!$token_noe) {
                return response()->json([
                    'error' => 'Token no proporcionado'
                ], 401);
            }

            $dataResponse = Http::withHeaders([
            'Authorization' => $token_noe
            ])->delete('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/discografias/' . $id);

            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al eliminar la categoría en la API externa',
                    'details' => $dataResponse->json()
                ], 400);
            }

            $categoria->delete();

            return response()->json([
                'msg' => 'Categoría eliminada con éxito',
                'details' => $dataResponse->json()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500);
        }
    }
}
