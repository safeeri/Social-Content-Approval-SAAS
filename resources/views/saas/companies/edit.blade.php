@extends('layouts.app')

@section('title', '- Edit Company')
@section('page_title', 'Edit: '.$company->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-building"></i> {{ $company->name }}</div>
            <div class="card-body">
                @include('saas.companies._form')
                <button form="companyForm" type="submit" class="btn btn-warning mt-3">
                    <i class="bi bi-check-lg"></i> Save changes
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
