<?php

use Gzhegow\Eventman\Eventman;
use Gzhegow\Eventman\EventmanFactory;
use Gzhegow\Eventman\Event\DemoEvent;
use Gzhegow\Eventman\Filter\DemoFilter;
use Gzhegow\Eventman\Subscriber\DemoSubscriber;


require_once __DIR__ . '/vendor/autoload.php';


function sayHelloWorld($event)
{
    echo __FUNCTION__ . PHP_EOL;
}

function changeTrueToFalse($filter, $bool)
{
    echo __FUNCTION__ . PHP_EOL;

    return ! $bool;
}


// > напишите свою фабрику, которая будет использовать контейнер зависимостей
$eventmanFactory = new EventmanFactory();

// > создаем Eventman
$eventman = new Eventman($eventmanFactory);

// > регистрируем событие
$eventman->onEvent(DemoEvent::class, 'sayHelloWorld');

// > регистрируем фильтр
$eventman->onFilter(DemoFilter::class, 'changeTrueToFalse');

// > регистрируем подписчика (события и фильтры + методы их обслуживающие в одном классе)
$eventman->subscribe(DemoSubscriber::class);

// > можно задать какие-то свои настройки, которые будут нужны позже
$context = 'My Custom Data';
// $context = ['my_key' => 'My Custom Data'];
// $context = (object) ['my_key' => 'My Custom Data'];

// > стреляем событием, событие не возвращает данных
$eventman->fireEvent(DemoEvent::class, $context);
// > sayHelloWorld
// > Gzhegow\Eventman\Subscriber\DemoSubscriber::demoEvent
// > Gzhegow\Eventman\Handler\DemoEventHandler

// > фильтруем переменную, проводя её через все назначенные фильтры
$input = true;
$result = $eventman->applyFilter(DemoFilter::class, $input, $context);
// > changeTrueToFalse
// > Gzhegow\Eventman\Subscriber\DemoSubscriber::demoFilter
// > Gzhegow\Eventman\Handler\DemoFilterHandler
var_dump($result);
// > bool(false)
