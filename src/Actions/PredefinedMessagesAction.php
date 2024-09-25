<?php

namespace HercegDoo\AIComposePlugin\Actions;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;

class PredefinedMessagesAction extends AbstractAction
{

    protected function handler(): void
    {
       error_log("Ucitan hendlar za custom akciju");
    }

    protected function validate(): void
    {
        error_log("Ucitan hendlar za custom akciju");
    }
}