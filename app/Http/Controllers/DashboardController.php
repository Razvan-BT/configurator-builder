<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Configurators;
use App\Models\Logs;
use Illuminate\Support\Facades\DB;
use Inertia\Response;
use Inertia\Inertia;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public $UrlShopify = 'https://storage.googleapis.com/custom-product-builder/45402292382/';

    public function showData() {
        $data = Configurators::all();
        $products = [
            'configurators' => $data,
        ];

        // dd($products);
        return Inertia::render('Dashboard', $products);
    }

    public function showLogs() {
        $data = Logs::all();
        $logs = [
            'logs' => $data,
        ];
        return Inertia::render('Logs', $logs);
    }

    /**
     * $name -> who make action
     * $params -> prefer conf data,
     * $action -> what action make it
     * 
     * @params
     */
    public function insertLogs($name, $params, $action, $configurator) {

        $data = array(
            'by_' => $name,
            'params' => $params,
            'action' => $action,
            'configurator' => $configurator
        );

        DB::table('logs')->insert($data);
    }

    public function create_new(Request $request) {
        // template name
        $products = $request->input('templateName');
        $detail = $request->input('templateDetails');
        $addedBy = $request->input('addedBy');
        $shopifyId = $request->input('shopifyId');

        $data = array();
        
        $generatedConfiguratorID = mt_rand(100000000000, 999999999999);
        if(isset($shopifyId) && $shopifyId != '') {
            $shopifyId = intval($shopifyId);
            $response = file_get_contents($this->UrlShopify . $shopifyId . '.json');
            // $jsonData = json_decode($response, JSON_PRETTY_PRINT);
            Storage::disk('products')->put($shopifyId . '.json', $response);

            if(!Configurators::where('configurator_id', '=', $shopifyId)->exists()) {
    
                $data = array('configurator_id' => $shopifyId, 
                    'configurator_title' => $products, 
                    'configurator_detail' => $detail, 
                    'added_by' => $addedBy
                );
                // Configurators::insert();
    
                $action = 'Shopify'; 
                self::insertLogs($addedBy, $response, $action, $shopifyId);
                DB::table('configurators')->insert($data);
            }
        } else {
            // // create new config ID
            if(!Configurators::where('configurator_id', '=', $generatedConfiguratorID)->exists()) {
                $configID = mt_rand(100000000000, 999999999999);
    
                $data = array('configurator_id' => $configID, 
                    'configurator_title' => $products, 
                    'configurator_detail' => $detail, 
                    'added_by' => $addedBy
                );
                // Configurators::insert();
    
                $action = 'New'; 
                self::insertLogs($addedBy, $response, $action, $configID);
                DB::table('configurators')->insert($data);
            }
        }

        return response()->json(['data' => $data]);
    }

    public function view_config($id_c) {
        // dd($id_c);
        $data = Configurators::where('configurator_id', '=', $id_c)->get();
        if(count($data) > 0) {
            return Inertia::render('Configurator', ['ID' => $data[0]['configurator_id'], 'title' => $data[0]['configurator_title']]);
        }
        else abort(404, 'File not found');
    }

    public function save_product(Request $request) { 

        // dd($request);
        $fileData = $request->all();

        // Convert the associative array to a JSON string
        $jsonData = json_encode($fileData['data'], JSON_PRETTY_PRINT);

        Storage::disk('products')->put($fileData['configuratorId'] . '.json', $jsonData);

        $action = 'Edit'; 
        self::insertLogs($fileData['by'], $jsonData, $action, $fileData['configuratorId']);
        
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
        $by = $request->input('by');
        $deleted = DB::table('configurators')->where('configurator_id', '=', $id)->delete();

        $action = 'Delete'; 
        self::insertLogs($by, "", $action, $id);
        
        return response()->json(['status' => 'Configurator '. $id .' was removed successfull!', 'debug' => $deleted]);
    }

    public function duplicateConfigurator(Request $request) {
        $id = $request->input('id');
        $title = $request->input('title');
        $detail = $request->input('detail');
        $by = $request->input('by');
        $path = Storage::disk('public')->path("products/{$id}.json");
        if(file_exists($path)) {
            // citesc JSON
            $fileContent = Storage::disk('public')->get("products/{$id}.json");
            $fileContent = json_decode($fileContent);
            // generez ID de conf nou
            $generatedConfiguratorID = mt_rand(100000000000, 999999999999);
            if(!Configurators::where('configurator_id', '=', $generatedConfiguratorID)->exists()) {
                $configID = mt_rand(100000000000, 999999999999);
                
                $data = array('configurator_id' => $configID, 
                        'configurator_title' => $title . ' - COPY', 
                        'configurator_detail' => (isset($detail) && $detail != '') ? $detail : '-', 
                        'added_by' => $by
                );
    
                DB::table('configurators')->insert($data);

                $jsonData = json_encode($fileContent, JSON_PRETTY_PRINT);
                $action = 'Duplicate '. $id; 
                self::insertLogs($by, $jsonData, $action, $configID);
                Storage::disk('products')->put($configID . '.json', $jsonData);
                
                
                return response()->json(['status' => 1, 'message' => $id . ' cloned successfully!', 'configurator' => $configID]);
            } else return response()->json(['status' => 0, 'message' => 'Error to clone this configurator: conflict.']);
        } else return response()->json(['status' => 0, 'message' => 'Error to reading JSON file path.']);
    }
}
