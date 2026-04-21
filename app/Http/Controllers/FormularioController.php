<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Formulario\FormularioService;

class FormularioController extends Controller
{
 public function __construct(
     protected FormularioService $formularioService
 ) {
 }

 public function index()
 {
     return view('formulario');
 }
 
public function store(Request $request)
{

    // validaciones del lado del backend
    $validated = $request->validate([
        'nombre' => ['required', 'string', 'max:120'],
        'email' => ['required', 'email', 'max:190'],
        'mensaje' => ['required', 'string', 'max:5000'],
        'adjuntos' => ['nullable', 'array', 'max:5'],
        'adjuntos.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,txt', 'max:5120'],
    ]);

    $result = $this->formularioService->submit(
        $validated,
        $request->file('adjuntos', [])
    );

    return response()->json([
        'status' => $result['status'],
        'body' => $result['body'],
    ]);
}



public function enviarEmail(Request $request)
{
    try {
    
        $datos = $request->json()->all();

        $this->formularioService->processEmailPayload($datos);

        return response()->json([
            'status' => 'Correo enviado correctamente'
        ], 200);

    } catch (\Throwable $e) {

        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}

}
