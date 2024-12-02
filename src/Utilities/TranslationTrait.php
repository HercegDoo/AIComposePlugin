<?php

namespace HercegDoo\AIComposePlugin\Utilities;

trait TranslationTrait
{
    protected function translation(string $key): string
    {
        return \rcmail::get_instance()->gettext("AIComposePlugin.{$key}");
    }
}
