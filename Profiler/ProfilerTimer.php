<?php

class ProfilerTimer
{
    public string $name = '';
    public string $groupName = '';

    public float $startTime = 0;
    public float $endTime = 0;
    public float $duration = 0;
    public float $childrenDuration = 0;

    public ?string $parentTimer = null;

    public function totalDuration(): float
    {
        return round($this->endTime - $this->startTime, 4);
    }
}