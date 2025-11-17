<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify User - {{ config('app.name', 'Laravel') }}</title>
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
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Verify User</h2>
            <p class="text-gray-600">Verify mobile money users via MOPAY API</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Verification Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">User Verification</h3>

                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('message'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('message') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('users.verify') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number
                        </label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="+250XXXXXXXXX"
                            value="{{ old('phone') }}"
                        >
                        <p class="text-sm text-gray-500 mt-1">Enter the phone number to verify</p>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
                    >
                        <i class="fas fa-search mr-2"></i>
                        Verify User
                    </button>
                </form>
            </div>

            <!-- User Details -->
            @if($userData)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">User Details</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Name:</span>
                            <span>{{ $userData['firstName'] }} {{ $userData['lastName'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Phone:</span>
                            <span>{{ $userData['phone'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Gender:</span>
                            <span>{{ $userData['gender'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Birth Date:</span>
                            <span>{{ $userData['birthDate'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Status:</span>
                            <span class="px-2 py-1 text-xs rounded-full {{ $userData['isActive'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $userData['isActive'] ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Recent Users -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Recent Verified Users</h3>
            @if($recentUsers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Name</th>
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Phone</th>
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Gender</th>
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Verified Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentUsers as $user)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 font-medium">{{ $user->first_name }} {{ $user->last_name }}</td>
                                    <td class="p-3">{{ $user->phone }}</td>
                                    <td class="p-3">{{ $user->gender }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-600">{{ $user->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-600">No users verified yet</p>
            @endif
        </div>
    </div>
</body>
</html>
