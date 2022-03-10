<?php

namespace App\Messenger;

interface CommandBusInterface
{
    public function dispatch(mixed $command): mixed;
}