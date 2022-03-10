<?php

namespace App\Messenger;

interface QueryBusInterface
{
    public function fetch(mixed $query): mixed;
}