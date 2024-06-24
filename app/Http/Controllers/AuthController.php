<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function index(){
        return view('auth.login');
    }

    public function register(){ 
        return view('auth.register'); 
    }

    public function proses_login(Request $request){
        // isi credential hanya berupa username dan password
        $credentials = $request->only('email', 'password');

        // validasi menggunakan Illuminate\Support\Facades\Validator
        $validate = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // jika terdapat field yang kosong
        if ($validate->fails()) {
            // kembali ke halaman login & tampilkan error pada setiap inputnya
            return back()->withErrors($validate)->withInput();
        }

        // verifikasi data user pada kolom email dan password sesuai atau belum
        if (Auth::attempt($credentials)) {
            // jika sesuai maka jalankan fungsi dashboard
            return redirect()->intended('dashboard')->with('success', 'Successfully Login');
        }

        // kembali ke halaman login dan tampilkan pesan error pada login_error
        return redirect('login')->withInput()->withErrors(['login_error' => 'Username or password are wrong!']);
    }

    public function dashboard(){ 
        // cek berhasil login 
        if (Auth::check()) {
            return view('home');
        }

        return redirect('login')->with('error', 'You don\'t have access');
    }

    public function proses_register(Request $request){
        // validasi menggunakan Illuminate\Support\Facades\Validator
        $validate = Validator::make($request->all(), [
            'fullname' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        // jika terdapat field yang kosong
        if ($validate->fails()) {
            // kembali ke halaman register & tampilkan error pada setiap inputnya
            return back()->withErrors($validate)->withInput();
        }

        // tambahkan field level dan kita isi dengan admin
        $user = new User();
        $user->fullname = $request->fullname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->level = 'admin';
        $user->save();

        Auth::login($user);

        return redirect('dashboard')->with('success', 'You have successfully registered');
    }

    public function logout(){
        // clear session dan memberitahu auth dengan status logout
        Session::flush();
        Auth::logout();

        return Redirect('login');
    }
}