<?php

namespace App\Livewire;

use App\Models\DigitalWorkshop;
use Illuminate\Support\Facades\App;
use Livewire\Attributes\Url;
use Livewire\Component;

class DigitalWorkshopLanding extends Component
{
    public ?DigitalWorkshop $workshop = null;

    #[Url(as: 'lang', keep: true)]
    public string $locale = 'id';

    public function mount(): void
    {
        $this->locale = in_array($this->locale, ['en', 'id']) ? $this->locale : 'id';
        App::setLocale($this->locale);

        $this->workshop = DigitalWorkshop::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }

    public function setLocale(string $locale): void
    {
        if (in_array($locale, ['en', 'id'])) {
            $this->locale = $locale;
            App::setLocale($locale);
            $this->dispatch('locale-changed', locale: $locale);
        }
    }

    public function updatedLocale(): void
    {
        $this->locale = in_array($this->locale, ['en', 'id']) ? $this->locale : 'id';
        App::setLocale($this->locale);
    }

    /**
     * Null when there is no published workshop, so the price line is omitted
     * rather than rendered as "IDR 0".
     */
    public function getFormattedStandalonePriceProperty(): ?string
    {
        return $this->formatPrice($this->workshop?->price);
    }

    /**
     * Null when the workshop has no bundle price (an admin cleared it), so the
     * bundle line disappears instead of advertising a free bundle.
     */
    public function getFormattedBundlePriceProperty(): ?string
    {
        return $this->formatPrice($this->workshop?->bundle_price);
    }

    public function render()
    {
        return view('livewire.digital-workshop-landing')
            ->title(__('seminar.digital_workshop_title').' - '.config('app.name'));
    }

    private function formatPrice(int|float|null $amount): ?string
    {
        if ($amount === null) {
            return null;
        }

        return number_format($amount, 0, ',', '.');
    }
}
