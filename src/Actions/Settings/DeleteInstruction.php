<?php

namespace HercegDoo\AIComposePlugin\Actions\Settings;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;

class DeleteInstruction extends AbstractAction
{
    private array $predefinedInstructions;

    protected function handler(): void
    {
        $rcmail = \rcmail::get_instance();
        self::$plugin->include_script('assets/dist/settings.bundle.js');

        header('Content-Type: application/json');

        $id = \rcube_utils::get_input_value('id', \rcube_utils::INPUT_POST);
        $filteredInstructionsArray = $this->removeItemById($this->predefinedInstructions, $id);

        $rcmail->user->save_prefs([
            'predefinedInstructions' => $filteredInstructionsArray,
        ]);

        echo json_encode([
            'status' => 'success',
            'returnValue' => $filteredInstructionsArray,
        ]);
    }

    protected function validate(): void
    {
        $this->predefinedInstructions = \rcmail::get_instance()->user->get_prefs()['predefinedInstructions'] ?? [];
    }

    /**
     * @param array<array<string, string>> $items
     * @param null|array<string>|string    $id
     *
     * @return array<array<string, string>>
     */
    private function removeItemById(array $items, $id): array
    {
        $filteredItems = array_filter($items, static function ($item) use ($id) {
            return $item['id'] !== $id;
        });

        return array_values($filteredItems);
    }
}
