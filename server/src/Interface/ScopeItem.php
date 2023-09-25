<?php

namespace cs\Interface;

interface ScopeItem
{

    public function scope(): void;

    public function isScopedIn(): bool;

}
