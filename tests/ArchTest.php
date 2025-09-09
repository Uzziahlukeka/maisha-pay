<?php

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch()
    ->expect('src\Models')
    ->toBeClasses()
    ->toExtend('Illuminate\Database\Eloquent\Model');

arch()
    ->expect('src\Exceptions')
    ->toBeClasses()
    ->toExtend('Exception');

arch()
    ->expect('src\Facades')
    ->toBeClasses()
    ->toExtend('Illuminate\Support\Facades\Facade');

arch()
    ->expect('src\Http\Controllers')
    ->toBeClasses()
    ->toExtend('Illuminate\Routing\Controller')
    ->toHaveMethod('handleCallback');
