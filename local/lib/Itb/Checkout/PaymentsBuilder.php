<?php

namespace Itb\Checkout;

class PaymentsBuilder
{
    private $paySystemMap = [
        'cash' => 'cash',
        'bank_card' => 'bank_card',
        'pay_invoice' => 'pay_invoice',
        'sbp' => 'sbp',
    ];

    /**
     * @var \Illuminate\Support\Collection from sale.order.ajax $arResult['PAY_SYSTEM']
     */
    private $paySystems;
    
    /**
     * @var int|null
     */
    private $selectedPaySystemId;

    public function __construct(array $paySystems, ?int $selectedPaySystemId)
    {
        $this->paySystems = collect($paySystems)->filter(fn($payment) => $this->paySystemMap[$payment['CODE']] ?? false);
        $this->selectedPaySystemId = $selectedPaySystemId;
    }

    /**
     * @return array доступные службы оплаты
     */
    public function buildPayments(): array
    {
        return $this->paySystems->mapWithKeys(function ($payment) {
            return [
                $this->paySystemMap[$payment['CODE']] => [
                    'name' => $payment['NAME'],
                    'logotip' => $payment['LOGOTIP'] ? \CFile::GetPath($payment['LOGOTIP']) : null,
                    'description' => $payment['DESCRIPTION'],
                ]
            ];
        })->toArray();
    }

    /**
     * @return string выбранную службу оплаты
     */
    public function getSelectedPaymentDTOKey(): string
    {
        $payment = $this->paySystems->first(fn($payment) => $payment['ID'] == $this->selectedPaySystemId);
        if(!$payment['CODE']){
            $payment = $this->paySystems->first();
        }
        return $this->paySystemMap[$payment['CODE'] ?? ''] ?? '';
    }

    /**
     * @return array [dtoKey => paySystemId]
     */
    public function buildIdsMap(): array
    {
        return $this->paySystems->mapWithKeys(fn($payment) => [
            $this->paySystemMap[$payment['CODE']] ?? '' => $payment['ID']
        ])->toArray();
    }
}
