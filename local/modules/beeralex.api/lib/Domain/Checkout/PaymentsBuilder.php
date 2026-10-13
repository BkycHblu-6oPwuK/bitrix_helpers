<?php
namespace Beeralex\Api\Domain\Checkout;

use Illuminate\Support\Collection;
use Beeralex\Api\Domain\Checkout\DTO\PaymentDTO;

class PaymentsBuilder
{
    private Collection $paySystems;
    private ?int $selectedPaySystemId;

    public function __construct(array $paySystems, ?int $selectedPaySystemId)
    {
        $this->paySystems = collect($paySystems);
        $this->selectedPaySystemId = $selectedPaySystemId;
    }

    /**
     * @return array<string, PaymentDTO> доступные службы оплаты
     */
    public function buildPayments(): array
    {
        return $this->paySystems->mapWithKeys(function ($payment) {
            $code = $this->resolveKey($payment);
            return [
                $code => PaymentDTO::make([
                    'id' => $payment['ID'],
                    'code' => $code,
                    'name' => $payment['NAME'],
                    'logotip' => $payment['LOGOTIP'] ? \CFile::GetPath($payment['LOGOTIP']) : null,
                    'description' => $payment['DESCRIPTION'],
                ])
            ];
        })->toArray();
    }

    /**
     * @return string выбранную службу оплаты
     */
    public function getSelectedPaymentDTOKey(): string
    {
        $payment = $this->paySystems->first(fn($payment) => $payment['ID'] == $this->selectedPaySystemId)
            ?: $this->paySystems->first();

        return $this->resolveKey($payment);
    }

    /**
     * @return array [dtoKey => paySystemId]
     */
    public function buildIdsMap(): array
    {
        return $this->paySystems->mapWithKeys(fn($payment) => [
            $this->resolveKey($payment) => $payment['ID']
        ])->toArray();
    }

    private function resolveKey(?array $payment): string
    {
        if (!$payment) {
            return '';
        }
        return (string)($payment['CODE'] ?: $payment['ID']);
    }
}
