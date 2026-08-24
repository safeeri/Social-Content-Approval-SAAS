@extends('layouts.app')

@section('title', '- Edit Client')
@section('page_title', 'Edit: '.$client->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-people"></i> {{ $client->name }}</div>
            <div class="card-body">
                @include('company.clients._form')
                <button form="clientForm" type="submit" class="btn btn-warning mt-3">
                    <i class="bi bi-check-lg"></i> Save changes
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
