@extends('layouts.app')

@section('title', '- New Company')
@section('page_title', 'New Company')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-plus-lg"></i> Create a tenant company</div>
            <div class="card-body">
                @include('saas.companies._form')
                <button form="companyForm" type="submit" class="btn btn-warning mt-3">
                    <i class="bi bi-plus-lg"></i> Create company
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
