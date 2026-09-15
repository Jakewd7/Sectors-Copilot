{{-- Adapter: keeps @extends('layouts.app') working now that the real shell is a component.
     Thin wrapper so existing `@extends` + `@section('content')` views keep working
     unchanged; new views should use <x-app-shell> directly. --}}
<x-app-shell :title="View::getSection('title')">
    @yield('content')
</x-app-shell>
