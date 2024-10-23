<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Genero;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Faker\Factory as Faker;

class GeneroController extends Controller
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
            // Obtener los datos de los libros desde la base de datos
            $genero = Genero::all();

            // Hacer la petición a la API externa utilizando el token proporcionado
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token_noe}"
            ])->get('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/resenas');

            // Verificar si la respuesta de la API falló
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400);
            }

            // Devolver la respuesta con los libros locales y los artistas de la API externa
            return response()->json([
                'msg' => 'generos encontrados',
                'generos' => $genero,
                'resenas' => $dataResponse->json()
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
    $token_noe = $request->header('token_noe');

    // Validar si el token fue proporcionado
    if (!$token_noe) {
        return response()->json([
            'error' => 'Token no proporcionado'
        ], 401);
    }

    try {
        $genero = Genero::find($id);

        // Validar si se encontró el género
        if (!$genero) {
            return response()->json([
                'error' => 'Género no encontrado'
            ], 404); // Devuelve error 404 si el género no fue encontrado
        }

        // Hacer la petición a la API externa utilizando el token proporcionado
        $dataResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token_noe
        ])->get('https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/resenas/' . $id);

        // Verificar si la respuesta de la API falló
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al obtener datos de la API externa'
            ], $dataResponse->status());
        }

        // Devolver la respuesta con el género local y los artistas de la API externa
        return response()->json([
            'msg' => 'Género encontrado',
            'genero' => $genero,
            'resenas' => $dataResponse->json()
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
            $request->validate([
                'nombre' => 'required|string|max:255',
            ]);
    
            $faker = Faker::create();
    
            $genero = new Genero();
            $genero->nombre = $request->input('nombre'); 
            $genero->save();

            $token_noe = $request->header('token_noe');

            if (!$token_noe) {
                return response()->json([
                    'error' => 'Token no proporcionado'
                ], 401);
            }
            $resenadata = [
                'resena'=>$faker->sentence,
                'fecha'=>$faker->date,
                'calificacion'=>$faker ->numberBetween(1, 10),
                'user_id'=>1,
                'cancion_id'=>1,
            ];
    
            $dataResponse = Http::withHeaders([
            'Authorization' => $token_noe // Usar el token obtenido del header 'token_noe'
            ])->post("https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/resenas", $resenadata);
        
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al crear la resena en la API externa',
                    'details' => $dataResponse->json() 
                ], 400);
            }
    
            $resena = $dataResponse->json();
            \Log::info('Respuesta de la API externa:', $resena);
    
            return response()->json([
                'msg' => 'género creado',
                'genero' => $genero,
                'data' => $resena 
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
        // Paso 1: Validar la solicitud
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        // Paso 2: Encontrar el libro por su ID
        $genero = Genero::find($id);

        if (!$genero) {
            return response()->json([
                'error' => 'Libro no encontrado'
            ], 404);
        }

        // Paso 3: Actualizar los datos del libro
        $genero->nombre = $request->input('nombre');
        $genero->save();

        // Paso 4: Obtener el token desde el header 'token_noe'
        $token_noe = $request->header('token_noe');

        if (!$token_noe) {
            return response()->json([
                'error' => 'Token no proporcionado'
            ], 401);
        }
        $faker = Faker::create();
        // Paso 5: Preparar los datos para la API externa
        $generoData = [
        'resena'=>$faker->word,
        'fecha'=>$faker->date,
        'calificacion'=>$faker ->numberBetween(1, 10),
        'user_id'=>$faker ->numberBetween(50, 100),
        'cancion_id'=>$faker ->numberBetween(50,100),       
        ];

        // Paso 6: Actualizar el artista en la API externa
        $dataResponse = Http::withHeaders([
            'Authorization' => $token_noe
        ])->put("https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/resenas/". $id , $generoData);

        // Verificar si la respuesta de la API falló
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al actualizar el artista en la API externa',
                'details' => $dataResponse->json()
            ], 400);
        }

        // Devolver la respuesta confirmando la actualización
        return response()->json([
            'msg' => 'Libro y artista actualizados con éxito',
            'genero' => $genero,
            'artista' => $dataResponse->json()
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
        // Paso 1: Encontrar el libro por su ID
        $genero = Genero::find($id);

        if (!$genero) {
            return response()->json([
                'error' => 'Libro no encontrado'
            ], 404);
        }

        // Paso 2: Obtener el token desde el header 'token_noe'
        $token_noe = $request->header('token_noe');

        if (!$token_noe) {
            return response()->json([
                'error' => 'Token no proporcionado'
            ], 401);
        }

        // Paso 3: Eliminar el libro localmente
        $genero->delete();

        // Paso 4: Eliminar el artista en la API externa
        $dataResponse = Http::withHeaders([
            'Authorization' => $token_noe
        ])->delete("https://710e-2806-101e-b-2c16-7424-7dea-e6e6-4762.ngrok-free.app/resenas/{$id}");

        // Verificar si la respuesta de la API falló
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al eliminar el resena en la API externa',
                'details' => $dataResponse->json()
            ], 400);
        }

        // Devolver la respuesta confirmando la eliminación
        return response()->json([
            'msg' => 'genero y resena eliminados con éxito'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
        ], 500);
    }
}
}
