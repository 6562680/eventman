<?php

use Gzhegow\Eventman\Eventman;
use Gzhegow\Eventman\EventmanFactory;
use Gzhegow\Eventman\Event\DemoEvent;
use Gzhegow\Eventman\Pipeline\Pipeline;
use Gzhegow\Eventman\Subscriber\DemoSubscriber;


require_once __DIR__ . '/vendor/autoload.php';


function eventHandler_sayHelloWorld($event)
{
    echo __FUNCTION__ . PHP_EOL;
}

function filterHandler_changeTrueToFalse($event, $bool)
{
    echo __FUNCTION__ . PHP_EOL;

    return ! $bool;
}

function middleware_wrapExisting($event, Pipeline $pipeline)
{
    echo __FUNCTION__ . '@before' . PHP_EOL;

    $result = $pipeline->next($event, $pipeline);

    echo __FUNCTION__ . '@after' . PHP_EOL;

    return $result;
}


// > напишите свою фабрику, которая будет использовать контейнер зависимостей
$eventmanFactory = new EventmanFactory();

// > создаем Eventman
$eventman = new Eventman($eventmanFactory);

// > регистрируем событие
$eventman->onEvent(DemoEvent::class, 'eventHandler_sayHelloWorld');
// > оборачиваем в middleware
$eventman->middleEvent(DemoEvent::class, 'middleware_wrapExisting');

// > регистрируем фильтр
$eventman->onFilter(DemoEvent::class, 'filterHandler_changeTrueToFalse');
// > оборачиваем в middleware
$eventman->middleFilter(DemoEvent::class, 'middleware_wrapExisting');

// > регистрируем подписчика (события/фильтры + обработчики/мидлвары их обслуживающие в одном классе)
$eventman->subscribe(DemoSubscriber::class);

// > можно задать какие-то свои настройки, которые будут нужны позже
$context = 'My Custom Data';
// $context = ['my_key' => 'My Custom Data'];
// $context = (object) ['my_key' => 'My Custom Data'];

// > стреляем событием, событие не возвращает данных
$eventman->fireEvent(DemoEvent::class, $context);
// > middleware_wrapExisting@before
// > Gzhegow\Eventman\Subscriber\DemoSubscriber::demoMiddleware@before
// > Gzhegow\Eventman\Handler\DemoMiddleware::handle@before
// > eventHandler_sayHelloWorld
// > Gzhegow\Eventman\Subscriber\DemoSubscriber::demoEvent
// > Gzhegow\Eventman\Handler\DemoEventHandler::handle
// > Gzhegow\Eventman\Handler\DemoMiddleware::handle@after
// > Gzhegow\Eventman\Subscriber\DemoSubscriber::demoMiddleware@after
// > middleware_wrapExisting@after

echo PHP_EOL;

// > фильтруем переменную, проводя её через все назначенные фильтры
$input = true;
$result = $eventman->applyFilter(DemoEvent::class, $input, $context);
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
