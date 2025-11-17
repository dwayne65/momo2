<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - {{ config('app.name', 'Laravel') }}</title>
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
                    <a href="{{ route('dashboard') }}" class="text-blue-600 font-medium">Dashboard</a>
                    <a href="{{ route('users.index') }}" class="text-gray-700 hover:text-blue-600">Users</a>
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
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Dashboard</h2>
            <p class="text-gray-600">Overview of your mobile money system</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Users</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($userCount) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-credit-card text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Payments</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($paymentCount) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i class="fas fa-exchange-alt text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Transfers</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($transferCount) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-users-cog text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Groups</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($groupCount) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Financial Summary</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-4 bg-green-50 rounded-lg">
                        <span class="text-gray-700">Total Payments</span>
                        <span class="text-2xl font-bold text-green-600">{{ number_format($totalPayments, 2) }} RWF</span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-yellow-50 rounded-lg">
                        <span class="text-gray-700">Total Transfers</span>
                        <span class="text-2xl font-bold text-yellow-600">{{ number_format($totalTransfers, 2) }} RWF</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('users.verify') }}" class="block w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-200 text-center">
                        <i class="fas fa-user-check mr-2"></i>Verify User
                    </a>
                    <a href="{{ route('payments.process') }}" class="block w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition duration-200 text-center">
                        <i class="fas fa-credit-card mr-2"></i>Process Payment
                    </a>
                    <a href="{{ route('transfers.process') }}" class="block w-full bg-yellow-600 text-white py-2 px-4 rounded-md hover:bg-yellow-700 transition duration-200 text-center">
                        <i class="fas fa-exchange-alt mr-2"></i>Make Transfer
                    </a>
                    <a href="{{ route('groups.index') }}" class="block w-full bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700 transition duration-200 text-center">
                        <i class="fas fa-users-cog mr-2"></i>Manage Groups
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Payments -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Recent Payments</h3>
                @if($recentPayments->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentPayments as $payment)
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $payment->phone }}</p>
                                    <p class="text-sm text-gray-600">{{ $payment->created_at->format('M d, Y H:i') }}</p>
                                </div>
                                <span class="text-green-600 font-bold">{{ number_format($payment->amount, 2) }} RWF</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600">No recent payments</p>
                @endif
            </div>

            <!-- Recent Transfers -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Recent Transfers</h3>
                @if($recentTransfers->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentTransfers as $transfer)
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $transfer->receiver_phone }}</p>
                                    <p class="text-sm text-gray-600">{{ $transfer->created_at->format('M d, Y H:i') }}</p>
                                </div>
                                <span class="text-yellow-600 font-bold">{{ number_format($transfer->amount, 2) }} RWF</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600">No recent transfers</p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
