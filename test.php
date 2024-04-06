<?php

use Gzhegow\Eventman\Eventman;
use Gzhegow\Eventman\EventmanFactory;
use Gzhegow\Eventman\Event\DemoEvent;
use Gzhegow\Eventman\Pipeline\Pipeline;
use Gzhegow\Eventman\Subscriber\DemoSubscriber;


require_once __DIR__ . '/vendor/autoload.php';


function sayHelloWorld($event)
{
    echo __FUNCTION__ . PHP_EOL;
}

function changeTrueToFalse($event, $bool)
{
    echo __FUNCTION__ . PHP_EOL;

    return ! $bool;
}

function middleThis($event, Pipeline $pipeline)
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
$eventman->onEvent(DemoEvent::class, 'sayHelloWorld');
// > оборачиваем в middleware
$eventman->middleEvent(DemoEvent::class, 'middleThis');

// > регистрируем фильтр
$eventman->onFilter(DemoEvent::class, 'changeTrueToFalse');
// > оборачиваем в middleware
$eventman->middleFilter(DemoEvent::class, 'middleThis');

// > регистрируем подписчика (события/фильтры + обработчики/мидлвары их обслуживающие в одном классе)
$eventman->subscribe(DemoSubscriber::class);

// > можно задать какие-то свои настройки, которые будут нужны позже
$context = 'My Custom Data';
// $context = ['my_key' => 'My Custom Data'];
// $context = (object) ['my_key' => 'My Custom Data'];

// > стреляем событием, событие не возвращает данных
$eventman->fireEvent(DemoEvent::class, $context);
// > middleThis@before
// > Gzhegow\Eventman\Subscriber\DemoSubscriber::demoMiddleware@before
// > Gzhegow\Eventman\Handler\DemoMiddleware::handle@before
// > sayHelloWorld
// > Gzhegow\Eventman\Subscriber\DemoSubscriber::demoEvent
// > Gzhegow\Eventman\Handler\DemoEventHandler::handle
// > Gzhegow\Eventman\Handler\DemoMiddleware::handle@after
// > Gzhegow\Eventman\Subscriber\DemoSubscriber::demoMiddleware@after
// > middleThis@after

echo PHP_EOL;

// > фильтруем переменную, проводя её через все назначенные фильтры
$input = true;
$result = $eventman->applyFilter(DemoEvent::class, $input, $context);
// > middleThis@before
// > Gzhegow\Eventman\Subscriber\DemoSubscriber::demoMiddleware@before
// > Gzhegow\Eventman\Handler\DemoMiddleware::handle@before
// > changeTrueToFalse
// > Gzhegow\Eventman\Subscriber\DemoSubscriber::demoFilter
// > Gzhegow\Eventman\Handler\DemoFilterHandler::handle
// > Gzhegow\Eventman\Handler\DemoMiddleware::handle@after
// > Gzhegow\Eventman\Subscriber\DemoSubscriber::demoMiddleware@after
// > middleThis@after

echo PHP_EOL;

var_dump($result);
// > bool(false)
