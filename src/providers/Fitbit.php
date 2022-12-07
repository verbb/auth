<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\fitbit\provider\Fitbit as FitbitProvider;

class Fitbit extends FitbitProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return static::BASE_FITBIT_API_URL . '/1';
    }
}