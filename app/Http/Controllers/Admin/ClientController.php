<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditor;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index()
    {
        if (Auth::guard('web')->check()) {
            // Authenticated as auditor
            $clients = Client::orderBy('created_at', 'desc')
                ->paginate(20);
        } else {
            // Default to admin

            $clients = Client::where('auditor_id', Auth::guard('auditor')->id())
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }
        // dd($clients);
        return view('admin.pages.client.index', compact('clients'));
    }

    public function create()
    {
        // Only active auditors for assignment
        $auditors = Auditor::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $statuses = ['Planning', 'Proposal', 'In Progress', 'Completed'];
        return view('admin.pages.client.create', compact('auditors', 'statuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['nullable', 'email', 'max:255'],
            'contact'          => ['nullable', 'string', 'max:255'],
            'company_name'     => ['required', 'string', 'max:255'],
            'audit_start_date' => ['nullable', 'date'],
            'due_date'         => ['nullable', 'date', 'after_or_equal:audit_start_date'],
            'audit_status'     => ['required', Rule::in(['Planning', 'Proposal', 'In Progress', 'Completed'])],
            'auditor_id'       => ['nullable', 'exists:auditors,id'],
        ]);

        // lock role to client
        $data['role'] = 'client';

        Client::create($data);

        return redirect()->route('admin.client.index')->with('ok', 'Client created.');
    }

    public function edit(Client $client)
    {
        $auditors = Auditor::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $statuses = ['Planning', 'Proposal', 'In Progress', 'Completed'];
        return view('admin.pages.client.edit', compact('client', 'auditors', 'statuses'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['nullable', 'email', 'max:255'],
            'contact'          => ['nullable', 'string', 'max:255'],
            'company_name'     => ['required', 'string', 'max:255'],
            'audit_start_date' => ['nullable', 'date'],
            'due_date'         => ['nullable', 'date', 'after_or_equal:audit_start_date'],
            'audit_status'     => ['required', Rule::in(['Planning', 'Proposal', 'In Progress', 'Completed'])],
            'auditor_id'       => ['nullable', 'exists:auditors,id'],
        ]);

        // keep role as client (don’t let UI change it)
        $data['role'] = 'client';

        $client->update($data);

        return redirect()->route('admin.client.index')->with('ok', 'Client updated.');
    }

    public function destroy(Client $client)
    {
        $client->delete(); // soft delete per your migration
        return back()->with('ok', 'Client deleted.');
    }
}
