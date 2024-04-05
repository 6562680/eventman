# Eventman

Необходимость в событиях в системе возникает тогда, когда один из модулей должен влиять или следить за поведением другого.

События на первых порах создают ощущение полёта - код получается очень чистый и красивый.  
Нужно понимать, что большое количество событий в программе ведёт к необходимости рисовать на бумаге их очередь выполнения и гадать какое из действий привело к результату.  
С другой стороны избыток ООП приведет к дублированию не методов, а целых слоёв логики, что придется потом переписывать.

Например, модуль авторизации может впустить человека без пароля если он ничего не покупал еще. В этом явно будет участвовать модуль магазина.   
Но если вы укажете зависимость в авторизацию на модуль магазина - то без него она теперь работать вообще не будет.

Правильный путь - создать управляющий модуль для их двоих, декорирующий оригинал авторизации, и использовать его.

Но если сторонних влияний десятки, то таких декораторов будет много. Придется писать декоратор под каждый кусок логики.

Чтобы не заниматься такой ерундой используют события и фильтры. И вообще практика настолько распространенная, что Wordpress почти целиком построен на этой технологии. Но, как я уже сказал, пользуйтесь осторожно, увлечётесь, создадите проблемы, которые невозможно отследить.

Инструмент поддерживает отложенную инициализацию Subscriber, также заменив реализацию Factory на свою можно подключить контейнер зависимостей.

```php
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

dd($eventman);
```
