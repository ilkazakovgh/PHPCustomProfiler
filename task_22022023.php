<?php

require_once __DIR__ . "/Profiler/ProfilerTimer.php";
require_once __DIR__ . "/Profiler/Profiler.php";

/*
Написать класс Profiler, умеющий замерять скорость работы программы.
Важно, чтобы каждый таймер не учитывал время вложенных таймеров.
*/

interface IProfiler
{
    public function startTimer(string $timerName);
    public function endTimer(string $timerName);
    public function getTimers() :array;
}

function testProfiler(IProfiler $profiler) {
    $profiler->startTimer('main');

    sleep(1);

    $profiler->startTimer('doLoop');

    sleep(3);

    for ($i = 0; $i < 10; $i++) {
        $profiler->startTimer('processItem');
        sleep(1);
        $profiler->endTimer('processItem');
    }

    sleep(2);


    $profiler->endTimer('doLoop');

    usleep(200000); //Спим 0.2 секунды

    $profiler->startTimer('doLoop');
    $profiler->endTimer('doLoop');

    $profiler->endTimer('main');

    $result = $profiler->getTimers();

    print_r($result);

    //Вот это должно вернуть скорее всего. Время округляем до 0.001 секунды.
    $correctResult = [
        'processItem' => [ //10 раз спали по секунде на строке 25
            'count' => 10, //Количество запусков таймера с таким именем
            'duration' => 10, //Суммарная продолжитьность запусков таймера с таким именем без времени вложенных таймеров
        ],
        'doLoop' => [ //3 секунды на строке 21 и ещё 2 секунды на строке 29. То что мы спали на строке 25 не берём, так как этот код внутри вложенного таймера.
            'count' => 2, //Два замера doLoop, на строке 19 и 36. На строке 36 считаем что он работал меньше милесекунды.
            'duration' => 5,
        ],
        'main' => [ //1 секунду спали на строке 17 и ещё 0.2 секунды на строке 34. Всё что вложено в doLoop не берём.
            'count' => 1,
            'duration' => 1.2,
        ],
    ];
}


testProfiler(new Profiler());
