<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Verification - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">

    @include('admin.partials.navbar')

    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-building mr-3 text-orange-600"></i>
                Organization Verification
            </h1>
            <p class="text-gray-600 mt-2">Review and verify organization registrations</p>
        </div>

        <!-- Status Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('admin.organizations.verification', ['status' => 'Pending']) }}" 
                   class="py-4 px-1 border-b-2 font-medium text-sm
                          {{ $status == 'Pending' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-clock mr-2"></i>
                    Pending ({{ $organizations->total() }})
                </a>
                <a href="{{ route('admin.organizations.verification', ['status' => 'Verified']) }}" 
                   class="py-4 px-1 border-b-2 font-medium text-sm
                          {{ $status == 'Verified' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-check-circle mr-2"></i>
                    Verified
                </a>
                <a href="{{ route('admin.organizations.verification', ['status' => 'Rejected']) }}" 
                   class="py-4 px-1 border-b-2 font-medium text-sm
                          {{ $status == 'Rejected' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-times-circle mr-2"></i>
                    Rejected
                </a>
            </nav>
        </div>

        <!-- Search Bar -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <form method="GET" action="{{ route('admin.organizations.verification') }}" class="flex gap-4">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search organizations..."
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg transition">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
            </form>
        </div>

        <!-- Organizations List -->
        <div class="space-y-4">
            @forelse($organizations as $org)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-3">
                                <h3 class="text-xl font-bold text-gray-800">{{ $org->organization_name }}</h3>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full
                                    {{ $org->verification_status == 'Pending' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $org->verification_status == 'Verified' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $org->verification_status == 'Rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $org->verification_status }}
                                </span>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $org->organization_type }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 mb-4">
                                <div>
                                    <i class="fas fa-user mr-2 text-gray-400"></i>
                                    <strong>Contact:</strong> {{ $org->contact_person ?? $org->user->first_name . ' ' . $org->user->last_name }}
                                </div>
                                <div>
                                    <i class="fas fa-envelope mr-2 text-gray-400"></i>
                                    <strong>Email:</strong> {{ $org->user->email }}
                                </div>
                                <div>
                                    <i class="fas fa-phone mr-2 text-gray-400"></i>
                                    <strong>Phone:</strong> {{ $org->user->phone ?? 'N/A' }}
                                </div>
                                <div>
                                    <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                    <strong>Founded:</strong> {{ $org->founded_year ?? 'N/A' }}
                                </div>
                                <div>
                                    <i class="fas fa-id-card mr-2 text-gray-400"></i>
                                    <strong>Registration #:</strong> {{ $org->registration_number ?? 'N/A' }}
                                </div>
                                <div>
                                    <i class="fas fa-clock mr-2 text-gray-400"></i>
                                    <strong>Submitted:</strong> {{ $org->created_at->format('M d, Y') }}
                                </div>
                            </div>

                            @if($org->description)
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <p class="text-sm text-gray-700 line-clamp-3">{{ $org->description }}</p>
                            </div>
                            @endif

                            @if($org->mission_statement)
                            <div class="mb-4">
                                <h4 class="font-semibold text-gray-700 mb-2">Mission Statement:</h4>
                                <p class="text-sm text-gray-600 italic">{{ $org->mission_statement }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                        <a href="{{ route('admin.organizations.show', $org->org_id) }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-eye mr-2"></i>View Details
                        </a>

                        @if($org->verification_status == 'Pending')
                        <button onclick="openApproveModal({{ $org->org_id }})" 
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-check mr-2"></i>Approve
                        </button>
                        <button onclick="openRejectModal({{ $org->org_id }})" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-times mr-2"></i>Reject
                        </button>
                        <button onclick="openDocumentRequestModal({{ $org->org_id }})" 
                                class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-file-upload mr-2"></i>Request Docs
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-building text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Organizations Found</h3>
                <p class="text-gray-500">No organizations with {{ $status }} status</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($organizations->hasPages())
        <div class="mt-6">
            {{ $organizations->links() }}
        </div>
        @endif
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                Approve Organization
            </h3>
            <p class="text-gray-600 mb-6">Are you sure you want to verify and approve this organization? They will be able to post volunteer opportunities.</p>
            <form id="approveForm" method="POST" action="">
                @csrf
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('approveModal')" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition">
                        <i class="fas fa-check mr-2"></i>Approve
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-times-circle text-red-600 mr-2"></i>
                Reject Organization
            </h3>
            <form id="rejectForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason *</label>
                    <textarea name="rejection_reason" rows="4" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                              placeholder="Explain why this organization is being rejected..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('rejectModal')" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition">
                        <i class="fas fa-times mr-2"></i>Reject
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Request Documents Modal -->
    <div id="documentRequestModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-file-upload text-yellow-600 mr-2"></i>
                Request Additional Documents
            </h3>
            <form id="documentRequestForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Document Request *</label>
                    <textarea name="document_request" rows="4" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500"
                              placeholder="Specify which additional documents are needed..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('documentRequestModal')" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg transition">
                        <i class="fas fa-paper-plane mr-2"></i>Send Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openApproveModal(orgId) {
            document.getElementById('approveForm').action = `/admin/organizations/${orgId}/approve`;
            document.getElementById('approveModal').classList.remove('hidden');
        }

        function openRejectModal(orgId) {
            document.getElementById('rejectForm').action = `/admin/organizations/${orgId}/reject`;
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function openDocumentRequestModal(orgId) {
            document.getElementById('documentRequestForm').action = `/admin/organizations/${orgId}/request-documents`;
            document.getElementById('documentRequestModal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = ['approveModal', 'rejectModal', 'documentRequestModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target == modal) {
                    closeModal(modalId);
                }
            });
        }
    </script>

</body>
</html>
