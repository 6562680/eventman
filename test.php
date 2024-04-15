<?php

use Gzhegow\Eventman\Eventman;
use Gzhegow\Eventman\EventmanFactory;
use Gzhegow\Eventman\Point\DemoPoint;
use Gzhegow\Eventman\Pipeline\Pipeline;
use Gzhegow\Eventman\Subscriber\DemoSubscriber;


require_once __DIR__ . '/vendor/autoload.php';


error_reporting(E_ALL);

set_error_handler(function ($errseverity, $errmsg, $errfile, $errline) {
    throw new ErrorException($errmsg, -1, $errseverity, $errline, $errline);
});


function eventHandler_sayHelloWorld($point)
{
    echo __FUNCTION__ . PHP_EOL;

    echo 'Hello, World!' . PHP_EOL;
}

function filterHandler_changeTrueToFalse($point, $bool)
{
    echo __FUNCTION__ . PHP_EOL;

    return ! $bool;
}

function middleware_wrapExisting(Pipeline $pipeline, $point, $input = null, $context = null)
{
    echo __FUNCTION__ . '@before' . PHP_EOL;

    $result = $pipeline->next($point, $input, $context);

    echo __FUNCTION__ . '@after' . PHP_EOL;

    return $result;
}


// > напишите свою фабрику, которая будет использовать контейнер зависимостей
$eventmanFactory = new EventmanFactory();

// > создаем Eventman
$eventman = new Eventman($eventmanFactory);

// > регистрируем событие
$eventman->onEvent(DemoPoint::class, 'eventHandler_sayHelloWorld');
// > оборачиваем все вызовы события в middleware
$eventman->middleEvent(DemoPoint::class, 'middleware_wrapExisting');

// > регистрируем фильтр
$eventman->onFilter(DemoPoint::class, 'filterHandler_changeTrueToFalse');
// > оборачиваем все вызовы события в middleware
$eventman->middleFilter(DemoPoint::class, 'middleware_wrapExisting');

// > регистрируем подписчика (события/фильтры + обработчики/мидлвары их обслуживающие в одном классе)
$eventman->subscribe(DemoSubscriber::class);

// > создаем конвеер в отрыве от точки привязки (если триггерить событие не нужно, а нужен конвеер)
$pipeEvent = $eventman->pipelineEvent(
    [ 'eventHandler_sayHelloWorld' ],
    [ 'middleware_wrapExisting' ]
);
// > создаем конвеер в отрыве от точки привязки (если триггерить событие не нужно, а нужен конвеер)
$pipeFilter = $eventman->pipelineFilter(
    [ 'filterHandler_changeTrueToFalse' ],
    [ 'middleware_wrapExisting' ]
);


// > можно задать какие-то свои настройки, которые будут нужны позже
$context = 'My Custom Data';
// $context = ['my_key' => 'My Custom Data'];
// $context = (object) ['my_key' => 'My Custom Data'];

// > стреляем событием, событие не возвращает данных
$input = null; // > можно передать входные данные, конвеер передаст оригинал в каждый обработчик
$eventman->fireEvent(DemoPoint::class, $input, $context);
// > middleware_wrapExisting@before
// > Gzhegow\Eventman\Subscriber\DemoSubscriber::demoMiddleware@before
// > Gzhegow\Eventman\Handler\DemoMiddleware::handle@before
// > eventHandler_sayHelloWorld
// > Hello, World!
// > Gzhegow\Eventman\Subscriber\DemoSubscriber::demoEvent
// > Gzhegow\Eventman\Handler\DemoEventHandler::handle
// > Gzhegow\Eventman\Handler\DemoMiddleware::handle@after
// > Gzhegow\Eventman\Subscriber\DemoSubscriber::demoMiddleware@after
// > middleware_wrapExisting@after

echo PHP_EOL;

// > фильтруем переменную, проводя её через все назначенные фильтры
$input = true; // > входные данные меняются по цепи, из предыдущего обработчика в следующий
$result = $eventman->applyFilter(DemoPoint::class, $input, $context);
// > middleware_wrapExisting@before
// > Gzhegow\Eventman\Subscriber\DemoSubscriber::demoMiddleware@before
// > Gzhegow\Eventman\Handler\DemoMiddleware::handle@before
// > filterHandler_changeTrueToFalse
// > Gzhegow\Eventman\Subscriber\DemoSubscriber::demoFilter
// > Gzhegow\Eventman\Handler\DemoFilterHandler::handle
// > Gzhegow\Eventman\Handler\DemoMiddleware::handle@after
// > Gzhegow\Eventman\Subscriber\DemoSubscriber::demoMiddleware@after
// > middleware_wrapExisting@after

echo PHP_EOL;

var_dump($result);
// > bool(false)

echo PHP_EOL;

// > запускаем конвеер события без привязки (вместо имени события - произвольный текст)
$input = null; // > можно передать входные данные, конвеер передаст оригинал в каждый обработчик
$pipeEvent->run('my-custom-text', $input, $context);
// > middleware_wrapExisting@before
// > eventHandler_sayHelloWorld
// > Hello, World!
// > middleware_wrapExisting@after

echo PHP_EOL;

// > запускаем конвеер фильтра без привязки (вместо имени события - произвольный текст)
$input = true; // > входные данные меняются по цепи, из предыдущего обработчика в следующий
$result = $pipeFilter->run('my-custom-text', $input, $context);
// > middleware_wrapExisting@before
// > filterHandler_changeTrueToFalse
// > middleware_wrapExisting@after

echo PHP_EOL;

var_dump($result);
// > bool(false)
