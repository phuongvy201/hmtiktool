<?php

namespace App\Http\Controllers;

use App\Models\ServicePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServicePackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view-service-packages');

        $query = ServicePackage::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by featured
        if ($request->filled('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        $packages = $query->orderBy('sort_order')->orderBy('name')->paginate(10)->withQueryString();

        return view('service-packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-service-packages');

        return view('service-packages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-service-packages');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'duration_days' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'max_users' => 'required|integer|min:1',
            'max_projects' => 'required|integer|min:1',
            'max_storage_gb' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'limitations' => 'nullable|array',
            'limitations.*' => 'string',
            'sort_order' => 'integer|min:0',
        ]);

        // Generate slug from name
        $validated['slug'] = Str::slug($validated['name']);

        // Ensure slug is unique
        $counter = 1;
        $originalSlug = $validated['slug'];
        while (ServicePackage::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        ServicePackage::create($validated);

        return redirect()->route('service-packages.index')
            ->with('success', 'Gói dịch vụ đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ServicePackage $servicePackage)
    {
        $this->authorize('view-service-packages');

        $servicePackage->load(['subscriptions.user', 'activeSubscriptions.user']);

        return view('service-packages.show', compact('servicePackage'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServicePackage $servicePackage)
    {
        $this->authorize('edit-service-packages');

        return view('service-packages.edit', compact('servicePackage'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServicePackage $servicePackage)
    {
        $this->authorize('edit-service-packages');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'duration_days' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'max_users' => 'required|integer|min:1',
            'max_projects' => 'required|integer|min:1',
            'max_storage_gb' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'limitations' => 'nullable|array',
            'limitations.*' => 'string',
            'sort_order' => 'integer|min:0',
        ]);

        // Generate new slug if name changed
        if ($servicePackage->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);

            // Ensure slug is unique
            $counter = 1;
            $originalSlug = $validated['slug'];
            while (ServicePackage::where('slug', $validated['slug'])
                ->where('id', '!=', $servicePackage->id)
                ->exists()
            ) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $servicePackage->update($validated);

        return redirect()->route('service-packages.index')
            ->with('success', 'Gói dịch vụ đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServicePackage $servicePackage)
    {
        $this->authorize('delete-service-packages');

        // Check if package has active subscriptions
        if ($servicePackage->activeSubscriptions()->exists()) {
            return redirect()->back()
                ->with('error', 'Không thể xóa gói dịch vụ đang có người dùng đăng ký.');
        }

        $servicePackage->delete();

        return redirect()->route('service-packages.index')
            ->with('success', 'Gói dịch vụ đã được xóa thành công.');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(ServicePackage $servicePackage)
    {
        $this->authorize('edit-service-packages');

        $servicePackage->update(['is_active' => !$servicePackage->is_active]);

        $status = $servicePackage->is_active ? 'kích hoạt' : 'vô hiệu hóa';

        return redirect()->back()
            ->with('success', "Gói dịch vụ đã được {$status} thành công.");
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(ServicePackage $servicePackage)
    {
        $this->authorize('edit-service-packages');

        $servicePackage->update(['is_featured' => !$servicePackage->is_featured]);

        $status = $servicePackage->is_featured ? 'nổi bật' : 'bỏ nổi bật';

        return redirect()->back()
            ->with('success', "Gói dịch vụ đã được {$status} thành công.");
    }
}
