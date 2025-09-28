@component('mail::message')
    <strong>Test Mail details: </strong><br>
    <strong>Mail From Name: </strong>{{ $request->email['mail_from_name'] }} <br>
    <strong>Mail From Email: </strong>{{ $request->email['mail_from_address'] }} <br>
    <strong>Mail Mailer: </strong>{{ $request->email['mail_mailer'] }} <br>
    <strong>Mail Host: </strong>{{ $request->email['mail_host'] }} <br>
    <strong>Mail Port: </strong>{{ $request->email['mail_port'] }} <br>
    <strong>Mail Encryption: </strong>{{ $request->email['mail_from_address'] }} <br>
    <strong>Mail Username: </strong>{{ $request->email['mail_username'] }} <br><br>
@endcomponent
