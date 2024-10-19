<?php

namespace App\Http\Controllers;

use App\Models\Libro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Faker\Factory as Faker;

class LibroController extends Controller
{public function index(Request $request)
    {
        // Obtener todos los headers de la solicitud entrante
        $headers = $request->headers->all();
    
        // Mostrar todos los headers para depuración
        \Log::info('Headers recibidos:', $headers);
    
        // Extraer el token específico 'token_noe'
        $token_noe = $request->header('token_noe');
    
        // Validar si el token fue proporcionado
        if (!$token_noe) {
            return response()->json([
                'message' => 'Token no proporcionado'
            ], 401);
        }
    
        // Mostrar el token para depuración
        \Log::info('Token recibido:', [$token_noe]);
    
        // Agregar el token a los headers para la API externa
        $headers['Authorization'] = "Bearer " . $token_noe;
    
        // Mostrar los headers que se enviarán a la API externa para depuración
        \Log::info('Headers enviados a la API externa:', $headers);
    
        try {
            // Hacer la petición HTTP GET a la API externa, pasando los headers
            $apiResponse = Http::withHeaders($headers)->get('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/artistas');
    
            // Verificar si la petición a la API externa fue exitosa
            if ($apiResponse->failed()) {
                \Log::error('Error en la API externa:', [
                    'status' => $apiResponse->status(),
                    'response' => $apiResponse->body()
                ]);
                return response()->json([
                    'message' => 'Error al obtener datos de la API externa',
                    'status' => $apiResponse->status(), // Agrega el código de estado
                    'response' => $apiResponse->body() // Muestra el cuerpo de la respuesta
                ], 400);
            }
    
            // Devolver la respuesta de la API externa
            return response()->json([
                'message' => 'Datos obtenidos correctamente',
                'data' => $apiResponse->json()
            ], 200);
    
        } catch (\Exception $e) {
            // Manejo de excepciones en caso de error
            return response()->json([
                'message' => 'Error al comunicarse con la API externa',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    
    public function show($id)
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
        $libros = Libro::all();

        // Hacer la petición a la API externa utilizando el token proporcionado
        $dataResponse = Http::withHeaders([
            'Authorization' => "Bearer {$token_noe}"
        ])->get('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/artistas');

        // Verificar si la respuesta de la API falló
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al obtener datos de la API externa'
            ], 400);
        }

        // Devolver la respuesta con los libros locales y los artistas de la API externa
        return response()->json([
            'msg' => 'Libros encontrados',
            'libros' => $libros,
            'artistas' => $dataResponse->json()
        ], 200);

    } catch (\Exception $e) {
        // Manejo de excepciones en caso de error al comunicarse con la API externa
        return response()->json([
            'error' => 'Error al comunicarse con la API externa'
        ], 500);
    }
}
    // --------------------Crear-----------------------------

    public function store(Request $request)
{
    try {
        // Paso 1: Validar la solicitud
        $request->validate([
            'titulo' => 'required|string|max:255',
            'editorial_id' => 'required|integer|exists:editoriales,id',
        ]);

        // Crear una instancia de Faker
        $faker = Faker::create();

        // Paso 2: Crear un nuevo libro
        $libro = new Libro();
        $libro->titulo = $request->input('titulo'); 
        $libro->editorial_id = $request->input('editorial_id'); 
        $libro->save();

        // Paso 3: Obtener el token
        $tokenResponse = Http::post('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/login', [
            "email" => "noe@juadsdaaaaaaaaaaazn.iker",
            "password" => "password"
        ]);
    
        if ($tokenResponse->failed()) {
            return response()->json([
                'error' => 'Error al autenticar con la API externa'
            ], 400);
        }

        $token = $tokenResponse->json('token');

        // Paso 4: Preparar los datos para la API externa
        $artistaData = [
            'nombre' => $faker->name,
            'nacionalidad' => $faker->country,
        ];

        // Paso 5: Crear el artista
        $dataResponse = Http::withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->post("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/artistas", $artistaData);
    
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al crear el artista en la API externa',
                'details' => $dataResponse->json() // Agrega detalles de la respuesta
            ], 400);
        }

        // Imprimir la respuesta para verificar su estructura
        $artista = $dataResponse->json();
        \Log::info('Respuesta de la API externa:', $artista); // Registra la respuesta

        // Paso 7: Devolver la respuesta confirmando la creación
        return response()->json([
            'msg' => 'Libro creado con éxito',
            'libro' => $libro,
            'artista' => $artista // Devolver el artista creado
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
        ], 500);
    }
}

    // --------------------Editar-----------------------------

    public function update(Request $request, $id)
    {
        try {
            // Paso 1: Validar la solicitud
            $request->validate([
                'titulo' => 'required|string|max:255',
                'editorial_id' => 'required|integer|exists:editoriales,id', // Asegúrate de que el editorial_id existe
            ]);
    
            // Crear una instancia de Faker
            $faker = Faker::create();
    
            // Paso 2: Buscar el libro por ID
            $libro = Libro::find($id);
        
            // Verificar si el libro existe
            if (!$libro) {
                return response()->json([
                    'msg' => 'No se encontró el libro'
                ], 404); // Código 404 para "No encontrado"
            }
    
            $tokenResponse = Http::post('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/login', [
                "email" => "noe@juadsdaaaaaaaaaaazn.iker",
                "password" => "password"
            ]);
        
            if ($tokenResponse->failed()) {
                return response()->json([
                    'error' => 'Error al autenticar con la API externa'
                ], 400); 
            }
        
            // Extraer el token de la respuesta
            $token = $tokenResponse->json('token');
    
            // Paso 4: Actualizar el libro en tu base de datos local
            $libro->titulo = $request->input('titulo'); 
            $libro->editorial_id = $request->input('editorial_id'); 
            $libro->save(); 
        
            // Paso 5: Preparar los datos para la API externa
            $artistaData = [
                'nombre' => "HOLS", 
                'nacionalidad' => "OLSKDFSMFS",
            ];
    
            // Suponiendo que tienes el ID del artista almacenado en el libro
            $artistaId = $libro->artista_id; // Cambia esto si el ID se almacena de otra manera
        
            // Paso 6: Hacer la solicitud PUT a la API protegida para actualizar el artista
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token}"
            ])->put("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/artistas/". $id , $artistaData);
        
            // Verificar si la solicitud de actualización fue exitosa
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al actualizar el artista en la API externa'
                ], 400); // Código 400 para errores de la API externa
            }
        
            // Paso 7: Devolver la respuesta al cliente confirmando la actualización
            return response()->json([
                'msg' => 'Libro actualizado con éxito',
                'libro' => $libro,
                'artista' => $dataResponse->json() // Devolver el artista actualizado si es necesario
            ], 200); // Código 200 para indicar éxito
        } catch (\Exception $e) {
            // Manejo de errores generales
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500); // Código 500 para errores de servidor
        }
    }
    

// ----------------eliminar-----------------

public function destroy($id)
{
    try {
        // Paso 1: Hacer la solicitud POST para obtener el token desde la API externa
        $tokenResponse = Http::post('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/login', [
            "email" => "noe@juadsdaaaaaaaaaaazn.iker",
            "password" => "password"
        ]);

        // Verificar si la solicitud para el token fue exitosa
        if ($tokenResponse->failed()) {
            return response()->json([
                'error' => 'Error al autenticar con la API externa'
            ], 400); // Código 400 para errores de autenticación
        }

        // Extraer el token de la respuesta
        $token = $tokenResponse->json('token');

        // Paso 2: Hacer la solicitud DELETE a la API protegida para eliminar el libro
        $dataResponse = Http::withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->delete("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/artistas/". $id );

        // Verificar si la solicitud de eliminación fue exitosa
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al eliminar el libro en la API externa'
            ], 400); // Código 400 para errores de la API externa
        }

        // Paso 3: Devolver la respuesta al cliente confirmando la eliminación
        return response()->json([
            'msg' => 'Libro eliminado con éxito'
        ], 200); // Código 200 para indicar éxito
    } catch (\Exception $e) {
        // Manejo de errores generales
        return response()->json([
            'error' => 'Error al comunicarse con la API externa'
        ], 500); // Código 500 para errores de servidor
    }
}

}
