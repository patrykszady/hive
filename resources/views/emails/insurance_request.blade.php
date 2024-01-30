<div>
    Hello,
    <br>
    On behalf of <b>{{$vendor->business_name}}</b> we are requesting new certificates of insurance for the following policies that have expired. Please contact the insured directly if needed.
</div>

{{-- table of expired certificates --}}
{{-- @if(!$agent_expired_docs->isEmpty()) --}}
<hr>
<div>
    Expired Insurance Certificates:
</div>
<ul>
    <hr>
    @foreach($agent_expired_docs as $agent_expired_doc)
        <li>{{$agent_expired_doc->type}} | {{$agent_expired_doc->expiration_date->format('m/d/Y')}}</li>
        <hr>
    @endforeach
</ul>
{{-- @endif --}}

<hr>

<div>
    Certificate Holder:
    <br>
    <b>{{$requesting_vendor->business_name}}</b>
    <br>
    {{$requesting_vendor->address}}
    @if(!is_null($requesting_vendor->address_2))
    <br>
    {{$requesting_vendor->address_2}}
    @endif
    <br>
    {{$requesting_vendor->city}}, {{$requesting_vendor->state}} {{$requesting_vendor->zip_code}}
</div>

<hr>

<div>
    Thank you,
    <br>
    Patryk
    <br>
    <a href="https://dashboard.hive.contractors/">Hive Contractors</a>
    <br>
    Contractors: Join <a href="https://dashboard.hive.contractors/">Hive Contractors</a> today to flawlessly manage your construction projects.
</div>
