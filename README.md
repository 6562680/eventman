# Eventman


## Что это?

Это сильно улучшенная реализация паттерна Observer.  
Это сильно упрощенная реализация MessageBus.

## Как это готовить?

Шина сообщений работает по принципу "применить к сообщению обработчики которые на него подписаны", то есть любой Observer - это простейшая шина.

- Оберните сообщение в обьект Конверт, и прилепляйте на него штампы - и можно хранить историю его изменений, как в `symfony/messenger`.
- Привяжите на ваши сообщения по классу или строке обработчики получения/отчета или отправки. Удобно соединять получение и отчет с помощью Middleware.
- Подмените класс фабрики и подключите контейнер зависимостей при запуске обработчиков.
- Подмените парсер сообщений, если нужно извлекать из сообщения адресатов, на которые обработчики были привязаны.
- Если приложение содержит множество доменов (областей знаний), можете использовать подписчиков, чтобы держать настройки подписок в разных пространствах имен.
- Если вам не нужны точки привязки - используйте конвеер прямо в коде и получите тот же функционал без привязок.

## Термины:

- Точка (point) - точка привязки действия
- Обработчик (handler) - действие, которое нужно выполнить
- Мидлвар (middleware) - обертка для точки, позволяющая выполнить действие до и после обработчиков
- Конвеер (pipeline) - цепочка обработчиков, обернутая в middleware(-s)
- Подписчик (subscriber) - класс, содержащий несколько привязок обработчиков к точкам (может содержать и сами обработчики)
- Событие (event) - способ запуска цепочки, где каждому обработчику отправляются исходные данные и ответ не важен
- Фильтр (filter) - способ запуска цепочки, где каждое следующее звено получает результат предыдущего

## Принцип работы:

- Привязываем фильтр или действие на точку
- Триггерим точку, передавая данные
- Если не нужна точка - используем конвееры и запускаем данные через них

## Зачем?

Необходимость в событиях в системе возникает тогда, когда один из модулей должен влиять или следить за поведением другого.

События на первых порах создают ощущение полёта - код получается очень чистый и красивый.  
Нужно понимать, что большое количество событий в программе ведёт к необходимости рисовать на бумаге их очередь выполнения и гадать какое из действий привело к результату.  
С другой стороны избыток ООП приведет к дублированию не методов, а целых слоёв логики, что придется потом переписывать.

Например, модуль авторизации может впустить человека без пароля если он ничего не покупал еще. В этом явно будет участвовать модуль магазина.   
Но если вы укажете зависимость в авторизацию на модуль магазина - то без него она теперь работать вообще не будет.

Правильный путь - создать управляющий модуль для их двоих, декорирующий оригинал авторизации, и использовать его.

Но если сторонних влияний десятки, то таких декораторов будет много. Придется писать декоратор под каждый кусок логики.

Чтобы не заниматься такой ерундой используют события и фильтры. И вообще практика настолько распространенная, что Wordpress почти целиком построен на этой технологии. Но, как я уже сказал, пользуйтесь осторожно, увлечётесь, создадите проблемы, которые невозможно отследить. Что и происходит в Wordpress, за что его все терпеть не могут.

## Todo

Сделать, чтобы исполнители возвращали генератор, позволяя запускать параллельно по одному шагу из каждой цепочки за тик

```php
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
```
