<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Ilovepdf\Ilovepdf;
use Intervention\Image\Facades\Image;

use File;
use Response;

class VendorDocsController extends Controller
{
    public function find_insurance_data($ocr_path)
    {
        //for uploaded file, find insurance types and Start and End dates.
        $uri = file_get_contents(storage_path($ocr_path));
        //start OCR
        $ch = curl_init();
        $post = '{"urlSource":"' . $uri . '"}';
        $document_model = 'newOct2023';
        $azure_api_key = env('AZURE_RECEIPTS_KEY');
        $azure_api_version = '2023-07-31';

        curl_setopt($ch, CURLOPT_URL, "https://" . env('AZURE_RECEIPTS_URL') . "/formrecognizer/documentModels/" . $document_model . ":analyze?api-version=" . $azure_api_version . " ");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/pdf',
            "Ocp-Apim-Subscription-Key: $azure_api_key",
            ));

        $location_result = curl_exec($ch);
        curl_close($ch);

        $re = '/(\d|\D){8}-(\d|\D){4}-(\d|\D){4}-(\d|\D){4}-(\d|\D){12}/m';
        $str = $location_result;
        preg_match($re, $str, $matches, PREG_OFFSET_CAPTURE, 0);
        $operation_location_id = $matches[0][0];

        //get OCR result
        $result = exec('curl -v -X GET "https://' . env('AZURE_RECEIPTS_URL') . '/formrecognizer/documentModels/' . $document_model . '/analyzeResults/' . $operation_location_id . '?api-version=' . $azure_api_version . '" -H "Ocp-Apim-Subscription-Key: ' . $azure_api_key . '"');
        $result = json_decode($result, true);

        //wait but go as soon as done.
        while($result['status'] == "running" || $result['status'] == "notStarted"){
            sleep(1);
            $result = exec('curl -v -X GET "https://' . env('AZURE_RECEIPTS_URL') . '/formrecognizer/documentModels/' . $document_model . '/analyzeResults/' . $operation_location_id . '?api-version=' . $azure_api_version . '" -H "Ocp-Apim-Subscription-Key: ' . $azure_api_key . '"');
            $result = json_decode($result, true);
        }

        return $result['analyzeResult']['documents'][0]['fields'];
    }

    public function audit_docs_pdf($files)
    {
        $filename = 'audit-' . auth()->user()->vendor->id . '-' . date('Y-m-d-h-m-s');

        //10-15-2023 Create cover page
        ///////cover page here/// use audit view? csv? table?

        $ilovepdf = new Ilovepdf(env('I_LOVE_PDF_PUBLIC'), env('I_LOVE_PDF_SECRET'));
        // Create a new task
        $myTaskMerge = $ilovepdf->newTask('merge');

        // Add files to task for upload
        foreach($files as $key => $file){
            ${'merged_' . $key} = $myTaskMerge->addFile($file);
        }

        // dd($myTaskMerge);
        // $file1 = $myTaskMerge->addFile('/home/vagrant/web/gs/storage/files/vendor_docs/elm_r3.pdf');
        // $file2 = $myTaskMerge->addFile('/home/vagrant/web/gs/storage/files/vendor_docs/elm_r3.pdf');
        // Execute the task
        $myTaskMerge->setOutputFilename($filename);
        $myTaskMerge->execute();
        // $myTaskMerge->download();
        // Download the package files
        $myTaskMerge->download(storage_path('files/vendor_docs/'));

        // //stream/download
        $path = storage_path('files/vendor_docs/' . $filename . '.pdf');
        // $response = Response::make(file_get_contents($path), 200, [
        //     'Content-Type' => 'application/pdf'
        // ]);

        // $response;
        return response()->download($path);
    }

    //1-18-2023 combine the next 2 functions into one. Pass type = original or temp
    //Show full-size receipt to anyone with a link
    // No Middleware or Policies
    //PUBLIC AS FUCK! BE CAREFUL!
    public function document($filename)
    {
        $path = storage_path('files/vendor_docs/' . $filename);

        if(File::extension($filename) == 'pdf'){
            $response = Response::make(file_get_contents($path), 200, [
                'Content-Type' => 'application/pdf'
            ]);
        }else{
            $response = Image::make($path)->response();
        }

        return $response;
    }
}
