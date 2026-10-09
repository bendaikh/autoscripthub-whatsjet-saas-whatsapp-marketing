<?php
/**
 * WhatsJet
 *
 * This file is part of the WhatsJet software package developed and licensed by livelyworks.
 *
 * You must have a valid license to use this software.
 *
 * © 2024 - 2026 livelyworks. All rights reserved.
 * Redistribution or resale of this file, in whole or in part, is prohibited without prior written permission from the author.
 *
 * For support or inquiries, contact: contact@livelyworks.net
 *
 * @package     WhatsJet
 * @author      livelyworks <contact@livelyworks.net>
 * @copyright   Copyright (c) 2024 - 2026 livelyworks
 * @website     https://livelyworks.net
 */


namespace App\Yantrana\Components\WhatsAppService\Requests;

use App\Yantrana\__Laraware\Core\CoreRequestTwo;

class SendMessageRequest extends CoreRequestTwo
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $messageBody = $this->input('message_body');

        if ($messageBody !== null) {
            if (strlen($messageBody) == 1 and in_array($messageBody, ['<', '>'])) {
                $messageBody = str_replace(
                    ['<', '>'],
                    ['__SINGLE_OPEN_PLACEHOLDER__', '__SINGLE_CLOSE_PLACEHOLDER__'],
                    $messageBody
                );
            } else {
                $messageBody = str_replace(
                    ['<<', '>>'],
                    ['__OPEN_PLACEHOLDER__', '__CLOSE_PLACEHOLDER__'],
                    $messageBody
                );
            }
            
            $this->merge([
                'message_body' => $messageBody,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'contact_uid' => 'required',
            'message_body' => ['required', 'string', 'max:4096'],
        ];
    }

    protected function passedValidation(): void
    {
        $messageBody = $this->input('message_body');

        if ($messageBody !== null) {
            if (in_array($messageBody, ['__SINGLE_OPEN_PLACEHOLDER__', '__SINGLE_CLOSE_PLACEHOLDER__'])) {
                $this->merge([
                    'message_body' => str_replace(
                        ['__SINGLE_OPEN_PLACEHOLDER__', '__SINGLE_CLOSE_PLACEHOLDER__'],
                        ['<', '>'],
                        $messageBody
                    ),
                ]);
            } else {
                $this->merge([
                    'message_body' => str_replace(
                        ['__OPEN_PLACEHOLDER__', '__CLOSE_PLACEHOLDER__'],
                        ['<<', '>>'],
                        $messageBody
                    ),
                ]);
            }
        }
    }
}
