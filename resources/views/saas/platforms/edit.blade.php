@extends('layouts.app')

@section('title', '- Edit Platform')
@section('page_title', 'Edit: '.$platform->name)

@section('content')
@include('saas.platforms._form')
@endsection
