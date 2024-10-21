
# AI Email Generator Plugin for Roundcube

## Overview

The AI Email Generator plugin for Roundcube enhances the email composing experience by integrating AI-based email generation capabilities. With this plugin, users can generate professional and personalized emails with just a few clicks, saving time and effort.

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

1. Clone repository content to a AIComposePlugin directory inside your RoundCube plugins directory.
2. Then reference plugin by adding an item `AIComposePlugin` to RoundCube plugins list in configuration (`$config['plugins']` variable in file `$ROUNDCUBE_INSTALL_DIRECTORY/config/config.inc.php`). Ensure your web user has read access to the plugin directory and all files in it.
3. Rename config.inc.php.dist to config.inc.php in the AIComposePlugin directory

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
