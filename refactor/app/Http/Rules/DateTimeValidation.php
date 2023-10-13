<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DateTimeValidation implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (request()->immediate != 'yes') {
            $dateTime = request()->due_date . " " . request()->due_time;
            $formatDate = Carbon::createFromFormat('m-d-Y H:i', $dateTime);
            return $formatDate->isPast();
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "Can't create booking in past";
    }
}
