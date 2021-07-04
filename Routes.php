<?php
    return [
        #LOGIN + REGISTER USER + RESTART PASS
        App\Core\Route::get('|^user/register/?$|',                  'Main',                  'getRegister'),
        App\Core\Route::post('|^user/register/?$|',                 'Main',                  'postRegister'),
        
        App\Core\Route::get('|^user/login/?$|',                     'Main',                  'getLogin'),
        App\Core\Route::post('|^user/login/?$|',                    'Main',                  'postLogin'),
        App\Core\Route::get('|^student/logout/?$|',                 'Main',                  'getLogout'),
        
        App\Core\Route::get('|^user/restartPass/?$|',               'Main',                  'getRestartPass'),
        App\Core\Route::post('|^user/restartPass/?$|',              'Main',                  'postRestartPass'),

        #LOGIN + REGISTER ADMIN
        App\Core\Route::get('|^admin/register/?$|',                 'Main',                  'getAdminRegister'),
        App\Core\Route::post('|^admin/register/?$|',                'Main',                  'postRegisterAdmin'),
        App\Core\Route::get('|^admin/login/?$|',                    'Main',                  'getLoginAdmin'),
        App\Core\Route::post('|^admin/login/?$|',                   'Main',                  'postLoginAdmin'),
        App\Core\Route::get('|^admin/logout/?$|',                   'Main',                  'getLogoutAdmin'),

        App\Core\Route::post('|^term/edit/?$|',                     'Main',                  'postEditTerm'),

        App\Core\Route::post('|^admin/term/edit/?$|',               'AdminDashboard',        'postEditTermAdmin'),
        App\Core\Route::post('|^student/term/edit/?$|',             'StudentDashboard',      'postEditTermStudent'),
        
        App\Core\Route::get('|^admin/profile/?$|',                  'AdminDashboard',        'home'),
        App\Core\Route::get('|^student/profile/?$|',                'StudentDashboard',      'home'),

        App\Core\Route::get('|^handle/?$|',                         'EventHandler',          'handle'),

        App\Core\Route::get('|^admin/calendar(?:/([0-9]{4})/([0-9]{1,2}))?/?$|',             'AdminDashboard',        'calendarAdmin'),
        App\Core\Route::get('|^admin/agenda(?:/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2}))?/?$|',  'AdminDashboard',        'agendaAdmin'),

        App\Core\Route::post('|^admin/agenda/delete/([0-9]+)/?$|',  'AdminDashboard',        'postDelete'),

        #API
        App\Core\Route::post('|^api/getTermStates/?$|',             'ApiTerm',               'getTermStates'),

        #fallback route
        App\Core\Route::any('|^.*$|',                               'Main',                  'home'),
    ];