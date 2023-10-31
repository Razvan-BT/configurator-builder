<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Configurators;
use Illuminate\Support\Facades\DB;
use Inertia\Response;
use Inertia\Inertia;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function showData() {
        $data = Configurators::all();
        $products = [
            'configurators' => $data,
        ];

        // dd($products);
        return Inertia::render('Dashboard', $products);
    }

    public function create_new(Request $request) {
        // template name
        $products = $request->input('templateName');
        $data = array();
        // // create new config ID
        $generatedConfiguratorID = mt_rand(10000000000, 99999999999);
        if(!Configurators::where('configurator_id', '=', $generatedConfiguratorID)->exists()) {
            $configID = mt_rand(10000000000, 99999999999);

            $data = array('configurator_id' => $configID, 'configurator_title' => $products, 'added_by' => 'Razvan');
            // Configurators::insert();

            DB::table('configurators')->insert($data);
        }

        return response()->json(['data' => $data]);
    }

    public function view_config($id_c) {
        // dd($id_c);
        return Inertia::render('Configurator', ['ID' => $id_c]);
    }
}
