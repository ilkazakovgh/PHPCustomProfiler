<?php

class Profiler implements IProfiler
{
    private array $timers = [];
    private array $timerNames = [];
    private array $runningTimers = [];

    function __construct()
    {
    }

    function __destruct()
    {
        unset($this->timers);
        unset($this->timerNames);
        unset($this->runningTimers);
    }

    public function startTimer(string $timerName)
    {
        $profilerTimer = new ProfilerTimer();
        $profilerTimer->startTime = microtime(true);
        if (!array_key_exists($timerName, $this->timerNames))
            $this->timerNames[$timerName] = 0;
        else
            $this->timerNames[$timerName] += 1;

        $profilerTimer->groupName = $timerName;
        $profilerTimer->name = $timerName . '_' . $this->timerNames[$timerName];

        if (count($this->runningTimers) > 0)
            $profilerTimer->parentTimer = end($this->runningTimers);

        $this->timers[$profilerTimer->name] = $profilerTimer;
        $this->runningTimers[] = $profilerTimer->name;
    }

    public function endTimer(string $timerName)
    {
        if (array_key_exists($timerName, $this->timerNames)) {
            $this->timers[$timerName . '_' . $this->timerNames[$timerName]]->endTime = microtime(true);
            if (($key = array_search($timerName . '_' . $this->timerNames[$timerName], $this->runningTimers)) !== false) {
                unset($this->runningTimers[$key]);
            }
        }
    }

    private function getChildrenDurations(string $timerNameUnique) : float
    {
        $result = 0;
        array_walk($this->timers, function ($item) use ($timerNameUnique, &$result) {
            if ($item->parentTimer == $timerNameUnique) {
                $result += $item->totalDuration();
            }
        });
        return $result;
    }

    public function getTimers() : array
    {
        $result = [];

        foreach ($this->timers as $key => $timer) {
            $timer->childrenDuration = $this->getChildrenDurations($key);
            $timer->duration = $timer->totalDuration() - $timer->childrenDuration;

            if (!array_key_exists($timer->groupName, $result)) {
                $result[$timer->groupName] = ['count' => 0, 'duration' => 0];
            }

            $result[$timer->groupName]['count'] += 1;
            $result[$timer->groupName]['duration'] += $timer->duration;
        }

        return $result;
    }
}

