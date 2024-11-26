<?php

namespace HercegDoo\AIComposePlugin\Utilities;

abstract class AbstractUtility
{
    protected function translation(string $key): string
    {
        return \rcmail::get_instance()->gettext("AIComposePlugin.{$key}");
    }
}