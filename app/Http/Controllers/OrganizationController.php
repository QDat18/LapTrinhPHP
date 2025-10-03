<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OrganizationController extends Controller
{
    /**
     * Display organization profile
     */
    public function show()
    {
        $user = Auth::user();
        
        if (!$user->isOrganization()) {
            abort(403, 'Only organizations can access this page');
        }
        
        $organization = $user->organization;
        
        return view('organization.profile', compact('organization'));
    }
    
    /**
     * Show edit form
     */
    public function edit()
    {
        $user = Auth::user();
        
        if (!$user->isOrganization()) {
            abort(403, 'Only organizations can access this page');
        }
        
        $organization = $user->organization;
        
        return view('organization.edit', compact('organization'));
    }
    
    /**
     * Update organization
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isOrganization()) {
            return response()->json([
                'success' => false,
                'message' => 'Only organizations can update profile'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'organization_name' => 'required|string|max:150',
            'organization_type' => 'required|in:NGO,NPO,Charity,School,Hospital,Community Group',
            'description' => 'nullable|string',
            'mission_statement' => 'nullable|string',
            'website' => 'nullable|url|max:100',
            'contact_person' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:50',
            'founded_year' => 'nullable|integer|min:1900|max:' . date('Y'),
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        
        try {
            $organization = $user->organization;
            $organization->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Organization updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update organization'
            ], 500);
        }
    }
    
    /**
     * Upload verification documents
     */
    public function uploadDocuments(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isOrganization()) {
            return response()->json([
                'success' => false,
                'message' => 'Only organizations can upload documents'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'documents' => 'required|array|max:5',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        
        try {
            $uploadedFiles = [];
            
            foreach ($request->file('documents') as $file) {
                $path = $file->store('organization-documents/' . $user->user_id, 'public');
                $uploadedFiles[] = $path;
            }
            
            // Update organization verification status to Pending
            $organization = $user->organization;
            $organization->update([
                'verification_status' => 'Pending'
            ]);
            
            // Create notification for admin
            \App\Models\Notification::create([
                'user_id' => 1, // Admin user_id
                'notification_type' => 'System',
                'title' => 'New Verification Request',
                'content' => $organization->organization_name . ' has submitted verification documents',
                'related_id' => $organization->org_id,
                'related_type' => 'organization',
                'priority' => 'high',
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Documents uploaded successfully. Verification pending.',
                'files' => $uploadedFiles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload documents'
            ], 500);
        }
    }
    
    /**
     * Request verification
     */
    public function requestVerification(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isOrganization()) {
            return response()->json([
                'success' => false,
                'message' => 'Only organizations can request verification'
            ], 403);
        }
        
        $organization = $user->organization;
        
        if ($organization->verification_status === 'Verified') {
            return response()->json([
                'success' => false,
                'message' => 'Organization is already verified'
            ], 400);
        }
        
        if ($organization->verification_status === 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Verification request is already pending'
            ], 400);
        }
        
        $organization->update([
            'verification_status' => 'Pending'
        ]);
        
        // Notify admin
        \App\Models\Notification::create([
            'user_id' => 1, // Admin
            'notification_type' => 'System',
            'title' => 'Verification Request',
            'content' => $organization->organization_name . ' has requested verification',
            'related_id' => $organization->org_id,
            'related_type' => 'organization',
            'priority' => 'high',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Verification request submitted successfully'
        ]);
    }
    
    /**
     * Get organization statistics
     */
    public function statistics()
    {
        $user = Auth::user();
        
        if (!$user->isOrganization()) {
            return response()->json([
                'success' => false,
                'message' => 'Only organizations can view statistics'
            ], 403);
        }
        
        $organization = $user->organization;
        
        $stats = [
            'total_opportunities' => $organization->total_opportunities,
            'active_opportunities' => $organization->opportunities()->where('status', 'Active')->count(),
            'volunteer_count' => $organization->volunteer_count,
            'rating' => $organization->rating,
            'pending_applications' => $organization->opportunities()
                ->join('applications', 'volunteer_opportunities.opportunity_id', '=', 'applications.opportunity_id')
                ->where('applications.status', 'Pending')
                ->count(),
            'total_applications' => $organization->opportunities()
                ->join('applications', 'volunteer_opportunities.opportunity_id', '=', 'applications.opportunity_id')
                ->count(),
        ];
        
        return response()->json($stats);
    }
    
    /**
     * Get organization dashboard data
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        if (!$user->isOrganization()) {
            abort(403, 'Only organizations can access dashboard');
        }
        
        $organization = $user->organization;
        
        // Recent opportunities
        $recentOpportunities = $organization->opportunities()
            ->latest()
            ->take(5)
            ->get();
        
        // Pending applications
        $pendingApplications = \App\Models\Application::whereHas('opportunity', function($q) use ($organization) {
                $q->where('org_id', $organization->org_id);
            })
            ->where('status', 'Pending')
            ->with(['volunteer', 'opportunity'])
            ->latest()
            ->take(10)
            ->get();
        
        // Statistics
        $stats = [
            'total_opportunities' => $organization->total_opportunities,
            'active_opportunities' => $organization->opportunities()->where('status', 'Active')->count(),
            'volunteer_count' => $organization->volunteer_count,
            'rating' => $organization->rating,
            'pending_applications' => $pendingApplications->count(),
        ];
        
        return view('organization.dashboard', compact('organization', 'recentOpportunities', 'pendingApplications', 'stats'));
    }
}