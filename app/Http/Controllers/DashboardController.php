<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Configurators;
use Illuminate\Support\Facades\DB;
use Inertia\Response;
use Inertia\Inertia;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

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
        $detail = $request->input('templateDetails');
        $data = array();
        // // create new config ID
        $generatedConfiguratorID = mt_rand(100000000000, 999999999999);
        if(!Configurators::where('configurator_id', '=', $generatedConfiguratorID)->exists()) {
            $configID = mt_rand(100000000000, 999999999999);

            $data = array('configurator_id' => $configID, 
                'configurator_title' => $products, 
                'configurator_detail' => $detail, 
                'added_by' => '-'
            );
            // Configurators::insert();

            DB::table('configurators')->insert($data);
        }

        return response()->json(['data' => $data]);
    }

    public function view_config($id_c) {
        // dd($id_c);
        $data = Configurators::where('configurator_id', '=', $id_c)->get();
        return Inertia::render('Configurator', ['ID' => $data[0]['configurator_id'], 'title' => $data[0]['configurator_title']]);
    }

    public function save_product(Request $request) { 

        // dd($request);
        $fileData = $request->all();

        // Convert the associative array to a JSON string
        $jsonData = json_encode($fileData['data'], JSON_PRETTY_PRINT);

        Storage::disk('products')->put($fileData['configuratorId'] . '.json', $jsonData);

        return response()->json(['message' => "You configurator was saved successfull!"]);
    }

    public function showImage($filename)
    {
        $path = Storage::disk('public')->path("images/{$filename}");

        if (file_exists($path)) {
            $fileContent = Storage::disk('public')->get("images/{$filename}");
            $type = Storage::disk('public')->mimeType("images/{$filename}");

            return response($fileContent, 200)->header('Content-Type', $type);
        } else {
            abort(404, 'File not found');
        }
    }

    public function uploadImage(Request $request)
    {
        // Validate the request
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        try {
            // Store the uploaded image
            $imagePath = $request->file('image')->store('images', 'public');
            $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
    
            return response()->json(['message' => 'Image uploaded successfully', 'image_path' => $imagePath, 'server_host' => $link]);
        } catch (err) {
            return response()->json(['message' => 'Unable to upload the image']);
        }
    }

    public function getProduct_data($product) {
        $path = Storage::disk('public')->path("products/{$product}.json");

        if(file_exists($path)) {
            $fileContent = Storage::disk('public')->get("products/{$product}.json");
            $type = Storage::disk('public')->mimeType("products/{$product}.json");

            return response($fileContent, 200)->header('Content-Type', $type);
        } else return response()->json(['error' => 'not found']);
        
    }

    public function deleteConfigurator(Request $request) {
        $id = $request->input('id');
        $deleted = DB::table('configurators')->where('configurator_id', '=', $id)->delete();
        return response()->json(['status' => 'Configurator '. $id .' was removed successfull!', 'debug' => $deleted]);
    }
}
