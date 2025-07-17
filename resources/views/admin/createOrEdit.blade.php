@extends('admin::admin.layouts.master')

@section('title', 'Categories Management')

@section('page-title', isset($category) ? 'Edit Category' : 'Create Category')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page"><a href="{{ route('admin.categories.index') }}">Manage Categories</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{isset($category) ? 'Edit Category' : 'Create Category'}}</li>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Start category Content -->
        <div class="row">
            <div class="col-12">
                <div class="card card-body">
                    <form action="{{ isset($category) ? route('admin.categories.update', $category->id) : route('admin.categories.store') }}"
                        method="POST" id="categoryForm"  enctype="multipart/form-data">
                        @if (isset($category))
                            @method('PUT')
                        @endif
                        @csrf
                        <div class="row">
                            <div class="col-md-6">                                
                                <div class="form-group">
                                    <label>Title<span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control alphabets-only"
                                        value="{{ $category?->title ?? old('title') }}" required>
                                    @error('title')
                                        <div class="text-danger validation-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Parent Category</label>
                                    <select name="parent_category_id" class="form-control select2">
                                        <option value="0">None (Main Category)</option>
                                        @if(isset($mainCategories))
                                            @foreach($mainCategories as $mainCategory)
                                                <option value="{{ $mainCategory->id }}" 
                                                    @if(($category->parent_category_id ?? old('parent_category_id')) == $mainCategory->id) selected @endif>
                                                    {{ $mainCategory->title }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('parent_category_id')
                                        <div class="text-danger validation-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">                                
                                <div class="form-group">
                                    <label>Sort Order<span class="text-danger">*</span></label>
                                    <input type="text" name="sort_order" class="form-control numbers-only"
                                        value="{{ $category?->sort_order ?? old('sort_order') }}" required>
                                    @error('sort_order')
                                        <div class="text-danger validation-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status<span class="text-danger">*</span></label>
                                    <select name="status" class="form-control select2" required>
                                        <option value="1" {{ (($category?->status ?? old('status')) == '1') ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ (($category?->status ?? old('status')) == '0') ? 'selected' : '' }}>InActive</option>
                                    </select>
                                    @error('status')
                                        <div class="text-danger validation-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">                                
                                <div class="form-group">
                                    <label>Image<span class="text-danger">*</span></label>
                                    <input type="file" name="image" class="form-control" id="imageInput" {{ isset($category) ? '' : 'required' }}>
                                    @error('image')
                                        <div class="text-danger validation-error">{{ $message }}</div>
                                    @enderror

                                    <div id="imagePreview" style="margin-top:10px;">
                                        @if(isset($category) && $category->image)
                                            <img src="{{ asset('storage/'.$category->image) }}" alt="Category Image" style="max-width: 200px; max-height: 120px;">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- End category Content -->
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.0.0/ckeditor5.css">
    <!-- Select2 CSS & JS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Custom CSS for the category -->
    <link rel="stylesheet" href="{{ asset('backend/custom.css') }}">           
@endpush

@push('scripts')
    <!-- Then the jQuery Validation plugin -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <!-- Select2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2 for any select elements with the class 'select2'
            $('.select2').select2();

            $.validator.addMethod(
                "alphabetsOnly",
                function (value, element) {
                    return this.optional(element) || /^[a-zA-Z\s]+$/.test(value);
                },
                "Please enter letters only"
            );

            //jquery validation for the form
            $('#categoryForm').validate({
                ignore: [],
                rules: {
                    title: {
                        required: true,
                        minlength: 3,
                        alphabetsOnly: true
                    },
                    sort_order: {
                        required: true,
                        digits: true,
                        min: 1,
                    }
                },
                messages: {
                    title: {
                        required: "Please enter a title",
                        minlength: "Title must be at least 3 characters long"
                    },
                    sort_order: {
                        required: "Please enter a sort order",
                        digits: "Sort order must be a valid number",
                        min: "Sort order must be at least 1"
                    },
                    image: {
                       required: "Image is required",
                    }
                },
                submitHandler: function(form) {
                    const $btn = $('#saveBtn');
                    $btn.prop('disabled', true).text('Saving...');
                    form.submit();
                },
                errorElement: 'div',
                errorClass: 'text-danger custom-error',
                errorPlacement: function(error, element) {
                    $('.validation-error').hide(); // hide blade errors
                    error.insertAfter(element);
                }
            });

            $('#imageInput').on('change', function(event) {
                const input = event.target;
                const preview = $('#imagePreview');
                preview.empty(); // Remove old image

                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.html('<img src="' + e.target.result + '" style="max-width:200px; max-height:120px;" />');
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            });
        });
    </script>
@endpush
