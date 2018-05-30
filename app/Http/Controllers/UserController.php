<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth, Mail;
class UserController extends Controller
{
    //验证用户是否登录
    public function __construct()
    {
        //未登录的用户可以访问
        $this->middleware('auth', [            
            'except' => ['show', 'create', 'store', 'index', 'confirmEmail']
        ]);
        //只允许未登录的用户访问
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }
    /**
     * [index description]
     * @return [type] [description]
     */
    public function index()
    {
        $users = User::paginate(10);
        return view('user.index', compact('users'));
    }
	//用户首页
    public function create()
    {
    	return view('user.create');
    }
    //用户主页
    public function show(User $user)
    {
        $statuses = $user->statuses()
                           ->orderBy('created_at', 'desc')
                           ->paginate(30);
        return view('user.show', compact('user', 'statuses'));
    }
    //处理提交数据
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
        // Auth::login($user);
        // session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        // return redirect()->route('users.show', [$user]);
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('user.edit', compact('user'));
    }
    //update 修改用户
    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);
        //对数据进行验证
        $this->validate($request, [
            'name'     =>'required|max:50',
            'password' =>'required|confirmed|min:6',

        ]);
        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('user.show', $user->id);
    }

    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $to = $user->email;
        $subject = "感谢注册 Sample 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }

    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();
        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('user.show', [$user]);
    }
    
    //判断是否关注
    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);
    }

    public function followings(User $user)
    {
        $users = $user->followings()->paginate(30);
        $title = '关注的人';
        return view('user.show_follow', compact('users', 'title'));
    }

    public function followers(User $user)
    {
        $users = $user->followers()->paginate(30);
        $title = '粉丝';
        return view('user.show_follow', compact('users', 'title'));
    }
}
