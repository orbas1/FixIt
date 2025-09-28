<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\ContactMail;
use Illuminate\Support\Facades\Mail;

class ContactUsController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('frontend.contact-us.index');
    }

    public function sendMail(Request $request)
    {
        Mail::to($request->email)->send(mailable: new ContactMail($request));
        return redirect()->back()->with('message','Mail Send Successfully');
    }
}