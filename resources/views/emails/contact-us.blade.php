@component('mail::message')
    <strong>Contect Us details: </strong><br>
    <strong>Name: </strong>{{ $contact->name }} <br>
    <strong>Email: </strong>{{ $contact->email }} <br>
    <strong>Rating: </strong>{{ $contact->rating }} <br>
    <strong>Error Type: </strong>{{ $contact->error_type }} <br>
    <strong>Message: </strong>{{ $contact->description }} <br><br>
@endcomponent
