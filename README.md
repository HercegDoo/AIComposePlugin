
# AI Email Generator Plugin for Roundcube

## Overview

The AI Email Generator plugin for Roundcube enhances the email composing experience by integrating AI-based email generation capabilities. With this plugin, users can generate professional and personalized emails with just a few clicks, saving time and effort.

**Current Version:** v2.0.0

### Features

1. **AI Email Generation:**
    - Allows users to generate an email by providing specific instructions.

2. **Customizable Parameters: Users can choose:**

   - Style: The tone and style of the email (e.g., formal, casual, informational, etc.).

   - Length: How long the generated email should be (short, medium, or long).

   - Creativity: Adjust the level of creativity in the email (low, medium, or high).

   - Language: Choose from Bosnian, Croatian, English, German, or Dutch.
   
3. **User-Defined Predefined Instructions:**
     - Users can create custom predefined instructions that they can reuse for generating emails. Instead of typing instructions each time, users can save and select frequently used ones, enhancing productivity.
4. **Default Settings:**
     - Users can set default values for style, length, creativity, and language. These default settings will be automatically applied during email generation, allowing for a more streamlined experience.
5. **Seamless Integration:**
   - Adds a new button to the Compose page in Roundcube that opens a prompt for email generation.

## Install
1. Clone repository content to an `AIComposePlugin` directory inside your RoundCube `plugins` directory.
2. Then reference the plugin by adding an item `AIComposePlugin` to the RoundCube plugins list in the configuration:

   ```php
   $config['plugins'] = array('AIComposePlugin', ...);
   
3. Dependencies and built frontend bundles are already included. If you want to rebuild them manually, navigate to the plugin directory and run:

```bash
composer install --no-dev
npm install --omit=dev
npm run build:prod
```
4. Fill in the settings in the config.inc.php.dist file according to the instructions provided within it. After completing the configuration, rename the file to config.inc.php.
 
## Usage
1. **Compose a New Email:**

    - Open Roundcube and start composing a new email.
    - A new button labeled **Generate** will appear on the Compose page.

2. **Open the AI Prompt:**

    - Click on the **Generate Email** button.
    - A prompt will appear where you can:
        - Enter instructions for the email.
        - Choose the style, length, creativity, and language of the email.

3. **Generate Email:**

    - Provide the necessary details, and click **Generate**.
    - The AI will generate an email based on the provided input.
    - The generated email can be inserted into the Compose window, ready for further editing or immediate sending.
  

![image](https://github.com/user-attachments/assets/15a813ee-65a6-483d-906c-1abd1beb0bad)

## Maintaining prompts and providers

Prompt wording lives in `src/AIEmailService/Prompt/EmailPromptBuilder.php`. It builds both the system instruction and the user instruction for new emails and selected-text revisions. Change wording there and update `tests/AIEmailService/Prompt/EmailPromptBuilderTest.php`.

`AIEmail::generate()` builds an `EmailPrompt` before calling the configured provider. It also accepts a `PromptBuilderInterface` implementation as an optional second argument when a different prompt strategy is needed. A new provider implements `InterfaceProvider::generateEmail(RequestData $requestData, EmailPrompt $prompt)` and translates those instructions into its API's request format. Provider classes handle transport and responses; they do not need their own copy of the email prompt. Register a new provider in `Settings::setProvider()`, then configure `aiComposeProvider` and `aiProvider<ProviderName>Config`; task initialization loads that configuration by provider name.

## OpenAI models

Set `aiProviderOpenAIConfig['model']` in `config.inc.php` to an API model ID. Supported examples are `gpt-4.1`, `gpt-5`, `gpt-6-astra`, `gpt-6-sol`, and `gpt-6-luna`. The ChatGPT product names and plain `gpt-6` are not valid API model IDs. Your OpenAI API project must have access to the selected model.

The OpenAI provider uses the Chat Completions API for all of these models. GPT-5 and GPT-6 requests send shared instructions in a `developer` message, use `max_completion_tokens`, and omit `temperature`, which those reasoning models may reject. For `gpt-5`, the provider requests `minimal` reasoning effort; for the three GPT-6 models, it requests `low`. The creativity setting controls temperature only for older models such as `gpt-4.1`; it has no effect with GPT-5 or GPT-6. A custom `apiUrl` must point to a Chat Completions-compatible endpoint.

`aiDefaultMaxTokens` limits both visible output and reasoning tokens on GPT-5 and GPT-6. If generation stops before returning an email, increase this value. The provider reports this case when the API returns a `length` finish reason.
