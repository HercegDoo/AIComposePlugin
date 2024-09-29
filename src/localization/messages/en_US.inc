<?php

$messages['api_error_contact_admin'] = 'An error occurred while generating the email, please contact the administrator.';

$messages['ai_no_instructions'] = 'Type instructions';
$messages['ai_instructions_too_long'] = 'Your instructions are too long; they can have a maximum of $n characters.';
$messages['ai_empty_response'] = 'Server returned an empty response';

$messages['ai_validation_error_not_enough_characters_instruction'] = 'Number of characters must be greater than 1!';
$messages['ai_validation_error_invalid_input_instructions'] = "The 'instructions' field must contain valid text!";
$messages['ai_validation_error_invalid_input_subject'] = "The 'subject' field must contain valid text.";
$messages['ai_validation_error_invalid_recipient_email_address'] = "Recipient's email address is invalid.";
$messages['ai_validation_error_invalid_sender_email_address'] = "Sender's email address is invalid.";
$messages['ai_validation_error_invalid_style'] = "The 'style' field is invalid!";
$messages['ai_validation_error_invalid_language'] = "The 'language' field is invalid!";
$messages['ai_validation_error_invalid_length'] = "The 'length' field is invalid!";
$messages['ai_validation_error_invalid_creativity'] = "The 'creativity' field is invalid!";
$messages['ai_validation_error_sender_name_mandatory'] = "Sender's name is required!";
$messages['ai_validation_error_invalid_sender_name_text'] = "The 'Sender Name' field must contain valid text!";
$messages['ai_validation_error_invalid_recipient_name_text'] = "The 'Recipient Name' field must contain valid text!";
$messages['ai_validation_error_not_enough_characters_sender_name'] = "The 'Sender Name' field must be at least 3 characters long.";
$messages['ai_validation_error_not_enough_characters_recipient_name'] = "The 'Recipient Name' field must be at least 3 characters long if it is provided.";

$messages['ai_predefined_request_error'] = 'An error occurred while processing the request.';
$messages['ai_predefined_delete_error'] = 'An error occurred while deleting the instruction.';
$messages['ai_predefined_get_all_instructions_error'] = 'An error occurred while fetching instructions.';
$messages['ai_predefined_get_specific_instruction_error'] = 'An error occurred while fetching information for the instruction.';
$messages['ai_predefined_content_error'] = 'Error displaying the page.';
$messages['ai_predefined_max_instructions_error'] = "The maximum number of available instructions is 10!";
