<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @section('title', __('Forbidden'))

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex items-center justify-center" >
    @extends('errors::minimal')

    <img src="{{ url('images/Desktop - ERROR 403 UNAUTHORIZED ACCESS.png')}}" alt="" class="">


</body>

</html>
