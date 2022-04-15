<?php

namespace App\Http\Controllers;

use App\Imports\TransactionImport;
//use App\Imports\TransactionsImport;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{

    public function uploadContentWithPackage(Request $request)
    {
        if ($request->file) {
            $file = $request->file;
            $import = new TransactionImport();
//            (new \Maatwebsite\Excel\Excel)->import($import, $request->file);
            Excel::import($import, $request->file);

            return response()->json([
                'message' => $import->data->count() ." records successfully uploaded"
            ]);
        } else {
            throw new \Exception('No file was uploaded', Response::HTTP_BAD_REQUEST);
        }
    }
}
