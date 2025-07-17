@extends('admin::admin.layouts.master')

@section('title', 'Categories Management')

@section('page-title', 'Category Details')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page"><a href="{{ route('admin.categories.index') }}">Manage Categories</a></li>
    <li class="breadcrumb-item active" aria-current="page">Category Details</li>
@endsection

@section('content')
    <!-- Container fluid  -->
    <div class="container-fluid">
        <!-- Start Email Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">                    
                    <div class="table-responsive">
                         <div class="card-body">      
                            <table class="table table-responsive-lg table-no-border">
                                <tbody>
                                    <tr>
                                        <th scope="row">Image</th>
                                        <td scope="col">
                                            @if ($category->image)
                                                <img src="{{ asset('storage/'.$category->image) }}" alt="{{ $category->title }}" class="img-fluid" style="max-width: 200px; max-height: 120px;">
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>                      
                                    <tr>
                                        <th scope="row">Title</th>
                                        <td scope="col">{{ $category->title ?? 'N/A' }}</td>
                                    </tr>         
                                    <tr>
                                        <th scope="row">Slug</th>
                                        <td scope="col">{{ $category->slug ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Parent Category</th>
                                        <td scope="col">
                                            @if($category->parent)
                                                {{ $category->parent->title }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>

                                    @if($category->children && $category->children->count())
                                    <tr>
                                        <th scope="row">Subcategories</th>
                                        <td scope="col">
                                            <ul class="mb-0">
                                                @foreach($category->children as $child)
                                                    <li>{{ $child->title }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th scope="row">Sort Order</th>
                                        <td scope="col">{{ $category->sort_order }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Status</th>
                                        <td scope="col"> {!! config('admin.constants.aryStatusLabel.' . $category->status, 'N/A') !!}</td>
                                    </tr>    
                                    <tr>
                                        <th scope="row">Created At</th>
                                        <td scope="col"> {{ $category->created_at
                                            ? $category->created_at->format(config('GET.admin_date_time_format') ?? 'Y-m-d H:i:s')
                                            : '—' }}</td>
                                    </tr>                                
                                </tbody>
                            </table>   
                                             
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Back</a> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End category Content -->
    </div>
    <!-- End Container fluid  -->
@endsection
