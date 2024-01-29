<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

use App\Models\CompanyEmail;
use App\Models\Expense;
use App\Models\ExpenseReceipts;
use App\Models\Receipt;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\ReceiptAccount;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Carbon\CarbonInterval;
use Intervention\Image\Facades\Image;
use Spatie\Browsershot\Browsershot;
use Nesk\Puphpeteer\Puppeteer;
use setasign\Fpdi\Fpdi;

use Microsoft\Graph\Model\Message;
use Microsoft\Graph\Model\Attachment;
use Microsoft\Graph\Model\MailFolder;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Http;
use Microsoft\Graph\Model;
use GuzzleHttp\Client;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Arr;

use File;
// use Response;
use Storage;

class ReceiptController extends Controller
{
    public $ms_graph = NULL;

    public function amazon_login()
    {
        $url = 'https://www.amazon.com/b2b/abws/oauth';

        $params = array(
            'state' => '100',
            'redirect_uri' => env('AMAZON_REDIRECT_URI'),
            'applicationId' => env('AMAZON_APPLICATION_ID')
            );
        header ('Location: '.$url.'?'.http_build_query ($params));
    }

    public function amazon_auth_response()
    {
        if(isset(request()->query()['code'])){
            $code = request()->query()['code'];
        }else{
            ///6-16-2023 return with error ... no code
            return redirect(route('company_emails.index'));
        }

        $guzzle = new Client();

        $url = 'https://api.amazon.com/auth/O2/token';
        $amazon_account_tokens = json_decode($guzzle->post($url, [
            'form_params' => [
                'client_id' => env('AMAZON_CLIENT_ID'),
                'client_secret' => env('AMAZON_CLIENT_SECRET'),
                'code' => $code,
                'redirect_uri' => env('AMAZON_REDIRECT_URI'),
                'grant_type' => 'authorization_code',
            ],
        ])->getBody()->getContents());

        $receipt_account = ReceiptAccount::where('vendor_id', 54)->first();

        //json
        $api_data = array(
            'access_token' => $amazon_account_tokens->access_token,
            'refresh_token' => $amazon_account_tokens->refresh_token,
            //->setTimezone('America/Chicago')
            'expires_in' => Carbon::now()->addMinutes(55)->toIso8601String(),
            'token_type' => $amazon_account_tokens->token_type,
        );

        $api_data = json_encode($api_data);

        $receipt_account->options = $api_data;
        $receipt_account->save();

        return redirect(route('company_emails.index'));
    }

    public function amazon_orders_api()
    {
        ini_set('max_execution_time', '4800');

        $receipt_accounts =  ReceiptAccount::withoutGlobalScopes()->where('vendor_id', 54)->whereNotNull('options->refresh_token')->get();
        //Initialize the Credentials object.
        //access token and secret from AWS
        $credentials = new \Aws\Credentials\Credentials(env('AMAZON_AWS_ACCESS_TOKEN'), env('AMAZON_AWS_SECRET_TOKEN'));

        foreach($receipt_accounts as $receipt_account){
            // dd(Carbon::parse($company_email->options['expires_in'])->setTimezone('America/Chicago'));
            // dd(Carbon::now()->addMinutes(55)->toIso8601String());

            //if NOW  is greater than > expires_in ... get new access_token
            //get new access_token valid for 1 hour and change 'expires_in' to 55 minutes from when submitted
            //ONLY if access token is expired....

            if(Carbon::now() > Carbon::parse($receipt_account->options['expires_in'])){
                $guzzle = new Client();
                $url = 'https://api.amazon.com/auth/O2/token';
                $amazon_account_tokens = json_decode($guzzle->post($url, [
                    'form_params' => [
                        'client_id' => env('AMAZON_CLIENT_ID'),
                        'client_secret' => env('AMAZON_CLIENT_SECRET'),
                        'refresh_token' => $receipt_account->options['refresh_token'],
                        'access_token' => $receipt_account->options['access_token'],
                        'grant_type' => 'refresh_token',
                    ],
                ])->getBody()->getContents());

                $receipt_account->update([
                    'options->expires_in' => Carbon::now()->addMinutes(55)->toIso8601String(),
                    'options->access_token' => $amazon_account_tokens->access_token,
                ]);

                $receipt_account->fresh();
            }

            // Instantiate Client object with api key header.
            $client = new \GuzzleHttp\Client([
                'headers' => [
                    //api.business.amazon.com
                    //na.business-api.amazon.com
                    'host' => 'api.business.amazon.com',
                    'x-amz-access-token' => $receipt_account->options['access_token'],
                    'x-amz-date' => Carbon::now()->toIso8601String(),
                    'user-agent' => 'Hive Production Test/0.2 (Language=PHP;Platform=Linux)',
                    ]
                ]);

            $url = 'https://na.business-api.amazon.com';

            // //FOR TESTING ONLY
            //INDIVIDUAL ORDER
                // $path = '/reports/2021-01-08/orders/112-0325143-0982626/';

                // $params = array(
                //     'includeCharges' => 'true',
                //     'includeLineItems' => 'true',
                //     'includeShipments' => 'true',
                // );
                // // . '?' . http_build_query ($params)
                // $full_url = $url . $path . '?' . http_build_query ($params);

                // $request = new \GuzzleHttp\Psr7\Request('GET', $full_url);
                // //Intialize the signer.
                // $s4 = new \Aws\Signature\SignatureV4("execute-api", "us-east-1");
                // //Build the signed request using the Credentials object. This is required in order to authenticate the call.
                // $signedRequest = $s4->signRequest($request, $credentials);
                // //Send the (signed) API request.
                // $response = $client->send($signedRequest);
                // $result = collect(json_decode($response->getBody()->getContents(), true));

                // dd($result);

            $path = '/reports/2021-01-08/orders/';

            //7-17-2023 find last amazon expenses date
            // '2023-10-14', '2023-10-14'

            //Carbon::today()->subDays(14)->setTimezone('UTC'), Carbon::today()->setTimezone('UTC')
            $dates = CarbonPeriod::create(Carbon::today()->subDays(14)->setTimezone('UTC'), Carbon::today()->setTimezone('UTC'));
            foreach($dates as $date){
                $today = $date;

                $params = array(
                    'startDate' => $today->startOfDay()->toIso8601String(),
                    'endDate' => $today->endOfDay()->toIso8601String(),
                    'includeCharges' => 'true',
                    'includeLineItems' => 'true',
                    'includeShipments' => 'true',
                );

                $full_url = $url . $path . '?' . http_build_query ($params);
                $request = new \GuzzleHttp\Psr7\Request('GET', $full_url);
                //Intialize the signer.
                $s4 = new \Aws\Signature\SignatureV4("execute-api", "us-east-1");
                //Build the signed request using the Credentials object. This is required in order to authenticate the call.
                $signedRequest = $s4->signRequest($request, $credentials);
                //Send the (signed) API request.
                $response = $client->send($signedRequest);

                $orders = collect(json_decode($response->getBody()->getContents(), true)['orders']);
                // dd($orders);
                foreach($orders as $key => $order){
                    //->setTimezone('America/Chicago')
                    $order_date = Carbon::parse($order['orderDate'])->setTimezone('America/Chicago')->format('Y-m-d');

                    //check for expense duplicates
                    $duplicates =
                        Expense::
                            withoutGlobalScopes()->
                            where('belongs_to_vendor_id', $receipt_account->belongs_to_vendor_id)->
                            where('vendor_id', 54)-> //54 = AMAZON
                            // whereNull('deleted_at')->
                            where('invoice', $order['orderId'])->
                            // where('amount', $order['orderNetTotal']['amount'])->
                            where('amount', 'NOT LIKE', '-%')->
                            where('date', $order_date)->
                            get();

                    // dd($duplicates);
                    //7-17-2023 duplicate by Invoice/ Order # only... see if Order status changed
                    if($duplicates->isEmpty()){
                        //create expense Model
                        //CREATE expense
                        $expense = Expense::create([
                            'amount' => $order['orderNetTotal']['amount'],
                            'date' => $order_date,
                            'project_id' => $receipt_account->project_id,
                            'distribution_id' => $receipt_account->distribution_id,
                            'created_by_user_id' => 0, //automated
                            'invoice' => $order['orderId'],
                            'vendor_id' => 54, //54 = AMAZON
                            'note' => NULL,
                            'belongs_to_vendor_id' => $receipt_account->belongs_to_vendor_id
                        ]);
                    }else{
                        $expense = $duplicates->first();

                        if($order['orderStatus'] == 'CANCELLED'){
                            $expense->amount = 0.00;
                            $expense->save();

                            $transactions = Transaction::withoutGlobalScopes()->where('expense_id', $expense->id)->get();
                            foreach($transactions as $transaction){
                                $transaction->expense_id = NULL;
                                $transaction->save();
                            }

                            $expense->delete();
                        }else{
                            if($expense->amount != $order['orderNetTotal']['amount']){
                                $expense->amount = $order['orderNetTotal']['amount'];
                                $expense->save();
                            }
                        }

                        //CHARGES
                        $charges = [];
                        foreach($order['charges'] as $key => $charge){
                            $charges[$key]['transactionDate'] = $charge['transactionDate'];
                            $charges[$key]['transactionId'] = $charge['transactionId'];
                            $charges[$key]['amount'] = $charge['amount']['amount'];
                            $charges[$key]['paymentInstrumentLast4Digits'] = $charge['paymentInstrumentLast4Digits'];
                        }

                        $receipt = $expense->receipts()->latest()->first();
                        $items = $receipt->receipt_items;
                        $items->charges = $charges;

                        $receipt->receipt_items = json_encode($items);
                        $receipt->save();

                        continue;
                    }

                    //only runs/continues below IF
                    //$expense makes it here / doenst "continue" in the else above

                    //create expense_receipt_data
                    //ITEMS
                    $items = [];
                    foreach($order['lineItems'] as $key => $item){
                        $items[$key]['valueObject']['Price']['valueNumber'] = $item['purchasedPricePerUnit']['amount'];
                        $items[$key]['valueObject']['Quantity']['valueNumber'] = $item['itemQuantity'];
                        $items[$key]['valueObject']['TotalPrice']['valueNumber'] = $item['itemSubTotal']['amount'];
                        $items[$key]['valueObject']['Description']['valueString'] = $item['title'];
                        $items[$key]['valueObject']['ProductCode']['valueString'] = $item['asin'];
                    }

                    //CHARGES
                    $charges = [];
                    foreach($order['charges'] as $key => $charge){
                        $charges[$key]['transactionDate'] = $charge['transactionDate'];
                        $charges[$key]['transactionId'] = $charge['transactionId'];
                        $charges[$key]['amount'] = $charge['amount']['amount'];
                        $charges[$key]['paymentInstrumentLast4Digits'] = $charge['paymentInstrumentLast4Digits'];
                    }

                    //items array!
                    $expense_receipt_data = [
                        'items' => $items,
                        'total' => $order['orderNetTotal']['amount'],
                        'subtotal' => $order['orderSubTotal']['amount'],
                        'total_tax' => $order['orderTax']['amount'],
                        'invoice_number' => $order['orderId'],
                        'purchase_order' => $order['purchaseOrderNumber'],
                        'transaction_date' => [
                            'valueDate' => $order_date,
                        ],
                        'charges' => $charges,
                    ];

                    ExpenseReceipts::create([
                        'expense_id' => $expense->id,
                        'receipt_html' => NULL,
                        'receipt_items' => json_encode($expense_receipt_data),
                        'receipt_filename' => NULL,
                    ]);
                }
                // sleep(1);
            }

            $path = '/reconciliation/2021-01-08/transactions';
            $params = array(
                'feedStartDate' => Carbon::now()->subDays(60)->toIso8601String(),
                'feedEndDate' => Carbon::now()->toIso8601String()
                );

            $full_url = $url . $path . '?' . http_build_query ($params);
            $request = new \GuzzleHttp\Psr7\Request('GET', $full_url);
            //Intialize the signer.
            $s4 = new \Aws\Signature\SignatureV4("execute-api", "us-east-1");
            //Build the signed request using the Credentials object. This is required in order to authenticate the call.
            $signedRequest = $s4->signRequest($request, $credentials);
            //Send the (signed) API request.
            $response = $client->send($signedRequest);

            $transactions = collect(json_decode($response->getBody()->getContents(), true));
            $transactions = collect($transactions['transactions'])->where('transactionType', '!=', 'CHARGE');

            foreach($transactions as $transaction){
                $order_date = Carbon::create($transaction['transactionDate'])->format('Y-m-d');
                $order_id = $transaction['transactionLineItems'][0]['orderId'];
                // $invoice_numbers = [];
                // foreach($transaction['transactionLineItems'] as $key => $line_item){
                //     $invoice_numbers[$key]['orderId'] = $line_item['orderId'];
                //     $invoice_numbers[$key]['orderLineItemId'] = $line_item['orderLineItemId'];
                //     $invoice_numbers[$key]['shipmentId'] = $line_item['shipmentId'];
                // }
                // dd($invoice_numbers);
                //check for expense duplicates
                // dd($transaction);

                $duplicates =
                    Expense::
                        where('belongs_to_vendor_id', $receipt_account->belongs_to_vendor_id)->
                        where('vendor_id', 54)-> //54 = AMAZON
                        whereNull('deleted_at')->
                        where('invoice', $order_id)->
                        // where('amount', $order['orderNetTotal']['amount'])->
                        where('amount', 'LIKE', '-%')->
                        where('date', $order_date)->
                        get();

                //7-17-2023 duplicate by Invoice/ Order # only... see if Order status changed
                if($duplicates->isEmpty()){
                    //create expense Model
                    //CREATE expense
                    $expense = Expense::create([
                        'amount' => '-' . $transaction['amount']['amount'],
                        'date' => $order_date,
                        'project_id' => $receipt_account->project_id,
                        'distribution_id' => $receipt_account->distribution_id,
                        'created_by_user_id' => 0, //automated
                        'invoice' => $order_id,
                        'vendor_id' => 54, //54 = AMAZON
                        'note' => NULL,
                        'belongs_to_vendor_id' => $receipt_account->belongs_to_vendor_id
                    ]);

                    //find associated expense and link
                    $associated =
                        Expense::
                            where('belongs_to_vendor_id', $receipt_account->belongs_to_vendor_id)->
                            where('vendor_id', 54)-> //54 = AMAZON
                            whereNull('deleted_at')->
                            where('invoice', $order_id)->
                            // where('amount', $order['orderNetTotal']['amount'])->
                            where('amount', 'NOT LIKE', '-%')->
                            // where('date', $order_date)->
                            first();

                    if($associated){
                        $associated->parent_expense_id = $expense->id;
                        $associated->save();
                    }

                    //create expense_receipt_data
                    //ITEMS
                    $items = [];
                    foreach($transaction['transactionLineItems'] as $key => $item){
                        $items[$key]['valueObject']['Price']['valueNumber'] = $item['principalAmount']['amount'];
                        $items[$key]['valueObject']['Quantity']['valueNumber'] = $item['itemQuantity'];
                        $items[$key]['valueObject']['TotalPrice']['valueNumber'] = $item['totalAmount']['amount'];
                        $items[$key]['valueObject']['Description']['valueString'] = $item['productTitle'];
                        $items[$key]['valueObject']['ProductCode']['valueString'] = $item['asin'];
                    }

                    //CHARGES
                    $charges = [];

                    $charges[0]['transactionDate'] = $order_date;
                    $charges[0]['transactionId'] = $transaction['transactionId'];
                    $charges[0]['amount'] = '-' . $transaction['amount']['amount'];
                    $charges[0]['paymentInstrumentLast4Digits'] = $transaction['paymentInstrumentLast4Digits'];

                    //items array!
                    $expense_receipt_data = [
                        'items' => $items,
                        'total' => '-' . $transaction['amount']['amount'],
                        'subtotal' => NULL,
                        'total_tax' => NULL,
                        'invoice_number' => $order_id,
                        'purchase_order' => $transaction['transactionLineItems'][0]['purchaseOrderNumber'],
                        'transaction_date' => [
                            'valueDate' => $order_date,
                        ],
                        'charges' => $charges,
                    ];

                    ExpenseReceipts::create([
                        'expense_id' => $expense->id,
                        'receipt_html' => NULL,
                        'receipt_items' => json_encode($expense_receipt_data),
                        'receipt_filename' => NULL,
                    ]);
                }else{
                    // $expense = $duplicates->first();

                    // if($expense->amount != '-' . $transaction['amount']['amount']){
                    //     $expense->amount = '-' . $transaction['amount']['amount'];
                    //     $expense->save();
                    // }else{

                    // }
                    continue;
                }
            }

            sleep(1);
            // usleep(500000);
        }
    }

    public function ms_graph_login()
    {
        // $guzzle = new Client();
        // $url = 'https://login.microsoftonline.com/' . env('TENANT_ID') . '/oauth2/v2.0/token';
        // $token = json_decode($guzzle->post($url, [
        //     'form_params' => [
        //         'client_id' => env('CLIENT_ID'),
        //         'client_secret' => env('SECRET_ID'),
        //         // 'response_type' => 'token',
        //         // 'redirect_uri' => 'https://hive.test/receipts/ms_graph_auth_response',
        //         'scope' => 'https://graph.microsoft.com/.default',
        //         'grant_type' => 'client_credentials',
        //     ],
        // ])->getBody()->getContents());
        // $accessToken = $token->access_token;

        // dd($accessToken);

        // $guzzle = new Client();
        $url = 'https://login.microsoftonline.com/' . env('MS_GRAPH_TENANT_ID') . '/oauth2/v2.0/authorize';

        $params = array(
            'client_id' => env('MS_GRAPH_CLIENT_ID'),
            'redirect_uri' => env('MS_GRAPH_REDIRECT_URI'),
            //token
            'response_type' => 'code',
            'response_mode' => 'query',
            'scope' => env('MS_GRAPH_USER_SCOPES'),
            'state' => '12345');
        header ('Location: '.$url.'?'.http_build_query ($params));
        // $accessToken = $token->access_token;
    }

    public function google_cloud_client()
    {
        $client = new \Google_Client();
        // load our config.json that contains our credentials for accessing google's api as a json string
        // $configJson = storage_path('files/client_secret.json');

        $applicationName = 'hivecontractors';
        $client_id = env('GOOGLE_CLOUD_CLIENT_ID');
        $project_id = 'hive-contractors';
        $auth_uri = 'https://accounts.google.com/o/oauth2/auth';
        $token_uri = 'https://oauth2.googleapis.com/token';
        $auth_provider_x509_cert_url = 'https://www.googleapis.com/oauth2/v1/certs';
        $client_secret = env('GOOGLE_CLOUD_CLIENT_SECRET');
        
        if(env('APP_ENV') == 'production'){
            $redirect_uris = ['https://dashboard.hive.contractors/receipts/google_cloud_auth_response'];
        }else{
            $redirect_uris = ['http://localhost:8000/receipts/google_cloud_auth_response'];
        }

        $client_credentials = ["web" => [
            "client_id" => $client_id,
            "project_id" => $project_id,
            "auth_uri" => $auth_uri,
            "token_uri" => $token_uri,
            "auth_provider_x509_cert_url" => $auth_provider_x509_cert_url,
            "client_secret" => $client_secret,
            "redirect_uris" => $redirect_uris
        ]];
        // create the client
        $client = new \Google_Client();
        $client->setApplicationName($applicationName);

        // $client->setAuthConfig($configJson);
        $client->setAuthConfig($client_credentials);

        $client->setAccessType('offline'); // necessary for getting the refresh token
        $client->setApprovalPrompt('force'); // necessary for getting the refresh token
        // scopes determine what google endpoints we can access. keep it simple for now.
        $client->setScopes(
            [
                \Google\Service\Oauth2::USERINFO_PROFILE,
                \Google\Service\Oauth2::USERINFO_EMAIL,
                \Google\Service\Oauth2::OPENID,
                \Google\Service\Gmail::GMAIL_MODIFY // allows reading of gmail messages
            ]
        );
        $client->setIncludeGrantedScopes(true);

        return $client;
    }

    public function google_cloud_login()
    {
        $client = $this->google_cloud_client();
        //Generate the url at google we redirect to
        //https://console.cloud.google.com/apis/credentials/oauthclient
        $authUrl = $client->createAuthUrl();

        return redirect($authUrl);
    }

    public function google_cloud_auth_response()
    {
        if(isset(request()->query()['code'])){
            $code = request()->query()['code'];
        }else{
            return redirect(route('company_emails.index'));
        }

        $client = $this->google_cloud_client();

        $accessToken = $client->fetchAccessTokenWithAuthCode($code);
        $client->setAccessToken($accessToken);

        $oauth2 = new \Google\Service\Oauth2($client);
        $user_info = $oauth2->userinfo->get();

        $existing_company_emails = CompanyEmail::withoutGlobalScopes()->where('email', $user_info->email)->get();
        if(!$existing_company_emails->isEmpty()){
            //return back with error
            session()->flash('error', 'Email already connected.');
            if(auth()->user()->vendor->registration['registered'] == FALSE){
                return redirect(route('vendor_registration', auth()->user()->vendor));
            }else{
                return redirect(route('company_emails.index'));
            }
        }

        $service = new \Google\Service\Gmail($client);

        //create HIVE folder in mailbox...
        try{
            // Create a new label object
            $label = new \Google\Service\Gmail\Label();
            $label->setName('HIVE_CONTRACTORS_RECEIPTS');
            $label->setLabelListVisibility('labelShow');
            $label->setMessageListVisibility('show');

            $results = $service->users_labels->create('me', $label);
            $api_data['hive_folder'] = $results->getId();
        }catch (\Exception $e){
            //409 = A folder with the specified name already exists.
            if($e->getCode() == 409){
                //get ID of the existing folder...
                $results = $service->users_labels->listUsersLabels('me');

                // Check if there is a label with the given name
                $label_id = null;
                foreach ($results->getLabels() as $label) {
                if($label->getName() == 'HIVE_CONTRACTORS_RECEIPTS'){
                        // Get the label ID
                        $label_id = $label->getId();
                        break;
                    }
                }

                if($label_id != NULL){
                    $api_data['hive_folder'] = $label_id;
                }else{
                    abort(404);
                }
            }else{
                abort(404);
            }
        }

        //create sub-HIVE folders in HIVE_CONTRACTORS_RECEIPTS mailbox...
        $sub_folders = ['Saved', 'Duplicate', 'Error', 'Add', 'Retry', 'Test'];
        foreach($sub_folders as $folder){
            try{
                // Create the sublabel
                $subLabel = new \Google\Service\Gmail\Label();
                $subLabel->setName('HIVE_CONTRACTORS_RECEIPTS/' . $folder);
                $subLabel->setLabelListVisibility('labelHide');
                $subLabel->setMessageListVisibility('show');
                $results = $service->users_labels->create('me', $subLabel);
                $subLabelId = $results['id'];

                $api_data['hive_folder_' . strtolower($folder)] = $subLabelId;
            }catch (\Exception $e){
                //409 = A folder with the specified name already exists.
                if($e->getCode() == 409){
                    //get ID of the existing folder...
                    $results = $service->users_labels->listUsersLabels('me');
                    // Check if there is a label with the given name
                    $label_id = null;
                    foreach ($results->getLabels() as $label) {
                    if($label->getName() == 'HIVE_CONTRACTORS_RECEIPTS/' . $folder){
                            // Get the label ID
                            $label_id = $label->getId();
                            break;
                        }
                    }

                    if($label_id != NULL){
                        $api_data['hive_folder_' . strtolower($folder)] = $label_id;
                    }else{
                        abort(404);
                    }
                }else{
                    abort(404);
                }
            }
        }

        //json
        $api_data = array_merge($api_data, array(
            'provider' => 'gmail',
            'access_token' => $client->getAccessToken()['access_token'],
            'refresh_token' => $client->getAccessToken()['refresh_token'],
            'user_id' => $user_info->id,
        ));

        $api_data = json_encode($api_data);

        CompanyEmail::create([
            'email' => $user_info->email,
            'vendor_id' => auth()->user()->vendor->id,
            'api_json' => $api_data,
        ]);

        if(auth()->user()->vendor->registration['registered'] == FALSE){
            return redirect(route('vendor_registration', auth()->user()->vendor));
        }else{
            return redirect(route('company_emails.index'));
        }
    }

    public function ms_graph_auth_response()
    {
        if(isset(request()->query()['code'])){
            $code = request()->query()['code'];
        }else{
            return redirect(route('company_emails.index'));
        }

        $guzzle = new Client();
        $url = 'https://login.microsoftonline.com/' . env('MS_GRAPH_TENANT_ID') . '/oauth2/v2.0/token';
        $email_account_tokens = json_decode($guzzle->post($url, [
            'form_params' => [
                'client_id' => env('MS_GRAPH_CLIENT_ID'),
                'scope' => env('MS_GRAPH_USER_SCOPES'),
                'code' => $code,
                'redirect_uri' => env('MS_GRAPH_REDIRECT_URI'),
                'grant_type' => 'authorization_code',
                'client_secret' => env('MS_GRAPH_SECRET_ID'),
            ],
        ])->getBody()->getContents());

        $access_token = $email_account_tokens->access_token;

        $graph = new Graph();
        $graph->setAccessToken($access_token);

        $user = $graph->createRequest("GET", "/me")
            ->setReturnType(Model\User::class)
            ->execute();

        $existing_company_emails = CompanyEmail::withoutGlobalScopes()->where('email', $user->getMail())->get();
        if(!$existing_company_emails->isEmpty()){
            //return back with error
            session()->flash('error', 'Email already connected.');
            if(auth()->user()->vendor->registration['registered'] == FALSE){
                return redirect(route('vendor_registration', auth()->user()->vendor));
            }else{
                return redirect(route('company_emails.index'));
            }
        }
        //create HIVE folder in mailbox...
        //HIVE_CONTRACTORS_RECEIPTS
        try{
            $create_hive_folder = $graph->createRequest("POST", "/users/" . $user->getId() . "/mailFolders")
                ->attachBody(
                    array(
                        'displayName' => 'HIVE_CONTRACTORS_RECEIPTS',
                        'isHidden' => false,
                        )
                    )
                ->setReturnType(MailFolder::class)
                ->execute();
            $api_data['hive_folder'] = $create_hive_folder->getId();
        }catch (\Exception $e){
            //409 = A folder with the specified name already exists.
            if($e->getCode() == 409){
                //get ID of the existing folder...
                $user_hive_folder = $graph->createCollectionRequest("GET", "/me/mailFolders?filter=displayName eq 'HIVE_CONTRACTORS_RECEIPTS'&expand=childFolders")
                ->setReturnType(MailFolder::class)
                ->execute();

                $api_data['hive_folder'] = $user_hive_folder[0]->getId();
            }else{
                abort(404);
            }
        }

        //create sub-HIVE folders in HIVE_CONTRACTORS_RECEIPTS mailbox...
        $sub_folders = ['Saved', 'Duplicate', 'Error', 'Add', 'Retry', 'Test'];
        foreach($sub_folders as $folder){
            try{
                $create_hive_folder = $graph->createRequest("POST", "/users/" . $user->getId() . "/mailFolders/" . $api_data['hive_folder'] . "/childFolders")
                ->attachBody(
                    array(
                        'displayName' => $folder,
                        'isHidden' => false,
                        )
                    )
                ->setReturnType(MailFolder::class)
                ->execute();

                $api_data['hive_folder_' . strtolower($folder)] = $create_hive_folder->getId();
            }catch (\Exception $e){
                //409 = A folder with the specified name already exists.
                if($e->getCode() == 409){
                    //get ID of the existing folder...
                    $user_hive_folder = $graph->createCollectionRequest("GET", "/me/mailFolders/" . $api_data['hive_folder'] . "/childFolders?filter=displayName eq '" . $folder ."'")
                    ->setReturnType(MailFolder::class)
                    ->execute();

                    $api_data['hive_folder_' . strtolower($folder)] = $user_hive_folder[0]->getId();
                }else{
                    abort(404);
                }
            }
        }

        //json
        $api_data = array_merge($api_data, array(
            'provider' => 'outlook',
            'access_token' => $access_token,
            'refresh_token' => $email_account_tokens->refresh_token,
            'user_id' => $user->getId(),
        ));

        $api_data = json_encode($api_data);

        //6-8-2023 Unique only
        CompanyEmail::create([
            'email' => $user->getMail(),
            'vendor_id' => auth()->user()->vendor->id,
            'api_json' => $api_data,
        ]);

        if(auth()->user()->vendor->registration['registered'] == FALSE){
            return redirect(route('vendor_registration', auth()->user()->vendor));
        }else{
            return redirect(route('company_emails.index'));
        }
    }

    public function auto_receipt()
    {
        //09/22/2023 EACH FILE SHOULD BE UPLOADED TO ONEDRIVE AND NOT VIA EMAIL!
            //get receipt from email/onedrive
        $company_emails =  CompanyEmail::withoutGlobalScopes()->whereNotNull('api_json->user_id')->get();
        foreach($company_emails as $company_email){
            $email_vendor = $company_email->vendor;
            // $email_vendor = $company_email->vendor()->withoutGlobalScopes()->first();
            $email_vendor_bank_account_ids = $email_vendor->bank_accounts->pluck('id');

            //check if access_token is expired, if so get new access_token and refresh_token
            $guzzle = new Client();
            $url = 'https://login.microsoftonline.com/' . env('MS_GRAPH_TENANT_ID') . '/oauth2/v2.0/token';
            $email_account_tokens = json_decode($guzzle->post($url, [
                'form_params' => [
                    'client_id' => env('MS_GRAPH_CLIENT_ID'),
                    'scope' => env('MS_GRAPH_USER_SCOPES'),
                    'refresh_token' => $company_email->api_json['refresh_token'],
                    'redirect_uri' => env('MS_GRAPH_REDIRECT_URI'),
                    'grant_type' => 'refresh_token',
                    'client_secret' => env('MS_GRAPH_SECRET_ID'),
                ],
            ])->getBody()->getContents());

            //json
            $api_data = $company_email->api_json;
            $api_data['access_token'] = $email_account_tokens->access_token;
            $api_data['refresh_token'] = $email_account_tokens->refresh_token;
            $api_data = json_encode($api_data);

            $company_email->update([
                'api_json' => $api_data,
            ]);

            $this->ms_graph = new Graph();
            $this->ms_graph->setAccessToken($company_email->api_json['access_token']);

            // $receipt_folder = $this->ms_graph->createRequest("GET", "/me/drive/root/children")
            //     ->addHeaders(["Content-Type" => "application/json"])
            //     ->setReturnType(DriveItem::class)
            //     ->execute();

            // dd($receipt_folder);

            //6-12-2023 6-27-2023 exclude ones already read ... save $message->getId() to a database...
            $receipts_emails =
                $this->ms_graph
                    ->createCollectionRequest("GET",
                    "/me/mailFolders/inbox/messages?filter=from/emailAddress/address eq 'noreply@print.epsonconnect.com' and subject eq 'Receipt Scans'")
                    ->setReturnType(Message::class)
                    ->execute();

            foreach($receipts_emails as $index => $message){
                if($message->getHasAttachments()){
                    $attachments =
                        $this->ms_graph->createRequest("GET", "/me/messages/" . $message->getId() . "/attachments")
                        ->setReturnType(Attachment::class)
                        ->execute();

                    foreach($attachments as $loop => $attachment_found){
                        //09/22/2023 EACH FILE SHOULD BE UPLOADED TO ONEDRIVE AND NOT VIA EMAIL!
                        //if is for testing only...
                        // if($loop == 2 - 1){
                            $attachment = $attachment_found;

                            $ocr_filename = date('Y-m-d-H-i-s') . '-' . rand(10,99) . '.pdf';
                            $content_bytes = array_values((array) $attachment)[0]['contentBytes'];
                            $contents = base64_decode($content_bytes);
                            Storage::disk('files')->put('/_temp_ocr/' . $ocr_filename, $contents);

                            $ocr_path = 'files/_temp_ocr/' . $ocr_filename;
                            $location = storage_path($ocr_path);

                            $post_data = file_get_contents($location);
                            $doc_type = '.pdf';

                            //if $width under 180mm($width), prebuilt-receipt, otherwise if wider, use prebuilt-invoice
                            $pdf = new Fpdi();
                            $pdf->setSourceFile($location);
                            $pageId = $pdf->importPage(1);
                            //unit = mm
                            $width = $pdf->getTemplateSize($pageId)['width'];

                            //$document_model = based on file dimensions. receipt vs invoice
                            if($width < 180 ){
                                $document_model = 'prebuilt-receipt';
                            }else{
                                $document_model = 'prebuilt-invoice';
                            }

                            $ocr_receipt_extracted = $this->azure_receipts($post_data, $doc_type, $document_model);
                            // dd($ocr_receipt_extracted);

                            //pass receipt info from ocr_receipt_extracted to ocr_extract method
                            $ocr_receipt_data = $this->ocr_extract($ocr_receipt_extracted);
                            // dd($ocr_receipt_data);

                            if(isset($ocr_receipt_data['error']) && $ocr_receipt_data['error'] == TRUE){
                                //if error move this single $attachment to a folder for debug...
                                //move _temp_ocr file to /files/receipts
                                Storage::disk('files')->move('/_temp_ocr/' . $ocr_filename, '/auto_receipts_failed/' . $ocr_filename);
                                continue;
                            }

                            // match Vendor to MerchantName ... MerchantName = transaction_description ...
                            $start_date = Carbon::parse($ocr_receipt_data['fields']['transaction_date'])->subDays(4)->format('Y-m-d');
                            $end_date = Carbon::parse($ocr_receipt_data['fields']['transaction_date'])->addDays(5)->format('Y-m-d');

                            //find existing transaction
                            $transactions =
                                Transaction::
                                    whereIn('bank_account_id', $email_vendor_bank_account_ids)
                                    ->whereNull('expense_id')
                                    ->whereNull('check_number')
                                    ->whereNull('deposit')
                                    ->where('amount', $ocr_receipt_data['fields']['total'])
                                    ->whereBetween('transaction_date', [$start_date, $end_date])
                                    ->get();
                            // dd($transactions);

                            //create expense with or without Vendor_id and attach receipt
                            if($transactions->count() == 1){
                                //create expense with $transaction->vendor_id and associate with this transaction
                                $transaction = $transactions->first();
                                // 12/13/23 WHYY if greather than ??
                            }elseif($transactions->count() > 1){
                                $transaction = NULL;

                            //find amount in string .. like partial receipts / multiple transactions per expense
                            }else{
                                //no merchant ... filter
                                $exisitng_transactions =
                                    Transaction::
                                        whereIn('bank_account_id', $email_vendor_bank_account_ids)
                                        ->whereNull('expense_id')
                                        ->whereNull('check_number')
                                        ->whereNull('deposit')
                                        // ->where('amount', $ocr_receipt_data['fields']['total'])
                                        ->whereBetween('transaction_date', [$start_date, $end_date])
                                        ->get();

                                // dd($exisitng_transactions);
                                $vendor_found_transactions = collect();
                                $receipt_merchant_name = explode(",", $ocr_receipt_data['fields']['merchant_name'])[0];

                                foreach($exisitng_transactions as $exisitng_transaction){
                                    //either by vendor or by amount found in receipt scan text
                                    if(strpos($exisitng_transaction->plaid_merchant_name, $receipt_merchant_name) !== FALSE){
                                        //add this to vendor_found_transactions
                                        $vendor_found_transactions->push($exisitng_transaction);
                                    }
                                }

                                if(!$vendor_found_transactions->isEmpty()){
                                    //closest date dateDiff
                                    foreach($vendor_found_transactions as $vendor_found_transaction){
                                        $str = $ocr_receipt_data['content'];
                                        $re = '/\\D' . str_replace(".", "\.", trim($vendor_found_transaction->amount, '-')) . '/m';
                                        preg_match($re, $str, $matches, PREG_OFFSET_CAPTURE, 0);

                                        if(!empty($matches)){
                                            $transaction = $vendor_found_transaction;
                                            $ocr_receipt_data['fields']['total'] = $transaction->amount;
                                        }
                                    }

                                    if(!isset($transaction)){
                                        $transaction = NULL;
                                    }

                                    $vendor = NULL;
                                }else{
                                    //find vendor that matches merchant_name
                                    $transaction = NULL;
                                    $vendor = Vendor::withoutGlobalScopes()->where('business_type', 'Retail')->where('business_name', 'LIKE', $receipt_merchant_name)->first();
                                }
                            }

                            // dd($transaction);

                            $duplicate_start_date = Carbon::parse($ocr_receipt_data['fields']['transaction_date'])->subDays(1)->format('Y-m-d');
                            $duplicate_end_date = Carbon::parse($ocr_receipt_data['fields']['transaction_date'])->addDays(4)->format('Y-m-d');
                            //find duplicate expenses
                            $duplicates =
                                Expense::
                                    where('belongs_to_vendor_id', $email_vendor->id)->
                                    //08-02-2023 when merchant name/ vendor_id isset... check vendor_id on expense table otherwise dont
                                    // where('vendor_id', $receipt->vendor_id)->
                                    with('receipts')->
                                    whereNull('deleted_at')->
                                    where('amount', $ocr_receipt_data['fields']['total'])->
                                    //where Not 0.00
                                    where('amount', '!=', '0.00')->
                                    whereBetween('date', [$duplicate_start_date, $duplicate_end_date])->
                                    get();

                            // dd($duplicates);
                            // if 1 duplicate attach expense_receipt info
                            if($duplicates->count() >= 1){
                                foreach($duplicates as $duplicate){
                                    $duplicate->date_diff = Carbon::parse($ocr_receipt_data['fields']['transaction_date'])->floatDiffInDays($duplicate->date);
                                }
                                //create expense and associate with this transaction
                                $expense_duplicate = $duplicates->sortBy('date_diff')->first();

                                // if receipt_html exactly the same dont add new ExpenseReceipt
                                if(isset($expense_duplicate->receipts()->latest()->first()->receipt_html)){
                                    if($expense_duplicate->receipts()->latest()->first()->receipt_html != $ocr_receipt_data['content']){
                                        //12/13/23 $expense should be new?
                                        $expense = $expense_duplicate;
                                    }else{
                                        continue;
                                    }
                                }else{
                                    $expense = $expense_duplicate;
                                    //if error move this single $attachment to a folder for debug...
                                    //move _temp_ocr file to /files/receipts
                                    // Storage::disk('files')->move('/_temp_ocr/' . $ocr_filename, '/auto_receipts_failed/' . $ocr_filename);
                                    // continue;
                                }
                            }elseif($duplicates->isEmpty()){
                                if($transaction){
                                    if($transaction->vendor_id){
                                        $transaction_vendor_id = $transaction->vendor_id;
                                    }else{
                                        $transaction_vendor_id = NULL;
                                    }
                                }else{
                                    $transaction_vendor_id = NULL;
                                }

                                $expense_vendor_id = !is_null($transaction_vendor_id) ? $transaction_vendor_id : (isset($vendor) ? $vendor->id : 0);
                                // dd($expense_vendor_id);

                                $expense = Expense::create([
                                    'amount' => $ocr_receipt_data['fields']['total'],
                                    'date' => $ocr_receipt_data['fields']['transaction_date'],
                                    'project_id' => 0,
                                    'distribution_id' => NULL,
                                    'vendor_id' => $expense_vendor_id,
                                    'check_id' => NULL,
                                    'paid_by' => NULL,
                                    'belongs_to_vendor_id' => $email_vendor->id,
                                    'created_by_user_id' => 0,
                                    'invoice' => $ocr_receipt_data['fields']['invoice_number'] ? $ocr_receipt_data['fields']['invoice_number'] : NULL,
                                ]);
                            }

                            $filename = $expense->id . '-' . date('Y-m-d-H-i-s') . $doc_type;

                            //SAVE expense_receipt_data for each attachment
                            $expense_receipt = new ExpenseReceipts;
                            $expense_receipt->expense_id = $expense->id;
                            $expense_receipt->receipt_filename = $filename;
                            $expense_receipt->receipt_html = $ocr_receipt_data['content'];
                            $expense_receipt->receipt_items = json_encode($ocr_receipt_data['fields']);
                            $expense_receipt->save();

                            if($transaction){
                                $transaction->expense_id = $expense->id;
                                $transaction->save();
                            }

                            Storage::disk('files')->move('/_temp_ocr/' . $ocr_filename, '/receipts/' . $filename);
                        // } //if loop
                    }
                }
                //Delete/move email
                $this->ms_graph->createRequest("DELETE", "/users/" . $company_email->api_json['user_id'] . "/messages/" . $message->getId())->execute();
            }
        }
    }

    //foreach outlook/microsoft email get and process emails...
    public function ms_graph_email_api()
    {
        //6-28-2023 catch forwarded messages where From is in database table company_emails
        $company_emails =  CompanyEmail::withoutGlobalScopes()->whereNotNull('api_json->user_id')->get();
        // dd($company_emails);

        foreach($company_emails as $company_email){
            //check if access_token is expired, if so get new access_token and refresh_token
            $guzzle = new Client();
            $url = 'https://login.microsoftonline.com/' . env('MS_GRAPH_TENANT_ID') . '/oauth2/v2.0/token';
            $email_account_tokens = json_decode($guzzle->post($url, [
                'form_params' => [
                    'client_id' => env('MS_GRAPH_CLIENT_ID'),
                    'scope' => env('MS_GRAPH_USER_SCOPES'),
                    'refresh_token' => $company_email->api_json['refresh_token'],
                    'redirect_uri' => env('MS_GRAPH_REDIRECT_URI'),
                    'grant_type' => 'refresh_token',
                    'client_secret' => env('MS_GRAPH_SECRET_ID'),
                ],
            ])->getBody()->getContents());

            //json
            $api_data = $company_email->api_json;
            $api_data['access_token'] = $email_account_tokens->access_token;
            $api_data['refresh_token'] = $email_account_tokens->refresh_token;
            $api_data = json_encode($api_data);

            $company_email->update([
                'api_json' => $api_data,
            ]);

            $this->ms_graph = new Graph();
            $this->ms_graph->setAccessToken($company_email->api_json['access_token']);

            // FOLDER name Test etc
            // $user_hive_folder =
            //     $this->ms_graph->createCollectionRequest("GET", "/me/mailFolders?filter=displayName eq 'Home Depot Rebates'&expand=childFolders")
            //         ->setReturnType(MailFolder::class)
            //         ->execute();
            // dd($user_hive_folder);

            if(env('APP_ENV') == 'production'){
                //6-12-2023 6-27-2023 exclude ones already read ... save $message->getId() to a database...
                $messages_inbox = $this->ms_graph->createCollectionRequest("GET", "/me/mailFolders/inbox/messages?top=20")
                    ->setReturnType(Message::class)
                    ->execute();

                $messages_inbox_retry = $this->ms_graph->createCollectionRequest("GET", "/me/mailFolders/" . $company_email->api_json['hive_folder'] . "/childFolders/" . $company_email->api_json['hive_folder_retry'] . "/messages?top=20")
                    ->setReturnType(Message::class)
                    ->execute();

                $messages = Arr::collapse([$messages_inbox, $messages_inbox_retry]);
            }else{
                //if array key exists
                if(isset($company_email->api_json['hive_folder_test'])){
                    $messages = $this->ms_graph->createCollectionRequest("GET", "/me/mailFolders/" . $company_email->api_json['hive_folder'] . "/childFolders/" . $company_email->api_json['hive_folder_test'] . "/messages?top=20")
                    ->setReturnType(Message::class)
                    ->execute();
                }else{
                    continue;
                }
            }

            foreach($messages as $key => $message){
                if(!isset($message->getToRecipients()[0])){
                    continue;
                }

                $email_from = $message->getFrom()->getEmailAddress()->getAddress();
                $email_from_domain = substr($email_from, strpos($email_from, "@"));
                //find the right Receipt:: that belongs to this email....
                // $email_to = strtolower($message->getToRecipients()[0]['emailAddress']['address']);
                $email_subject = $message->getSubject();
                $email_date =
                    Carbon::parse($message->getReceivedDateTime())
                        ->setTimezone('America/Chicago')
                        ->format('Y-m-d');

                //find the right Receipt:: that belongs to this email....
                $from_email_receipts = Receipt::withoutGlobalScopes()->where('from_address', $email_from)->orWhere('from_address', $email_from_domain)->get();

                if($from_email_receipts->isEmpty()){
                    //continue... email not a Receipt
                    continue;
                }else{
                    foreach($from_email_receipts as $email_receipt){
                        if(strpos($email_subject, $email_receipt->from_subject) !== FALSE){
                            $receipt = $email_receipt;
                        }else{
                            continue;
                        }
                    }
                }

                //06-17-2023 forwarded/redirected emails? if HIVE doesnt find them? let users forward emails
                    //use $email_to = strtolower($message->getToRecipients()[0]['emailAddress']['address']);

                //if is_null $receit, move to add_receipt folder
                if(!isset($receipt)){
                    //email not a Receipt
                    continue;
                }

                //NOTE: $receipt MUST be set by now
                $receipt_account =
                    ReceiptAccount::withoutGlobalScopes()
                        ->where('belongs_to_vendor_id', $company_email->vendor_id)
                        ->where('vendor_id', $receipt->vendor_id)
                        ->first();

                //missing receipt_account..receipt and companyemail exist but receipt/companyemail combo does not
                //1-17-2023 6-27-2023 YES!~ should still process without #receipt_account? right?
                if(is_null($receipt_account)){
                    //move to Add sub_folder.
                    $this->ms_graph->createRequest("POST", "/users/" . $company_email->api_json['user_id'] . "/messages/" . $message->getId() . "/move")
                        ->attachBody(
                            array(
                                'destinationId' => $company_email->api_json['hive_folder_add']
                                )
                            )
                        ->execute();

                    continue;
                }

                //getContent = HTML or TEXT
                $string = $message->getBody()->getContent();

                //remove images
                //ONLY IF {"receipt_image_regex":  is NOT set
                if(!isset($receipt->options['receipt_image_regex'])){
                    $string = preg_replace("/<img[^>]+\>/i", "", $string);
                }else{
                    //FIND receipt email image in email html (eg. Floor and Decor)
                    $re = $receipt->options['receipt_image_regex'];
                    $str = $string;
                    preg_match($re, $str, $matches, PREG_OFFSET_CAPTURE, 0);

                    //iamge/receipt url
                    $image_email_url = $matches[1][0];

                    //6-27-2023 error if cant find
                }

                // SHOW HTML RENDERED
                // print_r($string);
                // dd();

                //SHOW EMAIL TEXT
                // print_r(htmlspecialchars($string));
                // dd();

                if(isset($receipt->options['receipt_start'])){
                    //if receipt_start = array
                    //if false, look for next.
                    if(is_array($receipt->options['receipt_start'])){
                        foreach($receipt->options['receipt_start'] as $key => $receipt_start_text){
                            $receipt_start = strpos($string, $receipt_start_text);

                            if(is_numeric($receipt_start)){
                                $receipt_start_text = $receipt_start_text;
                                break;
                            }
                        }
                    }else{
                        $receipt_start = strpos($string, $receipt->options['receipt_start']);
                    }

                    //include the "receipt_start" text or start receipt_html after the text
                    if(isset($receipt->options['receipt_start_offset'])){
                        $receipt_start = strpos($string, $receipt_start_text) + strlen($receipt_start_text);
                    }
                }else{
                    $receipt_start = 0;
                }

                if(isset($receipt->options['receipt_end'])){
                    // dd($receipt->options['receipt_end']);

                    //if receipt_end = array
                    //if false, look for next.
                    if(is_array($receipt->options['receipt_end'])){
                        foreach($receipt->options['receipt_end'] as $key => $receipt_end_text){
                            $receipt_end = strpos($string, $receipt_end_text, $receipt_start);

                            if(is_numeric($receipt_end)){
                                break;
                            }
                        }
                    }else{
                        $receipt_end = strpos($string, $receipt->options['receipt_end'], $receipt_start);
                    }

                //if receipt_end = null, use last character of $string
                }else{
                    $receipt_end = strlen($string);
                }

                $receipt_position = $receipt_end - $receipt_start;
                $receipt_html_main = substr($string, $receipt_start, $receipt_position);

                //1-26-23 remove receipt text in the middle (Amazon)
                //1-28-23 multiple removals? foreach receipt_middle_texts?
                if(isset($receipt->options['receipt_middle_text'])){
                    $re = $receipt->options['receipt_middle_text'];
                    $str = $string;
                    preg_match($re, $str, $matches);

                    if(!empty($matches)){
                        $receipt_html_main = str_replace($matches[1], '', $receipt_html_main);
                    }
                }

                //PREVIEWS HTML RECEIPT
                // print_r($receipt_html_main);
                // dd();

                //create Expense
                if(!isset($image_email_url)){
                    $image_email_url = NULL;
                }

                $move_type = $this->create_expense_from_email($company_email, $message, $receipt_account, $receipt, $receipt_html_main, $email_date, $image_email_url);

                //move message here...
                if($move_type == 'duplicate'){
                    //move to duplicate folder
                    $this->ms_graph->createRequest("POST", "/users/" . $company_email->api_json['user_id'] . "/messages/" . $message->getId() . "/move")
                        ->attachBody(
                            array(
                                //1-17-2023 or is send to "receipts@cliff.construction? .. Remove...
                                'destinationId' => $company_email->api_json['hive_folder_duplicate']
                                )
                            )
                        ->execute();

                    continue;
                }elseif($move_type == 'error'){
                    // Log::channel('ms_form_amount_not_found')->info($ocr_receipt_extract_prefix);

                    $this->ms_graph->createRequest("POST", "/users/" . $company_email->api_json['user_id'] . "/messages/" . $message->getId() . "/move")
                        ->attachBody(
                            array(
                                'destinationId' => $company_email->api_json['hive_folder_error']
                                )
                            )
                        ->execute();

                    continue;
                }else{
                    //move email to Saved folder
                    $this->ms_graph->createRequest("POST", "/users/" . $company_email->api_json['user_id'] . "/messages/" . $message->getId() . "/move")
                        ->attachBody(
                            array(
                                //1-17-2023 or is send to "receipts@cliff.construction? .. Remove...
                                'destinationId' => $company_email->api_json['hive_folder_saved']
                                )
                            )
                        ->execute();

                    continue;
                }
            } //foreach messages
        }
    }

    public function create_expense_from_email($company_email, $message, $receipt_account, $receipt, $receipt_html_main, $email_date, $image_email_url = NULL)
    {
        $message_type = array_values((array) $message->getBody()->getContentType())[0];

        if(!isset($receipt->options['receipt_image_regex']) && !isset($receipt->options['pdf_html'])){
            $doc_type = '.pdf';

            $ocr_filename = date('Y-m-d-H-i-s') . '-' . rand(10,99) . '.pdf';

            $view = view('misc.create_pdf_receipt', compact(['receipt_html_main', 'message_type']))->render();
            $location = storage_path('files/_temp_ocr/' . $ocr_filename);

            Browsershot::html($view)
                ->newHeadless()
                ->format('A4')
                ->save($location);
        }elseif(isset($receipt->options['pdf_html'])){
            $doc_type = '.pdf';
            //if no email text, use pdf as html_receipt
            //use first attachment
            if($message->getHasAttachments()){
                $attachments =
                    $this->ms_graph->createRequest("GET", "/me/messages/" . $message->getId() . "/attachments")
                    ->setReturnType(Attachment::class)
                    ->execute();
                foreach($attachments as $loop => $attachment_found){
                    if(isset($receipt->options['attachment_name'])){
                        $re = '/' . $receipt->options['attachment_name'] . '/';
                        $str = $attachment_found->getName();
                        preg_match($re, $str, $matches, PREG_OFFSET_CAPTURE, 0);

                        if(!empty($matches)){
                            $attachment = $attachment_found;
                            break;
                        }else{
                            if(array_key_last($attachments) == $loop){
                                $attachment = $attachments[0];
                            }else{
                                continue;
                            }
                        }
                    }else{
                        $attachment = $attachment_found;
                    }
                }

                $ocr_filename = date('Y-m-d-H-i-s') . '-' . rand(10,99) . '.pdf';
                $content_bytes = array_values((array) $attachment)[0]['contentBytes'];
                //file decoded
                $contents = base64_decode($content_bytes);
                Storage::disk('files')->put('/_temp_ocr/' . $ocr_filename, $contents);

                $ocr_path = 'files/_temp_ocr/' . $ocr_filename;
                $location = storage_path($ocr_path);
            }else{
                //move to add_receipt_info folder, no attachment in email. need attachment when isset($receipt->options['pdf_html'])
                $move_type = 'error';
                return $move_type;
            }
        }else{
            //image / jpg OR png
            $ocr_filename = date('Y-m-d-H-i-s') . '-' . rand(10,99) . '.jpg';
            $ocr_path = 'files/_temp_ocr/' . $ocr_filename;
            $location = storage_path($ocr_path);

            Image::make($image_email_url)->save($location);
            $doc_type = '.jpg';
        }

        //upload
        $uri = $location;
        $post_data = file_get_contents($uri);

        //ocr the file
        $document_model = $receipt->options['document_model'];
        $ocr_receipt_extracted = $this->azure_receipts($post_data, $doc_type, $document_model);
        // dd($ocr_receipt_extracted);
        //pass receipt info to ocr_extract method
        $ocr_receipt_data = $this->ocr_extract($ocr_receipt_extracted, NULL, 'email');

        if(isset($ocr_receipt_data['error'])){
            $move_type = 'error';
            return $move_type;
        }else{
            //01-26-2023 pass rest of receipt info to ocr_extract method
            if(!is_null($ocr_receipt_data['fields']['transaction_date'])){
                $date = $ocr_receipt_data['fields']['transaction_date'];
                // $date = Carbon::parse($date);
                // if($date->year > now()->format('Y')){
                //     $date = $date->year(now()->format('Y'));
                // }else{
                //     $date = $date->format('Y-m-d');
                // }
            }else{
                $date = $email_date;
            }

            //8-18-23 we can remove this?!
            if(isset($receipt->options['refund'])){
                $amount = '-' . $ocr_receipt_data['fields']['total'];
            }else{
                $amount = $ocr_receipt_data['fields']['total'];
            }

            // receipt number / invoice
            if(isset($receipt->options['invoice_regex'])){
                $re = $receipt->options['invoice_regex'];
                $str = $ocr_receipt_data['content'];

                preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);

                if(empty($matches)){
                    $receipt_number = NULL;
                }else{
                    // $receipt_number = str_replace(' ', '', $matches[count($matches) - 1][0]);
                    $invoice = trim($matches[count($matches) - 1][0]);

                    $ocr_receipt_data['fields']['invoice_number'] = $invoice;
                }
            }elseif(isset($ocr_receipt_data['fields']['invoice_number'])){
                $invoice = $ocr_receipt_data['fields']['invoice_number'];
            }else{
                $invoice = NULL;
            }

            // receipt po / purchase order
            if(isset($receipt->options['po_regex'])){
                $re = $receipt->options['po_regex'];
                $str = $ocr_receipt_data['content'];
                preg_match($re, $str, $matches);

                if(empty($matches)){
                    $purchase_order = NULL;
                }else{
                    $purchase_order = trim($matches[1]);
                }
            }else{
                $purchase_order = NULL;
            }

            $ocr_receipt_data['fields']['purchase_order'] = $purchase_order;
        }

        //FIND duplicates
        //confirm expense does not yet exist
        //1-18-2023 | 9/30/2023 NEED TO ACCOUNT FOR SAME VENDOR, AMOUNT, AND DATE being saved multiple of times (accounted for in old $duplicates in $this->dirty_work)
            //maybe by adding date_TIME to 'date'? or checking time in the expense_receipt_data json?

        $duplicates =
            Expense::
                where('belongs_to_vendor_id', $receipt_account->belongs_to_vendor_id)->
                where('vendor_id', $receipt->vendor_id)->
                whereNull('deleted_at')->
                where('amount', $amount)->
                // where('date', $date)->
                whereBetween('date', [Carbon::create($date)->subDay(), Carbon::create($date)->addDays(4)])->
                get();

        if(!$duplicates->isEmpty()){
            // 1-22-2023! WHAT IF THERE IS MULTIPLE?! -- diff in days!
            $duplicate_expense = $duplicates->first();

            //ATTACHMENTS
            $attachments = $this->add_attachments_to_expense($duplicate_expense->id, $message, $ocr_receipt_data, $ocr_filename, $company_email);
            //add po and add invoice from ocr
            $duplicate_expense->invoice = $invoice;
            $duplicate_expense->date = $date;
            // $duplicate_expense->note = $purchase_order;
            $duplicate_expense->save();

            //move email receipt to Duplicate folder
            $move_type = 'duplicate';
            return $move_type;
        }

        //CREATE NEW Expense
        //1-18-2023 FIX, 0 should be NULL on database!
        // $expense->project_id = $receipt_account->project_id;
        //If PO matches a project, use that project
        if(isset($receipt_account->project_id)){
            if($receipt_account->project_id === 0){
                $receipt_account->project = 0;
            }else{
                $receipt_account->project = $receipt_account->project_id;
            }

            $receipt_account->distribution_id = NULL;
        }elseif(isset($receipt_account->distribution_id)){
            $receipt_account->distribution_id = $receipt_account->distribution_id;
            $receipt_account->project_id = NULL;
        }

        //SAVE expense
        $expense = new Expense;
        $expense->amount = $amount;
        $expense->reimbursment = NULL;
        $expense->project_id = $receipt_account->project_id;
        $expense->distribution_id = $receipt_account->distribution_id;
        $expense->created_by_user_id = 0;//automated
        $expense->date = $date;
        $expense->invoice = $invoice;
        $expense->vendor_id = $receipt->vendor_id; //Vendor_id of vendor being Queued
        $expense->note = NULL;
        $expense->belongs_to_vendor_id = $receipt_account->belongs_to_vendor_id;
        $expense->save();

        //save ocr data and file/s
        //ATTACHMENTS
        $attachments = $this->add_attachments_to_expense($expense->id, $message, $ocr_receipt_data, $ocr_filename, $company_email);

        $move_type = 'new';
        return $move_type;
    }

    //send receipt location, document_model_type
    public function azure_receipts($post_data, $doc_type, $document_model)
    {
        //jpg or jpeg
        if($doc_type == '.jpg'){
            $doc_content_type = 'Content-Type: image/jpeg';
        }elseif($doc_type == '.pdf'){
            $doc_content_type = 'Content-Type: application/pdf';
        // }elseif($doc_type == '.png'){
        //     $doc_content_type = 'Content-Type: image/png';
        }else{
            //LOG
            //MOVE EMAIL
            //DO NOT DD FAILED
            // dd('FAILED ReceiptController azure_receips first else');
        }
        //start OCR
        $ch = curl_init();
        $post = $post_data;
        $document_model = $document_model; //lol WHY!?
        $azure_api_key = env('AZURE_RECEIPTS_KEY');
        $azure_api_version = env('AZURE_RECEIPTS_VERSION');

        curl_setopt($ch, CURLOPT_URL, "https://" . env('AZURE_RECEIPTS_URL') . "/formrecognizer/documentModels/" . $document_model . ":analyze?api-version=" . $azure_api_version . " ");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $doc_content_type,
            "Ocp-Apim-Subscription-Key: $azure_api_key"
            ));

        $location_result = curl_exec($ch);
        curl_close($ch);

        $re = '/(\d|\D){8}-(\d|\D){4}-(\d|\D){4}-(\d|\D){4}-(\d|\D){12}/m';
        $str = $location_result;
        preg_match($re, $str, $matches, PREG_OFFSET_CAPTURE, 0);
        $operation_location_id = $matches[0][0];

        //get OCR result
        //&pages=[1]d
        $result = exec('curl -v -X GET "https://' . env('AZURE_RECEIPTS_URL') . '/formrecognizer/documentModels/' . $document_model . '/analyzeResults/' . $operation_location_id . '?api-version=' . $azure_api_version . '" -H "Ocp-Apim-Subscription-Key: ' . $azure_api_key . '"');
        $result = json_decode($result, true);

        //wait but go as soon as done.
        while($result['status'] == "running" || $result['status'] == "notStarted"){
            sleep(2);
            $result = exec('curl -v -X GET "https://' . env('AZURE_RECEIPTS_URL') . '/formrecognizer/documentModels/' . $document_model . '/analyzeResults/' . $operation_location_id . '?api-version=' . $azure_api_version . '" -H "Ocp-Apim-Subscription-Key: ' . $azure_api_key . '"');
            $result = json_decode($result, true);
        }

        $all_fields = [];
        foreach($result['analyzeResult']['documents'] as $document){
            $all_fields = array_merge_recursive($all_fields, $document['fields']);
        }

        $result['analyzeResult']['document'] = $all_fields;

        return $result['analyzeResult'];
    }

    public function ocr_extract($ocr_receipt_extracted, $expense_amount = NULL, $email = NULL)
    {
        if(isset($ocr_receipt_extracted['document'])){
            $ocr_receipt_extract_prefix = $ocr_receipt_extracted['document'];
        }else{
            $ocr_receipt_data = [
                'error' => true,
            ];
            return $ocr_receipt_data;
        }

        if(isset($ocr_receipt_extracted['keyValuePairs'])){
            $key_value_pairs = $ocr_receipt_extracted['keyValuePairs'];
            $key_value_pairs = collect(json_decode(json_encode($key_value_pairs)));
        }

        // ®
        $ocr_receipt_extracted['content'] = htmlentities($ocr_receipt_extracted['content']);
        //TIP AMOUNT
        // if(isset($ocr_receipt_extract_prefix['Tip'])){
        //     $tip_amount = $ocr_receipt_extract_prefix['Tip']['valueNumber'];
        // }else{
        //     $tip_amount = NULL;
        // }

        //HANDWRITTEN
        $handwritten_notes = [];
        if($ocr_receipt_extracted['styles']){
            foreach($ocr_receipt_extracted['styles'] as $key => $handwritten){
                if($handwritten['isHandwritten'] == TRUE && $handwritten['confidence'] > 0.6){
                    foreach($handwritten['spans'] as $span_key => $span){
                        $offset = $handwritten['spans'][$span_key]['offset'];
                        $length = $handwritten['spans'][$span_key]['length'];
                        $handwritten_notes[] = substr($ocr_receipt_extracted['content'], $offset, $length);
                    }
                }
            }
        }

        //MERCHANT / VENDOR NAME
        if(isset($ocr_receipt_extract_prefix['MerchantName'])){
            if(isset($ocr_receipt_extract_prefix['MerchantName']['valueString'])){
                $merchant_name = $ocr_receipt_extract_prefix['MerchantName']['valueString'];
            }elseif($ocr_receipt_extract_prefix['MerchantName']['content']){
                $merchant_name = $ocr_receipt_extract_prefix['MerchantName']['content'];
            }else{
                $merchant_name = NULL;
            }
        }elseif(isset($ocr_receipt_extract_prefix['VendorName'])){
            if(isset($ocr_receipt_extract_prefix['VendorName']['valueString'])){
                $merchant_name = $ocr_receipt_extract_prefix['VendorName']['valueString'];
            }else{
                $merchant_name = NULL;
            }
        }else{
            $merchant_name = NULL;
        }

        $merchant_name = str_replace("\n","", $merchant_name);

        //INVOICE NUMBER/ID
        if(isset($ocr_receipt_extract_prefix['InvoiceId'])){
            $invoice_number = $ocr_receipt_extract_prefix['InvoiceId']['valueString'];
        }elseif(isset($ocr_receipt_extract_prefix['invoice_number'])){
            $invoice_number = $ocr_receipt_extract_prefix['invoice_number'];
        }else{
            $invoice_number = NULL;
        }

        //PO NUMBER
        if(isset($ocr_receipt_extract_prefix['PurchaseOrder'])){
            $purchase_order_number = $ocr_receipt_extract_prefix['PurchaseOrder']['valueString'];
        }else{
            $purchase_order_number = NULL;
        }

        //TOTAL TAX
        if(isset($ocr_receipt_extract_prefix['TotalTax'])){
            if(isset($ocr_receipt_extract_prefix['TotalTax']['valueCurrency'])){
                $total_tax = $ocr_receipt_extract_prefix['TotalTax']['valueCurrency']['amount'];
            }elseif(isset($ocr_receipt_extract_prefix['TotalTax']['valueNumber'])){
                $total_tax = $ocr_receipt_extract_prefix['TotalTax']['valueNumber'];
            }else{
                $total_tax = NULL;
            }
        }else{
            $total_tax = NULL;
        }

        //TRANSACTION DATE
        if(isset($ocr_receipt_extract_prefix['TransactionDate'])){
            if(isset($ocr_receipt_extract_prefix['TransactionDate']['valueDate'])){
                $transaction_date = $ocr_receipt_extract_prefix['TransactionDate']['valueDate'];
            }elseif(isset($ocr_receipt_extract_prefix['TransactionDate']['content'])){
                $transaction_date = $ocr_receipt_extract_prefix['TransactionDate']['content'];
            }else{
                $transaction_date = NULL;
            }
        }elseif(isset($ocr_receipt_extract_prefix['DepartureDate'])){
            $transaction_date = $ocr_receipt_extract_prefix['DepartureDate']['valueDate'];
        }elseif(isset($ocr_receipt_extract_prefix['InvoiceDate'])){
            $transaction_date = $ocr_receipt_extract_prefix['InvoiceDate']['valueDate'];

        //use analyze options for "Order Date" if no InvoiceDate...
        }elseif(isset($key_value_pairs)){
            if(!$key_value_pairs->where('key.content', 'Order Date')->isEmpty()){
                $transaction_date = $key_value_pairs->where('key.content', 'Order Date')->first()->value->content;
            }elseif(!$key_value_pairs->where('key.content', 'Completed Date:')->isEmpty()){
                $transaction_date = $key_value_pairs->where('key.content', 'Completed Date:')->first()->value->content;
            }else{
                $transaction_date = NULL;
            }
        }else{
            $transaction_date = NULL;
        }

        //change year
        if($transaction_date != NULL){
            //if transaction date has letters
            if(is_array($transaction_date)){
                $transaction_date = $transaction_date[0];
            }
            // $transaction_date = preg_replace("/[^0-9]/", "", $transaction_date);
            $transaction_date = Carbon::parse($transaction_date);
            if($transaction_date->year < date('Y', strtotime('-8 years'))){
                $transaction_date = $transaction_date->year(now()->format('Y'));
            }

            $transaction_date = $transaction_date->format('Y-m-d');
        }else{
            //if coming from creating email, allow $transaction_date to be NULL. if from auto_receipts, send error
            if($email == NULL){
                $ocr_receipt_data = [
                    'error' => true,
                ];

                return $ocr_receipt_data;
            }
        }

        //SUBTOTAL
        if(isset($ocr_receipt_extract_prefix['SubTotal'])){
            $subtotal = $ocr_receipt_extract_prefix['SubTotal']['valueCurrency']['amount'];
        }elseif(isset($ocr_receipt_extract_prefix['Subtotal'])){
            $subtotal = $ocr_receipt_extract_prefix['Subtotal']['valueNumber'];
        }else{
            $subtotal = NULL;
        }

        if(isset($ocr_receipt_extract_prefix['Items'])){
            $items = $ocr_receipt_extract_prefix['Items']['valueArray'];
        }else{
            $items = NULL;
        }

        //AMOUNT
        if(isset($ocr_receipt_extract_prefix['Total'])){
            $amount = $ocr_receipt_extract_prefix['Total']['valueNumber'];
        }elseif(isset($ocr_receipt_extract_prefix['InvoiceTotal'])){
            $amount = $ocr_receipt_extract_prefix['InvoiceTotal']['valueCurrency']['amount'];
        }elseif(isset($ocr_receipt_extract_prefix['SubTotal']) && isset($ocr_receipt_extract_prefix['TotalTax'])){
            $amount = $ocr_receipt_extract_prefix['SubTotal']['valueCurrency']['amount'] + $ocr_receipt_extract_prefix['TotalTax']['valueCurrency']['amount'];
        }elseif(isset($key_value_pairs)){
            if(!$key_value_pairs->where('key.content', 'Authorized Amount:')->isEmpty()){
                $amount = $key_value_pairs->where('key.content', 'Authorized Amount:')->first()->value->content;
            }
        //ONLY if coming from ExpensesNewForm, allow $amount above to be empty. ONLY
        }else{
            //if coming from ExpensesNewForm, allow $amount above to be empty.
            if(!is_null($expense_amount)){
                $amount = $expense_amount;
            }else{
                $ocr_receipt_data = [
                    'error' => true,
                ];

                return $ocr_receipt_data;
            }
        }

        if(!isset($amount) && is_null($subtotal)){
            $ocr_receipt_data = [
                'error' => true,
            ];

            return $ocr_receipt_data;
        }else{
            if($amount == 0 && !is_null($subtotal)){
                $amount = $subtotal;
            }

            // if(!is_null($tip_amount)){
            //     dd([$amount, $ocr_receipt_extract_prefix]);
            // }
        }

        $ocr_receipt_data = [
            'content' => $ocr_receipt_extracted['content'],
            'fields' => [
                'items' => $items,
                'subtotal' => $subtotal,
                'total' => $amount,
                'total_tax' => $total_tax,
                'transaction_date' => $transaction_date,
                'merchant_name' => $merchant_name,
                'invoice_number' => $invoice_number,
                'merchant_name' => $merchant_name,
                'purchase_order' => $purchase_order_number,
                'handwritten_notes' => $handwritten_notes,
            ],
        ];

        return $ocr_receipt_data;
    }

    public function add_attachments_to_expense($expense_id, $message = NULL, $ocr_receipt_data, $ocr_filename)
    {
        $filename = $expense_id . '-' . $ocr_filename;

        if(!is_null($message)){
            if($message->getHasAttachments()){
                $attachments =
                    $this->ms_graph->createRequest("GET", "/me/messages/" . $message->getId() . "/attachments")
                        ->setReturnType(Attachment::class)
                        ->execute();

                //Add Email Attachments
                foreach($attachments as $key => $attachment){
                    $filename_attached = $expense_id . '-' . $key . '-' . $ocr_filename;
                    $content_bytes = array_values((array) $attachment)[0]['contentBytes'];
                    //file decoded
                    $contents = base64_decode($content_bytes);
                    Storage::disk('files')->put('/receipts/' . $filename_attached, $contents);

                    //SAVE expense_receipt_data for each attachment
                    $expense_receipt = new ExpenseReceipts;
                    $expense_receipt->expense_id = $expense_id;
                    $expense_receipt->receipt_filename = $filename_attached;
                    $expense_receipt->receipt_html = $ocr_receipt_data['content'];
                    $expense_receipt->receipt_items = json_encode($ocr_receipt_data['fields']);
                    $expense_receipt->save();
                }
            }else{
                //use created file from ocr
                //SAVE expense_receipt_data for each attachment
                $expense_receipt = new ExpenseReceipts;
                $expense_receipt->expense_id = $expense_id;
                $expense_receipt->receipt_filename = $filename;
                $expense_receipt->receipt_html = $ocr_receipt_data['content'];
                $expense_receipt->receipt_items = json_encode($ocr_receipt_data['fields']);
                $expense_receipt->save();
            }
        }else{
            //use created file from ocr
            //SAVE expense_receipt_data for each attachment
            $expense_receipt = new ExpenseReceipts;
            $expense_receipt->expense_id = $expense_id;
            $expense_receipt->receipt_filename = $filename;
            $expense_receipt->receipt_html = $ocr_receipt_data['content'];
            $expense_receipt->receipt_items = json_encode($ocr_receipt_data['fields']);
            $expense_receipt->save();
        }

        //move _temp_ocr file to /files/receipts
        Storage::disk('files')->move('/_temp_ocr/' . $ocr_filename, '/receipts/' . $filename);

        $complete = true;
        return $complete;
    }

    public function hd_rebates()
    {
        // dd(base_path() . "/js/browsershot.js");
        // dd(dirname(__DIR__));
        ini_set('max_execution_time', '4800');

        //read last expense_id file
        $last_expense_id_file = Storage::disk('files')->get('/hd_rebates_expense_id.json');
        $last_expense_id = json_decode($last_expense_id_file, true)['last_expense_id'];

        //3-10-23 foreach vendor that has hd_rebates enabled ...
        $vendors = Vendor::hiveVendors()->get();

        foreach($vendors as $vendor){
            $expenses = Expense::withoutGlobalScopes()
                ->where('belongs_to_vendor_id', $vendor->id)
                ->with('receipts')
                ->where('id', '>', $last_expense_id)
                ->where('vendor_id', 8)
                ->whereNotNull('invoice')
                ->whereRaw('LENGTH(invoice) > 14')
                ->whereBetween('date', [env('HD_REBATE_START'), env('HD_REBATE_END')])
                ->where('amount', 'not like', '-%')
                ->get();
                // dd($expenses);

            foreach($expenses as $expense){
                $receipt_number = str_replace(' ', '', $expense->invoice);
                $receipt_date = $expense->date->format('m/d/Y');
                $receipt_total = $expense->amount;

                $data = ['receipt_number' => $receipt_number, 'receipt_date' => $receipt_date, 'receipt_total' => $receipt_total];
                // dd($data);
                $this->hd_puphpeteer($data, $vendor);

                // print_r(Browsershot::url('https://www.homedepotrebates11percent.com/#/home')
                // ->newHeadless()
                // ->setNodeBinary('/usr/bin/node')
                // ->setNpmBinary('/usr/bin/npm')
                // ->setChromePath('/usr/bin/chromium-browser')
                // ->waitUntilNetworkIdle()
                // ->type('#purchaseDateOnlyText', $data['receipt_date'])
                // ->delay(500)
                // ->click('#home-offer-purchasedate-continue2')



                // ->waitUntilNetworkIdle()
                // ->bodyHtml());
                // Browsershot::url('https://www.homedepotrebates11percent.com/#/home')
                //     ->addChromiumArguments(["no-sandbox"])
                //     // ->newHeadless()

                //     // ->setNodeBinary('/usr/bin/node')
                //     // ->setNpmBinary('/usr/bin/npm')
                //     // ->setChromePath('/usr/bin/chromium-browser')
                //     // //Puppeteer stealth
                //     // ->setBinPath(base_path() . "/js/browsershot.js")
                //     ->setBinPath(base_path() . "/vendor/spatie/browsershot/libs/browsershot.js")
                //     ->waitUntilNetworkIdle()
                //     ->type('#purchaseDateOnlyText', $data['receipt_date'])
                //     ->delay(500)
                //     ->click('#home-offer-purchasedate-continue2')

                //     // ->waitUntilNetworkIdle()
                //     // ->bodyHtml()
                //     ->delay(6500)
                //     ->click('#continueOrSubmitBtn')
                //     // // ->click('#continue-or-submit-btn')
                //     // // // //, 'left', 5, 200
                //     // // ->click('#continueOrSubmitBtn')
                //     // ->click('document.querySelector("#continueOrSubmitBtn")')

                //     // // ->triggeredRequests();

                //     // // ->bodyHtml()

                //     ->savePdf('example112.pdf');

                //     // dd($html);
                // // dd($pdf);
                // dd('far');
                // sleep(1);


                // //change last_expense_id in file
                // //keep track of last expense_id processed in local/text database
                Storage::disk('files')->put('hd_rebates_expense_id.json', json_encode(array('last_expense_id' => $expense->id)));

                // //log expense_id and tracking #
                Log::channel('hd_rebates')->info([$expense->id, $data]);
            }
        }
    }

    public function hd_puphpeteer($data, $vendor)
    {
        $user = $vendor->users()->where('role_id', 1)->where('is_employed', 1)->first();
        //foreach Home Depot receipt betweenDates ... run this now and then every home depot receipt thereafter.
        $puppeteer = new Puppeteer;
        $browser = $puppeteer->launch();

        $page = $browser->newPage();
        $page->goto('https://www.homedepotrebates11percent.com/#/home');
        $page->waitForTimeout(500);

        $page->type('#purchaseDateOnlyText', $data['receipt_date']);
        $page->click('#home-offer-purchasedate-continue2');
        $page->waitForTimeout(1000);

        $page->click('#continueOrSubmitBtn');
        $page->waitForTimeout(500);

        $page->type('#Receipt\ Number', $data['receipt_number']);
        // $page->type('#X\ CPR\ ID', $vendor->business_phone);
        $page->type('#Gross\ Sales', $data['receipt_total']);
        $page->click('#continueOrSubmitBtn');
        $page->waitForTimeout(500);

        // $page->screenshot(['path' => 'example.png']);
        // dd('here');

        $page->type('input[name="firstName"]', $user->first_name);
        $page->type('input[name="lastName"]', $user->last_name);
        // $page->type('input[name="companyName"]', str_replace(array(',', '.', '-', ':'), ' ', $vendor->business_name));
        // $page->type('input[name="phoneNumber"]', $vendor->business_phone);
        // $page->type('input[name="email"]', $vendor->business_email);
        $page->type('input[name="phoneNumber"]', $vendor->business_phone);
        $page->type('input[name="email"]', $vendor->business_email);
        $page->type('input[name="confirmEmail"]', $vendor->business_email);
        $page->type('input[name="address1"]', $vendor->address);
        $page->type('input[name="address2"]', !is_null($vendor->address_2) ? (string) $vendor->address_2 : '');
        $page->type('input[name="postalCode"]', (string) $vendor->zip_code);
        $page->waitForTimeout(1000);

        // $page->type('input[name="city"]', 'Prospect Heights');
        $page->type('select[name="country"]', 'US');
        $page->type('select[name="state"]', $vendor->state);
        $page->waitForTimeout(500);

        $page->click('button[aria-label="Verify\ Address"]');
        $page->waitForTimeout(1500);

        $page->click('#recommendedAddressBtn');
        $page->waitForTimeout(3000);

        $page->click('#continueOrSubmitBtnBottom');
        $page->waitForTimeout(500);

        //3-4-2023 get tracking #
        $browser->close();

        // $page->click('#The\ Home\ Depot\ Physical\ Gift\ Card');
        // $page->click('#continueOrSubmitBtn');
        // $page->waitForTimeout(1000);

        return;
    }

    public function hd_print_certificates()
    {
        dd('in hd_print_certificates');
        //get email folder---foreach folder item ... pupetheer (open link)... create pdf
        $company_emails =  CompanyEmail::withoutGlobalScopes()->whereNotNull('api_json->user_id')->get();
        // dd($company_emails);

        foreach($company_emails as $company_email){
            //check if access_token is expired, if so get new access_token and refresh_token
            $guzzle = new Client();
            $url = 'https://login.microsoftonline.com/' . env('MS_GRAPH_TENANT_ID') . '/oauth2/v2.0/token';
            $email_account_tokens = json_decode($guzzle->post($url, [
                'form_params' => [
                    'client_id' => env('MS_GRAPH_CLIENT_ID'),
                    'scope' => env('MS_GRAPH_USER_SCOPES'),
                    'refresh_token' => $company_email->api_json['refresh_token'],
                    'redirect_uri' => env('MS_GRAPH_REDIRECT_URI'),
                    'grant_type' => 'refresh_token',
                    'client_secret' => env('MS_GRAPH_SECRET_ID'),
                ],
            ])->getBody()->getContents());

            //json
            $api_data = $company_email->api_json;
            $api_data['access_token'] = $email_account_tokens->access_token;
            $api_data['refresh_token'] = $email_account_tokens->refresh_token;
            $api_data = json_encode($api_data);

            $company_email->update([
                'api_json' => $api_data,
            ]);

            $this->ms_graph = new Graph();
            $this->ms_graph->setAccessToken($company_email->api_json['access_token']);

            $folder = 'AQMkADZhOTM1NDI2LWUzMTktNDViMy05OQFhLWZlYTE2YmU3MzAyZAAuAAAD_zyyhiR0HUm0oKJuKA6AnQEA23n86TJcJU_kYhT4djVwqgAHjvLUQAAAAA==';
            $child_folder = 'AQMkADZhOTM1NDI2LWUzMTktNDViMy05OQFhLWZlYTE2YmU3MzAyZAAuAAAD_zyyhiR0HUm0oKJuKA6AnQEA23n86TJcJU_kYhT4djVwqgAIMO_KYQAAAA==';
            $messages = $this->ms_graph->createCollectionRequest("GET", "/me/mailFolders/" . $folder . "/childFolders/" . $child_folder . "/messages?top=5")
                ->setReturnType(Message::class)
                ->execute();

            $links = [];
            foreach($messages as $message){
                $str = $message->getBody()->getContent();
                $re = '/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1/m';
                preg_match($re, $str, $matches, PREG_OFFSET_CAPTURE, 0);

                $link = $matches[2][0];
                // dd($link);
                $html = Browsershot::url($link)
                ->addChromiumArguments(["no-sandbox"])
                // ->newHeadless()

                // ->setNodeBinary('/usr/bin/node')
                // ->setNpmBinary('/usr/bin/npm')
                // ->setChromePath('/usr/bin/chromium-browser')
                // //Puppeteer stealth
                // ->setBinPath(base_path() . "/js/browsershot.js")
                ->setBinPath(base_path() . "/vendor/spatie/browsershot/libs/browsershot.js")
                ->waitUntilNetworkIdle()
                // ->type('#purchaseDateOnlyText', $data['receipt_date'])
                // ->delay(500)
                // ->click('#home-offer-purchasedate-continue2')

                // ->waitUntilNetworkIdle()
                ->bodyHtml();

                dd($html);
                // ->delay(6500)
                // ->click('#continueOrSubmitBtn')
                // // ->click('#continue-or-submit-btn')
                // // // //, 'left', 5, 200
                // // ->click('#continueOrSubmitBtn')
                // ->click('document.querySelector("#continueOrSubmitBtn")')

                // // ->triggeredRequests();

                // // ->bodyHtml()

                // ->savePdf('example112.pdf');
            }
        }
    }

    public function att()
    {
        //->newHeadless()
        $pdf = Browsershot::url('https://signin.att.com')
        ->setNodeBinary('/usr/bin/node')
        ->setChromePath('/usr/bin/chromium-browser')
        // ->format('A4')
        // ->scale(0.5)
        // ->authenticate('patryk@gs.construction', 'Pilka123#')
        // ->type('#login_email', 'patryk@gs.construction')
        // ->delay(1500)
        // ->type('#login_password', 'Pilka123#')

        // ->click('.b-account-login_form_submit_group_btn g-button_1')
        // ->delay(1500)
        // ->waitUntilNetworkIdle()
        // ->click('[name="Sign in"] < button')
        // ->click('body > div.l-main.ug-everyone.ug-unregistered > div.pt_storefront > div:nth-child(1) > div > div.b-account-login_content > div.b-account-login_signin > div > div.b-account-login_form > form > div.b-account-login_form_submit_group > button')
        // ->click('/html/body/div[1]/div[9]/div[1]/div/div[3]/div[1]/div/div[3]/form/div[3]/button')
        // ->delay(2500)
        ->waitUntilNetworkIdle()
        // ->pdf();

        ->save('example.pdf');

        dd('done');
    }

    public function menards()
    {
        //Browsershot

        // $estimate = $this->estimate;
        // return view('livewire.estimates.print', compact('estimate'));
        // ->setNodeBinary('/usr/bin/node')
        // $pdf = Browsershot::url('https://www.menards.com/main/login.html')
        // ->setNodeBinary('/usr/bin/node')
        // ->setChromePath('/usr/bin/chromium-browser')
        // // ->format('A4')
        // // ->scale(0.8)
        // // ->authenticate('patryk@gs.construction', 'Pilka123#')
        // ->type('#username', 'patryk@gs.construction')
        // ->type('#login-password', 'Pilka123#')
        // // ->click('#loginForm > button')
        // ->delay(500)
        // // ->pdf();
        // ->save('example.pdf');




        // dd('done');




        $puppeteer = new Puppeteer([
            'js_extra' => /** @lang JavaScript */
            "
                const puppeteer = require('puppeteer-extra');
                const StealthPlugin = require('puppeteer-extra-plugin-stealth');
                puppeteer.use(StealthPlugin());
                instruction.setDefaultResource(puppeteer);
            "
        ]);

        $browser = $puppeteer->launch();

        $page = $browser->newPage();
        $page->goto('https://www.menards.com/main/login.html');
        $page->waitForTimeout(500);

        $page->type('#username', 'patryk@gs.construction');
        $page->type('#login-password', 'Pilka123#');
        $page->waitForTimeout(1000);
        $page->click('#loginForm > button');
        $page->waitForTimeout(500);

        $page->screenshot(['path' => 'example_captcha_screen.png']);

        dd('done');
        //solve reCAPCHA if challenged
        // $solver = new \TwoCaptcha\TwoCaptcha(env('2CAPTCHA_API'));
        // $result = $solver->recaptcha([
        //     //images
        //     'sitekey' => '6LfiZEYUAAAAAJD__10WyXUxDcTZh6dfNgPX18lT',
        //     'url'     => 'https://www.menards.com/main/login.html',

        //     //checkmark
        //     // 'sitekey' => '6Ld38BkUAAAAAPATwit3FXvga1PI6iVTb6zgXw62',
        //     // 'url'     => 'https://www.menards.com/main/checkcredentials.html',
        // ]);

        // sleep(20);
        // dd($result);
    }

    public function floordecor()
    {
        //->newHeadless()
        $pdf = Browsershot::url('https://www.flooranddecor.com/account-login')
        ->setNodeBinary('/usr/bin/node')
        ->setChromePath('/usr/bin/chromium-browser')
        ->format('A4')
        ->scale(0.5)
        // ->authenticate('patryk@gs.construction', 'Pilka123#')
        ->type('#login_email', 'patryk@gs.construction')
        // ->delay(1500)
        ->type('#login_password', 'Pilka123#')

        // ->click('.b-account-login_form_submit_group_btn g-button_1')
        // ->delay(1500)
        // ->waitUntilNetworkIdle()
        // ->click('[name="Sign in"] < button')
        // ->click('body > div.l-main.ug-everyone.ug-unregistered > div.pt_storefront > div:nth-child(1) > div > div.b-account-login_content > div.b-account-login_signin > div > div.b-account-login_form > form > div.b-account-login_form_submit_group > button')
        // ->click('/html/body/div[1]/div[9]/div[1]/div/div[3]/div[1]/div/div[3]/form/div[3]/button')
        // ->delay(2500)
        ->waitUntilNetworkIdle()
        // ->pdf();

        ->save('example.pdf');

        dd('done');
    }

    //01-18-20233 transitioning all to $this->azure_receipts
    //06-21-2022 USING BOTH NEW_OCR AND OCR_SPACE.. why?.
    public function new_ocr_status()
    {
        //public function new_ocr($ocr_filename)
        //ocr_space($ocr_filename)

        //Show OCR left before buying more
        dd(exec('curl http://api.newocr.com/v1/key/status?key='. env('NEW_OCR_API')));
    }

    //1-18-2023 combine the next 2 functions into one. Pass type = original or temp
    //Show full-size receipt to anyone with a link
    // No Middleware or Policies
    //PUBLIC AS FUCK! BE CAREFUL!
    public function original_receipt($filename)
    {
        $path = storage_path('files/receipts/' . $filename);

        if(File::extension($filename) == 'pdf'){
            $response = Response::make(file_get_contents($path), 200, [
                'Content-Type' => 'application/pdf'
            ]);
        }else{
            $response = Image::make($path)->response();
        }

        return $response;
    }

    public function temp_receipt($filename)
    {
        $path = storage_path('files/_temp_ocr/' . $filename);

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
