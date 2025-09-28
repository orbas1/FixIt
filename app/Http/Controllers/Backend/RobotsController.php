<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\UpdateRobotRequest;

class RobotsController extends Controller
{
    public function index()
    {

        $filePath = public_path('robots.txt');
        $content = file_exists($filePath) ? file_get_contents($filePath) : '';

        return view('backend.customization.robots', ['content' => $content]);
    }
   public function update(UpdateRobotRequest $request)
    {
        $filePath = public_path('robots.txt');
        file_put_contents($filePath, $request->input('content'));

        return redirect()->route('backend.robot.index')->with('success', 'robots.txt updated successfully!');
    }
}
