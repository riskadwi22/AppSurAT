<?php

namespace App\Http\Controllers;

use App\Models\result;
use App\Models\letter;
use App\Models\letter_type;
use App\Models\User;
use Illuminate\Http\Request;
use Excel;
use PDF;
use App\Exports\AllLetterExport;

class LetterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    public function getLetters()
    {
        $letters = Letter::orderBy('letter_type_id', 'ASC')->simplePaginate(5);
        $letterTypes = letter_type::get(); 
        $results = Result::get();
        $letterCounts = [];
        foreach ($letters as $letter) {
            $recipientId = json_decode($letter->recipients, true);
            $letterTypeId = letter_type::find($letter->letter_type_id);
            $letter->letterTypeId = $letterTypeId;
            $recipients = User::where('id', $recipientId)->get();
            $letter->recipientsData = $recipients;
            $notulisUser = User::find($letter->notulis);
            $letter->notulisUserData = $notulisUser;
            if (!isset($letterCounts[$letter->letter_type_id])) {
                $letterCounts[$letter->letter_type_id] = 1;
            } else {
                $letterCounts[$letter->letter_type_id]++;
            }
        }
        return view('letter.letters.index', compact('letters', 'results', 'letterTypes', 'letterCounts'));
    }
    public function searchLetters(Request $request)
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
        return view('letter.letters.index', compact('letters', 'results', 'letterTypes', 'letterCounts'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function createLetters()
    {
        $klasifikasi = letter_type::get();
        $user = User::where('role', 'guru')->get();
        return view('letter.letters.createLetter.create', compact('user', 'klasifikasi'));
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'letter_type_id' => 'required',
            'letter_perihal' => 'required',
            'recipients' => 'required|array',
            'content' => 'required',
            'notulis' => 'required'
        ]);
        
        letter::create([
            'letter_type_id' => $request->letter_type_id,
            'letter_perihal' => $request->letter_perihal,
            'recipients' => json_encode($request->recipients),
            'content' => $request->content,
            'attachment' => $request->attachment,
            'notulis' => $request->notulis
        ]);
        return redirect()->route('letter.letters.data')->with('success', 'Berhasil Menambahkan Surat Baru!');
    }
    //pdf fak
    public function downloadPDF($id)
    {
        $letter = letter::find($id);
        
        $letterTypes = letter_type::get();
        $letterCounts = [];
        $recipientId = json_decode($letter->recipients, true);
        $users = User::whereIn('id', $recipientId)->where('role', 'guru')->get();
        $recipientId = json_decode($letter->recipients, true);
        
        $letterTypeId = letter_type::find($letter->letter_type_id);
        $letter->letterTypeId = $letterTypeId;
        $recipients = User::whereIn('id', $recipientId)->get();
        $letter->recipientsData = $recipients;
        
        if (!isset($letterCounts[$letter->letter_type_id])) {
            $letterCounts[$letter->letter_type_id] = 1;
        } else {
            $letterCounts[$letter->letter_type_id]++;
        }
    
        $name = $letter->letter_perihal;
        $pdf = PDF::loadView('letter.letters.download', compact('letter', 'letterCounts', 'users'));
        return $pdf->download($name . '.pdf');
    }


    public function downloadExcel()
    {
        $file_name = 'Data Surat.xlsx';
        return Excel::download(new AllLetterExport, $file_name);
    }



    /**
     * Display the specified resource.
     */
    public function show(letter $letter, $id)
    {
        $letter = letter::find($id);
        $letterTypes = letter_type::get();
        $letterCounts = [];
        $recipientId = json_decode($letter->recipients, true);
        $users = User::whereIn('id', $recipientId)->where('role', 'guru')->get();
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
        return view('letter.letters.result', compact('letter', 'letterCounts', 'users', 'result'));
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $letterType = letter_type::get();
        $letters = letter::find($id);
        $user = User::where('role', 'guru')->get();
        return view('letter.letters.edit', compact('user', 'letters', 'letterType'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, letter $letter, $id)
    {
        $request->validate([
            'letter_type_id' => 'required',
            'letter_perihal' => 'required',
            'recipients' => 'required|array',
            'content' => 'required',
            'notulis' => 'required'
        ]);
        letter::where('id', $id)->update([
            'letter_type_id' => $request->letter_type_id,
            'letter_perihal' => $request->letter_perihal,
            'recipients' => json_encode($request->recipients), 
            'content' => $request->content,
            'attachment' => $request->attachment,
            'notulis' => $request->notulis
        ]);

        return redirect()->route('letter.letters.data')->with('success', 'Berhasil Mengubah Data Surat!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        letter::where('id', $id)->delete();
        
        return redirect()->back()->with('delete', 'Berhasil Menghapus Data Surat');
    }
}
