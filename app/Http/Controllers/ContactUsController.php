<?php

namespace App\Http\Controllers;

use App\Mail\ConnectWithProjectOwner;
use App\Mail\ContactUsMail;
use App\Traits\ProjectTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ContactUsController extends Controller
{
    use ProjectTrait;
    public function getView()
    {
        return view('contactus');
    }
    public function sendMessage(Request $request)
    {
        $details = [
            'subject' => $request->subject,
            'message' =>  $request->message,
            'user_info' => Auth::user()
        ];
        Mail::to("info@reveroplanner.com")->send(new ContactUsMail($details));
        return redirect()->back();
    }
    public function getViewContactProjectOwner($slug)
    {
        $project = $this->project($slug);
        $owner = $project->owner;

        return view('contactProjectOwne',compact('owner','project'));
    }
    public function sendMessageContactProjectOwner(Request $request,$slug)
    {
        $project = $this->project($slug);
        $owner = $project->owner;
        $details = [
            'sender_name' => $request->sender_name,
            'message' =>  $request->message,
            'sent_from' => $request->sender_mail,
            'project_name' => $project->name,
            'owner_name' => $owner->name.' '.@$owner->last_name,
            'send_to' => $request->to
        ];
        if($request->to == "zavero_team"){
            Mail::to('info@reveroplanner.com')
            ->send(new ConnectWithProjectOwner($details));
        }else{
            Mail::to($owner->email)
            ->bcc(['eman.motaz@thetailorsdev.com','info@reveroplanner.com'])
            ->send(new ConnectWithProjectOwner($details));
        }

        return redirect()->back();
    }
}
