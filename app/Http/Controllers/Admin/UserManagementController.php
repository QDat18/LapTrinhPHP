<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\VolunteerProfile;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    public function __construct(){
        $this->middleware(['auth', 'role:Admin']);
    }

    public function index(Request $request)
    {
        $query = User::query();
        if($request->filled('role')){
            $search = $request->search;
            $query ->where(function($q) use ($search){
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orwhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });

            if($request->filled('user_type')){
                $query->where('user_type', $request->user_type);
            }

            if($request->filled('is_verified')){
                $query->where('is_verified', $request->is_verified === '1' ? true : false);
            }

            if($request->filled('city')){
                $query->where('city',  $request->city);
            }
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            $users = $query->paginate(10)->withQueryString();

            $cities = User::select('city')
                ->whereNotNull('city')
                ->distinct()
                ->pluck('city');
            return view('admin.users.index', compact('users', 'cities'));
        }
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        if ($user->user_type == 'Volunteer') {
            $profile = $user->volunteerProfile;
            $applications = $user->applications()
                ->with('opportunity.organization')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            $activities = $user->volunteerActivities()
                ->with('opportunity')
                ->orderBy('activity_date', 'desc')
                ->limit(10)
                ->get();
            $reviews = $user->reviewsReceived()
                ->with('reviewer')
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($user->user_type == 'Organization') {
            $organization = $user->organization;
            $opportunities = $organization->opportunities()
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            $totalVolunteers = $organization->getTotalVolunteersAttribute();
        }
        return view('admin.users.show', compact('user'));    
    }

    public function UpdateStatus(Request $request, $id){
        $user = User::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'is_active' => 'required|boolean',
            'reason' => 'required_if:is_active,0|string|max:500',
        ]);

        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user->is_active = $request->is_active;
        if(!$request->is_active){
            $user->ban_reason = $request->reason;
        }
        else{
            $user->ban_reason = null;
        }
        $user->save();

        $message = $request->is_active ? 'User account has been activated.' : 'User account has been suspended.';
        return redirect()->back()->with('success', $message);
    }

    public function verify($id){
        $user = User::findOrFail($id);
        $user->is_verified = true;
        $user->save();

        return redirect()->back()->with('success', 'User account has been verified.');
    }

    public function destroy(Request $request, $id){
        $user = User::findOrFail($id);
        if($user->user_type == 'Admin'){
            return redirect()->back()->with('error', 'Admin accounts cannot be deleted.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User has been deleted successfully.');
    }
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,verify,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,user_id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $userIds = $request->user_ids;
        $action = $request->action;

        switch ($action) {
            case 'activate':
                User::whereIn('user_id', $userIds)->update(['is_active' => true]);
                $message = 'Selected users have been activated.';
                break;
            
            case 'deactivate':
                User::whereIn('user_id', $userIds)->update(['is_active' => false]);
                $message = 'Selected users have been deactivated.';
                break;
            
            case 'verify':
                User::whereIn('user_id', $userIds)->update(['is_verified' => true]);
                $message = 'Selected users have been verified.';
                break;
            
            case 'delete':
                User::whereIn('user_id', $userIds)
                    ->where('user_type', '!=', 'Admin')
                    ->delete();
                $message = 'Selected users have been deleted.';
                break;
        }

        return redirect()->back()->with('success', $message);
    }

    public function export(Request $request){
        $users = User::all();
        $filename = 'users_' . date('Y-m-d_His'). '.csv';
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        fputcsv($handle, [
            'ID', 'Email', 'First_Name', 'Last_Name', 'Phone', 'User_Type', 'City', 
            'Is_Active', 'Is_Verified', 'Created_At', 'Last_Login'
        ]);
        foreach ($users as $user) {
            fputcsv($handle, [
                $user->user_id,
                $user->email,
                $user->first_name,
                $user->last_name,
                $user->phone,
                $user->user_type,
                $user->city,
                $user->is_verified ? 'Yes' : 'No',
                $user->is_active ? 'Yes' : 'No',
                $user->created_at->format('Y-m-d H:i:s'),
                $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never',
            ]);
        }
        fclose($handle);
        exit;
    }

}