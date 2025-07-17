<?php

namespace admin\categories\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use admin\categories\Requests\CategoryCreateRequest;
use admin\categories\Requests\CategoryUpdateRequest;
use admin\categories\Models\Category;
use admin\admin_auth\Services\ImageService;

class CategoryManagerController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
        $this->middleware('admincan_permission:categories_manager_list')->only(['index']);
        $this->middleware('admincan_permission:categories_manager_create')->only(['create', 'store']);
        $this->middleware('admincan_permission:categories_manager_edit')->only(['edit', 'update']);
        $this->middleware('admincan_permission:categories_manager_view')->only(['show']);
        $this->middleware('admincan_permission:categories_manager_delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $categories = Category::with('parent')
                ->filter($request->query('keyword'))
                ->filterByStatus($request->query('status'))
                ->latest()
                ->paginate(Category::getPerPageLimit())
                ->withQueryString();

            return view('category::admin.index', compact('categories'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load categories: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $mainCategories = Category::whereNull('parent_category_id')->orWhere('parent_category_id', 0)->get();
            return view('category::admin.createOrEdit', compact('mainCategories'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load categories: ' . $e->getMessage());
        }
    }

    public function store(CategoryCreateRequest $request)
    {
        try {
            $requestData = $request->validated();

            if ($request->hasFile('image')) {
                $requestData['image'] = $this->imageService->upload($request->file('image'), 'category');
            }

            Category::create($requestData);
            return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load categories: ' . $e->getMessage());
        }
    }

    /**
     * show category details
     */
    public function show(Category $category)
    {
        try {
            $category->load(['parent', 'children']);
            return view('category::admin.show', compact('category'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load categories: ' . $e->getMessage());
        }
    }

    public function edit(Category $category)
    {
        try {
            $mainCategories = Category::whereNull('parent_category_id')->orWhere('parent_category_id', 0)->where('id', '!=', $category->id)->get();
            return view('category::admin.createOrEdit', compact('category', 'mainCategories'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load category for editing: ' . $e->getMessage());
        }
    }

    public function update(CategoryUpdateRequest $request, Category $category)
    {
        try {
            $requestData = $request->validated();


            if ($request->hasFile('image')) {
                $requestData['image'] = $this->imageService->update($request->file('image'), 'category');
            }

            $category->update($requestData);
            return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load category for editing: ' . $e->getMessage());
        }
    }

    public function destroy(Category $category)
    {
        try {
            if ($category->image && \Storage::disk('public')->exists($category->image)) {
                \Storage::disk('public')->delete($category->image);
            }

            $category->delete();
            return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete record.', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $category = Category::findOrFail($request->id);
            $category->status = $request->status;
            $category->save();

            // create status html dynamically        
            $dataStatus = $category->status == '1' ? '0' : '1';
            $label = $category->status == '1' ? 'Active' : 'InActive';
            $btnClass = $category->status == '1' ? 'btn-success' : 'btn-warning';
            $tooltip = $category->status == '1' ? 'Click to change status to inactive' : 'Click to change status to active';

            $strHtml = '<a href="javascript:void(0)"'
                . ' data-toggle="tooltip"'
                . ' data-placement="top"'
                . ' title="' . $tooltip . '"'
                . ' data-url="' . route('admin.categories.updateStatus') . '"'
                . ' data-method="POST"'
                . ' data-status="' . $dataStatus . '"'
                . ' data-id="' . $category->id . '"'
                . ' class="btn ' . $btnClass . ' btn-sm update-status">' . $label . '</a>';

            return response()->json(['success' => true, 'message' => 'Status updated to ' . $label, 'strHtml' => $strHtml]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete record.', 'error' => $e->getMessage()], 500);
        }
    }
}
