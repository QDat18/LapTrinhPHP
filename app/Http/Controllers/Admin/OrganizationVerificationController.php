<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class OrganizationVerificationController extends Controller{
    public function __construct(){
        $this->middleware(['auth', 'role: Admin']);
    }
    public function index(Request $request)
    {
        $query = Organization::with('user');

        $status = $request->get('status', 'Pending');
        $query->where('verification_status', $status);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('organization_name', 'LIKE', "%{$search}%");
        }

        $organizations = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.organizations.verification', compact('organizations', 'status'));
    }

    public function show($id)
    {
        $organization = Organization::with('user')->findOrFail($id);
        $stats = [
            'total_opportunities' => $organization->opportunities()->count(),
            'active_opportunities' => $organization->opportunities()->where('status', 'Active')->count(),
            'total_volunteers' => $organization->getTotalVolunteersAttribute(),
            'total_hours_received' => $organization->getTotalHoursAttribute(),
        ];
        return view('admin.organizations.show', compact('organization', 'stats'));
    }
    
    public function approve(Request $request, $id)
    {
        $organization = Organization::findOrFail($id);
        
        $organization->verification_status = 'Verified';
        $organization->save();

        $user = $organization->user;
        $user->is_verified = true;
        $user->save();
        return redirect()->back()->with('success', 'Organization approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $organization = Organization::findOrFail($id);

        $organization->verification_status = 'Rejected';
        $organization->rejection_reason = $request->rejection_reason;
        $organization->save();
        return redirect()->back()->with('success', 'Organization verification has been rejected.');
    }

    public function requestDocuments(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'document_request' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $organization = Organization::findOrFail($id);
        
        // TODO: Create notification for organization
        
        // TODO: Send email requesting additional documents

        return redirect()->back()->with('success', 'Document request has been sent to the organization.');
    }
}


// Continue with more controllers...
// Next: ReviewModerationController, ReportGenerationController, etc.