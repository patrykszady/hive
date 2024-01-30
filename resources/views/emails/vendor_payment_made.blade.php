<x-mail::message>
Hi {{$vendor->name}}!
<br>
Here are your payment details from <b>{{$paying_vendor->name}}</b>:

<x-mail::panel>
{{-- if no check nubmer show Transfer/Zelle or Cash --}}
Check # <b>{{$check_number}}</b><br>
Check Date <b>{{$check->date->format('m/d/Y')}}</b><br>
Check Total <b>{{money($check->amount)}}</b><br>
</x-mail::panel>

<h3>Project Payments:</h3>
<x-mail::panel>
@foreach($check->expenses as $expense)
    <b>{{money($expense->amount)}}</b> | {{$expense->project->name}}<br>
@endforeach
</x-mail::panel>

<x-mail::subcopy>
Join <a href="https://dashboard.hive.contractors/">Hive Contractors</a> today to flawlessly manage your construction projects, see more details for this payment, add bids, and so much more!<br>
Call Patryk 224-999-3880 to setup for free!
</x-mail::subcopy>
<x-mail::button :url="'https://dashboard.hive.contractors'">
Join Hive
</x-mail::button>
Thanks,<br>
Patryk<br>
<a href="https://dashboard.hive.contractors">Hive Contractors</a>
</x-mail::message>
