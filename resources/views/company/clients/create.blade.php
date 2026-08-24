@extends('layouts.app')

@section('title', '- New Client')
@section('page_title', 'New Client')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-plus-lg"></i> Client details</div>
            <div class="card-body">
                @include('company.clients._form')
                <button form="clientForm" type="submit" class="btn btn-warning mt-3">
                    <i class="bi bi-plus-lg"></i> Create client
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
