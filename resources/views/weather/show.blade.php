@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto p-6 bg-white shadow-2xl rounded-2xl text-center">
    @if(isset($error))
        <p class="text-red-500">{{ $error }}</p>
    @else
        <h1 class="text-2xl font-bold mb-4">Weather in {{ $city }}, {{ $country }}</h1>
        <p class="text-4xl font-semibold">
            {{ $temperature }}°C {{ $sign }}
        </p>
    @endif
</div>
@endsection
