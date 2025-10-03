<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Mail\AdminBroadcastEmail;

class AdminEmailController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

    }

    /**
     * Send email to users
     */
    public function sendEmail(Request $request)
    {
        $validated = $request->validate([
            'recipient_type' => 'required|in:all,volunteers,organizations,active,single',
            'user_id' => 'nullable|exists:users,user_id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        // Get recipients based on type
        $recipients = $this->getRecipients($validated['recipient_type'], $validated['user_id'] ?? null);

        if ($recipients->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No recipients found'
            ], 400);
        }

        // Send emails
        $sentCount = 0;
        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new AdminBroadcastEmail(
                    $validated['subject'],
                    $validated['message'],
                    $recipient
                ));
                $sentCount++;
            } catch (\Exception $e) {
                \Log::error('Failed to send email to ' . $recipient->email . ': ' . $e->getMessage());
            }
        }

        // Log email activity
        $this->logEmailActivity([
            'recipient_type' => $validated['recipient_type'],
            'recipient_count' => $sentCount,
            'subject' => $validated['subject'],
            'sent_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => "Email sent successfully to $sentCount recipient(s)"
        ]);
    }

    /**
     * Get email history
     */
    public function history()
    {
        $history = \DB::table('email_logs')
            ->join('users', 'email_logs.sent_by', '=', 'users.user_id')
            ->select('email_logs.*', 'users.first_name', 'users.last_name')
            ->orderBy('email_logs.created_at', 'desc')
            ->paginate(20);

        return view('admin.emails.history', compact('history'));
    }

    /**
     * Get recipients based on type
     */
    private function getRecipients($type, $userId = null)
    {
        $query = User::where('is_active', true);

        switch ($type) {
            case 'all':
                // All active users
                break;
                
            case 'volunteers':
                $query->where('user_type', 'Volunteer');
                break;
                
            case 'organizations':
                $query->where('user_type', 'Organization');
                break;
                
            case 'active':
                $query->whereNotNull('last_login_at')
                    ->where('last_login_at', '>=', now()->subDays(30));
                break;
                
            case 'single':
                if ($userId) {
                    $query->where('user_id', $userId);
                }
                break;
        }

        return $query->get();
    }

    /**
     * Log email activity
     */
    private function logEmailActivity($data)
    {
        \DB::table('email_logs')->insert([
            'recipient_type' => $data['recipient_type'],
            'recipient_count' => $data['recipient_count'],
            'subject' => $data['subject'],
            'sent_by' => $data['sent_by'],
            'sent_at' => now(),
            'created_at' => now()
        ]);
    }

    /**
     * Get email templates
     */
    public function getTemplates()
    {
        $templates = [
            'welcome' => [
                'subject' => 'Welcome to VolunteerConnect!',
                'message' => "Dear {name},\n\nWelcome to VolunteerConnect! We're thrilled to have you join our community of volunteers and organizations making a difference.\n\nGet started by:\n- Completing your profile\n- Browsing opportunities\n- Connecting with others\n\nBest regards,\nVolunteerConnect Team"
            ],
            'update' => [
                'subject' => 'Platform Update - New Features Available',
                'message' => "Hello {name},\n\nWe're excited to announce new features on VolunteerConnect:\n\n- Enhanced search filters\n- Improved messaging system\n- Video call integration\n- Analytics dashboard\n\nCheck them out and let us know what you think!\n\nBest regards,\nVolunteerConnect Team"
            ],
            'reminder' => [
                'subject' => 'Reminder: Stay Active on VolunteerConnect',
                'message' => "Hi {name},\n\nWe noticed you haven't been active lately. There are many exciting opportunities waiting for you!\n\n- New opportunities added daily\n- Connect with organizations\n- Make a difference in your community\n\nVisit us today and continue your volunteering journey.\n\nBest regards,\nVolunteerConnect Team"
            ],
            'announcement' => [
                'subject' => 'Important Announcement',
                'message' => "Dear Community,\n\nWe have an important announcement to share with you.\n\n[Your announcement here]\n\nThank you for being part of our community.\n\nBest regards,\nVolunteerConnect Team"
            ],
            'verification_approved' => [
                'subject' => 'Your Organization Has Been Verified!',
                'message' => "Dear {name},\n\nCongratulations! Your organization has been successfully verified on VolunteerConnect.\n\nYou can now:\n- Post unlimited opportunities\n- Access advanced features\n- Receive priority support\n\nStart posting opportunities and finding great volunteers today!\n\nBest regards,\nVolunteerConnect Team"
            ],
            'verification_rejected' => [
                'subject' => 'Organization Verification Status',
                'message' => "Dear {name},\n\nThank you for submitting your organization for verification. Unfortunately, we need additional information before we can approve your organization.\n\nPlease:\n- Check your documentation\n- Ensure all information is accurate\n- Resubmit your application\n\nIf you have questions, please contact our support team.\n\nBest regards,\nVolunteerConnect Team"
            ]
        ];

        return response()->json($templates);
    }
}