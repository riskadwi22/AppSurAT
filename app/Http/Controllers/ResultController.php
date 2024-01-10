<?php

namespace App\Http\Controllers;

use App\Models\result;
use App\Models\letter;
use App\Models\letter_type;
use App\Models\User;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    
        $letters = Letter::orderBy('letter_type_id', 'ASC')->simplePaginate(5);
        $letterTypes = letter_type::get(); 
        $results = Result::get();
        $letterCounts = [];

        foreach ($letters as $letter) {
            $notulisUser = User::find($letter->notulis);
            $letter->notulisUserData = $notulisUser;
            $recipientId = json_decode($letter->recipients, true);
            $letterTypeId = letter_type::find($letter->letter_type_id);
            $recipients = User::where('id', $recipientId)->get();
            $letter->recipientsData = $recipients;
            if (!isset($letterCounts[$letter->letter_type_id])) {
                $letterCounts[$letter->letter_type_id] = 1;
            } else {
                $letterCounts[$letter->letter_type_id]++;
            }
        }
        return view('result.index', compact('letters', 'results', 'letterTypes', 'letterCounts'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        $letter = Letter::find($id);
        $recipients = json_decode($letter->recipients, true);
        $users = User::whereIn('id', $recipients)->where('role', 'guru')->get();
        return view('result.create', compact('users', 'letter'));
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'letter_id' => 'required',
            'notes' => 'required',
            'presence_recipients' => 'required|array',
        ]);

        Result::create([
            'letter_id' => $request->letter_id,
            'notes' => $request->notes,
            'presence_recipients' => json_encode($request->presence_recipients), 
        ]);
 
        return redirect()->route('result.data')->with('success', 'Berhasil Membuat Hasil Rapat!');
    }


    /**
     * Display the specified resource.
     */
    public function show(letter $letter, $id)
    {
        $letter = letter::find($id);
        $letterType = letter_type::get();
        $letterCounts = [];
        $recipientId = json_decode($letter->recipients, true);
        $users = User::where('role', 'guru')->get();
        $result = result::where('letter_id', $id)->first();
        $letter->letterResult = $result;
        $recipientId = json_decode($letter->recipients, true);
        $letterTypeId = letter_type::find($letter->letter_type_id);
        $letter->letterTypeId = $letterTypeId;
        $recipients = User::whereIn('id', $recipientId)->get();
        $letter->recipientsData = $recipients;
        $notulisUser = User::find($letter->notulis);
        $letter->notulisUserData = $notulisUser;
        if (!isset($letterCounts[$letter->letter_type_id])) {
            $letterCounts[$letter->letter_type_id] = 1;
        } else {
            $letterCounts[$letter->letter_type_id]++;
        }
        return view('result.detail', compact('letter', 'letterCounts', 'users', 'result'));
    }
    public function search(Request $request)
    {
        $keyword = $request->input('name');
        $letters = letter::where('letter_perihal', 'like', "%$keyword%")->orderBy('letter_type_id', 'ASC')->simplePaginate(5);
        $letterTypes = letter_type::get(); 
        $results = Result::get();
        $letterCounts = [];

        foreach ($letters as $letter) {
            $recipientId = json_decode($letter->recipients, true);
            
            $letterTypeId = letter_type::find($letter->letter_type_id);
            $letter->letterTypeId = $letterTypeId;
            $recipients = User::whereIn('id', $recipientId)->get();
            $letter->recipientsData = $recipients;
            $notulisUser = User::find($letter->notulis);
            $letter->notulisUserData = $notulisUser;
            if (!isset($letterCounts[$letter->letter_type_id])) {
                $letterCounts[$letter->letter_type_id] = 1;
            } else {
                $letterCounts[$letter->letter_type_id]++;
            }
        }
    
        return view('result.index',  compact('letters', 'results', 'letterTypes', 'letterCounts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(result $result)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, result $result)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(result $result)
    {
        //
    }
}
