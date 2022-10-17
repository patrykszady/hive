<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\CompanyEmail;
use App\Models\Expense;
use App\Models\ExpenseReceipts;
use App\Models\Receipt;
use App\Models\ReceiptAccount;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Ddeboer\Imap\Server;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Intervention\Image\Facades\Image;

use File;
use Response;
use Storage;

class ReceiptController extends Controller
{
    //06-21-2022 USING BOTH NEW_OCR AND OCR_SPACE.. why?.
    public function new_orc_status()
    {
        //Show OCR left before buying more
        dd(exec('curl http://api.newocr.com/v1/key/status?key='. env('NEW_OCR_API')));
    }

    public function new_ocr($ocr_filename)
    {
        $ocr_file_stored = realpath(storage_path($ocr_filename));
        $result = exec('curl -H "Expect:" -F file=@'.realpath($ocr_file_stored).' http://api.newocr.com/v1/upload?key='. env('NEW_OCR_API'));
    
        $result = json_decode($result, true);
       
        //Recognize text
        $result_recognized = array();
        for ($page = 1; $page <= $result['data']['pages']; $page++) {
            $result_recognize = exec('curl "http://api.newocr.com/v1/ocr?key=' .  env('NEW_OCR_API') . '&file_id=' . $result['data']['file_id'] . '&page=' . $page . '&lang=eng&psm=6" ');
            $result_recognize = json_decode($result_recognize, true);

            $result_recognized[] = $result_recognize['data']['text'];
        }

        return collect($result_recognized)->implode("\n");
    }

    //middleware
    public function ocr_space($ocr_filename)
    {
        // dd($ocr_filename);
        // file_put_contents(
        //     storage_path($ocr_filename),
        //     file_get_contents($file)
        // );
        $ocr_file_stored = realpath(storage_path($ocr_filename));

        // dd($ocr_file_stored);

        $result = exec('curl -H "apikey:' .  env('OCR_SPACE_API') . '" --form "file=@' . realpath($ocr_file_stored) . '" --form "language=eng" --form "isTable=true" --form "OCREngine=1" --form "scale=true"  https://api.ocr.space/Parse/Image');
        $result = json_decode($result, true);

        $result = $result['ParsedResults'][0]['ParsedText'];

        return $result;
    }

    //Show full-size receipt to anyone with a link | No middleware or policies
    public function original_receipt($receipt)
    {
        $path = storage_path('files/receipts/' . $receipt);

        if(File::extension($receipt) == 'pdf'){
            $response = Response::make(file_get_contents($path), 200, [
                'Content-Type' => 'application/pdf'
            ]);
        } else {
            $response = Image::make($path)->response();
        }

        return $response;
    }

    // public function create_pdf()
    // {
    //     $pdf = SnappyPdf::loadHTML('<h1>Tessfsdfdst</h1>');
    //     $path = storage_path('files/_temp_ocr/1234567.pdf');
    //     $pdf->save($path);
    // }

    public function receipt_email()
    {
        //connect to main server Receipts Cliff Construction. Main Switchboard for all Vendor incoming email receipts.
        try{
            $server = new Server(env('RECEIPTS_SERVER'));
            $connection = $server->authenticate(env('RECEIPTS_EMAIL'), env('RECEIPTS_PASS'));
        }catch(\Ddeboer\Imap\Exception\AuthenticationFailedException $ex){
            //CALL TEXT EMAIL ME IF THIS HAPPENS!
            //add to log!
            dd('server_isnt_connecting');
        }

        //EVERY MESSAGE HAS TO MOVE SOMEWHERE... NONE CAN STAY IN env('RECEIPT_MAILBOX') MAILBOX
        $messages = $connection->getMailbox(env('RECEIPT_MAILBOX'))->getMessages();

        foreach($messages as $message){
            //can this be above this foreach?
            $company_emails =  CompanyEmail::withoutGlobalScopes()->get();
            //catch forwarded messages where From is in database table company_emails

            //if fromEmail isIn companYU_emails-pluck('emails)...
            $from_email_fwd = $message->getHeaders()->get('from')[0]->mailbox . '@' . $message->getHeaders()->get('from')[0]->host;
  
            //if $from_email in $company_emails->email then belongs to a vendor/enduser and is a forwarded email, if not, confinute to see if it's a TO email, elese but in non_existant_from_email in try & catch below.
            $from_email_fwd = $company_emails->where('email', $from_email_fwd)->first();
            if($from_email_fwd){
                $fwd_string = $message->getBodyHtml();
                //create new fucntion $this->forward($message)

                //From
                $re = '/(?<=&lt;)(.+?)&gt/';
                $str = $fwd_string;
                preg_match($re, $str, $matches_from, PREG_OFFSET_CAPTURE, 0);

                $from_email = $matches_from[1][0];

                //Date/Sent
                $re = '/Sent:<\/.>\s(.+?)</m';
                $str = $fwd_string;
                preg_match($re, $str, $matches_from, PREG_OFFSET_CAPTURE, 0);
                $email_date = Carbon::parse($matches_from[1][0])->format('Y-m-d');

                //Subject
                $re = '/Subject:<\/.>\s(.+?)</m';
                $str = $fwd_string;
                preg_match($re, $str, $matches_subject, PREG_OFFSET_CAPTURE, 0);
                $email_subject = html_entity_decode($matches_subject[1][0]);

                //To = $from_email_fwd
                $to_email = strtolower($from_email_fwd->email);
            }else{
                $to_email = strtolower($message->getHeaders()->get('to')[0]->mailbox . '@' . $message->getHeaders()->get('to')[0]->host);
                $from_email = $message->getFrom()->getAddress();
                $email_date = Carbon::parse($message->getDate())->format('Y-m-d');
                $email_subject = $message->getSubject();
            }

            // !! Forwarded messages must be converted by now. !!
            try{  
                if($company_emails->where('email', $to_email)->first()){
                    $company_email = $company_emails->where('email', $to_email)->first();
                }else{
                    throw new \Exception('No email found in company_emails table');
                }                
            }catch(\Exception $e){
                //move to non_existant_from_email
                $message->move($connection->getMailbox(env('RECEIPT_FOLDER') . '/Non_existant_from_email'));
                $connection->expunge();
                continue;
            }

            //where Receipt::from_subject in $email_subject 
            // $receipt_accounts = Receipt::withoutGlobalScopes()->where('from_address', 'like', '%' . 'info@jclicht.com')->get();

            //find the right Receipt:: that belongs to this email.... 
            //find receipt on table that corresponds to $message
            //case for from_type 1, 2, & 3
            $from_type_1 = Receipt::withoutGlobalScopes()->where('from_type', 1)->where('from_address', $from_email)->first();
            $from_type_2 = Receipt::withoutGlobalScopes()->where('from_type', 2)->get();
            $from_type_3 = Receipt::withoutGlobalScopes()->where('from_type', 3)->where('from_address', $from_email)->get();

            // dd(!$from_type_3->isEmpty());
            //The odd order of 1-3-2 is significant. 3 MUST come before 2.
            if(!is_null($from_type_1)){
                $receipt = $from_type_1;
            }elseif(!$from_type_3->isEmpty()){
                foreach($from_type_3 as $type_3){
                    if(strstr($email_subject, $type_3->from_subject)){
                        $receipt = $type_3;
                    }
                }
            }elseif(!$from_type_2->isEmpty()){
                foreach($from_type_2 as $type_2){
                    if(stripos($email_subject, $type_2->from_subject) !== false){
                        $receipt = $type_2;
                    }
                }
            }

            $receipt_account = ReceiptAccount::withoutGlobalScopes()->where('belongs_to_vendor_id', $company_email->vendor_id)->where('vendor_id', $receipt->vendor_id)->first();

            //6-16-2022 combine the 2 below into 1
            try{
                $receipt_account;
            }catch(\Exception $e){
                //move to Remove
                // is $receipt set by now? if not move to REMOVE floder
                $message->move($connection->getMailbox(env('RECEIPT_FOLDER') . '/Remove'));
                $connection->expunge();
                continue;
            }
         
            //NOTE: $receipt MUST be set by now
            try{
                $receipt_account;
            }catch(\Exception $e){
                //move to non_existant
                $message->move($connection->getMailbox(env('RECEIPT_FOLDER') . '/Non_existant'));
                $connection->expunge();
                continue;
            }

            $this->dirty_work($message, $receipt, $receipt_account, $company_email, $connection, $amazon_orders_loop = NULL, $email_date);   
        }
    }

    public function get_message_type($message)
    {
        //CHECK IF EMAIL IS HTML OR PLAIN TEXT
        if($message->getSubtype() == 'HTML'){
            $string = $message->getBodyHtml();
            $message_type = 'HTML';

        } elseif($message->getSubtype() == 'PLAIN') {
            $string = $message->getBodyText();
            $message_type = 'PLAIN';
        } else { //$message->getSubtype() == 'multipart'/ 'ALTERNATIVE'
            foreach($message->getParts() as $part) {
                if($part->getSubtype() == 'HTML'){
                    $string = $message->getBodyHtml();
                    $message_type = 'HTML';
                } elseif($part->getSubtype() == 'PLAIN') {
                    $string = $message->getBodyText(); 
                    $message_type = 'PLAIN';
                } else { //$part->getSubtype() == 'RELATED'
                    foreach($part->getParts() as $part) { 
                        if($part->getSubtype() == 'HTML'){
                            $string = $message->getBodyHtml();
                            $message_type = 'HTML';
                        } elseif($part->getSubtype() == 'PLAIN') {
                            $string = $message->getBodyText(); 
                            $message_type = 'PLAIN';
                        } else { //$part->getSubtype() == 'RELATED'
                            
                        }
                    }
                }
            }
        }
        return array($string, $message_type);
    }

    public function dirty_work($message, $receipt, $receipt_account, $company_email, $connection, $amazon_orders_loop, $email_date)
    {
        //CHECK IF EMAIL IS HTML OR PLAIN TEXT
        $message_type_array = $this->get_message_type($message);
        $message_type = $message_type_array[1];
        $string = $message_type_array[0];

        // print_r($string);  
        // print_r(htmlspecialchars($string));  
        // dd();

        //AFTER MESSAGE TYPE

        //OCR HERE

        //save image to be proccessed
        //ocr pdf or image
        if(isset($receipt->options['ocr'])){
            //OCR SUB FUNCTIONS / ocr_type!!
            if($receipt->options['ocr_type'] == 'image_in_email'){ // Floor and Decor
                //Find Image source URL in email OR PDF OR MAKE PDF TO ORC FROM EMAIL HTML
                $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
                if(preg_match_all("/$regexp/siU", $string, $matches)) {
                    //VIEW ALL EMAIL LINKS
                    foreach($matches[2] as $match){
                        if(strpos($match, $receipt->options['image_extension'])){
                            $img_urls[] = htmlspecialchars_decode($match);
                        }
                    }
                }

                $image_url = $img_urls[0];

                $ocr_path = date('Y-m-d-H-i-s') .'-' . $receipt->id . '.' . $receipt->options['image_extension'];
                $ocr_filename = 'files/_temp_ocr/' . $ocr_path;
                $location = storage_path($ocr_filename);
                Image::make($image_url)->save($location);
                $string = $this->new_ocr($ocr_filename);

            }elseif($receipt->options['ocr_type'] == 'html_to_pdf'){
                try{
                    //REMOVE IMAGES
                    /* $string = preg_replace("/<a.+?href.+?>.+?<\/a>/is","", $string); */
                    //make PDF from HTML
                    $pdf = PDF::loadView('receipts.makePdfReceipt', compact('string', 'message_type'))
                            ->setPaper('a4'); //->setOrientation('portrait')
                    $ocr_path = date('Y-m-d-H-i-s') .'-' . $receipt->id . '.' . $receipt->options['image_extension'];
                    $ocr_filename = 'files/_temp_ocr/' . $ocr_path;
                    $location = storage_path($ocr_filename);
                    $pdf->save($location);
                }catch(\Exception $e){

                }

                $string = $this->new_ocr($ocr_filename);

            //first attached PDF
            }elseif($receipt->options['ocr_type'] == 'pdf_to_text'){
                $attachments = $message->getAttachments();
                if(isset($attachments[0])){
                    $attachment = $attachments[0];
                    $ocr_path = date('Y-m-d-H-i-s') .'-' . $receipt->id . '.pdf';
                    $ocr_filename = 'files/_temp_ocr/' . $ocr_path;
                    file_put_contents(
                        storage_path($ocr_filename),
                        $attachment->getDecodedContent()
                    );

                    $string = $this->new_ocr($ocr_filename);
                    // dd('else if 458');
                }else{
                    //continue + LOG
                }
            }
            // //if isset, $ocr_path, otherwise NULL
            // if(!isset($ocr_path)){
            //     $ocr_path = NULL;
            // }
        }else{
            //remove images
            $string = preg_replace("/<a.+?href.+?>.+?<\/a>/is","", $string);
        }

        // //<--SHOW HTML ALL TEXT
        // print_r(htmlspecialchars($string));  
        // dd();

        if($receipt->options['receipt_start'] !== "0"){
            //include the "receipt_start" text or start receipt_html after the text
            if(isset($receipt->options['receipt_start_offset'])){
                $receipt_start = strpos($string, $receipt->options['receipt_start']) + strlen($receipt->options['receipt_start']);
            }else{
                $receipt_start = strpos($string, $receipt->options['receipt_start']);
            }
        }else{ 
            $receipt_start = 0; 
        }
        // dd($receipt_start);


        if($receipt->options['receipt_end'] !== "0"){
            // store patter in datebase as $receipt->options['receipt_end']
            // $pattern = "/\d{4}\sPRO XTRA SPEND|PRO XTRA SPEND/m";
            // preg_match_all($pattern, $string, $matches, PREG_OFFSET_CAPTURE, $receipt_start);
    

            // $receipt_end = end($matches[0]);
            // dd($receipt_end);
            // dd(preg_replace("/^\d{4}\s/m", "", $receipt->options['po_end']));
            $receipt_end = strpos($string, $receipt->options['receipt_end'], $receipt_start); 
            // dd($receipt_end);
            if($receipt_end != false){
                //REMOVE!
            }elseif($receipt_end == false AND isset($receipt->options['receipt_end_secondary'])){
                //if secndary receipt_end exists (home depot)
                $receipt_end = strpos($string, "REFUND-CUSTOMER COPY", $receipt_start);      
            }else{
                $receipt_end = strlen($string);
            }
        //if receipt_end = null, use last character of $string
        }else{
            $receipt_end = strlen($string);
        }
        // dd($receipt_end);

        $receipt_position = $receipt_end - $receipt_start;
        $receipt_html_main = substr($string, $receipt_start, $receipt_position);

        //PREVIEWS HTML RECEIPT
        // print_r($receipt_html_main);  
        // dd();

        //OCR HTML_TO_PDF AFTER HTML RECEIPT EXTRACTION FROM HTML EMAIL (HOME DEPOT)
        if(isset($receipt->options['ocr'])){
            if($receipt->options['ocr_type'] == 'extract_and_html_to_pdf'){
                //remove images
                $string = preg_replace("/<a.+?href.+?>.+?<\/a>/is","", $receipt_html_main);

                // print_r($string);
                // dd();

                // $this->create_pdf();
                $pdf = SnappyPdf::loadView('livewire.receipts.make_pdf_receipt', compact('string', 'message_type'))->setPaper('a4');
                // $pdf = SnappyPdf::loadHTML('<h1>Tessdfdsft</h1>');
                $ocr_filename = 'files/_temp_ocr/' . date('Y-m-d-H-i-s') . '-' . rand(10,99) . '.' . $receipt->options['image_extension'];
                $location = storage_path($ocr_filename);
                $pdf->save($location);

                $receipt_html_main = $this->new_ocr($ocr_filename);
            }
        }

        //AMAZON MULTIPE ORDERS
        if($receipt->vendor_id == 54 AND $message->getFrom()->getAddress() != 'digital-no-reply@amazon.com' AND !isset($receipt->options['refund'])){
            $po_pos = strpos($receipt_html_main, 'PO#');
            $re = '/Order #/';
            $str = $receipt_html_main;
            preg_match_all($re, $str, $matches, PREG_OFFSET_CAPTURE);

            //find strpos of "PO#" and only get $matches that have a position of less than strpos of PO# ..this equals = 
            foreach($matches[0] as $match){
                if($match[1] < $po_pos){
                    $match_pos[] = $match[1];
                }
            }
            foreach($matches as $key => $match){
                $total_found[] = $match;
            }

            $amazon_orders_count = count($match_pos);

            if($amazon_orders_count == 1){ //OR LESSSS.... just in case!
                //continue
            }else{
                if(isset($amazon_orders_loop)){
                    $order_loop = $amazon_orders_loop + 1;
                } else{
                    $order_loop = 1;
                }

                try{
                    $array_start_string = $amazon_orders_count + $order_loop;

                    //substr html of a single order  .... 
                    $test_string_amazon = substr($receipt_html_main, $total_found[0][$array_start_string - 1][1]);
                }catch(\Exception $e){
                    $amazon_orders_loop = NULL;
                    $mailbox_1 = $connection->getMailbox(env('RECEIPT_FOLDER') . '/Saved');
                    $message->move($mailbox_1);
                    $connection->expunge();      

                    //like continue?!
                    return $this->receipt_email();
                }             
                $next_string = '=======================================================================================';
                $intro_string = substr($receipt_html_main, 0, strpos($receipt_html_main, $next_string));
                $end = strpos($test_string_amazon, $next_string);
                $test_string_amazon = substr($test_string_amazon, 0, $end);

                $receipt_html_main = $intro_string . $test_string_amazon;
                if($amazon_orders_loop == $amazon_orders_count){
                    $amazon_orders_loop = NULL;
                }else{
                    $amazon_orders_loop = $order_loop;
                }
            }
        }

        $receipt_html = preg_replace('/\s+/', '', $receipt_html_main);
        // dd($receipt_html_main);
        $result = htmlspecialchars($receipt_html);
        // print_r($result);
        // dd();
        // dd($result);
        if(isset($receipt->options['invoice_text'])){
            $invoice_text = $receipt->options['invoice_text'];
            $invoice_end = $receipt->options['invoice_end'];
            $invoice_regex = NULL;
            $invoice_string = $this->find_invoice($result, $invoice_text, $invoice_end, $invoice_regex); 
        }elseif(isset($receipt->options['invoice_regex'])){
            $invoice_regex = $receipt->options['invoice_regex'];
            $invoice_text = NULL;
            $invoice_end = NULL;
            $invoice_string = $this->find_invoice($result, $invoice_text, $invoice_end, $invoice_regex); 
        }else{
            $invoice_string = NULL;
        }
        // dd($invoice_string);


        //FIND DATE
        //Need to implement 08/12/2021
        //right now $email_date above
        // $receipt_date = $this->find_date($result);


        //FIND PO
        if(isset($receipt->options['po_text'])){
            $po_text = $receipt->options['po_text'];
            $po_end = $receipt->options['po_end'];
            $po_string = $this->find_po($receipt_html, $po_text, $po_end);

            //need PO not to be a combined string
            //remove and make this more programable.. this is only temp for Home Depot Receipts 1/30/2021
            if($receipt->vendor_id == 8){
                //if last 4 characters = year('Y');
                if(substr($po_string, -4) == date('Y')){
                    $po_string = rtrim($po_string, date('Y'));
                }
            }
            
        }else{
            $po_string = NULL;
        }

        // print_r($receipt_html_main);
        // dd();
        //FIND AMOUNT
        if(isset($receipt->options['refund'])){
            $refund = true;
        }else{
            $refund = NULL;
        }

        if(isset($receipt->options['no_max'])){
            $no_max = true;
        }else{
            $no_max = NULL;
        }

        $amount = $this->find_amount($receipt_html_main, $refund, $no_max, $receipt);
        // dd($amount);

        //if amount not found
        if($amount == false) {
            //move to failed folder
            $message->move($connection->getMailbox(env('RECEIPT_FOLDER') . '/Failed'));
            $connection->expunge();
            //like "continue" but to a previous function that send code here
            return $this->receipt_email();
        }else{
            //if isset, $ocr_path, otherwise NULL
            if(!isset($ocr_path)){
                $ocr_path = NULL;
            }

            //Check if expense already exists
            $duplicates = Expense::
                with('receipts')->
                where('belongs_to_vendor_id', $receipt_account->belongs_to_vendor_id)->
                where('vendor_id', $receipt->vendor_id)->
                where('amount', $amount)->
                whereBetween('date', [
                    Carbon::create($email_date)->subDay()->format('Y-m-d'), Carbon::create($email_date)->addDay()->format('Y-m-d')
                    ])->
                get();

            if(isset($duplicates)){
                foreach($duplicates as $duplicate){
                    //if has/has not receipts.. this is just an extra check for $expenses that have receipts already .. 06/20/2021
                    if($duplicate->receipts->isEmpty() OR $receipt_html_main != $duplicate->receipts->first()->receipt_html){
                        $is_duplicate = TRUE;
                        //add receipts / attachments to $duplicate ($expense) $this->add_attachments
                        $this->add_attachments($message, $expense = $duplicate, $receipt, $receipt_html_main, $string, $message_type, $ocr_path);
                    //where email html is the same as receipts
                    }elseif($receipt_html_main == $duplicate->receipts->first()->receipt_html){
                        //do not add attachment again since it's idenical to the duplicate
                        $is_duplicate = TRUE;
                    }

                    if($is_duplicate == TRUE){
                        //move email to SavedDuplicate without creating new expense
                        $message->move($connection->getMailbox(env('RECEIPT_FOLDER') . '/Duplicate'));
                        $connection->expunge();
                        //like "continue" but to a previous function that send code here
                        return $this->receipt_email();
                    }
                }
            }

            //CREATE NEW Expense
            // $expense->project_id = $receipt_account->project_id; //If PO matches a project, use that project
            // $expense->distribution_id = $receipt_account->distribution_id;
            if(isset($receipt_account->project_id)){
                if($receipt_account->project_id === 0){
                    $receipt_account->project = 0;
                }else{
                    $receipt_account->project = $receipt_account->project_id;
                }
            }elseif(isset($receipt_account->distribution_id)){
                $receipt_account->project = 'D' . $receipt_account->distribution_id;
            }else{

            }
            
            //how to do $expense = Expense::create(); ?!. double check 06/20/2021
            $expense = new Expense;
            $expense->amount = $amount;
            $expense->reimbursment = NULL;
            $expense->project_id = $receipt_account->project;
            $expense->created_by_user_id = 0;//automated
            $expense->date = $email_date; ///carbon + system timezone //get from OCR or EMAIL....FIND DATE above 08/12/2021
            $expense->invoice = $invoice_string;
            $expense->vendor_id = $receipt->vendor_id; //Vendor_id of vendor being Queued 
            $expense->note = $po_string;
            $expense->belongs_to_vendor_id = $company_email->vendor_id;
            $expense->save();
                
            //ADD ATTACHMENTS $this->add_attachments
            $this->add_attachments($message, $expense, $receipt, $receipt_html_main, $string, $message_type, $ocr_path);

            if(isset($amazon_orders_loop)){
                //send back to message with 
                $expense->save(); 
                $this->dirty_work($message, $receipt, $receipt_account, $company_email, $connection, $amazon_orders_loop);
            }else{
                $amazon_orders_loop = NULL;
                $mailbox_1 = $connection->getMailbox(env('RECEIPT_FOLDER') . '/Saved');
                $message->move($mailbox_1);
                $connection->expunge();      

                $expense->save();              
            }
        }   //if amount ISSET and not NULL           
    }//function dirty_work

    public function find_po($result, $po_text, $po_end)
    {
        $total_pos = strpos(strtoupper($result), $po_text); //independent of CAPITAL or lowecase
        if($total_pos == false){
            return NULL;
        }
        //if po_end = "0"..end of receipt_html string
        if($po_end == "0"){
            $end_position = strlen($result);
        }else{
            $end_position = strpos($result, $po_end, $total_pos);
        }
        $po_string = substr($result, $total_pos + strlen($po_text), $end_position - $total_pos - strlen($po_text));
        // dd($po_string);
        return trim($po_string);
    }

    public function find_amount($receipt_html_main, $refund = NULL, $no_max = NULL, $receipt = NULL)
    {
        // print_r($result);
        // dd();
        $re = '/[$?|\s?]\d{1,3}[.]\d{1,2}|[\D]\d{1,3}[,]\d{1,3}[.]\d{1,2}[^\d|^pt|^Z|^I|^@|%]/';
        $str = $receipt_html_main;
        $primary_search = preg_match_all($re, $str, $matches, PREG_OFFSET_CAPTURE);

        //if an amount repeats more than once use that over the LARGEST amount (EG: Groot receipts)

        //if $amount = false (NO $, ect in sting)..look for antythibng like 1000.99 or 1,000.99 (decimales and dots)/.
        //CHRYSLER CAPITAL
        if($primary_search == 0){
            //$re = '/(?(?=[-(])[-(]\d{1,6}[.]\d{1,2}|\d{1,6}[.]\d{1,2})|(?(?=[-(])[-(]\d{1,3}[,]\d{1,3}[.]\d{1,2}|\d{1,3}[,]\d{1,3}[.]\d{1,2})/';
            $re = '/\d{1,6}[.]\d{1,2}|\d{1,3}[,]\d{1,3}[.]\d{1,2}/';
            $secondary_search = preg_match_all($re, $str, $matches, PREG_OFFSET_CAPTURE);
        }

        //HOME DEPOT ONLINE ORDERS ONLY
        if($receipt->id == 13){
            return preg_replace('/[^0-9.]*/', '', $matches[0][count($matches[0]) - 1][0]);
        }       

        foreach($matches[0] as $key => $match)
        {
            $match[0] = str_replace( ',', '', $match[0]); // $match[0] = preg_replace('/[^0-9.]*/', '', $match[0]);
            $match[0] = preg_replace('/[^0-9.]*/', '', $match[0]);
            $max[] = $match[0];

            if(substr($str, $match[1] - 1, 1) == '-' OR substr($str, $match[1] - 1, 1) == '('  OR substr($str, $match[1] - 2, 1) == '-'){
                $match[0] = '-' . $match[0];
                $negative = $key;
            }else{
            }
            $total_found[] = $match[0];
        }

        // dd($total_found);
        $amount_group_count = array_count_values($max);
        // dd($amount_group_count);

        //RIGHT NOW JUST FOR GROOT... AND amazon digital
        if(isset($no_max)){
            $amount = array_keys($amount_group_count, max($amount_group_count));
            return $amount[0];    
        }

        // dd(max($max));
        if(!isset($max)){
            $amount = false;
            return $amount;
        }

        if(isset($negative)){
            $max = $negative;
        }else{
            $max = array_search(max($max), $max);
        }


        // dd($max);
        // dd($total_found);        
        //if first character is "-" or "(", use min, if first char is numeric, user max
        if(is_numeric(substr($total_found[$max], 0, 1))){
            $amount = max($total_found);
        }else{
            $amount = min($total_found); //if negatiove or "()", do the opposite.
        }
        //if Refund isset add "-"
        if(isset($refund)){
            $amount = '-' . $amount;
        }

        return $amount;
    }

    public function find_invoice($result, $invoice_text, $invoice_end, $invoice_regex)
    {
        if(isset($invoice_text)){
            //THIS (IF) SHOULD BE RETIRED ASAP!
            $total_pos = strpos($result, $invoice_text); //independent of CAPITAL or lowecase

            if($total_pos == false){
                return NULL;
            }

            if($invoice_end == "0"){
                $end_position = strlen($result);

            //OLD/RETIRED: AMAZON ONLY..sometimes (Vendor_id 54)
            }elseif($invoice_end[0] == "+"){
                $end_position = strpos($result, $invoice_text) + strlen($invoice_text) + 19;
            }else{
                $end_position = strpos($result, $invoice_end, $total_pos);
            }

            $invoice_string = substr($result, $total_pos + strlen($invoice_text), $end_position - $total_pos - strlen($invoice_text));
            
            return $invoice_string;
        }elseif(isset($invoice_regex)){
            //MOVE ALL NEW RECEIPTS TO BE REGEX BASED!
            $re = $invoice_regex;
            $str = $result;
            $primary_search = preg_match_all($re, $str, $matches, PREG_OFFSET_CAPTURE);

            foreach($matches[0] as $key => $match){
                $invoice_all_match[] = $match[0];
            }

            $invoice_group_count = array_count_values($invoice_all_match);
            $invoice_test_string = array_keys($invoice_group_count, max($invoice_group_count));
            $invoice_string = $invoice_test_string[0];
            return $invoice_string;
        }else{
            return NULL;
        }
    }

    public function add_attachments($message, $expense, $receipt, $receipt_html_main, $string, $message_type, $ocr_path = NULL)
    {
        //GET EMAIL ATTACHMENT..if none....make PDF out of HTML...or save image from email as receipt
        $attachments = $message->getAttachments();
        // dd($attachments);
        if(!empty($attachments)){
            foreach ($attachments as $key => $attachment) {
                $file = $attachment->getDecodedContent();
                $name = date('Y-m-d-H-i-s') . '-' . $expense->id . '-' . $key . '.pdf';
                file_put_contents(
                    storage_path('files/receipts/' . $name),
                    $file
                );

                $expense_receipt = new ExpenseReceipts;
                $expense_receipt->expense_id = $expense->id;
                $expense_receipt->receipt_filename = $name;
                if($key == 0){
                    $expense_receipt->receipt_html = $receipt_html_main;
                }  
                $expense_receipt->save();                        
            }
        }else{
            if(isset($receipt->options['ocr'])){
                //SAVE TEMP OCR RECEIPT TO EXPENSE
                $name = date('Y-m-d-H-i-s') . '-' . $expense->id . '.' . $receipt->options['image_extension'];
                Storage::disk('files')->move('/_temp_ocr/' . $ocr_path, '/receipts/' . $name);
                //delete temp file...
            }else{
                //make PDF from HTML
                $pdf = PDF::loadView('receipts.makePdfReceipt', compact('string', 'message_type'))
                        ->setPaper('a4'); //->setOrientation('portrait')
                $name = date('Y-m-d-H-i-s') . '-' . $expense->id . '.pdf';
                $location = storage_path('files/receipts/' . $name);
                $pdf->save($location);                        
            }

            $expense_receipt = new ExpenseReceipts;
            $expense_receipt->expense_id = $expense->id;
            $expense_receipt->receipt_filename = $name;
            $expense_receipt->receipt_html = $receipt_html_main;
            $expense_receipt->save();   
        }
    }
}
