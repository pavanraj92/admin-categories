@extends('admin::admin.layouts.master')

@section('title', 'Categories Management')

@section('page-title', 'Category Details')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">
        <a href="{{ route('admin.categories.index') }}">Category Manager</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Category Details</li>
@endsection

@section('content')
    <!-- Container fluid  -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Header with Back button -->
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h4 class="card-title mb-0">{{ $category->title ?? 'N/A' }} - Category #{{ $category->sort_order }}</h4>
                            <div>
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary ml-2">
                                    Back
                                </a>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Category Information -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header bg-primary">
                                        <h5 class="mb-0 text-white font-bold">Category Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Image:</label>
                                                    <p>
                                                        @if ($category->image)
                                                            <img src="{{ asset('storage/'.$category->image) }}"
                                                                 alt="{{ $category->title }}"
                                                                 class="img-fluid rounded"
                                                                 style="max-width: 200px; max-height: 120px;">
                                                        @else
                                                            N/A
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Title:</label>
                                                    <p>{{ $category->title ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Parent Category:</label>
                                                    <p>{{ $category->parent ? $category->parent->title : '—' }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Sort Order:</label>
                                                    <p>{{ $category->sort_order ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        @if($category->children && $category->children->count())
                                            <div class="form-group">
                                                <label class="font-weight-bold">Subcategories:</label>
                                                <ul class="mb-0">
                                                    @foreach($category->children as $child)
                                                        <li>{{ $child->title }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Status:</label>
                                                    <p>{!! config('category.constants.aryStatusLabel.' . $category->status, 'N/A') !!}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Created At:</label>
                                                    <p>
                                                        {{ $category->created_at
                                                            ? $category->created_at->format(config('GET.admin_date_time_format') ?? 'Y-m-d H:i:s')
                                                            : '—' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions & SEO -->
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card">
                                        @include('admin::admin.seo_meta_data.view', ['seo' => $seo])
                                    </div>
                                    <div class="card-header bg-primary">
                                        <h5 class="mb-0 text-white font-bold">Quick Actions</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex flex-column">
                                            @admincan('categories_manager_edit')
                                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning mb-2">
                                                <i class="mdi mdi-pencil"></i> Edit Category
                                            </a>
                                            @endadmincan

                                            @admincan('categories_manager_delete')
                                                <button type="button" class="btn btn-danger delete-btn delete-record"
                                                    title="Delete this record"
                                                    data-url="{{ route('admin.categories.destroy', $category) }}"
                                                    data-redirect="{{ route('admin.categories.index') }}"
                                                    data-text="Are you sure you want to delete this record?"
                                                    data-method="DELETE">
                                                    <i class="mdi mdi-delete"></i> Delete Category
                                                </button>
                                            @endadmincan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- row end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Container fluid  -->
@endsection
