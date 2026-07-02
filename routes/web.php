<?php
// Simple route map supporting GET and POST.
// Map: '/path' => 'Controller@method' (or a callable for custom behavior)

return [
    'GET' => [
        '/' => 'PageController@index',
        '/homepage' => 'PageController@homepage',
        '/blog' => 'PageController@blog',
        '/deget' => 'PageController@deget',
        '/alergologji' => 'PageController@alergologji',
        '/deshmi' => 'PageController@deshmi',
        '/login' => 'PageController@login',
        '/register' => 'PageController@register',
        '/admin/dashboard' => 'PageController@adminDashboard',
        '/admin/login' => 'AdminController@login',
        '/admin/reservations' => 'AdminController@reservations',

        '/patient/dashboard' => 'PageController@patientDashboard',
        '/patient/appointments' => 'PageController@patientAppointments',

        '/doctor/dashboard' => 'PageController@doctorDashboard',
        '/doctor/patients' => 'PageController@doctorPatients',
        '/doctor/requests' => 'PageController@doctorRequests',
        '/doctor/schedule' => 'PageController@doctorSchedule',
        '/doctor/appointments' => 'PageController@doctorAppointments',
        
        '/logout' => 'AuthController@logout',
        '/rezervoni' => 'PageController@rezervoni',
        '/healthcard' => 'PageController@healthcard',
        '/sherbimet' => 'PageController@sherbimet',
        '/stafimjekesor' => 'PageController@stafimjekesor',
        '/tereja' => 'PageController@tereja',
        '/rrethnesh' => 'PageController@rrethnesh',
        '/kontakt' => 'PageController@kontakt',
        '/profile' => 'PageController@profile',
        // '/my_appointments' => 'PageController@myAppointments',
    ],

    'POST' => [
        // API endpoints mapped to controllers
        '/api/login' => 'AuthController@login',
        '/api/register' => 'AuthController@register',
        '/api/logout' => 'AuthController@logout',
        // keep existing file-based endpoints intact unless replaced here
    ],

    'ANY' => [
        // Fallbacks
    ]
];
