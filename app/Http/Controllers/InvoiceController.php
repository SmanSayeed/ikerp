<?php

namespace App\Http\Controllers;

use App\Models\SqliteModelPower;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function generateInvoice(Request $request){
        $data = SqliteModelPower::all();

        return response()->json($data);
    }
}
