# Eventman


## Что это?

Это сильно улучшенная реализация паттерна Observer.  
Это сильно упрощенная реализация MessageBus.

Шина сообщений работает по принципу "применить к сообщению обработчики которые на него подписаны".  
Используя этот инструмент, можно привязать на произвольный класс сообщения обработчики получения, отчета и отправки и будет абстракция для шины.

А если немножко постараться, то сообщение можно обернуть в класс Конверт и прилепляя на него штампы - хранить историю его изменений или передавать адрес, как в `symfony/messenger`;

Подменив класс фабрики, можно подключить как контейнер зависимостей.
Подменив класс фабрики, можно изменить парсер сообщений, чтобы извлекать из конверта адрес доставки, на который обработчики были зарегистрированы.

Инструмент поддерживает отложенную инициализацию Subscriber для экономии памяти на хранении колбэков.


## Зачем это?

Необходимость в событиях в системе возникает тогда, когда один из модулей должен влиять или следить за поведением другого.

События на первых порах создают ощущение полёта - код получается очень чистый и красивый.  
Нужно понимать, что большое количество событий в программе ведёт к необходимости рисовать на бумаге их очередь выполнения и гадать какое из действий привело к результату.  
С другой стороны избыток ООП приведет к дублированию не методов, а целых слоёв логики, что придется потом переписывать.

Например, модуль авторизации может впустить человека без пароля если он ничего не покупал еще. В этом явно будет участвовать модуль магазина.   
Но если вы укажете зависимость в авторизацию на модуль магазина - то без него она теперь работать вообще не будет.

Правильный путь - создать управляющий модуль для их двоих, декорирующий оригинал авторизации, и использовать его.

Но если сторонних влияний десятки, то таких декораторов будет много. Придется писать декоратор под каждый кусок логики.

Чтобы не заниматься такой ерундой используют события и фильтры. И вообще практика настолько распространенная, что Wordpress почти целиком построен на этой технологии. Но, как я уже сказал, пользуйтесь осторожно, увлечётесь, создадите проблемы, которые невозможно отследить.

```php
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
```
