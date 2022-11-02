<!DOCTYPE html>
<?php
set_time_limit(0);
?>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <title>{{$expenses->first()->project->name}} Reimbursment PDF</title>
<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<style type="text/css" media="print">
    div.page
    {
        page-break-after: always;
        page-break-inside: avoid;
    }
</style>
  </head>
  <body>

      <div class="container page">
            <div class="row">
                <div class="row">
      <div class="col-xs-12">
        @foreach ($expenses as $expense)
      <h1>{{ money($expense->amount) . ' for ' . $expense->vendor->business_name }}</h1><br>
     
@endforeach  
      </div>

    
    </div>


            </div> <!-- /row -->
        </div> <!-- /container -->
<style type="text/css">
    .page {
        overflow: hidden;
        page-break-after: always;
    }
</style>


@foreach ($expenses as $expense)

@if(isset($expenses->first()->receipts()->latest()->first()->receipt_html))
        <div class="container page">
            <div class="row">
                <div class="row">
      <div class="col-xs-12">
      <h1>{{ money($expense->amount) . ' for ' . $expense->vendor->business_name }}</h1>
      <pre style="background-color:transparent">
        {!! $expenses->first()->receipts()->latest()->first()->receipt_html !!}
     </pre>
    {{-- <img src="{{route('expenses.receipt', $expense->receipt)}}"> --}}

      </div>

      
    </div>
    <div class="row">
        <div class="col-md-12">
      <h4><a href="{{ route('expenses.original_receipt', $expense->receipts()->latest()->first()->receipt_filename) }}" target="new">Original Receipt</a></h4>
    </div>
    </div>

            </div> <!-- /row -->
        </div> <!-- /container -->

@else
  {{dd('IMG')}}
    @if(Image::make(storage_path('files/receipts/' . $expense->receipt))->height() > Image::make(storage_path('files/receipts/' . $expense->receipt))->width()  ) {{-- landscape this if protrait do this. --}}
    @else
    <style type="text/css" media="print">
        .landscape
        {
         -webkit-transform: rotate(-90deg); 
        }
    </style>
    @endif

        <div class="container page">
            <div class="row">
                <div class="row">
    	<div class="col-xs-12">
    	<h1>{{ money($expense->amount) . ' for ' . $expense->vendor->business_name }}</h1>
    <img height="900" widht="auto" max-width="500" alt="Expense Receipt" src="{{storage_path() . '/files/receipts/' . $expense->receipt}}" />
    {{-- <img src="{{route('expenses.receipt', $expense->receipt)}}"> --}}

    	</div>
    	
    </div>

            </div> <!-- /row -->
        </div> <!-- /container -->
@endif
<style type="text/css">
    .page {
        overflow: hidden;
        page-break-after: always;
    }
</style>
@endforeach
  </body>
</html>