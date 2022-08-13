<?php

namespace cs\Interface;

use cs\Event\ReloadEvent;

interface Reloadable
{
    public function reload(): ?ReloadEvent;

}
