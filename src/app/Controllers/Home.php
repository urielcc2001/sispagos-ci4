<?php

namespace App\Controllers;

class Home extends BaseController
{
public function index()
{
    try {
        $dbDefault = \Config\Database::connect();
        $dbUni     = \Config\Database::connect('uni');
        $dbPrepa   = \Config\Database::connect('prepa');

        echo "<h2>Estado de Conexiones:</h2>";
        echo "✅ Principal: " . $dbDefault->getDatabase() . " (Conectado)<br>";
        echo "✅ Universidad: " . $dbUni->getDatabase() . " (Conectado)<br>";
        echo "✅ Prepa: " . $dbPrepa->getDatabase() . " (Conectado)<br>";
        
        // Intento de leer una tabla real de la uni (asumiendo que existe 'alumnos')
        // $query = $dbUni->table('alumnos')->get(5); 
        // dd($query->getResult());

    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage();
    }
}
}
