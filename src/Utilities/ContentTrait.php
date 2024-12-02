<?php

namespace HercegDoo\AIComposePlugin\Utilities;

trait ContentTrait
{
    protected function translation(string $key): string
    {
        return \rcmail::get_instance()->gettext("AIComposePlugin.{$key}");
    }
}
