<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - {{ config('app.name', 'Laravel') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-mobile-alt text-2xl text-blue-600 mr-3"></i>
                    <h1 class="text-xl font-bold text-gray-800">{{ config('app.name', 'Laravel') }}</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-blue-600">Dashboard</a>
                    <a href="{{ route('users.index') }}" class="text-blue-600 font-medium">Users</a>
                    <a href="{{ route('payments.index') }}" class="text-gray-700 hover:text-blue-600">Payments</a>
                    <a href="{{ route('transfers.index') }}" class="text-gray-700 hover:text-blue-600">Transfers</a>
                    <a href="{{ route('groups.index') }}" class="text-gray-700 hover:text-blue-600">Groups</a>
                    <a href="{{ route('export.index') }}" class="text-gray-700 hover:text-blue-600">Export</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition duration-200">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Users</h2>
                <p class="text-gray-600">Manage verified mobile money users</p>
            </div>
            <a href="{{ route('users.verify') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                <i class="fas fa-user-plus mr-2"></i>Verify New User
            </a>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            @if($users->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="text-left p-4 text-sm font-medium text-gray-600">Name</th>
                                <th class="text-left p-4 text-sm font-medium text-gray-600">Phone</th>
                                <th class="text-left p-4 text-sm font-medium text-gray-600">Gender</th>
                                <th class="text-left p-4 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left p-4 text-sm font-medium text-gray-600">Birth Date</th>
                                <th class="text-left p-4 text-sm font-medium text-gray-600">Verified Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-4 font-medium">{{ $user->first_name }} {{ $user->last_name }}</td>
                                    <td class="p-4">{{ $user->phone }}</td>
                                    <td class="p-4">{{ $user->gender }}</td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-sm text-gray-600">{{ $user->birth_date ? $user->birth_date->format('M d, Y') : 'N/A' }}</td>
                                    <td class="p-4 text-sm text-gray-600">{{ $user->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center">
                    <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600">No users verified yet</p>
                    <a href="{{ route('users.verify') }}" class="inline-block mt-4 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                        Verify First User
                    </a>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
