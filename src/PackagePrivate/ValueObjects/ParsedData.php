<?php

namespace EDTF\PackagePrivate\ValueObjects;

use EDTF\PackagePrivate\ValueObjects\Composites\Date;
use EDTF\PackagePrivate\ValueObjects\Composites\Qualification;
use EDTF\PackagePrivate\ValueObjects\Composites\Time;
use EDTF\PackagePrivate\ValueObjects\Composites\Timezone;

class ParsedData
{
    private Date $date;

    private Time $time;

    private Qualification $qualification;

    private Timezone $timezone;

    public function __construct(Date $date, Time $time, Qualification $qualification, Timezone $timezone)
    {
        $this->date = $date;
        $this->time = $time;
        $this->qualification = $qualification;
        $this->timezone = $timezone;
    }

    public function getDate(): Date
    {
        return $this->date;
    }

    public function getTime(): Time
    {
        return $this->time;
    }

    public function getQualification(): Qualification
    {
        return $this->qualification;
    }

    public function getTimezone(): Timezone
    {
        return $this->timezone;
    }
}
