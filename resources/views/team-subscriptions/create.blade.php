@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-900 text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-6">
                <a href="{{ route('team-subscriptions.index') }}" class="text-blue-400 hover:text-blue-300 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Assign Service Package to Team</h1>
                    <p class="text-gray-400">Create a new service package for the team</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <form action="{{ route('team-subscriptions.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Team Selection -->
                    <div>
                        <label for="team_id" class="block text-sm font-medium text-gray-300 mb-2">Select Team *</label>
                        <select name="team_id" id="team_id" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                            <option value="">Select a team...</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                    {{ $team->name }} - {{ $team->description ? Str::limit($team->description, 50) : 'No description' }}
                                </option>
                            @endforeach
                        </select>
                        @error('team_id')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Service Package Selection -->
                    <div>
                        <label for="service_package_id" class="block text-sm font-medium text-gray-300 mb-2">Select Service Package *</label>
                        <select name="service_package_id" id="service_package_id" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                            <option value="">Select a package...</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ old('service_package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }} - {{ $package->formatted_price }} ({{ $package->duration_days }} days)
                                </option>
                            @endforeach
                        </select>
                        @error('service_package_id')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-300 mb-2">Start date *</label>
                            <input type="date" 
                                   name="start_date" 
                                   id="start_date" 
                                   value="{{ old('start_date', now()->format('Y-m-d')) }}"
                                   required
                                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-300 mb-2">End date *</label>
                            <input type="date" 
                                   name="end_date" 
                                   id="end_date" 
                                   value="{{ old('end_date', now()->addDays(30)->format('Y-m-d')) }}"
                                   required
                                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status *</label>
                        <select name="status" id="status" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="expired" {{ old('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Information -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="paid_amount" class="block text-sm font-medium text-gray-300 mb-2">Paid amount</label>
                            <input type="number" 
                                   name="paid_amount" 
                                   id="paid_amount" 
                                   value="{{ old('paid_amount') }}"
                                   step="0.01"
                                   min="0"
                                   placeholder="0"
                                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                            @error('paid_amount')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-300 mb-2">Payment method</label>
                            <select name="payment_method" id="payment_method" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                                <option value="">Select method...</option>
                                <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank transfer</option>
                                <option value="credit_card" {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit card</option>
                                <option value="free" {{ old('payment_method') === 'free' ? 'selected' : '' }}>Free</option>
                            </select>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="transaction_id" class="block text-sm font-medium text-gray-300 mb-2">Transaction ID</label>
                            <input type="text" 
                                   name="transaction_id" 
                                   id="transaction_id" 
                                   value="{{ old('transaction_id') }}"
                                   placeholder="Transaction ID..."
                                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                            @error('transaction_id')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Auto Renew -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="auto_renew" 
                                   value="1" 
                                   {{ old('auto_renew') ? 'checked' : '' }}
                                   class="rounded border-gray-600 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-300">Auto renew</span>
                        </label>
                        @error('auto_renew')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-300 mb-2">Notes</label>
                        <textarea name="notes" 
                                  id="notes" 
                                  rows="4"
                                  placeholder="Notes about this subscription..."
                                  class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-700">
                        <a href="{{ route('team-subscriptions.index') }}" 
                           class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-colors duration-200">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Assign package
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-calculate end date based on package duration
document.getElementById('service_package_id').addEventListener('change', function() {
    const packageId = this.value;
    const startDate = document.getElementById('start_date').value;
    
    if (packageId && startDate) {
        // Get package duration from the selected option
        const selectedOption = this.options[this.selectedIndex];
        const durationMatch = selectedOption.text.match(/\((\d+)\s+days\)/);
        
        if (durationMatch) {
            const durationDays = parseInt(durationMatch[1]);
            const start = new Date(startDate);
            const end = new Date(start);
            end.setDate(start.getDate() + durationDays);
            
            document.getElementById('end_date').value = end.toISOString().split('T')[0];
        }
    }
});

// Update end date when start date changes
document.getElementById('start_date').addEventListener('change', function() {
    const packageSelect = document.getElementById('service_package_id');
    if (packageSelect.value) {
        packageSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
