<?php

namespace App\Http\Controllers;

use App\Models\letter_type;
use App\Models\letter;
use App\Models\result;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usersStaff = User::where('role', 'staff')->count();
        $usersGuru = User::where('role', 'guru')->count();
        $allklasifikasi = letter_type::count();
        $allLetters = letter::count();
        return view('dashboard', compact('usersGuru','usersStaff', 'allklasifikasi', 'allLetters'));
    }
    

    public function getDataGuru()
    {
        $users = User::where('role', 'guru')->orderBy('name', 'ASC')->simplePaginate(5);
        return view('user.guru.index', compact('users'));
    }


    public function getDataStaff()
    {
        $users = User::where('role', 'staff')->orderBy('name', 'ASC')->simplePaginate(5);
        return view('user.staff.index', compact('users'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function createGuru()
    {
        return view('user.guru.create');
    }

    public function createStaff()
    {
        return view('user.staff.create');
    }

    public function searchGuru(Request $request)
    {
        $keyword = $request->input('name');
        $users = User::where('name', 'like', "%$keyword%")->where('role', 'guru')->orderBy('name', 'ASC')->simplePaginate(5);
        return view('user.guru.index', compact('users'));
    }

    public function searchStaff(Request $request)
    {
        $keyword = $request->input('name');
        $users = User::where('name', 'like', "%$keyword%")->where('role', 'staff')->orderBy('name', 'ASC')->simplePaginate(5);
        return view('user.staff.index', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|min:5',
            'role' => 'required'
        ]);

        // tiga karakter pertama nama dan email
        $namaUser = substr($request->name, 0, 3);
        $emailUser = substr($request->email, 0, 3);
        $defaultPassword = Hash::make($namaUser . $emailUser);
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => $defaultPassword
        ]);
        return redirect()->back()->with('success', 'Berhasil Menambahkan Data!');
    }


    /**
     * Display the specified resource.
     */
    public function show(result $result)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */

    public function edit($id)
    {
        $user = User::find($id);
        if ($user->role == 'staff') {
            return view('user.staff.edit', compact('user'));
        }
        else {
            return view('user.guru.edit', compact('user'));
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|min:5',
            'role' => 'required',
            'password' => 'required'
        ]);

        $hashedPassword = Hash::make($request->password);

        User::where('id', $id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => $hashedPassword,
        ]);

        if ($request->role == 'staff') {
            return redirect()->route('user.staff.data')->with('success', 'Berhasil Mengubah Data Pengguna!');
        }
        else {
            return redirect()->route('user.guru.data')->with('success', 'Berhasil Mengubah Data Pengguna!');
        }

    }

    public function authLogin (request $request) {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        $user = $request->only(['email', 'password']);
        if (Auth::attempt($user)) {
            return redirect('/dashboard'); 
        } else {
            return redirect()->back()->with('failed', 'Login gagal! silahkan coba lagi');
        } 
    }


    public function logout()
    {
        Auth::logout();
        return redirect()->route('login'); 
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        User::where('id', $id)->delete();
        return redirect()->back()->with('delete', 'Berhasil Menghapus Data Pengguna');
    }
}
