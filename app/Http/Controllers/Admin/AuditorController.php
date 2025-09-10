<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuditorController extends Controller
{
      public function index()
    {
        $auditors = Auditor::all();
        return view('admin.pages.auditor.index', compact('auditors'));
    }

    public function create()
    {
        return view('admin.pages.auditor.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255', Rule::unique('auditors','email')],
            'username' => ['required','string','max:50', Rule::unique('auditors','username')],
            'password' => ['required','string','min:8','confirmed'],
            'status'   => ['required','in:active,inactive'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'auditor';

        Auditor::create($data);
        return redirect()->route('admin.auditor.index')->with('ok','Auditor created.');
    }

    public function edit(Auditor $auditor)
    {
        return view('admin.pages.auditor.edit', compact('auditor'));
    }

    public function update(Request $request, Auditor $auditor)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255', Rule::unique('auditors','email')->ignore($auditor->id)],
            'username' => ['required','string','max:50', Rule::unique('auditors','username')->ignore($auditor->id)],
            'password' => ['nullable','string','min:8','confirmed'],
            'status'   => ['required','in:active,inactive'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // never let role be changed here; enforce auditor role
        $data['role'] = 'auditor';

        $auditor->update($data);
        return redirect()->route('admin.auditor.index')->with('ok','Auditor updated.');
    }

    public function destroy($auditorId)
    {
        $auditor = Auditor::findOrFail($auditorId);
        $auditor->delete(); // soft delete
        return back()->with('ok','Auditor deleted.');
    }

    // Quick actions
    public function activate(Auditor $auditor)
    {
        $auditor->update(['status'=>'active']);
        return back()->with('ok','Auditor activated.');
    }

    public function deactivate(Auditor $auditor)
    {
        $auditor->update(['status'=>'inactive']);
        return back()->with('ok','Auditor deactivated.');
    }
}
