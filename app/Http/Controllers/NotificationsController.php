<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // 获取登录用户的所有通知
        $user = User::find(Auth::id());
        $notifications =  $user->notifications()->paginate(20);
        $user->markAsRead();
        return view('notifications.index', compact('notifications'));
    }
}
